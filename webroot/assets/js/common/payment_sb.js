(function (window, $, app) {
    let paymentDeffered = null;
    let tds2infotokenValueList = [];
    let token = [];

    // 決済
    app.common.payment = {
        // トークン取得
        getToken: function (setting, data) {
            const deffered = new $.Deferred();

            if (typeof(com_sbps_system) === 'undefined') {
                setTimeout(function () {
                    deffered.resolve({
                        data: {},
                        token: []
                    });
                }, 0);
            } else if (
                typeof(data.card_no) === 'string' && data.card_no !== ''
                && !(new RegExp('^[0-9]{3,4}$')).test(String(data.security_code))
            ) {
                setTimeout(function () {
                    deffered.reject({
                        data: {},
                        error: 'error_security_code'
                    });
                }, 0);
            } else if (data.three_d_secure_flg) {
                if (typeof(com_sbps_system_tds2) === 'undefined') {
                    setTimeout(function () {
                        deffered.resolve({
                            data: {},
                            token: []
                        });
                    }, 0);
                } else if (data.name === '') {
                    setTimeout(function () {
                        deffered.reject({
                            data: {},
                            error: 'error_name_sb_required'
                        });
                    }, 0);
                } else if (data.email_address === '' && data.phone_number === '' ) {
                    setTimeout(function () {
                        deffered.reject({
                            data: {},
                            error: 'error_email_address_sb_or_phone_number_sb_required'
                        });
                    }, 0);
                } else if (
                    (typeof(data.email_address) === 'string' && data.email_address !== '' && !data.email_address.match(/^[!-~]+@[!-~]+$/))
                    || (typeof(data.phone_number) === 'string' && data.phone_number !== '' && !data.phone_number.match(/^\d{1,20}$/))
                ) {
                    let error = [];
                    if (typeof(data.email_address) === 'string' && data.email_address !== '' && !data.email_address.match(/^[!-~]+@[!-~]+$/)) {
                        error.push('error_email_address_sb_format');
                    }
                    if (typeof(data.phone_number) === 'string' && data.phone_number !== '' && !data.phone_number.match(/^\d{1,20}$/)) {
                        error.push('error_phone_number_sb_format');
                    }
                    setTimeout(function () {
                        deffered.reject({
                            data: {},
                            error: error
                        });
                    }, 0);
                } else {
                    paymentDeffered = deffered;
                    tds2infotokenValueList = data;
                    tds2infotokenValueList.merchant_id = setting.merchant_id;
                    tds2infotokenValueList.service_id = setting.service_id;

                    com_sbps_system.generateToken({
                        merchantId: setting.merchant_id,
                        serviceId: setting.service_id,
                        ccNumber: data.card_no,
                        ccExpiration: '20' + data.expire_year + '' + data.expire_month, // YYYYMM
                        securityCode: data.security_code
                    }, window.callbackPaymentGetToken);
                }
            } else {
                paymentDeffered = deffered;
                com_sbps_system.generateToken({
                    merchantId: setting.merchant_id,
                    serviceId: setting.service_id,
                    ccNumber: data.card_no,
                    ccExpiration: '20' + data.expire_year + '' + data.expire_month, // YYYYMM
                    securityCode: data.security_code
                }, window.callbackPaymentGetToken);
            }

            return deffered.promise();
        }
    };

    window.callbackPaymentGetToken = function (data)
    {
        const deffered = paymentDeffered;

        if (data.result === 'OK') {
            if (tds2infotokenValueList.three_d_secure_flg) {
                // カード名義人 全角を半角に変換し、全角スペースを半角スペースに変換する
                const nameHalfWidthText = tds2infotokenValueList.name.replace(/[！-～]/g, function(char) {
                    return String.fromCharCode(char.charCodeAt(0) - 0xFEE0);
                }).replace(/　/g, " ");
                // スペースで区切る
                const name = nameHalfWidthText.split(" ");
                // 姓名揃っていなければエラー
                if (!Array.isArray(name) || name.length !== 2) {
                    setTimeout(function () {
                        deffered.reject({
                            data: {},
                            error: 'error_name_sb_required'
                        });
                    }, 0);

                    return deffered.promise();
                }
                // スペースのみはエラー
                // 半角英字以外があればエラー
                let firstName = name[0].trim();
                let lastName = name[1].trim();
                let regex = new RegExp('^[a-z]+$', 'i');
                if (!regex.test(lastName) || !regex.test(firstName)) {
                    setTimeout(function () {
                        deffered.reject({
                            data: {},
                            error: 'error_name_sb_invalid'
                        });
                    }, 0);

                    return deffered.promise();
                }

                token.push({
                    token: data.tokenResponse.token,
                    token_key: data.tokenResponse.tokenKey
                });

                let sendData = {
                    merchantId: tds2infotokenValueList.merchant_id,
                    serviceId: tds2infotokenValueList.service_id,
                    billingLastName: lastName,
                    billingFirstName: firstName
                }
                if (tds2infotokenValueList.phone_number !== '') {
                    sendData.billingPhone = tds2infotokenValueList.phone_number;
                }
                if (tds2infotokenValueList.email_address !== '') {
                    sendData.email = tds2infotokenValueList.email_address;
                }

                com_sbps_system_tds2.generateToken(sendData, window.callbackPaymentGetTds2infotoken);
            } else {
                token.push({
                    token: data.tokenResponse.token,
                    token_key: data.tokenResponse.tokenKey
                });

                deffered.resolve({
                    data: data,
                    token: token
                });

                // リセット
                paymentDeffered = null;
                token = [];
            }
        } else {
            deffered.reject({
                data: data,
                error: 'code_' + data.errorCode
            });

            // リセット
            paymentDeffered = null;
            token = [];
        }
    };

    window.callbackPaymentGetTds2infotoken = function (data)
    {
        const deffered = paymentDeffered;

        if (data.result === 'OK') {
            token[0].tds2info_token = data.tokenResponse.tds2infoToken;
            token[0].tds2info_token_key = data.tokenResponse.tds2infoTokenKey;

            deffered.resolve({
                data: data,
                token: token
            });
        } else {
            deffered.reject({
                data: data,
                error: 'code_tds2_' + data.errorCode
            });
        }

        // リセット
        paymentDeffered = null;
        token = [];
    };
})(window, jQuery, window.app);
