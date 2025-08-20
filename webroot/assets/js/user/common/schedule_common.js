// JavaScript Document

//「予約状況をみる」追従
//SPの場合
function searchBtnFixedSP() {
  if ($('#search-group-wrap').length) {
    function btn_scroll() {
      function searchBottom_fn() {
        return parseInt($('#search-group-wrap').outerHeight() + ($('.btn-group').outerHeight())*2 + 40);
      }
      var searchBottom = searchBottom_fn();
      $(window).on('load scroll resize' , function() {
        var scroll = $(window).scrollTop() + $(window).height();
        var search = $('#search-group-wrap').offset().top;
        if ((scroll > searchBottom) || (scroll < search)) {
          $('.btn-group').css({ position: 'relative' });
          $('.search-group-wrap').css({ paddingBottom: '' });
        } else {
          $('.btn-group').css({ position: 'fixed' });
          $('.search-group-wrap').css({ paddingBottom: $('.btn-group').outerHeight() });
        }
      });
    }
    btn_scroll();
    $(window).on('load resize' , function() {
      btn_scroll();
    });
  }
}
//PCTBの場合
function searchBtnFixedPCTB() {
  $(window).on('load resize' , function() {
    var scroll = 0;
    var searchBottom = 0;
    $(window).on('load scroll' , function() {
      var scroll = 0;
      var search = 0;
      if ((scroll > searchBottom) || (scroll < search)) {
        $('.btn-group').css({ position: '' });
        $('.search-group-wrap').css({ paddingBottom: '' });
      } else {
        $('.btn-group').css({ position: '' });
        $('.search-group-wrap').css({ paddingBottom: '' });
      }
    });
    $('.btn-group').css({ position: '' });
    $('.search-group-wrap').css({ paddingBottom: '' });
  });
}

//「予約状況をみる」幅取得
function serchBtnWidth_PCTB() {
  $(window).on('load resize' , function() {
    $('.btn-group').css({ width: '' });
  });
}
function serchBtnWidth_sp() {
  var searchW = $('.ttl-sec').innerWidth();
  $('.btn-group').css({ width: searchW });
  $(window).on('load resize' , function() {
    var searchW = $('.ttl-sec').innerWidth();
    $('.btn-group').css({ width: searchW });
  });
}

//表示形式変更ボタン表示範囲
//選択中の表示形式のindex取得
function getIndexSelectedView() {
  if ($('.viewChange li').length <= 1) {
    return -1;
  }

  return $('.viewChange ul li.select').index('.viewChange ul li');
}

//PCの場合
function viewChange_widthPC() {
  var viewNum = $('.viewChange li').length;
  if (viewNum >= 5) {
    $('.viewChange').css({ maxWidth: 442 });
    $('.schedule-header .input').css({ maxWidth: 30 + '%' });
    var ua = navigator.userAgent.toLowerCase(),
        isMac = ((ua.indexOf('mac') > -1) && (ua.indexOf('os') > -1)) && !((ua.indexOf('iphone') > -1) || (ua.indexOf('ipad') > -1) || (ua.indexOf('windows') > -1));
    if (isMac) {
      $('.viewChange > ul').addClass('is-scroll');
    }
  } else {
    $('.viewChange').css({ maxWidth: (viewNum * 80) + 6 });
    $('.schedule-header .input').css({ maxWidth: (8 * (9 - viewNum )) + '%' });
  }
  var index = getIndexSelectedView();
  if (index !== -1) {
    $('.viewChange ul').scrollLeft(80 * index);
  }
}
//TBの場合
function viewChange_widthTB() {
  $('.viewChange').css({ maxWidth: 120 });
  $('.viewChange ul').css({
    overflowX: 'auto',
    whiteSpace: 'nowrap'
  });
  $('.schedule-header .input').css({ maxWidth: '' });
  var ua = navigator.userAgent.toLowerCase(),
      isMac = ((ua.indexOf('mac') > -1) && (ua.indexOf('os') > -1)) && !((ua.indexOf('iphone') > -1) || (ua.indexOf('ipad') > -1) || (ua.indexOf('windows') > -1));
  if (isMac) {
    $('.viewChange > ul').removeClass('is-scroll');
  }
  var index = getIndexSelectedView();
  if (index !== -1) {
    $('.viewChange ul').scrollLeft(80 * index);
  }
}
//SPの場合
function viewChange_widthSP() {
  $('.viewChange').css({ maxWidth: 25 + '%' });
  $('.viewChange ul').css({
    overflowX: 'auto',
    whiteSpace: 'nowrap'
  });
  $('.schedule-header .input').css({ maxWidth: '' });
  var ua = navigator.userAgent.toLowerCase(),
      isMac = ((ua.indexOf('mac') > -1) && (ua.indexOf('os') > -1)) && !((ua.indexOf('iphone') > -1) || (ua.indexOf('ipad') > -1) || (ua.indexOf('windows') > -1));
  if (isMac) {
    $('.viewChange > ul').removeClass('is-scroll');
  }
  var index = getIndexSelectedView();
  if (index !== -1) {
    $('.viewChange ul').scrollLeft(65 * index);
  }
}

function schedule_common_PC() {
	serchBtnWidth_PCTB();
    searchBtnFixedPCTB();
    viewChange_widthPC();
}

function schedule_common_TB() {
	serchBtnWidth_PCTB();
    searchBtnFixedPCTB();
    viewChange_widthTB();
}

function schedule_common_SP() {
	serchBtnWidth_sp();
    searchBtnFixedSP();
    viewChange_widthSP();
}

function re_common_trigger() {
	if ($(window).innerWidth() >= 1025) {
		schedule_common_PC();
	} else if ($(window).innerWidth() >= 768) {
		schedule_common_TB();
	} else {
		schedule_common_SP();
	}
    $(window).trigger('resize, scroll');
	
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
		if (e['breakpoint'] === PCw) {
			//PC
			schedule_common_PC();
		} else if (e['breakpoint'] === TBw) {
			//TB
			schedule_common_TB();
		} else {
			//SP
			schedule_common_SP();
		}
	});
}