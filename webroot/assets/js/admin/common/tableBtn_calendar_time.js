// JavaScript Document
function calendar_time() {
    //グローバル変数
    var l_main = $('.l-main'),
            thisTable = $('.timeTable-wrap'),
            schedule_wrap = $('.schedule-header-wrap'),
            reservation_filter = $('.schedule-header'),
            calender_wrap = $('.calendar-wrap'),
            calender_list = $('#calender_list'),
            timeTableTime = $('#timeTableTime'),
            time_table = thisTable.find('.time_table'),
            tableH_wrap = $('.timeTableHead'),
            stickyElm = thisTable.find('.stickyTable'),
            stickyElm_height = stickyElm.outerHeight(),
            header = parseInt($('.cmn-header').outerHeight()),
            tableWrap = $('.timeTable-wrap'),
            reservation_filter_height = reservation_filter.outerHeight(),
            width_T = 0,
            emptyCell = $('.emptyCell');
    
    // position: stickyがブラウザで使えるかチェックするための関数
    function detectSticky() {
        const div = document.createElement('div');
        div.style.position = 'sticky';
        // position: stickyがブラウザで使えればtrue、使えなければfalseを返す
        return div.style.position.indexOf('sticky') !== -1;
    }
    //Edge判定
    var ua = navigator.userAgent.toLowerCase();
    var isEdge = (ua.indexOf('edge') > -1);
    var isFirefox = (ua.indexOf('firefox') > -1);
    
    ////////////     テーブルヘッダー追従処理     //////////////

    function scheduleHeaderW() {
        width_T = parseInt(l_main.innerWidth() - 40);
        schedule_wrap.css({ width: width_T });
    }

    function tableHeaderW() {
        width_T = parseInt(l_main.innerWidth()) - 120;
        tableH_wrap.css({ maxWidth: width_T });
        tableWrap.css({ maxWidth: width_T });
        if (!detectSticky() || isEdge) {
            tableH_wrap.css({ maxWidth: width_T + 1 });
            tableWrap.css({ maxWidth: width_T + 1 });
        }
    }

    //横スクロールボタン幅設定
    function scr_btn_width() {
        var table_height = time_table.outerHeight();
        if (table_height <= 440) {
            $('.fixedTable-scroll-btn').css({ height: table_height });
        } else {
            var scrollHeight = $(window).height()/2;
            $('.fixedTable-scroll-btn').css({ height: scrollHeight });
        }
    }

    //予約しぼり込みの高さ取得
    function showBtn_fn() {
        return parseInt(schedule_wrap.outerHeight());
    }

    //ブラウザのスクロールトップ位置取得
    function scr_top() {
        return $(window).scrollTop();
    }

    //tableのスクロールトップ位置取得
    function table_top() {
        return parseInt(calender_wrap.offset().top - header);
    }
    
    //PCの場合
    tableH_wrap.addClass('absolute');

    scr_btn_width();
    
    
  
    emptyCell.css({    width: timeTableTime.outerWidth()    });
  

    //幅リサイズ処理
    function resizeBtn() {
        scheduleHeaderW();
        tableHeaderW();    
        var tableWrapW = tableWrap.outerWidth();
        var tblW = time_table.outerWidth();
        var tblScrlW = parseInt(tblW - tableWrapW);
        if (tblScrlW === 0) {
            $('.fixedTable-arrow').addClass('btn_fixed');
        } else if (tblScrlW >= 1) {
            $('.fixedTable-arrow').removeClass('btn_fixed');
        }
    var HthFirst = time_table.find('thead tr:first-child th:first-child'),
        HthFirstW = HthFirst.outerWidth();
    timeTableTime.css({ width: HthFirstW });
    emptyCell.css({ width: HthFirstW });
    }
    resizeBtn();
    $(window).on('load resize' , function() {
        resizeBtn();
    });
    //予約しぼり込みの開閉ボタンクリックするごとに高さを再取得
    var showBtnClick = showBtn_fn();
    tableH_wrap.css({ top: tableH_wrap.outerHeight() + schedule_wrap.outerHeight() - 4 });
    calender_list.css({ paddingTop: schedule_wrap.outerHeight() });
    timeTableTime.css({ top: showBtnClick + stickyElm_height +1});
  if (timeTableTime.hasClass('type_day')) {
    timeTableTime.css({ top: showBtnClick + stickyElm_height +2});
  }
  if (!detectSticky() || isEdge) {
    timeTableTime.css({ top: showBtnClick + stickyElm_height +2});
  }
    if (isFirefox) {
        tableH_wrap.css({ top: tableH_wrap.outerHeight() + schedule_wrap.outerHeight() - 3 });
    }
    schedule_wrap.find('.showBtn').on('click', function(){
        showBtnClick = showBtn_fn();
        tableH_wrap.css({ top: showBtnClick});
        if ($(this).hasClass('opend')) {
            tableH_wrap.css({ top: showBtnClick + header + reservation_filter_height});
            calender_list.css({ paddingTop: showBtnClick + reservation_filter_height });
            timeTableTime.css({ top: showBtnClick + stickyElm_height + reservation_filter_height });
        } else {
            tableH_wrap.css({ top: showBtnClick + header - reservation_filter_height });
            calender_list.css({ paddingTop: showBtnClick - reservation_filter_height });
            timeTableTime.css({ top: showBtnClick + stickyElm_height - reservation_filter_height });
        }
    });

    
    
    //ヘッダー追従時の挙動
    $(window).on('load scroll' , function(){
        var win_top = scr_top();
        var tableTop = table_top();
        if (win_top > tableTop) {
            var fixedHeight = header + schedule_wrap.outerHeight() - 4;
            if (isFirefox) {
                fixedHeight += 1;
            }
            tableH_wrap.removeClass('absolute').addClass('fixed').css({
                visibility: 'visible',
                top: fixedHeight
            });
            schedule_wrap.css({ top: header }).addClass('fixed');
            emptyCell.removeClass('absolute').addClass('fixed').css({ 
                height: stickyElm_height + 1,
                maxHeight: stickyElm_height + 1,
                top: fixedHeight
            });
        } else {
            schedule_wrap.css({ top: 0 }).removeClass('fixed');
            tableH_wrap.addClass('absolute').removeClass('fixed').css({ visibility: 'hidden' });
            emptyCell.addClass('absolute').removeClass('fixed').css({
                height: stickyElm_height + 1,
                maxHeight: stickyElm_height + 1,
                top: -stickyElm_height -1
            });
            if (!detectSticky() || isEdge) {
                emptyCell.css({ 
                    height: stickyElm_height + 1,
                    maxHeight: stickyElm_height + 1,
                    top: -stickyElm_height - 1
                });
            }
        }
    });
    //横スクロール発生しない際のth,td幅
    function th_w_fun() {
        var thisTh = thisTable.find('.timeTableHead thead tr:nth-child(2)').find('th:not(:first-child)'),
                width_T = parseInt(l_main.outerWidth()) - 120,
                thNum = thisTh.length,
                thW = parseInt((width_T - thisTable.find('.column.time').outerWidth() ) / thNum);
        thisTable.find('thead tr:nth-child(2)').find('th:not(:first-child)').css({
            width: thW,
            maxWidth: thW,
            minWidth: thW
        });
        thisTable.find('.line').css({
            width: thW,
            maxWidth: thW,
            minWidth: thW
        });
    }
    if (thisTable.find('.type_day').length) {
        if ($('.fixedTable-arrow').hasClass('btn_fixed')) {
            th_w_fun();
            $(window).on('load resize' , function() {
                th_w_fun();
            });
        }
    }
}

//初期読み込み時の関数
function cal_time_trigger() {
    calendar_time();
    cal_time_scroll();
    $(window).trigger('resize');
}



//カレンダー再読み込み後のテーブル幅再取得
function cal_time_scroll() {
    var thisTable = $('.timeTable-wrap'),
            time_table = thisTable.find('.time_table'),
            tableWrap = $('.timeTable-wrap'),
            tableH_wrap = $('.timeTableHead'),
            loadCount = parseInt(tableH_wrap.find('table').css('margin-left')) * -1;
    
    ////////////     横スクロールボタン     //////////////
    //コマ数・送りの設定
    var feed = 15,
            frame = 15;
    
    //入力フォームにフォーカス時は横スクロール無効
    $('input[type="text"]')
        .focusin(function() {
        feed = 0;
        frame = 0;
    })
        .focusout(function() {
        feed = 15;
        frame = 15;
    });
    
    //横幅取得・設定
    var tblWrapW = tableWrap.outerWidth(),
            tblW = time_table.outerWidth(),
            tblScrlW = parseInt(tblW - tblWrapW),
            tableLimit =  time_table.outerWidth() - tableH_wrap.outerWidth();
    
    
    if (tblScrlW < 1 ) {
        $('#right-button').css({
            'opacity': 0.4,
            'pointer-events': 'none'
        });
    }
    
    //カウント初期値
    if (loadCount > 1) {
        var count = loadCount;
    } else {
        var count = 1;
    }
    
    //clearinterval対策
    var setItv = '';

    //スクロール値取得
    tableWrap.on('load scroll', function() {
        count = $(this).scrollLeft();
        tableH_wrap.find('table').css({ marginLeft: - count });
    });
    
    $('#left-button').css('opacity', '0.4');
    //移動設定
    var countUp = function() {
        if (checkBA === 'a') {
            var count2 = count + feed;
            tableWrap.scrollLeft(count2);
            if ((tableLimit - count) < feed) {
                clearInterval(setItv.shift()); 
            }
        } else {
            var count2 = count - feed;
            tableWrap.scrollLeft(count2);
            if (count < 1) {
                clearInterval(setItv.shift()); 
            }
        }
        
        if (!$('.fixedTable-arrow').hasClass('btn_fixed')) {
            tableLimit =  time_table.outerWidth() - tableH_wrap.outerWidth();
            if (count < 1) {
                $('#left-button').css({
                    'opacity': 0.4,
                    'pointer-events': 'none'
                });
                $('#right-button').css({
                    'opacity': 1,
                    'pointer-events': 'auto'
                });
            } else if ((tableLimit - count) < feed) {
                $('#left-button').css({
                    'opacity': 1,
                    'pointer-events': 'auto'
                });
                $('#right-button').css({
                    'opacity': 0.4,
                    'pointer-events': 'none'
                });
            } else {
                $('#left-button').css({
                    'opacity': 1,
                    'pointer-events': 'auto'
                });
                $('#right-button').css({
                    'opacity': 1,
                    'pointer-events': 'auto'
                });         
            }
        } else {
            $('#left-button').css({
                'opacity': '',
                'pointer-events': ''
            });
            $('#right-button').css({
                'opacity': '',
                'pointer-events': ''
            }); 
        }
    };
    
    if (loadCount > 1) {
    $('#left-button').css({
      'opacity': 1,
      'pointer-events': 'auto'
    });
    $('#right-button').css({
      'opacity': 1,
      'pointer-events': 'auto'
    });         
  }
    
    //インターバルの設定
    setItv = new Array();
    function setItvF() {
        setItv.push(setInterval(countUp, frame));
    }
    
    //進む
    $('#right-button').mousedown(function() {
        checkBA = 'a';
        if (count < tblScrlW) {
            setItvF();
            if (setItv.length > 1) {
                clearInterval(setItv.shift());
            }
        }
    }).mouseup(function() {
        clearInterval(setItv.shift());
    });
    
    //テンキー→で横スクロール
    $('body').addClass('windows');
    $(window).on('keydown', function(e) {
        if (e.keyCode === 39) {
            checkBA = 'a';
            if (count < tblScrlW) {
                setItvF();
                if (setItv.length > 1) {
                    clearInterval(setItv.shift());
                }
            }
        }
    }).on('keyup', function(e) {
        if(e.keyCode === 39) {
            clearInterval(setItv.shift());
        }
    });
    
    //iPad時→ボタンタッチで進む
    $('#right-button').on('touchstart' , function() {
        checkBA = 'a';
        if (count < tblScrlW) {
            setItvF();
            if (setItv.length > 1) {
                clearInterval(setItv.shift());
            }
        }
    }).on('touchend' , function() {
        clearInterval(setItv.shift());
    });
    
    //戻る
    $('#left-button').mousedown(function() {
        checkBA = 'b';
        if (1 < count) {
            setItvF();
            if (setItv.length > 1) {
                clearInterval(setItv.shift());
            }
        }
    }).mouseup(function() {
        clearInterval(setItv.shift());
    });
    //テンキー←で横スクロール
    $(window).on('keydown', function(e) {
        if(e.keyCode === 37) {
            checkBA = 'b';
            if (0 < count) {
                setItvF();
                if (setItv.length > 1) {
                    clearInterval(setItv.shift());
                }
            }
        }
    }).on('keyup', function(e) {
        if(e.keyCode === 37) {
            clearInterval(setItv);
        }
    });

    //iPad時←ボタンタッチで戻る
    $('#left-button').on('touchstart' , function() {
        checkBA = 'b';
        if (0 < count) {
            setItvF();
            if (setItv.length > 1) {
                clearInterval(setItv.shift());
            }
        }
    }).on('touchend' , function() {
        clearInterval(setItv.shift());
    });
    
}

//再読み込みした際の関数トリガー
function cal_time_reload_trigger() {
    calendar_time();
    cal_time_scroll();
    $(window).trigger('resize');
    $(window).trigger('scroll');
}
