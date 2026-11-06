(function($) {
	$(document).ready(function(){
		// Tabs
		$('.drplus_metabox-tab').on('click', function() {
			let active = 'drplus_metabox-tab-active';
			if($(this).hasClass(active)) return;

			$(this).siblings(`.${active}`).removeClass(active)

			$('.drplus_metabox-tab-content:visible').slideUp();
			$(`.drplus_metabox-tab-content[data-tab="${$(this).attr('data-tab')}"]`).slideDown();

			$(this).addClass(active);
		});

		// Post finder
		if($('.drplus_metabox_post_finder').length) {
			$('.drplus_metabox_post_finder').select2({
				width: '25em',
				minimumInputLength: 2,
				ajax: {
					url: drplusMetabox.ajaxUrl,
					type: 'POST',
					data: function(params) {
						return {
							action: `drplus_find_post`,
							text: params.term,
							nonce: drplusMetabox.nonces.postFinder,
						}
					},
					processResults: function(data) {
						if(typeof data.data != 'undefined') {
							return {
								results: data.data,
							}
						} else {
							return {
								results: [],
							}
						}
					},
					cache: false
				},
			});
		}

		// User finder
		if($('.drplus_metabox_user_finder').length) {
			$('.drplus_metabox_user_finder').select2({
				width: '25em',
				placeholder: drplusMetabox.i18n.selectUser,
				minimumInputLength: 2,
				ajax: {
					url: drplusMetabox.ajaxUrl,
					type: 'POST',
					data: function(params) {
						return {
							action: `drplus_get_users`,
							name: params.term,
							nonce: drplusMetabox.nonces.getUsers,
						}
					},
					processResults: function(data) {
						if(typeof data.data != 'undefined') {
							return {
								results: data.data,
							}
						} else {
							return {
								results: [],
							}
						}
					},
					cache: false
				},
			});
		}

		function toggleRelativeOption(mainOption, relativeOption, reverse) {
			$(mainOption).on('change', function() {
				if(!reverse) {
					if( $(this).prop('checked') ) {
						$(relativeOption).show();
					} else {
						$(relativeOption).hide();
					}
				} else {
					if( $(this).prop('checked') ) {
						$(relativeOption).hide();
					} else {
						$(relativeOption).show();
					}
				}
			});
		}

		toggleRelativeOption('#drplus_disable_header', '#disable_header_user-table'); 
		toggleRelativeOption('#drplus_show_title', '#page-icon-row'); // Hide select sidebar if sidebar is hidden
		toggleRelativeOption('#drplus_show_sidebar', '#select-sidebar-row'); // Hide select sidebar if sidebar is hidden
		toggleRelativeOption('#drplus_disable_footer', '#disable_footer_user-table'); 
	});
})(jQuery);