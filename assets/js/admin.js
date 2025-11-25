(function () {
	'use strict';

	function qs( id ) {
		return document.getElementById( id );
	}

document.addEventListener( 'DOMContentLoaded', function () {
var navLinks = document.querySelectorAll( '.snipi-tabs a' );
navLinks.forEach( function ( link ) {
if ( link.href.indexOf( 'admin.php?page=snipi' ) !== -1 ) {
var parent = link.parentNode;
if ( parent ) {
parent.removeChild( link );
}
}
} );

var tabButtons = document.querySelectorAll( '.snipi-tab-btn' );
var tabPanels = document.querySelectorAll( '.snipi-tab' );

		tabButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var target = btn.getAttribute( 'data-tab' );

				tabButtons.forEach( function ( el ) {
					el.classList.remove( 'button-primary' );
				} );
				btn.classList.add( 'button-primary' );

				tabPanels.forEach( function ( panel ) {
					var panelTarget = panel.getAttribute( 'data-tab-content' );
					if ( panelTarget === target ) {
						panel.classList.add( 'active' );
						panel.style.display = 'block';
					} else {
						panel.classList.remove( 'active' );
						panel.style.display = 'none';
					}
				} );
			} );
		} );

var copyBtn = qs( 'snipi_copy_shortcode' );
var copyBtnDefaultHtml = copyBtn ? copyBtn.innerHTML : '';

function handleCopyAction( text, btn, restoreHtml ) {
if ( ! btn ) {
return;
}

var original = restoreHtml || btn.innerHTML;

function setFeedback( msg ) {
btn.textContent = msg;
setTimeout( function () {
btn.innerHTML = original;
}, 1500 );
}

if ( navigator.clipboard && navigator.clipboard.writeText ) {
navigator.clipboard.writeText( text ).then( function () {
setFeedback( 'Kopirano' );
} ).catch( function () {
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
if ( ! field ) {
return;
}

handleCopyAction( field.value || '', copyBtn, copyBtnDefaultHtml );
} );
}

var listCopyButtons = document.querySelectorAll( '.snipi-copy-list' );
if ( listCopyButtons.length ) {
listCopyButtons.forEach( function ( btn ) {
btn.addEventListener( 'click', function () {
var value = btn.getAttribute( 'data-snipi-copy' ) || '';
handleCopyAction( value, btn );
} );
} );
}

		var logoUploadBtn = qs( 'snipi_logo_upload' );
		var logoRemoveBtn = qs( 'snipi_logo_remove' );
		var logoInput = qs( 'snipi_logo_id' );
		var logoPreview = qs( 'snipi_logo_preview' );
		var logoHeightInput = qs( 'snipi_logo_height' );
		var logoHeightValue = qs( 'snipi_logo_height_value' );
		var mediaFrame = null;

		function updateLogoPreviewHeight( overrideHeight ) {
			if ( ! logoPreview ) {
				return;
			}

			var img = logoPreview.querySelector( 'img' );
			if ( ! img ) {
				return;
			}

			var heightValue = overrideHeight;
			if ( ! heightValue && logoHeightInput ) {
				heightValue = parseInt( logoHeightInput.value, 10 );
			}

			if ( heightValue ) {
				img.style.height = heightValue + 'px';
				img.style.maxHeight = heightValue + 'px';
				img.style.width = 'auto';
			}
		}

		if ( logoUploadBtn && window.wp && wp.media ) {
			logoUploadBtn.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				if ( mediaFrame ) {
					mediaFrame.open();
					return;
				}

				mediaFrame = wp.media({
					title: 'Izberi logo',
					button: { text: 'Uporabi logo' },
					multiple: false
				});

				mediaFrame.on( 'select', function () {
					var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
					var heightVal = logoHeightInput ? logoHeightInput.value : 60;
					if ( logoInput ) {
						logoInput.value = attachment.id;
					}
					if ( logoPreview ) {
						logoPreview.innerHTML = '<img src="' + attachment.url + '" style="height:' + heightVal + 'px;width:auto;" />';
						updateLogoPreviewHeight();
					}
				});

				mediaFrame.open();
			});
		}

		if ( logoRemoveBtn ) {
			logoRemoveBtn.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				if ( logoInput ) {
					logoInput.value = '';
				}
				if ( logoPreview ) {
					logoPreview.innerHTML = '';
				}
			} );
		}

		if ( logoHeightInput && logoHeightValue ) {
			logoHeightInput.addEventListener( 'input', function () {
				logoHeightValue.textContent = logoHeightInput.value + 'px';
				updateLogoPreviewHeight();
			} );
		}

		updateLogoPreviewHeight();

		var previewBtn = qs( 'snipi_preview_css' );
		if ( previewBtn ) {
			previewBtn.addEventListener( 'click', function () {
				handlePreview();
			} );
		}

		function handlePreview() {
			if ( typeof SNIPI_ADMIN === 'undefined' || ! SNIPI_ADMIN.rest_url ) {
				return;
			}

			var cssField = qs( 'snipi_css_editor' );
			var previewBox = qs( 'snipi-styling-preview' );
			if ( previewBox ) {
				var initial = previewBox.getAttribute( 'data-initial' );
				if ( ! initial ) {
					previewBox.setAttribute( 'data-initial', previewBox.innerHTML );
				}
				previewBox.textContent = 'Nalagam predogled ...';
			}

			var payload = {
				post_id: SNIPI_ADMIN.post_id || 0,
				style: {
					custom_css: cssField ? cssField.value : ''
				}
			};

			var headers = {
				'Content-Type': 'application/json'
			};
			if ( SNIPI_ADMIN.preview_nonce ) {
				headers['X-WP-Nonce'] = SNIPI_ADMIN.preview_nonce;
			}

			fetch( SNIPI_ADMIN.rest_url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: headers,
				body: JSON.stringify( payload )
			})
			.then( function ( resp ) {
				if ( ! resp.ok ) {
					throw new Error( 'HTTP ' + resp.status );
				}
				return resp.json();
			} )
			.then( function ( data ) {
				if ( previewBox ) {
					var html = data && data.html ? data.html : '<div class="snipi-preview-error">Predogled ni na voljo.</div>';
					previewBox.innerHTML = html;
				}
			} )
			.catch( function () {
				if ( previewBox ) {
					previewBox.innerHTML = '<div class="snipi-preview-error">Predogled ni uspel.</div>';
					setTimeout( function () {
						var stored = previewBox.getAttribute( 'data-initial' ) || '';
						previewBox.innerHTML = stored;
					}, 2000 );
				}
			} );
		}
	} );
})();
