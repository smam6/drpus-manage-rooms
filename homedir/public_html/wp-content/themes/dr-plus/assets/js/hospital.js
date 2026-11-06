(function($) {
	$(document).ready(function(){
		$('.hospital-gallery-popup-overlay').on('click', function() {
			$('.hospital-gallery-popup').fadeOut();
			$(this).fadeOut();
		})
		$('.hospital-gallery').on('click', function() {
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

			$('.hospital-gallery-popup-overlay').fadeIn();
			$('.hospital-gallery-popup').fadeIn({
				start: function() {
					$(this).css('display', 'flex');
				},
				complete: function() {
					let thumbSlider = new Swiper(document.getElementsByClassName('hospital-gallery-popup-thumb-slider')[0], thumbsOptions);
					let popupMainOptions = {...mainOptions};
					popupMainOptions.thumbs = {
						swiper: thumbSlider
					}
					new Swiper(document.getElementsByClassName('hospital-gallery-popup-main-slider')[0], popupMainOptions);
				}
			});
		})

		$('.hospital-contact-copy').on('click', function(e) {
			e.preventDefault();
			
			// پیدا کردن مقدار متن کنار دکمه
			var value = $(this).siblings('.hospital-contact-value').text().trim();
		
			// ایجاد یک input موقتی برای کپی کردن متن
			var $tempInput = $('<input>');
			$('body').append($tempInput);
			$tempInput.val(value).select();
			document.execCommand('copy');
			$tempInput.remove();
			alert(`${drplusHospital.i18n.copy} ${value}`);
		});
	});
})(jQuery);