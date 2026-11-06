(function($) {
	$(document).ready(function(){
		const prefix = "sheyda_wallet_settings_";
		$(`#${prefix}withdrawal_fee_type`).on('change', function() {
			let type = $(this).val();
			
			if(type == 'none') {
				$(`.${prefix}withdrawal_percentage_fee-row, .${prefix}withdrawal_fixed_fee-row`).hide();
			} else if(type == 'fixed') {
				$(`.${prefix}withdrawal_percentage_fee-row`).hide();
				$(`.${prefix}withdrawal_fixed_fee-row`).show();
			}
			 else if(type == 'percentage') {
				$(`.${prefix}withdrawal_fixed_fee-row`).hide();
				$(`.${prefix}withdrawal_percentage_fee-row`).show();
			}
		});		
	});
})(jQuery);