// front.js - REST-based frontend (vanilla JS)
// Hybrid filtering: server returns normalized ISO (with Europe/Ljubljana offset).
// Client re-checks to be extra-safe. Shows all events for the requested date (today)
// and hides only events whose end < now (client local time converted to Ljubljana).

(function () {
	'use strict';

	if ( typeof SNIPI_FRONT_REST === 'undefined' ) {
		return;
	}

	var restRoot = SNIPI_FRONT_REST.rest_root.replace( /\/$/, '' );
	var postId = SNIPI_FRONT_REST.post_id;
var rowsPerPage = parseInt( SNIPI_FRONT_REST.rowsPerPage || 8, 10 );
var autoplayInterval = parseInt( SNIPI_FRONT_REST.autoplayInterval || 10, 10 );
var noEventsMessage = SNIPI_FRONT_REST.noEventsMessage || 'Danes ni predvidenih izobraževanj.';
var showProgramColumn = !!SNIPI_FRONT_REST.showProgramColumn;

	function parseISOToMs( isoString ) {
		if ( ! isoString ) return NaN;
		var t = Date.parse( isoString );
		return isNaN( t ) ? NaN : t;
	}

	function nowLjubljana() {
		// Use Intl to get Ljubljana time based on current time
		var now = new Date();
		var str = now.toLocaleString( 'en-CA', { timeZone: 'Europe/Ljubljana' } );
		return new Date( str );
	}

	function formatTimeRange( startIso, endIso ) {
		try {
			var s = new Date( parseISOToMs( startIso ) );
			var e = new Date( parseISOToMs( endIso ) );
			if ( isNaN( s ) || isNaN( e ) ) return '';
			var sH = s.toLocaleTimeString( 'sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false } );
			var eH = e.toLocaleTimeString( 'sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false } );
			return sH + ' - ' + eH;
		} catch ( e ) {
			return '';
		}
	}

	function getEndpoint() {
		return restRoot + '/snipi/v1/ekrani/timeslots?post_id=' + encodeURIComponent( postId );
	}

	function getDayKey( isoString ) {
		var ms = parseISOToMs( isoString );
		if ( isNaN( ms ) ) return '';
		var d = new Date( ms );
		var month = String( d.getMonth() + 1 ).padStart( 2, '0' );
		var day = String( d.getDate() ).padStart( 2, '0' );
		return d.getFullYear() + '-' + month + '-' + day;
	}

	function formatDayLabel( isoString ) {
		try {
			var ms = parseISOToMs( isoString );
			if ( isNaN( ms ) ) return '';
			return new Intl.DateTimeFormat( 'sl-SI', {
				weekday: 'long',
				day: 'numeric',
				month: 'long',
				year: 'numeric'
			} ).format( new Date( ms ) );
		} catch ( e ) {
			return '';
		}
	}

document.addEventListener( 'DOMContentLoaded', function () {
var containers = document.querySelectorAll( '.snipi' );
if ( ! containers.length ) {
return;
}

			containers.forEach( function ( container ) {
				var headerDate = container.querySelector( '.snipi__date' );
				var headerClock = container.querySelector( '.snipi__clock' );
var table = container.querySelector( '.snipi__table' );
var tbody = container.querySelector( 'tbody' );
var paginationEl = container.querySelector( '.snipi__pagination' );
var bottomRowEl = container.querySelector( '[data-snipi-bottom-row]' );
var logoEl = container.querySelector( '.snipi__logo' );

			var items = [];
			var currentPage = 1;
			var totalPages = 1;
			var autoplayTimer = null;
			var fetchTimer = null;

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

function setHeaderNow() {
				try {
					var d = nowLjubljana();
					if ( headerDate ) {
						headerDate.textContent = new Intl.DateTimeFormat( 'sl-SI', {
							weekday: 'long',
							day: 'numeric',
							month: 'long',
							year: 'numeric'
						} ).format( d );
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

			function clientFilter( rawItems ) {
				var now = nowLjubljana();

				var keep = rawItems.filter( function ( it ) {
					var startIso = it.start_iso || it.start || '';
					var endIso = it.end_iso || it.end || '';
					var s = new Date( parseISOToMs( startIso ) );
					var e = new Date( parseISOToMs( endIso ) );

					if ( isNaN( s ) || isNaN( e ) ) {
						return true;
					}

					if ( e < now ) {
						return false;
					}

					return true;
				} );

				keep.sort( function ( a, b ) {
					var as = parseISOToMs( a.start_iso || a.start || '' );
					var bs = parseISOToMs( b.start_iso || b.start || '' );
					if ( isNaN( as ) && isNaN( bs ) ) return 0;
					if ( isNaN( as ) ) return 1;
					if ( isNaN( bs ) ) return -1;
					if ( as === bs ) {
						var ae = parseISOToMs( a.end_iso || a.end || '' );
						var be = parseISOToMs( b.end_iso || b.end || '' );
						return ( ae || 0 ) - ( be || 0 );
					}
					return as - bs;
				} );

				var dayKeys = keep.map( function ( it ) {
					return getDayKey( it.start_iso || it.start || '' );
				} ).filter( function ( key ) {
					return key !== '';
				} );
				var uniqueDayCount = Array.from( new Set( dayKeys ) ).length;
				var annotateDays = uniqueDayCount > 1;
				var lastDayKey = '';

				return keep.map( function ( it ) {
					var clone = Object.assign( {}, it );
					if ( annotateDays ) {
						var dayKey = getDayKey( clone.start_iso || clone.start || '' );
						if ( dayKey && dayKey !== lastDayKey ) {
							clone._dayLabel = formatDayLabel( clone.start_iso || clone.start || '' );
							lastDayKey = dayKey;
						} else {
							clone._dayLabel = '';
						}
					} else {
						clone._dayLabel = '';
					}
					return clone;
				} );
			}

			function updateBottomRow( shouldDisplay, html ) {
				if ( ! bottomRowEl ) {
					return;
				}

				if ( ! shouldDisplay || ! html ) {
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
					.then( function ( resp ) {
						if ( ! resp.ok ) {
							throw new Error( 'HTTP ' + resp.status );
						}
						return resp.json();
					} )
					.then( function ( data ) {
if ( data ) {
if ( Object.prototype.hasOwnProperty.call( data, 'show_program_column' ) ) {
showProgramColumn = !! data.show_program_column;
syncHeaderColumns( showProgramColumn );
}
updateBottomRow( data.display_bottom, data.bottom_row );
if ( Object.prototype.hasOwnProperty.call( data, 'logo_url' ) ) {
updateLogo( data.logo_url );
}
}

						if ( ! data || ! Array.isArray( data.items ) ) {
							renderNoEvents();
							return;
						}

						items = clientFilter( data.items );
						currentPage = 1;

						renderPage();
					} )
					.catch( function ( err ) {
						console.error( 'SNIPI REST error', err );
						renderNoEvents();
					} );
			}

			function renderPage() {
				if ( ! tbody ) {
					return;
				}

				totalPages = Math.max( 1, Math.ceil( items.length / rowsPerPage ) );
				if ( currentPage > totalPages ) {
					currentPage = 1;
				}

				if ( paginationEl ) {
					paginationEl.textContent = currentPage + '/' + totalPages;
				}

				var startIndex = ( currentPage - 1 ) * rowsPerPage;
				var endIndex = startIndex + rowsPerPage;
				var pageItems = items.slice( startIndex, endIndex );

				if ( ! pageItems.length ) {
					renderNoEvents();
					return;
				}

				renderRows( pageItems );
			}

function resolveProgram( item ) {
if ( item.program_display ) {
return item.program_display;
}
if ( item.project ) {
return item.project;
}
if ( item.studyName ) {
return item.studyName;
}
if ( Array.isArray( item.subjects ) ) {
for ( var i = 0; i < item.subjects.length; i++ ) {
if ( item.subjects[i] && item.subjects[i].studyName ) {
return item.subjects[i].studyName;
}
}
}
return '';
}

function renderRows( rows ) {
if ( ! tbody ) {
return;
}
				tbody.innerHTML = '';

rows.forEach( function ( it, index ) {
var tr = document.createElement( 'tr' );
if ( index % 2 === 1 ) {
tr.classList.add( 'snipi__row--alt' );
}

					var tdTime = document.createElement( 'td' );
					var timeText = formatTimeRange( it.start_iso || it.start, it.end_iso || it.end );
					if ( it._dayLabel ) {
						var dayLabel = document.createElement( 'div' );
						dayLabel.className = 'snipi__day-label';
						dayLabel.textContent = it._dayLabel;
						tdTime.appendChild( dayLabel );
					}
					var timeSpan = document.createElement( 'div' );
					timeSpan.className = 'snipi__time';
					timeSpan.textContent = timeText;
					tdTime.appendChild( timeSpan );

var tdName = document.createElement( 'td' );
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
if ( showProgramColumn && tdProgram ) {
tr.appendChild( tdProgram );
}
tr.appendChild( tdTeacher );
tr.appendChild( tdRoom );
tr.appendChild( tdFloor );

					tbody.appendChild( tr );
				} );
			}

function renderNoEvents() {
if ( ! tbody ) {
return;
}
tbody.innerHTML = '';
var tr = document.createElement( 'tr' );
var td = document.createElement( 'td' );
td.colSpan = showProgramColumn ? 6 : 5;
td.style.textAlign = 'center';
				td.style.padding = '20px';
				td.textContent = noEventsMessage;
				tr.appendChild( td );
				tbody.appendChild( tr );
			}

			function startAutoplay() {
				if ( autoplayTimer ) {
					clearInterval( autoplayTimer );
				}
				autoplayTimer = setInterval( function () {
					currentPage++;
					if ( currentPage > totalPages ) {
						currentPage = 1;
					}
					renderPage();
				}, autoplayInterval * 1000 );
			}

			fetchData();
			startAutoplay();

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
