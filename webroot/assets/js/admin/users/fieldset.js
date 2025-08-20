(function (window, $, app) {
    // パラメータを変更
    var changeParameter = function (parameter) {
        var form = $('.js_user_form');
        var query = {};

        $('.js_user_parameter').each(function (index, element) {
            query[$(element).data('name')] = $(element).val();
        });

        if (typeof (parameter) !== 'object') {
            parameter = {};
        }
        query = $.extend(query, parameter);

        app.common.ajax('usersChangeForm', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'users/change-form', query),
            type: 'post',
            data: form.serialize()
        }).done(function (result) {
            $('.js_user_form').replaceWith(result.html);
            app.common.useDatePicker();
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    $(function () {
        // 権限変更
        $(document).on('change.changeUserAuthorityId', '.js_user_authority_id', function (event) {
            if ($(event.target).val() !== '') {
                changeParameter({
                    user_authority_id: $(event.target).val(),
                });
            }
        });
    });
})(window, jQuery, window.app);
