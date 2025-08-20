(function (window, $, app) {
    var getVideoMeetingType = function () {
        var type = null;
        if ($('.js_video_meeting_type_radio').length > 0) {
            if ($('.js_video_meeting_type_radio:checked').length > 0) {
                type = $('.js_video_meeting_type_radio:checked').val();
            }
        } else {
            type = $('.js_video_meeting_type_hidden').val();
        }

        return type;
    };

    var setVideoMeetingType = function () {
        $('.js_type_container').addClass('hidden');
        $('.js_type_container_' + getVideoMeetingType()).removeClass('hidden');

        setZoomConnectType();
    };

    var setZoomConnectType = function () {
        if (getVideoMeetingType() == $('.js_video_meeting_type_row').data('type-zoom')) {
            $('.js_for_zoom_connect_type').addClass('hidden');
            $('.js_for_zoom_connect_type_' + $('.js_zoom_connect_type:checked').val()).removeClass('hidden');
        }
    };

    $(function () {
        $(document).on('change.type', '.js_video_meeting_type_radio', function () {
            setVideoMeetingType();
        });

        $(document).on('change.type', '.js_zoom_connect_type', function () {
            setZoomConnectType();
        });

        setVideoMeetingType();
    });
})(window, jQuery, window.app);
