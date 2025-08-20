function continue_pctbdesign() {
  //stickyのIE対応
  var elements = $('.sticky');
  Stickyfill.add(elements);
  $.each($('.list_body_line_wrap') , function(i, elem) {
    var baseElm1 = $(this).find('.list_body_line').find('li:last-child'),
        baseElm2 = $(this).find('.list_body_line').find('li:first-child'),
        changeElm = $(this).find('.b-confirm');
    baseElm1.after(changeElm);
    changeElm.css({ height: '' })
  });
}

function continue_spdesign() {
  $.each($('.list_body_line_wrap') , function(i, elem) {
    var baseElm = $(this).find('.list_body_line').find('li:first-child'),
        changeElm = $(this).find('.b-confirm'),
        boxHeight = $(this).find('.b-name').outerHeight() + $(this).find('.b-dayTime').outerHeight() + $(this).find('.b-num').outerHeight();
    baseElm.after(changeElm);
    changeElm.css({ height: boxHeight});
  });
}

function continue_trigger() {
  if ($(window).innerWidth() >= 768) {
    continue_pctbdesign();
  } else {
    continue_spdesign();
  }
  $(window).trigger('resize, scroll');
  //発火タイミング
  axia.addEventListener( 'breakpoints', function( e ){
    if (e['breakpoint'] === PCw) {
      //PC
      continue_pctbdesign();
    } else if (e['breakpoint'] === TBw) {
      //TB
      continue_pctbdesign();
    } else {
      //SP
      continue_spdesign();
    }
  });
}