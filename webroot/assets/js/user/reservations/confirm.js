(function (window, $, app) {
    var dialog = null;

    var setContinuous = function () {
        app.common.ajax('reservationsSetContinuous', {
            url: app.userCommon.ajaxUrl() + 'reservations/set-continuous',
            type: 'post',
            data: {}
        }).done(function (result) {
            app.common.changeLocation(result.url);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    var viewContinuous = function (key) {
        var query = {
            key: key
        };

        app.common.ajax('reservationsViewContinuous', {
            url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'reservations/view-continuous', query),
            type: 'post',
            data: {}
        }).done(function (result) {
            if (dialog !== null) {
                $(dialog).dialog('close');
            }

            dialog = app.common.dialog(result.html, {
                width: app.common.getScreenWidth(900),
                height: 800,
                classes: {
                    'ui-dialog': 'caution-popup'
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
            url: app.common.mergeUrlQuery(app.userCommon.ajaxUrl() + 'reservations/remove-continuous', query),
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
            url: app.userCommon.ajaxUrl() + 'reservations/remove-all-continuous',
            type: 'post',
            data: {}
        }).done(function (result) {
            app.common.changeLocation(result.url);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    var togglePaymentFieldset = function () {
        if ($('.js_payment_method:checked').val() === $('.js_payment_method_card').val()) {
            $('.js_payment_form').data('payment', true);
            $('.js_payment_fieldset').slideDown();
            $('.js_payment_kyc_fieldset').slideDown();
        } else {
            $('.js_payment_form').data('payment', false);
            $('.js_payment_fieldset').slideUp({
                done: function (animation, jumpedToEnd) {
                    app.common.resetForm($('.js_payment_fieldset'));
                }
            });
            $('.js_payment_kyc_fieldset').slideUp({
                done: function (animation, jumpedToEnd) {
                    app.common.resetForm($('.js_payment_kyc_fieldset'));
                }
            });
        }
    };

    $(function () {
        $(document).on('click.setContinuous', '.js_reservation_set_continuous', function (event) {
            setContinuous();

            event.preventDefault();
        });

        $(document).on('click.viewContinuous', '.js_reservation_view_continuous', function (event) {
            viewContinuous($(event.target).closest('.js_reservation_view_continuous').data('continuous-key'));

            event.preventDefault();
        });

        $(document).on('click.removeContinuous', '.js_reservation_remove_continuous', function (event) {
            removeContinuous($(event.target).closest('.js_reservation_remove_continuous').data('continuous-key'));

            event.preventDefault();
        });

        $(document).on('click.allContinuous', '.js_reservation_remove_all_continuous', function (event) {
            removeAllContinuous();

            event.preventDefault();
        });

        $(document).on('change.paymentMethod', '.js_payment_method', function (event) {
            togglePaymentFieldset();
        });

        if (typeof(continue_trigger) !== 'undefined') {
            continue_trigger();
            axia.check_breakpoints();
        }

        $('.js_payment_fieldset').hide();
        $('.js_payment_fieldset').removeClass('hidden');
        $('.js_payment_kyc_fieldset').hide();
        $('.js_payment_kyc_fieldset').removeClass('hidden');
        togglePaymentFieldset();
    });
})(window, jQuery, window.app);
