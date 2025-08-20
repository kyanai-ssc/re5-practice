// JavaScript Document
$(function () {
  
  //メニュー開閉
  var winH = $(window).height();
  $('.nav-layer').css('height', winH);
  $('.open-nav').each( function() {
    var openNav = $(this);
    $(this).find('.swich').on('click', function(){
      var targetNav = $(this).next('.nav-list');
      if ( targetNav.css('display') === ('none') ) {
            openNav.find('.nav-list').hide();
            openNav.find('.swich.open').removeClass('open');
            $('.nav-layer').hide();
          }
      targetNav.toggle();
      $(this).toggleClass('open');
      $('.nav-layer').toggle();

    });
  });
  $('.nav-layer').on('click', function(){
    $('.nav-layer').hide();
    $('.nav-list').hide();
  });

  //ツールチップ
  var isPC = $('body').hasClass('pc');
  if (isPC) { 
    if ($(document).find('.tooltip')) {
      $('.tooltip').tooltip();
    }
  }
  
  //フォーカス外れた後に全角を半角に変換
  var charactersChange = function(ele){
    var val = ele.val();
    var han = val.replace(/[Ａ-Ｚａ-ｚ０-９]/g,function(s){
      return String.fromCharCode(s.charCodeAt(0)-0xFEE0);
    });

    if(val.match(/[Ａ-Ｚａ-ｚ０-９]/g)){
      $(ele).val(han);
    }
  };
  $(".js-characters-change").blur(function(){
    charactersChange($(this));
  });

});

//デバイス判定
window.onload = function () {
    var ua        = navigator.userAgent,
        bodyClass = document.body.classList;

    if (ua.indexOf('iPad') > 0 || ua.indexOf('Android') > 0) {
        // タブレット
        bodyClass.add('tablet');

        if(ua.indexOf('iPad') > 0)    bodyClass.add('ipad');       // iPad
        if(ua.indexOf('Android') > 0) bodyClass.add('android'); // Android
    } else {
        // PC用コード
        bodyClass.add('pc');
    }
}// window.onload


























