// front.js - REST-based frontend (vanilla JS)
// Hybrid filtering: server returns normalized ISO with Europe/Ljubljana offset.
// Client re-checks to hide only events, ki so zares že končani danes.

(function () {
	'use strict';

	if ( typeof SNIPI_FRONT_REST === 'undefined' ) { return; }

	var restRoot         = SNIPI_FRONT_REST.rest_root.replace( /\/$/, '' );
	var postId           = SNIPI_FRONT_REST.post_id;
	var rowsPerPage      = parseInt( SNIPI_FRONT_REST.rowsPerPage || 8, 10 );
	var autoplayInterval = parseInt( SNIPI_FRONT_REST.autoplayInterval || 10, 10 );
	var showProgramColumn = SNIPI_FRONT_REST.showProgramColumn === '1' || SNIPI_FRONT_REST.showProgramColumn === true;

	// Skaliranje pisave: 'fill' = zapolni zaslon | 'free' = privzeta pisava
	var rowScaleMode      = SNIPI_FRONT_REST.rowScaleMode || 'fill';

	// Višina spodnje vrstice
	var footerHeightMode  = SNIPI_FRONT_REST.footerHeightMode || 'auto';
	var footerFixedHeight = parseInt( SNIPI_FRONT_REST.footerFixedHeight || 80, 10 );

	/* ══════════════════════════════════════════════════════════
	   ČASI IN TIMEZONE
	   ══════════════════════════════════════════════════════════ */

	function parseISOToMs( isoString ) {
		if ( ! isoString ) return NaN;
		var d = new Date( isoString );
		if ( ! isNaN( d.getTime() ) ) { return d.getTime(); }
		var normalized = isoString.replace( ' ', 'T' );
		if ( ! /Z$/i.test( normalized ) && ! /[+-]\d{2}:\d{2}$/.test( normalized ) ) {
			normalized += 'Z';
		}
		return new Date( normalized ).getTime();
	}

	function nowLjubljana() {
		var now       = new Date();
		var formatter = new Intl.DateTimeFormat( 'en-US', {
			timeZone: 'Europe/Ljubljana',
			year: 'numeric', month: '2-digit', day: '2-digit',
			hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
		} );
		var parts = formatter.formatToParts( now );
		var map   = {};
		for ( var i = 0; i < parts.length; i++ ) { map[ parts[i].type ] = parts[i].value; }
		return new Date(
			parseInt( map.year, 10 ), parseInt( map.month, 10 ) - 1, parseInt( map.day, 10 ),
			parseInt( map.hour, 10 ), parseInt( map.minute, 10 ), parseInt( map.second, 10 )
		);
	}

	function formatTimeRange( startIso, endIso ) {
		if ( ! startIso || ! endIso ) { return ''; }
		var s = new Date( parseISOToMs( startIso ) );
		var e = new Date( parseISOToMs( endIso ) );
		if ( isNaN( s ) || isNaN( e ) ) { return ''; }
		return s.toLocaleTimeString( 'sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false } )
			+ ' - '
			+ e.toLocaleTimeString( 'sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false } );
	}

	function getEndpoint() {
		return restRoot + '/snipi/v1/ekrani/timeslots?post_id=' + encodeURIComponent( postId ) + '&_cb=' + Date.now();
	}

	function getDayKey( isoString ) {
		var ms = parseISOToMs( isoString );
		if ( isNaN( ms ) ) { return ''; }
		var d = new Date( ms );
		return d.getFullYear() + '-' + String( d.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( d.getDate() ).padStart( 2, '0' );
	}

	function formatDayLabel( isoString ) {
		var ms = parseISOToMs( isoString );
		if ( isNaN( ms ) ) { return ''; }
		return new Date( ms ).toLocaleDateString( 'sl-SI', { weekday: 'long', day: 'numeric', month: 'long' } );
	}

	function decorateDayMetadata( orderedItems ) {
		var todayKey = getDayKey( nowLjubljana().toISOString() );
		orderedItems.forEach( function ( item ) {
			var startIso  = item.start_iso || item.start || '';
			var dayKey    = getDayKey( startIso );
			item._dayKey  = dayKey;
			item._dayLabel = ( dayKey && dayKey !== todayKey ) ? formatDayLabel( startIso ) : '';
		} );
	}

	function resolveProgram( it ) {
		return it.program || it.program_display || it.program_name || it.programTitle || it.project || '';
	}

	/* ══════════════════════════════════════════════════════════
	   GLAVNA LOGIKA (per-shell)
	   ══════════════════════════════════════════════════════════ */

	document.addEventListener( 'DOMContentLoaded', function () {
		var shells = document.querySelectorAll( '.snipi--shell' );
		if ( ! shells.length ) { return; }

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
			var resizeTimer   = null;

			/* ── Program stolpec ────────────────────────── */

			function setProgramLayoutClass( show ) {
				container.classList.toggle( 'snipi--program-hidden',  ! show );
				container.classList.toggle( 'snipi--program-visible', !! show );
			}

			function syncHeaderColumns( show ) {
				if ( ! table ) { return; }
				var headRow  = table.querySelector( 'thead tr' );
				if ( ! headRow ) { return; }
				var programTh = headRow.querySelector( '[data-snipi-program]' );
				if ( show && ! programTh ) {
					var newTh = document.createElement( 'th' );
					newTh.setAttribute( 'data-snipi-col', 'program' );
					newTh.setAttribute( 'data-snipi-program', '1' );
					newTh.textContent = 'PROGRAM';
					var teacherTh = headRow.querySelector( '[data-snipi-col="teacher"]' );
					headRow.insertBefore( newTh, teacherTh || null );
				} else if ( ! show && programTh ) {
					headRow.removeChild( programTh );
				}
			}

			syncHeaderColumns( showProgramColumn );
			setProgramLayoutClass( showProgramColumn );

			/* ── Datum + ura ────────────────────────────── */

			function setHeaderNow() {
				try {
					var d = nowLjubljana();
					if ( headerDate ) {
						headerDate.textContent = new Intl.DateTimeFormat( 'sl-SI', {
							weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
						} ).format( d ).toLowerCase();
					}
					if ( headerClock ) {
						headerClock.textContent = d.toLocaleTimeString( 'sl-SI', { hour12: false } );
					}
				} catch ( e ) {
					if ( headerClock ) { headerClock.textContent = ( new Date() ).toLocaleTimeString(); }
				}
			}

			setHeaderNow();
			setInterval( setHeaderNow, 1000 );

			/* ── Filtriranje ────────────────────────────── */

			function clientFilter( rawItems ) {
				var now = nowLjubljana();
				var todayYear  = now.getFullYear();
				var todayMonth = String( now.getMonth() + 1 ).padStart( 2, '0' );
				var todayDay   = String( now.getDate() ).padStart( 2, '0' );
				var todayKey   = todayYear + '-' + todayMonth + '-' + todayDay;

				var keep = rawItems.filter( function ( it ) {
					var startIso = it.start_iso || it.start || '';
					var endIso   = it.end_iso   || it.end   || '';
					var s = new Date( parseISOToMs( startIso ) );
					var e = new Date( parseISOToMs( endIso ) );
					if ( isNaN( s ) || isNaN( e ) ) { return true; }
					var eventKey = getDayKey( startIso );
					if ( ! eventKey ) { return true; }
					if ( eventKey === todayKey ) { return e >= now; }
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

			/* ── Višina prostora za tabelo ──────────────── */

			/**
			 * Vrne višino spodnje vrstice v pikslih.
			 * Upošteva footer_height_mode: 'fixed' ali 'auto'.
			 */
			function getFooterHeight() {
				if ( ! bottomRowEl || bottomRowEl.classList.contains( 'snipi__bottom-row--hidden' ) ) {
					return 0;
				}
				if ( footerHeightMode === 'fixed' ) {
					return footerFixedHeight;
				}
				// auto: izmeri dejansko višino
				return bottomRowEl.getBoundingClientRect().height;
			}

			/**
			 * Izmeri višino ene vrstice (z vzorčno vrstico).
			 */
			function measureRowHeight() {
				if ( ! tbody || ! table ) { return 0; }
				var sample  = document.createElement( 'tr' );
				sample.style.visibility    = 'hidden';
				sample.style.position      = 'absolute';
				sample.style.pointerEvents = 'none';

				var cols = [ 'time', 'name', 'teacher', 'room', 'floor' ];
				if ( showProgramColumn ) { cols.splice( 2, 0, 'program' ); }
				cols.forEach( function ( col ) {
					var td = document.createElement( 'td' );
					td.setAttribute( 'data-snipi-col', col );
					td.textContent = col === 'time' ? '00:00 - 00:00' : 'Vrednost';
					sample.appendChild( td );
				} );

				tbody.appendChild( sample );
				var h = sample.getBoundingClientRect().height;
				tbody.removeChild( sample );
				return h;
			}

			/**
			 * Izračuna razpoložljivo višino za tbody.
			 *
			 * @return {number} Višina v pikslih.
			 */
			function getAvailableHeight() {
				var tbodyTop     = tbody.getBoundingClientRect().top;
				var footerH      = getFooterHeight();
				var viewportH    = window.innerHeight;

				var wrapper       = container.querySelector( '.snipi__table-wrapper' );
				var paddingBottom = 0;
				if ( wrapper ) {
					paddingBottom = parseFloat( window.getComputedStyle( wrapper ).paddingBottom ) || 0;
				}

				// Spodnja meja: vrh footerja (ali viewport bottom brez footerja)
				var bottomBoundary;
				if ( footerHeightMode === 'fixed' ) {
					// Rezerviramo fiksno višino
					bottomBoundary = viewportH - footerH;
				} else if ( bottomRowEl && ! bottomRowEl.classList.contains( 'snipi__bottom-row--hidden' ) ) {
					// auto: izmerjemo dejansko pozicijo vrha footerja
					bottomBoundary = bottomRowEl.getBoundingClientRect().top;
				} else {
					bottomBoundary = viewportH;
				}

				return bottomBoundary - tbodyTop - paddingBottom;
			}

			/**
			 * Izračuna efektivno število vrstic na stran.
			 *
			 * V 'fill' načinu UPOŠTEVA rowsPerPage kot cilj in skalira pisavo.
			 * V 'free' načinu ugotovi, koliko vrstic se fizično ujame.
			 *
			 * @return {number}
			 */
			function calculateRowsPerPage() {
				var rowHeight = measureRowHeight();
				if ( ! rowHeight || ! isFinite( rowHeight ) ) {
					return Math.max( 1, rowsPerPage );
				}

				var availH = getAvailableHeight();
				if ( ! isFinite( availH ) || availH <= 0 ) {
					return Math.max( 1, rowsPerPage );
				}

				if ( rowScaleMode === 'fill' ) {
					// Cilj je rowsPerPage vrstic. Skaliramo pisavo da zapolnijo prostor.
					var targetRowH = availH / rowsPerPage;
					applyFontScale( rowHeight, targetRowH );
					// Po skaliranju vrnemo rowsPerPage (font se bo prilagodil)
					return Math.max( 1, rowsPerPage );
				}

				// 'free' način: koliko vrstic gre z obstoječo pisavo
				var maxRows = Math.floor( availH / rowHeight );
				if ( ! isFinite( maxRows ) || maxRows < 1 ) { maxRows = 1; }
				return Math.max( 1, Math.min( maxRows, items.length || rowsPerPage ) );
			}

			/**
			 * Aplicira font-scale CSS variable na container.
			 * Skalira font, line-height in celično višino sorazmerno.
			 *
			 * @param {number} currentRowH  Izmerjena višina vrstice pri trenutni pisavi.
			 * @param {number} targetRowH   Želena višina vrstice.
			 */
			function applyFontScale( currentRowH, targetRowH ) {
				if ( ! currentRowH || currentRowH <= 0 ) { return; }

				var ratio = targetRowH / currentRowH;

				// Omejimo na razumne meje: 50% – 250% privzete pisave
				ratio = Math.max( 0.5, Math.min( 2.5, ratio ) );

				// Bazna pisava tabele je ~0.95rem; 1rem = 16px ob privzetih nastavitvah
				// Nastavimo CSS spremenljivko – front.css jo bere
				container.style.setProperty( '--snipi-row-scale', ratio.toFixed( 4 ) );
			}

			/* ── Renderiranje strani ────────────────────── */

			function renderPage() {
				if ( ! tbody ) { return; }
				tbody.innerHTML = '';

				if ( ! items.length ) {
					var trEmpty = document.createElement( 'tr' );
					var tdEmpty = document.createElement( 'td' );
					tdEmpty.colSpan    = showProgramColumn ? 6 : 5;
					tdEmpty.style.textAlign = 'center';
					tdEmpty.style.padding   = '20px';
					tdEmpty.textContent     = 'Ni podatkov za izbrani dan.';
					trEmpty.appendChild( tdEmpty );
					tbody.appendChild( trEmpty );
					if ( bottomRowEl ) {
						bottomRowEl.classList.add( 'snipi__bottom-row--hidden' );
						bottomRowEl.innerHTML = '';
					}
					// Počisti font scale
					container.style.removeProperty( '--snipi-row-scale' );
					return;
				}

				// Izračunamo efektivno število vrstic (in ob fill modu nastavimo font scale)
				var effectiveRows = calculateRowsPerPage();

				// Ločimo maxRowsForPagination od efektivnih za prikaz:
				// V fill modu je effectiveRows = rowsPerPage (nastavitev), kar je pravilno za paginacijo.
				// V free modu effectiveRows = fizično število vrstic.
				totalPages  = Math.max( 1, Math.ceil( items.length / effectiveRows ) );
				if ( currentPage > totalPages ) { currentPage = 1; }

				if ( paginationEl ) {
					paginationEl.textContent = 'stran ' + currentPage + '/' + totalPages;
				}

				var startIndex = ( currentPage - 1 ) * effectiveRows;
				var pageItems  = items.slice( startIndex, startIndex + effectiveRows );

				var todayKey    = getDayKey( nowLjubljana().toISOString() );
				var previousKey = null;

				pageItems.forEach( function ( it, index ) {
					var tr = document.createElement( 'tr' );
					if ( index % 2 === 1 ) { tr.classList.add( 'snipi__row--alt' ); }

					var timeText = formatTimeRange( it.start_iso || it.start || '', it.end_iso || it.end || '' ) || '';

					/* TIME cell */
					var tdTime   = document.createElement( 'td' );
					tdTime.setAttribute( 'data-snipi-col', 'time' );

					var dayKey       = it._dayKey || getDayKey( it.start_iso || it.start || '' );
					var showDayLabel = dayKey && dayKey !== todayKey && dayKey !== previousKey && it._dayLabel;
					if ( showDayLabel ) {
						var dayLabel = document.createElement( 'div' );
						dayLabel.className   = 'snipi__day-label';
						dayLabel.textContent = it._dayLabel;
						tdTime.appendChild( dayLabel );
					}
					previousKey = dayKey;

					var timeWrapper = document.createElement( 'div' );
					timeWrapper.className = 'snipi__time-wrapper';
					var timeSpan = document.createElement( 'span' );
					timeSpan.className   = 'snipi__time';
					timeSpan.textContent = timeText;
					timeWrapper.appendChild( timeSpan );

					// Live indicator
					var now      = nowLjubljana();
					var startMs  = parseISOToMs( it.start_iso );
					var endMs    = parseISOToMs( it.end_iso );
					if ( startMs <= now.getTime() && endMs > now.getTime() ) {
						var liveImg = document.createElement( 'img' );
						liveImg.className = 'snipi__live-indicator';
						liveImg.src       = SNIPI_FRONT_REST.pluginUrl + 'assets/Live.svg';
						liveImg.alt       = 'live';
						timeWrapper.appendChild( liveImg );
					}

					tdTime.appendChild( timeWrapper );

					/* Ostale celice */
					var tdName = document.createElement( 'td' );
					tdName.setAttribute( 'data-snipi-col', 'name' );
					tdName.textContent = it.name || '';

					var tdProgram = null;
					if ( showProgramColumn ) {
						tdProgram = document.createElement( 'td' );
						tdProgram.setAttribute( 'data-snipi-col', 'program' );
						tdProgram.textContent = resolveProgram( it );
					}

					var tdTeacher = document.createElement( 'td' );
					tdTeacher.setAttribute( 'data-snipi-col', 'teacher' );
					tdTeacher.textContent = it.teacher || '';

					var tdRoom = document.createElement( 'td' );
					tdRoom.setAttribute( 'data-snipi-col', 'room' );
					tdRoom.textContent = it.room || '';

					var tdFloor = document.createElement( 'td' );
					tdFloor.setAttribute( 'data-snipi-col', 'floor' );
					tdFloor.textContent = it.floor || '';

					tr.appendChild( tdTime );
					tr.appendChild( tdName );
					if ( tdProgram ) { tr.appendChild( tdProgram ); }
					tr.appendChild( tdTeacher );
					tr.appendChild( tdRoom );
					tr.appendChild( tdFloor );
					tbody.appendChild( tr );
				} );

				if ( bottomRowEl ) {
					bottomRowEl.classList.remove( 'snipi__bottom-row--hidden' );
				}
			}

			/* ── Footer posodobitev ─────────────────────── */

			function updateBottomRow( html, shouldDisplay ) {
				if ( ! bottomRowEl ) { return; }
				if ( shouldDisplay === false || ! html ) {
					bottomRowEl.classList.add( 'snipi__bottom-row--hidden' );
					bottomRowEl.innerHTML = '';
					return;
				}
				bottomRowEl.classList.remove( 'snipi__bottom-row--hidden' );
				bottomRowEl.innerHTML = html;

				// Fiksna višina: nastavi CSS spremenljivko za table-wrapper padding
				syncFixedFooterReserve();
			}

			/**
			 * Ko je footer v fixed modu, nastavi padding-bottom na table-wrapper
			 * tako da tabela ne seže za footer.
			 */
			function syncFixedFooterReserve() {
				var wrapper = container.querySelector( '.snipi__table-wrapper' );
				if ( ! wrapper ) { return; }
				if ( footerHeightMode === 'fixed' ) {
					wrapper.style.paddingBottom = ( footerFixedHeight + 8 ) + 'px';
				} else {
					// auto: pustimo CSS default (60px iz front.css)
					wrapper.style.paddingBottom = '';
				}
			}

			function updateLogo( url ) {
				if ( ! logoEl ) { return; }
				if ( url ) {
					logoEl.setAttribute( 'src', url );
					logoEl.style.display = '';
				} else {
					logoEl.removeAttribute( 'src' );
					logoEl.style.display = 'none';
				}
			}

			/* ── ResizeObserver na footer ───────────────── */
			// V auto modu: ko se višina footerja spremeni, ponovno izračunamo vrstice.

			if ( bottomRowEl && typeof ResizeObserver !== 'undefined' ) {
				var footerResizeTimer = null;
				var footerObserver   = new ResizeObserver( function () {
					clearTimeout( footerResizeTimer );
					footerResizeTimer = setTimeout( function () {
						renderPage();
					}, 100 );
				} );
				footerObserver.observe( bottomRowEl );
			}

			/* ── Fetch podatkov ─────────────────────────── */

			function fetchData() {
				fetch( getEndpoint(), { credentials: 'same-origin' } )
					.then( function ( response ) {
						if ( ! response.ok ) { throw new Error( 'Network error: ' + response.status ); }
						return response.json();
					} )
					.then( function ( payload ) {
						if ( ! payload || ! Array.isArray( payload.items ) ) {
							items = [];
							renderPage();
							return;
						}

						if ( typeof payload.rows_per_page !== 'undefined' ) {
							var pr = parseInt( payload.rows_per_page, 10 );
							if ( ! isNaN( pr ) && pr > 0 ) { rowsPerPage = pr; }
						}

						if ( typeof payload.autoplay_interval !== 'undefined' ) {
							var pa = parseInt( payload.autoplay_interval, 10 );
							if ( ! isNaN( pa ) && pa > 0 ) { autoplayInterval = pa; }
						}

						var filtered = clientFilter( payload.items );
						items = filtered;
						decorateDayMetadata( items );

						var shouldDisplayBottom = payload.display_bottom === true || payload.display_bottom === '1';
						var bottomHTML = payload.bottom_row || payload.bottom_row_html || '';
						updateBottomRow( bottomHTML, shouldDisplayBottom );

						if ( typeof payload.logo_url === 'string' ) { updateLogo( payload.logo_url ); }

						if ( typeof payload.show_program_column === 'boolean' ) {
							showProgramColumn = payload.show_program_column;
							syncHeaderColumns( showProgramColumn );
							setProgramLayoutClass( showProgramColumn );
						} else if ( typeof payload.show_program === 'boolean' ) {
							showProgramColumn = payload.show_program;
							syncHeaderColumns( showProgramColumn );
							setProgramLayoutClass( showProgramColumn );
						}

						currentPage = 1;
						syncFixedFooterReserve();
						renderPage();
						startAutoplay();
					} )
					.catch( function () {
						items = [];
						renderPage();
						updateBottomRow( '', false );
					} );
			}

			/* ── Autoplay ───────────────────────────────── */

			function startAutoplay() {
				if ( autoplayTimer ) { clearInterval( autoplayTimer ); autoplayTimer = null; }
				if ( totalPages <= 1 ) { return; }
				autoplayTimer = setInterval( function () {
					currentPage++;
					if ( currentPage > totalPages ) { currentPage = 1; }
					renderPage();
				}, autoplayInterval * 1000 );
			}

			/* ── Resize event ───────────────────────────── */

			window.addEventListener( 'resize', function () {
				clearTimeout( resizeTimer );
				resizeTimer = setTimeout( function () { renderPage(); }, 150 );
			} );

			/* ── Start ──────────────────────────────────── */

			syncFixedFooterReserve();
			fetchData();
			fetchTimer = setInterval( fetchData, 60 * 1000 );

			window.addEventListener( 'beforeunload', function () {
				if ( fetchTimer )    { clearInterval( fetchTimer ); }
				if ( autoplayTimer ) { clearInterval( autoplayTimer ); }
			} );

		} ); // shells.forEach
	} ); // DOMContentLoaded
})();
