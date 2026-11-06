(function($) {
	$(document).ready(function(){
		$('#drplus_notification_all_users').on('change', function() {
			let row = $('#drplus_notification_select_user')
			if($(this).prop('checked')) {
				row.hide();
			} else {
				row.show();
			}
		});

		$('#drplus_notification_users').select2({
			width: '25em',
			placeholder: drplusMetabox.i18n.selectUser,
			minimumInputLength: 2,
			ajax: {
				url: drplusMetabox.ajaxUrl,
				type: 'POST',
				data: function(params) {
					let type = $('#drplus_notification_recipients').val();
					if(type == 'all_specialists') {
						type = 'specialists'
					} else {
						type = 'users'
					}
					return {
						action: `drplus_get_users`,
						name: params.term,
						type: type,
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
	});
})(jQuery);