/**
 * SNIPI Ekrani – Admin Styling JS
 *
 * Logika za Oblikovanje tab:
 * - Inicializacija wp-color-picker (Iris)
 * - Posodabljanje prikazane vrednosti range sliderjev
 * - Accordion toggle za sekcije
 * - Živi predogled: CSS se injicira v <style> tag v previewu
 *
 * Odvisnosti: jQuery, wp-color-picker
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
	   RANGE SLIDERJI – posodobi prikaz vrednosti
	   ══════════════════════════════════════════════════════════ */

	$( document ).on( 'input', '.snipi-style-range', function () {
		var $s      = $( this );
		var suffix  = $s.data( 'suffix' ) || '';
		$s.siblings( '.snipi-range-display' ).text( $s.val() + suffix );
		debouncePreview();
	} );

	// Inicializiraj prikaz ob nalaganju
	$( '.snipi-style-range' ).each( function () {
		var $s     = $( this );
		var suffix = $s.data( 'suffix' ) || '';
		var $disp  = $s.siblings( '.snipi-range-display' );
		if ( $disp.length ) {
			$disp.text( $s.val() + suffix );
		}
	} );

	/* ══════════════════════════════════════════════════════════
	   SELECT + CHECKBOX → sproži predogled
	   ══════════════════════════════════════════════════════════ */

	$( document ).on( 'change', '.snipi-style-select, .snipi-style-check', function () {
		debouncePreview();
	} );

	/* ══════════════════════════════════════════════════════════
	   ACCORDION TOGGLE
	   ══════════════════════════════════════════════════════════ */

	// Sekcije z aria-expanded="false" zapremo ob nalaganju
	$( '.snipi-style-section__toggle[aria-expanded="false"]' )
		.closest( '.snipi-style-section' )
		.addClass( 'snipi-style-section--collapsed' )
		.find( '.snipi-style-section__body' ).first().hide();

	$( document ).on( 'click', '.snipi-style-section__toggle', function () {
		var $btn     = $( this );
		var $section = $btn.closest( '.snipi-style-section' );
		var $body    = $section.find( '.snipi-style-section__body' ).first();
		var $icon    = $btn.find( '.snipi-toggle-icon' );
		var isOpen   = ! $section.hasClass( 'snipi-style-section--collapsed' );

		if ( isOpen ) {
			$section.addClass( 'snipi-style-section--collapsed' );
			$body.slideUp( 180 );
			$icon.text( '▶' );
			$btn.attr( 'aria-expanded', 'false' );
		} else {
			$section.removeClass( 'snipi-style-section--collapsed' );
			$body.slideDown( 180 );
			$icon.text( '▼' );
			$btn.attr( 'aria-expanded', 'true' );
		}
	} );

	/* ══════════════════════════════════════════════════════════
	   ŽIVI PREDOGLED – injicira scoped CSS v preview box
	   ══════════════════════════════════════════════════════════ */

	var previewTimer = null;

	function debouncePreview() {
		clearTimeout( previewTimer );
		previewTimer = setTimeout( injectPreviewCSS, 200 );
	}

	function colorVal( id ) {
		// Iris shrani vrednost v hidden input, ki ga ustvari wpColorPicker;
		// najprej poskusi Iris API, sicer beri value direktno
		var $el = $( '#' + id );
		if ( ! $el.length ) { return ''; }
		return $el.val() || '';
	}

	function rangeVal( id ) {
		var $el = $( '#' + id );
		return $el.length ? parseInt( $el.val(), 10 ) : null;
	}

	function selectVal( id ) {
		var $el = $( '#' + id );
		return $el.length ? $el.val() : '';
	}

	function checked( id ) {
		var $el = $( '#' + id );
		return $el.length ? $el.is( ':checked' ) : true;
	}

	/**
	 * Gradi CSS string iz trenutnih vrednosti GUI.
	 * Uporablja isti scoping algoritem kot PHP generate_styling_css().
	 */
	function injectPreviewCSS() {
		var $box = $( '#snipi-styling-preview' );
		if ( ! $box.length ) { return; }

		// Scoping: .snipi znotraj preview boxa
		var scope = '#snipi-styling-preview .snipi';
		var css   = '';
		var c, fs, pTop, pH;

		/* ── A. CEL ZASLON ─────────────────────────────── */
		var screenRules = '';
		var fontFamily  = selectVal( 'snipi_style_screen_font_family' );
		if ( fontFamily )                        { screenRules += 'font-family:' + fontFamily + ';'; }
		c = colorVal( 'snipi_style_screen_bg' );
		if ( c )                                  { screenRules += 'background:' + c + ';'; }
		c = colorVal( 'snipi_style_screen_color' );
		if ( c )                                  { screenRules += 'color:' + c + ';'; }
		if ( screenRules )                        { css += scope + '{' + screenRules + '}'; }

		/* ── B. GLAVA ──────────────────────────────────── */
		c = colorVal( 'snipi_style_header_bg' );
		if ( c ) { css += scope + ' .snipi__header{background:' + c + ';}'; }

		c = colorVal( 'snipi_style_header_title_color' );
		if ( c ) { css += scope + ' .snipi__title{color:' + c + ';}'; }

		c = colorVal( 'snipi_style_header_meta_color' );
		if ( c ) {
			css += scope + ' .snipi__date,' + scope + ' .snipi__clock-value,' + scope + ' .snipi__pagination{color:' + c + ';}';
		}

		fs = rangeVal( 'snipi_style_header_font_scale' );
		if ( fs !== null && fs !== 100 ) {
			var hf = fs / 100;
			css += scope + ' .snipi__title--large{font-size:' + ( 2 * hf ).toFixed(3) + 'rem;}';
			css += scope + ' .snipi__date{font-size:' + ( 1.3 * hf ).toFixed(3) + 'rem;}';
			css += scope + ' .snipi__clock-value,' + scope + ' .snipi__pagination{font-size:' + hf.toFixed(3) + 'rem;}';
		}

		pTop = rangeVal( 'snipi_style_header_padding_top' );
		pH   = rangeVal( 'snipi_style_header_padding_h' );
		if ( pTop !== null || pH !== null ) {
			pTop = pTop !== null ? pTop : 10;
			pH   = pH   !== null ? pH   : 16;
			if ( pTop !== 10 || pH !== 16 ) {
				css += scope + ' .snipi__header{padding-top:' + pTop + 'px;padding-bottom:' + pTop + 'px;padding-left:' + pH + 'px;padding-right:' + pH + 'px;}';
			}
		}

		/* ── C. TABELA ─────────────────────────────────── */
		c = colorVal( 'snipi_style_table_thead_bg' );
		if ( c ) { css += scope + ' .snipi__table thead{background:' + c + ';}'; }

		c = colorVal( 'snipi_style_table_thead_color' );
		if ( c ) { css += scope + ' .snipi__table thead th{color:' + c + ';}'; }

		c = colorVal( 'snipi_style_table_row_color' );
		if ( c ) { css += scope + ' .snipi__table tbody td{color:' + c + ';}'; }

		c = colorVal( 'snipi_style_table_alt_bg' );
		if ( c ) { css += scope + ' .snipi__row--alt{background:' + c + ';}'; }

		fs = rangeVal( 'snipi_style_table_font_scale' );
		if ( fs !== null && fs !== 100 ) {
			var tf = fs / 100;
			css += scope + ' .snipi__table td,' + scope + ' .snipi__table th{font-size:' + ( 0.95 * tf ).toFixed(3) + 'rem;}';
		}

		pTop = rangeVal( 'snipi_style_table_padding_top' );
		pH   = rangeVal( 'snipi_style_table_padding_h' );
		if ( pTop !== null || pH !== null ) {
			pTop = pTop !== null ? pTop : 6;
			pH   = pH   !== null ? pH   : 14;
			if ( pTop !== 6 || pH !== 14 ) {
				css += scope + ' .snipi__table td,' + scope + ' .snipi__table th{padding-top:' + pTop + 'px;padding-bottom:' + pTop + 'px;padding-left:' + pH + 'px;padding-right:' + pH + 'px;}';
			}
		}

		if ( ! checked( 'snipi_style_table_show_live' ) ) {
			css += scope + ' .snipi__live-indicator{display:none;}';
		}

		/* ── D. SPODNJA VRSTICA ────────────────────────── */
		var footerRules = '';
		c = colorVal( 'snipi_style_footer_bg' );
		if ( c ) { footerRules += 'background:' + c + ';'; }

		c = colorVal( 'snipi_style_footer_color' );
		if ( c ) { footerRules += 'color:' + c + ';'; }

		fs = rangeVal( 'snipi_style_footer_font_scale' );
		if ( fs !== null && fs !== 100 ) {
			footerRules += 'font-size:' + ( 0.9 * fs / 100 ).toFixed(3) + 'rem;';
		}

		var ta = selectVal( 'snipi_style_footer_text_align' );
		if ( ta ) { footerRules += 'text-align:' + ta + ';'; }

		pTop = rangeVal( 'snipi_style_footer_padding_top' );
		pH   = rangeVal( 'snipi_style_footer_padding_h' );
		if ( pTop !== null || pH !== null ) {
			pTop = pTop !== null ? pTop : 8;
			pH   = pH   !== null ? pH   : 16;
			if ( pTop !== 8 || pH !== 16 ) {
				footerRules += 'padding-top:' + pTop + 'px;padding-left:' + pH + 'px;padding-right:' + pH + 'px;';
			}
		}

		if ( footerRules ) {
			css += scope + ' .snipi__bottom-row{' + footerRules + '}';
		}

		/* ── Injiciraj ─────────────────────────────────── */
		var $style = $( '#snipi-live-preview-style' );
		if ( ! $style.length ) {
			$style = $( '<style id="snipi-live-preview-style">' ).appendTo( 'head' );
		}
		$style.text( css );
	}

	// Zaženi predogled takoj ob nalaganju
	injectPreviewCSS();

} );
