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
	   PREDOGLED – odpri popup okno
	   ══════════════════════════════════════════════════════════ */

	$( document ).on( 'click', '#snipi_open_preview', function () {
		var postId = $( this ).data( 'preview-post' );
		var nonce  = $( this ).data( 'nonce' );

		if ( ! postId || typeof SNIPI_ADMIN === 'undefined' || ! SNIPI_ADMIN.admin_url ) {
			alert( 'Predogled ni na voljo.' );
			return;
		}

		var previewUrl = SNIPI_ADMIN.admin_url
			+ '?post_type=ekran'
			+ '&page=snipi-edit-screen'
			+ '&post=' + encodeURIComponent( postId )
			+ '&snipi_preview=1'
			+ '&nonce=' + encodeURIComponent( nonce );

		var win = window.open(
			previewUrl,
			'snipi_preview_' + postId,
			'width=1280,height=720,resizable=yes,scrollbars=no,menubar=no,toolbar=no,location=no,status=no'
		);
		if ( win ) { win.focus(); }
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
