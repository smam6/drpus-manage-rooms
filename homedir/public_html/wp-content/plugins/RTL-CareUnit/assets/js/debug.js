(jQuery)(document).ready(function ($) {
    let keyValue   = -1;
    let firstTime  = 0;
    let objectKeys = {};

    $('#rsmLogin').on('submit', function (e) {
        e.preventDefault()
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
                    window.location.reload()
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

    $('.debug-connection').on('click', function (e) {
        e.preventDefault()
        let btn  = $(this)
        let text = btn.text();
        let type = btn.data('type')
        btn.prop('disable', true);
        btn.text('در حال تست');
        $('#' + type + '-msg').html('')
        let data = {
            'action': 'rsmAjaxDebugConnection',
            'nonce' : rsm.nonce,
            'tab'   : 'debug',
            'type'  : type
        }
        $.ajax({
            method  : 'POST',
            url     : rsm.url,
            data    : data,
            dataType: 'json',

        }).done(function (response) {
            btn.prop('disable', false);
            btn.text(text);
            let message = response.data
            let html    = ''
            if (typeof message == 'object' && message !== null) {
                html = checkObject(message);
            } else {
                html = `<div class="dd-div">
                    <div class="dd-value"> ${message}</div>
                </div>`
            }

            $('#' + type + '-msg').html(html)
        }).fail(function () {
            btn.prop('disable', false);
            btn.text(text);
            rtlError()
        })


    })

    $('#check-server').on('click', function (e) {
        e.preventDefault()
        $('#custom-error').empty()
        let btn               = $(this)
        let slug              = $('#server-id').val();
        let connectionType    = $('#connection-type').val();
        let connectionTimeOut = $('#connection-timeout').val();
        let newUrl            = '';
        if (slug == null) {
            rtlError('سرور را انتخاب کنید')
            return
        }
        if (slug === 'new-address') {
            newUrl = $('#new-url').val();
            if (newUrl == null) {
                rtlError('ادرس جدید را وارد کنید')
                return
            }
        }
        let text   = btn.text();
        let option = $('#' + slug)
        btn.prop('disable', true);
        btn.text('در حال تست');

        let data = {
            'action'         : 'rsmCheckConnection',
            'nonce'          : rsm.nonce,
            'tab'            : 'check',
            'slug'           : slug,
            'connectionType' : connectionType,
            connectionTimeOut: connectionTimeOut,
            newUrl           : newUrl
        }

        $.ajax({
            method  : 'POST',
            url     : rsm.url,
            data    : data,
            dataType: 'json',

        }).done(function (response) {
            btn.prop('disable', false);
            btn.text(text);
            let server = response.data.server

            if (typeof server?.html !== 'undefined') {
                let html = `<div>Response </div> <div>${response.data.server.html}</div>`;
                $('#custom-error').html(html)
            } else {
                let title  = response.data.message
                let status = server?.status
                let msg    = `نام سرور = ${server?.title} --- پینگ = ${server?.ping} --- وضعیت = ${status ? 'فعال' : 'غیر فعال'}`
                option.text(msg)
                if (status) {
                    option.css('background-color', '#fff')
                    rtlSuccess(msg, title)
                } else {
                    option.css('background-color', '#f79494')
                    rtlError(msg)
                }
            }


        }).fail(function () {
            rtlError()
        })


    })

    $('#open-rtl-iframe').on('click', function (e) {

        e.preventDefault()

        $('#rtl-iframe').attr('src', '')
        $('#rtl-iframe-modal').hide()
        $('#loadingMessage').show();

        rtlShowLoading()

        let slug   = $('#server-id').val();
        let newUrl = '';
        let src    = '';

        if (slug == null) {
            rtlError('سرور را انتخاب کنید')
            return
        }

        if (slug === 'new-address') {
            newUrl = $('#new-url').val();
            if (newUrl == null) {
                rtlError('ادرس جدید را وارد کنید')
                return
            }
            src = newUrl;
            $('#rtl-iframe').attr('src', src)
            $('#rtl-iframe-modal').show()
            rtlHideLoading()
        } else {
            let data = {
                'action': 'rsmGetServer',
                'nonce' : rsm.nonce,
                'tab'   : 'check',
                'slug'  : slug,
            }
            $.ajax({
                method  : 'GET',
                url     : rsm.url,
                data    : data,
                dataType: 'json',

            }).done(function (response) {
                src = response.data
                $('#rtl-iframe').attr('src', src)
                $('#rtl-iframe-modal').show()
                rtlHideLoading()
            }).fail(function () {
                rtlError()
            })
        }


    })

    $('#close-iframe').on('click', function () {
        $('#rtl-iframe').attr('src', '')
        $('#rtl-iframe-modal').hide()
    })

    // $('#rtl-iframe').ready(function () {
    //     $('#loadingMessage').hide();
    // });
    // $('#rtl-iframe').load(function () {
    //     $('#loadingMessage').css('display', 'none');
    // });

    $('#install-product').on('click', function () {

        let url              = $('#productUrl').val()
        let type             = $('#product-install-type').val()
        let productNonce     = $('#productNonce').val()
        let pluginInstallUrl = $('#pluginInstallUrl').val()
        let installUrl       = '';
        if (!url) {
            rtlError('ادرس معتبر نیست');
            return;
        }
        if (type != 'plugin') {
            rtlError('این قسمت در حال توسعه هست');
            return;
        } else {
            installUrl = pluginInstallUrl;
        }

        let installLink = installUrl + '&url=' + url + '&type=' + type + '&nonce=' + productNonce


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


    $('#get-ip').on('click', function (e) {

        e.preventDefault()
        $('#rtl-ip').empty()
        rtlShowLoading()

        let data = {
            'action': 'rsmGetIp',
            'nonce' : rsm.nonce,
            'tab'   : 'check',

        }

        $.ajax({
            method  : 'GET',
            url     : rsm.url,
            data    : data,
            dataType: 'json',

        }).done(function (response) {
            $('#rtl-ip').html(response.data)
            rtlSuccess()
        }).fail(function () {
            rtlError()
        })


    })

    $('#server-id').on('change', function () {
        let val = $(this).val()
        if (val == 'new-address') {
            $('#new-url').show();
        } else {
            $('#new-url').hide();
        }
    })


    function checkObject(data) {
        let html = ''
        if (typeof data == 'object' && data !== null) {
            let keys              = Object.keys(data)
            objectKeys[firstTime] = keys
            firstTime++;
            $.each(data, function (key, val) {
                if (key == 'changeLog') {
                    return;
                }

                if ($.inArray(key + '', objectKeys[firstTime - 1]) === -1) {
                    if (key !== keyValue && $.inArray(key + '', objectKeys[0]) !== -1) {
                        html += '<div class="dd-div" ><span class="dd-title" >' + key + '</span>'
                    }
                }
                if (key !== keyValue && $.inArray(key + '', objectKeys[firstTime - 1]) !== -1) {
                    html += '<div class="dd-div" ><span class="dd-title" >' + key + '</span>'
                }
                if (typeof val == 'object' && val !== null) {
                    $.each(val, function (key1, value) {
                        if (key1 == 'changeLog') {
                            return;
                        }
                        if (typeof value == 'object' && value !== null) {
                            html += '<span class="dd-key"> [' + key1 + ']=></span>'
                            html += checkObject(value)
                        } else {
                            html += `<div 1><span class="dd-key"> ${key1} => <span class="dd-value">${value}</span></span></div>`;
                        }
                    })
                } else {
                    html += `<div 2><span class="dd-key"> ${key} => <span class="dd-value">${val}</span></span></div>`;
                }
                if ($.inArray(key + '', objectKeys[firstTime - 1]) === -1) {
                    if (key !== keyValue && $.inArray(key + '', objectKeys[0]) !== -1) {
                        keyValue = key
                        html += '</div>'
                    }
                }
                if (key !== keyValue && $.inArray(key + '', objectKeys[firstTime - 1]) !== -1) {
                    keyValue = key
                    html += '</div>'
                }

            })

        }

        return html;
    }

    $('#rsmTogglePass').on('click', function () {
        const passInput = $('#rsmPass');
        const type      = passInput.attr('type') === 'password' ? 'text' : 'password';
        passInput.attr('type', type);
        $(this).text(type === 'password' ? 'Show' : 'Hide');
    });
})