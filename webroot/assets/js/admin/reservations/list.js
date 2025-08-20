(function (window, $, app) {
    // リロード
    var reload = function () {
        app.adminCommon.saveCheck($('.js_save_check_url').val()).done(function () {
            let url = $('.js_reload_url').val();

            // 未決済
            if ($('.js_search_payment_expired_query').length > 0) {
                url = app.common.mergeUrlQuery(url, {
                    search_payment_expired: 1
                } );
            }

            // スマートロック未連携
            if ( $('.js_search_smart_lock_unlinked_query').length > 0) {
                url = app.common.mergeUrlQuery(url, {
                    search_smart_lock_unlinked: 1
                });
            }

            app.common.changeLocation(url);
        });
    };

    // 検索項目リロード
    var reloadSearchForm = function () {
        let query = {};
        if ($('.js_search_payment_expired_query').length > 0) {
            query.search_payment_expired = $('.js_search_payment_expired_query').val();
        }

        // スマートロック連携
        if ($('.js_search_smart_lock_unlinked_query').length > 0) {
            query.search_smart_lock_unlinked = $('.js_search_smart_lock_unlinked_query').val();
        }

        app.common.ajax( 'adminSearchItemsEditReload', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/search-form', query),
            type: 'post',
            data: $('.js_reservation_search_form').serialize()
        }).done(function (result) {
            $('.js_reservation_search_form').html(result.html);
            app.common.useDatePicker();
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    $(function () {
        let url = app.common.mergeUrlQuery($('.js_save_check_url').val());
        if ($('.js_search_payment_expired_query').length <= 0
            && $('.js_search_smart_lock_unlinked_query').length <= 0) {
            app.adminCommon.setSaveCheckEvent();
        } else {
            if ($('.js_search_payment_expired_query').length > 0) {
                url = app.common.mergeUrlQuery(url, {
                    search_payment_expired: 1
                });
            }

            if ($('.js_search_smart_lock_unlinked_query').length > 0) {
                url = app.common.mergeUrlQuery(url, {
                    search_smart_lock_unlinked: 1
                });
            }

            $(document).off('click.paginatorPage', '.js_paginator_page');
            $(document).on('click.paginatorPage', '.js_paginator_page', function (event) {
                app.adminCommon.saveCheck(url, $(event.target).closest('.js_paginator_page'));

                event.preventDefault();
            });

            $(document).off('click.paginatorSort', '.js_paginator_sort');
            $(document).on('click.paginatorSort', '.js_paginator_sort', function (event) {
                app.adminCommon.saveCheck(url, $(event.target).closest('.js_paginator_sort'));

                event.preventDefault();
            });

            $(document).off('change.changeSearchLimit', '.js_change_search_limit');
            $(document).on('change.changeSearchLimit', '.js_change_search_limit', function (event) {
                app.adminCommon.saveCheck(url, $(event.target));

                event.preventDefault();
            });
        }

        // 検索項目変更
        $(document).on('adminSearchItemsEditFinish', function (event) {
            reloadSearchForm();
        });

        // 一覧項目変更
        $(document).on('adminListItemsEditFinish', function (event) {
            reload();
        });

        // ステータス変更
        $(document).on('reservationsUpdateStatusFinish', function (event) {
            reload();
        });
    });
})(window, jQuery, window.app);
