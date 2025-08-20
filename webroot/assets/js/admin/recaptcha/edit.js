(function (window, $, app) {
    var changeRequired = function () {
        $(document).on('change', '[name$="use_flg"]', function(event) {
            const setting = $('input[name$="use_flg"]:checked').val();
            const flgValues = $('.js_use_flg_container');

            if (setting == flgValues.data('on')) {
                $('.js_required_on_use').find('.any').show();
            } else if (setting == flgValues.data('off')) {
                $('.js_required_on_use').find('.any').hide();
            }
        });

        $('input[name$="use_flg"]').trigger('change');
    }

    $(function () {
        changeRequired();
    });

})(window, jQuery, window.app);
