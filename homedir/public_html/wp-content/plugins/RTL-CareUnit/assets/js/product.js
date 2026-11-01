(jQuery)(document).ready(function ($) {

    $.ajaxProductInProgress = false;
    /**
     *  get update question for confirm by user after accepted by user, the plugin or theme will be updated to new version
     */
    $(document).on('click', '.update-plugin,.update-theme', function (e) {
        e.preventDefault();
        let btn        = $(this)
        let updateLink = btn.data('update-url');

        Swal.fire({
            title             : rsm.Translate.ays,
            text              : rsm.Translate.updateAlert,
            icon              : "warning",
            showCancelButton  : true,
            confirmButtonColor: "#ef5350",
            cancelButtonColor : "#3085d6",
            confirmButtonText : rsm.Translate.yesUpdated,
            cancelButtonText  : rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                let win = window.open(updateLink, '_blank');
                if (win) {
                    //Browser has allowed it to be opened
                    win.focus();
                } else {
                    //Browser has blocked it
                    alert('Please allow popups for this website');
                }
            }
        });
    })


    /**
     *  get restore question for confirm by user after accepted by user,
     *  the plugin will be restored to selected version backup
     */
    $(document).on('click', '.restore-prev-plugin,.restore-prev-theme', function (e) {
        e.preventDefault();
        let btn         = $(this)
        let restoreLink = btn.data('restore-url');

        Swal.fire({
            title             : rsm.Translate.ays,
            text              : rsm.Translate.restoreAlert,
            icon              : "warning",
            showCancelButton  : true,
            confirmButtonColor: "#ef5350",
            cancelButtonColor : "#3085d6",
            confirmButtonText : rsm.Translate.yesRestore,
            cancelButtonText  : rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                let win = window.open(restoreLink, '_blank');
                if (win) {
                    //Browser has allowed it to be opened
                    win.focus();
                } else {
                    //Browser has blocked it
                    alert('Please allow popups for this website');
                }
            }
        });
    })

    /**
     *  show change ability in new version
     *  open modal
     *
     */
    $(document).on('click', '.show-changelog-plugin,.show-changelog-theme', function (e) {
        e.preventDefault();
        let slug = $(this).data('slug')

        /*****
         | rtlRsmChangeLog come from plugins and theme page in front folder
         ****/
        if (slug in rtlRsmChangeLog) {
            let data = rtlRsmChangeLog[slug]
            let html = ''
            $('#rsm-modal-title').text(data['name'])
            $.each(data['changeLog'], function (key, value) {
                html += `<div className="rsm-modal__item-title">
                    نسخه ${value['version']} - ${value['date']}
                </div>`
                $.each(value['changes'], function (key, item) {
                    html += `<div class="rsm-modal__item"  >${item} </div>`
                })
            })
            $('#rsm-modal__body').html(html)
            $('body').addClass("rsm-modal-open");
        } else {
            rtlError('تغییرات نسخه محصول مورد نظر پیدا نشد')
        }

        if ($('body').hasClass("rsm-modal-open")) {
            $('.rsm-modal').on('click', function (event) {
                if (!$(event.target).closest('.rsm-modal-dialog').length) {
                    $('body').removeClass("rsm-modal-open");
                }
            });
        }

    })

    /**
     *  close modal
     *
     */
    $('.rsm-close-modal').on('click', function () {
        $('body').removeClass("rsm-modal-open");
    });

    /**
     *  send form for save settings
     *
     */
    $('#plugin-form, #theme-form').submit(function (e) {
        e.preventDefault();

        rtlShowLoading();
        $.ajax({
            method     : 'POST',
            url        : rsm.url,
            data       : new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (resp) {
            if (resp.success === true) {
                rtlSuccess(resp.data.message).then(function () {
                    // window.location.reload()
                })
            } else {
                rtlError(resp.data.message).then(function () {
                    // window.location.reload()
                })
            }
        }).fail(function (response) {
            let resp = response.responseText
            if (typeof resp === 'string') {
                resp = $.parseJSON(response.responseText)
            }
            rtlError(resp.data.message)
        })
    })

    /**
     *  if auto backup all turn on or turn off, all plugin auto backup situation must change
     */
    $('#autoBackupAllPlugin').on('change', function () {
        let input = $(this)
        if (input.is(':checked')) {
            onOffCheckbox('.auto-backup-plugin', 'on')
        } else {
            onOffCheckbox('.auto-backup-plugin', 'off')
        }

    })

    /**
     *  if was change one of the auto backup checkbox to off, the auto backup all checkbox will automatically change to off
     */
    $('.auto-backup-plugin').on('change', function () {
        let input = $(this)
        if (!input.is(':checked')) {
            $('#autoBackupAllPlugin').removeAttr('checked');
        }
    })

    /**
     *  if auto update all turn on or turn off, all plugin auto update situation must change
     */
    $('#autoUpdateAllPlugin').on('change', function () {
        let input = $(this)
        if (input.is(':checked')) {
            onOffCheckbox('.auto-update-plugin', 'on')
        } else {
            onOffCheckbox('.auto-update-plugin', 'off')
        }

    })

    /**
     *  if was change one of the auto update checkbox to off, the auto update all checkbox will automatically change to off
     */
    $('.auto-update-plugin').on('change', function () {
        let input = $(this)
        if (!input.is(':checked')) {
            $('#autoUpdateAllPlugin').removeAttr('checked');
        }
    })


    /**
     *  if auto backup all turn on or turn off, all theme auto backup situation must change
     */
    $('#autoBackupAllTheme').on('change', function () {
        let input = $(this)
        if (input.is(':checked')) {
            onOffCheckbox('.auto-backup-theme', 'on')
        } else {
            onOffCheckbox('.auto-backup-theme', 'off')
        }

    })

    /**
     *  if was change one of the auto backup checkbox to off, the auto backup all checkbox will automatically change to off
     */
    $('.auto-backup-theme').on('change', function () {
        let input = $(this)
        if (!input.is(':checked')) {
            $('#autoBackupAllTheme').removeAttr('checked');
        }
    })

    /**
     *  if auto update all turn on or turn off, all theme auto update situation must change
     */
    $('#autoUpdateAllTheme').on('change', function () {
        let input = $(this)
        if (input.is(':checked')) {
            onOffCheckbox('.auto-update-theme', 'on')
        } else {
            onOffCheckbox('.auto-update-theme', 'off')
        }

    })

    /**
     *  if was change one of the auto update checkbox to off, the auto update all checkbox will automatically change to off
     */
    $('.auto-update-plugin').on('change', function () {
        let input = $(this)
        if (!input.is(':checked')) {
            $('#autoUpdateAllPlugin').removeAttr('checked');
        }
    })


    $('.rsm-check-update-product').on('click', function () {
        rtlShowLoading(rsm?.Translate?.updatingProductList, 'در حال بررسی بروزرسانی ها');
        $.ajaxProductInProgress = true;
        $.ajax({
            method: 'GET',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxCheckProductsUpdate',
                'nonce' : rsm.nonce,
                'tab'   : 'theme',
            }
        }).done(function (resp) {
            rtlHideLoading()
            $.ajaxProductInProgress = false;
            if (resp.success === true) {
                rtlSuccess(resp.data.message).then(function () {
                    window.location.reload()
                })
            } else {
                rtlError(resp.data.message)
            }
        }).fail(function (response) {
            $.ajaxProductInProgress = false;
            rtlHideLoading()
            let resp = response.responseText
            if (typeof resp === 'string') {
                resp = $.parseJSON(response.responseText)
            }
            rtlError(resp.data.message)

        })

    })
    /**
     *  search in plugin list
     */
    let pluginTimeout;
    $('#rsm-search-plugin').on('input', function () {
        rsmSearchPluginHandler($(this).val());
    })

    /**
     *  search in theme list
     */
    let themeTimeout
    $('#rsm-search-theme').on('input', function () {
        rsmSearchThemeHandler($(this).val());
    })

    $('.install-plugin').on('click', function () {
        let btn         = $(this)
        let installLink = btn.data('install-url');

        Swal.fire({
            title             : rsm.Translate.installAlert,
            icon              : "warning",
            showCancelButton  : true,
            confirmButtonColor: "#039405",
            cancelButtonColor : "#c3d1ec",
            confirmButtonText : rsm.Translate.yesInstall,
            cancelButtonText  : rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                let win = window.open(installLink, '_blank');
                if (win) {
                    //Browser has allowed it to be opened
                    win.focus();
                } else {
                    //Browser has blocked it
                    alert(rsm.Translate.allowPopup);
                }
            }
        });

    })

    function rsmSearchPluginHandler(value = '') {
        if (pluginTimeout) clearTimeout(pluginTimeout);
        let input = value

        pluginTimeout = setTimeout(function () {
            rtlShowLoading()
            $.ajax({
                method: 'GET',
                url   : rsm.url,
                dataType: 'json',
                data  : {
                    'action': 'rsmAjaxSearchPlugin',
                    'nonce' : rsm.nonce,
                    'tab'   : 'plugin',
                    'search': input
                }
            }).done(function (resp) {
                rtlHideLoading()

                if (resp.success === true) {
                    let data = resp.data
                    let html = createProductHtml(data, 'plugin');
                    $('#rsm-plugins-place').html(html)
                }
            }).fail(function () {
                rtlHideLoading()
                rtlError()
            })
            clearTimeout(pluginTimeout);
        }, 1300)
    }

    function rsmSearchThemeHandler(value = '') {
        let input = value
        if (themeTimeout) clearTimeout(themeTimeout);

        themeTimeout = setTimeout(function () {
            rtlShowLoading()
            $.ajax({
                method: 'GET',
                url   : rsm.url,
                dataType: 'json',
                data  : {
                    'action': 'rsmAjaxSearchTheme',
                    'nonce' : rsm.nonce,
                    'tab'   : 'theme',
                    'search': input
                }
            }).done(function (resp) {
                rtlHideLoading()
                if (resp.success === true) {
                    let data = resp.data
                    let html = createProductHtml(data, 'theme');

                    $('#rsm-themes-place').html(html)
                }

            }).fail(function () {
                rtlHideLoading()
                rtlError()
            })
            clearTimeout(themeTimeout);
        }, 1300)
    }

    /**
     *  on or off all checkbox input by class
     * @param className
     * @param flag
     */
    function onOffCheckbox(className, flag) {
        $.each($(className), function (key, value) {
            if (flag === 'on') {
                $(document).find(value).prop('checked', true)
            } else {
                $(document).find(value).removeAttr('checked');
            }
        })
    }

    function createProductHtml(data, type) {
        let defaultImg = rsm.obj.defaultImg

        $('#rsm-' + type + 's-place').empty();

        let settings    = data.settings
        let supportLink = data.supportLink
        // for use pascal Case type
        let Type        = type === 'plugin' ? 'Plugin' : 'Theme'


        let isOnBackup = settings['autoBackupAll' + Type] === 'on';
        let isOnUpdate = settings['autoUpdateAll' + Type] === 'on';

        let html = '';

        $.each(data.list, function (key, $item) {
            let isInstalled = true;

            let isRtlProduct = $item['isRtlProduct'];
            if (isRtlProduct && $item['rtlStatus'] === 'notInstalled') {
                isInstalled = false;
            }
            <!--    if rtl product use this card-->
            html += `<div class="product-card ${isRtlProduct ? 'product-card--rtl' : ''}">
                        <div class="product-card__area">
                            <div class="product-card__banner">
                            <!-- TODO:: IMG SIZE -->
                                <img style="width: 21.8rem;height: 11rem" src=" ${$item['screenshot'] ?? defaultImg} " alt="${$item['name']}">
                                `;
            if (isRtlProduct) {
                html += `<div class="product-card__banner-actions">`;
                <!--remove product-card__banner-badge--danger for green badge-->
                if ($item['rtlStatus'] === 'Deactivate') {
                    html += ` <div class="product-card__banner-btn product-card__banner-badge product-card__banner-badge--danger">
                                                <div class="product-card__banner-badge-icon">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12.3033 2.4375C12.5377 2.53299 12.7243 2.6849 12.8632 2.89323C13.0021 3.10156 13.0715 3.3316 13.0715 3.58333C13.0715 5.27604 12.8068 6.7474 12.2773 7.9974C11.7477 9.2474 11.1314 10.2891 10.4283 11.1224C9.72518 11.947 9.02639 12.5634 8.33195 12.9714C7.64619 13.3793 7.14271 13.5833 6.82153 13.5833C6.49167 13.5833 5.97952 13.375 5.28508 12.9583C4.59931 12.5503 3.90487 11.9297 3.20174 11.0964C2.5073 10.263 1.89532 9.22135 1.3658 7.97135C0.83629 6.72135 0.571533 5.25868 0.571533 3.58333C0.571533 3.3316 0.640978 3.10156 0.779867 2.89323C0.918755 2.6849 1.10539 2.52865 1.33976 2.42448L6.33976 0.341146C6.41789 0.315104 6.49601 0.293403 6.57414 0.276042C6.66094 0.258681 6.74341 0.25 6.82153 0.25C6.90834 0.25 6.9908 0.258681 7.06893 0.276042C7.14705 0.293403 7.22518 0.319444 7.3033 0.354167L12.3033 2.4375ZM12.2382 3.59635C12.2382 3.56163 12.2252 3.50087 12.1991 3.41406C12.1731 3.31858 12.0993 3.24479 11.9778 3.19271L6.97778 1.10937C6.95174 1.10069 6.9257 1.09635 6.89966 1.09635C6.87362 1.08767 6.84758 1.08333 6.82153 1.08333C6.79549 1.08333 6.76511 1.08767 6.73039 1.09635C6.70435 1.09635 6.6783 1.10503 6.65226 1.1224L1.65226 3.20573C1.58282 3.23177 1.52205 3.27951 1.46997 3.34896C1.42657 3.4184 1.40487 3.49653 1.40487 3.58333C1.40487 5.04167 1.6349 6.33941 2.09497 7.47656C2.55504 8.61372 3.08889 9.57292 3.69653 10.3542C4.30417 11.1354 4.90747 11.73 5.50643 12.138C6.10539 12.546 6.54376 12.75 6.82153 12.75C7.09063 12.75 7.52466 12.546 8.12362 12.138C8.72258 11.7387 9.32587 11.1528 9.93351 10.3802C10.5412 9.59896 11.0793 8.63976 11.5481 7.5026C12.0082 6.36545 12.2382 5.06337 12.2382 3.59635ZM8.90487 4.97656C9.02639 4.97656 9.12622 5.01997 9.20435 5.10677C9.28247 5.1849 9.32153 5.28038 9.32153 5.39323C9.32153 5.44531 9.30851 5.4974 9.28247 5.54948C9.26511 5.60156 9.23907 5.64497 9.20435 5.67969L6.41789 8.46615C6.38317 8.50087 6.33976 8.53125 6.28768 8.55729C6.2356 8.57465 6.18351 8.58333 6.13143 8.58333C6.07935 8.58333 6.02726 8.57465 5.97518 8.55729C5.9231 8.53125 5.87535 8.50087 5.83195 8.46615L4.43872 7.07292C4.404 7.02951 4.37362 6.98177 4.34758 6.92969C4.33021 6.8776 4.32153 6.82552 4.32153 6.77344C4.32153 6.66927 4.3606 6.57378 4.43872 6.48698C4.51685 6.40017 4.61667 6.35677 4.7382 6.35677C4.79028 6.35677 4.84237 6.36979 4.89445 6.39583C4.94653 6.41319 4.99428 6.44358 5.03768 6.48698L6.13143 7.58073L8.60539 5.09375C8.64879 5.05035 8.69653 5.01997 8.74862 5.0026C8.8007 4.98524 8.85278 4.97656 8.90487 4.97656Z"
                                                              fill="#3B6D12"/>
                                                    </svg>
                                                </div>
                                                <div class="product-card__banner-badge-text">${rsm.Translate.inactiveLicense}</div>
                                            </div>`;
                } else if ($item['rtlStatus'] === 'Active') {
                    html += `  <div class="product-card__banner-btn product-card__banner-badge">
                                                <div class="product-card__banner-badge-icon">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12.3033 2.4375C12.5377 2.53299 12.7243 2.6849 12.8632 2.89323C13.0021 3.10156 13.0715 3.3316 13.0715 3.58333C13.0715 5.27604 12.8068 6.7474 12.2773 7.9974C11.7477 9.2474 11.1314 10.2891 10.4283 11.1224C9.72518 11.947 9.02639 12.5634 8.33195 12.9714C7.64619 13.3793 7.14271 13.5833 6.82153 13.5833C6.49167 13.5833 5.97952 13.375 5.28508 12.9583C4.59931 12.5503 3.90487 11.9297 3.20174 11.0964C2.5073 10.263 1.89532 9.22135 1.3658 7.97135C0.83629 6.72135 0.571533 5.25868 0.571533 3.58333C0.571533 3.3316 0.640978 3.10156 0.779867 2.89323C0.918755 2.6849 1.10539 2.52865 1.33976 2.42448L6.33976 0.341146C6.41789 0.315104 6.49601 0.293403 6.57414 0.276042C6.66094 0.258681 6.74341 0.25 6.82153 0.25C6.90834 0.25 6.9908 0.258681 7.06893 0.276042C7.14705 0.293403 7.22518 0.319444 7.3033 0.354167L12.3033 2.4375ZM12.2382 3.59635C12.2382 3.56163 12.2252 3.50087 12.1991 3.41406C12.1731 3.31858 12.0993 3.24479 11.9778 3.19271L6.97778 1.10937C6.95174 1.10069 6.9257 1.09635 6.89966 1.09635C6.87362 1.08767 6.84758 1.08333 6.82153 1.08333C6.79549 1.08333 6.76511 1.08767 6.73039 1.09635C6.70435 1.09635 6.6783 1.10503 6.65226 1.1224L1.65226 3.20573C1.58282 3.23177 1.52205 3.27951 1.46997 3.34896C1.42657 3.4184 1.40487 3.49653 1.40487 3.58333C1.40487 5.04167 1.6349 6.33941 2.09497 7.47656C2.55504 8.61372 3.08889 9.57292 3.69653 10.3542C4.30417 11.1354 4.90747 11.73 5.50643 12.138C6.10539 12.546 6.54376 12.75 6.82153 12.75C7.09063 12.75 7.52466 12.546 8.12362 12.138C8.72258 11.7387 9.32587 11.1528 9.93351 10.3802C10.5412 9.59896 11.0793 8.63976 11.5481 7.5026C12.0082 6.36545 12.2382 5.06337 12.2382 3.59635ZM8.90487 4.97656C9.02639 4.97656 9.12622 5.01997 9.20435 5.10677C9.28247 5.1849 9.32153 5.28038 9.32153 5.39323C9.32153 5.44531 9.30851 5.4974 9.28247 5.54948C9.26511 5.60156 9.23907 5.64497 9.20435 5.67969L6.41789 8.46615C6.38317 8.50087 6.33976 8.53125 6.28768 8.55729C6.2356 8.57465 6.18351 8.58333 6.13143 8.58333C6.07935 8.58333 6.02726 8.57465 5.97518 8.55729C5.9231 8.53125 5.87535 8.50087 5.83195 8.46615L4.43872 7.07292C4.404 7.02951 4.37362 6.98177 4.34758 6.92969C4.33021 6.8776 4.32153 6.82552 4.32153 6.77344C4.32153 6.66927 4.3606 6.57378 4.43872 6.48698C4.51685 6.40017 4.61667 6.35677 4.7382 6.35677C4.79028 6.35677 4.84237 6.36979 4.89445 6.39583C4.94653 6.41319 4.99428 6.44358 5.03768 6.48698L6.13143 7.58073L8.60539 5.09375C8.64879 5.05035 8.69653 5.01997 8.74862 5.0026C8.8007 4.98524 8.85278 4.97656 8.90487 4.97656Z"
                                                              fill="#3B6D12"/>
                                                    </svg>
                                                </div>
                                                <div class="product-card__banner-badge-text">${rsm.Translate.activeLicense} </div>
                                            </div>`
                } else if ($item['rtlStatus'] === 'notInstalled') {
                    html += `  <div class="product-card__banner-btn product-card__banner-badge">
                                                <div class="product-card__banner-badge-icon">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12.3033 2.4375C12.5377 2.53299 12.7243 2.6849 12.8632 2.89323C13.0021 3.10156 13.0715 3.3316 13.0715 3.58333C13.0715 5.27604 12.8068 6.7474 12.2773 7.9974C11.7477 9.2474 11.1314 10.2891 10.4283 11.1224C9.72518 11.947 9.02639 12.5634 8.33195 12.9714C7.64619 13.3793 7.14271 13.5833 6.82153 13.5833C6.49167 13.5833 5.97952 13.375 5.28508 12.9583C4.59931 12.5503 3.90487 11.9297 3.20174 11.0964C2.5073 10.263 1.89532 9.22135 1.3658 7.97135C0.83629 6.72135 0.571533 5.25868 0.571533 3.58333C0.571533 3.3316 0.640978 3.10156 0.779867 2.89323C0.918755 2.6849 1.10539 2.52865 1.33976 2.42448L6.33976 0.341146C6.41789 0.315104 6.49601 0.293403 6.57414 0.276042C6.66094 0.258681 6.74341 0.25 6.82153 0.25C6.90834 0.25 6.9908 0.258681 7.06893 0.276042C7.14705 0.293403 7.22518 0.319444 7.3033 0.354167L12.3033 2.4375ZM12.2382 3.59635C12.2382 3.56163 12.2252 3.50087 12.1991 3.41406C12.1731 3.31858 12.0993 3.24479 11.9778 3.19271L6.97778 1.10937C6.95174 1.10069 6.9257 1.09635 6.89966 1.09635C6.87362 1.08767 6.84758 1.08333 6.82153 1.08333C6.79549 1.08333 6.76511 1.08767 6.73039 1.09635C6.70435 1.09635 6.6783 1.10503 6.65226 1.1224L1.65226 3.20573C1.58282 3.23177 1.52205 3.27951 1.46997 3.34896C1.42657 3.4184 1.40487 3.49653 1.40487 3.58333C1.40487 5.04167 1.6349 6.33941 2.09497 7.47656C2.55504 8.61372 3.08889 9.57292 3.69653 10.3542C4.30417 11.1354 4.90747 11.73 5.50643 12.138C6.10539 12.546 6.54376 12.75 6.82153 12.75C7.09063 12.75 7.52466 12.546 8.12362 12.138C8.72258 11.7387 9.32587 11.1528 9.93351 10.3802C10.5412 9.59896 11.0793 8.63976 11.5481 7.5026C12.0082 6.36545 12.2382 5.06337 12.2382 3.59635ZM8.90487 4.97656C9.02639 4.97656 9.12622 5.01997 9.20435 5.10677C9.28247 5.1849 9.32153 5.28038 9.32153 5.39323C9.32153 5.44531 9.30851 5.4974 9.28247 5.54948C9.26511 5.60156 9.23907 5.64497 9.20435 5.67969L6.41789 8.46615C6.38317 8.50087 6.33976 8.53125 6.28768 8.55729C6.2356 8.57465 6.18351 8.58333 6.13143 8.58333C6.07935 8.58333 6.02726 8.57465 5.97518 8.55729C5.9231 8.53125 5.87535 8.50087 5.83195 8.46615L4.43872 7.07292C4.404 7.02951 4.37362 6.98177 4.34758 6.92969C4.33021 6.8776 4.32153 6.82552 4.32153 6.77344C4.32153 6.66927 4.3606 6.57378 4.43872 6.48698C4.51685 6.40017 4.61667 6.35677 4.7382 6.35677C4.79028 6.35677 4.84237 6.36979 4.89445 6.39583C4.94653 6.41319 4.99428 6.44358 5.03768 6.48698L6.13143 7.58073L8.60539 5.09375C8.64879 5.05035 8.69653 5.01997 8.74862 5.0026C8.8007 4.98524 8.85278 4.97656 8.90487 4.97656Z"
                                                              fill="#3B6D12"/>
                                                    </svg>
                                                </div>
                                                <div class="product-card__banner-badge-text">${rsm.Translate.notInstalled} </div>
                                            </div>`
                } else if ($item['rtlStatus'] === 'Free') {
                    html += `<div class="product-card__banner-btn product-card__banner-badge">
                                                <div class="product-card__banner-badge-icon">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12.3033 2.4375C12.5377 2.53299 12.7243 2.6849 12.8632 2.89323C13.0021 3.10156 13.0715 3.3316 13.0715 3.58333C13.0715 5.27604 12.8068 6.7474 12.2773 7.9974C11.7477 9.2474 11.1314 10.2891 10.4283 11.1224C9.72518 11.947 9.02639 12.5634 8.33195 12.9714C7.64619 13.3793 7.14271 13.5833 6.82153 13.5833C6.49167 13.5833 5.97952 13.375 5.28508 12.9583C4.59931 12.5503 3.90487 11.9297 3.20174 11.0964C2.5073 10.263 1.89532 9.22135 1.3658 7.97135C0.83629 6.72135 0.571533 5.25868 0.571533 3.58333C0.571533 3.3316 0.640978 3.10156 0.779867 2.89323C0.918755 2.6849 1.10539 2.52865 1.33976 2.42448L6.33976 0.341146C6.41789 0.315104 6.49601 0.293403 6.57414 0.276042C6.66094 0.258681 6.74341 0.25 6.82153 0.25C6.90834 0.25 6.9908 0.258681 7.06893 0.276042C7.14705 0.293403 7.22518 0.319444 7.3033 0.354167L12.3033 2.4375ZM12.2382 3.59635C12.2382 3.56163 12.2252 3.50087 12.1991 3.41406C12.1731 3.31858 12.0993 3.24479 11.9778 3.19271L6.97778 1.10937C6.95174 1.10069 6.9257 1.09635 6.89966 1.09635C6.87362 1.08767 6.84758 1.08333 6.82153 1.08333C6.79549 1.08333 6.76511 1.08767 6.73039 1.09635C6.70435 1.09635 6.6783 1.10503 6.65226 1.1224L1.65226 3.20573C1.58282 3.23177 1.52205 3.27951 1.46997 3.34896C1.42657 3.4184 1.40487 3.49653 1.40487 3.58333C1.40487 5.04167 1.6349 6.33941 2.09497 7.47656C2.55504 8.61372 3.08889 9.57292 3.69653 10.3542C4.30417 11.1354 4.90747 11.73 5.50643 12.138C6.10539 12.546 6.54376 12.75 6.82153 12.75C7.09063 12.75 7.52466 12.546 8.12362 12.138C8.72258 11.7387 9.32587 11.1528 9.93351 10.3802C10.5412 9.59896 11.0793 8.63976 11.5481 7.5026C12.0082 6.36545 12.2382 5.06337 12.2382 3.59635ZM8.90487 4.97656C9.02639 4.97656 9.12622 5.01997 9.20435 5.10677C9.28247 5.1849 9.32153 5.28038 9.32153 5.39323C9.32153 5.44531 9.30851 5.4974 9.28247 5.54948C9.26511 5.60156 9.23907 5.64497 9.20435 5.67969L6.41789 8.46615C6.38317 8.50087 6.33976 8.53125 6.28768 8.55729C6.2356 8.57465 6.18351 8.58333 6.13143 8.58333C6.07935 8.58333 6.02726 8.57465 5.97518 8.55729C5.9231 8.53125 5.87535 8.50087 5.83195 8.46615L4.43872 7.07292C4.404 7.02951 4.37362 6.98177 4.34758 6.92969C4.33021 6.8776 4.32153 6.82552 4.32153 6.77344C4.32153 6.66927 4.3606 6.57378 4.43872 6.48698C4.51685 6.40017 4.61667 6.35677 4.7382 6.35677C4.79028 6.35677 4.84237 6.36979 4.89445 6.39583C4.94653 6.41319 4.99428 6.44358 5.03768 6.48698L6.13143 7.58073L8.60539 5.09375C8.64879 5.05035 8.69653 5.01997 8.74862 5.0026C8.8007 4.98524 8.85278 4.97656 8.90487 4.97656Z"
                                                              fill="#3B6D12"/>
                                                    </svg>
                                                </div>
                                                <div class="product-card__banner-badge-text">${rsm.Translate.free}</div>
                                            </div>`;
                }
                html += ` <a href="${supportLink}" target="_blank" class="product-card__banner-btn">
                                            <div class="product-card__banner-btn-icon">
                                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.0419 13.0548H9.20854L6.05644 15.1515C5.58894 15.4631 4.95854 15.1302 4.95854 14.5636V13.0548C2.83354 13.0548 1.41687 11.6381 1.41687 9.51315V5.2631C1.41687 3.1381 2.83354 1.72144 4.95854 1.72144H12.0419C14.1669 1.72144 15.5835 3.1381 15.5835 5.2631V9.51315C15.5835 11.6381 14.1669 13.0548 12.0419 13.0548Z"
                                                          stroke="#656B74" stroke-width="1.2" stroke-miterlimit="10"
                                                          stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.49967 8.04663V7.89791C8.49967 7.41624 8.79719 7.16123 9.09469 6.95581C9.3851 6.75748 9.67548 6.50249 9.67548 6.03499C9.67548 5.38332 9.15133 4.85913 8.49967 4.85913C7.848 4.85913 7.32385 5.38332 7.32385 6.03499"
                                                          stroke="#656B74" stroke-width="1.2" stroke-linecap="round"
                                                          stroke-linejoin="round"/>
                                                    <path d="M8.49661 9.73966H8.50298" stroke="#656B74"
                                                          stroke-width="1.2"
                                                          stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                            <div class="product-card__banner-btn-text">
                                                ${rsm.Translate.support}
                                            </div>
                                        </a>
                                    </div>`;
            }

            let hasChangeLog = ($item['changeLog'] === null || $item['changeLog'] === '' || $.isEmptyObject($item['changeLog']));

            html += `</div>
                            <div class="product-card__content">
                                <div class="product-card__title">
                                    ${$item['name']}   
                                    <span class="product-card__title-badge"> ${$item['version']} </span>
                                </div>
                                <div class="product-card__filters">
                                    <div class="product-card__filters-item">
                                        <div class="product-card__filters-text">
                                            ${rsm.Translate.automaticUpdate}
                                        </div>
                                        <label class="rsm-switch">
                                            <input name="autoUpdate[${key}]" class=" ${(isRtlProduct && isInstalled) ? 'auto-update-' + type : 'disabled'} "
                                                   type="checkbox" ${(isRtlProduct && isInstalled) ? (isOnUpdate ? 'checked' : ($.inArray(key, settings['autoUpdate']) != -1 ? 'checked' : '')) : 'disabled'} >
                                            <span class="rsm-switch__slider"></span>
                                        </label>
                                    </div>
                                    <div class="product-card__filters-item">
                                        <div class="product-card__filters-text">
                                            ${rsm.Translate.automaticBackup}
                                        </div>
                                        <label class="rsm-switch">
                                            <input name="autoBackup[${key}]" class="${(isRtlProduct && isInstalled) ? 'auto-backup-' + type : 'disabled'}"
                                                   type="checkbox" ${(isRtlProduct && isInstalled) ? (isOnBackup ? 'checked' : ($.inArray(key, settings['autoBackup']) != -1 ? 'checked' : '')) : 'disabled'}>
                                            <span class="rsm-switch__slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="product-card__bottom ${($item['updateInfo']).length !== 0 ? '' : 'product-card__bottom-update-less'}">`
            if (($item['updateInfo']).length !== 0) {
                html += `<div class="product-card__alert ${isRtlProduct ? '' : 'product-card__alert--danger'} ">
                                                        <div class="product-card__alert-icon">
                                                            <svg width="19" height="19" viewBox="0 0 19 19" fill="none"
                                                                 xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M9.53125 0.59375C10.7688 0.59375 11.9375 0.83125 13.0375 1.30625C14.125 1.76875 15.075 2.4125 15.8875 3.2375C16.7125 4.05 17.3563 5 17.8188 6.0875C18.2938 7.1875 18.5312 8.35625 18.5312 9.59375C18.5312 10.8313 18.2938 12 17.8188 13.1C17.3563 14.1875 16.7125 15.1438 15.8875 15.9688C15.075 16.7812 14.125 17.4188 13.0375 17.8813C11.9375 18.3563 10.7688 18.5938 9.53125 18.5938C8.29375 18.5938 7.125 18.3563 6.025 17.8813C4.9375 17.4188 3.98125 16.7812 3.15625 15.9688C2.34375 15.1438 1.70625 14.1875 1.24375 13.1C0.76875 12 0.53125 10.8313 0.53125 9.59375C0.53125 8.35625 0.76875 7.1875 1.24375 6.0875C1.70625 5 2.34375 4.05 3.15625 3.2375C3.98125 2.4125 4.9375 1.76875 6.025 1.30625C7.125 0.83125 8.29375 0.59375 9.53125 0.59375ZM8.63125 5.69375V10.4938C8.63125 10.7438 8.71875 10.9563 8.89375 11.1313C9.06875 11.3063 9.28125 11.3938 9.53125 11.3938C9.78125 11.3938 9.99375 11.3063 10.1688 11.1313C10.3438 10.9563 10.4313 10.7438 10.4313 10.4938V5.69375C10.4313 5.44375 10.3438 5.23125 10.1688 5.05625C9.99375 4.88125 9.78125 4.79375 9.53125 4.79375C9.28125 4.79375 9.06875 4.88125 8.89375 5.05625C8.71875 5.23125 8.63125 5.44375 8.63125 5.69375ZM9.53125 14.9938C9.85625 14.9938 10.1313 14.8813 10.3563 14.6563C10.5938 14.4188 10.7125 14.1375 10.7125 13.8125C10.7125 13.4875 10.5938 13.2125 10.3563 12.9875C10.1313 12.75 9.85625 12.6313 9.53125 12.6313C9.20625 12.6313 8.925 12.75 8.6875 12.9875C8.4625 13.2125 8.35 13.4875 8.35 13.8125C8.35 14.1375 8.4625 14.4188 8.6875 14.6563C8.925 14.8813 9.20625 14.9938 9.53125 14.9938Z"
                                                                      fill="#65AA40"/>
                                                            </svg>
                                                        </div>
                                                        <div class="product-card__alert-text">
                                                         ${$item['updateInfo']['message']}
                                                        </div>
                                                    </div>`

            }
            html += `<div class="product-card__actions">
                        <div class="product-card__accordion">
                            <div class="product-card__accordion-item">
                                <div class="product-card__accordion-title">
                                    <span class="product-card__accordion-title-text"></span>
                                    <div class="product-card__accordion-title-actions">
                                        <button
                                            class="btn btn--has-icon show-changelog-${type} ${hasChangeLog ? 'disabled' : ''}"
                                            ${hasChangeLog ? 'disabled' : ''} data-slug="${hasChangeLog ? '' : key}" >
                                            <img src="${rsm.obj.list}"
                                                 alt="list">
                                                ${rsm.Translate.versionChanges}
                                        </button>
                                        <button
                                            class="btn btn--has-icon restore-prev-${type} ${($item['hasBackup']).length === 0 ? 'disabled' : ''}"
                                            ${($item['hasBackup']).length === 0 ? 'disabled' : ''}
                                            data-restore-url="${$item['hasBackup']}">
                                            <img src="${rsm.obj.reload}" alt="reload">
                                              ${rsm.Translate.returnPrevious}
                                        </button>`

            if (($item['isPurchased']) === false && isRtlProduct) {
                if (type === 'theme') {
                    html += `<button class="btn btn--has-icon btn--blue RCU-Debug-Theme-BTN">
                                                      <img src="${rsm.assets}media/icon/verify-white.svg">
                                                      ${rsm.Translate.manualDebugging}
                                                    </button>`
                }
                if (type === 'plugin' && $item['status'] === 'Deactivate') {
                    html += `<button class="btn btn--has-icon btn--blue RCU-Debug-Plugin-BTN">
                                                      <img src="${rsm.assets}media/icon/verify-white.svg">
                                                      ${rsm.Translate.manualDebugging}
                                                    </button>`
                }

            } else if (($item['updateInfo']).length !== 0 && isRtlProduct) {
                html += `<button data-update-url="${$item['updateInfo']['updateLink']}"
                                                                            data-name="${$item['name']}"
                                                                            class="btn btn--has-icon btn--blue update-theme">
                                                        <svg width="15" height="16" viewBox="0 0 15 16" fill="none"
                                                             xmlns="http://www.w3.org/2000/svg">
                                                            <path opacity="0.8"
                                                                  d="M4.83716 5.625C4.15356 5.625 3.53345 5.8252 2.97681 6.22559C2.42993 6.62598 2.04907 7.14844 1.83423 7.79297C1.76587 7.98828 1.64868 8.14453 1.48267 8.26172C1.31665 8.37891 1.13599 8.4375 0.940674 8.4375C0.891846 8.4375 0.843018 8.43262 0.794189 8.42285C0.745361 8.41309 0.696533 8.40332 0.647705 8.39355C0.403564 8.30566 0.2229 8.14941 0.105713 7.9248C-0.0114746 7.69043 -0.0310059 7.45117 0.0471191 7.20703C0.2229 6.68945 0.467041 6.21582 0.779541 5.78613C1.10181 5.36621 1.4729 5.00488 1.89282 4.70215C2.31274 4.39941 2.77173 4.16504 3.26978 3.99902C3.77759 3.83301 4.30005 3.75 4.83716 3.75H9.93481L8.71899 2.53418C8.53345 2.34863 8.44067 2.12891 8.44067 1.875C8.44067 1.61133 8.53345 1.3916 8.71899 1.21582C8.90454 1.03027 9.12427 0.9375 9.37817 0.9375C9.64185 0.9375 9.86157 1.03027 10.0374 1.21582L12.8499 4.02832C13.0354 4.21387 13.1282 4.43848 13.1282 4.70215C13.1282 4.95605 13.0354 5.1709 12.8499 5.34668L10.0374 8.15918C9.85181 8.34473 9.6272 8.4375 9.36353 8.4375C9.10962 8.4375 8.89478 8.34473 8.71899 8.15918C8.53345 7.97363 8.44067 7.75391 8.44067 7.5C8.44067 7.23633 8.53345 7.0166 8.71899 6.84082L9.93481 5.625H4.83716ZM14.3586 8.48145C14.6028 8.56934 14.7834 8.73047 14.9006 8.96484C15.0178 9.18945 15.0374 9.42383 14.9592 9.66797C14.7834 10.1855 14.5393 10.6592 14.2268 11.0889C13.9045 11.5088 13.5334 11.8701 13.1135 12.1729C12.6936 12.4756 12.2346 12.71 11.7366 12.876C11.2288 13.042 10.7063 13.125 10.1692 13.125H5.07153L6.28735 14.3408C6.4729 14.5264 6.56567 14.7461 6.56567 15C6.56567 15.2637 6.4729 15.4834 6.28735 15.6592C6.10181 15.8447 5.8772 15.9375 5.61353 15.9375C5.35962 15.9375 5.14478 15.8447 4.96899 15.6592L2.15649 12.8467C1.97095 12.6611 1.87817 12.4414 1.87817 12.1875C1.87817 11.9238 1.97095 11.7041 2.15649 11.5283L4.96899 8.71582C5.15454 8.53027 5.37427 8.4375 5.62817 8.4375C5.89185 8.4375 6.11157 8.53027 6.28735 8.71582C6.4729 8.90137 6.56567 9.12598 6.56567 9.38965C6.56567 9.64355 6.4729 9.8584 6.28735 10.0342L5.07153 11.25H10.1692C10.8528 11.25 11.468 11.0498 12.0149 10.6494C12.5715 10.249 12.9573 9.72656 13.1721 9.08203C13.2405 8.88672 13.3577 8.73047 13.5237 8.61328C13.6897 8.49609 13.8704 8.4375 14.0657 8.4375C14.1145 8.4375 14.1633 8.44238 14.2122 8.45215C14.261 8.46191 14.3098 8.47168 14.3586 8.48145Z"
                                                                  fill="white"/>
                                                        </svg>
                                                              ${rsm.Translate.update}
                                                    </button>`
            } else if (($item['updateInfo']).length !== 0 && !isRtlProduct) {

                html += `<a href="${$item['updateInfo']['buyLink']}"
                                                                       class="btn btn--has-icon btn--green" target="_blank">
                                                        <img src="${rsm.obj.rtl}" alt="rtl">
                                                              ${rsm.Translate.buyFrom}
                                                    </a>`
            } else if (isRtlProduct && !isInstalled) {
                html += `<button type="button"  class="btn btn--has-icon btn--green install-plugin" data-install-url="${$item['updateInfo']['installLink']}"  >
                                                        <img src="${rsm.obj.rtl}" alt="rtl">
                                                         ${rsm.Translate.install}
                                                    </button>`
            } else {
                html += `<button class="btn btn--has-icon disabled" disabled>
                                                        <img src="${rsm.obj.list}">
                                                             ${rsm.Translate.latest}
                                                    </button>`
            }
            html += `</div>
                                </div>
                            </div>
                        </div>
                    </div>`
            html += `</div>`
            // dont have change log
            //let hasChangeLog = ($item['changeLog'] === null || $item['changeLog'] === '' || $.isEmptyObject($item['changeLog']));
            html += `</div></div></div>`


        })

        return html
    }

    if (rsm.hasJsonList === 0 && $.ajaxProductInProgress === false) {
        $('.rsm-check-update-product').trigger('click')
    }

    $('.RCU-Debug-Theme-BTN').on("click", async function () {
        if ($.ajaxInProgress === true || typeof rsm === 'undefined') {
            return;
        }

        let debugBTN = $(this);

        $.ajaxInProgress = true;
        debugBTN.attr('disabled')

        await DiagnoseSync();

        debugBTN.prop('disabled', false)
        $.ajaxInProgress = false;

        rsmSearchThemeHandler()
    });

    $('.RCU-Debug-Plugin-BTN').on("click", async function () {
        if ($.ajaxInProgress === true || typeof rsm === 'undefined') {
            return;
        }

        let debugBTN = $(this);

        $.ajaxInProgress = true;
        debugBTN.rtlButton('loading')

        await DiagnoseSync();

        debugBTN.rtlButton('reset')
        $.ajaxInProgress = false;

        rsmSearchPluginHandler()
    });

    /**
     * Ping Test
     */
    async function DiagnoseSync() {
        /*let statusBadge = $('#sync-badge');
        let statusBadgePing = $('#sync-ping');

        statusBadge.attr('class', rsm.class.spinner);
        statusBadge.text('');
        statusBadgePing.text('');*/
        await $.ajax({
            method: 'POST',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxDiagnoseSync',
                'nonce' : rsm.nonce,
                'tab'   : 'license'
            }
        }).done((resp) => {
            if (resp.success === true) {
                rtlSuccess(resp.data.message).then(function () {
                    // window.location.reload()
                })
            } else {
                rtlError(resp.data.message).then(function () {
                    // window.location.reload()
                })
            }
        }).fail(function (response) {
            let resp = response.responseText
            if (typeof resp === 'string') {
                resp = $.parseJSON(response.responseText)
            }
            rtlError(resp.data.message)
        })
    }

})

