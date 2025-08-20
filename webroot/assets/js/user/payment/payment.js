(function (window, $, app) {
    var telInput = null;

    const executePayment = function (form, telInput) {
        const settingElement = $(form).find('.js_payment_setting');
        const setting = settingElement.data('setting');
        setting.token_number = settingElement.data('token-number');

        $(form).find('.js_payment_error').addClass('hidden');

        let data = {
            card_no: $('.js_payment_card_no').val(),
            expire_year: $('.js_payment_expire_year').val(),
            expire_month: $('.js_payment_expire_month').val(),
            security_code: $('.js_payment_security_code').val()
        }
        if ($(form).find('.js_payment_name').length > 0) {
            data.name = $('.js_payment_name').val();
            data.three_d_secure_flg = true;
        } else if ($(form).find('.js_payment_name_sb').length > 0) {
            data.name = $('.js_payment_name_sb').val();
            data.email_address = $('.js_payment_email_address_sb').val();
            data.phone_number = $('.js_payment_phone_number_sb').val();
            data.three_d_secure_flg = true;
        }

        app.common.payment.getToken(setting, data, form).done(function (result) {
            const tokenContainer = $('<div class="hidden"></div>');
            const tokenElement = $('<input type="hidden"/>');
            $.each(result.token, function (index, token) {
                $.each(token, function (key, value) {
                    const element = tokenElement.clone();
                    element.prop('name', settingElement.data('token-name') + '[value_' + index + ']' + '[' + key + ']');
                    element.val(value);
                    tokenContainer.append(element);
                });
            });
            $(form).append(tokenContainer);

            $(form).find('.js_payment_input').each(function (index, element) {
                $(element).prop('disabled', true);
            });
            app.common.resetForm('.js_payment_fieldset');

            if (typeof (telInput) === 'object') {
                $('.js_payment_phone_number').val(telInput.getNumber(intlTelInput.utils.numberFormat.INTERNATIONAL));
            }

            $(form).off('submit');
            $(form).on('submit', function (event) {
                app.common.submitOnce(event.target);
                event.stopPropagation();
            });
            $(form).submit();
        }).fail(function (result) {
            let errorType = 'other';
            if ('error' in result && Array.isArray(result.error)) {
                result.error.forEach(function (value, index) {
                    if ($(form).find('.js_payment_result_' + value).length > 0) {
                        errorType = $(form).find('.js_payment_result_' + value).val();
                    }

                    if ($(form).find('.js_payment_error_' + errorType).hasClass('hidden')) {
                        $(form).find('.js_payment_error_' + errorType).removeClass('hidden');
                    }
                });
            } else {
                if ('error' in result && $(form).find('.js_payment_result_' + result.error).length > 0) {
                    errorType = $(form).find('.js_payment_result_' + result.error).val();
                }
                $(form).find('.js_payment_error_' + errorType).removeClass('hidden');
            }

            $('html,body').animate({
                scrollTop: $(form).find('.js_payment_fieldset').offset().top
            }, 500, 'swing');

            $(form).off('submit');
        });
    };

    $(function () {
        if ($('.js_payment_form').find('.js_payment_phone_number').length > 0) {
            const input = document.querySelector('.js_payment_phone_number');
            var telInput = window.intlTelInput(input, {
                initialCountry: 'jp',
                nationalMode: true,
                loadUtils: () => import('/assets/js/vendor/intl-tel-input/utils.js'),
            });
        }

        $(document).on('submit.payment', '.js_payment_form', function (event) {
            if ($('.js_payment_form').data('payment')) {
                // reCAPTCHA
                app.recaptcha.setToken(event).done(function () {
                    executePayment($(event.target), telInput);
                });

                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
})(window, jQuery, window.app);
