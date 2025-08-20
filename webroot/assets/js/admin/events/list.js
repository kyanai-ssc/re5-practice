(function (window, $, app) {
    $(function () {
        app.adminCommon.setSaveCheckEvent();

        // フォーマットタイプ変更
        $(document).on('click', '.js_public_flg_update', function (_event) {
            var options = {
                dialogClass: 'caution-popup',
                classes: {
                    'ui-dialog': false
                },
                buttons: [{
                    text: $('.js_button_no').val(),
                    class: 'btn-cancel',
                    click: function (_btnEvent) {
                        $('input[name="' + $(_event.target).attr('name') + '"]:not(:checked)').prop('checked', true);
                        $(this).dialog('close');
                    }
                }, {
                    text: $('.js_button_yes').val(),
                    class: 'btn-submit',
                    click: function (_btnEvent) {
                        var ajax = {};
                        var data = {};

                        data.id = $(_event.target).data('eventid');
                        data.public_flg = $(_event.target).data('change');

                        ajax.url = app.adminCommon.ajaxUrl() + 'events/update-public';
                        ajax.type = 'post';
                        ajax.data = data;
                        ajax.dataType = 'json';

                        $(this).dialog('close');
                        app.common.ajax('update_public', ajax).done(function (_data) {
                            if (!_data.result) {
                                app.common.errorDialog(_data.message);
                            } else {
                                if ($(_event.target).parent().hasClass('is-release')) {
                                    $(_event.target).parent().addClass('hidden');
                                    $(_event.target).parent().next('.is-private').removeClass('hidden');
                                } else {
                                    $(_event.target).parent().addClass('hidden');
                                    $(_event.target).parent().prev('.is-release').removeClass('hidden');
                                }
                            }
                        }).fail(function (_errorThrow, _message) {
                            app.common.errorDialog(_message);
                        });
                    }
                },]
            };

            if ($(_event.target).hasClass('is-release')) {
                var message = $('.js_public_flg_update_message_private').html();
            } else {
                var message = $('.js_public_flg_update_message_open').html();
            }

            dialog = app.common.dialog(message,options);
            dialog.on('dialogclose', function (event, ui) {
                dialog = null;
            });
        });
    });
})(window, jQuery, window.app);
