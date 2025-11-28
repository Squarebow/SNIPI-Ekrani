// ================================================================
// SNIPI EKRANI — FRONTEND JS (REST-based)
// ZADNJA POPRAVLJENA VERZIJA Z LIVE SVG + POPRAVLJENO ČASOVNO POLJE
// ================================================================

(function () {
	'use strict';

	// ================================================================
	// 0. PREVERI, ČE SO FRONT-END PODATKI NA VOLJO
	// ================================================================
	if (typeof SNIPI_FRONT_REST === 'undefined') {
		return;
	}

	var restRoot         = SNIPI_FRONT_REST.rest_root.replace(/\/$/, '');
	var postId           = SNIPI_FRONT_REST.post_id;
	var rowsPerPage      = parseInt(SNIPI_FRONT_REST.rowsPerPage || 8, 10);
	var autoplayInterval = parseInt(SNIPI_FRONT_REST.autoplayInterval || 10, 10);
	var showProgramColumn = SNIPI_FRONT_REST.showProgramColumn === '1';

	// ================================================================
	// 1. FUNKCIJE ZA DATUME, PARSING IN FORMATIRANJE ČASA
	// ================================================================
	function parseISOToMs(isoString) {
		if (!isoString) return NaN;
		var d = new Date(isoString);
		if (!isNaN(d.getTime())) return d.getTime();

		var normalized = isoString.replace(' ', 'T');
		if (!/Z$/i.test(normalized) && !/[+-]\d{2}:\d{2}$/.test(normalized)) {
			normalized += 'Z';
		}
		var d2 = new Date(normalized);
		return d2.getTime();
	}

	function nowLjubljana() {
		var now = new Date();
		var formatter = new Intl.DateTimeFormat('en-US', {
			timeZone: 'Europe/Ljubljana',
			year: 'numeric',
			month: '2-digit',
			day: '2-digit',
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hour12: false
		});

		var parts = formatter.formatToParts(now);
		var map = {};
		for (var i = 0; i < parts.length; i++) {
			map[parts[i].type] = parts[i].value;
		}

		return new Date(
			parseInt(map.year, 10),
			parseInt(map.month, 10) - 1,
			parseInt(map.day, 10),
			parseInt(map.hour, 10),
			parseInt(map.minute, 10),
			parseInt(map.second, 10)
		);
	}

	function formatTimeRange(startIso, endIso) {
		if (!startIso || !endIso) return '';

		var s = new Date(parseISOToMs(startIso));
		var e = new Date(parseISOToMs(endIso));
		if (isNaN(s) || isNaN(e)) return '';

		var sH = s.toLocaleTimeString('sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false });
		var eH = e.toLocaleTimeString('sl-SI', { hour: '2-digit', minute: '2-digit', hour12: false });

		return sH + ' - ' + eH;
	}

	// ================================================================
	// 2. REST ENDPOINTS
	// ================================================================
	function getEndpoint() {
		return restRoot + '/snipi/v1/ekrani/timeslots?post_id=' + encodeURIComponent(postId);
	}

	// ================================================================
	// 3. SORTIRANJE, GROUPANJE IN FILTRIRANJE DOGODKOV
	// ================================================================
	function getDayKey(isoString) {
		var ms = parseISOToMs(isoString);
		if (isNaN(ms)) return '';
		var d = new Date(ms);

		return (
			d.getFullYear() + '-' +
			String(d.getMonth() + 1).padStart(2, '0') + '-' +
			String(d.getDate()).padStart(2, '0')
		);
	}

	function clientFilter(rawItems) {
		var now = nowLjubljana();

		var todayYear  = now.getFullYear();
		var todayMonth = String(now.getMonth() + 1).padStart(2, '0');
		var todayDay   = String(now.getDate()).padStart(2, '0');
		var todayKey   = todayYear + '-' + todayMonth + '-' + todayDay;

		var keep = rawItems.filter(function (it) {
			var startIso = it.start_iso || it.start || '';
			var endIso   = it.end_iso || it.end || '';

			var s = new Date(parseISOToMs(startIso));
			var e = new Date(parseISOToMs(endIso));

			if (isNaN(s) || isNaN(e)) return true;

			var eventKey = getDayKey(startIso);
			if (!eventKey) return true;

			if (eventKey === todayKey) {
				return e >= now;
			}

			return true;
		});

		keep.sort(function (a, b) {
			var as = parseISOToMs(a.start_iso || a.start || '');
			var bs = parseISOToMs(b.start_iso || b.start || '');
			return as - bs;
		});

		return keep;
	}

	function resolveProgram(it) {
		return it.program
			|| it.program_display
			|| it.program_name
			|| it.programTitle
			|| it.project
			|| '';
	}

	// ================================================================
	// 4. RENDERER — IZRIS TABELE
	// ================================================================
	document.addEventListener('DOMContentLoaded', function () {
		var shells = document.querySelectorAll('.snipi--shell');
		if (!shells.length) return;

		shells.forEach(function (container) {

			var headerDate   = container.querySelector('.snipi__date');
			var headerClock  = container.querySelector('.snipi__clock-value');
			var table        = container.querySelector('.snipi__table');
			var tbody        = container.querySelector('tbody');
			var paginationEl = container.querySelector('.snipi__pagination');
			var bottomRowEl  = container.querySelector('[data-snipi-bottom-row]');
			var logoEl       = container.querySelector('.snipi__logo');

			var items         = [];
			var currentPage   = 1;
			var totalPages    = 1;
			var autoplayTimer = null;
			var fetchTimer    = null;

			// ================================================================
			// 4A. POSODOBITEV GLAVE (datum + ura)
			// ================================================================
			function setHeaderNow() {
				try {
					var d = nowLjubljana();

					if (headerDate) {
						headerDate.textContent = new Intl.DateTimeFormat('sl-SI', {
							weekday: 'long',
							day: 'numeric',
							month: 'long',
							year: 'numeric'
						}).format(d).toLowerCase();
					}

					if (headerClock) {
						headerClock.textContent = d.toLocaleTimeString('sl-SI', { hour12: false });
					}

				} catch (e) {
					if (headerClock) {
						headerClock.textContent = (new Date()).toLocaleTimeString();
					}
				}
			}

			setHeaderNow();
			setInterval(setHeaderNow, 1000);

			// ================================================================
			// 4B. GLAVNI IZRIS STRANI
			// ================================================================
			function renderPage() {

				if (!tbody) return;

				tbody.innerHTML = '';

				// Če ni podatkov
				if (!items.length) {
					var trEmpty = document.createElement('tr');
					var tdEmpty = document.createElement('td');
					tdEmpty.colSpan = showProgramColumn ? 6 : 5;
					tdEmpty.style.textAlign = 'center';
					tdEmpty.style.padding = '20px';
					tdEmpty.textContent = 'Ni podatkov za izbrani dan.';
					trEmpty.appendChild(tdEmpty);
					tbody.appendChild(trEmpty);

					if (bottomRowEl) {
						bottomRowEl.classList.add('snipi__bottom-row--hidden');
						bottomRowEl.innerHTML = '';
					}
					return;
				}

				totalPages = Math.max(1, Math.ceil(items.length / rowsPerPage));
				if (currentPage > totalPages) currentPage = 1;

				if (paginationEl) {
					paginationEl.textContent = 'stran ' + currentPage + '/' + totalPages;
				}

				var startIndex = (currentPage - 1) * rowsPerPage;
				var endIndex   = startIndex + rowsPerPage;
				var pageItems  = items.slice(startIndex, endIndex);


				// ================================================================
				// 4C. IZRIS POSAMEZNE VRSTICE
				// ================================================================
				pageItems.forEach(function (it, index) {

					var tr = document.createElement('tr');
					if (index % 2 === 1) tr.classList.add('snipi__row--alt');

					// ---------------- TIME CELL (POPOLNOMA PRENOVLJENO) ----------------
					var tdTime = document.createElement('td');

					// DAY LABEL (če je prisoten)
					if (it._dayLabel) {
						var dayLabel = document.createElement('div');
						dayLabel.className = 'snipi__day-label';
						dayLabel.textContent = it._dayLabel;
						tdTime.appendChild(dayLabel);
					}

					// Formatiran čas
					var formattedTime = formatTimeRange(it.start_iso || it.start || '', it.end_iso || it.end || '');

					var timeWrapper = document.createElement('span');
					timeWrapper.classList.add('snipi-ekrani-time-wrapper');

					var timeTextEl = document.createElement('span');
					timeTextEl.textContent = formattedTime;
					timeWrapper.appendChild(timeTextEl);

					// LIVE indikator
					var now = nowLjubljana();
					var startMs = parseISOToMs(it.start_iso);
					var endMs   = parseISOToMs(it.end_iso);

					if (startMs <= now.getTime() && endMs > now.getTime()) {
						var liveIcon = document.createElement('img');
						liveIcon.className = 'snipi-live-indicator';
						liveIcon.src = snipiEkraniData.plugin_url + 'assets/icons/Live.svg';
						liveIcon.alt = 'V živo';
						timeWrapper.appendChild(liveIcon);
					}

					tdTime.appendChild(timeWrapper);


					// ---------------- DRUGI STOLPCI ----------------
					var tdName    = document.createElement('td');
					tdName.textContent = it.name || '';

					var tdProgram = null;
					if (showProgramColumn) {
						tdProgram = document.createElement('td');
						tdProgram.textContent = resolveProgram(it);
					}

					var tdTeacher = document.createElement('td');
					tdTeacher.textContent = it.teacher || '';

					var tdRoom = document.createElement('td');
					tdRoom.textContent = it.room || '';

					var tdFloor = document.createElement('td');
					tdFloor.textContent = it.floor || '';


					// ---------------- APPEND TO ROW ----------------
					tr.appendChild(tdTime);
					tr.appendChild(tdName);
					if (tdProgram) tr.appendChild(tdProgram);
					tr.appendChild(tdTeacher);
					tr.appendChild(tdRoom);
					tr.appendChild(tdFloor);

					tbody.appendChild(tr);
				});

				if (bottomRowEl) {
					bottomRowEl.classList.remove('snipi__bottom-row--hidden');
				}
			}

			// ================================================================
			// 5. Posodobi spodnjo vrstico
			// ================================================================
			function updateBottomRow(html) {
				if (!bottomRowEl) return;

				if (!html) {
					bottomRowEl.classList.add('snipi__bottom-row--hidden');
					bottomRowEl.innerHTML = '';
					return;
				}

				bottomRowEl.classList.remove('snipi__bottom-row--hidden');
				bottomRowEl.innerHTML = html;
			}

			// ================================================================
			// 6. Posodobitev logotipa
			// ================================================================
			function updateLogo(url) {
				if (!logoEl) return;

				if (url) {
					logoEl.setAttribute('src', url);
					logoEl.style.display = '';
				} else {
					logoEl.removeAttribute('src');
					logoEl.style.display = 'none';
				}
			}

			// ================================================================
			// 7. FETCH LOGIKA — KLIC REST APIJA IN POSODOBITEV TABELE
			// ================================================================
			function fetchData() {
				fetch(getEndpoint(), { credentials: 'same-origin' })
					.then(function (response) {
						if (!response.ok) throw new Error('Network error: ' + response.status);
						return response.json();
					})
					.then(function (payload) {

						if (!payload || !Array.isArray(payload.items)) {
							items = [];
							renderPage();
							return;
						}

						var rawItems = payload.items || [];
						items = clientFilter(rawItems);

						if (payload.bottom_row) {
							updateBottomRow(payload.bottom_row);
						} else if (payload.bottom_row_html) {
							updateBottomRow(payload.bottom_row_html);
						} else {
							updateBottomRow('');
						}

						if (typeof payload.logo_url === 'string') {
							updateLogo(payload.logo_url);
						}

						// Program column toggle
						if (typeof payload.show_program_column === 'boolean') {
							showProgramColumn = payload.show_program_column;
						}

						currentPage = 1;
						renderPage();
					})
					.catch(function () {
						items = [];
						renderPage();
						updateBottomRow('');
					});
			}

			// ================================================================
			// 8. Samodejno preklapljanje strani (avtoplay)
			// ================================================================
			function startAutoplay() {
				if (autoplayTimer) clearInterval(autoplayTimer);

				if (totalPages <= 1) return;

				autoplayTimer = setInterval(function () {
					currentPage++;
					if (currentPage > totalPages) currentPage = 1;
					renderPage();
				}, autoplayInterval * 1000);
			}

			// ================================================================
			// 9. ZAGON SISTEMA
			// ================================================================
			fetchData();
			startAutoplay();
			fetchTimer = setInterval(fetchData, 60 * 1000);

			// Ob zapiranju strani počisti timerje
			window.addEventListener('beforeunload', function () {
				if (fetchTimer) clearInterval(fetchTimer);
				if (autoplayTimer) clearInterval(autoplayTimer);
			});
		});
	});
})();
