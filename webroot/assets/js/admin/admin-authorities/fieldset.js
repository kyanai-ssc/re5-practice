(function (window, $, app) {
    $(function () {
        $(document).on('change', '.js_access_setting', function(_event) {

            var all = $(_event.target).data('all');
            var value = $(_event.target).val();

            if ( all != value) {
                $('.js_access_setting:first').prop('checked', false);
            } else {
                $('.js_access_setting').not(_event.target).prop('checked', false);
            }
        });
        $(document).on('change', '.js_access_operator', function(_event) {

            var all = $(_event.target).data('all');
            var value = $(_event.target).val();

            if ( all != value) {
                $('.js_access_operator:first').prop('checked', false);
            } else {
                $('.js_access_operator').not(_event.target).prop('checked', false);
            }
        });
    });
})(window, jQuery, window.app);
