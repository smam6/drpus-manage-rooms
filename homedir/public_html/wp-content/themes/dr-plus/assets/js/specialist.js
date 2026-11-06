(function($) {
	$(document).ready(function(){
		let $showMoreContainer = $('.specialist_show-more-container');
		
		$showMoreContainer.each(function(i, el) {
			let $cont = $(el);
			let height = $(el).find('.specialist_show-more-wrap').attr('data-height');
			if( typeof height == 'undefined' ) height = 300;
			
			if($cont.outerHeight() >= parseInt(height)) {
				$cont.addClass('collapse');
			}
		});
		
		$('.specialist_show-more-wrap').on('click', function() {
			let $cont = $(this).closest('.specialist_show-more-container');
			$cont.css('height', '100%');
			$(this).fadeOut({
				complete: function() {
					$cont.removeClass('collapse');
				}
			})
		})

		// Show certificates in lightgallery
		lightGallery(document.getElementsByClassName('specialist_certificates-list')[0], {
			zoomFromOrigin: true,
			selector: '.specialist_certificate-item[data-src]',
			download: false,
		})
	});
})(jQuery);