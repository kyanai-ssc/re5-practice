(function (window, $, app) {
    // 検索項目リロード
    var reloadSearchForm = function () {
        app.common.ajax('adminSearchItemsEditReload', {
            url: app.adminCommon.ajaxUrl() + 'mail-deliveries/search-form',
            type: 'post',
            data: $('.js_mail_deliveries_search_form').serialize()
        }).done(function (result) {
            $('.js_mail_deliveries_search_form').html(result.html);
            app.common.useDatePicker();
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    $(function () {
        // 検索項目変更
        $(document).on('adminSearchItemsEditFinish', function (event) {
            reloadSearchForm();
        });

        app.adminCommon.multipleSelector();

    });
})(window, jQuery, window.app);
