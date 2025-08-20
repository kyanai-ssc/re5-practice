(function (window, $, app) {
    var dispLabel = function (mailType) {
        var dispLabelIds = [];

        if ($("#dispLabelIds").data('ids') != null) {
            dispLabelIds = $("#dispLabelIds").data('ids').split(",");
        }

        // ラベル非表示の場合
        if ($.inArray(mailType.toString(), dispLabelIds) === -1) {
            $(".js_label_area").addClass("hidden");
            // ラベル表示の場合
        } else {
            $(".js_label_area").removeClass("hidden");
        }

    };

    var dispUserAuthority = function (mailType) {
        var dispUserAuthorityIds = [];

        if ($('#dispUserAuthorityIds').data('ids') != null) {
            dispUserAuthorityIds = $('#dispUserAuthorityIds').data('ids').split(",");
        }

        // ラベル非表示の場合
        if ($.inArray(mailType.toString(), dispUserAuthorityIds) === -1) {
            $('.js_user_authority_area').addClass('hidden');
            // ラベル表示の場合
        } else {
            $('.js_user_authority_area').removeClass('hidden');
        }
    };

    var dispAdminSend = function (checked) {
        if (checked) {
            $(".adminSend").show();
        } else {
            $(".adminSend").hide();
        }
    };

    var dispStatus = function (mailType) {
        $('.js_reservation_status').removeClass('show');
        $('.js_reservation_status').addClass('hidden');
        $('.js_reservation_status_' + mailType).removeClass('hidden');
        $('.js_reservation_status_' + mailType).addClass('show');

        $(".hide input").each(function (index, element) {
            var $elem = $(element);
            $elem.prop('disabled', true);
        });

        $(".show input").each(function (index, element) {
            var $elem = $(element);
            $elem.prop('disabled', false);
        });
    };

    $(function () {
        // タイプ変更時
        $(document).on('change', '[name="type"]', function (_event) {
            dispLabel($(_event.target).val());
            dispUserAuthority($(_event.target).val());
            dispStatus($(_event.target).val());

            $('.js_replace_vars').hide();
            $('.js_replace_vars_' + $(_event.target).val()).show();

        });


        $(document).on('change', '[name="admin_operation_mail_flg"]', function (_event) {
            dispAdminSend($(_event.target).prop('checked'));
        });

        // メール形式変更時
        $(document).on('change', '[name="content_type"]', function (_event) {
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

        $('[name="type"]').trigger('change');
        $('[name="content_type"]:checked').trigger('change');
        $('[name="admin_operation_mail_flg"]').trigger('change');
    });

    $(document).on('click', '.js_send_test_mail', function (_event) {
        var ajax = {};
        var data = {};

        $.fn.serializeAllArray = function () {
            var obj = {};

            $('input,select,textarea:not(:disabled)', this).each(function () {
                obj[this.name] = $(this).val();
            });

            return obj;
        };

        data = $('form').serializeAllArray();
        data.mail = $('input[name="test_mail"]').val();
        data.content_type = $('input[name="content_type"]:checked').val();

        if ($('input[name="content_type"]:checked').val() == $('input[name="content_type"]:checked').data('html')) {
            data.header = tinymce.get('autoReplyMails-header-html').getContent();
            data.contents = tinymce.get('autoReplyMails-contents-html').getContent();
            data.footer = tinymce.get('autoReplyMails-footer-html').getContent();
        }

        ajax.url = app.adminCommon.ajaxUrl() + 'auto-reply-mails/test-mail';
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
                        $(this).dialog("close");
                    }
                }
            });
        }).fail(function (_errorThrow, _message) {
            app.common.errorDialog(_message);
        });
    });

})(window, jQuery, window.app);
