(function (window, $, app) {
    app.recaptcha = {
        // reCAPTCHAトークン取得
        getRecaptchaToken:function (siteKey, action) {
            const deffered = new $.Deferred();

            grecaptcha.ready(function () {
                try {
                    grecaptcha.execute(siteKey, {
                        action: action
                    }).then(function (token) {
                        deffered.resolve(token);
                    });
                } catch (e) {
                    deffered.reject(e);
                }
            });

            return deffered.promise();
        },

        submit:function (event) {
            $(event.target).off('submit');
            $(event.target).on('submit', function (event2) {
                app.common.submitOnce(event2.target);
                event2.stopPropagation();
            });
            $(event.target).submit();
        },

        setToken:function (event) {
            const deffered = new $.Deferred();
            if ($(event.target).hasClass('js_recaptcha_form')) {
                app.recaptcha.getRecaptchaToken(
                    $(event.target).data('recaptcha-site-key'),
                    $(event.target).data('recaptcha-action')
                ).done(function (token) {
                    $('.js_recaptcha_token').val(token);
                    deffered.resolve();
                }).fail(function () {
                    $('.js_recaptcha_token').val('');
                    deffered.resolve();
                })

                event.preventDefault();
                event.stopPropagation();
            } else {
                deffered.resolve();
            }
            return deffered.promise();
        },
    };

    $(function () {
        $(document).on('submit.recaptcha', '.js_recaptcha_form', function (event) {
            // カード決済でない場合
            if ($('.js_payment_method').length === 0 || $('.js_payment_method:checked').val() !== $('.js_payment_method_card').val()) {
                app.recaptcha.setToken(event).done(function () {
                    app.recaptcha.submit(event);
                });
            }
        });
    });
})(window, jQuery, window.app);
