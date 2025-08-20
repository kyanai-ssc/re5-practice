// JavaScript Document
//グローバル変数

function calendar_list() {
  var l_main = $('.l-main'),
      header = $('.cmn-header').height(),
      thisTable = $('.table_reserve_list'),
      side_primary = $('#side-primary'),
      schedule_wrap = $('.schedule-header-wrap'),
      calendar_wrap = $('.calendar-wrap'),
      scheduleHeader = schedule_wrap.outerHeight(),
      reservation_filter = $('.schedule-header'),
      reservation_filter_height = reservation_filter.outerHeight(),
      tHeader = thisTable.find('thead'),
      th = tHeader.find('th'),
      tHeaderNum = th.length,
      width_T,
      tableTop,
      scrollTop;

  // position: stickyがブラウザで使えるかチェックするための関数
  function detectSticky() {
    const div = document.createElement('div');
    div.style.position = 'sticky';
    // position: stickyがブラウザで使えればtrue、使えなければfalseを返す
    return div.style.position.indexOf('sticky') !== -1;
  }

  // .stickyが指定されている要素に対してposition: stickyを適用させる関数
  function callStickyState() {
    // position: stickyを適用させたい要素を引数に指定し、
    // StickyStateをnewしてインスタンスを返す
    return new StickyState($('.sticky'));
  }

  //UA判定
  var ua = navigator.userAgent.toLowerCase(),
      isEdge = (ua.indexOf('edge') > -1),
      isFirefox = (ua.indexOf('firefox') > -1),
      isChrome = (ua.indexOf('chrome')> -1);

  //スケジュールテーブルの幅取得
  function scheduleHeaderW() {
    width_T = l_main.outerWidth() - 40;
    schedule_wrap.css({ width: width_T + 2 });
  }

  //thの幅取得
  function tableHeaderW() {
    if (isFirefox) {
      th.css({ width: width_T / tHeaderNum });
    } else {
      th.css({ width: parseInt(width_T / tHeaderNum) });
    }
    tHeader.css({ width: width_T });
  }

  //予約しぼり込みの高さ取得
  function showBtn_fn() {
    return parseInt(schedule_wrap.outerHeight());
  }

  //ブラウザのスクロールトップ位置取得
  function scr_top() {
    return $(window).scrollTop();
  }

  //ブラウザ幅取得
  function win_width() {
    return $(window).innerWidth();
  }

  //tableのスクロールトップ位置取得
  function table_top() {
    var t = calendar_wrap.offset().top;
    return parseInt(t - header);
  }

  function commondesign() {
    //予約しぼり込みの開閉ボタンクリックするごとに高さを再取得
    var showBtnClick = showBtn_fn();
    side_primary.css({ paddingTop: showBtnClick });
    schedule_wrap.find('.showBtn').on('click', function(){
      showBtnClick = showBtn_fn();
      reservation_filter_height = reservation_filter.outerHeight();
      if ($(this).hasClass('opend')) {
        side_primary.css({ paddingTop: showBtnClick + reservation_filter_height});
      } else {
        side_primary.css({ paddingTop: showBtnClick - reservation_filter_height});
      }
    });

    $(window).on('load resize' , function(){
      scheduleHeaderW(); //予約しぼり込み幅リサイズで再取得
      tableHeaderW();
    });

    //ヘッダー追従時の挙動
    $(window).on('load scroll' , function () {
      var win_top = scr_top(),
          tableTop = table_top();
      if (win_top > tableTop) {
        schedule_wrap.css({ top: header }).addClass('fixed');
        tHeader.css({ top: header + schedule_wrap.outerHeight() }).addClass('fixed');
        thisTable.css({marginTop:tHeader.outerHeight()});
        if (isChrome && !isEdge || isFirefox) { tHeader.css({ marginLeft:-1 }); }
        if ($('body').hasClass('.tablet')) { schedule_wrap.css({ marginLeft:-1 }) }
      } else {
        schedule_wrap.css({ top: '' , marginLeft: '' }).removeClass('fixed');
        tHeader.css({ top: '', marginLeft:'' }).removeClass('fixed');
        thisTable.css({marginTop:''});
      }
    });
  }
  commondesign();
}

function cal_list_trigger() {
  calendar_list();
  $(window).trigger('resize');
}