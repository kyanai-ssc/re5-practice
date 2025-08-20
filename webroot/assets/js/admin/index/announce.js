(function (window, $, app) {
    $(function () {
        app.common.ajax('announce', {
            url: app.adminCommon.ajaxUrl() + 'announce/index',
            type: 'get',
            data: {},
            notLoading: true,
        }).done(function (result) {
            if (result.success) {
                if (result.data.announce.length >= 1) {
                    var announceBody = $('#announce-body-base').html();
                    var body = '';
                    $.each(result.data.announce, function (i, val) {
                        body = announceBody.replace(/\:title/g, app.common.htmlEscape(val.title)).replace(':body', app.common.htmlEscape(val.body));
                        $(body).appendTo('#announce-body');
                    });
                    $('#announce').removeClass('hidden');
                }
            }
        });
    });
})(window, jQuery, window.app);
