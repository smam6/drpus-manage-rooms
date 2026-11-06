(function($) {
	$(document).ready(function(){
		$('.product-slider-popup-overlay').on('click', function() {
			$('.product-slider-popup').fadeOut();
			$(this).fadeOut();
		})
		$('.product-main-slider .swiper-wrapper').on('click', function() {
			let thumbsOptions = {
				loop: true,
				direction: 'horizontal',
				spaceBetween: 12,
				slidesPerView: 4,
				watchSlidesProgress: true,
				navigation: {
					nextEl: ".swiper-button-next",
					prevEl: ".swiper-button-prev",
				},
			};
			let mainOptions = {
				loop: true,
				direction: 'horizontal',
				spaceBetween: 12,
				slidesPerView: 1,
				navigation: {
					nextEl: ".swiper-button-next",
					prevEl: ".swiper-button-prev",
				},
				thumbs: {},
			};

			$('.product-slider-popup-overlay').fadeIn();
			$('.product-slider-popup').fadeIn({
				start: function() {
					$(this).css('display', 'flex');
				},
				complete: function() {
					let thumbSlider = new Swiper(document.getElementsByClassName('product-slider-popup-thumb-slider')[0], thumbsOptions);
					let popupMainOptions = {...mainOptions};
					popupMainOptions.thumbs = {
						swiper: thumbSlider
					}
					new Swiper(document.getElementsByClassName('product-slider-popup-main-slider')[0], popupMainOptions);
				}
			});
		})

		$('.product-attribute-dropdown').on('mouseenter touchenter click', function() {
			$(this).addClass('hover');
			let dropdown = $(this).find('.dropdown-items');
            dropdown.css('display', 'flex');
		});
		$('.product-attribute-dropdown').on('mouseleave touchleave', function() {
			$(this).removeClass('hover');
		});
		// Select dropdown attr
		$('.product-attribute-dropdown .dropdown-item').on('click', function(e) {
			let dropdown = $(this).closest('.dropdown');
			dropdown.find('.dropdown-current').text($(this).text())
			selectAttr(dropdown.attr('data-attr'), $(this).attr('data-value'));
			dropdown.find('.dropdown-items').hide();
			dropdown.removeClass('hover');
			e.stopPropagation();
		})
		// Select color attr
		$(document).on('click', '.product-head-variation-item', function() {
			$(this).siblings('.selected').removeClass('selected');
			$(this).addClass('selected');
			selectAttr($(this).closest('.product-head-variation').attr('data-attr'), $(this).attr('data-value'));
		})

		// Remove unselectable items from selects
		let additionalOptionsDeleted = false;
		$(document).on('woocommerce_update_variation_values', function () {
			if( !additionalOptionsDeleted ) {
				$('.product-attribute-dropdown .dropdown-item').each(function() {
					let attr = $(this).closest('.dropdown').attr('data-attr');
					if( !$(`select[id="${attr}"] option[value="${$(this).attr('data-value')}"]`).length ) {
						$(this).remove();
					}
				});
				$('.product-head-variation-item').each(function() {
					let attr = $(this).closest('.product-head-variation').attr('data-attr');
					if( !$(`select[id="${attr}"] option[value="${$(this).attr('data-value')}"]`).length ) {
						$(this).remove();
					}
				});
				additionalOptionsDeleted = true;
			}
		});

		function selectAttr( attrName, value ) {
			$(`select[id="${attrName}"]`).val(value).trigger('change');
		}

		$('form.variations_form.cart').on('found_variation', function(e, variation) {
			if(!$('.product-section .product-main-slider').hasClass('swiper')) return;
			// Change image in slider after selected the attribute
			let slideIndex = $(`.product-thumb-slider .swiper-slide[data-id="${variation.image_id}"]`).index(),
				mainSlider = $('.product-main-slider')[0].swiper;
			if( mainSlider ) {
				mainSlider.slideTo(slideIndex);
			}
		})

		$('.product-tab-title a').on('click', function() {
			$('.product-tab-title').removeClass('active');
			$(this).closest('li').addClass('active');
		})

		// Show more attrs link
		$('.product-show-more-attr-link').on('click', function() {
			if($(this).hasClass('opened')) {
				$(this).siblings('.woocommerce-product-attributes').find('.product-attr-extra-row').slideUp();
			} else {				
				$(this).siblings('.woocommerce-product-attributes').find('.product-attr-extra-row').slideDown({
					start: function() {						
						$(this).css('display', 'grid')
					}
				});
			}
			$(this).toggleClass('opened');
		});
	});
})(jQuery);

function checkSliders() {
	let thumbsOptions, mainOptions;
	let thumbSliders = jQuery('.product-thumb-slider:visible'),
		mainSliders = jQuery('.product-main-slider:visible');
	if( window.innerWidth > 767 ) {
		thumbsOptions = {
			loop: true,
			direction: 'vertical',
			spaceBetween: 12,
			slidesPerView: parseInt(thumbSliders[0].closest('.images').getAttribute('data-columns')),
			watchSlidesProgress: true,
			mousewheel: true,
			freeMode: true,
			scrollbar: {
				el: ".swiper-scrollbar",
			}
		},
		mainOptions = {
			loop: true,
			spaceBetween: 16,
			slidesPerView: 1,
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			thumbs: {},
		};
	} else {
		thumbsOptions = {
			loop: true,
			direction: 'horizontal',
			spaceBetween: 12,
			slidesPerView: 'auto',
			watchSlidesProgress: true,
			freeMode: true,
			scrollbar: {
				el: ".swiper-scrollbar",
			}
		},
		mainOptions = {
			loop: true,
			direction: 'horizontal',
			slidesPerView: 1,
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			thumbs: {},
		};
	}

	for( let sliderIndex = 0; sliderIndex <= thumbSliders.length; sliderIndex++ ) {
		let thumbSlider = thumbSliders[sliderIndex],
			mainSlider = mainSliders[sliderIndex];
		if( typeof thumbSlider != 'undefined' && typeof thumbSlider.swiper != 'undefined' ) {
			thumbSlider.swiper.destroy( true, true );
		}
		if( typeof mainSlider != 'undefined' && typeof mainSlider.swiper != 'undefined' ) {
			mainSlider.swiper.destroy( true, true );
		}	
		if( !jQuery(thumbSlider).is(":visible") ) continue;
		thumbSlider = new Swiper(thumbSlider, thumbsOptions);
		mainOptions.thumbs = {
			swiper: thumbSlider
		};
		new Swiper(mainSlider, mainOptions);
	}
}
document.addEventListener("DOMContentLoaded", checkSliders);
checkSliders();
window.matchMedia(`(min-width: 768px)`).addEventListener('change', checkSliders)