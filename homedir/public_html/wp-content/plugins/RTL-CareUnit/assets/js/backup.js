(jQuery)(document).ready(function ($){

    $('#submit-backup').on('click',function (){
        let autoBackupAllProduct = $('#auto-backup-all-product').is(':checked') ? 'on' : 'off';
        let backupNumber = $('#select-file-number').val()
        rtlShowLoading();
        $.ajax({
            method: 'POST',
            url: rsm.url,
            dataType: 'json',
            data: {
                'action': 'rsmAjaxBackupSettings',
                'nonce': rsm.nonce,
                'tab': 'backup',
                'backupNumber' : backupNumber,
                'autoBackupAllProduct':autoBackupAllProduct
            }
        }).done(function (resp) {
            if (resp.success === true){
                rtlSuccess(resp.data)
            }else{
                rtlError(resp.data)
            }
        }).fail(function (response) {
            let resp = response.responseText
            if (typeof resp === 'string') {
                resp = $.parseJSON(response.responseText)
            }
            rtlError(resp.data).then(function (){
                window.location.reload()
            })
        })

    })

    $('.delete-backup').on('click',function (){
        let btn = $(this)
        let fileName = btn.data('name')
        let id = btn.data('id')
        let type = btn.data('type')
        Swal.fire({
            title: rsm.Translate.ays,
            text: rsm.Translate.deletedAlert,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef5350",
            cancelButtonColor: "#3085d6",
            confirmButtonText: rsm.Translate.yesDeleted,
            cancelButtonText: rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                rtlShowLoading()
                $.ajax({
                    method: 'POST',
                    url: rsm.url,
                    dataType: 'json',
                    data: {
                        'action': 'rsmAjaxDeleteBackup',
                        'nonce': rsm.nonce,
                        'tab': 'backup',
                        'type': type,
                        'fileName' : fileName
                    }
                }).done(function (resp) {
                    if (resp.success === true){
                        rtlSuccess(resp.data).then(function (){
                            $('#'+id).remove();
                        })

                    }else{
                        rtlError(resp.data)
                    }

                }).fail(function (response) {
                    let resp = response.responseText
                    if (typeof resp === 'string') {
                        resp = $.parseJSON(response.responseText)
                    }
                    rtlError(resp.data)
                })

            }
        });

    })

    /**
     *  get restore question for confirm by user after accepted by user,
     *  the plugin will be restored to selected version backup
     */
    $('.restore-plugin,.restore-theme').on('click',function (e){
        e.preventDefault();
        let btn = $(this)
        let restoreLink = btn.data('restore-url');

        Swal.fire({
            title: rsm.Translate.ays,
            text: rsm.Translate.restoreAlert,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef5350",
            cancelButtonColor: "#3085d6",
            confirmButtonText: rsm.Translate.yesRestore,
            cancelButtonText: rsm.Translate.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                let win = window.open(restoreLink, '_blank');
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


    //ready
})
