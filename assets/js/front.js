// front.js - REST-based frontend (vanilla JS)
// Hybrid filtering: server returns normalized ISO with Europe/Ljubljana offset.
// Client re-checks to hide only events, ki so zares že končani danes.

(function () {
	'use strict';

	if ( typeof SNIPI_FRONT_REST === 'undefined' ) {
		return;
	}

	var restRoot         = SNIPI_FRONT_REST.rest_root.replace( /\/$/, '' );
	var postId           = SNIPI_FRONT_REST.post_id;
	var rowsPerPage      = parseInt( SNIPI_FRONT_REST.rowsPerPage || 8, 10 );
	var autoplayInterval = parseInt( SNIPI_FRONT_REST.autoplayInterval || 10, 10 );
	var showProgramColumn = SNIPI_FRONT_REST.showProgramColumn === '1';

	function parseISOToMs( isoString ) {
		if ( ! isoString ) return NaN;
		var d = new Date( isoString );
		if ( ! isNaN( d.getTime() ) ) {
			return d.getTime();
		}
		var normalized = isoString.replace( ' ', 'T' );
		if ( ! /Z$/i.test( normalized ) && ! /[+-]\d{2}:\d{2}$/.test( normalized ) ) {
			normalized += 'Z';
		}
		var d2 = new Date( normalized );
		return d2.getTime();
	}

	function nowLjubljana() {
		var now = new Date();
		var formatter = new Intl.DateTimeFormat( 'en-US', {
			timeZone: 'Europe/Ljubljana',
			year: 'numeric',
			month: '2-digit',
			day: '2-digit',
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hour12: false
		} );

		var parts = formatter.formatToParts( now );
		var map   = {};
		for ( var i = 0; i < parts.length; i++ ) {
			map[ parts[ i ].type ] = parts[ i ].value;
		}

		var year   = parseInt( map.year, 10 );
		var month  = parseInt( map.month, 10 ) - 1;
		var day    = parseInt( map.day, 10 );
		var hour   = parseInt( map.hour, 10 );
		var minute = parseInt( map.minute, 10 );
		var second = parseInt( map.second, 10 );

		// Date objekt z lokalnim časom za Europe/Ljubljana
		return new Date( year, month, day, hour, minute, second );
	}

	function formatTimeRange( startIso, endIso ) {
		if ( ! startIso || ! endIso ) {
			return '';
		}
		var s = new Date( parseISOToMs( startIso ) );
		var e = new Date( parseISOToMs( endIso ) );
		if ( isNaN( s ) || isNaN( e ) ) return '';
		var sH = s.toLocaleTimeString( 'sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false } );
		var eH = e.toLocaleTimeString( 'sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false } );
		return sH + ' - ' + eH;
	}

	function getEndpoint() {
		return restRoot + '/snipi/v1/ekrani/timeslots?post_id=' + encodeURIComponent( postId );
	}

	function getDayKey( isoString ) {
		var ms = parseISOToMs( isoString );
		if ( isNaN( ms ) ) return '';
		var d     = new Date( ms );
		var month = String( d.getMonth() + 1 ).padStart( 2, '0' );
		var day   = String( d.getDate() ).padStart( 2, '0' );
		var year  = d.getFullYear();
		return year + '-' + month + '-' + day;
	}

	// Added: helper to format future-day labels for weekend mode and upcoming events.
	function formatDayLabel( isoString ) {
		var ms = parseISOToMs( isoString );
		if ( isNaN( ms ) ) {
			return '';
		}

		return new Date( ms ).toLocaleDateString( 'sl-SI', {
			weekday: 'long',
			day: 'numeric',
			month: 'long'
		} );
	}

	// Added: stamp each item with its day key/label so pagination can show day headers.
	function decorateDayMetadata( orderedItems ) {
		var todayKey = getDayKey( nowLjubljana().toISOString() );

		orderedItems.forEach( function ( item ) {
			var startIso = item.start_iso || item.start || '';
			var dayKey   = getDayKey( startIso );

			item._dayKey   = dayKey;
			item._dayLabel = '';

			if ( dayKey && dayKey !== todayKey ) {
				item._dayLabel = formatDayLabel( startIso );
			}
		} );
	}

       function groupByDay( items ) {
               var map = {};
               items.forEach( function ( it ) {
                       var startIso = it.start_iso || it.start || '';
			var key      = getDayKey( startIso );
			if ( ! key ) return;
			if ( ! map[ key ] ) {
				map[ key ] = [];
			}
			map[ key ].push( it );
		} );
		return map;
	}

	function sortItems( items ) {
		return items.slice().sort( function ( a, b ) {
			var as = parseISOToMs( a.start_iso || a.start || '' );
			var bs = parseISOToMs( b.start_iso || b.start || '' );
			if ( isNaN( as ) && isNaN( bs ) ) return 0;
			if ( isNaN( as ) ) return 1;
			if ( isNaN( bs ) ) return -1;
			return as - bs;
		} );
	}

	function resolveProgram( it ) {
		if ( it.program && typeof it.program === 'string' ) {
			return it.program;
		}
		if ( it.program_display && typeof it.program_display === 'string' ) {
			return it.program_display;
		}
		if ( it.program_name && typeof it.program_name === 'string' ) {
			return it.program_name;
		}
		if ( it.programTitle && typeof it.programTitle === 'string' ) {
			return it.programTitle;
		}
		if ( it.project && typeof it.project === 'string' ) {
			return it.project;
		}
		return '';
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var shells = document.querySelectorAll( '.snipi--shell' );
		if ( ! shells.length ) {
			return;
		}

		shells.forEach( function ( container ) {

			var headerDate   = container.querySelector( '.snipi__date' );
			var headerClock  = container.querySelector( '.snipi__clock-value' );
			var table        = container.querySelector( '.snipi__table' );
			var tbody        = container.querySelector( 'tbody' );
			var paginationEl = container.querySelector( '.snipi__pagination' );
			var bottomRowEl  = container.querySelector( '[data-snipi-bottom-row]' );
			var logoEl       = container.querySelector( '.snipi__logo' );

			var items         = [];
			var currentPage   = 1;
			var totalPages    = 1;
			var autoplayTimer = null;
			var fetchTimer    = null;

			function syncHeaderColumns( shouldShowProgram ) {
				if ( ! table ) {
					return;
				}

				var headRow = table.querySelector( 'thead tr' );
				if ( ! headRow ) {
					return;
				}

				var programTh = headRow.querySelector( '[data-snipi-program]' );

				if ( shouldShowProgram && ! programTh ) {
					var newTh = document.createElement( 'th' );
					newTh.setAttribute( 'data-snipi-col', 'program' );
					newTh.setAttribute( 'data-snipi-program', '1' );
					newTh.textContent = 'PROGRAM';

					var teacherTh = headRow.querySelector( '[data-snipi-col="teacher"]' );
					if ( teacherTh ) {
						headRow.insertBefore( newTh, teacherTh );
					} else {
						headRow.appendChild( newTh );
					}
				} else if ( ! shouldShowProgram && programTh ) {
					headRow.removeChild( programTh );
				}
			}

			syncHeaderColumns( showProgramColumn );

			// =========================
			// Datum + ura v glavi
			// =========================
			function setHeaderNow() {
				try {
					var d = nowLjubljana();

					if ( headerDate ) {
						var formattedDate = new Intl.DateTimeFormat( 'sl-SI', {
							weekday: 'long',
							day: 'numeric',
							month: 'long',
							year: 'numeric'
						} ).format( d );
						headerDate.textContent = formattedDate.toLowerCase();
					}

					if ( headerClock ) {
						headerClock.textContent = d.toLocaleTimeString( 'sl-SI', { hour12: false } );
					}
				} catch ( e ) {
					if ( headerClock ) {
						headerClock.textContent = ( new Date() ).toLocaleTimeString();
					}
				}
			}

			setHeaderNow();
			setInterval( setHeaderNow, 1000 );

			// =========================
			// Filtriranje dogodkov:
			// - današnji: skrije tiste, ki so se že končali
			// - prihodnji: vedno prikaže (vikend / +3 logika ostane)
			// =========================
			function clientFilter( rawItems ) {
				var now   = nowLjubljana();

				var todayYear  = now.getFullYear();
				var todayMonth = String( now.getMonth() + 1 ).padStart( 2, '0' );
				var todayDay   = String( now.getDate() ).padStart( 2, '0' );
				var todayKey   = todayYear + '-' + todayMonth + '-' + todayDay;

				var keep = rawItems.filter( function ( it ) {
					var startIso = it.start_iso || it.start || '';
					var endIso   = it.end_iso || it.end || '';

					var s = new Date( parseISOToMs( startIso ) );
					var e = new Date( parseISOToMs( endIso ) );

					if ( isNaN( s ) || isNaN( e ) ) {
						// Če ni veljavnega časa, ne tvegamo – prikažemo.
						return true;
					}

					var eventKey = getDayKey( startIso );
					if ( ! eventKey ) {
						return true;
					}

					// Današnji dogodki: skrij samo tiste, ki so se res že končali
					if ( eventKey === todayKey ) {
						return e >= now;
					}

					// Prihodnji dnevi (+ vikend način / +3) – ne filtriramo
					return true;
				} );

				keep.sort( function ( a, b ) {
					var as = parseISOToMs( a.start_iso || a.start || '' );
					var bs = parseISOToMs( b.start_iso || b.start || '' );
					if ( isNaN( as ) && isNaN( bs ) ) return 0;
					if ( isNaN( as ) ) return 1;
					if ( isNaN( bs ) ) return -1;
					return as - bs;
				} );

				return keep;
			}

			function renderPage() {
				if ( ! tbody ) {
					return;
				}

				tbody.innerHTML = '';

				if ( ! items.length ) {
					var trEmpty = document.createElement( 'tr' );
					var tdEmpty = document.createElement( 'td' );
					tdEmpty.colSpan = showProgramColumn ? 6 : 5;
					tdEmpty.style.textAlign = 'center';
					tdEmpty.style.padding = '20px';
					tdEmpty.textContent = 'Ni podatkov za izbrani dan.';
					trEmpty.appendChild( tdEmpty );
					tbody.appendChild( trEmpty );

					if ( bottomRowEl ) {
						bottomRowEl.classList.add( 'snipi__bottom-row--hidden' );
						bottomRowEl.innerHTML = '';
					}
					return;
				}

				totalPages = Math.max( 1, Math.ceil( items.length / rowsPerPage ) );
				if ( currentPage > totalPages ) {
					currentPage = 1;
				}

                                if ( paginationEl ) {
                                        paginationEl.textContent = 'stran ' + currentPage + '/' + totalPages;
                                }

                                var startIndex = ( currentPage - 1 ) * rowsPerPage;
                                var endIndex   = startIndex + rowsPerPage;
                                var pageItems  = items.slice( startIndex, endIndex );

                                tbody.innerHTML = '';

                                var todayKey = getDayKey( nowLjubljana().toISOString() );
                                var previousKey = null;

                                pageItems.forEach( function ( it, index ) {
                                        var tr = document.createElement( 'tr' );
                                        if ( index % 2 === 1 ) {
                                                tr.classList.add( 'snipi__row--alt' );
                                        }

                                        var timeText = formatTimeRange( it.start_iso || it.start || '', it.end_iso || it.end || '' ) || '';
                                        var tdTime   = document.createElement( 'td' );

                                        var dayKey   = it._dayKey || getDayKey( it.start_iso || it.start || '' );
                                        var showDayLabel = dayKey && dayKey !== todayKey && dayKey !== previousKey && it._dayLabel;

                                        if ( showDayLabel ) {
                                                var dayLabel = document.createElement( 'div' );
                                                dayLabel.className = 'snipi__day-label';
                                                dayLabel.textContent = it._dayLabel;
                                                tdTime.appendChild( dayLabel );
                                        }

                                        previousKey = dayKey;

					var timeWrapper = document.createElement('div');
					timeWrapper.className = 'snipi__time-wrapper';
					var timeSpan = document.createElement('span');
					timeSpan.className = 'snipi__time';
					timeSpan.textContent = timeText;

					timeWrapper.appendChild(timeSpan);

					/* LIVE INDICATOR: check if event is ongoing */
					var now = nowLjubljana();
					var startMs = parseISOToMs(it.start_iso);
					var endMs   = parseISOToMs(it.end_iso);

					if (startMs <= now.getTime() && endMs > now.getTime()) {

    					var liveImg = document.createElement('img');
						liveImg.className = 'snipi__live-indicator';
						liveImg.src = SNIPI_FRONT_REST.pluginUrl + 'assets/Live.svg';
						liveImg.alt = 'live';

						timeWrapper.appendChild(liveImg);
					}


					tdTime.appendChild(timeWrapper);

					var tdName    = document.createElement( 'td' );
					tdName.textContent = it.name || '';

					var tdProgram = null;
					if ( showProgramColumn ) {
						tdProgram = document.createElement( 'td' );
						tdProgram.textContent = resolveProgram( it );
					}

					var tdTeacher = document.createElement( 'td' );
					tdTeacher.textContent = it.teacher || '';

					var tdRoom = document.createElement( 'td' );
					tdRoom.textContent = it.room || '';

					var tdFloor = document.createElement( 'td' );
					tdFloor.textContent = it.floor || '';

					tr.appendChild( tdTime );
					tr.appendChild( tdName );
					if ( tdProgram ) {
						tr.appendChild( tdProgram );
					}
					tr.appendChild( tdTeacher );
					tr.appendChild( tdRoom );
					tr.appendChild( tdFloor );

					tbody.appendChild( tr );
				} );

				if ( bottomRowEl ) {
					bottomRowEl.classList.remove( 'snipi__bottom-row--hidden' );
				}
			}

function updateBottomRow( html, shouldDisplay ) {
if ( ! bottomRowEl ) {
return;
}

if ( shouldDisplay === false ) {
bottomRowEl.classList.add( 'snipi__bottom-row--hidden' );
bottomRowEl.innerHTML = '';
return;
}

if ( ! html ) {
bottomRowEl.classList.add( 'snipi__bottom-row--hidden' );
bottomRowEl.innerHTML = '';
return;
}

				bottomRowEl.classList.remove( 'snipi__bottom-row--hidden' );
				bottomRowEl.innerHTML = html;
			}

			function updateLogo( url ) {
				if ( ! logoEl ) {
					return;
				}

				if ( url ) {
					logoEl.setAttribute( 'src', url );
					logoEl.style.display = '';
				} else {
					logoEl.removeAttribute( 'src' );
					logoEl.style.display = 'none';
				}
			}

		function fetchData() {
			fetch( getEndpoint(), {
				credentials: 'same-origin'
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Network error: ' + response.status );
					}
					return response.json();
				} )
				.then( function ( payload ) {
					if ( ! payload || ! Array.isArray( payload.items ) ) {
						items = [];
						renderPage();
						return;
					}

					var rawItems = payload.items || [];
					var filtered = clientFilter( rawItems );
					items        = filtered;

					// Added: enrich events with day metadata so future days are visible across pagination.
					decorateDayMetadata( items );

var shouldDisplayBottom = payload.display_bottom === true || payload.display_bottom === '1';

if ( payload.bottom_row ) {
updateBottomRow( payload.bottom_row, shouldDisplayBottom );
} else if ( payload.bottom_row_html ) {
updateBottomRow( payload.bottom_row_html, shouldDisplayBottom );
} else {
updateBottomRow( '', shouldDisplayBottom );
}

					if ( typeof payload.logo_url === 'string' ) {
						updateLogo( payload.logo_url );
					}

					if ( typeof payload.show_program_column === 'boolean' ) {
						showProgramColumn = payload.show_program_column;
						syncHeaderColumns( showProgramColumn );
					} else if ( typeof payload.show_program === 'boolean' ) {
						showProgramColumn = payload.show_program;
						syncHeaderColumns( showProgramColumn );
					}

					currentPage = 1;
					renderPage();
					startAutoplay();
				} )
.catch( function () {
items = [];
renderPage();
updateBottomRow( '', false );
} );
		}

		function startAutoplay() {
			if ( autoplayTimer ) {
				clearInterval( autoplayTimer );
				autoplayTimer = null;
			}
			if ( totalPages <= 1 ) {
				return;
			}

			// Added: restart autoplay with fresh totals so all paginated days rotate.
			autoplayTimer = setInterval( function () {
				currentPage++;
				if ( currentPage > totalPages ) {
					currentPage = 1;
				}
				renderPage();
			}, autoplayInterval * 1000 );
		}

fetchData();

			fetchTimer = setInterval( fetchData, 60 * 1000 );

			window.addEventListener( 'beforeunload', function () {
				if ( fetchTimer ) {
					clearInterval( fetchTimer );
				}
				if ( autoplayTimer ) {
					clearInterval( autoplayTimer );
				}
			} );
		} );
	} );
})();
