drplus = {
	convertChars: function(string) {
		string = string.toString();
		let replaces = {
			'۰'	: '0',
			'۱'	: '1',
			'۲'	: '2',
			'۳'	: '3',
			'۴'	: '4',
			'۵'	: '5',
			'۶'	: '6',
			'۷'	: '7',
			'۸'	: '8',
			'۹'	: '9',
			'٪'	: '%',
			'÷'	: '/',
			'×'	: '*',
			'-'	: '-',
			'ـ'	: '_',
			'ي'	: 'ی',
			'ك'	: 'ک',
		}
		return string.replace(/[۰۱۲۳۴۵۶۷۸۹٪÷×ـيك]/g, match => replaces[match]);
	},
	formatPrice: function(price, decimals = null) {
		price = String(price);
		let original = drplus.convertChars(price).replace(/\s+/g, '').replaceAll(',',''),
			addLastCharDot = price.slice(-1) == "." && price.includes('.');
		price = parseFloat(original);
		if(isNaN(price)) return "";
		if( decimals !== null && ( original.includes('0.0') || !Number.isInteger(price) ) ) {
			let priceParts = original.split('.');
			if(priceParts[1] && priceParts[1].length > decimals) {
				priceParts[1] = priceParts[1].slice(0, decimals);
			}
			priceParts[0] = parseInt(priceParts[0]).toLocaleString();
			
			return parseFloat(priceParts.join("."));
		} else {
			price = price.toLocaleString();
			return addLastCharDot ? `${price}.` : price;
		}
	},
	validateEmail: function(email) {
		// Regular expression to validate email addresses
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		
		// Test the email against the regex and return the result
		return emailRegex.test(drplus.convertChars(email).trim());
	},
	validateIDCode: function(string) {
		string = drplus.convertChars(string).trim();
		// Check if the string is exactly 10 digits
		if (!/^[0-9]{10}$/.test(string)) {
			return false;
		}

		// Reject strings with all identical digits (e.g., "0000000000", "1111111111", etc.)
		for (let i = 0; i < 10; i++) {
			const pattern = new RegExp('^' + i + '{10}$');
			if (pattern.test(string)) {
			return false;
			}
		}

		// Calculate the weighted sum for the first 9 digits
		let sum = 0;
		for (let i = 0; i < 9; i++) {
			sum += (10 - i) * parseInt(string.charAt(i), 10);
		}
		
		// Determine the control digit based on modulus 11
		const ret = sum % 11;
		const parity = parseInt(string.charAt(9), 10);

		// Check the validity of the last digit against the calculated value
		if ((ret < 2 && ret === parity) || (ret >= 2 && ret === 11 - parity)) {
			return true;
		}

		return false;
	},
	validateMobile: function(value) {
		value = drplus.convertChars(value).trim().replaceAll(' ', '');
		return value.substr(0, 2) == '09' && /^[0-9]{11}$/.test(value);
	},

	validateCardNumber: function(cardNumber) {
		// حذف فاصله‌ها و کاراکترهای غیر عددی
		cardNumber = cardNumber.replace(/\s+/g, '').replace(/\D/g, '');

		// بررسی طول کارت
		if (cardNumber.length !== 16) {
			return false;
		}

		// الگوریتم لانه (Luhn)
		let sum = 0;
		for (let i = 0; i < 16; i++) {
			let digit = parseInt(cardNumber[i]);
			if (i % 2 === 0) {
				digit *= 2;
				if (digit > 9) digit -= 9;
			}
			sum += digit;
		}

		return (sum % 10) === 0;
	},

	validateShabaNumber: function(shaba) {
		shaba = shaba.toUpperCase().replace(/\s+/g, '');
		
		// افزودن IR اگر فقط ۲۴ رقم عددی داده شده
		if (/^\d{24}$/.test(shaba)) {
			shaba = 'IR' + shaba;
		}

		// بررسی طول و شروع با IR
		if (!/^IR\d{24}$/.test(shaba)) {
			return false;
		}

		// جابجا کردن ۴ کاراکتر اول به انتهای رشته
		const rearranged = shaba.slice(4) + shaba.slice(0, 4);

		// تبدیل کاراکترها به عدد
		const numericshaba = rearranged.replace(/[A-Z]/g, (char) => {
			return (char.charCodeAt(0) - 55).toString(); // A=10, B=11, ..., Z=35
		});

		// استفاده از تقسیم عددی بزرگ (mod 97)
		let remainder = numericshaba;
		while (remainder.length > 2) {
			const part = remainder.slice(0, 9);
			remainder = (parseInt(part, 10) % 97).toString() + remainder.slice(part.length);
		}

		return parseInt(remainder, 10) % 97 === 1;
	},
	updateRepeaterIndexes: function(wrap, classes = {}, keyFromZero = true) {
		let $ = jQuery;
		classes.item ??= 'specialist_repeater_item';
		classes.index ??= 'specialist_repeater-index';

		$(wrap).find(`.${classes.item}`).each(function(index) {
			let key = index;
			if(!keyFromZero) key++;
			$(this).find(`.${classes.index}`).html(index+1);
			$(this).find('input, textarea, select').each(function() {
				$(this).attr('name', $(this).attr('name').replace(/\d+/g, key));
				if( $(this).attr('id') ) {
					$(this).attr('id', $(this).attr('id').replace(/\d+/g, key));
				}
				if($(this).attr('data-city-selector')) {
					$(this).attr('data-city-selector', $(this).attr('data-city-selector').replace(/\d+/g, key));
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
	},
	initDropzone: function(dropzones) {
		let $ = jQuery;
		$('.drplus-dropzone:not(.drplus-dropzone-initiated)').each(function () {
			let $this = $(this),
				wrap = $this.closest('.drplus-dropzone-wrap'),
				index = $this.attr('data-index'),
				maxFileSize = $this.data('max') / 1024 / 1024; // Convert bytes to MB
			
			dropzones[index] = new Dropzone(this, {
				url: drplusVars.ajaxUrl,
				paramName: 'file',
				maxFilesize: maxFileSize, // Max file size in MB
				acceptedFiles: 'image/*',
				dictDefaultMessage: drplusVars.i18n.dropzone.dictDefaultMessage,
				maxFiles: 1,
				thumbnailWidth: null,
				thumbnailHeight: null,
				clickable: true,
				headers: {
					"X-WP-Nonce": drplusVars.nonces.dropzone
				},
				init: function() {
					this.on('sending', function(file, xhr, formData) {
						formData.append('action', 'drplus_upload');
						formData.append('nonce', drplusVars.nonces.dropzone)
					})
				},
				success: function (file, response) {
					if( response.success ) {
						wrap.find('input[type="hidden"]').val(response.data.id).trigger('change');
						let template = wp.template(`drplus-dropzone-current-value`),
							html = template({
								img: response.data.url,
								filename: response.data.filename,
								size: response.data.size
							});
						wrap.find('.drplus-dropzone-current-value').remove();
						$(html).appendTo(wrap);
					}
				},
				error: function (file, response) {
					console.error("Upload failed:", response);
				}
			});
			dropzones[index].on('maxfilesexceeded', function(file) {
				dropzones[index].removeFile(file);
			})
			$(this).addClass('drplus-dropzone-initiated');
		});
		return dropzones;
	},
	initSelect2: function() {
		if(jQuery('.drplus-select2:not([data-select2-id])').length) {
			jQuery('.drplus-select2:not([data-select2-id])').select2({
				width: '25em',
			});
		}
	},
	initProvinceSelector: function() {
		let $ = jQuery;
		if($('.drplus-province-selector:not([data-select2-id])').length) {
			$('.drplus-province-selector:not([data-select2-id])').select2({
				width: '25em',
			});
		}
		$('.drplus-province-selector').on('select2:select', function() {
			let cityElement = $($(this).attr('data-city-selector'));
			cityElement.empty();
			drplusCities[$(this).val()].forEach(function(city) {
				cityElement.append(`<option value="${city.id}">${city.name}</option>`);
			})
		})
	},
	IRdayIndex: function(day) {
		switch(day) {
			case 0:
				return 2;
			case 1:
				return 3;
			case 2:
				return 4;
			case 3:
				return 5;
			case 4:
				return 6;
			case 5:
				return 7;
			case 6:
				return 1;
		}
	},
	USdayIndex: function(day) {
		switch(day) {
			case 1:
				return 6;
			case 2:
				return 0;
			case 3:
				return 1;
			case 4:
				return 2;
			case 5:
				return 3;
			case 6:
				return 4;
			case 7:
				return 5;
		}
	},
	parseTime: function(timeStr) {			
		const [hours, minutes] = timeStr.split(':').map(Number);
		const date = new Date("1970-01-01T00:00:00Z"); // Set a fixed date
		date.setUTCHours(hours, minutes, 0, 0); // Use UTC to ensure only time is represented
		return date;
	},
	uniqueId: function(length = 8) {
		const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * characters.length));
		}
		return result;
	},
	addZero: function(number) {
		if (typeof number === 'number' && number < 10) {
			if (number >= 0) {
				return `0${parseFloat(number)}`;
			} else {
				return `-0${Math.abs(number)}`;
			}
		}
		return number;
	},
	formatTime: function(seconds) {
		let minutes = Math.floor(seconds / 60);
		let secs = Math.floor(seconds % 60);
		return drplus.addZero(minutes) + ':' + drplus.addZero(secs);
	},
	chatRequest: async function(path, options = {}, restNonce = null) {
		const headers = options.headers || {};

		if (restNonce) {
			headers['X-WP-Nonce'] = restNonce;
		}

		const response = await fetch(path.replace(/^\/+/, ''), {
			credentials: 'same-origin',
			...options,
			headers,
		});

		const data = await response.json().catch(() => null);

		if (!response.ok) {
			const message =
				data && data.message
					? data.message
					: 'Request failed.';

			throw {
				status: response.status,
				code: data && data.code ? data.code : 'request_failed',
				message,
				data,
			};
		}

		return data;
	}
}