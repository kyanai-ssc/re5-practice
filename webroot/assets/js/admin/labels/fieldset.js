(function (window, $, app) {
    $(function () {
        $(document).on('change', '.js_label_select_id', function(_event) {
            var labels = $(_event.target).closest('.js_label_select');

            if (labels.find('.js_label_select_label_count').val() > 0) {
                labels.siblings('.js_parent_id_description').hide();
            } else {
                labels.siblings('.js_parent_id_description').show();
            }
        });
    });
})(window, jQuery, window.app);
