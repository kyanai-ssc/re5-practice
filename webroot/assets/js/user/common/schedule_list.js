// JavaScript Document
//PCデザイン
function re_list_pcdesign() {
  $('.schedule-header .is-top').css({ 'padding-top': '' });

  $.each($('.list_body_line_wrap') , function() {
    var baseElm = $(this).find('.list_body_line').find('li:first-child'),
        baseElmLater = $(this).find('.list_body_line').find('li:last-child'),
        changeElm = $(this).find('.b-status');
      baseElmLater.after(changeElm);
      baseElm.css({ borderBottom: '' });
  });
  var ua = navigator.userAgent.toLowerCase(),
      isEdge = (ua.indexOf('edge') > -1);
  function tableWidth_fn() {
    if(isEdge) {
      var tableWidth =  $('.reserve_list_body').outerWidth(),
          scheduleHeader = $('.schedule-header');
      scheduleHeader.css({ width: tableWidth });
    }
  }
  if(isEdge) {
    tableWidth_fn();
    $(window).on('load resize', function() {
      tableWidth_fn();
    });
  }
}

//SPデザイン
function re_list_spdesign() {
  $('.schedule-header .is-top').css({ 'padding-top': 10 });
  $.each($('.list_body_line_wrap') , function(index) {
    var baseElm = $(this).find('.list_body_line').find('li:first-child'),
        changeElm = $(this).find('.b-status');
    if ($(this).find('.b-day').length) {
      baseElm.css({ borderBottom: '1px solid #ccc' });
    } else {
			$(this).find('.b-detail a').addClass('noTimeDay');
		}
    baseElm.after(changeElm);
  });
}

function re_list_trigger() {
  if ($(window).innerWidth() >= 768) {
    re_list_pcdesign();
  } else {
    re_list_spdesign();
  }
  $(window).trigger('resize, scroll');

  //発火タイミング
  axia.addEventListener( 'breakpoints', function( e ){
    if (e['breakpoint'] === PCw) {
      //PC
      re_list_pcdesign();
    } else if (e['breakpoint'] === TBw) {
      //TB
      re_list_pcdesign();
    } else {
      //SP
      re_list_spdesign();
    }
  });
}


