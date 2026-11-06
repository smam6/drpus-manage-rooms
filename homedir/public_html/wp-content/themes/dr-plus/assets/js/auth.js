(function($) {
	$(document).ready(function(){
		let timerInterval, lastMobile,
			mode, // For mobile auth ---> login | register
			redirect = $('#auth-redirect').val();

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
					$('.otp-timer').show();
					$('.otp-timer-resend').hide();
				} else {
					clearInterval(timerInterval);
					timerInterval = null;
					element.text("00:00");
					$('.otp-timer').hide();
					$('.otp-timer-resend').show();
				}
			}
		
			timerInterval = setInterval(updateTimer, 1000);
			updateTimer();
		}
		function switchForm(from, to) {
			$(`.auth-${from}-section`).slideUp({
				complete: function() {
					$(`.auth-${to}-section .error`).removeClass('error');
					$(`.auth-${to}-section input`).val('');
					$(`.auth-${to}-section .loading`).removeClass('loading');
					$(`.auth-${to}-section`).slideDown({
						start: function() {
							$(this).css('display', 'flex');
						},
						complete: function() {
							$(`.auth-${to}-section input`).eq(0).focus();
						}
					});
				}
			});
		}
		function fieldError( inputGroup, text ) {
			$(inputGroup + ' .input-error-text').html(text);
			$(inputGroup).addClass('error');
		}

		/*--------- Mobile login section ---------*/
		// Attach an input event listener to the element with ID "auth-mobile-input"
		$("#auth-mobile-input").on('input', function() { // Check enable/disable send OTP button
			if(drplus.validateMobile($(this).val())) {
				$("#auth-mobile-submit").removeClass('disabled').prop('disabled', false)
			} else {
				$("#auth-mobile-submit").addClass('disabled').prop('disabled', true)
			}
		})
		$(".auth-otp-input").on("input", function () { // Move between OTP fields
			// Allow only one character in the input
			let value = drplus.convertChars($(this).val());
			if (value.length > 1) {
				$(this).val(value.charAt(0));
			}
	
			// Move to the next input when a character is typed
			if (drplus.convertChars($(this).val()) !== "") {
				const nextInput = $(this).parent().next().find(".auth-otp-input");
				if (nextInput.length > 0) {
					nextInput.focus();
				}
			}
		});
		$(".auth-otp-input").on("keydown", function (e) { // Move with arrow keys or backspace
			let key = e.key,
				currentInput = $(this),
				prevInput = currentInput.parent().prev().find(".auth-otp-input");
	
			// Move to the previous input when the left arrow key is pressed
			if (key === "ArrowLeft") {
				prevInput.focus();
			}
			// Move to the next input when the right arrow key is pressed
			else if (key === "ArrowRight") {
				currentInput.parent().next().find(".auth-otp-input").focus();
			}
	
			// Clear the current input and move to the previous one on backspace
			if (key === "Backspace") {
				if (drplus.convertChars(currentInput.val()) === "") {
					prevInput.focus().val("");
				} else {
					currentInput.val("");
				}
			}
		});
		$('.auth-otp-input').on('change keyup', function() { // Check enable/disable verify btn
			// Check if all inputs are filled
			let allFilled = true;
			$(".auth-otp-input").each(function () {
				if (drplus.convertChars($(this).val()).trim() === "") {
					allFilled = false;
					return false; // Break the loop
				}
			});
	
			// Click the submit button if all inputs are filled
			if (allFilled) {
				$("#auth-verify-otp-btn").removeClass('disabled').prop('disabled', false)
				$("#auth-verify-otp-btn:not(.loading)").click();
			} else {
				$("#auth-verify-otp-btn").addClass('disabled').prop('disabled', true)
			}
		});

		/*--------- Switch forms ---------*/
		$('.auth-switch-login-btn').on('click', function(e) {
			e.preventDefault();
			switchForm('mobile', 'login');
		});
		$('.auth-switch-mobile-btn').on('click', function(e) {
			e.preventDefault();
			switchForm('login', 'mobile');
		});
		$('.lost-password-link').on('click', function(e) {
			e.preventDefault();
			$('.auth-lost_password-section-notice').hide();
			switchForm('login', 'lost_password');
		});
		$('.signup-btn').on('click', function(e) {
			e.preventDefault();
			if( $(this).closest('.auth-section').attr('data-section') == 'login' ) {
				$('.auth-signup-mobile-group').css('display', 'flex');
				switchForm('login', 'signup');
			} else {
				switchForm('mobile', 'signup');
			}
		});
		$('.auth-change-mobile').on('click', function(e) {
			e.preventDefault();
			switchForm('otp', 'mobile');
		});
		$('.auth-switch-login').on('click', function(e) {
			e.preventDefault();
			let to = 'login';
			if( !$('.auth-login-section').length && $('.auth-mobile-section').length ) {
				to = 'mobile';
			}
			switchForm($(this).closest('.auth-section').attr('data-section'), to);
		});

		/*--------- Login with email ---------*/
		$('#auth-login-username, #auth-login-password').on('input', function() {
			if($('#auth-login-username').val().trim() && $('#auth-login-password').val().trim()) {
				$('#auth-login-btn').removeClass('disabled').prop('disabled', false);
			} else {
				$('#auth-login-btn').addClass('disabled').prop('disabled', true);
			}
		})

		/*--------- Lost password ---------*/
		$('#auth-lost_password-input').on('input', function() {
			let value = drplus.convertChars($(this).val()).trim(),
				enabled = false;
			if(value.length > 0) {
				if(value.substr(0, 1) == '0' || value.substr(0, 2) == '09') {
					enabled = value.length === 11;
				} else {
					if( value.includes('@') ) {
						enabled = drplus.validateEmail(value);
					} else {
						enabled = true;
					}
				}
			}
			if(enabled) {
				$('#auth-lost_password-btn').removeClass('disabled').prop('disabled', false);
			} else {
				$('#auth-lost_password-btn').addClass('disabled').prop('disabled', true);
			}
		});

		/*--------- Register with email ---------*/
		let registerInputs = ['#auth-signup-username', '#auth-signup-email', '#auth-signup-password'];
		if( $('#auth-signup-mobile').length ) {
			registerInputs.push('#auth-signup-mobile');
		}
		let requiredRegisterInputs = [];
		registerInputs.forEach(function(inputId) {
			if($(inputId).prop("required")) {
				requiredRegisterInputs.push(inputId)
			}
		})
		$(registerInputs.join(',')).on('input', function () {
			let enabled = true;

			// Check if all registerInputs are filled
			requiredRegisterInputs.forEach(input => {
				if ($(input).val().trim() === '') {
					enabled = false;
				}
			});

			// Validate the email address
			let email = $('#auth-signup-email').val().trim();
			if( email && !drplus.validateEmail(email) ) {
				enabled = false;
			}

			let mobile = $('#auth-signup-mobile').val().trim();
			if( $('#auth-signup-mobile').length && mobile && !drplus.validateMobile(mobile)) {
				enabled = false;
			}

			// Enable or disable the button based on the validation
			if (enabled) {
				$('#auth-signup-btn').removeClass('disabled').prop('disabled', false);
			} else {
				$('#auth-signup-btn').addClass('disabled').prop('disabled', true);
			}
		});

		/*--------- Register with mobile (one_form mode is off) ---------*/


		/*--------- AJAX ---------*/
		$(".auth-send-otp").on('click', function(e) { // Send OTP
			e.preventDefault();
			let $this = $(this);

			$this.closest('.auth-section').find('.error').removeClass('error');

			if(!$this.hasClass('otp-timer-resend')) {
				$this.addClass('loading')
			}

			$('.otp-timer-resend').hide();
			$('.auth-otp-input').val('');
			$('#auth-otp-input-0').focus();
			$('#auth-verify-otp-btn').addClass('loading');
			$('.auth-mobile-group').removeClass('error');

			const currentSection = $this.attr('id') == 'auth-signup-btn' ? 'signup' : 'login';
			let mobile;
			if(currentSection == 'signup') {
				mobile = $('#auth-signup-mobile').val().replaceAll(' ', '');
			} else {
				mobile = $('#auth-mobile-input').val().replaceAll(' ', '');
			}

			function afterClick() {
				if(currentSection == 'signup') {
					switchForm('signup', 'otp');
				} else {
					switchForm('mobile', 'otp');
				}
				$('.otp-timer').show();
				$('#auth-verify-otp-btn').removeClass('loading');
				if(mode == 'login') {
					$('.auth-otp-section .auth-terms').hide();
				} else {
					$('.auth-otp-section .auth-terms').show();
				}
			}
			if( !timerInterval || lastMobile != mobile ) {
				lastMobile = mobile;
				$.ajax({
					url: drplusVars.ajaxUrl,
					type: 'post',
					data: {
						action: 'drplus_send_otp',
						mobile: mobile,
						nonce: $('.auth-mobile-section').attr('data-nonce')
					},
					success: function(res) {
						if( res.success ) {
							mode = res.data.mode;
							startTimer(res.data.mode == 'login' ? drplusAuth.otpLoginTime : drplusAuth.otpRegisterTime, '.otp-timer');
							afterClick();
						} else {
							if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined') {
								fieldError('.auth-mobile-group', res.data.message);
							}
						}
					},
					complete: function() {
						$this.removeClass('loading');
					}
				});
			} else {
				afterClick();
			}
		});
		$('.auth-verify-otp-btn').on('click', function(e) { // Verify OTP
			e.preventDefault();
			$this = $(this);
			$this.addClass('loading');
			$this.closest('.auth-section').find('.error').removeClass('error');
			$.ajax({
				url: drplusVars.ajaxUrl,
				type: 'post',
				data: {
					action: 'drplus_check_otp',
					nonce: $('.auth-otp-section').attr('data-nonce'),
					mobile: lastMobile,
					otp: $('#auth-otp-input-0').val() + $('#auth-otp-input-1').val() + $('#auth-otp-input-2').val() + $('#auth-otp-input-3').val()
				},
				success: function(res) {
					if( res.success ) {
						if( res.data.code == 'login_success' || !$('.auth-signup-section').length ) {
							window.location.href = redirect;
						} else {
							$('.auth-signup-mobile-group').hide();
							switchForm('otp', 'signup');
						}
					} else {
						if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined') {
							fieldError('.otp-fields-group', res.data.message);
						}
					}
				}
			}).done(function() {
				$this.removeClass('loading')
			});
		})
		$('.auth-login-btn').on('click', function(e) { // Login with email
			e.preventDefault();
			$this = $(this);
			$this.addClass('loading');
			$this.closest('.auth-section').find('.error').removeClass('error');
			$.ajax({
				url: drplusVars.ajaxUrl,
				type: 'post',
				data: {
					action: 'drplus_login',
					nonce: $('.auth-login-section').attr('data-nonce'),
					username: $('#auth-login-username').val(),
					password: $('#auth-login-password').val(),
					remember: $('#auth-login-rememberme').prop('checked')
				},
				success: function(res) {
					if( res.success ) {
						window.location.href = redirect;
					} else {
						if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined' && typeof res.data.code !== 'undefined') {
							switch (res.data.code) {
								case "user_not_found":
									fieldError('.auth-login-username-group', res.data.message);
									break;
							
								case "wrong_password":
									fieldError('.auth-login-password-group', res.data.message);
									break;
								default:
									$('.auth-login-section-error').html(res.data.message).addClass('error');
									break;
							}
						}
					}
				}
			}).done(function() {
				$this.removeClass('loading')
			});
		})
		$('.auth-signup-btn').on('click', function(e) { // Signup with email
			e.preventDefault();
			$this = $(this);
			$this.addClass('loading');
			$this.closest('.auth-section').find('.error').removeClass('error');
			let mobile = $('#auth-signup-mobile').val();
			
			if( (drplusAuth.mobileOneForm && $('#auth-mobile-input').length) || drplusAuth.emailAuth) {
				mobile = $('#auth-mobile-input').val();
			}
			
			$.ajax({
				url: drplusVars.ajaxUrl,
				type: 'post',
				data: {
					action: 'drplus_signup',
					nonce: $('.auth-signup-section').attr('data-nonce'),
					username: $('#auth-signup-username').val(),
					email: $('#auth-signup-email').val(),
					password: $('#auth-signup-password').val(),
					mobile: mobile,
				},
				success: function(res) {
					if( res.success ) {
						window.location.href = redirect;
					} else {
						if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined' && typeof res.data.code !== 'undefined') {
							switch (res.data.code) {
								case "username_exists":
									fieldError('.auth-signup-username-group', res.data.message);
									break;
							
								case "email_exists":
									fieldError('.auth-signup-email-group', res.data.message);
									break;

								case "mobile_exists":
									fieldError('.auth-signup-mobile-group', res.data.message);
									break;

								default:
									$('.auth-signup-section-error').html(res.data.message).addClass('error');
									break;
							}
						}
					}
				}
			}).done(function() {
				$this.removeClass('loading')
			});
		})
		$('.auth-lost_password-btn').on('click', function(e) { // Signup with email
			e.preventDefault();
			$this = $(this);
			$this.addClass('loading');
			$this.closest('.auth-section').find('.error').removeClass('error');
			$('.auth-lost_password-section-notice').hide();
			$.ajax({
				url: drplusVars.ajaxUrl,
				type: 'post',
				data: {
					action: 'drplus_lost_password',
					nonce: $('.auth-lost_password-section').attr('data-nonce'),
					entry: $('#auth-lost_password-input').val(),
				},
				success: function(res) {
					if( res.success ) {
						$('.auth-lost_password-section-notice').html(res.data.message).show();
					} else {
						if(typeof res.data !== 'undefined' && typeof res.data.message !== 'undefined') {
							fieldError('.auth-lost_password-group', res.data.message);
						}
					}
				}
			}).done(function() {
				$this.removeClass('loading')
			});
		})

		// Enter on submit btn
		$('.auth-section input').on('keydown', function(e) {
			if(e.keyCode == 13) {
				e.preventDefault();
				$(this).closest('.auth-section').find('.auth-section-submit-btn:not(.disabled):not(.loading)').click();
			}
		});
	});
})(jQuery);