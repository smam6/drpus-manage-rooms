(function($) {
	$(window).on('elementor/frontend/init', function () {
		if(!$('body').hasClass('e-preview--show-hidden-elements')) return;
		const SliderWidgets = [
			'drplus_archive',
			'drplus_archive_2',
			'drplus_hospitals',
			'drplus_pro_icon_group',
			'drplus_products',
			'drplus_services',
			'drplus_services2',
			'drplus_specialist_slider',
			'drplus_specialist_offline_visits',
			'drplus_specialist_online_visits',
			'drplus_specialists',
			'drplus_statistics_card',
			'drplus_statistics_card2',
			'drplus_statistics',
			'drplus_specialists_search',
			'drplus_testimonials1',
			'drplus_testimonials2',
			'drplus_testimonials3'
		]
		SliderWidgets.forEach(widget => {
			elementorFrontend.hooks.addAction(`frontend/element_ready/${widget}.default`, function($scope, $) {
				let sliders = $scope.find('.drplus-slider-wrap');
				if(sliders.length) {
					drplusSlider.init(sliders, true);
				}
			});
		});
	});
})(jQuery);