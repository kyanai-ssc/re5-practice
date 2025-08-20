(function (window, $, app) {
    var popupDialog = {};
    var detailDialog = null;

    // カレンダーを取得
    var loadCalendar = function (data) {
        var query = {};

        if (typeof ($('.js_calendar_container').data('select-calendar')) !== 'undefined') {
            query.select_calendar = $('.js_calendar_container').data('select-calendar');
        }
        if (typeof ($('.js_calendar_container').data('edit-reservation-id')) !== 'undefined') {
            query.edit_reservation_id = $('.js_calendar_container').data('edit-reservation-id');
        }

        if (typeof (data) === 'undefined') {
            data = {};
        }
        if (typeof (data) === 'object' && $('.js_calendar_search_data').length > 0) {
            data = $.extend({}, $('.js_calendar_search_data').data('search'), data);
        }

        app.common.ajax('reservationsCalendar', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/calendar', query),
            type: 'post',
            data: data
        }).done(function (result) {
            settingCalendar(result.html);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // カレンダーをセット
    var settingCalendar = function (html) {
        html = applyStyle(html);
        $('.js_calendar_container').html(html);

        app.common.useDatePicker();
        if (!$('.js_calendar_container').hasClass('noLoad')) {
            html.find('.js_date_select').datetimepicker({
                format: 'Y/m/d',
                yearStart: 1900,
                scrollInput: false,
                timepicker: false,
                onSelectDate: function (date, input) {
                    loadCalendar({date: $(input).val(), page: 1});
                }
            }).attr('autocomplete', 'off');
            html.find('.js_date_select').off('touchstart');
        }

        Stickyfill.add($('.sticky'));
        window[$('.js_calendar_trigger').val()]();
        $(window).trigger('scroll');

        if ($('.js_calendar_next_page').data('scroll') === 'right') {
            $('.js_calendar_horizontal_scroll').on('scroll', function (event) {
                var width = $(event.target).children('table').outerWidth();
                var offset = -10;

                if ($('.js_calendar_next_page').length > 0
                    && $(event.target).scrollLeft() >= width - $(event.target).innerWidth() + offset
                ) {
                    loadNextPage($.parseJSON($('.js_calendar_next_page').val())).done(function () {
                        cal_time_reload_trigger();
                    });
                }
            });
        }

        if ($('.js_calendar_container').hasClass('js_calendar_loaded')) {
            //フォームの中に.warningがあればスクロールしない
            errNum = $('#event-search-form').find('.warning').parents().index();
            if (errNum === -1) {
                scrollTimetable();
            }
        } else {
            app.adminCommon.loadShowBtn();
            $('.js_calendar_container').addClass('js_calendar_loaded');
            scrollTimetable();
        }
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
        var globalNaviOffset = $('.js_global_navi').outerHeight();
        var searchHeaderOffset = $('.js_search_header').outerHeight();

        if ($('.js_calendar_time_scroll').length > 0) {
            target = $('.js_calendar_time_scroll').offset().top - $('.js_timetable_header').outerHeight();
            target -= searchHeaderOffset;
        } else {
            target = $('.js_search_header').offset().top;
        }
        target -= globalNaviOffset;

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
            if (typeof (data) === 'object' && $('.js_calendar_search_data').length > 0) {
                data = $.extend({}, $('.js_calendar_search_data').data('search'), data);
            }

            app.common.ajax('reservationsCalendarPage', {
                url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/calendar-page', query),
                type: 'post',
                data: data
            }).done(function (result) {
                var html = $(result.html);

                var listTypeNextPageFlg = false;
                if (typeof ($('.table_reserve_list').data('list-type-flg')) !== 'undefined') {
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

        app.common.ajax('reservationsCalendarPopup', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/calendar-popup', query),
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
                width: app.common.getScreenWidth(800),
                height: 600
            });
            popupDialog[type].on('dialogclose', function (event, ui) {
                delete popupDialog[type];
            });

            setCalendarPopupEvent(popupDialog[type]);

            app.common.useDatePicker();
            html.find('.js_date_select').datetimepicker({
                format: 'Y/m/d',
                yearStart: 1900,
                scrollInput: false,
                timepicker: false,
                onSelectDate: function (date, input) {
                    var searchData = html.data('search-data');

                    searchData.date = $(input).val();
                    showCalendarPopup(html.data('popup-type'), searchData);
                }
            }).attr('autocomplete', 'off');
            html.find('.js_date_select').off('touchstart');
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

    // 詳細表示
    var showCalendarDetail = function (data) {
        app.common.ajax('reservationsCalendarDetail', {
            url: app.adminCommon.ajaxUrl() + 'reservations/calendar-detail',
            type: 'post',
            data: data
        }).done(function (result) {
            if (detailDialog !== null) {
                $(detailDialog).dialog('close');
            }
            detailDialog = app.common.dialog(result.html, {
                classes: {
                    'ui-dialog': 'REdetail'
                },
                width: app.common.getScreenWidth(800),
                height: 600
            });
            detailDialog.on('dialogclose', function (event, ui) {
                detailDialog = null;
            });

            setCalendarDetailEvent(detailDialog);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 詳細表示イベント
    var setCalendarDetailEvent = function (element) {
        $(element).find('.js_paginator_page').on('click.paginatorPage', function (event) {
            var query = app.common.parseUrlQuery($(event.target).closest('.js_paginator_page').data('url'));
            var searchData = element.data('search-data');

            searchData.page = query.page;
            showCalendarDetail(searchData);

            event.preventDefault();
            event.stopPropagation();
        });

        $(element).find('.js_paginator_sort').on('click.paginatorSort', function (event) {
            var query = app.common.parseUrlQuery($(event.target).closest('.js_paginator_sort').data('url'));
            var searchData = element.data('search-data');

            searchData.sort = query.sort;
            searchData.direction = query.direction;
            searchData.page = query.page;
            showCalendarDetail(searchData);

            event.preventDefault();
            event.stopPropagation();
        });

        // ステータス変更
        $(document).on('reservationsUpdateStatusFinish', function (event) {
            showCalendarDetail(element.data('search-data'));
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
        if (!$('.js_calendar_container').hasClass('noLoad')) {
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

            // 全時間表示
            $(document).on('change.changeDisplayAllTime', '.js_display_all_time', function (event) {
                var searchData = {
                    display_all_time: null
                };

                if ($(event.target).prop('checked')) {
                    searchData.display_all_time = $(event.target).val();
                    Cookies.set('display_all_time', $(event.target).val(), {
                        expires: 7,
                        path: app.adminCommon.baseUrl(),
                        secure: true
                    });
                } else {
                    Cookies.set('display_all_time', $(event.target).val(), {
                        expires: -1,
                        path: app.adminCommon.baseUrl(),
                        secure: true
                    });
                }

                loadCalendar(searchData);
            });

            // 台帳表示項目変更
            $(document).on('change.changeDisplayItem', '.js_display_item', function (event) {
                localStorage.setItem('reservationCalendarItem', $(event.target).val());
                loadCalendar({display_item: $(event.target).val()});
            });

            // 表示タイプ変更
            $(document).on('change.changeCalendarType', '.js_calendar_type', function (event) {
                loadCalendar({calendar_type: $(event.target).val(), page: 1});
            });

            // 台帳更新
            $(document).on('click.reloadCalendar', '.js_reload_calendar', function (event) {
                loadCalendar();

                event.preventDefault();
            });

            // ページャ
            $(document).off('click.paginatorPage', '.js_paginator_page');
            $(document).on('click.paginatorPage', '.js_paginator_page', function (event) {
                var query = app.common.parseUrlQuery($(event.target).closest('.js_paginator_page').data('url'));

                loadCalendar({page: query.page});

                event.preventDefault();
            });

            // ポップアップ表示
            $(document).on('click.showCalendarPopup', '.js_show_calendar_popup', function (event) {
                var target = $(event.target).closest('.js_show_calendar_popup');

                showCalendarPopup(target.data('popup-type'), target.data('search-data'));

                event.preventDefault();
            });

            // 詳細表示
            $(document).on('click.showCalendarDetail', '.js_show_calendar_detail', function (event) {
                var target = $(event.target).closest('.js_show_calendar_detail');

                showCalendarDetail(target.data('search-data'));

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

                if ($('.js_calendar_next_page').data('scroll') === 'bottom' && app.adminCommon.isScrollBottom(offset)) {
                    loadNextPage($.parseJSON($('.js_calendar_next_page').val()));
                }
            });
        }
    });

    $(window).on('load', function () {
        window.setTimeout(function () {
            var searchData = null;

            if (!$('.js_calendar_container').hasClass('noLoad')) {
                searchData = $.extend(true, {}, $('.js_calendar_container').data('search'));
                if (!('display_item' in searchData) || searchData.display_item === null) {
                    searchData.display_item = app.common.getLocalStorage().getItem('reservationCalendarItem');
                }

                loadCalendar(searchData);
            } else {
                settingCalendar($('.js_calendar_container').html());
            }
        }, 0);
    });

})(window, jQuery, window.app);
