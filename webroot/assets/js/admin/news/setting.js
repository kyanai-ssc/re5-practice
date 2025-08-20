(function (window, $, app) {
    $(function () {
        // リマインダ単位変更
        $(document).on('change', '[name$="news_new_period_type"]', function(_event) {
            
            var unit = $('select[name$="news_new_period_type"]').val();
            var checkTime = $('select[name$="news_new_period_type"]').data('time');
            var checkDay = $('select[name$="news_new_period_type"]').data('day');
            
            if (unit == checkTime) {
                $('.js_news_new_period_number_hour').show();
                $('.js_news_new_period_number_day').hide();
            }
            if (unit == checkDay) {
                $('.js_news_new_period_number_hour').hide();
                $('.js_news_new_period_number_day').show();
            }
        });
        
        $('select[name$="news_new_period_type"]').trigger('change');
        
    });
})(window, jQuery, window.app);
