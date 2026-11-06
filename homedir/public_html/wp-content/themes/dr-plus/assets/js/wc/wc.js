(function($) {
	$(document).ready(function(){
		$(document).on('click', '.quantity button', function(e) {
			e.preventDefault();
			let $this = $(this),
				qtyInput = $this.siblings('input.qty'),
				qty = parseInt(qtyInput.val()),
				min = parseInt(qtyInput.attr('min')),
				max = parseInt(qtyInput.attr('max'));
			if($this.hasClass('plus-quantity')) {
				qty++;
			} else {
				qty--;
			}
			if(typeof min != 'undefined' && min > qty) {
				qty = min;
			}
			if(typeof max != 'undefined' && max < qty) {
				qty = max;
			}
			qtyInput.val(qty).trigger('change');
		});

		// Set qty in mini cart
		var miniCartQTYTimer;
		$(document).on('click', '.drplus_mini-cart-item-quantity .quantity button', function() {
			clearTimeout(miniCartQTYTimer);
			
			miniCartQTYTimer = setTimeout(() => {
				let loading = $('.mini-cart-loading');
				$('.mini-cart-loading').fadeIn({
					start: () => {
						$(loading).css('display', 'flex');
					}
				});
				
				let wrap = $(this).closest('.drplus_mini-cart-item-quantity'),
					item = $(this).closest('.woocommerce-mini-cart-item'),
					item_qty = $(this).siblings('.qty').val();

				$.ajax({
					url: drplusVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'drplus_update_mini_cart',
						nonce: wrap.attr('data-nonce'),
						item_key: wrap.attr('data-key'),
						item_qty: $(this).siblings('.qty').val()
					},
					success: function(res) {
						if(res) {
							if( $('body').hasClass('woocommerce-cart') ) {
								location.reload();
							}
							if( res.success && parseInt(item_qty) == 0 ) {
								item.remove();
							}
							$.each( res.fragments, function( key, value ) {
								jQuery(key).replaceWith(value);
							});
							sessionStorage.setItem( "wc_fragments", JSON.stringify( res.fragments ) );
							sessionStorage.setItem( "wc_cart_hash", res.cart_hash );
							$('body').trigger( 'wc_fragment_refresh' );
							loading.fadeOut();
						}
					}
				});
			}, 500);
		});

		// Price filter submit
		var priceFilterTimer;
		$('.price_slider').on('slidechange', function() {
			clearTimeout(priceFilterTimer);
			priceFilterTimer = setTimeout(() => {
				$(this).closest('form').submit();
			}, 1000);
		});

		// Change color filter display
		if($('.drplus-filter-color-wrap').length) {
			$('.drplus-filter-color-wrap').closest('ul').addClass('drplus-filter-color-list');
		}

		// Payment method select
		$(document).on('click', '.drplus-booking-checkout .wc_payment_method', function(e) {
			if( $(e.target).is("input") ) return;
			$(this).find('input').prop('checked', true).trigger('change').trigger('click');
		});

		// Cancel booking alert
		$('.drplus-booking-receipt-cancel-booking-open-popup').on('click', function() {
			$('#drplus-overlay').fadeIn();
			$('.drplus-booking-receipt-cancel-booking-popup').fadeIn();
		})
		$('.drplus-booking-receipt-back-cancel-booking').on('click', function() {
			$('.drplus-booking-receipt-cancel-booking-popup').fadeOut();
			$('#drplus-overlay').fadeOut();
		});
	});
})(jQuery);