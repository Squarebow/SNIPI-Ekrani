// front.js - REST-based frontend (vanilla JS)
// Hybrid filtering: server returns normalized ISO with Europe/Ljubljana offset.
// Client re-checks to hide only events, ki so zares že končani danes.

(function () {
	'use strict';

	// Če front-end podatki niso na voljo, se skripta ne izvaja.
	if ( typeof SNIPI_FRONT_REST === 'undefined' ) {
		return;
	}

	// Osnovne nastavitve z lokaliziranega objekta.
	var restRoot         = SNIPI_FRONT_REST.rest_root.replace( /\/$/, '' );
	var postId           = SNIPI_FRONT_REST.post_id;
	var rowsPerPage      = parseInt( SNIPI_FRONT_REST.rowsPerPage || 8, 10 );
	var autoplayInterval = parseInt( SNIPI_FRONT_REST.autoplayInterval || 10, 10 );
	var showProgramColumn = SNIPI_FRONT_REST.showProgramColumn === '1';

	// URL vtičnika, uporabljen za pot do Live.svg (nastavljen v PHP preko wp_localize_script).
	var pluginUrl        = ( typeof SNIPI_FRONT_REST.pluginUrl !== 'undefined' )
		? SNIPI_FRONT_REST.pluginUrl
		: '';

	// ------------------------------------------------------------
	// Parsanje ISO datuma v milisekunde
	// ------------------------------------------------------------
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

	// ------------------------------------------------------------
	// Trenutni čas v časovnem pasu Europe/Ljubljana
	// ------------------------------------------------------------
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

	// ------------------------------------------------------------
	// Formatiranje časovnega intervala (HH:MM - HH:MM)
	// ------------------------------------------------------------
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

	// ------------------------------------------------------------
	// REST endpoint za timeslots
	// ------------------------------------------------------------
	function getEndpoint() {
		return restRoot + '/snipi/v1/ekrani/timeslots?post_id=' + encodeURIComponent( postId );
	}

	// ------------------------------------------------------------
	// Izračun "ključ dneva" YYYY-MM-DD
	// ------------------------------------------------------------
	function getDayKey( isoString ) {
		var ms = parseISOToMs( isoString );
		if ( isNaN( ms ) ) return '';
		var d     = new Date( ms );
		var month = String( d.getMonth() + 1 ).padStart( 2, '0' );
		var day   = String( d.getDate() ).padStart( 2, '0' );
		var year  = d.getFullYear();
		return year + '-' + month + '-' + day;
	}

	// ------------------------------------------------------------
	// Sortiranje dogodkov po začetku
	// ------------------------------------------------------------
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

	// ------------------------------------------------------------
	// Resolucija imena programa (ker lahko pride iz različnih polj)
	// ------------------------------------------------------------
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

	// ------------------------------------------------------------
	// DOMContentLoaded: inicializacija vseh .snipi--shell
	// ------------------------------------------------------------
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

			// ------------------------------------------------------------
			// Posodobitev glave (datum + ura v živo)
			// ------------------------------------------------------------
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

			// ------------------------------------------------------------
			// Filtriranje dogodkov na strani klienta:
			// - današnji: skrije le že končane
			// - prihodnji: pusti vse (vikend/+3 logika ostane na strežniku)
			// ------------------------------------------------------------
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
						// Če ni veljavnega časa, raje prikažemo kot skrijemo.
						return true;
					}

					var eventKey = getDayKey( startIso );
					if ( ! eventKey ) {
						return true;
					}

					// Današnji dogodki: skrij samo tiste, ki so se že končali
					if ( eventKey === todayKey ) {
						return e >= now;
					}

					// Prihodnji dnevi – vedno prikažemo
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

			// ------------------------------------------------------------
			// Glavni izris strani z upoštevanjem paginacije
			// ------------------------------------------------------------
			function renderPage() {
				if ( ! tbody ) {
					return;
				}

				tbody.innerHTML = '';

				// Če ni dogodkov, prikažemo sporočilo + skrijemo spodnjo vrstico
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

				// --------------------------------------------------------
				// Izris posamezne vrstice
				// --------------------------------------------------------
				pageItems.forEach( function ( it, index ) {
					var tr = document.createElement( 'tr' );
					if ( index % 2 === 1 ) {
						tr.classList.add( 'snipi__row--alt' );
					}

					// ---------------- ČASOVNI STOLPEC + LIVE IKONA ----------------
					var tdTime   = document.createElement( 'td' );

					// Oznaka dneva (če je prisotna)
					if ( it._dayLabel ) {
						var dayLabel = document.createElement( 'div' );
						dayLabel.className = 'snipi__day-label';
						dayLabel.textContent = it._dayLabel;
						tdTime.appendChild( dayLabel );
					}

					// Formatiran čas dogodka
					var formattedTime = formatTimeRange(
						it.start_iso || it.start || '',
						it.end_iso || it.end || ''
					) || '';

					// Wrapper za čas + live indikator (postavitev, ne velikost)
					var timeWrapper = document.createElement( 'span' );
					timeWrapper.classList.add( 'snipi-ekrani-time-wrapper' );

					// Časovni tekst
					var timeSpan = document.createElement( 'span' );
					timeSpan.className = 'snipi__time';
					timeSpan.textContent = formattedTime;
					timeWrapper.appendChild( timeSpan );

					// Live indikator: preverimo, ali je dogodek trenutno v teku
					var now     = nowLjubljana();
					var startMs = parseISOToMs( it.start_iso || it.start || '' );
					var endMs   = parseISOToMs( it.end_iso || it.end || '' );

					if (
						! isNaN( startMs ) &&
						! isNaN( endMs ) &&
						startMs <= now.getTime() &&
						endMs > now.getTime()
					) {
						var liveImg = document.createElement( 'img' );
						liveImg.className = 'snipi-live-indicator';
						// Pot do Live.svg – dimenzije določa SVG sam, CSS ureja le poravnavo
						liveImg.src = pluginUrl + 'assets/Live.svg';
						liveImg.alt = 'V živo';
						timeWrapper.appendChild( liveImg );
					}

					tdTime.appendChild( timeWrapper );

					// ---------------- OSTALI STOLPCI ----------------
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

			// ------------------------------------------------------------
			// Spodnja vrstica (custom HTML iz admina)
			// ------------------------------------------------------------
			function updateBottomRow( html ) {
				if ( ! bottomRowEl ) {
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

			// ------------------------------------------------------------
			// Posodabljanje logotipa v glavi
			// ------------------------------------------------------------
			function updateLogo( url ) {
				if ( ! logoEl ) {
					return;
				}

				if ( url ) {
					logoEl.setAttribute( 'src', url );
					lo
goEl.style.display = '';
				} else {
					logoEl.removeAttribute( 'src' );
					logoEl.style.display = 'none';
				}
			}

			// ------------------------------------------------------------
			// Fetch podatkov iz REST API in render
			// ------------------------------------------------------------
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

						if ( payload.bottom_row ) {
							updateBottomRow( payload.bottom_row );
						} else if ( payload.bottom_row_html ) {
							updateBottomRow( payload.bottom_row_html );
						} else {
							updateBottomRow( '' );
						}

						if ( typeof payload.logo_url === 'string' ) {
							updateLogo( payload.logo_url );
						}

						if ( typeof payload.show_program_column === 'boolean' ) {
							showProgramColumn = payload.show_program_column;
						} else if ( typeof payload.show_program === 'boolean' ) {
							showProgramColumn = payload.show_program;
						}

						currentPage = 1;
						renderPage();
					} )
					.catch( function () {
						items = [];
						renderPage();
						updateBottomRow( '' );
					} );
			}

			// ------------------------------------------------------------
			// Avtomatsko preklapljanje strani (avtoplay)
			// ------------------------------------------------------------
			function startAutoplay() {
				if ( autoplayTimer ) {
					clearInterval( autoplayTimer );
				}
				if ( totalPages <= 1 ) {
					return;
				}
				autoplayTimer = setInterval( function () {
					currentPage++;
					if ( currentPage > totalPages ) {
						currentPage = 1;
					}
					renderPage();
				}, autoplayInterval * 1000 );
			}

			// Prvi fetch + zagon avtoplay
			fetchData();
			startAutoplay();

			// Redni refetch na 60 s
			fetchTimer = setInterval( fetchData, 60 * 1000 );

			// Počistimo timerje ob zapiranju strani
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
