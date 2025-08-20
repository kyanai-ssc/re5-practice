(function (window, $, app) {
    var dialog = null;

    // グループ削除
    var deleteFormGroup = function (formType, selector, context) {
        var sessionKey = [];

        $(context).find(selector).find('.js_form_item_key').each(function (index, element) {
            sessionKey.push($(element).val());
        });

        app.common.ajax('formItemsRemove', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'form-items/delete-many', {
                form_type: formType
            }),
            type: 'post',
            data: {
                session_key: sessionKey
            }
        }).done(function (result) {
            app.common.removeInput(selector, context);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 項目登録
    var addFormItem = function (formType, formGroupIndex) {
        app.common.ajax('formItemsAdd', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'form-items/add', {
                form_type: formType
            }),
            type: 'get',
            data: {}
        }).done(function (result) {
            showInputDialog(formGroupIndex, null, result.html);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 項目登録実行
    var addExecFormItem = function (formType, formGroupIndex, formItemData) {
        app.common.ajax('formItemsAddExec', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'form-items/add', {
                form_type: formType
            }),
            type: 'post',
            data: formItemData
        }).done(function (result) {
            if (result.finish) {
                replaceItemList(formGroupIndex, null, null, result.html);
            } else {
                showInputDialog(formGroupIndex, null, result.html);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 項目編集
    var editFormItem = function (formType, formGroupIndex, formItemIndex, sessionKey) {
        app.common.ajax('formItemsEdit', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'form-items/edit', {
                form_type: formType,
                session_key: sessionKey
            }),
            type: 'get',
            data: {}
        }).done(function (result) {
            showInputDialog(formGroupIndex, formItemIndex, result.html, sessionKey);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 項目編集実行
    var editExecFormItem = function (formType, formGroupIndex, formItemIndex, sessionKey, formItemData) {
        app.common.ajax('formItemsEditExec', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'form-items/edit', {
                form_type: formType,
                session_key: sessionKey
            }),
            type: 'post',
            data: formItemData
        }).done(function (result) {
            if (result.finish) {
                replaceItemList(formGroupIndex, formItemIndex, sessionKey, result.html);
            } else {
                showInputDialog(formGroupIndex, formItemIndex, result.html, sessionKey);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 項目削除
    var deleteFormItem = function (formType, sessionKey) {
        app.common.ajax('formItemsDelete', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'form-items/delete', {
                form_type: formType,
                session_key: sessionKey
            }),
            type: 'post',
            data: {}
        }).done(function (result) {
            app.common.removeInput($('.js_form_items_element_' + sessionKey), $('.js_form_groups_container'));
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 項目入力画面表示
    var showInputDialog = function (formGroupIndex, formItemIndex, html, sessionKey) {
        if (sessionKey === undefined) {
            sessionKey = 0;
        }

        if (dialog !== null) {
            $(dialog).dialog('close');
        }

        dialog = app.common.dialog(html, {
            width: app.common.getScreenWidth(900),
            height: 800,
            classes: {
                'ui-dialog': 'form-item'
            },
            title: $('form').data('item-setting-title'),
        });

        dialog.on('dialogbeforeclose', function (event, ui) {
            app.common.removeWysiWyg(dialog);
        });
        dialog.on('dialogclose', function (event, ui) {
            dialog = null;
        });

        //タブ切り替えイベント
        $('.js-tab-btn').on('click', function () {
            //セレクタ
            var thisElm = $(this);
            var thisTabWrap = thisElm.parents('.js-tab-wrap');
            var thisTabBtn = thisTabWrap.find('.js-tab-btn');
            var thisTabContents = thisTabWrap.find('.contents-box');

            //is-open
            var openClass = 'is-open';
            thisTabBtn.removeClass(openClass);
            thisElm.addClass(openClass);

            //クリックされたタブの順番取得
            var thisElmIndex = thisTabBtn.index(this);

            //contents-box切り替え
            thisTabContents.removeClass(openClass);
            thisTabContents.eq(thisElmIndex).addClass(openClass);
        });

        if (sessionKey > 0) {
            $('.js-menu-wrap ul li').not('.is-open').addClass('hidden');
        }

        //説明文のウィジウィグ化
        app.common.toWysiWyg();
        app.common.useDatePicker();

        changeInputType(dialog, dialog.find('[name="input_type"]').val());

        //タブのコンテンツに.warningがあればタブの背景色が変わる
        var errNum = $('.baseEdit').find('.warning').parents().index();
        if (errNum !== -1) {
            $('.js_form_item_tab_base').addClass('errTab');
        }

        errNum = $('.detailEdit').find('.warning').parents().index();
        if (errNum !== -1) {
            $('.js_form_item_tab_detail').addClass('errTab');
        }


        if (formItemIndex === null) {
            $('.js_form_item_add_submit').data('form-group-index', formGroupIndex);
        } else {
            $('.js_form_item_edit_submit').data('form-group-index', formGroupIndex);
            $('.js_form_item_edit_submit').data('form-item-index', formItemIndex);
        }

        app.common.useDatePicker();
    };

    // 項目一覧置換
    var replaceItemList = function (formGroupIndex, formItemIndex, sessionKey, html) {
        var container = $('.js_form_items_container_' + formGroupIndex);

        if (isNaN(parseInt(formItemIndex, 10))) {
            formItemIndex = app.common.maxValue('.js_form_items_index', container);
            if (formItemIndex === null) {
                formItemIndex = -1;
            }
            formItemIndex += 1;
        }

        html = html.replace(new RegExp('%FORM_GROUP_INDEX%', 'g'), formGroupIndex);
        html = html.replace(new RegExp('%FORM_ITEM_INDEX%', 'g'), formItemIndex);
        if (sessionKey === null) {
            container.append(html);
        } else {
            $('.js_form_items_element_' + sessionKey).replaceWith(html);
        }

        if (dialog !== null) {
            $(dialog).dialog('close');
        }

        setSortable();
    };

    // 入力タイプ変更
    var changeInputType = function (container, inputType) {
        var inputTypeData = $('.js_form_item_input_type_data_' + inputType);

        $(container).find('[name="input_type"]').val(inputType);

        if ($(container).find('.js_form_item_detail_' + inputType).length > 0) {
            app.common.disabledInput('.js_form_item_detail');
            app.common.unDisabledInput('.js_form_item_detail_' + inputType);

            $(container).find('.js_form_item_tab_detail').show();
            if ($.isEmptyObject($(container).find('.js_form_item_detail_' + inputType))) {
                $(container).find('.js_form_item_tab_detail').hide();
            }
        } else {
            app.common.disabledInput('.js_form_item_detail');
            $(container).find('.js_form_item_tab_detail').hide();
        }


        app.common.disabledInput('.js_form_item_required');
        if (inputTypeData.data('can-select-required')) {
            app.common.unDisabledInput('.js_form_item_select_required_on');
        } else {
            if (inputTypeData.data('required')) {
                app.common.unDisabledInput('.js_form_item_required_on');
            } else {
                app.common.unDisabledInput('.js_form_item_required_off');
            }
        }

        app.common.disabledInput('.js_form_item_can_reservation_display');
        if (inputTypeData.data('can-reservation-display')) {
            app.common.unDisabledInput('.js_form_item_can_reservation_display_on');
        } else {
            app.common.unDisabledInput('.js_form_item_can_reservation_display_off');
        }


        $('.js_form_item_detail').addClass('hidden');
        if ($('.js_form_item_detail_' + inputType).length > 0) {
            $('.js_form_item_detail_' + inputType).removeClass('hidden');
        }

        $('.js_form_item_required').addClass('hidden');
        if (inputTypeData.data('can-select-required')) {
            $('.js_form_item_select_required_on').removeClass('hidden');
        } else {
            $('.js_form_item_select_required_off').removeClass('hidden');
        }
        $('.js_form_item_can_reservation_display').addClass('hidden');
        if (inputTypeData.data('can-reservation-display')) {
            $('.js_form_item_can_reservation_display_on').removeClass('hidden');
        } else {
            $('.js_form_item_can_reservation_display_off').removeClass('hidden');
        }
    };

    // 終了日タイプ変更
    var changeDateUpperLimitType = function (container, dateUpperLimitType) {
        app.common.disabledInput('.js_form_item_date_upper_limit');
        app.common.unDisabledInput('.js_form_item_date_upper_limit_' + dateUpperLimitType);

        $('.js_form_item_date_upper_limit').addClass('hidden');
        $('.js_form_item_date_upper_limit_' + dateUpperLimitType).removeClass('hidden');
    };

    var refreshSortableTr = function () {
        //空要素の非表示化
        $('tbody.js_sortable_items').each(function (index, element) {
            if ($(element).find('tr').length == 1) {
                $(element).find('tr.ui-state-disabled').removeClass('hidden');
            } else {
                $(element).find('tr.ui-state-disabled').addClass('hidden')
            }
        });
    }

    // sortableの設定を追加
    var setSortable = function () {
        app.adminCommon.sortableDom({
            connectWith: '.js_sortable_items',
            update: function (event, ui) {
                name = ui.item.find('input.js_form_item_key').attr('name');
                groupNumber = ui.item.closest('tbody').attr('class').match(/js\_form\_items\_container\_[0-9]+/);
                groupNumber = groupNumber['0'].replace(/js\_form\_items\_container\_/, '');
                replaceName = name.replace(/^form_groups\[\d\]/, 'form_groups\[' + groupNumber + '\]');
                ui.item.find('input.js_form_item_key').attr('name', replaceName);
                refreshSortableTr();
            },
        });

        $('.js_sortable_group').sortable({
            handle: '.handle',
            axis: 'y',
        });

    };

    $(function () {
        // グループ追加
        $(document).on('click.addGrope', '.js_add_group', function () {
            setSortable();
        });

        // グループ削除
        $(document).on('click.deleteFormGroup', '.js_delete_form_group', function (event) {
            var target = $(event.target).closest('.js_delete_form_group');
            var title = target.data('confirm-title');
            var message = target.data('confirm-message');
            var html = target.data('confirm-html');

            app.common.confirmDialog(message, title, html).done(function (dialog) {
                deleteFormGroup(target.data('form-type'), target.data('selector'), target.data('context'));
            });

            event.preventDefault();
        });

        // 項目登録
        $(document).on('click.addFormItem', '.js_add_form_item', function (event) {
            addFormItem(
                $(event.target).data('form-type'),
                $(event.target).data('form-group-index')
            );

            event.preventDefault();
        });

        // 項目登録実行
        $(document).on('submit.formItemAddSubmit', '.js_form_item_add_submit', function (event) {
            addExecFormItem(
                $(event.target).data('form-type'),
                $(event.target).data('form-group-index'),
                $(event.target).serialize()
            );

            event.preventDefault();
        });

        // 項目編集
        $(document).on('click.editFormItem', '.js_edit_form_item', function (event) {
            editFormItem(
                $(event.target).closest('.js_edit_form_item').data('form-type'),
                $(event.target).closest('.js_edit_form_item').data('form-group-index'),
                $(event.target).closest('.js_edit_form_item').data('form-item-index'),
                $(event.target).closest('.js_edit_form_item').data('session-key')
            );

            event.preventDefault();
        });

        // 項目編集実行
        $(document).on('submit.formItemEditSubmit', '.js_form_item_edit_submit', function (event) {
            editExecFormItem(
                $(event.target).data('form-type'),
                $(event.target).data('form-group-index'),
                $(event.target).data('form-item-index'),
                $(event.target).data('session-key'),
                $(event.target).serialize()
            );

            event.preventDefault();
        });

        // 項目削除
        $(document).on('click.deleteFormItem', '.js_delete_form_item', function (event) {
            var target = $(event.target).closest('.js_delete_form_item');
            var title = target.data('confirm-title');
            var message = target.data('confirm-message');
            var html = target.data('confirm-html');

            app.common.confirmDialog(message, title, html).done(function (dialog) {
                deleteFormItem(
                    target.closest('.js_delete_form_item').data('form-type'),
                    target.closest('.js_delete_form_item').data('session-key')
                );
                refreshSortableTr();
                event.preventDefault();
            });
        });

        // 入力タイプ変更
        $(document).on('click.formItemInputTypeChanger', '.js_form_item_input_type_changer', function (event) {
            var thisMenuWrap = $(event.target).parents('.js-menu-wrap');
            var thisMenuBtn = thisMenuWrap.find('.js-menu-btn');
            var openClass = 'is-open';

            thisMenuBtn.removeClass(openClass);
            $(event.target).addClass(openClass);

            changeInputType(dialog, $(event.target).data('input-type'));

            event.preventDefault();
        });

        // 終了日タイプ変更
        $(document).on('change.formItemDateUpperLimitType', '.js_form_item_date_upper_limit_type', function (event) {
            changeDateUpperLimitType(dialog, $(event.target).val());

            event.preventDefault();
        });

        setSortable();

    });
})(window, jQuery, window.app);
