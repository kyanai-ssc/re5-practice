(function (window, $, app) {
    $(function () {
        $(document).on('change', '.js_send_type', function (_event) {
            var reserve = $(_event.target).data('type');
            var value = $(_event.target).val();

            if (reserve == value) {
                $('.js_send_datetime').removeClass('hidden');
            } else {
                $('.js_send_datetime').addClass('hidden');
            }
        });

        $(document).on('change', '.js_content_type', function (_event) {
            var text = $(_event.target).data('text');
            var html = $(_event.target).data('html');
            var value = $(_event.target).val();

            if (text == value) {
                app.common.disabledInput('.js_contents_html');
                app.common.unDisabledInput('.js_contents_text');
            } else {
                app.common.disabledInput('.js_contents_text');
                app.common.unDisabledInput('.js_contents_html');
            }
        });

        $(document).on('click', '.js_send_test_mail', function (_event) {
            var ajax = {};
            var data = {};

            data.mail = $('input[name="test_mail"]').val();
            data.content_type = $('input[name="content_type"]:checked').val();

            ajax.url = app.adminCommon.ajaxUrl() + 'mail-deliveries/test-mail';
            ajax.type = 'post';
            ajax.data = data;
            ajax.dataType = 'json';

            app.common.ajax('send_testmail', ajax).done(function (_data) {
                app.common.dialog(_data.message, {
                    title: _data.title,
                    width: app.common.getScreenWidth(500),
                    classes: {
                        'ui-dialog': 'tool-popup'
                    },
                    buttons: {
                        OK: function () {
                            $(this).dialog('close');
                        }
                    }
                });
            }).fail(function (_errorThrow, _message) {
                app.common.errorDialog(_message);
            });
        });

        app.common.toWysiWyg();
        if ($('.js_send_type:checked').prop('checked', true).length > 0) {
            $('.js_send_type:checked').prop('checked', true).trigger('change');
        } else {
            $('.js_send_type:eq(0)').trigger('change');
        }

        if ($('.js_content_type:checked').prop('checked', true).length > 0) {
            $('.js_content_type:checked').prop('checked', true).trigger('change');
        } else {
            $('.js_content_type:eq(0)').trigger('change');
        }

    });
})(window, jQuery, window.app);
