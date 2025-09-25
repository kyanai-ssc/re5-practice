(function (window, $, app) {
    var calendarDialog = null;

    // ログイン
    var executeLogin = function (data) {
        var deffered = new $.Deferred();

        app.common.ajax('authLogin', {
            url: app.userCommon.ajaxUrl() + 'auth/login',
            type: 'post',
            data: data
        }).done(function (result) {
            if (result.finish) {
                deffered.resolve();
            } else {
                deffered.reject(true);
                $('.js_login_fieldset').replaceWith(result.fieldsetHtml);
                $('.js_login_error').html(result.errorHtml);
            }
        }).fail(function (error) {
            deffered.reject(false);
            app.common.errorDialog(error);
        });

        return deffered.promise();
    };

    // パラメータを変更
    var changeParameter = function (parameter) {
        var form = $('.js_reservation_form');
        var query = {};

        $('.js_reservation_parameter').each(function (index, element) {
            query[$(element).data('name')] = $(element).val();
        });

        if (typeof (parameter) !== 'object') {
            parameter = {};
        }
        query = $.extend(query, parameter);

        app.common.ajax('reservationsChangeForm', {
            url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'reservations/change-form', query),
            type: 'post',
            data: form.serialize()
        }).done(function (result) {
            $('.js_reservation_form').replaceWith(result.html);
            settingForm();
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 枠を選択
    var selectCalendar = function (event) {
        if (calendarDialog !== null) {
            $(calendarDialog).dialog('close');
        }

        calendarDialog = app.common.dialog($('.js_select_calendar_iframe').val(), {
            width: app.common.getScreenWidth(1200),
            height: 800,
            title: $(event.target).data('title'),
        });

        calendarDialog.on('dialogclose', function (event, ui) {
            calendarDialog = null;
        });
    };

    // オプション表示の制御
    var toggleReservationOption = function () {
        $('.js_reservation_option_number').prop('disabled', true);
        $('.js_reservation_option_check:checked').each(function (index, element) {
            $('.js_reservation_option_number_' + $(element).val()).prop('disabled', false);
        });
    };

    // フォームの設定
    var settingForm = function () {
        app.common.useDatePicker();
        toggleReservationOption();
    };

    // 連続予約：曜日選択表示の制御
    var toggleSelectDayOfWeek = function() {
        if ($('.js_select_day_of_week').prop('checked')) {
            $('.js_day_of_week').show();
        } else {
            $('.js_day_of_week').hide();
        }
    };

    $(function () {
        // ログイン
        $(document).on('submit.login', '.js_reservation_login_form', function (event) {
            executeLogin($('.js_reservation_login_form').serialize()).done(function () {
                var formUrl = $('.js_reservation_form').prop('action');
                var url = app.common.stripUrlQuery(formUrl);
                var query = app.common.parseUrlQuery(formUrl);

                delete query.reservation_type;
                app.common.changeLocation(app.common.mergeUrlQuery(url, query));
            });

            event.preventDefault();
        });

        // 予約タイプ変更
        $(document).on('change.changeReservationType', '.js_reservation_type', function (event) {
            changeParameter({reservation_type: $(event.target).val()});
        });

        // 枠選択画面
        $(document).on('click.selectCalendar', '.js_select_calendar', function (event) {
            selectCalendar(event);

            event.preventDefault();
        });

        // 枠選択画面での選択
        $(document).on('selectCalendar', function (event, data) {
            if (calendarDialog !== null) {
                $(calendarDialog).dialog('close');
            }

            changeParameter({
                event_id: data.event_id,
                usage_timestamp_from: data.usage_timestamp_from
            });
        });

        // オプション選択
        $(document).on('change.reservationOptionCheck', '.js_reservation_option_check', function (event) {
            toggleReservationOption();
        });

        // 繰り返し予約：選択変更
        $(document).on('change', '.js_change_repeat_reservation', function (_event) {
            $('.js_toggle_repeat_reservation').removeClass('hidden');
            $('.js_toggle_repeat_reservation').hide();
            $('.js_toggle_repeat_reservation_' + $('.js_change_repeat_reservation:checked').val()).show();
            if ($('.js_select_day_of_week').prop('checked')) {
                $('.js_day_of_week').show();
            } else {
                $('.js_day_of_week').hide();
            }
        });

        // 繰り返し予約：曜日選択
        $(document).on('change', '.js_select_day_of_week', function (event) {
            toggleSelectDayOfWeek();
            $('.js_day_of_week').removeClass('hidden').hide();
            $('.js_day_of_week_' + $('.js_select_day_of_week:checked').val()).show();
        });

        settingForm();
    });
})(window, jQuery, window.app);
