(function ($) {
    jQuery.fn.extend({
        rtlButton: function (flag) {
            let btn = $(this)
            let html = btn.html();
            let text_loading = btn.data('loading-text')
            let text_reset = btn.data('loading-reset')

            if (typeof text_loading === 'undefined') {
                text_loading = "<i class='rsm-spinner loading white mx-05'></i>  در حال اجرا "
            } else {
                text_loading = "<i class='rsm-spinner loading white mx-05'></i>  " + text_loading
            }

            switch (flag) {
                case 'loading':
                    btn.html(text_loading)
                    btn.data('loading-reset', html)
                    btn.prop('disabled', true).addClass('disabled').css({'opacity': '0.65', 'cursor': 'default'})
                    break
                case 'reset':
                    btn.html(text_reset)
                    btn.prop('disabled', false).css({'opacity': '', 'cursor': 'pointer'}).removeClass('disabled')
                    break
            }

            return this;
        }
    });

    jQuery.fn.alterClass = function ( removals, additions ) {

        var self = this;

        if ( removals.indexOf( '*' ) === -1 ) {
            // Use native jQuery methods if there is no wildcard matching
            self.removeClass( removals );
            return !additions ? self : self.addClass( additions );
        }

        var patt = new RegExp( '\\s' +
            removals.
            replace( /\*/g, '[A-Za-z0-9-_]+' ).
            split( ' ' ).
            join( '\\s|\\s' ) +
            '\\s', 'g' );

        self.each( function ( i, it ) {
            var cn = ' ' + it.className + ' ';
            while ( patt.test( cn ) ) {
                cn = cn.replace( patt, ' ' );
            }
            it.className = $.trim( cn );
        });

        return !additions ? self : self.addClass( additions );
    };

})(jQuery);

function rtlShowLoading(message = 'لطفاً صبور باشید...', title = 'در حال انجام عملیات') {
    Swal.fire({
        showConfirmButton: false,
        allowOutsideClick: false,
        target: document.querySelector(".rsm-content"),
        html:
            '<div class="backup-alert">' +
            ' <span class="rsm-spinner loading"></span>' +
            '<div class="alert-title">'+title+'</div>' +
            '<div class="alert-text">'+message+'</div> ' +
            '</div>',
    });
}

function rtlHideLoading() {
    Swal.close()
}

function rtlSuccess(message,title) {
    let msg = message || "";
    let header = title || rsm.Translate.operationSuccess;
    const backupEndAlertButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn--light-blue backup-end-btn",
        },
        buttonsStyling: false,
    });
    return backupEndAlertButtons.fire({
        title: header,
        text: msg,
        icon: "success",
        target: document.querySelector(".rsm-content"),
        showCancelButton: false,
        confirmButtonText: rsm.Translate.close,
        reverseButtons: false,
    })
}

function rtlError($message) {
    let msg = $message || "";

    const backupEndAlertButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn--light-blue backup-end-btn",
        },
        buttonsStyling: false,
    });
    return backupEndAlertButtons.fire({
        title: rsm.Translate.operationFailed,
        text: msg,
        icon: "error",
        showCancelButton: false,
        target: document.querySelector(".rsm-content"),
        confirmButtonText: rsm.Translate.close,
        reverseButtons: false,
    })

}

function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}



