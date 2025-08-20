(function (window, $, app) {
    $(function () {
        $(document).on('change', '.js_together_setting', function(_event) {
            var setIndex = $(_event.target).closest('td').find('.js_together_setting').index(_event.target);
            var checked = $(_event.target).is(':checked');
            var indexTr = $(_event.target).closest('tr').index() + 1;
            var groupId = $(_event.target).closest('table').data('group-id');

            $('.js_together_data_' + groupId + ' tr').eq(indexTr).find('td.js_together_set').each(function(index, element){
                $(element).find('input[type="radio"],input[type="checkbox"]').eq(setIndex).prop('checked', checked);
            });
        });
    });
})(window, jQuery, window.app);