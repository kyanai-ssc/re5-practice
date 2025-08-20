(function (window, $, app) {
    $(function () {
        $(document).on('change', '.js_access', function(_event) {
            
            var all = $(_event.target).data('all');
            var value = $(_event.target).val();
            
            if ( all != value) {
                $('.js_access:first').prop('checked', false);
            } else {
                $('.js_access').not(_event.target).prop('checked', false);
            }
        });
    });
})(window, jQuery, window.app);
