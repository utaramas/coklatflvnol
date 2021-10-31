(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 $(document).ready(function(){
	 	$('#exclude_post_cat').on('change, keyup', function() {
	 		// alert(8);
	 		var currentInput = $(this).val();
	 		var fixedInput = currentInput.replace(/[^0-9\,]/, '');
	 		$(this).val(fixedInput);
	 		console.log(fixedInput);
	 	});	 	
	 	$('#exclude_menu_items').on('change, keyup', function() {
	 		// alert(8);
	 		var currentInput = $(this).val();
	 		var fixedInput = currentInput.replace(/[^0-9\,]/, '');
	 		$(this).val(fixedInput);
	 		console.log(fixedInput);
	 	});



	 	// $('#enable_menu').prop('checked', true);

	 	$('#enable_menu').on('change', function() {
	 		var value = this.checked ? $(".mmhs-selectmenu").show() : $(".mmhs-selectmenu").hide();
	 		// $('#textInput').prop('disabled', this.checked).val(value);
	 	}).trigger('change');

	 	$('#enable_blog').on('change', function() {
	 		var value = this.checked ? $(".mmhs-selectblogcat").show() : $(".mmhs-selectblogcat").hide();
	 		// $('#textInput').prop('disabled', this.checked).val(value);
	 	}).trigger('change');

	 	$('#enable_pages').on('change', function() {
	 		var value = this.checked ? $(".mmhs-excludepage").show() : $(".mmhs-excludepage").hide();
	 		// $('#textInput').prop('disabled', this.checked).val(value);
	 	}).trigger('change');

	 	$('#enable_allpost').on('change', function() {
	 		var value = this.checked ? $(".mmhs-excludeppostcat").show() : $(".mmhs-excludeppostcat").hide();
	 		// $('#textInput').prop('disabled', this.checked).val(value);
	 	}).trigger('change');

	 })

	})( jQuery );
