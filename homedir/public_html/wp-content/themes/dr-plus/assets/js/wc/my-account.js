(function($) {
	$(document).ready(function(){
		$('.woocommerce-MyAccount-navigation-expand a').on('click', function(e) {
			e.preventDefault();
			$('.myaccount-sidebar-wrap').toggleClass('expanded');
			if( $('.myaccount-sidebar-wrap').hasClass('expanded') ) {
				$('.myaccount-sidebar-wrap').css('width', '240px');
			} else {
				$('.myaccount-sidebar-wrap').css('width', '');
			}
		});
		$('.myaccount-sidebar-mobile-expand').on('click', function() {
			$('.myaccount-sidebar-wrap').addClass('mobile-expanded');
			$('#drplus-overlay').fadeIn();
		});
		$('#drplus-overlay').on('click', function() {
			$('.myaccount-sidebar-wrap').removeClass('mobile-expanded');
			$('#drplus-overlay').fadeOut();
		});

		// Upload new avatar
		$('#drplus-edit-avatar').on('click', function(e) {
			e.preventDefault();
			let wrap = $(this).closest('.drplus-edit-avatar-wrap-row'),
				selectedFileInput = wrap.find('#account_avatar_id');
			var fileFrame = wp.media({
				frame: 'select',
				editing : false,
				multiple : false,
				library: {
					type: 'image'
				},
				selection : ""
			});

			fileFrame.on('open', function() {
				var selection = fileFrame.state().get('selection');
				let attachment = wp.media.attachment( selectedFileInput.val() );
				selection.add( attachment ? [ attachment ] : []);
			});

			fileFrame.on('select', function() {
				var selection = fileFrame.state().get('selection').first();

				selectedFileInput.val(selection.attributes.id);
				// Show avatar
				wrap.find('.drplus-edit-avatar-wrap img').attr('src', selection.attributes.url).removeAttr('srcset');
			});

			fileFrame.open();
		});

		$('.drplus-delete-avatar-icon').on('click', function(e) {
			e.stopPropagation();
			$('#account_avatar_id').val('');
			$(this).siblings('img').attr('src', drplusVars.defaults.avatar).removeAttr('srcset');
		});

		$('.notification-head').on('click', function() {
			let notification = $(this).closest('.notification');
			if(notification.hasClass('notification-unread')) {
				$.ajax({
					url: drplusVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'drplus_set_notification_read',
						id: notification.attr('data-id')
					},
					success: function(res) {
						if(res.success) {
							if(res.data.unreadCount > 0) {
								$('.account-notif-count').text(res.data.unreadCount);
							} else {
								$('.account-notif-count-wrap').fadeIn({
									complete: function() {
										$(this).remove();
									}
								});
							}
						}
					}
				});
			}
			notification.removeClass('notification-unread');
			notification.find('.notification-text').slideToggle();
		})

		// Prevent to submit edit account form when it need otp
		if($('.drplus_otp_code-wrap').length) {
			let otpConfirmed = false,
				button = $('button[name="save_account_details"]'),
				mobileChanged = false,
				lastMobile = $('#drplus_mobile').val().replaceAll(' ', ''),
				origMobile = lastMobile,
				timerInterval;

			$('#drplus_mobile').on('change input', function() {
				mobileChanged = true;
				button.prop('disabled', !drplus.validateMobile($(this).val()));

			})

			$('#drplus_otp_code').on('change input', function() {
				button.prop('disabled', $(this).val().length !== 4);
			})

			function startTimer(durationInSeconds, element) {
				element = $(element);
				let remainingTime = durationInSeconds;

				clearInterval(timerInterval);
			
				function updateTimer() {
					const minutes = String(Math.floor(remainingTime / 60)).padStart(2, '0');
					const seconds = String(remainingTime % 60).padStart(2, '0');
					element.text(`${minutes}:${seconds}`);
				
					if (remainingTime > 0) {
						remainingTime--;
						$('.drplus_otp-timer').show();
						$('.drplus_otp-timer-resend').hide();
					} else {
						clearInterval(timerInterval);
						timerInterval = null;
						element.text("00:00");
						$('.drplus_otp-timer').hide();
						$('.drplus_otp-timer-resend').show();
					}
				}
			
				timerInterval = setInterval(updateTimer, 1000);
				updateTimer();
			}

			function sendOtp() {
				let mobile = $('#drplus_mobile').val().replaceAll(' ', '');
				button.addClass('loading').prop('disabled', true);
				if( mobile != origMobile ) {
					if( !timerInterval || lastMobile != mobile ) {
						if( drplus.validateMobile( mobile ) ) {
							$('.drplus_mobile-wrap,.drplus_otp_code-wrap').removeClass('error');
							
							lastMobile = mobile;
							$.ajax({
								url: drplusVars.ajaxUrl,
								type: 'post',
								data: {
									action: 'drplus_profile_send_otp',
									mobile: mobile,
									nonce: drplusMyAccount.nonces.sendOtp,
								},
								success: function(res) {
									if( res.success ) {
										startTimer(drplusMyAccount.otpTimer, '.drplus_otp-timer');
										$('.drplus_otp-timer').show();
										$('.drplus_otp_code-wrap').slideDown();
										$('.auth-otp-number').text(mobile);
									} else {
										if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined') {
											$('#drplus_mobile ~ .input-error .input-error-text').text(res.data.message);
											$('.drplus_mobile-wrap').addClass('error');
										}
									}
								},
								complete: function() {
									button.removeClass('loading');
								}
							});
						} else {
							$('#drplus_mobile ~ .input-error .input-error-text').text(drplusVars.i18n.wrongMobile);
							$('.drplus_mobile-wrap').addClass('error');
						}
					}
				}
			}
			
			$('form.edit-account').on('submit', function(e) {
				let mobile = $('#drplus_mobile').val().replaceAll(' ', '');
				if( !otpConfirmed && mobileChanged && mobile != origMobile ) {
					e.preventDefault();
					if( !$('.drplus_otp_code-wrap').is(":visible") ) {
						sendOtp();
					} else {
						let form = $(this),
							otp = $('#drplus_otp_code').val();
						if(drplus.validateMobile(mobile)) {
							if(otp.length === 4) {
								$.ajax({
									url: drplusVars.ajaxUrl,
									type: 'post',
									data: {
										action: 'drplus_profile_check_otp',
										nonce: drplusMyAccount.nonces.checkOtp,
										mobile: mobile,
										otp: otp
									},
									success: function(res) {
										if( res.success ) {
											if( res.data.code == 'otp_confirmed' ) {
												otpConfirmed = true;
												form.submit();
											}
										} else {
											if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined') {
												$('#drplus_otp_code ~ .input-error .input-error-text').text(res.data.message);
												$('.drplus_otp_code-wrap').addClass('error');
											}
										}
									}
								}).done(function() {
									button.removeClass('loading')
								});
							}
						} else {
							$('#drplus_mobile ~ .input-error .input-error-text').text(drplusVars.i18n.wrongMobile);
							$('.drplus_mobile-wrap').addClass('error');
						}
					}
				}
			})
			$('.drplus_otp-timer-resend').on('click', sendOtp);
		}
	});
})(jQuery);