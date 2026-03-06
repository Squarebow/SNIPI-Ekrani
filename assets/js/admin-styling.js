/**
 * SNIPI Ekrani – Admin Styling JS v2.3.1
 *
 * - wp-color-picker inicializacija
 * - Range slider vrednosti
 * - Accordion toggle
 * - Radio pill aktivni razred
 * - Gumb "Odpri predogled v novem oknu" – popup z nastavljivo velikostjo
 *
 * @package SNIPI_Ekrani
 * @since   2.3.0
 */

jQuery( function ( $ ) {
	'use strict';

	/* ══════════════════════════════════════════════════════════
	   COLOR PICKER (Iris via wp-color-picker)
	   ══════════════════════════════════════════════════════════ */

	if ( $.fn.wpColorPicker ) {
		$( '.snipi-color-picker' ).wpColorPicker( {
			width: 220,
			change: function () { debouncePreview(); },
			clear:  function () { debouncePreview(); }
		} );
	}

	/* ══════════════════════════════════════════════════════════
	   RANGE SLIDERJI – posodobi prikaz vrednosti ob nalaganju in spremembi
	   ══════════════════════════════════════════════════════════ */

	function syncRangeDisplay( $input ) {
		var suffix = $input.data( 'suffix' ) || '';
		$input.closest( '.snipi-field-col' )
			.find( '.snipi-range-display' )
			.text( $input.val() + suffix );
	}

	// Inicializacija ob nalaganju
	$( '.snipi-style-range' ).each( function () {
		syncRangeDisplay( $( this ) );
	} );

	// Posodobi ob spremembi
	$( document ).on( 'input', '.snipi-style-range', function () {
		syncRangeDisplay( $( this ) );
		debouncePreview();
	} );

	/* ══════════════════════════════════════════════════════════
	   SELECT + CHECKBOX → sproži predogled
	   ══════════════════════════════════════════════════════════ */

	$( document ).on( 'change', '.snipi-style-select, .snipi-style-check', function () {
		debouncePreview();
	} );

	/* ══════════════════════════════════════════════════════════
	   RADIO PILLS – aktivni razred (TV način prikaza)
	   ══════════════════════════════════════════════════════════ */

	$( document ).on( 'change', '.snipi-radio-pill input[type="radio"]', function () {
		var name = $( this ).attr( 'name' );
		$( '.snipi-radio-pill input[name="' + name + '"]' )
			.closest( '.snipi-radio-pill' )
			.removeClass( 'snipi-radio-pill--active' );
		$( this ).closest( '.snipi-radio-pill' ).addClass( 'snipi-radio-pill--active' );
	} );

	/* ══════════════════════════════════════════════════════════
	   ACCORDION TOGGLE
	   ══════════════════════════════════════════════════════════ */

	// Zapremo sekcije z aria-expanded="false" ob nalaganju
	$( '.snipi-style-section__toggle[aria-expanded="false"]' )
		.closest( '.snipi-style-section' )
		.find( '.snipi-style-section__body' ).first().hide();

	$( document ).on( 'click', '.snipi-style-section__toggle', function () {
		var $btn     = $( this );
		var $section = $btn.closest( '.snipi-style-section' );
		var $body    = $section.find( '.snipi-style-section__body' ).first();
		var $icon    = $btn.find( '.snipi-toggle-icon' );
		var isOpen   = $btn.attr( 'aria-expanded' ) === 'true';

		if ( isOpen ) {
			$body.slideUp( 180 );
			$icon.text( '▶' );
			$btn.attr( 'aria-expanded', 'false' );
		} else {
			$body.slideDown( 180 );
			$icon.text( '▼' );
			$btn.attr( 'aria-expanded', 'true' );
		}
	} );

	/* ══════════════════════════════════════════════════════════
	   PREDOGLED – odpri frontend stran ali prikaži navodilo
	   ══════════════════════════════════════════════════════════ */

	$( document ).on( 'click', '#snipi_open_preview', function () {
		var postId = $( this ).data( 'preview-post' );

		if ( typeof SNIPI_ADMIN === 'undefined' ) { return; }

		var pageUrl = SNIPI_ADMIN.preview_page_url || '';

		if ( pageUrl ) {
			// Odpremo dejansko frontend stran ki vsebuje shortcode
			var win = window.open(
				pageUrl,
				'snipi_preview_' + postId,
				'width=1280,height=720,resizable=yes,scrollbars=yes,menubar=no,toolbar=no,location=yes,status=no'
			);
			if ( win ) { win.focus(); }
		} else {
			// Shortcode ni vstavljen v nobeno stran – pokažemo navodilo
			$( '#snipi-preview-notice' ).remove();
			var $notice = $(
				'<div id="snipi-preview-notice" style="'
				+ 'margin-top:12px;padding:12px 16px;background:#fff3cd;border:1px solid #ffc107;'
				+ 'border-radius:4px;font-size:13px;line-height:1.5;">'
				+ '<strong>Predogled ni mogoč:</strong> Ta ekran še ni vstavljen v nobeno objavljeno stran ali prispevek. '
				+ 'Kopirajte kratko kodo iz zavihka <em>Nastavitve</em> in jo prilepite v stran, '
				+ 'nato se vrnite in poskusite znova.'
				+ '</div>'
			);
			$( '#snipi_open_preview' ).after( $notice );
		}
	} );

	/* ══════════════════════════════════════════════════════════
	   ZBIRANJE VREDNOSTI GUI (za prihodnje REST predoglede)
	   ══════════════════════════════════════════════════════════ */

	function collectStyleData() {
		var data = {};
		$( '.snipi-color-picker, .snipi-style-range, .snipi-style-select' ).each( function () {
			var $el = $( this );
			var name = $el.attr( 'name' );
			if ( name ) { data[ name ] = $el.val(); }
		} );
		$( '.snipi-style-check' ).each( function () {
			var $el = $( this );
			var name = $el.attr( 'name' );
			if ( name ) { data[ name ] = $el.is( ':checked' ) ? '1' : '0'; }
		} );
		return data;
	}

	/* ══════════════════════════════════════════════════════════
	   DEBOUNCE (za morebitne prihodnje live CSS preview razširitve)
	   ══════════════════════════════════════════════════════════ */

	var previewTimer = null;
	function debouncePreview() {
		clearTimeout( previewTimer );
		previewTimer = setTimeout( function () {
			// Prostor za live CSS inject v prihodnje
		}, 200 );
	}

} );
