(function($) {
	$(document).ready(function(){
		$('.sheyda_wallet_topup-predefined-amount-btn').on('click', function() {
			$('#sheyda_wallet_topup-amount-field').val($(this).attr('data-amount')).trigger('input');
		});

		$('#sheyda_wallet_topup-amount-field').on('change input', function() {
			$('.sheyda_wallet_topup-errors').slideUp({
				complete: function() {
					$(this).empty();
				}
			});
		})

		// Subscription plan
		$('#sheyda_wallet_topup-submit').on('click', function() {
			let $this	= $(this),
				amount	= $('#sheyda_wallet_topup-amount-field').val(),
				nonce	= $this.attr('data-nonce');
			
			$this.addClass('loading');

			$.ajax({
				url: sheydaWalletVars.ajaxUrl,
				type: 'POST',
				data: {
					action: 'sheyda_wallet_process_topup',
					amount: amount,
					nonce: nonce,
				},
				success: function(res) {
					if( res.success ) {
						window.location.replace(res.data.checkoutUrl);
					} else {
						$('.sheyda_wallet_topup-errors').html(res.data.message).slideDown();
						console.log(res);
					}
				},
				complete: function() {
					$this.removeClass('loading');
				}
			});
		});

		$('#sheyda_wallet_financial-new-account-btn').on('click', function() {
			let template = wp.template(`sheyda-wallet-financial-account`);
							
			let newItem = template({
				index: $('.sheyda_wallet_financial-account').length,
			});
			
			$('.sheyda_wallet_financial-accounts').append(newItem);

			$('.sheyda_wallet_financial-account_type').select2({
				width: '100%',
				minimumResultsForSearch: Infinity
			});
			$('.sheyda_wallet_financial-account_type').trigger('change');
		});

		$(document).on('change', '.sheyda_wallet_financial-account_type', function() {			
			let value = $(this).val();
			const types = ['card', 'account', 'shaba'];
			// hide other labels
			types.forEach(type => {
				if(type !== value) {
					$(this).closest('.sheyda_wallet_financial-account').find(`.sheyda_wallet_financial_account_card_number_label[data-type=${type}]`).hide();
				}
			});
			// show selected label
			$(this).closest('.sheyda_wallet_financial-account').find(`.sheyda_wallet_financial_account_card_number_label[data-type=${value}]`).show();

			// Remove other classes
			$(this).closest('.sheyda_wallet_financial-account').find('.sheyda_wallet_financial-account_field.sheyda-wallet-check-account-number').removeClass('sheyda-wallet-shaba-number-input sheyda-wallet-card-number-input');
			if(value == 'card') {
				$(this).closest('.sheyda_wallet_financial-account').find('.sheyda_wallet_financial-account_field.sheyda-wallet-check-account-number').addClass('sheyda-wallet-card-number-input');
			}else if(value == 'shaba') {
				$(this).closest('.sheyda_wallet_financial-account').find('.sheyda_wallet_financial-account_field.sheyda-wallet-check-account-number').addClass('sheyda-wallet-shaba-number-input');
			}
			$(this).closest('.sheyda_wallet_financial-account').find('.sheyda-wallet-check-account-number').trigger('input');
		});
		$('.sheyda_wallet_financial-account_type').trigger('change');

		$(document).on('click', '.sheyda_wallet_financial-account-remove', function() {
			$(this).closest('.sheyda_wallet_financial-account').remove();
			
			$('.sheyda_wallet_financial-account').each(function(index) {
				let key = index;
				$(this).find('input, select').each(function() {
					$(this).attr('name', $(this).attr('name').replace(/\d+/g, key));
					if( $(this).attr('id') ) {
						$(this).attr('id', $(this).attr('id').replace(/\d+/g, key));
					}

					let label = $(this).siblings('label');
					if(label.length) {
						label.attr('for', $(this).siblings('label').attr('for').replace(/\d+/g, key));
						if(label.attr('id')) {
							label.attr('id', $(this).siblings('label').attr('id').replace(/\d+/g, key));
						}
					}
				});
			});
		});

		$('#sheyda_wallet_withdrawal-destination-field, .sheyda_wallet_financial-account_type').select2({
			width: '100%',
			minimumResultsForSearch: Infinity
		});

		function checkWithdrawalAmount() {
			let $amountField = $('#sheyda_wallet_withdrawal-amount-field'),
				$submitButton = $('#sheyda_wallet_withdrawal-submit'),
				value = parseFloat(sheydaWallet.convertChars($amountField.val()).replaceAll(',', '')),
				minValue = parseFloat($amountField.attr('data-min')),
				maxValue = parseFloat($amountField.attr('data-max'));

			if(value >= minValue && value <= maxValue) {
				$submitButton.removeClass('disabled');
				$('.sheyda_wallet_withdrawal-errors').slideUp({
					complete: function() {
						$(this).empty();
					}
				});
				return true;
			} else {
				$submitButton.addClass('disabled');
				$('.sheyda_wallet_withdrawal-errors').html(value < minValue ? walletWC.i18n.minWithdrawalError : walletWC.i18n.maxWithdrawalError).slideDown();
				return false;
			}
		}

		$('#sheyda_wallet_withdrawal-amount-field').on('input', function() {
			checkWithdrawalAmount();
		});

		// Check amount before submit
		$('#sheyda_wallet_withdrawal-submit').on('click', function(e) {
			e.preventDefault();
			if(checkWithdrawalAmount()) {
				$('#sheyda_wallet_withdrawal-request-form').submit()
			}	
		});
	});
})(jQuery);