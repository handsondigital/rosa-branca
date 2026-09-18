/**
 * Vanilla JS for the native (no ACF) meta boxes in inc/home-fields.php /
 * inc/fale-conosco-fields.php — two behaviours: an image picker using
 * wp.media (WordPress's own native media modal, already loaded on every
 * post-edit screen, so no new dependency) and repeater add/remove rows.
 * Classic wp-admin script (jQuery/wp.media globals are already present
 * there) — deliberately NOT part of the front-end Vite bundle, since
 * nothing here ships to a visitor.
 */
( function () {
	'use strict';

	function initImageFields() {
		document.querySelectorAll( '[data-rb-image-field]' ).forEach( function ( field ) {
			// Already wired (e.g. a repeater row cloned after this ran once).
			if ( field.dataset.rbImageBound ) return;
			field.dataset.rbImageBound = '1';

			const input = field.querySelector( '[data-rb-image-input]' );
			const preview = field.querySelector( '.rb-image-field__preview' );
			const selectBtn = field.querySelector( '[data-rb-image-select]' );
			const removeBtn = field.querySelector( '[data-rb-image-remove]' );
			let frame = null;

			selectBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( ! frame ) {
					frame = wp.media( { title: selectBtn.textContent, multiple: false, library: { type: 'image' } } );
					frame.on( 'select', function () {
						const attachment = frame.state().get( 'selection' ).first().toJSON();
						input.value = attachment.id;
						preview.innerHTML = '<img src="' + ( attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url ) + '" alt="">';
						preview.hidden = false;
						removeBtn.hidden = false;
					} );
				}
				frame.open();
			} );

			removeBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				input.value = '0';
				preview.innerHTML = '';
				preview.hidden = true;
				removeBtn.hidden = true;
			} );
		} );
	}

	function reindexRepeater( repeater ) {
		const name = repeater.getAttribute( 'data-rb-repeater' );
		const rows = repeater.querySelectorAll( '[data-rb-repeater-rows] [data-rb-repeater-row]' );
		rows.forEach( function ( row, index ) {
			row.querySelectorAll( '[name]' ).forEach( function ( el ) {
				el.name = el.name.replace(
					new RegExp( '^' + name.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '\\[[^\\]]*\\]' ),
					name + '[' + index + ']'
				);
			} );
			row.querySelectorAll( '[for]' ).forEach( function ( el ) {
				el.setAttribute( 'for', el.getAttribute( 'for' ) + '-' + index );
			} );
		} );
	}

	function initRepeaters() {
		document.querySelectorAll( '[data-rb-repeater]' ).forEach( function ( repeater ) {
			if ( repeater.dataset.rbRepeaterBound ) return;
			repeater.dataset.rbRepeaterBound = '1';

			const addBtn = repeater.querySelector( '[data-rb-repeater-add]' );
			const rowsContainer = repeater.querySelector( '[data-rb-repeater-rows]' );
			const template = repeater.querySelector( '[data-rb-repeater-template]' );

			addBtn.addEventListener( 'click', function () {
				const fragment = template.content.cloneNode( true );
				rowsContainer.appendChild( fragment );
				reindexRepeater( repeater );
				initImageFields();
				bindRemoveButtons( repeater );
			} );

			bindRemoveButtons( repeater );
		} );
	}

	function bindRemoveButtons( repeater ) {
		repeater.querySelectorAll( '[data-rb-repeater-remove]' ).forEach( function ( btn ) {
			if ( btn.dataset.rbBound ) return;
			btn.dataset.rbBound = '1';
			btn.addEventListener( 'click', function () {
				btn.closest( '[data-rb-repeater-row]' ).remove();
				reindexRepeater( repeater );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initImageFields();
		initRepeaters();
	} );
} )();
