(function (window, $, app) {
    $(function () {
        app.adminCommon.sortableDom();
        // 入力項目追加
        $(document).on('click.addInput', '.js_add_input', function (event) {
            var container = $(event.target).data('container');
            var index = app.common.maxValue($(event.target).data('index-element'), container);
            var addContainer = container + '_' + index;

            app.adminCommon.setColorPicker($(addContainer + ' .colorPicker'));

            event.preventDefault();
        });

    });
})(window, jQuery, window.app);

