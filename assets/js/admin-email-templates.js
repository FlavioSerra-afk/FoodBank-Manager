/* Email Templates admin interactions */
(function ($) {
	$( '.fbm-email-preview' ).on(
		'click',
		function (e) {
			e.preventDefault();
			var form = $( this ).closest( 'form' );
			var data = form.serializeArray();
			data.push( {name:'fbm_action', value:'preview'} );
			data.push( {name:'fbm_ajax', value:'1'} );
			$.post(
				ajaxurl,
				data,
				function (resp) {
					if (resp && resp.body) {
						alert( resp.body );
					}
				}
			);
		}
	);
})( jQuery );
