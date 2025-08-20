(function (window, $, app) {
    var reload = function () {
        app.common.changeLocation($('.js_reload_url').val());
    };

    $(function () {
        $(document).on('adminListItemsEditFinish', function (event) {
            reload();
        });
    });
})(window, jQuery, window.app);
