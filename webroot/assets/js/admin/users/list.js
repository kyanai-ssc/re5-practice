(function (window, $, app) {
    var dialog = null;

    // リロード
    var reload = function () {
        app.adminCommon.saveCheck($('.js_save_check_url').val()).done(function () {
            app.common.changeLocation($('.js_reload_url').val());
        });
    };

    // 検索項目リロード
    var reloadSearchForm = function () {
        var query = {};
        if ($('.js_select_user_query').length > 0) {
            query.select_user = $('.js_select_user_query').val();
        }

        app.common.ajax('adminSearchItemsEditReload', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'users/search-form', query),
            type: 'post',
            data: $('.js_user_search_form').serialize()
        }).done(function (result) {
            $('.js_user_search_form').html(result.html);
            app.adminCommon.multipleSelector();
            app.common.useDatePicker();
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 権限変更
    var updateAuthority = function (userId) {
        app.common.ajax('usersUpdateAuthority', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'users/update-authority', {
                id: userId
            }),
            type: 'get',
            data: {}
        }).done(function (result) {
            showUpdateAuthorityDialog(result.html);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 権限変更実行
    var updateAuthorityExec = function (userId, data) {
        app.common.ajax('usersUpdateAuthorityExec', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'users/update-authority', {
                id: userId
            }),
            type: 'post',
            data: data
        }).done(function (result) {
            if (result.finish) {
                $(dialog).dialog('close');
                reload();
            } else {
                showUpdateAuthorityDialog(result.html);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 権限変更画面表示
    var showUpdateAuthorityDialog = function (html) {
        if (dialog !== null) {
            $(dialog).dialog('close');
        }

        dialog = app.common.dialog(html, {
            classes: {
                'ui-dialog': 'tool-popup'
            },
            width: app.common.getScreenWidth(300)
        });
        dialog.on('dialogclose', function (event, ui) {
            dialog = null;
        });
    };

    // 会員選択
    var selectUser = function (data) {
        var targetWindow = window.self;

        if (window.parent !== window.self) {
            targetWindow = window.parent;
        }

        targetWindow.$(targetWindow.document).trigger('selectUser', data);
    };

    $(function () {
        if ($('.js_select_user_query').length <= 0) {
            app.adminCommon.setSaveCheckEvent();
        } else {
            $(document).off('click.paginatorPage', '.js_paginator_page');
            $(document).on('click.paginatorPage', '.js_paginator_page', function (event) {
                var target = $(event.target).closest('.js_paginator_page');

                app.common.changeLocation(app.common.mergeUrlQuery(target.data('url'), {
                    select_user: $('.js_calendar_container').data('select-calendar')
                }));

                event.preventDefault();
            });

            $(document).off('click.paginatorSort', '.js_paginator_sort');
            $(document).on('click.paginatorSort', '.js_paginator_sort', function (event) {
                var target = $(event.target).closest('.js_paginator_sort');

                app.common.changeLocation(app.common.mergeUrlQuery(target.data('url'), {
                    select_user: $('.js_calendar_container').data('select-calendar')
                }));

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

        // 権限変更
        $(document).on('click.updateAuthority', '.js_update_authority', function (event) {
            updateAuthority($(event.target).data('user-id'));

            event.preventDefault();
        });

        // 権限選択肢変更
        $(document).on('change.updateAuthority', '.js_user_authority_id', function (event) {
            var form = $(event.target).closest('form');
            var confirmMessage = form.data('confirm-message');
            var confirmTitle = form.data('confirm-title');

            app.common.confirmDialog(confirmMessage, confirmTitle).done(function (dialog) {
                form.trigger('submit');
            }).fail(function (dialog) {
                var authorities = form.find('.js_user_authority_id');

                authorities.prop('checked', false);
                authorities.filter('[value="' + form.data('user-authority-id') + '"]').prop('checked', true);
            });

            event.preventDefault();
        });

        // 権限変更実行
        $(document).on('submit.updateAuthorityExec', '.js_update_authority_form', function (event) {
            updateAuthorityExec($(event.target).data('user-id'), $(event.target).serialize());

            event.preventDefault();
        });

        // 会員選択
        $(document).on('click.selectUser', '.js_select_user', function (event) {
            selectUser($(event.target).data('user-data'));

            event.preventDefault();
        });

        app.adminCommon.multipleSelector();
    });
})(window, jQuery, window.app);
