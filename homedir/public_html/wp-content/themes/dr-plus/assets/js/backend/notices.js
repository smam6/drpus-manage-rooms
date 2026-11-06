(function($) {
	$(document).ready(function(){
		$.ajax({
			url: drplus_notices.ajaxUrl,
			type: 'POST',
			data: {
				action: 'drplus_get_notices',
			},
			success: function(res) {
				if(res) {
					$('#drplus-notices').html(res).show();
				}
			}
		});
		$(document).on('click', '.drplus_notice .notice-dismiss', function(e) {
			e.preventDefault();
			$(this).closest('.drplus_notice').fadeOut();
			$.ajax({
				url: drplus_notices.ajaxUrl,
				type: 'POST',
				data: {
					action: 'drplus_dismiss_notice',
					id: $(this).closest( '.drplus_notice' ).attr('data-id'),
					nonce: drplus_notices.nonce
				}
			});
		})
	});
})(jQuery);