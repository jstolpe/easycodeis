/**
 * Handle loading overlays
 *
 * @author Justin Stolpe
 */
var loader = {
	/**
	 * Initialize our loading overlays for use
	 *
	 * @params void
	 *
	 * @return void
	 */
	initialize : function () {
		var html = 
			'<div class="loading-overlay"></div>' +
			'<div class="loading-overlay-image-container">' +
				'<img src="assets/loading.gif" class="loading-overlay-img"/>' +
			'</div>';

		// append our html to the DOM body
		$( 'body' ).append( html );
	},

	/**
	 * Show the loading overlay
	 *
	 * @params void
	 *
	 * @return void
	 */
	showLoader : function () {
		jQuery( '.loading-overlay' ).show();
		jQuery( '.loading-overlay-image-container' ).show();
	},

	/**
	 * Hide the loading overlay
	 *
	 * @params void
	 *
	 * @return void
	 */
	hideLoader : function () {
		jQuery( '.loading-overlay' ).hide();
		jQuery( '.loading-overlay-image-container' ).hide();
	}
}