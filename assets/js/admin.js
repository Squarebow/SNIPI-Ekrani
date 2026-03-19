(function () {
	'use strict';

	function qs( id ) {
		return document.getElementById( id );
	}

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ══════════════════════════════════════════════════════════
		   TAB NAVIGACIJA (legacy tab buttons – ohranimo za varnost)
		   ══════════════════════════════════════════════════════════ */

		var navLinks = document.querySelectorAll( '.snipi-tabs a' );
		navLinks.forEach( function ( link ) {
			if ( link.href.indexOf( 'admin.php?page=snipi' ) !== -1 ) {
				var parent = link.parentNode;
				if ( parent ) { parent.removeChild( link ); }
			}
		} );

		/* ══════════════════════════════════════════════════════════
		   KOPIRANJE KRATKE KODE
		   ══════════════════════════════════════════════════════════ */

		var copyBtn            = qs( 'snipi_copy_shortcode' );
		var copyBtnDefaultHtml = copyBtn ? copyBtn.innerHTML : '';

		function handleCopyAction( text, btn, restoreHtml ) {
			if ( ! btn ) { return; }
			var original = restoreHtml || btn.innerHTML;

			function setFeedback( msg ) {
				btn.textContent = msg;
				setTimeout( function () { btn.innerHTML = original; }, 1500 );
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text )
					.then( function () { setFeedback( 'Kopirano' ); } )
					.catch( function () {
						document.execCommand( 'copy', false, text );
						setFeedback( 'Kopirano' );
					} );
				return;
			}
			document.execCommand( 'copy', false, text );
			setFeedback( 'Kopirano' );
		}

		if ( copyBtn ) {
			copyBtn.addEventListener( 'click', function () {
				var field = qs( 'snipi_shortcode_field' );
				if ( field ) {
					handleCopyAction( field.value || '', copyBtn, copyBtnDefaultHtml );
				}
			} );
		}

		// Kopiraj v listi
		var listCopyButtons = document.querySelectorAll( '.snipi-copy-list' );
		if ( listCopyButtons.length ) {
			listCopyButtons.forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					handleCopyAction( btn.getAttribute( 'data-snipi-copy' ) || '', btn );
				} );
			} );
		}

		/* ══════════════════════════════════════════════════════════
		   LOGO UPLOAD + HEIGHT SLIDER
		   ══════════════════════════════════════════════════════════ */

		var logoUploadBtn  = qs( 'snipi_logo_upload' );
		var logoRemoveBtn  = qs( 'snipi_logo_remove' );
		var logoInput      = qs( 'snipi_logo_id' );
		var logoPreview    = qs( 'snipi_logo_preview' );
		var logoHeightIn   = qs( 'snipi_logo_height' );
		var logoHeightVal  = qs( 'snipi_logo_height_value' );
		var mediaFrame     = null;

		function updateLogoPreviewHeight( overrideHeight ) {
			if ( ! logoPreview ) { return; }
			var img = logoPreview.querySelector( 'img' );
			if ( ! img ) { return; }
			var h = overrideHeight || ( logoHeightIn ? parseInt( logoHeightIn.value, 10 ) : null );
			if ( h ) {
				img.style.height    = h + 'px';
				img.style.maxHeight = h + 'px';
				img.style.width     = 'auto';
			}
		}

		if ( logoUploadBtn && window.wp && wp.media ) {
			logoUploadBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( mediaFrame ) { mediaFrame.open(); return; }
				mediaFrame = wp.media( { title: 'Izberi logo', button: { text: 'Uporabi logo' }, multiple: false } );
				mediaFrame.on( 'select', function () {
					var att    = mediaFrame.state().get( 'selection' ).first().toJSON();
					var hVal   = logoHeightIn ? logoHeightIn.value : 60;
					if ( logoInput )   { logoInput.value = att.id; }
					if ( logoPreview ) {
						logoPreview.innerHTML = '<img src="' + att.url + '" style="height:' + hVal + 'px;width:auto;" />';
						updateLogoPreviewHeight();
					}
				} );
				mediaFrame.open();
			} );
		}

		if ( logoRemoveBtn ) {
			logoRemoveBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( logoInput )   { logoInput.value = ''; }
				if ( logoPreview ) { logoPreview.innerHTML = ''; }
			} );
		}

		if ( logoHeightIn && logoHeightVal ) {
			logoHeightIn.addEventListener( 'input', function () {
				logoHeightVal.textContent = logoHeightIn.value + 'px';
				updateLogoPreviewHeight();
			} );
		}

		updateLogoPreviewHeight();

		/* ══════════════════════════════════════════════════════════
		   SPODNJA VRSTICA – toggle vidnosti editorja
		   ══════════════════════════════════════════════════════════ */

		var bottomToggle = qs( 'snipi_display_bottom' );
		var bottomEditor = document.querySelector( '[data-snipi-bottom-editor]' );

		function syncBottomEditorVisibility() {
			if ( ! bottomEditor ) { return; }
			if ( bottomToggle && bottomToggle.checked ) {
				bottomEditor.classList.remove( 'snipi-bottom-editor--hidden' );
			} else {
				bottomEditor.classList.add( 'snipi-bottom-editor--hidden' );
			}
		}

		if ( bottomToggle ) {
			bottomToggle.addEventListener( 'change', syncBottomEditorVisibility );
		}
		syncBottomEditorVisibility();

		/* ══════════════════════════════════════════════════════════
		   SPODNJA VRSTICA – višina: prikaz/skrivanje sliderja
		   ══════════════════════════════════════════════════════════ */

		var footerFixedControl = qs( 'snipi_footer_fixed_control' );
		var footerHeightRadios = document.querySelectorAll( 'input[name="snipi_footer_height_mode"]' );
		var footerFixedSlider  = qs( 'snipi_footer_fixed_height' );
		var footerFixedDisplay = qs( 'snipi_footer_fixed_height_value' );

		function syncFooterHeightControl() {
			if ( ! footerFixedControl ) { return; }
			var selected = document.querySelector( 'input[name="snipi_footer_height_mode"]:checked' );
			var isFixed  = selected && selected.value === 'fixed';
			footerFixedControl.style.display = isFixed ? '' : 'none';
		}

		footerHeightRadios.forEach( function ( radio ) {
			radio.addEventListener( 'change', syncFooterHeightControl );
		} );
		syncFooterHeightControl();

		// Slider – posodobi prikaz vrednosti
		if ( footerFixedSlider && footerFixedDisplay ) {
			footerFixedSlider.addEventListener( 'input', function () {
				footerFixedDisplay.textContent = footerFixedSlider.value + 'px';
			} );
		}

		/* ══════════════════════════════════════════════════════════
		   LOGO HEIGHT SLIDER (v settings tabu)
		   ══════════════════════════════════════════════════════════ */

		// Že obdelan zgoraj skupaj z logoHeightIn

		/* ══════════════════════════════════════════════════════════
		   CSS PREDOGLED (Oblikovanje tab – custom CSS gumb)
		   ══════════════════════════════════════════════════════════ */

		var previewBtn = qs( 'snipi_preview_css' );
		if ( previewBtn ) {
			previewBtn.addEventListener( 'click', handlePreview );
		}

		function handlePreview() {
			if ( typeof SNIPI_ADMIN === 'undefined' || ! SNIPI_ADMIN.rest_url ) { return; }

			var cssField  = qs( 'snipi_css_editor' );
			var previewBox = qs( 'snipi-styling-preview' );
			if ( previewBox ) {
				if ( ! previewBox.getAttribute( 'data-initial' ) ) {
					previewBox.setAttribute( 'data-initial', previewBox.innerHTML );
				}
				previewBox.textContent = 'Nalagam predogled …';
			}

			var payload = {
				post_id : SNIPI_ADMIN.post_id || 0,
				style   : { custom_css: cssField ? cssField.value : '' }
			};

			var headers = { 'Content-Type': 'application/json' };
			if ( SNIPI_ADMIN.preview_nonce ) {
				headers['X-WP-Nonce'] = SNIPI_ADMIN.preview_nonce;
			}

			fetch( SNIPI_ADMIN.rest_url, {
				method: 'POST', credentials: 'same-origin', headers: headers,
				body: JSON.stringify( payload )
			} )
			.then( function ( resp ) {
				if ( ! resp.ok ) { throw new Error( 'HTTP ' + resp.status ); }
				return resp.json();
			} )
			.then( function ( data ) {
				if ( previewBox ) {
					previewBox.innerHTML = ( data && data.html ) ? data.html : '<div class="snipi-preview-error">Predogled ni na voljo.</div>';
				}
			} )
			.catch( function () {
				if ( previewBox ) {
					previewBox.innerHTML = '<div class="snipi-preview-error">Predogled ni uspel.</div>';
					setTimeout( function () {
						previewBox.innerHTML = previewBox.getAttribute( 'data-initial' ) || '';
					}, 2000 );
				}
			} );
		}

	} ); // DOMContentLoaded
})();
