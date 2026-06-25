/**
 * Altersverifikation – Admin JS (minimal).
 */
( function () {
	'use strict';

	var scopeSelect = document.getElementById( 'avf-scope' );
	var pagesRow    = document.getElementById( 'avf-pages-row' );

	function togglePagesRow() {
		if ( ! scopeSelect || ! pagesRow ) {
			return;
		}
		pagesRow.style.display = scopeSelect.value === 'pages' ? '' : 'none';
	}

	if ( scopeSelect ) {
		scopeSelect.addEventListener( 'change', togglePagesRow );
		togglePagesRow();
	}
}() );
