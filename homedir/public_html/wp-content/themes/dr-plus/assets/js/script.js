(function($) {
	let handleResize = function() {
		let width = window.innerWidth,
			body = $('body'),
			classes = {desktop: 'desktop', tablet: 'tablet', mobile: 'mobile'};

		if(width > 1200) {
			body.removeClass([classes.tablet, classes.mobile]).addClass(classes.desktop);
			body.attr('data-device', 'desktop');
		} else if( width <= 1200 && width > 768 ) {
			body.removeClass([classes.desktop, classes.mobile]).addClass(classes.tablet);
			body.attr('data-device', 'tablet');
		} else {
			body.removeClass([classes.desktop, classes.tablet]).addClass(classes.mobile);
			body.attr('data-device', 'mobile');
		}

		if (body.hasClass('sticky-header')) {
			const headerHeight = $('#header-container').outerHeight();
			if (headerHeight) {
				body.css('--header-height', headerHeight + 'px');
			}
		}
	};

	$(window).on('load resize', handleResize);

	$(document).ready(function(){
		// Set sticky header class
		$(window).on('scroll', function() {
			let classes = ['scrolled'];
			if( $('body').hasClass('sticky-header') ) {
				classes.push('sticky-header-active');
			}
			if( $(window).scrollTop() > 10 ) {
				$('body').addClass(classes);
			} else {
				$('body').removeClass(classes);
			}
		}).trigger('scroll');

		// Toggle mobile menu
		// Open/Close mobile menu
		function openMobileMenu() {
			$('#header_inner').removeAttr('style');
			$('body').addClass('mobile-menu-opened');
			$('#drplus-header-overlay').fadeIn();
			$('.header-mobile-menu-wrap').removeClass('closed').slideDown();
			$('.header-mobile-menu-toggle-icons').removeClass('closed');
		}
		function closeMobileMenu() {
			$('body').removeClass('mobile-menu-opened');
			$('#drplus-header-overlay').fadeOut();
			$('.header-mobile-menu-wrap').addClass('closed').slideUp({
				complete: function() {
					// Fix header height variable
					$('body.sticky-header').css('--header-height', $('#header-container').outerHeight() + 'px');
				}
			});
			$('.header-mobile-menu-toggle-icons').addClass('closed');
		}
		$('.header-mobile-menu-toggle-icons').on('click', function(e) {
			e.preventDefault();			
			if(!$('body').hasClass('mobile-menu-opened')) {
				openMobileMenu();
			} else {
				closeMobileMenu();
			}
		});
		$('#drplus-header-overlay').on('click', closeMobileMenu);

		$(document).on('click', 'body.mobile li.menu-item-has-children:not(.menu-item-active) > a', function(e) {
			e.preventDefault();
			$(this).addClass('menu-item-active');
			$(this).closest('li').addClass('menu-item-active');
			$(this).siblings('ul').slideDown({
				start: function() {
					$(this).css('display', 'flex')
				}
			});
		})
		// $('body.mobile li.menu-item-has-children:not(.menu-item-active) > a').on('click', function(e) {
		// 	if($(this).hasClass('menu-item-active')) return;

		// 	e.preventDefault();
		// 	$(this).addClass('menu-item-active');
		// 	$(this).closest('li').toggleClass('menu-item-active');
		// 	$(this).siblings('ul').slideToggle({
		// 		start: function() {
		// 			$(this).css('display', 'flex')
		// 		}
		// 	});
		// });

		// Toggle minicart in mobile
		$(document).on('click', '.header-cart-wrap.mobile-mode:not(.active)', function(e) {
			e.preventDefault();
			$(this).toggleClass('active');
		});
		$(document).on('click', function (e) {
			if (!$(e.target).closest('.header-cart-wrap.mobile-mode').length) {
				$('.header-cart-wrap.mobile-mode').removeClass('active');
			}
		});

		// Toggle account in mobile
		$(document).on('click', '.header-account-wrap.has-btn-arrow', function(e) {
			e.preventDefault();
			$(this).toggleClass('active');
		});
		$(document).on('click', '.header-account-wrap.has-btn-arrow .account-item-link', function(e) {
			e.stopPropagation();
		});
		$(document).on('click', function (e) {
			if (!$(e.target).closest('.header-account-wrap.has-btn-arrow').length) {
				$('.header-account-wrap.has-btn-arrow').removeClass('active');
			}
		});

		$(document).on('click', '.header-menu-wrap.has-btn-arrow .menu-item-has-children:not(.active)', function(e) {
			e.preventDefault();
			$(this).toggleClass('active');
		});
		$(document).on('click', function (e) {
			if (!$(e.target).closest('.header-menu-wrap.has-btn-arrow .menu-item-has-children').length) {
				$('.header-menu-wrap.has-btn-arrow .menu-item-has-children').removeClass('active');
			}
		});

		// Auto focus on input within the input wrap
		$(document).on('click', '.input-wrap', function() {
			$(this).find('input, select, textarea').focus().trigger('focus');
		})

		let dropzones = {};

		//***** Show/Hide password *****//
		$(document).on('click', '.show-password', function() {
			$(this).siblings("input[type='password']").attr('type', 'text');
		})
		$(document).on('click', '.hide-password', function() {
			$(this).siblings("input[type='text']").attr('type', 'password');
		})

		//***** Stars *****//
		$('.drplus_stars-has-radio .drplus_star').on('click', function() {
			let star = $(this).index(),
				stars = $(this).closest('.drplus_stars'),
				activeClass = 'drplus_star-active';

			$(this).prev().prop('checked', true); // Checked the radio
			
			stars.find('.drplus_star').removeClass(activeClass);
			for( let index = 0; index <= star+1; index++ ) {
				stars.find(`.drplus_star:nth-child(${index})`).addClass(activeClass);
			}
		});

		//***** Accordion *****//
		if($('.accordion-items').length) {
			$('.accordion-item-head').on('click', function() {
				let prevItem = $(this).closest('.accordion-items').find('.accordion-item-default, .accordion-item-active'),
					item = $(this).closest('.accordion-item'),
					itemIsActive = item.hasClass('accordion-item-default') || item.hasClass('accordion-item-active'); // Before removing classes
				prevItem.find('.accordion-item-content').slideUp({
					complete: function() {
						prevItem.removeClass(['accordion-item-default', 'accordion-item-active']);
					}
				});
				if( !itemIsActive ) {
					item.addClass('accordion-item-active');
					item.find('.accordion-item-content').slideDown();
				}
			})
		}

		//***** Clinics grid *****//
		function clinicsGrid() {
			$('.clinics').each(function() {
				let device = $('body').attr('data-device'),
					cols = parseInt($(this).attr(`data-${device}-cols`)),
					evenCols = cols % 2 === 0;
				
				$(this).removeClass(['even-mode', 'odd-mode']).addClass(evenCols ? 'even-mode' : 'odd-mode');
				$(this).find('.clinic-empty').each(function(index, item) {
					let row, col;
					row = Math.floor(index / cols);
					col = index % cols;

					if( evenCols ) {
						if( col === cols-1 ) {
							row++;
						}
					} else {
						if( row % 2 !== 0 && col % 2 !== 0 ) {
							row++;
						}
					}

					let hide;
					if( evenCols ) { // زوج
						hide = ( row % 2 === 0 ) && (index % 2 !== 0) || (row % 2 !== 0 && index % 2 === 0);
					} else {
						hide = ( row % 2 === 0 ) && (index % 2  === 0);
					}

					if( hide ) {
						$(item).hide();
					} else {
						$(item).show();
					}

					if($(item).is(":last-child")) {
						$(item).prev().prev().addClass('clinic-last');
					}
				});

				$(this).css('display', 'flex');
			});
		}
		window.addEventListener('load', clinicsGrid);
		let clinicsGridBreakpoints = {
			desktop: 1201,
			tablet: 769,
			mobile: 0,
		};
		for(const [key, breakpoint] of Object.entries(clinicsGridBreakpoints)) {
			window.matchMedia(`(min-width: ${breakpoint}px)`).addEventListener('change', clinicsGrid);
		}

		//***** Search widget *****//
		let searchResultsCache = {};
		function showSearchResults(results, value) {
			drplusSearchInput.reset();
			searchResultsCache[value] = results;
			if( results.length ) {
				results.map(item => {
					drplusSearchInput.addPopoverItem(item);
				});
			} else {
				drplusSearchInput.showEmpty();
			}
			drplusSearchInput.showPopover('hide');
		}
		$(document).on('input', '.drplus-search-text.drplus-search-with-ajax', function() {
			let $this = $(this),
				value = $this.val();
			if( value && value.length >= 2 ) {
				drplusSearchInput.activeInput = $this[0];
				if( typeof searchResultsCache[value] == 'undefined' ) {
					drplusSearchInput.showPopover( 'show' );
					$.ajax({
						url: drplusVars.ajaxUrl,
						type: 'POST',
						data: {
							action: 'drplus_search',
							text: value,
							nonce: $this.attr('data-nonce'),
							excludes: $(this).attr('data-excludes'),
							only: $(this).attr('data-only'),
						},
						success: function(res) {
							if(res.success) {
								showSearchResults(res.data, value);
							}
						}
					});
				} else {
					showSearchResults(searchResultsCache[value], value);
				}
			}
		});
		// Prevent links in search result if the active input has the alt field
		$(document).on('click', 'a.drplus-search-input-popover-item', function(e) {
			let activeInput = $(drplusSearchInput.activeInput),
				altInput = $(drplusSearchInput.activeInput).parent().siblings('.drplus-search-input-alt');
			if(altInput.length) {
				e.preventDefault();
				altInput.val($(this).attr('data-value'));
				activeInput.val($(this).find('.drplus-search-input-popover-item-title').text());
				drplusSearchInput.hidePopover();
			}
		})

		//***** Select *****//
		$(document).on('click', function(e) {
			// Check if the clicked element is outside the .drplus-select
			if (!$(e.target).closest('.drplus-select').length) {
				// Remove the .show class from all .drplus-select elements
				$('.drplus-select').removeClass('show');
			}
		});
		$('.drplus-select-head').on('click', function(e) {
			e.stopPropagation();
			$(this).parent().toggleClass('show');
		});
		$('.drplus-select-option').on('click', function(e) {
			e.stopPropagation();
			$(this).closest('.drplus-select').removeClass('show')
		});

		// Select sort item
		$('.sort-wrap .drplus-select-option').on('click', function(e) {
			e.preventDefault();			
			$(this).closest('form').find('.orderby').val($(this).attr('data-value'));
			$(this).closest('form').submit();
		})

		// Show/Hide password
		$('.password-icon').on('click', function() {
			$(this).siblings('input').attr('type', $(this).hasClass('show-password') ? 'text' : 'password');
		});

		//***** Hospital city filter *****//
		$('.hospital-city-filter').on('change', function() {
			$(this).closest('form').submit();
		})

		//***** Repeater *****//
		let repeaters = [];
		$('.repeater').each(function(index, repeater) {
			let swapyRepeater = Swapy.createSwapy(repeater);
			repeaters.push( {
				swapy: swapyRepeater,
				lastIndex: $(this).find('.repeater-slot').length
			} );
			$(this).attr('data-repeater-index', index)
			swapyRepeater.onSwapEnd(function() {
				drplus.updateRepeaterIndexes(repeater, {
					item: 'repeater-item'
				});
			});
		});
		// Create new item
		function newRepeaterSlot(repeater) {
			let repeaterIndex = repeater.attr('data-repeater-index'),
				template = wp.template(repeater.attr('data-template_id')),
				html = template({index: repeaters[repeaterIndex].lastIndex});
			
			if(!repeater.find('.repeater-new-slot-btn').length) {
				$(html).appendTo(repeater);
			} else {
				$(html).insertBefore(repeater.find('.repeater-new-slot-btn'));
			}
			repeaters[repeaterIndex].lastIndex++;
			drplus.updateRepeaterIndexes(repeater, {
				item: 'repeater-item'
			});
			repeaters[repeaterIndex].swapy.update();

			drplus.initSelect2();
			drplus.initProvinceSelector();

			dropzones = drplus.initDropzone(dropzones);
		}
		$(document).on('input', '.repeater-auto .repeater-slot:last-child input, .repeater-auto .repeater-slot:last-child textarea, .repeater-auto .repeater-slot:last-child select', function() {
			if( !$(this).val() ) return;
			newRepeaterSlot($(this).closest('.repeater'));
		})
		$(document).on('click', '.repeater-new-slot-btn', function(e) {
			e.preventDefault();
			newRepeaterSlot($(this).closest('.repeater'));
		})
		// Remove repeater slot
		$(document).on('click', '.repeater-slot .repeater-remove', function() {
			let repeater = $(this).closest('.repeater'),
				repeaterIndex = repeater.attr('data-repeater-index');
			if(!repeater.hasClass('repeater-manual') && repeater.find('.repeater-slot').length === 1) return;
			$(this).closest('.repeater-slot').remove();
			drplus.updateRepeaterIndexes(repeater, {
				item: 'repeater-item'
			});
			repeaters[repeaterIndex].swapy.update();
		})

		/* Specialities - filter widget */
		$('.drplus-specialities-filter-item-checkbox').on('change', function() {
			$(this).closest('form').submit();
		})

		//***** Book & Consult form widget *****//
		$('.drplus-foreign-checkbox').on('change', function() {
			let wrap = $(this).closest('.input-group'),
				field = wrap.find('.drplus-nid-input');
			if($(this).prop('checked')) {
				field.attr('disabled', true).addClass('disabled').val("");
				wrap.removeClass('drplus_form_error')
			} else {
				field.removeAttr('disabled').removeClass('disabled');
			}
		});
		// Check nid
		$('.drplus-book-form-widget-nid').on('change', function() {
			let error = false,
				wrap = $(this).closest('.input-group'),
				val = $(this).val(),
				button = $(this).closest('form').find('button[name="booking_widget_submit"]');
			if(val && !drplus.validateIDCode(val)) {
				error = true;
			}

			if(error) {
				wrap.addClass('error');
				wrap.find('.input-error-text').text(drplusVars.i18n.wrongIDCode)
			} else {
				wrap.removeClass('error');
			}
			button.prop('disabled', error)
		});
		// Check mobile
		$('.drplus-book-form-widget-phone.drplus-phone-input').on('change', function() {
			let error = false,
				wrap = $(this).closest('.input-group'),
				val = $(this).val(),
				button = $(this).closest('form').find('button[name="booking_widget_submit"]');
			if(val && !drplus.validateMobile(val)) {
				error = true;
			}

			if(error) {
				wrap.addClass('error');
				wrap.find('.input-error-text').text(drplusVars.i18n.wrongMobile)
			} else {
				wrap.removeClass('error');
			}
			button.prop('disabled', error)
		});

		$('.specialists-type-filter-item-checkbox').on('change', function() {
			$('.specialists-type-filter-item-checkbox').not(this).prop('checked', false);
			if ($(this).is(':checked')) {
				let link = $(this).closest('a').attr('href');
				if (link) {
					window.location.href = link;
				}
			}
		});

		//***** Specialists search *****//
		$('.specialists-search-input-select-city').on('click', function(e) {
			e.preventDefault();
			$(this).siblings('.specialists-search-city-popup').fadeToggle({
				start: function(){$(this).css('display', 'flex')}
			});
		})
		let cityResultsCache = {}, citiesAjax;
		function showCitySearchResults(results, value, $results) {
			$results.removeClass(['empty', 'loading']);
			cityResultsCache[value] = results;
			if( Object.keys(results).length ) {
				for(const [locationID, location] of Object.entries(results)) {
					let html = `<button type="button" class="specialists-search-city-popup-result fullwidth" data-id="${location.slug}"><div class="specialists-search-city-popup-result-city">${location.name}</div><div class="specialists-search-city-popup-result-province">${location.province}</div><i class="specialists-search-city-popup-result-arrow drplus-icon-${drplusVars.isRtl ? 'left' : 'right'}"></i></button>`;
					$results.append(html);
				}
			} else {
				$results.addClass('empty');
			}
		}
		$('.specialists-search-city-popup-search').on('input', function() {
			let $this = $(this),
				$results = $this.closest('.specialists-search-city-popup').find('.specialists-search-city-popup-results'),
				value = $this.val();
			if( value && value.length >= 2 ) {
				$results.addClass('loading');
				$results.find('.specialists-search-city-popup-result').remove();
				if( typeof cityResultsCache[value] == 'undefined' ) {
					$results.addClass('loading');
					if( typeof citiesAjax != 'undefined' ) {
						citiesAjax.abort();
					}
					citiesAjax = $.ajax({
						url: drplusVars.ajaxUrl,
						type: 'POST',
						data: {
							action: 'drplus_search_cities',
							text: value,
							nonce: $this.attr('data-nonce'),
						},
						success: function(res) {
							if(res.success) {
								showCitySearchResults(res.data, value, $results);
							}
						}
					});
				} else {
					showSearchResults(cityResultsCache[value], value, $results);
				}
			} else {
				$results.removeClass(['empty', 'loading']);
			}
		})
		// Select a city
		function selectCityFromSearchSpecialistsPopup(element, city, text) {
			let $form = $(element).closest('.specialists-search');
			$form.find('.specialists-search-input-city').val(city);
			$form.find('.specialists-search-input-select-city-name').text(text);
			$form.find('.specialists-search-city-popup').fadeOut();
		}
		$(document).on('click', '.specialists-search-city-popup-result', function(e) {
			e.preventDefault();
			selectCityFromSearchSpecialistsPopup(this, $(this).attr('data-id'), $(this).find('.specialists-search-city-popup-result-city').text());
		});
		// Select all cities
		$('.specialists-search-city-popup-all-cities').on('click', function(e) {
			e.preventDefault();
			selectCityFromSearchSpecialistsPopup(this, '', drplusFront.i18n.allCities);
		})
		// Hide select city popup
		$(document).on('click', function (e) {
			if(!$(e.target).closest('.specialists-search-input-select-city,.specialists-search-city-popup').length || $(e.target).closest('.specialists-search-city-popup-close').length) {
				$('.specialists-search-city-popup').fadeOut();
			}
		});

		/* Map popup */
		$(document).on('click', '.map-popup-opener', function() {
			$('.map-popup, .map-popup-overlay').fadeIn();
			$('#map-popup-title').text($(this).attr('data-title'));
		})
		$('.map-popup-close, .map-popup-overlay').on('click', function(e) {
			e.preventDefault();
			$('.map-popup, .map-popup-overlay').fadeOut();
		});
		// Fullscreen iframe
		$('.map-popup-maximize').on('click', function(e) {
			e.preventDefault();
			let iframe = $('.map-popup-iframe').eq(0).get(0);
			
			// بررسی اینکه مرورگر از Fullscreen API پشتیبانی می‌کند
			if (iframe.requestFullscreen) {
				iframe.requestFullscreen();
			} else if (iframe.webkitRequestFullscreen) { // برای Safari
				iframe.webkitRequestFullscreen();
			} else if (iframe.mozRequestFullScreen) { // برای Firefox
				iframe.mozRequestFullScreen();
			} else if (iframe.msRequestFullscreen) { // برای IE/Edge قدیمی
				iframe.msRequestFullscreen();
			}
		})

		clinicsGrid();
	});
})(jQuery);