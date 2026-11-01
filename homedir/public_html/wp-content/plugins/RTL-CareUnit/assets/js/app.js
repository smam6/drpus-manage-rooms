(function ($) {
    $(document).ready(function () {
        // ------------------------------------------------------------------------------------------------------ slider
        $('.sidebar__list-area').click(function () {
            $('.sidebar__list-area').removeClass('active');
            $(this).addClass('active');
            $('.sidebar__list-submenu').removeClass('show');
            $(this).find('.sidebar__list-submenu').addClass('show');
        });

        // -------------------------------------------------------------------------------------------------- accordions

        $(".product-card__accordion-title-text").on("click", function (e) {
            e.preventDefault();
            let $this = $(this);

            if (!$this.hasClass("accordion-active")) {
                $(".product-card__accordion-content").slideUp(400);
                $(".product-card__accordion-title").removeClass("accordion-active");
            }

            $this.toggleClass("accordion-active");
            $this.parent().next().slideToggle();
        });


        $(".license__accordion-title").on("click", function (e) {
            e.preventDefault();
            let $this = $(this);

            if (!$this.hasClass("accordion-active")) {
                $(".license__accordion-content").slideUp(400);
                $(".license__accordion-title").removeClass("accordion-active");
            }

            $this.toggleClass("accordion-active");
            $this.next().slideToggle();
        });


        $(".backup-table-content .btn--link").on("click", function () {
            $(this).parent().find(".backup-table").toggleClass("show-more")

            if ($(".backup-table").hasClass("show-more")) {
                $(this).html(`
                   بستن 
                    <svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.342448 4.48771L4.4987 0.550211C4.60807 0.449951 4.72656 0.372477 4.85417 0.31779C4.98177 0.272217 5.11393 0.24943 5.25065 0.24943C5.38737 0.24943 5.51953 0.272217 5.64714 0.31779C5.77474 0.372477 5.89323 0.449951 6.0026 0.550211L10.1589 4.48771C10.3776 4.69735 10.4915 4.95256 10.5007 5.25334C10.5098 5.55412 10.4095 5.81388 10.1999 6.03263C9.99023 6.25138 9.73503 6.36532 9.43424 6.37443C9.13346 6.38354 8.8737 6.28328 8.65495 6.07365L5.25065 2.84709L1.84635 6.07365C1.6276 6.28328 1.36784 6.38354 1.06706 6.37443C0.766276 6.36532 0.511068 6.25138 0.301433 6.03263C0.0917969 5.81388 -0.00846291 5.55412 0.00065136 5.25334C0.00976562 4.95256 0.123698 4.69735 0.342448 4.48771Z" fill="#BBBBBB"/>
                    </svg>
                `)
            } else {
                $(this).html(`
                    ادامه لیست
                    <svg width="9" height="5" viewBox="0 0 9 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.70658 1.5405L5.14446 4.75449C5.05072 4.83632 4.94917 4.89956 4.83981 4.9442C4.73044 4.9814 4.61718 5 4.5 5C4.38283 5 4.26956 4.9814 4.16019 4.9442C4.05083 4.89956 3.94928 4.83632 3.85554 4.75449L0.293425 1.5405C0.105946 1.36939 0.00829988 1.16107 0.000488228 0.915558C-0.00732342 0.670046 0.0786048 0.458012 0.258273 0.279457C0.437941 0.100902 0.656667 0.00790477 0.914452 0.000464987C1.17224 -0.0069748 1.39487 0.0748628 1.58235 0.245978L4.5 2.87966L7.41765 0.245978C7.60513 0.0748628 7.82776 -0.0069748 8.08555 0.000464987C8.34333 0.00790477 8.56206 0.100902 8.74173 0.279457C8.9214 0.458012 9.00732 0.670046 8.99951 0.915558C8.9917 1.16107 8.89405 1.36939 8.70658 1.5405Z" fill="#7A7D81"/>
                    </svg>
                `)
            }
        });

        // ------------------------------------------------------------------------------------------------------- video

        $(".help__video-button").click(function () {
            let videoPlayerEle = $(this).parent().find('.help__video-inner');
            let videoPlayer = $(this).parent().find('.help__video-inner').get(0);
            if (!videoPlayer.paused) {
                return;
            }
            videoPlayerEle.addClass("playing");
            videoPlayer.play();
            $(this).addClass('d-none');
        });

        $(document).on('click', '.help__video-inner.playing', function () {
            let videoPlayer = $(this).get(0);
            if (videoPlayer.paused) {
                return;
            }
            videoPlayer.pause();
            $(this).removeClass('playing');
            $(this).parent().parent().find('.help__video-button').removeClass('d-none');
        });

        // ------------------------------------------------------------------------------------------------------ slider

        let slideCount = $('.dashboard-slider .dashboard-slider__list .dashboard-slider__item').length;
        let slideWidth = $('.dashboard-slider .dashboard-slider__list .dashboard-slider__item').width();
        let slideHeight = $('.dashboard-slider .dashboard-slider__list .dashboard-slider__item').height();
        let sliderUlWidth = slideCount * slideWidth;

        $('.dashboard-slider').css({width: slideWidth, height: slideHeight});

        $('.dashboard-slider .dashboard-slider__list').css({width: sliderUlWidth, marginLeft: -slideWidth});

        $('.dashboard-slider .dashboard-slider__list .dashboard-slider__item:last-child').prependTo('.dashboard-slider .dashboard-slider__list');

        function moveLeft() {
            $('.dashboard-slider .dashboard-slider__list').animate({
                left: +slideWidth
            }, 300, function () {
                $('.dashboard-slider .dashboard-slider__list .dashboard-slider__item:last-child').prependTo('.dashboard-slider .dashboard-slider__list');
                $('.dashboard-slider .dashboard-slider__list').css('left', '');
            });
        };

        function moveRight() {
            $('.dashboard-slider .dashboard-slider__list').animate({
                left: -slideWidth
            }, 300, function () {
                $('.dashboard-slider .dashboard-slider__list .dashboard-slider__item:first-child').appendTo('.dashboard-slider .dashboard-slider__list');
                $('.dashboard-slider .dashboard-slider__list').css('left', '');
            });
        };

        $('.dashboard-slider__prev').click(function () {
            moveLeft();
        });

        $('.dashboard-slider__next').click(function () {
            moveRight();
        });


        setInterval(function () {
            // do your thing
            moveRight()
            // if(counter === 10) {
            //     clearInterval(i);
            // }
        }, 5000);

        // ----------------------------------------------------------------------------------------------------- sidebar

        function openMenu() {
            // $("body").css("overflow", "hidden")
            $(".sidebar").addClass("sidebar--active")
            $(".rsm-content").addClass("rsm-content--overlay")
            $(".sidebar__list-item-text").css("display", "block")
            $(".sidebar__banner").css("display", "block")
            $(".sidebar__copyright").css("display", "block")
            $(".sidebar__list-submenu").css("display", "block")
            $(".sidebar__list").addClass("sidebar__list--active")
            $(".sidebar__content").addClass("sidebar__content--active")
            $(".closeMenu-btn").addClass("closeMenu-btn--active")
            $(".menu-btn").addClass("menu-btn--hide")
        }

        function closeMenu() {
            $("body").css("overflow", "unset")
            $(".sidebar").removeClass("sidebar--active")
            $(".rsm-content").removeClass("rsm-content--overlay")
            $(".sidebar__list-item-text").css("display", "none")
            $(".sidebar__banner").css("display", "none")
            $(".sidebar__copyright").css("display", "none")
            $(".sidebar__list-submenu").css("display", "none")
            $(".sidebar__list").removeClass("sidebar__list--active")
            $(".sidebar__content").removeClass("sidebar__content--active")
            $(".closeMenu-btn").removeClass("closeMenu-btn--active")
            $(".menu-btn").removeClass("menu-btn--hide")
        }

        $(".menu-btn").on("click", function () {

            openMenu()
        })
        $(".closeMenu-btn").on("click", function () {

            closeMenu()
        })

        if ($(window).width() < 1200) {
            $(document.body).on('click', function (e) {
                if (!$(".rsm-sidebar").has(e.target).length) {
                    closeMenu()
                }
            });
        }

        // End of sidebar menu

        function hidingPluginAlert() {
            const hidingPluginAlertButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn--green",
                    cancelButton: "btn btn--light-blue backup-end-btn",
                },
                buttonsStyling: false,
            });
            hidingPluginAlertButtons.fire({
                title: "آیا از مخفی‌سازی افزونه مدیریت هوشمندراست چین اطمینان دارید؟",
                text: "حتماً لینک دسترسی به افزونه را نزد خود نگه دارید.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "مخفی سازی",
                cancelButtonText: "انصراف",
                reverseButtons: false,
            })
        }


        /*------------------------------------------------------------
         |Notification
         ------------------------------------------------------------*/

        $('#rsmShowUnReadAnnouncement').on('click', function () {
            let btn = $(this)
            let value = btn.is(':checked') ? 'show' : 'hide';

            //rtlShowLoading()
            let data = {
                'action': 'rsmAjaxNotificationFilter',
                'nonce': rsm.nonce,
                'tab': 'notification',
                'value': value,
            }
            rtlShowLoading()
            $.ajax({
                method: 'POST',
                url: rsm.url,
                dataType: 'json',
                data: data
            }).done(function (resp) {
                if (resp.success === true) {
                    rtlHideLoading()
                    $('#notificationPlace').empty()
                    let data = resp.data;
                    let html = ''
                    let visited = data.visited

                    $.each(data.list, function (key, value) {
                        let id = value['announcement_id'];
                        let isRead = $.inArray(id + '', visited) !== -1;
                        if (data['showStatus'] == 'show' && isRead) {
                            return;
                        }
                        html += `<a href="${data['singleNotificationLink'] + id}"
               class="notification-card ${isRead ? '' : 'notification-card--late'}">
                <div class="notification-card__content">
                    <div class="notification-card__head">
                        <div class="notification-card__icon">`;
                        if (isRead) {
                            html += `<svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.6"
                                          d="M7.50024 0.40625C8.46704 0.40625 9.38013 0.591797 10.2395 0.962891C11.0891 1.32422 11.8313 1.82715 12.4661 2.47168C13.1106 3.10645 13.6135 3.84863 13.9749 4.69824C14.3459 5.55762 14.5315 6.4707 14.5315 7.4375C14.5315 8.4043 14.3459 9.31738 13.9749 10.1768C13.6135 11.0264 13.1106 11.7734 12.4661 12.418C11.8313 13.0527 11.0891 13.5508 10.2395 13.9121C9.38013 14.2832 8.46704 14.4688 7.50024 14.4688C6.53345 14.4688 5.62036 14.2832 4.76099 13.9121C3.91138 13.5508 3.16431 13.0527 2.51978 12.418C1.88501 11.7734 1.38696 11.0264 1.02563 10.1768C0.654541 9.31738 0.468994 8.4043 0.468994 7.4375C0.468994 6.4707 0.654541 5.55762 1.02563 4.69824C1.38696 3.84863 1.88501 3.10645 2.51978 2.47168C3.16431 1.82715 3.91138 1.32422 4.76099 0.962891C5.62036 0.591797 6.53345 0.40625 7.50024 0.40625ZM10.8987 6.14844C11.0549 5.98242 11.1331 5.78711 11.1331 5.5625C11.1331 5.33789 11.0549 5.14258 10.8987 4.97656C10.7327 4.82031 10.5374 4.74219 10.3127 4.74219C10.0881 4.74219 9.89282 4.82031 9.72681 4.97656L6.56274 8.15527L5.27368 6.85156C5.10767 6.69531 4.91235 6.61719 4.68774 6.61719C4.46313 6.61719 4.26782 6.69531 4.10181 6.85156C3.94556 7.01758 3.86743 7.21289 3.86743 7.4375C3.86743 7.66211 3.94556 7.85742 4.10181 8.02344L5.97681 9.89844C6.0647 9.97656 6.15747 10.0352 6.25513 10.0742C6.35278 10.1133 6.45532 10.1328 6.56274 10.1328C6.67017 10.1328 6.77271 10.1133 6.87036 10.0742C6.96802 10.0352 7.06079 9.97656 7.14868 9.89844L10.8987 6.14844Z"
                                          fill="#656B74"/>
                                </svg>`
                        } else {
                            html += `<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="10" cy="10" r="10" fill="#8ED557"/>
                                    <path d="M9.7124 12.4185C10.0486 12.4185 10.3374 12.5391 10.5786 12.7803C10.8198 13.0215 10.9404 13.3102 10.9404 13.6465C10.9404 13.9827 10.8198 14.2715 10.5786 14.5127C10.3374 14.7539 10.0486 14.8745 9.7124 14.8745C9.37615 14.8745 9.08742 14.7539 8.8462 14.5127C8.60498 14.2715 8.48438 13.9827 8.48438 13.6465C8.48438 13.3102 8.60498 13.0215 8.8462 12.7803C9.08742 12.5391 9.37615 12.4185 9.7124 12.4185ZM9.67951 11.5413C9.4456 11.534 9.24823 11.4463 9.08742 11.2782C8.92661 11.11 8.84255 10.909 8.83524 10.6751L8.65981 5.99327C8.6525 5.69357 8.74752 5.43773 8.94488 5.22575C9.14224 5.00646 9.38346 4.88951 9.66854 4.87489C9.96093 4.86758 10.2131 4.96626 10.4251 5.17093C10.6371 5.36829 10.7504 5.61682 10.765 5.91652C10.765 5.93114 10.765 5.94576 10.765 5.96038C10.765 5.96768 10.765 5.97865 10.765 5.99327L10.5896 10.6751C10.5822 10.9236 10.4872 11.132 10.3045 11.3001C10.129 11.4682 9.92072 11.5486 9.67951 11.5413Z"
                                          fill="white"/>
                                </svg>`
                        }

                        html += `</div>
                        <div class="notification-card__title">
                            ${value['title']}
                        </div>
                    </div>
                    <div class="notification-card__desc">
                        ${value['excerpt']}
                    </div>
                </div>
                <div class="notification-card__time">
                   ${value['updated_at']}
                </div>
            </a>`;
                    })
                    $('#notificationPlace').html(html)

                } else {
                    rtlError(resp.data.message)
                }

            }).fail(function (response) {
                let resp = response.responseText
                if (typeof resp === 'string') {
                    resp = $.parseJSON(response.responseText)
                }
                rtlError(resp.data.message)
            })
        })
    });


})(jQuery);