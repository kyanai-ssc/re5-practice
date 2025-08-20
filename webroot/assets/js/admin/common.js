(function (window, $, app) {
    var searchItemsDialog = null;
    var listItemsDialog = null;
    var updateStatusDialog = null;

    // 検索項目設定
    var editAdminSearchItems = function (elem) {
        var type = elem.data('type');
        var title = elem.data('title');

        app.common.ajax('adminSearchItemsEdit', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'admin-search-items/edit', {
                type: type
            }),
            type: 'get',
            data: {}
        }).done(function (result) {
            showAdminSearchItemsDialog(result.html, title);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 検索項目設定実行
    var editExecAdminSearchItems = function (type, data) {
        app.common.ajax('adminSearchItemsEditExec', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'admin-search-items/edit', {
                type: type
            }),
            type: 'post',
            data: data
        }).done(function (result) {
            if (result.finish) {
                $(document).trigger('adminSearchItemsEditFinish');
                if (searchItemsDialog !== null) {
                    $(searchItemsDialog).dialog('close');
                    searchItemsDialog = null;
                }
            } else {
                showAdminSearchItemsDialog(result.html);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 検索項目設定画面表示
    var showAdminSearchItemsDialog = function (html, title) {
        if (searchItemsDialog !== null) {
            $(searchItemsDialog).dialog('close');
        }

        searchItemsDialog = app.common.dialog(html, {
            width: app.common.getScreenWidth(500),
            classes: {
                'ui-dialog': 'tool-popup'
            },
            title: title,
            buttons: false,
        });
        searchItemsDialog.on('dialogclose', function (event, ui) {
            searchItemsDialog = null;
        });
    };

    // 一覧項目設定
    var editAdminListItems = function (type, title) {
        app.common.ajax('adminListItemsEdit', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'admin-list-items/edit', {
                type: type
            }),
            type: 'get',
            data: {}
        }).done(function (result) {
            showAdminListItemsDialog(result.html, title);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 一覧項目設定実行
    var editExecAdminListItems = function (type, data) {
        app.common.ajax('adminListItemsEditExec', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'admin-list-items/edit', {
                type: type
            }),
            type: 'post',
            data: data
        }).done(function (result) {
            if (result.finish) {
                $(document).trigger('adminListItemsEditFinish');
                if (listItemsDialog !== null) {
                    $(listItemsDialog).dialog('close');
                    listItemsDialog = null;
                }
            } else {
                showAdminListItemsDialog(result.html);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // 一覧項目設定画面表示
    var showAdminListItemsDialog = function (html, title) {
        if (listItemsDialog !== null) {
            $(listItemsDialog).dialog('close');
        }

        listItemsDialog = app.common.dialog(html, {
            width: Math.floor($(window).innerWidth() * 0.99),
            title: title,
            classes: {
                'ui-dialog': 'thEdit-popup'
            },
            buttons: false,
            open: function (event) {
                app.adminCommon.sortableDom({axis: 'x'});
            }
        });
        listItemsDialog.on('dialogclose', function (event, ui) {
            listItemsDialog = null;
        });
    };

    // ステータス変更
    var updateStatus = function (reservationId) {
        app.common.ajax('reservationsUpdateStatus', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/update-status', {
                id: reservationId
            }),
            type: 'get',
            data: {}
        }).done(function (result) {
            showUpdateStatusDialog(result.html);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // ステータス変更実行
    var updateStatusExec = function (reservationId, data) {
        app.common.ajax('reservationsUpdateStatusExec', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/update-status', {
                id: reservationId
            }),
            type: 'post',
            data: data
        }).done(function (result) {
            if (result.finish) {
                if (updateStatusDialog !== null) {
                    $(updateStatusDialog).dialog('close');
                    updateStatusDialog = null;
                }
                app.common.hideLoading();
                statusSpan = $('span.js_update_status[data-reservation-id=' + reservationId + ']');
                statusSpan.removeClass(function (index, className) {
                    return (className.match(/\bis-\S+/g) || []).join(' ');
                });

                statusSpan.addClass(result.statusData.class);
                statusSpan.text(result.statusData.name);
                result.optionalMessages.forEach(function(item, index) {
                    app.common.errorDialog(item);
                })
            } else {
                showUpdateStatusDialog(result.html);
            }
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    // ステータス変更画面表示
    var showUpdateStatusDialog = function (html) {
        if (updateStatusDialog !== null) {
            $(updateStatusDialog).dialog('close');
        }

        updateStatusDialog = app.common.dialog(html, {
            classes: {
                'ui-dialog': 'tool-popup'
            },
            width: app.common.getScreenWidth(300)
        });
        updateStatusDialog.on('dialogclose', function (event, ui) {
            updateStatusDialog = null;
        });
    };

    app.adminCommon = {
        // ベースURL
        baseUrl: function () {
            return '/admin/';
        },

        // 非同期通信URL
        ajaxUrl: function () {
            return app.adminCommon.baseUrl() + 'ajax/';
        },

        //sort
        sortableDom: function (options) {
            var defaultOptions = {
                handle: '.handle',
                axis: 'y'
            };

            if (typeof (options) !== 'object') {
                options = {};
            }

            options = $.extend(true, {}, defaultOptions, options);

            $('.js_sortable').sortable(options);
        },

        //カラーピッカー
        setColorPicker: function (element, options) {
            var defaultOptions = {};
            options = $.extend(true, {}, defaultOptions, options);

            if (!('images' in options)) {
                options.images = {};
            }

            options.images.clientPath = '/assets/images/vendor/jpicker/';

            if (!('window' in options)) {
                options.window = {};
            }
            if (!('effects' in options.window)) {
                options.window.effects = {};
            }
            if (!('speed' in options.window.effects)) {
                options.window.effects.speed = {};
            }
            if (!('position' in options.window)) {
                options.window.position = {};
            }

            options.window.position = {
                y: 'bottom',
            };

            options.window.effects.type = 'show';
            options.window.effects.speed.show = 'fast';
            options.window.effects.speed.hide = 'fast';
            options.window.liveUpdate = false;


            var addPrefix = function (_element, _color, _index) {
                var target = $(_element).prev().find('input');
                if (typeof (_color.val('hex')) === 'string') {
                    target.val('#' + _color.val('hex'));
                    target.css('background-color', target.val());
                    if ($.jPicker.List[_index].color.active.val('va') == null
                        || $.jPicker.List[_index].color.active.val('va').v > 75) {
                        $(target).css('color', '#000000');
                    } else {
                        $(target).css('color', '#FFFFFF');
                    }
                } else {
                    target.val('');
                }
            };

            $(element).each(function (_index, _element) {
                var target = $(_element).prev().find('input');
                // カラーコードに＃を追加
                $(_element).jPicker(options, function (_color, _context) {
                    addPrefix(_element, _color, _index);

                }, function (_color, _context) {
                    addPrefix(_element, _color, _index);
                }, function (_color, _context) {
                    addPrefix(_element, _color, _index);
                });

                if (target.val().search(/^#[0-9A-Fa-f]{6}$/) >= 0) {
                    target.css('background-color', target.val());

                    if ($.jPicker.List[_index].color.active.val('va') == null
                        || $.jPicker.List[_index].color.active.val('va').v > 75) {
                        $(target).css('color', '#000000');
                    } else {
                        $(target).css('color', '#FFFFFF');
                    }
                }

                //カラー変更時
                $(target).on('change', function (_event) {
                    if ($(_event.target).val().search(/^#[0-9A-Fa-f]{6}$/) >= 0) {
                        if ($(element).length === 1) {
                            _index = parseInt($.jPicker.List.length) - 1;
                        }

                        $.jPicker.List[_index].color.active.val('hex', $(_event.target).val(), _event.target);
                        if ($.jPicker.List[_index].color.active.val('va') == null
                            || $.jPicker.List[_index].color.active.val('va').v > 75) {
                            $(_event.target).css('color', '#000000');
                        } else {
                            $(_event.target).css('color', '#FFFFFF');
                        }

                        $(_event.target).css('background-color', $(_event.target).val());
                    } else if ($(_event.target).val() == '') {
                        $.jPicker.List[_index].color.active.val('hex', null, _event.target);
                        $(_event.target).css('background-color', '');
                        $(_event.target).css('color', '');
                    }
                });
            });
        },

        multipleSelector: function () {
            app.common.multipleSelect($('.js_multiple_select'));
        },

        // チェック情報保存
        saveCheck: function (url, element) {
            var deferred = $.Deferred();
            var ids = $('.js_check_all_target:checked').map(function () {
                return $(this).val();
            }).get();
            var notCheckIds = $('.js_check_all_target:not(":checked")').map(function () {
                return $(this).val();
            }).get();
            var allCheck = $('.js_search_check:checked').val();

            app.common.ajax('listSaveCheck', {
                url: url,
                type: 'post',
                data: {
                    ids: ids,
                    notCheckIds: notCheckIds,
                    allCheck: allCheck
                }
            }).done(function (result) {
                if ($(element).data('url') != undefined) {
                    if ($(element).data('method') != undefined && $(element).data('method') == 'post') {
                        app.common.post($(element).data('url'), $('.js_post_link_form'));
                    } else {
                        let query = {};
                        if ($(element).hasClass('js_change_search_limit')) {
                            query.limit = $(element).val();
                            query.page = 1;
                        }
                        if (result.searchPaymentExpired) {
                            query.search_payment_expired = 1;
                        }

                        if (result.searchSmartLockUnlinked) {
                            query.search_smart_lock_unlinked = 1;
                        }

                        app.common.changeLocation(app.common.mergeUrlQuery($(element).data('url'), query));
                    }
                }
                deferred.resolve();
            }).fail(function (error) {
                app.common.errorDialog(error);
                deferred.reject();
            });

            return deferred.promise();
        },

        // チェック情報保存用イベント
        setSaveCheckEvent: function () {
            $(document).off('click.paginatorPage', '.js_paginator_page');
            $(document).on('click.paginatorPage', '.js_paginator_page', function (event) {
                app.adminCommon.saveCheck($('.js_save_check_url').val(), $(event.target).closest('.js_paginator_page'));

                event.preventDefault();
            });

            $(document).off('click.paginatorSort', '.js_paginator_sort');
            $(document).on('click.paginatorSort', '.js_paginator_sort', function (event) {
                app.adminCommon.saveCheck($('.js_save_check_url').val(), $(event.target).closest('.js_paginator_sort'));

                event.preventDefault();
            });

            $(document).off('change.changeSearchLimit', '.js_change_search_limit');
            $(document).on('change.changeSearchLimit', '.js_change_search_limit', function (event) {
                app.adminCommon.saveCheck($('.js_save_check_url').val(), $(event.target));

                event.preventDefault();
            });
        },

        loadShowBtn: function () {
            //showBtnの開閉状況を再現
            var showBtn = $('.showBtn');
            var showBtnData = [];
            showBtnData = app.common.getLocalStorage().getItem(location.pathname);
            showBtnData = JSON.parse(showBtnData);
            if (Array.isArray(showBtnData)) {
                showBtnData.forEach(function (open, index) {
                    if (open === true) {
                        $(showBtn[index]).parent().next('.showWrap').toggle();
                        $(showBtn[index]).addClass('opend');
                    }
                });
            }
        },

        // 最下部スクロールの判定
        isScrollBottom: function (offset) {
            if (typeof (offset) === 'undefined') {
                offset = 0;
            }

            return app.common.isScrollBottom(offset + $('.js_global_navi').outerHeight());
        }
    };

    $(function () {
        // ラベル変更時
        $(document).on('change.changeLabelSelect', '.js_change_label_select', function (event) {
            var element = $(event.target).closest('.js_label_select');

            app.common.changeLabelSelect(
                element,
                element.find('.js_label_select_type').val(),
                $(event.target).val(),
                element.find('.js_label_select_exclude_id').val(),
                app.adminCommon.ajaxUrl()
            );

            event.preventDefault();
        });

        // 全チェック変更
        $(document).on('change.searchCheck', '.js_search_check', function (event) {
            if ($('.js_search_check').prop('checked')) {
                $('.js_check_all_target').prop('checked', true);
                $('.js_check_all_target').prop('disabled', true);
                $('.js_search_check').next('label').attr('title', ($('.js_check_search_all_off').data('label')));
            } else {
                $('.js_check_all_target').prop('checked', false);
                $('.js_check_all_target').prop('disabled', false);
                $('.js_search_check').next('label').attr('title', ($('.js_check_search_all_on').data('label')));
            }

            $('.js_check_all_target').trigger('change');
        });

        // 各チェック変更
        $(document).on('change.checkAllTarget', '.js_check_all_target', function (event) {
            $('.js_check_all_toggle').toggle($('.js_check_all_target:checked').length > 0);
        });

        // チェック情報をダイアログ表示後保存
        $(document).on('click.saveCheckDataConfirm', '.js_save_check_data_confirm', function (event) {
            var title = $(event.target).data('confirm-title');
            var message = $(event.target).data('confirm-message');
            var html = $(event.target).data('confirm-html');

            app.common.confirmDialog(message, title, html).done(function (dialog) {
                app.adminCommon.saveCheck($('.js_save_check_url').val(), $(event.target));
            });

            event.preventDefault();
        });

        // チェック情報を保存
        $(document).on('click.saveCheckData', '.js_save_check_data', function (event) {
            app.adminCommon.saveCheck($('.js_save_check_url').val(), $(event.target));

            event.preventDefault();
        });

        // 検索項目設定
        $(document).on('click.selectSearchItems', '.js_select_search_items', function (event) {
            editAdminSearchItems($(event.target).closest('.js_select_search_items'));
            event.preventDefault();
        });

        // 検索項目設定実行
        $(document).on('submit.selectSearchItemsExec', '.js_select_search_items_form', function (event) {
            editExecAdminSearchItems($(event.target).data('type'), $(event.target).serialize());

            event.preventDefault();
        });

        // 一覧項目設定
        $(document).on('click.selectListItems', '.js_select_list_items', function (event) {
            editAdminListItems(
                $(event.target).closest('.js_select_list_items').data('type'),
                $(event.target).closest('.js_select_list_items').data('title')
            );

            event.preventDefault();
        });

        // 一覧項目設定実行
        $(document).on('submit.selectListItemsExec', '.js_select_list_items_form', function (event) {
            editExecAdminListItems($(event.target).data('type'), $(event.target).serialize());

            event.preventDefault();
        });

        // インポート
        $(document).on('click', '.js_import', function (event) {
            var options = {
                width: app.common.getScreenWidth(500),
                title: $(event.target).closest('.js_import').data('title'),
            };

            var dialogDom = $($(event.target).closest('.js_import').data('html'));
            var dialog = app.common.dialog(dialogDom.html(), options);

            event.preventDefault();
        });

        // インポート
        $(document).on('click', '.js_import_submit', function (event) {
            var formdata = new FormData($('.js_form_import').get(0));

            var ajax = {};
            var data = {};

            if ($('.js_import_file').val() !== '') {
                formdata.append('file', $('.js_import_file').prop("files")[0]);
            }
            formdata.append("_csrfToken", $("[name='_csrfToken']").val());

            data = formdata;
            ajax.url = app.adminCommon.ajaxUrl() + $(event.target).data('url') + '/import';
            ajax.type = 'post';
            ajax.data = data;
            ajax.dataType = 'json';
            ajax.cache = false;
            ajax.contentType = false;
            ajax.processData = false;

            $('.js_import_file').val('');
            app.common.ajax('import', ajax).done(function (result) {
                $('.ui-dialog-title').text(result.title);
                if (!result.result) {
                    $('.js_import_error').html(result.message);
                } else {
                    $(event.target).closest('.popup-content').html(result.message);
                }
            }).fail(function (error) {
                $('.js_import_error').html(error);
            });
        });

        $('.js_file_select_btn').click(function (_event) {
            var inputName = $(_event.target).data('name');
            var iframeStyle = 'min-width     : 100%; ' +
                'height        : 100%; ';

            $('#js_frameWindow').append('<iframe id="js_iframeDiv" width="100%" data-name="' + inputName + '" + " style="' + iframeStyle + '"></iframe>');
            $('#js_iframeDiv').attr({
                src: app.adminCommon.baseUrl() + 'file-groups/list?selectFile=true',
            });
            var options = {
                close: function () {
                    $('#js_iframeDiv').remove();
                },
                width: app.common.getScreenWidth(1000, 95, 960),
                height: 1200,
                modal: true,
                title: $(_event.target).data('title'),
            };

            app.common.dialog($('#js_iframeDiv'), options);
        });

        if ($('textarea.wysiwyg').length) {
            app.common.toWysiWyg();
        }

        if ($('.showBtn').length) {
            app.adminCommon.loadShowBtn();
        }

        if ($('.colorPicker').length) {
            app.adminCommon.setColorPicker($('.colorPicker'));
        }

        $(document).on('click.showBtn', '.showBtn', function (event) {
            $(this).parent().next('.showWrap').toggle();
            $(this).toggleClass('opend');
            if (!$(this).data('notsave')) {
                var showBtnOpen = [];
                //現在の状態を保存
                $('.showBtn').each(function (index, open) {
                    showBtnOpen[index] = $(this).hasClass('opend');
                });
                app.common.getLocalStorage().setItem(location.pathname, JSON.stringify(showBtnOpen));
            }
        });

        // ステータス変更
        $(document).on('click.updateStatus', '.js_update_status', function (event) {
            updateStatus($(event.target).data('reservation-id'));

            event.preventDefault();
        });

        // ステータス選択肢変更
        $(document).on('change.updateStatus', '.js_reservation_status_id', function (event) {
            var form = $(event.target).closest('form');
            var confirmMessage = form.data('confirm-message');
            var confirmTitle = form.data('confirm-title');
            var confirmHtml = form.data('confirm-html');

            app.common.confirmDialog(confirmMessage, confirmTitle, confirmHtml).done(function (dialog) {
                var hiddenContainer = $('<div></div>');

                hiddenContainer.addClass('hidden');
                hiddenContainer.append(dialog.find('.js_mail_check_container').clone());
                form.append(hiddenContainer);
                form.trigger('submit');
            }).fail(function (dialog) {
                var statuses = form.find('.js_reservation_status_id');

                statuses.prop('checked', false);
                statuses.filter('[value="' + form.data('reservation-status-id') + '"]').prop('checked', true);
            });

            event.preventDefault();
        });

        // ステータス変更実行
        $(document).on('submit.updateStatusExec', '.js_update_status_form', function (event) {
            updateStatusExec($(event.target).data('reservation-id'), $(event.target).serialize());

            event.preventDefault();
        });

        // WYSIWYG
        $(document).on('focusin', '.tox-textfield,.tox-textarea', function (event) {
            if ($(event.target).closest('.tox-textfield,.tox-textarea').length > 0) {
                event.stopImmediatePropagation();
            }
        });

        // jPicker
        $(document).on('click', 'table.jPicker input[type="radio"]', function (event) {
            var target = $(event.target);
            target.parents('tbody:first').find('input:radio[value!="' + target.val() + '"]').prop('checked', false);
        });
    });
})(window, jQuery, window.app);
