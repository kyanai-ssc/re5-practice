(function (window, $, app) {
    $(function () {
        // 確認ダイアログとPOST値JSON化
        $(document).on('submit.submitConfirm', '.js_submit_json_confirm', function (event) {
            var title = $(event.target).data('confirm-title');
            var message = $(event.target).data('confirm-message');
            var html = $(event.target).data('confirm-html');

            app.common.confirmDialog(message, title, html).done(function (dialog) {
                // JSONデータを生成
                var formData = $(event.target).serializeArray();
                var formJson = JSON.stringify(formData);
                $('<input>').attr({
                    'type': 'hidden',
                    'name': 'formJson',
                    'value': formJson
                }).appendTo($(event.target));
                $('[name="name"]').attr('disabled', true);
                $('[name="remark"]').attr('disabled', true);
                $('.js_form_pattern_display_types').attr('disabled', true);
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

        // グループ変更
        $(document).on('change', '.js_select_form_group_id', function (event) {
            var formGroupId = $(event.target).val();
            var url = new URL(window.location.href);
            // パラメータを除去
            url.searchParams.delete('group');
            // パラメータにグループIDを付与
            url.searchParams.append('group', formGroupId);
            // リロード
            location.href = url;
        });
    });
})(window, jQuery, window.app);
