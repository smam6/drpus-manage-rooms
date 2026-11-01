/**
 *  settings page jquery
 */

(jQuery)(document).ready(function ($) {

    $('.rsm-settings').on('click', function () {
        let btn = $(this)
        let val = btn.is(':checked')
        let key = btn.attr('id')
        rtlShowLoading()
        let data = {
            'action': 'rsmAjaxSettings',
            'nonce' : rsm.nonce,
            'tab'   : 'settings',
            'key'   : key,
            'value' : val
        }

        $.ajax({
            method  : 'POST',
            url     : rsm.url,
            dataType: 'json',
            data    : data
        }).done(function (resp) {
            if (resp.success === true) {
                rtlSuccess(resp.data.message)
            } else {
                rtlError(resp.data.message)
            }

        }).fail(function (response) {
            let resp = response.responseText;
            if (typeof response.responseText == "string") {
                resp = $.parseJSON(response.responseText)
            }

            rtlError(resp.data)
        })
    })

    $('#registerEmail').on('click', function () {
        Swal.fire({
            title             : rsm.Translate.ays,
            text              : rsmSettings.Translate.changeEmail,
            icon              : "warning",
            showCancelButton  : true,
            confirmButtonColor: "#039405",
            cancelButtonColor : "#c3d1ec",
            confirmButtonText : rsm.Translate.yes,
            cancelButtonText  : rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                let email = $('#notificationEmail').val()
                if (typeof email == 'undefined' || email == '' || !isEmail(email)) {
                    rtlError(rsmSettings.Translate.emailNotValid)
                    return;
                }

                let data = {
                    'action': 'rsmAjaxSettings',
                    'nonce' : rsm.nonce,
                    'tab'   : 'settings',
                    'key'   : 'notificationEmail',
                    'value' : email
                }
                rtlShowLoading()
                $.ajax({
                    method  : 'POST',
                    url     : rsm.url,
                    data    : data,
                    dataType: 'json'
                }).done(function (resp) {
                    if (resp.success === true) {
                        rtlSuccess(resp.data.message)
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
            }
        });
    })

    $('#hidePlugin').on('click', function () {
        let btn         = $(this)
        let status      = btn.data('status')
        let title       = rsmSettings.Translate.hidePlugin
        let text        = rsmSettings.Translate.keepLink
        let conBtnColor = "#e10606"
        let canBtnColor = "#1f8502"
        if (status == 'show') {
            title           = rsmSettings.Translate.showPlugin
            text            = ''
            let conBtnColor = "#33e501"
            let canBtnColor = "#1f8502"
        }
        Swal.fire({
            title             : title,
            text              : text,
            icon              : "warning",
            showCancelButton  : true,
            confirmButtonColor: conBtnColor,
            cancelButtonColor : canBtnColor,
            confirmButtonText : rsm.Translate.yes,
            cancelButtonText  : rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                rtlShowLoading()

                let data = {
                    'action': 'rsmAjaxHidePlugin',
                    'nonce' : rsm.nonce,
                    'tab'   : 'settings',
                    dataType: 'json',
                    'status': status
                }
                $.ajax({
                    method: 'POST',
                    url   : rsm.url,
                    data  : data
                }).done(function (resp) {
                    if (resp.success === true) {
                        rtlSuccess(resp.data.message).then(function () {
                            window.location.reload()
                        })
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
            }
        });
    })


    // end ready
})
