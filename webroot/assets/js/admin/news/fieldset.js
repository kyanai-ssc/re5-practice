(function (window, $, app) {
    $(function () {
        // プレビュー（時間設定）
        var previewNews = function (event) {
            var form = $('#js_preview_news');
            var publicFrom = $('input[name=public_from]').val();
            var publicTo = $('input[name=public_to]').val();
            var title = $('input[name=title]').val();
            var contents = tinyMCE.get('news-contents').getContent();

            $('input[name=preview_title]').remove();
            $('input[name=preview_contents]').remove();
            $('input[name=preview_public_from]').remove();
            $('input[name=preview_public_to]').remove();

            $('<input>').attr({
                'type': 'hidden',
                'name': 'preview_title',
                'value': title
            }).appendTo(form);

            $('<input>').attr({
                'type': 'hidden',
                'name': 'preview_contents',
                'value': contents
            }).appendTo(form);

            $('<input>').attr({
                'type': 'hidden',
                'name': 'preview_public_from',
                'value': publicFrom
            }).appendTo(form);

            form.submit();
        };

        // プレビュー
        $(document).on('click', '.js_news_preview_btn', function (_event) {
            previewNews(_event);
        });

        $(document).on('change', '.js_access', function (_event) {

            var all = $(_event.target).data('all');
            var value = $(_event.target).val();

            if (all != value) {
                $('.js_access:first').prop('checked', false);
            } else {
                $('.js_access').not(_event.target).prop('checked', false);
            }
        });
    });
})(window, jQuery, window.app);
