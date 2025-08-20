(function (window, $, app) {
    var popupDialog = {};
    var eventDialog = null;
    var waitingCancellationDialog = null;
    var alertDialog = null;

    // カレンダーを取得
    var loadCalendar = function (data) {
        var deffered = new $.Deferred();
        var query = {};

        if (typeof ($('.js_calendar_container').data('select-calendar')) !== 'undefined') {
            query.select_calendar = $('.js_calendar_container').data('select-calendar');
        }
        if (typeof ($('.js_calendar_container').data('edit-reservation-id')) !== 'undefined') {
            query.edit_reservation_id = $('.js_calendar_container').data('edit-reservation-id');
        }
        query = $.extend(query, app.common.getFramePageQuery());

        if (typeof (data) === 'undefined') {
            data = {};
        }
        if (typeof (data) === 'object' && $('.js_calendar_search_data').length > 0) {
            data = $.extend({}, $('.js_calendar_search_data').data('search'), data);
        }

        app.common.ajax('reservationsCalendar', {
            url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'reservations/calendar', query),
            type: 'post',
            data: data
        }).done(function (result) {
            settingCalendar(result.html);
            deffered.resolve();
        }).fail(function (error) {
            app.common.errorDialog(error);
            deffered.reject();
        });

        return deffered.promise();
    };

    // カレンダーをセット
    var settingCalendar = function (html) {
        html = applyStyle(html);
        $('.js_calendar_container').html(html);

        app.common.useDatePicker();
        html.find('.js_date_select').datetimepicker({
            onSelectDate: function (date, input) {
                loadCalendar({date: $(input).val(), page: 1});
            }
        });

        axia.removeEventListener('breakpoints');
        window[$('.js_calendar_trigger').val()]();
        re_common_trigger();
        axia.check_breakpoints();
        Stickyfill.add($('.sticky'));

        if ($('.js_calendar_next_page').data('scroll') === 'right') {
            $('.js_calendar_horizontal_scroll').on('scroll', function (event) {
                var width = $(event.target).children('table').outerWidth();
                var offset = -10;

                if ($('.js_calendar_next_page').length > 0
                    && $(event.target).scrollLeft() >= width - $(event.target).innerWidth() + offset
                ) {
                    loadNextPage($.parseJSON($('.js_calendar_next_page').val())).done(function () {
                        re_load_scroll_trigger();
                    });
                }
            });
        }

        scrollTimetable();
        $(window).trigger('resize');
        $(window).trigger('scroll');
    };

    // スタイルを適用
    var applyStyle = function (html, listTypeNextPageFlg = false) {
        html = $(html);

        var applyStyle = html.find('.js_apply_style');
        if (listTypeNextPageFlg) {
            applyStyle = html.filter('.js_apply_style');
        }

        applyStyle.each(function (index, element) {
            $(element).css($(element).data('style'));
            $(element).data('style', '');
            $(element).attr('data-style', '');
            $(element).removeClass('js_apply_style');
        });

        return html;
    };

    // スクロール
    var scrollTimetable = function () {
        var target = $('.js_search_header').offset().top;
        var searchHeaderOffset = $('.js_search_header').outerHeight();

        if ($('.js_calendar_time_scroll').length > 0) {
            target = $('.js_calendar_time_scroll').offset().top - $('.js_timetable_header').outerHeight();
            if ($('.subject_table').length > 0) {
                target -= searchHeaderOffset;
            } else {
                target -= searchHeaderOffset + $('.js_empty_cell').outerHeight();
            }
        } else {
            target = $('.js_search_header').offset().top;
        }

        $('html,body').animate({
            scrollTop: target
        }, 500, 'swing');
    };

    // 次のページ
    var loadNextPage = function (data) {
        var deffered = new $.Deferred();
        var query = {};

        if (!$('.js_calendar_next_page').hasClass('js_calendar_next_page_loading')) {
            $('.js_calendar_next_page').addClass('js_calendar_next_page_loading');

            if (typeof ($('.js_calendar_container').data('select-calendar')) !== 'undefined') {
                query.select_calendar = $('.js_calendar_container').data('select-calendar');
            }
            if (typeof ($('.js_calendar_container').data('edit-reservation-id')) !== 'undefined') {
                query.edit_reservation_id = $('.js_calendar_container').data('edit-reservation-id');
            }
            query = $.extend(query, app.common.getFramePageQuery());

            if (typeof (data) === 'object' && $('.js_calendar_search_data').length > 0) {
                data = $.extend({}, $('.js_calendar_search_data').data('search'), data);
            }

            app.common.ajax('reservationsCalendarPage', {
                url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'reservations/calendar-page', query),
                type: 'post',
                data: data
            }).done(function (result) {
                var html = $(result.html);

                var listTypeNextPageFlg = false;
                if (typeof ($('.reserve_list_body').data('list-type-flg')) !== 'undefined') {
                    listTypeNextPageFlg = true;
                }
                html.filter('.js_calendar_page_html').each(function (index, element) {
                    $($(element).data('selector')).append(applyStyle($(element).val(), listTypeNextPageFlg));
                });
                if (html.filter('.js_calendar_next_page').length > 0) {
                    $('.js_calendar_next_page').replaceWith(html.filter('.js_calendar_next_page'));
                } else {
                    $('.js_calendar_next_page').remove();
                }

                var arr = [];
                $('li.colorTip-block-li span.colorTip-block').each(function (index, val) {
                    arr[$(val).data('colorchip-id')] = {
                        id: $(val).data('colorchip-id'),
                        sort_no: $(val).data('sort'),
                        name: $.trim($(val).parent().text()),
                        color_code: $(val).css('background-color')
                    };
                });

                $.extend(true, arr, result.colorChips);
                arr = arr.sort(function (a, b) {
                    return (a.sort_no > b.sort_no ? 1 : -1);
                });

                $('ul.js_add_color > li.colorTip-block-li').remove();
                $.each(arr, function (index, colorChips) {
                    if (typeof colorChips === "object") {
                        var addLi = $('ul.colorTip-block-base').clone();
                        $(addLi).children('li').children('span.colorTip-block').data('sort', colorChips.sort_no);
                        $(addLi).children('li').children('span.colorTip-block').data('colorchip-id', colorChips.id);
                        $(addLi).children('li').children('span.colorTip-block').css('background-color', colorChips.color_code);
                        $(addLi).children('li').append(colorChips.name);
                        $('ul.js_add_color').append($(addLi).children('li'));
                    }
                });

                deffered.resolve(result);
            }).fail(function (error) {
                deffered.reject(error);
                app.common.errorDialog(error);
            });
        }

        return deffered.promise();
    };

    // 予約枠詳細を表示
    var showEventPopup = function (url, title) {
        var html = $($('.js_event_detail_iframe').val());

        if (eventDialog !== null) {
            $(eventDialog).dialog('close');
        }

        html.prop('src', url);
        eventDialog = app.common.dialog(html, {
            width: $(window).width() * 0.9,
            height: $(window).height() * 0.9,
            title: title
        });

        eventDialog.on('dialogclose', function (event, ui) {
            eventDialog = null;
        });
    };

    // ポップアップを表示
    var showCalendarPopup = function (type, data) {
        var query = {
            popup_type: type
        };

        if (typeof ($('.js_calendar_container').data('select-calendar')) !== 'undefined') {
            query.select_calendar = $('.js_calendar_container').data('select-calendar');
        }
        if (typeof ($('.js_calendar_container').data('edit-reservation-id')) !== 'undefined') {
            query.edit_reservation_id = $('.js_calendar_container').data('edit-reservation-id');
        }
        query = $.extend(query, app.common.getFramePageQuery());

        app.common.ajax('reservationsCalendarPopup', {
            url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'reservations/calendar-popup', query),
            type: 'post',
            data: data
        }).done(function (result) {
            var html = applyStyle(result.html);

            if (type in popupDialog) {
                $(popupDialog[type]).dialog('close');
            }
            popupDialog[type] = app.common.dialog(html, {
                classes: {
                    'ui-dialog': 'list-item'
                },
                title: $(html).data('title'),
                width: app.common.getScreenWidth($(html).data('popup-width')),
                height: 600
            });
            popupDialog[type].on('dialogclose', function (event, ui) {
                delete popupDialog[type];
            });

            setCalendarPopupEvent(popupDialog[type]);

            app.common.useDatePicker();
            html.find('.js_date_select').datetimepicker({
                onSelectDate: function (date, input) {
                    var searchData = html.data('search-data');

                    searchData.date = $(input).val();
                    showCalendarPopup(html.data('popup-type'), searchData);
                }
            });

            if (popupDialog[type].hasClass('js_event_list_popup')) {
                re_month_list_trigger();
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // ポップアップ表示イベント
    var setCalendarPopupEvent = function (element) {
        $(element).find('.js_change_popup_date').on('click.changePopupDate', function (event) {
            var searchData = $(element).data('search-data');

            searchData.date = $(event.target).data('date');
            showCalendarPopup($(element).data('popup-type'), searchData);

            event.preventDefault();
        });
    };

    // 予約画面へ遷移
    var makeReservation = function (url) {
        if (typeof($('.js_calendar_container').data('calendar-frame-new-window')) !== 'undefined') {
            app.common.openWindow(url);
        } else if (window.parent !== window.self) {
            app.common.changeLocation(url, '', window.parent);
        } else {
            app.common.changeLocation(url, '', window.self);
        }
    };

    // キャンセル待ち通知
    var waitingCancellation = function (query, data) {
        var httpMethod = 'post';

        if (typeof (data) === 'undefined') {
            data = {};
            httpMethod = 'get';
        }
        app.common.ajax('reservationsCalendarPopup', {
            url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'waiting-cancellations/add', query),
            type: httpMethod,
            data: data
        }).done(function (result) {
            var html = $(result.html);

            if (waitingCancellationDialog !== null) {
                $(waitingCancellationDialog).dialog('close');
            }
            waitingCancellationDialog = app.common.dialog(html, {
                classes: {
                    'ui-dialog': 'list-item'
                },
                title: html.data('title'),
                width: app.common.getScreenWidth(800),
            });
            waitingCancellationDialog.on('dialogclose', function (event, ui) {
                waitingCancellationDialog = null;
            });

            if (!result.finish) {
                setWaitingCancellationEvent(waitingCancellationDialog);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // キャンセル待ち通知イベント
    var setWaitingCancellationEvent = function (dialog) {
        $(dialog).find('.js_waiting_cancellation_form').on('submit.waitingCancellation', function (event) {
            var query = {};

            $(event.target).find('.js_waiting_cancellation_parameter').each(function (index, element) {
                query[$(element).data('name')] = $(element).val();
            });
            waitingCancellation(query, $(event.target).serialize());

            event.preventDefault();
        });
    };

    // 予約不可のアラート表示
    var showNotAvailableAlert = function () {
        if (alertDialog !== null) {
            $(alertDialog).dialog('close');
        }
        alertDialog = app.common.errorDialog($('.js_not_available_alert').val());
        alertDialog.on('dialogclose', function (event, ui) {
            alertDialog = null;
        });
    };

    // 枠選択
    var selectCalendar = function (data) {
        var targetWindow = window.self;

        if (window.parent !== window.self) {
            targetWindow = window.parent;
        }

        targetWindow.$(targetWindow.document).trigger('selectCalendar', data);
    };

    $(function () {
        // 検索
        $(document).on('submit.searchCalendar', '.js_calendar_form', function (event) {
            loadCalendar($(event.target).serialize());

            event.preventDefault();
        });

        // 日付変更
        $(document).on('click.changeDate', '.js_change_date', function (event) {
            loadCalendar({date: $(event.target).data('date'), page: 1});

            event.preventDefault();
        });

        // 日付選択
        $(document).on('change.changeCalendarDate', '.js_calendar_date', function (event) {
            loadCalendar({date: $(event.target).val(), page: 1});
        });

        // 枠ID絞り込み解除
        $(document).on('click.resetEventId', '.js_event_id', function (event) {
            loadCalendar({id: null, page: 1});
        });

        // 表示タイプ変更
        $(document).on('click.calendarType', '.js_calendar_type', function (event) {
            loadCalendar({calendar_type: $(event.target).closest('.js_calendar_type').data('calendar-type'), page: 1});

            event.preventDefault();
        });

        // ページャ
        $(document).off('click.paginatorPage', '.js_paginator_page');
        $(document).on('click.paginatorPage', '.js_paginator_page', function (event) {
            var query = app.common.parseUrlQuery($(event.target).closest('.js_paginator_page').data('url'));

            loadCalendar({page: query.page});

            event.preventDefault();
        });

        // 予約枠リンク
        $(document).on('click.showEventPopup', '.js_show_event_popup', function (event) {
            showEventPopup($(event.target).closest('.js_show_event_popup').data('url'), $(event.target).closest('.js_show_event_popup').data('title'));

            event.preventDefault();
        });

        // 予約リンク
        $(document).on('click.makeReservation', '.js_make_reservation', function (event) {
            var target = $(event.target).closest('.js_make_reservation');

            if (target.hasClass('js_cannot_reserve_unit')) {
                if ($('.js_has_reservation_authority').length > 0) {
                    showNotAvailableAlert();
                }
            } else if (target.hasClass('js_waiting_cancellation')) {
                if ($('.js_waiting_cancellation_url').length > 0) {
                    app.common.openWindow($('.js_waiting_cancellation_url').val(), {
                        event_id: target.data('event-id'),
                        usage_timestamp: target.data('usage-timestamp')
                    });
                } else {
                    waitingCancellation({
                        event_id: target.data('event-id'),
                        usage_timestamp: target.data('usage-timestamp')
                    });
                }
            } else if (target.hasClass('js_can_reserve')) {
                if ($('.js_has_reservation_authority').length > 0) {
                    makeReservation(target.data('url'));
                }
            }

            event.preventDefault();
        });

        // ポップアップ表示
        $(document).on('click.showCalendarPopup', '.js_show_calendar_popup', function (event) {
            var target = $(event.target).closest('.js_show_calendar_popup');

            showCalendarPopup(target.data('popup-type'), target.data('search-data'));

            event.preventDefault();
        });

        // 枠選択
        $(document).on('click.selectCalendar', '.js_select_calendar', function (event) {
            var target = $(event.target).closest('.js_select_calendar');

            selectCalendar(target.data('calendar-data'));

            event.preventDefault();
        });

        // ページ
        $(window).on('scroll', function (event) {
            var offset = -10;

            if ($('.js_calendar_next_page').data('scroll') === 'bottom' && app.userCommon.isScrollBottom(offset)) {
                loadNextPage($.parseJSON($('.js_calendar_next_page').val()));
            }
        });
    });

    $(window).on('load', function () {
        window.setTimeout(function () {
            loadCalendar($('.js_calendar_container').data('search'));
        }, 0);
    });

})(window, jQuery, window.app);
