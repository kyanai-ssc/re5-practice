/*JavaScript Document*/

//スケジュール幅
function re_subject_day_tableW(num) {
	var thisTable = $('.subject_table.type_day');
	$(window).on('load resize', function() {
		var width_t = $('.l-main').innerWidth() - num;
		thisTable.css({
			width: width_t
		});
	});
}

//スケジュールPC,TBの挙動
function re_subject_day_pcdesign() {
	// position: stickyがブラウザで使えるかチェックするための関数
	function detectSticky() {
	  const div = document.createElement('div');
	  div.style.position = 'sticky';
	  return div.style.position.indexOf('sticky') !== -1;
	}

	// .stickyが指定されている要素に対してposition: stickyを適用させる関数
	function callStickyState() {
	  return new StickyState($('.sticky'));
	}

	//Edge判別
	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);
	
	var thisTable = $('.subject_table.type_day'),
			scheduleHeader = $('.schedule-header'),
			calender_list = $('#calender_list'),
			stickyTable = thisTable.find('.stickyTable'),
			th_sticky = thisTable.find('th.sticky');

	// ブラウザでposition: stickyが使えない場合またはEdgeの場合の処理
	if (!detectSticky() || isEdge) {
		$('#calender_list th').removeClass('sticky');
		var scheduleHeight = parseInt(scheduleHeader.outerHeight());
		stickyTable.css({top: scheduleHeight});
		//IE Edgeでの幅取得処理
		function get_table_width_IE() {
			var tdWidth = calender_list.width() - stickyTable.find('th:first-child').width();
			stickyTable.find('th:not(:first-child)').css({width: tdWidth});
			stickyTable.find('th:first-child').css({width: stickyTable.find('th:first-child').width()});
			var tableWidth = parseInt(calender_list.outerWidth());
			stickyTable.css('width', tableWidth);
			$('#calender_list thead').css({width: tableWidth});
			if(isEdge) {
				scheduleHeader.removeClass('sticky').css({width: tableWidth });
			}
		}
		get_table_width_IE();
		$(window).on('load resize' , function(){
			get_table_width_IE();
		});
		
		$(window).on('load scroll' , function(){
			//トグル表示した際に.offset().top再取得
			var tableTop = parseInt($('.l-calendar').offset().top) + 30;
			var scrollTop = $(window).scrollTop();
			
			if (scrollTop > tableTop) {
				stickyTable.addClass('fixed');
				if(isEdge) {
					scheduleHeader.addClass('fixed');
					calender_list.css({paddingTop: scheduleHeight});
				}
			} else {
				stickyTable.removeClass('fixed');
				if(isEdge) {
					scheduleHeader.removeClass('fixed');
					calender_list.css({paddingTop: ''});
				}
			}
		});
	}
	var scheduleHeight = parseInt(scheduleHeader.outerHeight());
	th_sticky.css({top: scheduleHeight});
}

//スケジュールSPの挙動
function re_subject_day_spdesign() {
	var thisTable = $('.subject_table.type_day'),
			stickyTable = thisTable.find('.stickyTable'),
			scheduleHeader = $('.schedule-header'),
			scheduleHeight = parseInt(scheduleHeader.outerHeight()),
			th_sticky = thisTable.find('th.sticky');

	th_sticky.css({top: scheduleHeight});

	$(document).on('click', '.btn-colorTip', function() {
    if($('.colorTip').hasClass('is-open')) {
			scheduleHeight = parseInt(scheduleHeader.outerHeight());
		} else {
			scheduleHeight = parseInt(scheduleHeader.outerHeight());
		}
		th_sticky.css({top: scheduleHeight});
  });
}

function re_subject_day_PC() {
	re_subject_day_pcdesign();
	re_subject_day_tableW(40);
}
function re_subject_day_TB() {
	re_subject_day_pcdesign();
	re_subject_day_tableW(20);
}
function re_subject_day_SP() {
	re_subject_day_spdesign();
}

function re_subject_day_trigger() {
	if ($(window).innerWidth() >= 1025) {
		re_subject_day_PC();
	} else if ($(window).innerWidth() >= 768) {
		re_subject_day_TB();
	} else {
		re_subject_day_SP();
	}
    $(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
	  if (e['breakpoint'] === PCw) {
		//PC
		re_subject_day_PC();
	  } else if (e['breakpoint'] === TBw) {
		//TB
		re_subject_day_TB();
	  } else {
		//SP
		re_subject_day_SP();
	  }
	});
}
