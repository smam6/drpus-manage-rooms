(function($) {
	$(document).ready(function(){
		$(document).on('click', '.wishlist-button', function(e) {
			e.preventDefault();
			if($('body').hasClass('logged-in')) {
				let btn = $(this);
				btn.addClass('loading');
				$.ajax({
					url: drplusVars.ajaxUrl,
					type: 'post',
					data: {
						action: 'toggle_wishlist',
						product_id: btn.attr('data-product-id'),
						nonce: btn.attr('data-nonce')
					},
					success: function(res) {
						if(res.success) {
							btn.find('i').attr('class', res.data.icon_class);
							let popoverShow = '';
							if( res.data.status == 'added' ) {
								popoverShow = 'added';
								btn.find('.wishlist-popover-removed').hide();
							} else {
								popoverShow = 'removed';
								btn.find('.wishlist-popover-added').hide();
							}
							btn.find(`.wishlist-popover-${popoverShow}`).fadeIn();
							setTimeout(function() {
								btn.find(`.wishlist-popover-${popoverShow}`).hide();
							}, 2000);
						} else {
							if(typeof res.data != 'undefined' && typeof res.data.code != 'undefined') {
								if( res.data.code == 'forbidden' ) {
									$('.header-account-wrap a').click();
								}
							}
							console.log(res.data);
						}
					},
					complete: function() {
						btn.removeClass('loading');
					}
				});
			} else {
				$('.header-account-wrap a').click();
			}
		})
	});
})(jQuery);