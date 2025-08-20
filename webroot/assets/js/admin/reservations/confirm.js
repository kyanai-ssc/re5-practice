(function (window, $, app) {
    var dialog = null;

    var setContinuous = function () {
        app.common.ajax('reservationsSetContinuous', {
            url: app.adminCommon.ajaxUrl() + 'reservations/set-continuous',
            type: 'post',
            data: {}
        }).done(function (result) {
            app.common.changeLocation(result.url);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    var viewContinuous = function (key, title) {
        var query = {
            key: key
        };

        app.common.ajax('reservationsViewContinuous', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/view-continuous', query),
            type: 'post',
            data: {}
        }).done(function (result) {
            if (dialog !== null) {
                $(dialog).dialog('close');
            }

            dialog = app.common.dialog(result.html, {
                title: title,
                width:
                    app.common.getScreenWidth(900),
                height:
                    800,
                classes: {
                    'ui-dialog': 'tool-popup'
                },
            });
            dialog.on('dialogclose', function (event, ui) {
                dialog = null;
            });
            dialog.find('.js_reservation_accordion').each(function (index, element) {
                app.common.accordion(element);
            });
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    var removeContinuous = function (key) {
        var query = {
            key: key,
            current_key: $('.js_reservation_current_continuous_key').val()
        };

        app.common.ajax('reservationsRemoveContinuous', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/remove-continuous', query),
            type: 'post',
            data: {}
        }).done(function (result) {
            app.common.changeLocation(result.url);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    var removeAllContinuous = function (key) {
        app.common.ajax('reservationsRemoveAllContinuous', {
            url: app.adminCommon.ajaxUrl() + 'reservations/remove-all-continuous',
            type: 'post',
            data: {}
        }).done(function (result) {
            app.common.changeLocation(result.url);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    $(function () {
        $(document).on('click.setContinuous', '.js_reservation_set_continuous', function (event) {
            setContinuous();

            event.preventDefault();
        });

        $(document).on('click.viewContinuous', '.js_reservation_view_continuous', function (event) {
            viewContinuous($(event.target).closest('.js_reservation_view_continuous').data('continuous-key'), $(event.target).closest('.js_reservation_view_continuous').data('title'));

            event.preventDefault();
        });

        $(document).on('click.removeContinuous', '.js_reservation_remove_continuous', function (event) {
            removeContinuous($(event.target).closest('.js_reservation_remove_continuous').data('continuous-key'));

            event.preventDefault();
        });

        $(document).on('click.removeAllContinuous', '.js_reservation_remove_all_continuous', function (event) {
            removeAllContinuous();

            event.preventDefault();
        });

        $('.js_reservation_accordion').each(function (index, element) {
            app.common.accordion(element);
        });
    });
})(window, jQuery, window.app);
