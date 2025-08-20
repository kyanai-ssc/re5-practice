(function (window, $, app) {
    var addVideoMeeting = function (reservationId) {
        var deffered = new $.Deferred();

        app.common.ajax('reservationsAddVideoMeeting', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/add-video-meeting', {
                id: reservationId
            }),
            type: 'post',
            data: {}
        }).done(function (result) {
            deffered.resolve(result);
        }).fail(function (error) {
            app.common.errorDialog(error);
            deffered.reject(error);
        });

        return deffered.promise();
    };

    var deleteVideoMeeting = function (reservationId) {
        var deffered = new $.Deferred();

        app.common.ajax('reservationsDeleteVideoMeeting', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/delete-video-meeting', {
                id: reservationId
            }),
            type: 'post',
            data: {}
        }).done(function (result) {
            deffered.resolve(result);
        }).fail(function (error) {
            app.common.errorDialog(error);
            deffered.reject(error);
        });

        return deffered.promise();
    };

    var addSmartLock = function (reservationId) {
        var deffered = new $.Deferred();

        app.common.ajax('reservationsSmartLock', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/add-smart-lock', {
                id: reservationId
            }),
            type: 'post',
            data: {}
        }).done(function (result) {
            deffered.resolve(result);
        }).fail(function (error) {
            app.common.errorDialog(error);
            deffered.reject(error);
        });

        return deffered.promise();
    };

    // 表示項目リロード
    var reloadReservationsForm = function () {
        var query = {};

        $('.js_reservation_parameter').each(function (index, element) {
            query[$(element).data('name')] = $(element).val();
        });

        app.common.ajax('reservationsChangeForm', {
            url: app.common.mergeUrlQuery(app.adminCommon.ajaxUrl() + 'reservations/change-form', query),
            type: 'post',
            data: $('.js_reservation_form').serialize()
        }).done(function (result) {
            $('.js_reservation_form').replaceWith(result.html);
        }).fail(function (error) {
            app.common.errorDialog(error);
        });
    };

    $(function () {
        $(document).on('submit.addVideoMeeting', '.js_add_video_metting', function (event) {
            addVideoMeeting($(event.target).data('reservation-id')).done(function (result) {
                let url = result.url;
                if ($('.js_search_payment_expired_query').length > 0) {
                    url = app.common.mergeUrlQuery(result.url, {
                        search_payment_expired: 1
                    });
                }
                app.common.changeLocation(url);
            });

            event.preventDefault();
        });

        $(document).on('submit.deleteVideoMeeting', '.js_delete_video_metting', function (event) {
            deleteVideoMeeting($(event.target).data('reservation-id')).done(function (result) {
                let url = result.url;
                if ($('.js_search_payment_expired_query').length > 0) {
                    url = app.common.mergeUrlQuery(result.url, {
                        search_payment_expired: 1
                    });
                }
                app.common.changeLocation(url);
            });

            event.preventDefault();
        } );

        // スマートロック再連携
        $(document).on('submit.addSmartLock', '.js_add_smart_lock', function (event) {
            addSmartLock($(event.target).data('reservation-id')).done(function (result) {
                let url = result.url;
                if ($('.js_search_smart_lock_unlinked_query').length > 0) {
                    url = app.common.mergeUrlQuery(result.url, {
                        search_smart_lock_unlinked: 1
                    });
                }
                app.common.changeLocation(url);
            });

            event.preventDefault();
        });

        $('.js_reservation_accordion').each(function (index, element) {
            app.common.accordion(element);
        });

        // 項目変更
        $(document).on('adminSearchItemsEditFinish', function (event) {
            reloadReservationsForm();
        });
    });
})(window, jQuery, window.app);
