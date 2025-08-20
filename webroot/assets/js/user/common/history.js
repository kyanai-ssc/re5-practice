function history_pctbdesign() {
  //stickyのIE対応
  var elements = $('.sticky');
  Stickyfill.add(elements);
  $.each($('.list_body_line_wrap') , function(i, elem) {
    var baseElm = $(this).find('.list_body_line li:last-child'),
        changeElm1 = $(this).find('.b-statusP'),
        changeElm2 = $(this).find('.b-confirm');
    baseElm.after(changeElm2);
    changeElm2.before(changeElm1);
    changeElm2.css({ height: '' });
  });
}

function history_spdesign() {
  $.each($('.list_body_line_wrap') , function(i, elem) {
    var baseElm = $(this).find('.list_body_line li:first-child'),
        thisH = $(this).innerHeight(),
        changeElm1 = $(this).find('.b-statusP'),
        changeElm2 = $(this).find('.b-confirm'),
        num = $(this).find('.b-num');
    baseElm.after(changeElm2);
    changeElm2.before(changeElm1);
    if (changeElm1.length) {
      changeElm2.css({height: $(this).find('.b-name').outerHeight() + $(this).find('.b-dayTime').outerHeight()});
    } else if (num.length) {
      changeElm2.css({height: $(this).find('.b-name').outerHeight() + $(this).find('.b-dayTime').outerHeight() + num.outerHeight() });
    } else {
      changeElm2.css({height: $(this).find('.b-name').outerHeight() + $(this).find('.b-dayTime').outerHeight() + $(this).find('.b-statusR').outerHeight() });
    }
  });
}

function history_trigger() {
  if ($(window).innerWidth() >= 768) {
    history_pctbdesign();
  } else {
    history_spdesign();
  }
  $(window).trigger('resize, scroll');
  //発火タイミング
  axia.addEventListener( 'breakpoints', function( e ){
    if (e['breakpoint'] === PCw) {
      //PC
      history_pctbdesign();
    } else if (e['breakpoint'] === TBw) {
      //TB
      history_pctbdesign();
    } else {
      //SP
      history_spdesign();
    }
  });
}