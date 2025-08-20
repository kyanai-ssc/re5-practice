(function (window, $, app) {
    // リマインダ単位変更
    var changeReminderUnit = function (fieldName, classHour, classDay) {
        $(document).on('change', fieldName, function(_event) {
            var unit = $('input' + fieldName + ':checked').val();
            var checkTime = $('input' + fieldName + ':checked').data('time');
            var checkDay = $('input' + fieldName + ':checked').data('day');

            if (unit == checkTime) {
                $(classHour).show();
                $(classDay).hide();
            }
            if (unit == checkDay) {
                $(classHour).hide();
                $(classDay).show();
            }
        });

        $('input' + fieldName).trigger('change');
    }

    $(function () {
        // 予約リマインダーメール配信時間
        changeReminderUnit(
            '.reservation-reminder-type',
            '.js_reminder_mail_hour',
            '.js_reminder_mail_day');

        // 利用終了リマインダーメール配信時間
        changeReminderUnit(
            '.reservation-close-reminder-type',
            '.js_reservation_close_reminder_mail_hour',
            '.js_reservation_close_reminder_mail_day');
    });

})(window, jQuery, window.app);
