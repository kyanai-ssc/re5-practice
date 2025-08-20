(function (window, $, app) {
    $(window).on('load', function(){
        if ($('.js_image_slider').length) {
            var defOption = {
                auto: false,
                infiniteLoop: true,
                pager: true,
                touchEnabled: true,
                slideMargin: 10
            };
            var slider = $('.js_image_slider').bxSlider(defOption);
            if (slider.getSlideCount() < 2) {
                defOption['infiniteLoop'] = false;
                defOption['pager'] = false;
                defOption['touchEnabled'] = false;
                slider.reloadSlider(defOption);
            }
            
        }
    });
})(window, jQuery, window.app);
