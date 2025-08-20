(function (window, $, app) {
    var dialog = null;

    // プレビュー（時間設定）
    var previewUsageTime = function () {
        var iframeStyle = 'min-width     : 100%; ' +
            'height        : 100%; ';

        if ($('.js_plan_type').attr('type') == 'radio') {
            timePlan = $('form [name=time_plan]:checked').val();
        } else {
            timePlan = $('form [name=time_plan]').val();
        }

        var $getData =
            'time_from=' + $('form [name=time_from]').val() +
            '&time_to=' + $('form [name=time_to]').val() +
            '&event_unit_time=' + $('form [name=event_unit_time]').val() +
            '&usage_unit_time=' + $('form [name=usage_unit_time]').val() +
            '&usage_time_from=' + $('form [name=usage_time_from]').val() +
            '&usage_time_to=' + $('form [name=usage_time_to]').val() +
            '&usage_time_to=' + $('form [name=usage_time_to]').val() +
            '&time_plan=' + timePlan;

        $('#js_frameWindowForPreview').append('<iframe id="js_iframeDiv" width="100%" style="' + iframeStyle + '"></iframe>');
        $('#js_iframeDiv').attr({
            src: app.adminCommon.baseUrl() + 'events/preview-usage?' + $getData,
        });
        var options = {
            close: function () {
                $('#js_iframeDiv').remove();
            },
            width: app.common.getScreenWidth(1000, 95, 960),
            height: 1200,
            modal: true,
        };

        app.common.dialog($('#js_iframeDiv'), options);
    };

    var setDeadlineDateTime = function (plusNum, deadlineTypeElm, deadlineDateTimeElm, deadlineTimeElm) {
        if (Math.round(plusNum) === parseInt(plusNum)) {
            var dt = new Date();
            if (deadlineTypeElm.val() == deadlineTypeElm.data('time')) {
                dt.setHours(parseInt(dt.getHours()) + parseInt(plusNum));
                var hm = dt.getHours() + ':' + dt.getMinutes();
            } else {
                dt.setDate(parseInt(dt.getDate()) + parseInt(plusNum));
                var hm = deadlineTimeElm.val();
            }

            var month = dt.getMonth() + 1;
            var day = dt.getDate();
            var year = dt.getFullYear();

            var dispDate = year + '-' + month + '-' + day + ' ' + hm;
            deadlineDateTimeElm.text(dispDate);
        }
    };

    $(function () {

        $("#tabs").tabs();
        //タブ
        $('.js-tab').tabs();

        //タブのコンテンツに.warningがあればタブの背景色が変わる
        errNum = $('#event_tab').find('.warning').parents().index();
        if (errNum !== -1) {
            $('.js_tab_event').addClass('errTab');
        }

        errNum = $('#event_holidays_tab').find('.warning').parents().index();
        if (errNum !== -1) {
            $('.js_tab_event_holiday').addClass('errTab');
        }

        errNum = $('#event_stock_settings_tab').find('.warning').parents().index();
        if (errNum !== -1) {
            $('.js_tab_event_stock_settings').addClass('errTab');
        }

        // フォーマットタイプ変更
        $(document).on('change', '.js_change_format_type', function (_event) {
            var formatType = $(_event.target).val();
            var usetime = $('[name="time_plan"]');
            var usetimeSingle = usetime.filter('[value="' + $('.js_plan_type_single').val() + '"]');
            var usetimeMulti = usetime.filter('[value="' + $('.js_plan_type_multi').val() + '"]');
            var timePlanVal = null;

            //時間単位と日単位の項目切り替え
            if (formatType == $("input[name=js_type_time]").val()) {
                $(".js_disp_time").show();
                $(".js_disp_day").hide();
                $('.js_disp_time').removeClass('hidden');
            }

            if (formatType == $("input[name=js_type_day]").val()) {
                $(".js_disp_day").show();
                $(".js_disp_time").hide();
                $('.js_disp_day').removeClass('hidden');
            }

            if ($('.js_plan_type').attr('type') == 'radio') {
                var timePlanVal = $('[name="time_plan"]:checked').val();
            } else {
                var timePlanVal = $('[name="time_plan"]').val();
            }
            if (timePlanVal == $('.js_plan_type_multi').val()) {
                usetimeMulti.trigger('change');
            } else {
                usetimeSingle.trigger('change');
            }

        });

        // 料金タイプ変更
        $(document).on('change', '.js_plan_type', function (_event) {
            var planType = $(_event.target).val();

            if (planType == $("input[name=js_plan_type_multi]").val()) {
                $(".js_plan_type_multi").show();
                $(".js_plan_type_not_multi").hide();
            } else {
                $(".js_plan_type_multi").hide();
                $(".js_plan_type_not_multi").show();
            }
        });

        // プレビュー
        $(document).on('click', '.js_usage_time_preview', function (_event) {
            previewUsageTime();
        });


        // 在庫表示種別変更
        $(document).on('change', '.js_change_symbolic_flg', function (_event) {
            $('.js_toggle_vent_stock_marks').removeClass('hidden');
            $('.js_toggle_vent_stock_marks').hide();
            $('.js_toggle_vent_stock_marks_' + $('.js_change_symbolic_flg:checked').val()).show();
        });

        // 背景色種別変更
        $(document).on('change', '.js_change_background_color', function (_event) {
            $('.js_toggle_background_color').removeClass('hidden');
            $('.js_toggle_background_color').hide();
            $('.js_toggle_background_color_' + $('.js_change_background_color:checked').val()).show();
        });

        // 各締め切り日時間変更
        $(document).on('change', '.js_registration_deadline_type', function (_event) {
            $('.js_toggle_registration_deadline').removeClass('hidden');
            $('.js_toggle_registration_deadline').hide();
            $('.js_toggle_registration_deadline_type_' + $('.js_registration_deadline_type option:selected').val()).show();
            $('input[name="registration_deadline_number"]').trigger('blur');
        });

        $(document).on('change', '.js_cancellation_deadline_type', function (_event) {
            $('.js_toggle_cancellation_deadline').removeClass('hidden');
            $('.js_toggle_cancellation_deadline').hide();
            $('.js_toggle_cancellation_deadline_type_' + $('.js_cancellation_deadline_type option:selected').val()).show();
            $('input[name="cancellation_deadline_number"]').trigger('blur');
        });

        $(document).on('change', '.js_editing_deadline_type', function (_event) {
            $('.js_toggle_editing_deadline').removeClass('hidden');
            $('.js_toggle_editing_deadline').hide();
            $('.js_toggle_editing_deadline_type_' + $('.js_editing_deadline_type option:selected').val()).show();
            $('input[name="editing_deadline_number"]').trigger('blur');
        });

        $(document).on('change', '.js_registration_deadline_time', function (_event) {
            $('input[name="registration_deadline_number"]').trigger('blur');
        });

        $(document).on('change', '.js_editing_deadline_time', function (_event) {
            $('input[name="editing_deadline_number"]').trigger('blur');
        });

        $(document).on('change', '.js_cancellation_deadline_time', function (_event) {
            $('input[name="cancellation_deadline_number"]').trigger('blur');
        });

        $(document).on('blur', 'input[name="registration_deadline_number"]', function (_event) {

            var deadlineTypeElm = $('.js_registration_deadline_type');
            var deadlineDateTimeElm = $('.js_registration_deadline');
            var deadlineTimeElm = $('.js_registration_deadline_time');

            setDeadlineDateTime($(_event.target).val(), deadlineTypeElm, deadlineDateTimeElm, deadlineTimeElm);
        });

        $(document).on('blur', 'input[name="editing_deadline_number"]', function (_event) {
            var deadlineTypeElm = $('.js_editing_deadline_type');
            var deadlineDateTimeElm = $('.js_editing_deadline');
            var deadlineTimeElm = $('.js_editing_deadline_time');

            setDeadlineDateTime($(_event.target).val(), deadlineTypeElm, deadlineDateTimeElm, deadlineTimeElm);
        });

        $(document).on('blur', 'input[name="cancellation_deadline_number"]', function (_event) {
            var deadlineTypeElm = $('.js_cancellation_deadline_type');
            var deadlineDateTimeElm = $('.js_cancellation_deadline');
            var deadlineTimeElm = $('.js_cancellation_deadline_time');

            setDeadlineDateTime($(_event.target).val(), deadlineTypeElm, deadlineDateTimeElm, deadlineTimeElm);

        });

        $('.js_change_format_type').trigger('change');
    });
})(window, jQuery, window.app);
