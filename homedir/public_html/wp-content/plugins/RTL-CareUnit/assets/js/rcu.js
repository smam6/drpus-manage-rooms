(jQuery)(document).ready(function ($) {


    /*
    * global register in hook rsm variable
    */
    // conflict ajax
    $.ajaxInProgress = false;
    let debugBTN;

    $('.RCU-Debug-BTN').on("click", async function () {
        if ($.ajaxInProgress === true || typeof rsm === 'undefined') {
            return;
        }

        debugBTN       = $(this);
        let debugBadge = $('.license__accordion-title-content .license__accordion-title-badge');

        $.ajaxInProgress = true;
        debugBTN.rtlButton('loading')

        debugBadge.attr('class', 'license__accordion-title-badge license__accordion-title-badge--warning');
        debugBadge.text(rsm.Translate.notTested);

        try {
            await DiagnoseConTest();
            await DiagnoseHandShake();
            await DiagnoseSync();
            // await DiagnoseFileAccess();
        } catch (err) {
        }
        debugBTN.rtlButton('reset')
        $.ajaxInProgress = false;
    });


    /**
     * Connection Test
     */
    async function DiagnoseConTest() {
        let statusBadge     = $('#conTest-badge');
        let statusBadgePing = $('#conTest-ping');

        statusBadge.attr('class', rsm.class.spinner);
        statusBadge.text('');
        statusBadgePing.text('');
        await $.ajax({
            method: 'POST',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxDebugConTest',
                'nonce' : rsm.nonce,
                'tab'   : 'license'
            }
        }).done((response) => {
            handleResponse('#conTest', response, false)
        }).fail(function (e) {
            handleResponse('#conTest', e, true)
        });
    }

    /**
     * Handshake Test
     */
    async function DiagnoseHandShake() {

        let statusBadge     = $('#handShake-badge');
        let statusBadgePing = $('#handShakeBadge-ping');

        statusBadge.attr('class', rsm.class.spinner);
        statusBadge.text('');
        statusBadgePing.text('');

        await $.ajax({
            method: 'POST',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxDiagnoseHandShake',
                'nonce' : rsm.nonce,
                'tab'   : 'license'
            }
        }).done((response) => {
            handleResponse('#handShake', response, false)
        }).fail(function (e) {
            handleResponse('#handShake', e, true)
        });
    }

    /**
     * Ping Test
     */
    async function DiagnoseSync() {
        let statusBadge       = $('#sync-badge');
        let statusBadgePing   = $('#sync-ping');


        statusBadge.attr('class', rsm.class.spinner);
        statusBadge.text('');
        statusBadgePing.text('');
        await $.ajax({
            method: 'POST',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxDiagnoseSync',
                'nonce' : rsm.nonce,
                'tab'   : 'license'
            }
        }).done((response) => {
            handleResponse('#sync', response, false);
        }).fail(function (response) {
            handleResponse('#sync', response, true)
        });
    }

    /**
     * File Access Test
     */
    async function DiagnoseFileAccess() {
        let statusBadge     = $('#fileAccess-badge');
        let statusBadgePing = $('#fileAccess-ping');

        statusBadge.attr('class', rsm.class.spinner);
        statusBadge.text('');
        statusBadgePing.text('');

        await $.ajax({
            method: 'POST',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxDiagnoseFileAccess',
                'nonce' : rsm.nonce,
                'tab'   : 'license'
            }
        }).done((response) => {
            handleResponse('#fileAccess', response, false)
        }).fail(function (e) {
            handleResponse('#fileAccess', '', true)
        });
    }


    /**
     * create html message for accordion
     * @param object
     * @returns {string}
     */
    function createMsgHtml(object) {

        let html = ''
        $.each(object, function (key, value) {
            if (key != 'ping') {
                html += `<div class='license__accordion-content-text'>${value}</div>`
            }
        })
        return html;
    }

    /**
     * handle success response
     */
    function handleResponse(sectionId, response, fail) {
        let syncStatusBoxIcon = $('#license-sync-box-icon');
        let syncStatusBoxText = $('#license-sync-box-text');
        let statusBadge     = $(sectionId + '-badge');
        let statusBadgePing = $(sectionId + '-ping');

        if (fail){
            if (typeof response.responseText === 'string' ){
                response = $.parseJSON(response.responseText)
            }else {
                response = response.responseText;
            }

        }else{
            if (typeof response === 'string' ){
                response = $.parseJSON(response)
            }
        }
        console.log('log',response)

        if (typeof response?.success === 'undefined' || typeof response?.data === 'undefined') {
            statusBadge.attr('class', rsm.class.unSuccess);
            statusBadge.text(rsm.Translate.error);
            debugBTN.rtlButton('reset')
            syncStatusBoxIcon.attr('src', `${rsm.assets}media/icon/times-circle.svg`)
            syncStatusBoxText.text(rsm.Translate.licenseSyncFailed)
            syncStatusBoxText.alterClass('color-*', 'color-red');
            return;
        }
        let htmlMsg = createMsgHtml(response.data)
        $(sectionId + '-msg').html(htmlMsg);
        if (response.success !== true) {
            statusBadge.attr('class', rsm.class.unSuccess);
            statusBadge.text(rsm.Translate.error);
            syncStatusBoxIcon.attr('src', `${rsm.assets}media/icon/times-circle.svg`)
            syncStatusBoxText.text(rsm.Translate.licenseSyncFailed)
            syncStatusBoxText.alterClass('color-*', 'color-red');
        } else {
            if (sectionId === '#sync'){
                syncStatusBoxIcon.attr('src', `${rsm.assets}media/icon/success-circle.svg`)
                syncStatusBoxText.text(rsm.Translate.licenseSyncSuccess)
                syncStatusBoxText.alterClass('color-*', 'color-green');
            }
            statusBadge.attr('class', rsm.class.success);
            statusBadge.text(rsm.Translate.success);
            statusBadgePing.text(response.data.ping || '')
        }
    }

    $('#register-server').click(function () {
        let serverId = $('#server-id').val();
        if (serverId == 'new-address' || serverId == null) {
            rtlError('این گزینه قابل ذخیره سازی نیست')
            return;
        }
        rtlShowLoading()

        $.ajax({
            method  : 'POST',
            url     : rsm.url,
            dataType: 'json',
            data    : {
                'action'  : 'rsmAjaxSetServer',
                'nonce'   : rsm.nonce,
                'tab'     : 'license',
                'serverId': serverId
            }
        }).done((response) => {
            if (response.success) {
                rtlSuccess(response.data)
            } else {
                rtlError(response.data)
            }

        }).fail(function (response) {
            let resp = response.responseText
            if (typeof resp === 'string') {
                resp = $.parseJSON(response.responseText)
            }
            rtlError(resp?.data)
        })

    })

    /**
     * Handle Ping Button Ajax
     */
    $('#RCU-Ping-BTN').on('click', function () {
        if ($.ajaxInProgress === true || typeof rsm === 'undefined') {
            return;
        }

        $.ajaxInProgress = true;

        // show loading HERE
        rtlShowLoading()

        $.ajax({
            method: 'POST',
            url   : rsm.url,
            dataType: 'json',
            data  : {
                'action': 'rsmAjaxPing',
                'nonce' : rsm.nonce,
                'tab'   : 'license',
            }
        }).done(function (response) {
            if (typeof response?.success === 'undefined' || typeof response?.data === 'undefined') {
                // show error HERE
                rtlError(rsm.Translate.error)
                return false;
            }
            if (response.success === true) {
                $('.LastPing').attr('title', response?.data?.title).text(response?.data?.elapsed);
                rtlSuccess(response?.data?.message).then(function () {
                    location.reload();
                })
            } else {
                rtlError(response?.data?.message)
            }

        }).always(function () {
            $.ajaxInProgress = false;
        });
    });


    // end ready
})
