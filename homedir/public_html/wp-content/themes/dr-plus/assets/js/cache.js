(function($) {
	$(document).ready(function(){
		$.ajax( {
			url: drplusVars.ajaxUrl,
			type: 'POST',
			data: {
				action: 'drplus_cache_sync'
			}
		} );
	});
})(jQuery);