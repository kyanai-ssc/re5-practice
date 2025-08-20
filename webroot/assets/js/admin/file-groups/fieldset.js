(function (window, $, app) {
    $(function () {
        var fileUpload = $('.js_file_upload').clone();
        var fileUploadEdit = $('.js_file_upload_edit').clone();

        $(document).on('change', '.js_file_upload', function (_event) {
            // フォームデータを取得
            var formdata = new FormData($('.js_form_upload').get(0));

            var data = {};
            var ajax = {};

            if ($(_event.target).val() !== '') {
                formdata.append("file", $(_event.target).prop("files")[0]);

                if (!$('.js_files_index')[0]) {
                    $index = 0;
                } else {
                    $index = parseInt($('.js_files_index').last().val()) + 1;
                }

                if (typeof $('form').data('id') === 'undefined') {
                    $id = '';
                } else {
                    $id = $('form').data('id');
                }

                formdata.append('id', $id);
                formdata.append("index", $index);
                formdata.append($('.js_upload_token').attr('name'), $('.js_upload_token').val());
                formdata.append("_csrfToken", $("[name='_csrfToken']").val());
            }

            data = formdata;

            ajax.url = app.adminCommon.ajaxUrl() + 'files/upload';
            ajax.type = 'post';
            ajax.data = data;
            ajax.dataType = 'json';
            ajax.cache = false;
            ajax.contentType = false;
            ajax.processData = false;


            app.common.ajax('file_upload', ajax).done(function (_data) {
                if (_data.result) {
                    var html = $(_data.html);
                    $('.js_file_form').replaceWith(html);
                } else {
                    app.common.dialog(_data.html, {
                        title: _data.title,
                        buttons: {
                            OK: function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }).fail(function (_message) {
                app.common.errorDialog($(".js_error_message").data('error-message'));
            });

            $('.js_file_upload').replaceWith(fileUpload.clone());
        });

        $(document).on('change', '.js_file_upload_edit', function (_event) {
            // フォームデータを取得
            var formdata = new FormData($('.js_form_upload').get(0));

            var data = {};
            var ajax = {};

            if ($(_event.target).val() !== '') {
                formdata.append('id', $('form').data('id'));
                formdata.append("file", $(_event.target).prop("files")[0]);
                formdata.append("index", $(event.target).data('index'));
                formdata.append($('.js_upload_token').attr('name'), $('.js_upload_token').val());
                formdata.append("_csrfToken", $("[name='_csrfToken']").val());
            }

            data = formdata;

            ajax.url = app.adminCommon.ajaxUrl() + 'files/upload-edit';
            ajax.type = 'post';
            ajax.data = data;
            ajax.dataType = 'json';
            ajax.cache = false;
            ajax.contentType = false;
            ajax.processData = false;

            app.common.ajax('file_upload', ajax).done(function (_data) {
                if (!_data.result) {
                    app.common.dialog(_data.html, {
                        title: _data.title,
                        buttons: {
                            OK: function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }).fail(function (_errorThrow, _message) {
                app.common.errorDialog($(".js_error_message").data('error-message'));
                editClone = fileUploadEdit.clone();
                $(_event.target).replaceWith(editClone[$(_event.target).data('index')]);
            });
        });
    });
})(window, jQuery, window.app);
