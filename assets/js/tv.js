/**
 * SNIPI Ekrani - TV Detection & Optimization
 * Version: 2.2.0
 */

(function() {
	'use strict';

	class SnipiTVDetector {
		constructor(config) {
			this.config = config;
			this.detection = null;
			this.init();
		}

		init() {
			const override = this.config.tvModeOverride || 'auto';
			
			if (override === 'tv') {
				this.applyTVMode();
				return;
			}
			
			if (override === 'desktop') {
				return;
			}

			if (!this.config.enableTVDetection) {
				return;
			}

			this.detection = this.detect();
			
			if (this.detection.isTV) {
				if (this.shouldShowConfirm()) {
					this.showConfirmDialog();
				} else {
					const savedMode = localStorage.getItem('snipi-tv-mode-' + this.config.screenId);
					if (savedMode === 'tv' || !savedMode) {
						this.applyTVMode();
					}
				}
			}
		}

		detect() {
			const ua = navigator.userAgent.toLowerCase();
			const width = window.screen.width;
			const height = window.screen.height;

			const tvKeywords = ['smart-tv', 'smarttv', 'googletv', 'webos', 'tizen', 
			                    'hbbtv', 'netcast', 'vidaa', 'viera', 'operatv'];
			const isTVUA = tvKeywords.some(k => ua.includes(k));

			const tvResolutions = [[1366, 768], [1920, 1080], [3840, 2160]];
			const isTVRes = tvResolutions.some(([w, h]) => width === w && height === h);

			return {
				isTV: isTVUA || isTVRes,
				confidence: isTVUA && isTVRes ? 'high' : isTVUA || isTVRes ? 'medium' : 'low',
				resolution: { width, height },
				settings: this.calculateSettings(width, height)
			};
		}

		calculateSettings(width, height) {
			let columns, fontSize, cardHeight;

			if (width >= 3840) {
				columns = 6;
				fontSize = 24;
			} else if (width >= 1920) {
				columns = 4;
				fontSize = 16;
			} else if (width >= 1366) {
				columns = 3;
				fontSize = 14;
			} else {
				columns = 2;
				fontSize = 16;
			}

			const availableHeight = height - 200;
			const rows = Math.ceil(12 / columns);
			cardHeight = Math.floor(availableHeight / rows) - 20;

			return { columns, fontSize, cardHeight: Math.max(cardHeight, 120) };
		}

		shouldShowConfirm() {
			if (!this.config.tvConfirmDialog) return false;
			const saved = localStorage.getItem('snipi-tv-mode-' + this.config.screenId);
			return !saved && this.detection.confidence !== 'high';
		}

		showConfirmDialog() {
			const dialog = document.createElement('div');
			dialog.className = 'snipi-tv-dialog';
			dialog.innerHTML = `
				<div class="snipi-tv-dialog-content">
					<h2>Zaznan TV ekran. Optimiziram prikaz za TV?</h2>
					<div class="snipi-tv-dialog-buttons">
						<button class="snipi-tv-btn snipi-tv-btn-primary" onclick="snipiTVAction('tv')">Da</button>
						<button class="snipi-tv-btn snipi-tv-btn-secondary" onclick="snipiTVAction('desktop')">Ne</button>
					</div>
				</div>
			`;
			document.body.appendChild(dialog);

			setTimeout(() => {
				dialog.style.opacity = '0';
				dialog.style.transition = 'opacity 0.5s';
				setTimeout(() => dialog.remove(), 500);
			}, 15000);
		}

		applyTVMode() {
			document.documentElement.classList.add('snipi-tv-mode');
			
			if (this.detection && this.detection.settings) {
				const s = this.detection.settings;
				document.documentElement.style.setProperty('--tv-columns', s.columns);
				document.documentElement.style.setProperty('--tv-font-size', s.fontSize + 'px');
				document.documentElement.style.setProperty('--tv-card-height', s.cardHeight + 'px');
			}
		}
	}

	window.snipiTVAction = function(mode) {
		const screenId = window.snipiTVConfig ? window.snipiTVConfig.screenId : '0';
		localStorage.setItem('snipi-tv-mode-' + screenId, mode);
		document.querySelector('.snipi-tv-dialog')?.remove();
		if (mode === 'tv') {
			location.reload();
		}
	};

	document.addEventListener('DOMContentLoaded', function() {
		if (window.snipiTVConfig) {
			new SnipiTVDetector(window.snipiTVConfig);
		}
	});

})();
