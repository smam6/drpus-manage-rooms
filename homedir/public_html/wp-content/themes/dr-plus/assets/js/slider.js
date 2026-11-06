const drplusSlider = {
	wrapClass: 'drplus-slider-wrap',
    elements: [],

    init: function(slidersElement = null, execImmediately = false) {
        const targetElements = slidersElement || document.getElementsByClassName(this.wrapClass);
        if (targetElements.length) {
            this.elements = Array.from(targetElements);
            
            // اجرای اولیه
			if( !execImmediately ) {
            	window.addEventListener('load', () => this.checkSliderSettings());
			} else {
				this.checkSliderSettings();
			}

            // شنود بهینه تغییر سایز مرورگر
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => this.checkSliderSettings(), 150);
            });
        }
    },

    getCurrentDevice: function() {
        const width = Math.min( window.outerWidth, window.innerWidth );
        if (width >= this.breakpoints.desktop) return 'desktop';
        if (width >= this.breakpoints.tablet) return 'tablet';
        return 'mobile';
    },

    disableSlider: function(element) {
        if (element.swiper) {
            element.swiper.destroy(true, true);
            delete element.swiper;
        }

        element.classList.remove('swiper');
        element.querySelectorAll('.swiper-wrapper, .swiper-slide').forEach(el => {
            el.classList.remove('swiper-wrapper', 'swiper-slide');
        });

        const beforeStyles = element.getAttribute('data-before-styles');
        if (beforeStyles) {
            element.style.cssText = beforeStyles;
        }
    },

    checkSliderSettings: function() {
        const $ = jQuery;
        const currentDevice = this.getCurrentDevice();

        this.elements.forEach((element, index) => {
            const $element = $(element);
            
            // پارس کردن تنظیمات و کش کردن آن روی المنت برای پرفورمنس عالی
            let elementSettings = element._cachedSettings;
            if (!elementSettings) {
                try {
                    elementSettings = JSON.parse(element.getAttribute('data-settings')) || {};
                    element._cachedSettings = elementSettings;
                } catch (e) { return; }
            }

            let options = structuredClone(this.defaultOptions);
            Object.assign(options, elementSettings.slider || {});
            this.prepareAutoplay(options);

            // ایجاد شناسه یکتا برای اسلایدرها
            if (!element.dataset.index) {
                element.dataset.index = index;
                element.classList.add(`${this.wrapClass}-${index}`);
            }

            // بررسی و اعمال تنظیمات واکنش‌گرا (Breakpoints)
            for (const [device, size] of Object.entries(this.breakpoints)) {
                const deviceSettings = elementSettings[device]?.slider;
                if (deviceSettings?.enabled) {
                    options.breakpoints = options.breakpoints || {};
                    options.breakpoints[size] = this.prepareAutoplay({...deviceSettings});

                    if (currentDevice === device && deviceSettings.direction) {
                        if (element.swiper && element.swiper.params.direction !== deviceSettings.direction) {
                            this.disableSlider(element);
                        }
                        options.direction = deviceSettings.direction;
                    }
                }
            }

            const isSwiperActive = element.classList.contains('swiper');
            const deviceEnabled = elementSettings[currentDevice]?.slider?.enabled;

            if (deviceEnabled) {
                if (!isSwiperActive) {
                    this.enableSwiperClasses($element);
                }

                // ۱. تنظیم دکمه‌های ناوبری (Navigation)
                this.setupNavigation(element, options, element.dataset.index);

                // ۲. بازگردانی و اعمال ویژگی کنترلر شستی (Thumbs Swiper)
                if (typeof options.thumbs !== 'undefined') {
                    const prevElement = $element.prev()[0];
                    // مطمئن می‌شویم المان قبلی وجود دارد و نمونه Swiper روی آن فعال است
                    if (prevElement && prevElement.swiper) {
                        options.thumbs = {
                            swiper: prevElement.swiper // ارجاع مستقیم به نمونه Swiper اسلایدر قبلی
                        };
                    } else {
                        // اگر به هر دلیلی اسلایدر قبلی هنوز آماده نبود، ویژگی Thumbs را موقتا حذف می‌کنیم تا باگی رخ ندهد
                        delete options.thumbs;
                    }
                }

                // مقداردهی اولیه یا بروزرسانی زنده
                try {
                    if (!element.swiper) {
                        element.swiper = new Swiper(element, options);
                    } else if (options.reInit) {
                        element.swiper.destroy(true, true);
                        element.swiper = new Swiper(element, options);
                    } else {
                        element.swiper.update();
                    }
                } catch (err) { 
                    console.error("Swiper Init Error:", err); 
                }

            } else if (isSwiperActive) {
                this.disableSlider(element);
            }
        });
    },

    enableSwiperClasses: function($el) {
        $el.addClass('swiper');
        $el.find('.wrapper').addClass('swiper-wrapper');
        $el.find('.slider-slide').addClass('swiper-slide');
        
        const el = $el[0];
        if (!el.getAttribute('data-before-styles') && el.getAttribute('style')) {
            el.setAttribute('data-before-styles', el.getAttribute('style'));
        }
    },

    setupNavigation: function(element, options, index) {
        if (!element.querySelector('.swiper-button-next')) {
            delete options.navigation;
        } else if (options.navigation) {
            const prefix = `.${this.wrapClass}-${index}`;
            options.navigation.nextEl = `${prefix} .swiper-button-next`;
            options.navigation.prevEl = `${prefix} .swiper-button-prev`;
        }
    },

    prepareAutoplay: function(options) {
        if (options?.autoplay?.delay) {
            options.autoplay.delay = parseInt(options.autoplay.delay) * 1000;
        } else {
            delete options.autoplay;
        }
        return options;
    },

    breakpoints: { desktop: 1201, tablet: 769, mobile: 0 },
    defaultOptions: {
        direction: 'horizontal',
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    }
};

// اجرای بهینه در وردپرس
jQuery(document).ready(() => {
    drplusSlider.init();
    
    // بهینه‌سازی لایت‌باکس با Event Delegation
    jQuery(document).on('click', 'a[data-elementor-open-lightbox="yes"]', function() {
        setTimeout(() => {
            jQuery('.elementor-lightbox-item img[data-src]').each(function() {
                this.src = this.getAttribute('data-src');
                this.classList.remove('swiper-lazy');
            });
        }, 50);
    });
});