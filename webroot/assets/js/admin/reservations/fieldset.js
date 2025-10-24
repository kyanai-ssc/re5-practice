(function (window, $, app) {
    var userDialog = null;
    var calendarDialog = null;

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
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/change-form', query),
            type: 'post',
            data: form.serialize()
        }).done(function (result) {
            $('.js_reservation_form').replaceWith(result.html);
            settingForm();
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 会員を選択
    var selectUser = function (event) {
        if (userDialog !== null) {
            $(userDialog).dialog('close');
        }

        userDialog = app.common.dialog($('.js_select_user_iframe').val(), {
            width: app.common.getScreenWidth(1000, 95, 960),
            height: 800,
            title: $(event.target).data('title'),
        });

        userDialog.on('dialogclose', function (event, ui) {
            userDialog = null;
        });
    };

    // 枠を選択
    var selectCalendar = function (event) {
        if (calendarDialog !== null) {
            $(calendarDialog).dialog('close');
        }

        calendarDialog = app.common.dialog($('.js_select_calendar_iframe').val(), {
            width: app.common.getScreenWidth(1000, 95, 960),
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

    // 料金を計算
    var calculateCharge = function (data) {
        var deffered = new $.Deferred();
        var query = {};

        $('.js_reservation_parameter').each(function (index, element) {
            query[$(element).data('name')] = $(element).val();
        });

        app.common.ajax('reservationsCalculateCharge', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/calculate-charge', query),
            type: 'post',
            data: data
        }).done(function (result) {
            deffered.resolve(result.charge);
        }).fail(function (error) {
            deffered.reject();
            app.common.errorDialog(error);
        });

        return deffered.promise();
    };

    // 料金表示の制御
    var toggleReservationCharge = function () {
        if ($('.js_calculate_charge_check').prop('checked')) {
            $('.js_calculate_charge').hide();
            $('.js_reservation_charge').hide();
            $('.js_reservation_charge').val('');
        } else {
            $('.js_reservation_charge').show();
            $('.js_calculate_charge').show();
        }
    };

    // フォームの設定
    var settingForm = function () {
        app.common.useDatePicker();
        toggleReservationOption();
        toggleReservationCharge();
        $('.js_reservation_accordion').each(function (index, element) {
            app.common.accordion(element);
        });
    };

    $(function () {
        // 予約タイプ変更
        $(document).on('change.changeReservationType', '.js_reservation_type', function (event) {
            changeParameter({reservation_type: $(event.target).val()});
        });

        // 会員選択画面
        $(document).on('click.selectUser', '.js_select_user', function (event) {
            selectUser(event);

            event.preventDefault();
        });

        // 会員選択画面での選択
        $(document).on('selectUser', function (event, data) {
            if (userDialog !== null) {
                $(userDialog).dialog('close');
            }

            changeParameter({user_id: data.id});
        });

        // 会員検索項目変更
        $(document).on('adminSearchItemsEditFinish', function (event) {
            if ($('.js_search_payment_expired_query').length > 0) {
                changeParameter({search_payment_expired: 1});
            } else {
                changeParameter();
            }
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

            let parameter = {
                event_id: data.event_id,
                usage_timestamp_from: data.usage_timestamp_from
            };

            if ($('.js_search_payment_expired_query').length > 0) {
                parameter['search_payment_expired'] = 1;
            }

            changeParameter(parameter);
        });

        // 権限変更
        $(document).on('change.changeUserAuthorityId', '.js_user_authority_id', function (event) {
            if ($(event.target).val() !== '') {
                changeParameter({user_authority_id: $(event.target).val()});
            }
        });

        // オプション選択
        $(document).on('change.reservationOptionCheck', '.js_reservation_option_check', function (event) {
            toggleReservationOption();
        });

        // 料金計算
        $(document).on('click.calculateCharge', '.js_calculate_charge', function (event) {
            calculateCharge($('.js_reservation_form').serialize()).done(function (charge) {
                $('.js_reservation_charge').val(charge);
            });

            event.preventDefault();
        });

        // 自動計算チェック
        $(document).on('change.calculateChargeCheck', '.js_calculate_charge_check', function (event) {
            toggleReservationCharge();
        });

        // 繰り返し予約選択変更
        $(document).on('change', '.js_change_repeat_reservation', function (event) {
            app.common.repeatReservation.changeRepeatReservation();
        });

        // 繰り返し予約曜日選択変更
        $(document).on('change', '.js_select_day_of_week', function (event) {
            app.common.repeatReservation.selectDayOfWeek();
        });

        settingForm();
    });
})(window, jQuery, window.app);
