(function (window, $, app) {
    $(function () {
        $(document).on('change', '.js_together_edit', function(_event) {
            var inputName = $(_event.target).data('input');
            var disabledInput = true;
            if ($(_event.target).is(':checked')) {
                disabledInput = false;
            }
            
            var className = '.js_together_edit_' + inputName;
            if ($(className).length > 0 ){
                $(className + ' input, ' + className + ' select, ' + className + ' textarea').each(function(index, element){ 
                    $(element).prop('disabled', disabledInput);
                });
                
            }
        });

        $(document).on('submit', '.js_event_together_form', function() {
            $('.js_together_edit').trigger('change');
        });

        $('.js_together_edit').trigger('change');
    });
})(window, jQuery, window.app);
