window.app = {};

(function (window, $, app) {
    var ajax = {};
    var searchAddressDialog = null;
    var localStorageObject = null;

    app.common = {
        // 多重送信防止
        submitOnce: function (target) {
            $(target).off('submit');
            $(target).on('submit.submitOnce', function (event) {
                event.preventDefault();
                event.stopPropagation();
            });
        },

        // 多重押下防止
        clickOnce: function (target) {
            $(target).off('click');
            $(target).on('click.clickOnce', function (event) {
                event.preventDefault();
                event.stopPropagation();
            });
        },

        // 非同期通信
        ajax: function (name, settings) {
            var deffered = new $.Deferred();
            var csrfName = '_csrfToken';

            // CSRF
            settings = $.extend(true, {}, settings);
            if (typeof (settings) === 'object') {
                if ('type' in settings && settings.type.toLowerCase() === 'post') {
                    if ($('[name="' + csrfName + '"]').length > 0) {
                        if (!('data' in settings)) {
                            settings.data = {};
                        }
                        if (typeof (settings.data) === 'object' && !(csrfName in settings.data)) {
                            settings.data[csrfName] = $('[name="' + csrfName + '"]').val();
                        }
                    }
                }
                if (!('cache' in settings)) {
                    settings.cache = false;
                }
            }

            // 多重送信防止
            if (name in ajax) {
                ajax[name].abort();
            }
            ajax[name] = $.ajax(settings);
            ajax[name].always(function (jqXHR, textStatus) {
                delete ajax[name];
            });

            // コールバック
            ajax[name].done(function (data, textStatus, jqXHR) {
                deffered.resolve(data);
            });
            ajax[name].fail(function (jqXHR, textStatus, errorThrown) {
                if (typeof (jqXHR) === 'object' && 'status' in jqXHR && jqXHR.status > 0) {
                    if (typeof (jqXHR.responseJSON) === 'object' && 'message' in jqXHR.responseJSON) {
                        deffered.reject(jqXHR.responseJSON.message);
                    } else {
                        deffered.reject(errorThrown);
                    }
                }
            });
            ajax[name].always(function () {
                app.common.hideLoading();
            });

            // ローディング画像
            if (typeof settings.notLoading === "undefined" || !settings.notLoading) {
                app.common.showLoading(settings.loadDom);
            }

            return deffered.promise();
        },

        // 戻る
        historyBack: function () {
            window.history.back();
        },

        // 画面遷移
        changeLocation: function (url, query, targetWindow) {
            if (!$.isWindow(targetWindow)) {
                targetWindow = window.self;
            }

            targetWindow.location.href = app.common.mergeUrlQuery(url, query);
        },

        // 新規画面
        openWindow: function (url, query) {
            window.open(app.common.mergeUrlQuery(url, query));
        },

        // POST送信
        post: function (url, form, clone) {
            var csrfName = '_csrfToken';

            if (typeof (form) !== 'undefined') {
                if (clone) {
                    form = $(form).clone();
                    $('.js_container').append($(form));
                }
            } else {
                form = $('<form></form>');
                form.hide();
                $('.js_container').append(form);
            }

            if (typeof (url) !== 'undefined' && url !== null) {
                $(form).prop('action', url);
            }
            $(form).prop('method', 'post');
            if ($('[name="' + csrfName + '"]').length > 0 && $(form).find('[name="' + csrfName + '"]').length <= 0) {
                $(form).append($('[name="' + csrfName + '"]').clone());
            }
            $(form).submit();

            return $(form);
        },

        // ダイアログ
        dialog: function (html, options) {
            var element = $(html);
            var defaultOptions = {
                appendTo: '.js_container',
                classes: {
                    'ui-dialog': 'cmn-popup'
                },
                closeOnEscape: false,
                modal: true,
                title: '',
                open: function (event, ui) {
                    var options = $(this).dialog("option");
                },
                create: function(event, ui) {
                    $(this).parent().draggable('option', 'handle', '.ui-dialog-title');
                }
            };

            if (typeof (options) !== 'object') {
                options = {};
            }
            options = $.extend(true, {}, defaultOptions, options);

            element.dialog(options);
            element.on('dialogclose', function (event, ui) {
                $(event.target).dialog('destroy');
            });

            return element;
        },

        // 確認ダイアログ
        confirmDialog: function (message, title, addition) {
            var deffered = new $.Deferred();
            var element = $($('.js_confirm_dialog_html').val());

            element.find('.js_confirm_dialog_message').text(message);
            message = element.find('.js_confirm_dialog_message').html().replace(/\\r|\\n|\\r\\n/g, '<br>');
            element.find('.js_confirm_dialog_message').html(message);


            if (typeof (addition) !== 'undefined') {
                element.find('.js_confirm_dialog_addition').html(addition);
            }
            if (typeof (title) === 'undefined') {
                title = '';
            }

            app.common.dialog(element, {
                buttons: [{
                    class: 'btn-cancel',
                    click: function (event, ui) {
                        $(this).dialog('close');
                    },
                    text: $('.js_confirm_dialog_no').val()
                }, {
                    class: 'btn-submit',
                    click: function (event, ui) {
                        deffered.resolve(element);
                        $(this).dialog('close');
                    },
                    text: $('.js_confirm_dialog_yes').val()
                }],
                classes: {
                    'ui-dialog': 'caution-popup'
                },
                title: title,
                width: app.common.getScreenWidth(500),
            });

            element.on('dialogbeforeclose', function (event, ui) {
                if (deffered.state() === 'pending') {
                    deffered.reject(element);
                }
            });

            return deffered.promise();
        },

        // エラーダイアログ
        errorDialog: function (message, title) {
            var element = $($('.js_error_dialog_html').val());

            element.find('.js_error_dialog_message').text(message);
            element.find('.js_error_dialog_close').on('click', function (event) {
                element.dialog('close');
            });
            if (typeof (title) === 'undefined') {
                title = '';
            }

            return app.common.dialog(element, {
                classes: {
                    'ui-dialog': 'caution-popup'
                },
                title: title,
                width: app.common.getScreenWidth(300)
            });
        },

        // タブ
        tabs: function (element, options) {
            var defaultOptions = {
                classes: {
                    'ui-tabs': 'js_tabs'
                },
                heightStyle: 'fill'
            };

            if (typeof (options) !== 'object') {
                options = {};
            }
            options = $.extend(true, {}, options, defaultOptions);

            $(element).tabs(options);
        },

        // アコーディオン
        accordion: function (element, options) {
            var defaultOptions = {
                classes: {
                    'ui-accordion-header': 'js_accordion_header',
                    'ui-accordion-content': 'js_accordion_content'
                },
                collapsible: true
            };

            if (typeof (options) !== 'object') {
                options = {};
            }
            options = $.extend(true, {}, options, defaultOptions);

            $(element).accordion(options);
        },

        // 入力項目追加
        addInput: function (container, html, indexElement, indexReplace) {
            var element = null;
            var index = app.common.maxValue(indexElement, container);

            if (index === null) {
                index = -1;
            }
            index += 1;

            element = $(html.replace(new RegExp(indexReplace, 'g'), index));

            if ($(element).find('dt.addIndex').length >= 1) {
                $(element).find('dt.addIndex').text(parseInt($(element).find('dt.addIndex').text()) + 1);
            }

            $(container).append(element);

            return element;
        },

        // 入力項目削除
        removeInput: function (selector, context) {
            $(context).find(selector).remove();
        },

        // 最小値取得
        minValue: function (selector, context) {
            var minValue = null;

            $(context).find(selector).each(function (elementIndex, element) {
                var value = parseInt($(element).val(), 10);

                if (!isNaN(value)) {
                    minValue = Math.min(value, minValue);
                }
            });

            return minValue;
        },

        // 最大値取得
        maxValue: function (selector, context) {
            var maxValue = null;

            $(context).find(selector).each(function (elementIndex, element) {
                var value = parseInt($(element).val(), 10);

                if (!isNaN(value)) {
                    maxValue = Math.max(value, maxValue);
                }
            });

            return maxValue;
        },

        // URLデコード
        decodeUriComponent: function (value) {
            return decodeURIComponent(value.replace(new RegExp('\\+', 'g'), '%20'));
        },

        // クエリ取得
        parseUrlQuery: function (url) {
            var query = {};

            $.each(url.replace(new RegExp('^.*\\?'), '').split('&'), function (index, data) {
                var split = data.split('=');

                query[app.common.decodeUriComponent(split[0])] = app.common.decodeUriComponent(split[1]);
            });

            return query;
        },

        // クエリ付加
        mergeUrlQuery: function (url, query) {
            if (typeof (query) === 'undefined') {
                query = '';
            }
            if (typeof (query) === 'object') {
                query = $.param(query);
            }

            if (query !== '') {
                if (url.search(/\?/) < 0) {
                    url += '?';
                } else {
                    url += '&';
                }
                url += query;
            }

            return url;
        },

        // クエリ除去
        stripUrlQuery: function (url) {
            return url.replace(new RegExp('\\?.*$'), '');
        },

        // フォームリセット
        resetForm: function (form) {
            $(form).find('input:text,input:password,textarea').each(function (index, element) {
                $(element).val('');
            });
            $(form).find('input:checkbox,input:radio').each(function (index, element) {
                $(element).prop('checked', false);
            });
            $(form).find('select option').each(function (index, element) {
                $(element).prop('selected', false);
            });
        },

        // 特定以下のinput、select、texteareaをdisabledにして特定エリアを非表示にする
        disabledInput: function (className) {
            if (typeof (className) === 'undefined') {
                return;
            }

            if ($(className).length) {
                $(className + ' input,' + className + ' select,' + className + ' textarea').each(function (index, element) {
                    var $elem = $(element);
                    $elem.prop('disabled', true);
                });

                $(className).hide();
            }
        },
        // 特定以下のinput、select、texteareaをdisabledを解除にして特定エリアを表示にする
        unDisabledInput: function (className) {
            if (typeof (className) === 'undefined') {
                return;
            }

            if ($(className).length) {
                $(className + " input," + className + " select," + className + " textarea").each(function (index, element) {
                    var $elem = $(element);
                    $elem.prop('disabled', false);
                });

                $(className).show();
            }
        },

        // 住所検索
        searchAddress: function (url, zip) {
            var deferred = $.Deferred();

            app.common.ajax('zipSearch', {
                url: url,
                type: 'post',
                data: {
                    zip: zip
                }
            }).done(function (result) {
                if (result.data.length <= 1) {
                    deferred.resolve(result.data[0]);
                } else {
                    if (searchAddressDialog !== null) {
                        searchAddressDialog.dialog('close');
                    }
                    searchAddressDialog = app.common.dialog(result.html, {
                        width: app.common.getScreenWidth(800),
                        height: 400,
                    });
                    searchAddressDialog.on('dialogclose', function (event, ui) {
                        searchAddressDialog = null;
                    });
                    searchAddressDialog.find('.js_search_address_select').on('click', function (event) {
                        deferred.resolve($(event.target).data('address'));
                        searchAddressDialog.dialog('close');
                    });
                }
            }).fail(function (error) {
                app.common.errorDialog(error);
                deferred.reject();
            });

            return deferred.promise();
        },

        //tinymce
        toWysiWyg: function (options) {

            var defaultOptions = {
                selector: 'textarea.wysiwyg',
                language: 'ja',
                branding: false,
                plugins: [
                    'advlist',
                    'autolink',
                    'lists',
                    'link',
                    'image',
                    'charmap',
                    'preview',
                    'anchor',
                    'searchreplace',
                    'visualblocks',
                    'code',
                    'fullscreen',
                    'insertdatetime',
                    'media',
                    'table'
                ],
                toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',

                resize: 'both',
                convert_urls: false,
                height: 400,
                content_css: ["/assets/css/common/common.css", "/assets/css/user/base.css", "/assets/css/user/parts_design.css"],
                body_class: 'edit-area wysiwyg-area',
                promotion: false, // ver6.2から追加されたpromotion optionを無効化
                sandbox_iframes_exclusions: [
                    'google.com'
                ],
                license_key: 'gpl',
            };

            if (typeof (options) !== 'object') {
                options = {};
            }
            options = $.extend(true, {}, defaultOptions, options);

            tinymce.init(options);

            $('textarea.wysiwyg').each(function (index, element) {
                tinymce.execCommand('mceAddEditor', true, $(element).attr('id'));
            });
        },

        removeWysiWyg: function (target) {
            $(target).find('textarea.wysiwyg').each(function (index, element) {
                tinymce.execCommand('mceRemoveEditor', true, $(element).attr('id'));
            });
        },

        // Multiple Select
        multipleSelect: function (element, options) {
            var defaultOptions = {
                formatSelectAll: function () {
                    return $(element).data('select-all-text');
                },
                width: 400,
                allSelected: true,
            };
            if (typeof (options) !== 'object') {
                options = {};
            }
            options = $.extend(true, {}, options, defaultOptions);

            $(element).multipleSelect(options);
        },

        // ローディング画像表示
        showLoading: function (loadDom) {
            var html = $($('.js_loading_html').val());
            if ($('.js_loading_container').length <= 0) {
                if (typeof (loadDom) !== 'undefined') {
                    $(loadDom).prepend(html.css({'cssText': 'position: initial !important'}));
                } else {
                    $('.js_container').prepend(html);
                }
            }
        },

        // ローディング画像非表示
        hideLoading: function () {
            $('.js_loading_container').remove();
        },

        // datePikcer使用関数
        useDatePicker: function () {
            // 期間選択
            $('.js-datepicker-type-range-start:not(.js_datepicker_set)').datetimepicker({
                format: 'Y/m/d',
                yearStart: 1900,
                scrollInput: false,
                timepicker: false
            }).attr('autocomplete', 'off');
            $('.js-datepicker-type-range-start:not(.js_datepicker_set)').off('touchstart');
            $('.js-datepicker-type-range-start').addClass('js_datepicker_set');

            $('.js-datepicker-type-range-end:not(.js_datepicker_set)').datetimepicker({
                format: 'Y/m/d',
                yearStart: 1900,
                scrollInput: false,
                onShow: function (ct, input) {
                    var closest = 'td';
                    if (input.data('datepicker-dd') == 'on') {
                        closest = 'dd';
                    }
                    var startDate = input.closest(closest).find('.js-datepicker-type-range-start').val();

                    this.setOptions({
                        minDate: startDate ? startDate : false
                    });
                },
                timepicker: false
            }).attr('autocomplete', 'off');
            $('.js-datepicker-type-range-end:not(.js_datepicker_set)').off('touchstart');
            $('.js-datepicker-type-range-end').addClass('js_datepicker_set');

            // 日付選択
            $('.js-datepicker:not(.js_datepicker_set)').datetimepicker({
                format: 'Y/m/d',
                yearStart: 1900,
                scrollInput: false,
                onShow: function (ct, input) {
                    var startDate = input.data('start');
                    var endDate = input.data('end');

                    this.setOptions({
                        minDate: startDate ? startDate : false,
                        maxDate: endDate ? endDate : false,
                    });
                },
                onSelectDate: function (date, input) {
                    var url = input.data('url');
                    var param = input.data('param');

                    if (typeof (url) !== 'undefined') {
                        location.href = url + '?' + param + '=' + encodeURIComponent(input.val());
                    }
                },
                timepicker: false
            }).attr('autocomplete', 'off');
            $('.js-datepicker:not(.js_datepicker_set)').off('touchstart');
            $('.js-datepicker').addClass('js_datepicker_set');

            // 日時選択
            $('.js-datepicker-time:not(.js_datepicker_set)').datetimepicker({
                format: 'Y/m/d H:i',
                yearStart: 1900,
                step: 30,
            }).attr('autocomplete', 'off');
            $('.js-datepicker-time:not(.js_datepicker_set)').off('touchstart');
            $('.js-datepicker-time').addClass('js_datepicker_set');

            // 時間選択
            $('.js-timepicker:not(.js_datepicker_set)').datetimepicker({
                format: 'H:i',
                datepicker: false,
                validateOnBlur: false,
                defaultSelect: false,
                step: 30,
            }).attr('autocomplete', 'off');
            $('.js-timepicker:not(.js_datepicker_set)').off('touchstart');
            $('.js-timepicker').addClass('js_datepicker_set');
        },

        getFramePageQuery: function () {
            var query = {};

            if (typeof($('.js_calendar_container').data('calendar-frame')) !== 'undefined') {
                query.frame = $('.js_calendar_container').data('calendar-frame');
            }

            return query;
        },

        // ラベル変更
        changeLabelSelect: function (element, labelSelectType, id, excludeId, baseUrl) {
            var deferred = $.Deferred();
            var url = 'labels/parent';

            if ($.param(app.common.getFramePageQuery()) !== '') {
                url = url + '?' + $.param(app.common.getFramePageQuery());
            }

            app.common.ajax('labelsParent', {
                url: baseUrl + url,
                type: 'post',
                data: {
                    id: id,
                    exclude_id: excludeId,
                    label_select_type: labelSelectType
                },
                loadDom: '.js_label_select'
            }).done(function (result) {
                var html = $(result.html);

                element.replaceWith(html);
                html.find('.js_label_select_id').trigger('change');
                deferred.resolve();
            }).fail(function (error) {
                app.common.errorDialog(error);
                deferred.reject();
            });

            return deferred.promise();
        },

        //現在のスクリーンの大きさからをpercentage%の大きさを取得
        getScreenWidth: function (maxWidth, percentage, minWidth) {
            if (percentage === undefined) {
                percentage = 90;
            }

            if (maxWidth === undefined) {
                maxWidth = $(window).width();
            }

            if (maxWidth <= window.innerWidth) {
                return maxWidth;
            } else {
                maxWidth = window.innerWidth * (percentage / 100);

                if (minWidth !== undefined && !isNaN(parseInt(minWidth)) && maxWidth <= minWidth) {
                    maxWidth = minWidth;
                }

                return maxWidth;
            }
        },

        // 最下部スクロールの判定
        isScrollBottom: function (offset) {
            if (typeof (offset) === 'undefined') {
                offset = 0;
            }

            if ($(window).scrollTop() < $('body').height() - $(window).innerHeight() + offset) {
                return false;
            }

            return true;
        },

        // エスケープ
        htmlEscape: function (str) {
            if (!str) {
                return;
            }

            return str.replace(new RegExp('[<>&"\'`]', 'g'), function (match) {
                const escape = {
                    '<': '&lt;',
                    '>': '&gt;',
                    '&': '&amp;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '`': '&#x60;'
                };

                return escape[match];
            });
        },

        // ローカルストレージ
        getLocalStorage: function () {
            if (localStorageObject === null) {
                localStorageObject = {
                    // 値の取得
                    getItem: function (key) {
                        var value = null;

                        try {
                            value = localStorage.getItem(key);
                        } catch(e) {
                            return null;
                        }

                        return value;
                    },

                    // 値の設定
                    setItem: function (key, value) {
                        try {
                            localStorage.setItem(key, value);
                        } catch(e) {
                            return false;
                        }

                        return true;
                    }
                };
            }

            return localStorageObject;
        },

        // 決済(各処理は個別JS)
        payment: {
            // トークン取得
            getToken: function (setting, data) {}
        },

        // 連続予約：曜日選択表示の制御
        toggleSelectDayOfWeek: function() {
            if ($('.js_select_day_of_week').prop('checked')) {
                $('.js_day_of_week').show();
            } else {
                $('.js_day_of_week').hide();
            }
        }
    };

    $(function () {
        $.datetimepicker.setLocale('ja');

        // Back Forward Cache
        $(window).on('unload', function (event) {
            // DO NOTHING
        });

        // Click Event
        $('.js_container').on('click.nothing', function (event) {
            // DO NOTHING
        });

        // 送信防止
        $(document).on('submit.noSubmit', '.js_no_submit', function (event) {
            event.preventDefault();
        });

        // 多重送信防止
        $(document).on('submit.submitOnce', '.js_submit_once', function (event) {
            app.common.submitOnce(event.target);
        });

        // 確認ダイアログ後遷移
        $(document).on('click.changeUrlConfirm', '.js_change_url_confirm', function (event) {
            var title = $(event.target).closest('.js_change_url_confirm').data('confirm-title');
            var message = $(event.target).closest('.js_change_url_confirm').data('confirm-message');
            var html = $(event.target).closest('.js_change_url_confirm').data('confirm-html');

            app.common.confirmDialog(message, title, html).done(function (dialog) {
                app.common.changeLocation($(event.target).closest('.js_change_url_confirm').data('url'));
            });

            event.preventDefault();
            event.stopPropagation();
        });

        // 確認ダイアログ
        $(document).on('submit.submitConfirm', '.js_submit_confirm', function (event) {
            var title = $(event.target).data('confirm-title');
            var message = $(event.target).data('confirm-message');
            var html = $(event.target).data('confirm-html');

            app.common.confirmDialog(message, title, html).done(function (dialog) {
                $(event.target).off('submit');
                $(event.target).on('submit', function (event2) {
                    app.common.submitOnce(event2.target);
                    event2.stopPropagation();
                });
                $(event.target).submit();
            });

            event.preventDefault();
            event.stopPropagation();
        });

        // 多重押下防止
        $(document).on('click.clickOnce', '.js_click_once', function (event) {
            app.common.clickOnce(event.target);
        });

        // 戻る
        $(document).on('click.historyBack', '.js_history_back', function (event) {
            app.common.historyBack();

            event.preventDefault();
        });

        // 画面遷移
        $(document).on('click.changeUrl', '.js_change_url', function (event) {
            app.common.changeLocation($(event.target).closest('.js_change_url').data('url'));

            event.preventDefault();
        });

        // 新規画面
        $(document).on('click.openWindow', '.js_open_window', function (event) {
            app.common.openWindow($(event.target).closest('.js_open_window').data('url'));

            event.preventDefault();
        });

        // 閉じる
        $(document).on('click.closeWindow', '.js_close_window', function (event) {
            window.close();

            event.preventDefault();
        });

        // POST送信
        $(document).on('click.postLink', '.js_post_link', function (event) {
            app.common.post($(event.target).closest('.js_post_link').data('url'), $('.js_post_link_form'));

            event.preventDefault();
        });

        // POST送信(複数回許可)
        $(document).on('click.postLinkPlural', '.js_post_link_plural', function (event) {
            app.common.post(
                $(event.target).closest('.js_post_link_plural').data('url'),
                $('.js_post_link_form'),
                true
            ).remove();

            event.preventDefault();
        });

        // POST送信(確認)
        $(document).on('click.postConfirm', '.js_post_confirm', function (event) {
            var title = $(event.target).closest('.js_post_confirm').data('confirm-title');
            var message = $(event.target).closest('.js_post_confirm').data('confirm-message');
            var html = $(event.target).closest('.js_post_confirm').data('confirm-html');
            var form = $(event.target).closest('.js_post_confirm').data('confirm-form');

            if (typeof (form) === 'undefined') {
                form = '.js_post_link_form';
            }
            app.common.confirmDialog(message, title, html).done(function (dialog) {
                app.common.post($(event.target).closest('.js_post_confirm').data('url'), $(form));
            });

            event.preventDefault();
        });

        // 入力項目追加
        $(document).on('click.addInput', '.js_add_input', function (event) {
            app.common.addInput(
                $(event.target).data('container'),
                $(event.target).data('html'),
                $(event.target).data('index-element'),
                $(event.target).data('index-replace')
            );

            app.common.toWysiWyg();

            app.common.useDatePicker();

            event.preventDefault();
        });

        // 入力項目削除
        $(document).on('click.removeInput', '.js_remove_input', function (event) {
            var title = $(event.target).closest('.js_remove_input').data('confirm-title');
            var message = $(event.target).closest('.js_remove_input').data('confirm-message');
            var html = $(event.target).closest('.js_remove_input').data('confirm-html');

            if (typeof (message) === 'undefined') {
                app.common.removeInput(
                    $(event.target).closest('.js_remove_input').data('selector'),
                    $(event.target).closest('.js_remove_input').data('context')
                );
            } else {
                app.common.confirmDialog(message, title, html).done(function (dialog) {
                    app.common.removeInput(
                        $(event.target).closest('.js_remove_input').data('selector'),
                        $(event.target).closest('.js_remove_input').data('context')
                    );
                });
            }

            event.preventDefault();
        });

        // 表示件数
        $(document).on('change.changeSearchLimit', '.js_change_search_limit', function (event) {
            app.common.changeLocation($(event.target).data('url'), {
                limit: $(event.target).val(),
                page: '1'
            });
        });

        // ページ変更
        $(document).on('click.paginatorPage', '.js_paginator_page', function (event) {
            app.common.changeLocation($(event.target).closest('.js_paginator_page').data('url'));

            event.preventDefault();
        });

        // ソート
        $(document).on('click.paginatorSort', '.js_paginator_sort', function (event) {
            app.common.changeLocation($(event.target).closest('.js_paginator_sort').data('url'));

            event.preventDefault();
        });

        // 住所検索
        $(document).on('click.searchAddress', '.js_search_address', function (event) {
            var container = $(event.target).closest('.js_search_address_input');
            var zip = container.find('.js_search_address_input_zip').val();

            app.common.searchAddress($(event.target).data('url'), zip).done(function (result) {
                container.find('.js_search_address_input_zip').val(result.zip);
                container.find('.js_search_address_input_prefecture').val(result.prefecture);
                container.find('.js_search_address_input_municipality').val(result.municipality);
                container.find('.js_search_address_input_town').val(result.town);
            });

            event.preventDefault();
        });

        $(document).on('input', '.js-datepicker,.js-datepicker-time,.js-datepicker-type-range-start,.js-datepicker-type-range-end,.js-timepicker', function () {
            // 半角変換
            var halfVal = $(this).val().replace(
                /[！-～]/g,
                function (tmpStr) {
                    // 文字コードをシフト
                    return String.fromCharCode(tmpStr.charCodeAt(0) - 0xFEE0);
                }
            );
            // 数字以外の不要な文字を削除
            $(this).val(halfVal.replace(/[^0-9\/:\s]/g, ''));
        });

        // ツールチップ
        $('button').tooltip();

        app.common.useDatePicker();

        //共通ダイアログ
        $(document).on('click.commonDialog', '.js_common_dialog', function (event) {
            html = $(event.target).closest('.js_common_dialog').data('html');
            html = '<div><div class="popup-content">' + html + '</div></div>';
            options = {
                width: 800,
                height: 300,
                'title': $(event.target).closest('.js_common_dialog').data('title'),
            };

            app.common.dialog(html, options);
        });

        //ダイアログ範囲外閉じる処理
        $(document).on('click.dialogClose', '.ui-widget-overlay', function () {
            $(this).prev().find('.ui-dialog-content').dialog('close');
        });

        $('body.noNavi').css('width', $(window).innerWidth());

        if ($('.js_auto_submit').length > 0) {
            window.setTimeout(function () {
                $('.js_auto_submit').submit();
            }, 0);
        }

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
            app.common.toggleSelectDayOfWeek();
            $('.js_day_of_week').removeClass('hidden').hide();
            $('.js_day_of_week_' + $('.js_select_day_of_week:checked').val()).show();
        });
        
    });
})(window, jQuery, window.app);
