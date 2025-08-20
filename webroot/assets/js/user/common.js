(function (window, $, app) {
    app.userCommon = {
        // ベースURL
        baseUrl: function () {
            return '/';
        },

        // 非同期通信URL
        ajaxUrl: function () {
            return app.userCommon.baseUrl() + 'ajax/';
        },

        // 最下部スクロールの判定
        isScrollBottom: function (offset) {
            if (typeof (offset) === 'undefined') {
                offset = 0;
            }
            if ($('.js_footer').length > 0) {
                offset -= $('.js_footer').outerHeight();
            }

            return app.common.isScrollBottom(offset);
        }
    };

    $(function () {
        //デバイス判定
        var ua = navigator.userAgent,
            bodyClass = document.body.classList;

        if (ua.indexOf('iPhone') > 0 || ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0) {
            // スマートフォン
            bodyClass.add('sp');

            if (ua.indexOf('iPhone') > 0) bodyClass.add('iphone');   // iPhone
            if (ua.indexOf('Android') > 0) bodyClass.add('android'); // Android

        } else if (ua.indexOf('iPad') > 0 || ua.indexOf('Android') > 0) {
            // タブレット
            bodyClass.add('tablet');

            if (ua.indexOf('iPad') > 0) bodyClass.add('ipad');       // iPad
            if (ua.indexOf('Android') > 0) bodyClass.add('android'); // Android
        } else {
            // PC用コード
            bodyClass.add('pc');
        }

        //検索パネルの開閉
        $(document).on('click', '#showBtn', function () {
            var target = $(this).next();
            target.toggle();
            $(this).toggleClass('opend');
        });

        //デバイス判定
        var isPC = $('body').hasClass('pc');

        //ハンバーガーメニュー開閉
        var body = $('body'),
            bodyH = body.outerHeight(),
            menu = $('#sp-menu'),
            layer = $('<div id="sp-layer"></div>');
        $('header.cmn-header').before(layer);
        $('#sp-layer').hide().css({height: bodyH});
        menu.css({height: bodyH, width: 0});

        $('.btn-sp-menu, #sp-layer').on('click', function () {

            $('#sp-layer').stop(true, true).fadeToggle(200);
            body.toggleClass('menu_open');

            if (body.hasClass('menu_open')) {
                menu.animate({width: 90 + 'vw'}, 200);
            } else {
                menu.animate({width: 0}, 200);
            }
        });

        //カラーチップの開閉
        $(document).on('click', '.btn-colorTip', function () {
            $(this).parent('.colorTip').toggleClass('is-open');
        });

        //ダイアログの閉じるボタン
        $(document).on("click", ".ui-dialog .btn-close", function () {
            $(this).closest(".ui-dialog-content").dialog("close");
        });

        //モーダル時背景クリックで閉じる
        /*$(document).on("click", ".ui-widget-overlay", function(){
          $(this).prev().find(".ui-widget-content").dialog("close");
        });*/

        //ヘルプボタン表示
        $('.helpBtnPC').on('click', function () {
            $('.help-area.is-pc').toggle();
        });
        $('.helpBtnSP').on('click', function () {
            $('.help-area.is-sp').toggle();
        });

        //トップへ戻るボタン
        var $topBtn = $('.totopBtn');
        $topBtn.hide();
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $topBtn.fadeIn();
            } else {
                $topBtn.fadeOut();
            }
        });
        $('.totopBtn').on('click', function () {
            $('html, body').animate({
                scrollTop: 0
            }, 500, 'swing');
            return false;
        });

        $(window).on('scroll resize', function () {
            var scroll = $(window).scrollTop() + $(window).height();

            //戻ボタン固定フッター前で解除
            if ($('.cmn-footer').length) {
                var footer = $('.cmn-footer').offset().top;
                if (scroll > footer) {
                    $topBtn.addClass('is-end');
                } else {
                    $topBtn.removeClass('is-end');
                }
            }
        });

        //SP、TB時inputにreadonly付与
        if (isPC) {
            $('.js-datepicker').attr('readonly', false);
            $('.js-datepicker-time').attr('readonly', false);
            $('.js-timepicker').attr('readonly', false);
            $('.js-datepicker-type-range-start').attr('readonly', false);
            $('.js-datepicker-type-range-end').attr('readonly', false);
        } else {
            $('.js-datepicker').attr('readonly', true);
            $('.js-datepicker-time').attr('readonly', true);
            $('.js-timepicker').attr('readonly', true);
            $('.js-datepicker-type-range-start').attr('readonly', true);
            $('.js-datepicker-type-range-end').attr('readonly', true);
        }
        //フォーカス外れた後に全角を半角に変換
        var charactersChange = function (ele) {
            var val = ele.val();
            var han = val.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function (s) {
                return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
            });

            if (val.match(/[Ａ-Ｚａ-ｚ０-９]/g)) {
                $(ele).val(han);
            }
        };
        $(".js-characters-change").blur(function () {
            charactersChange($(this));
        });

        //タッチパネルディスプレイ判断
        var isTouchDevice = function () {
            return ('ontouchstart' in window || navigator.msPointerEnabled) ? true : false;
        };

        //ツールチップ
        function tooltip_event() {
            if (isTouchDevice() === false) {
                $(document).tooltip({
                    tooltipClass: 'ui-tooltip',
                    position: {
                        my: 'center top',
                        at: 'center bottom+10',
                        using: function (position, feedback) {
                            $(this).css(position);
                            $('<div>')
                                .addClass('arrow')
                                .addClass(feedback.vertical)
                                .addClass(feedback.horizontal)
                                .appendTo(this);
                        }
                    }
                });
            }
        }

        tooltip_event();


        //ブレイクポイント設定
        window.PCw = 1025;
        window.TBw = 768;
        window.SPw = 350;
        window.axia = new Axia({
            breakpoints: [window.SPw, window.TBw, window.PCw]
        });

        // ラベル変更時
        $(document).on('change.changeLabelSelect', '.js_change_label_select', function (event) {
            var element = $(event.target).closest('.js_label_select');

            app.common.changeLabelSelect(
                element,
                element.find('.js_label_select_type').val(),
                $(event.target).val(),
                element.find('.js_label_select_exclude_id').val(),
                app.userCommon.ajaxUrl()
            );

            event.preventDefault();
        });
    });
})(window, jQuery, window.app);
