(function (window, $, app) {
    $(function () {
        $(document).on('click', '.js_file_select', function (event) {
            inputName = window.parent.$('#js_iframeDiv').data('name');
            window.parent.$('input[name="' + inputName + '"]').val($(event.target).data('file'));
            window.parent.$('#js_iframeDiv').dialog('close');
        });
    });
})(window, jQuery, window.app);
