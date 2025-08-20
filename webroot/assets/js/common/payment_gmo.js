(function (window, $, app) {
    let paymentDeffered = null;

    // 決済
    app.common.payment = {
        // トークン取得
        getToken: function (setting, data) {
            const deffered = new $.Deferred();
            let card = {
                cardno: data.card_no,
                expire: data.expire_year + '' + data.expire_month,
                securitycode: data.security_code,
                tokennumber: setting.token_number
            }

            if (data.three_d_secure_flg && data.name == '') {
                setTimeout(function () {
                    deffered.reject({
                        data: {},
                        error: 'error_name_required'
                    });
                }, 0);
            } else if (typeof(Multipayment) !== 'undefined') {
                Multipayment.init(setting.shop_id);

                if (data.three_d_secure_flg) {
                    card.holdername = data.name;
                }

                paymentDeffered = deffered;
                Multipayment.getToken(card, window.callbackPaymentGetToken);
            } else {
                setTimeout(function () {
                    deffered.resolve({
                        data: {},
                        token: []
                    });
                }, 0);
            }

            return deffered.promise();
        }
    };

    window.callbackPaymentGetToken = function (data)
    {
        const deffered = paymentDeffered;
        paymentDeffered = null;

        const token = [];
        if (data.resultCode === '000') {
            if (data.tokenObject.isSecurityCodeSet) {
                if ($.isArray(data.tokenObject.token)) {
                    $.each(data.tokenObject.token, function (key, value) {
                        token.push({
                            token: value
                        });
                    });
                } else {
                    token.push({
                        token: data.tokenObject.token
                    });
                }

                deffered.resolve({
                    data: data,
                    token: token
                });
            } else {
                deffered.reject({
                    data: data,
                    error: 'error_security_code_required'
                });
            }
        } else {
            deffered.reject({
                data: data,
                error: 'code_' + data.resultCode
            });
        }
    };
})(window, jQuery, window.app);
