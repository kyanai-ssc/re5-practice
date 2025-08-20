// JavaScript Document

function re_day_week_pcdesign() {
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

	//ブラウザ判定
	var ua = navigator.userAgent.toLowerCase(),
			isEdge = (ua.indexOf('edge') > -1);
	
	var thisTable = $('.dayTable.type_week'),
			stickyTable = thisTable.find('.stickyTable'),
			tableH = stickyTable.find('th'),
			scheduleHeader = $('.schedule-header'),
			calenderList = $('#calender_list'),
			lCalendar = $('.l-calendar');
	
	$('#img_scroll_slide').addClass('hidden');
	$('.dayTableTime').remove();
	var tableWrap = $('#calender_list > div:not(.dayTableTime)');
	tableWrap.on('load scroll' , function() {
		count = $(this).scrollLeft();
		stickyTable.css({right: ''});
	});
	stickyTable.css({right: ''});
	tableH.removeClass('sticky');
	$('#calender_list > div:not(.dayTableTime)').css({ overflowX: '' });
	var scrEle_height = tableH.outerHeight();
	if (isEdge) {
		scheduleHeader.removeClass('sticky');
		var scrEle_height = scheduleHeader.outerHeight();
	}
	$(window).on('load scroll', function(){
		var scheduleHeight = scheduleHeader.outerHeight(),
				tableTop = parseInt(lCalendar.offset().top + 30),
				scrollTop = $(window).scrollTop();
		if (scrollTop > tableTop) {
			stickyTable.css({ top: scheduleHeight });
			stickyTable.addClass('fixed');
			if (isEdge) {
				scheduleHeader.addClass('fixed');
			}
			calenderList.css({ paddingTop: scrEle_height });
		} else {
			stickyTable.removeClass('fixed');
			calenderList.css({ paddingTop: '' });
			if (isEdge) {
				scheduleHeader.removeClass('fixed');
			}
		}
	});
}

function re_day_week_tableWidth(num) {
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

	//ブラウザ判定
	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);
	
	var thisTable = $('.dayTable.type_week'),
			stickyTable = thisTable.find('.stickyTable'),
			tableH = stickyTable.find('th'),
			lMain = $('.l-main'),
			tableWidth = parseInt(lMain.innerWidth() - num),
			scheduleHeader = $('.schedule-header'),
			tableTd = thisTable.find('td');
	if (isEdge) {
		scheduleHeader.css({ width: tableWidth });
	}
	var tdW = parseInt(tableWidth / 8);
	tableTd.css({
		width: tdW,
		minWidth: tdW,
		maxWidth: tdW
	});
	tableH.css({
		width: tdW,
		minWidth: tdW,
		maxWidth: tdW
	});
	$(window).on('load resize' , function() {
		var tableWidth = parseInt(lMain.innerWidth() - num);
		if (isEdge) {
			scheduleHeader.css({ width: tableWidth });
		}
		var tableWidth = parseInt(lMain.innerWidth() - num);
		var tdW = parseInt(tableWidth / 8);
		tableTd.css({
			width: tdW,
			minWidth: tdW,
			maxWidth: tdW
		});
		tableH.css({
			width: tdW,
			minWidth: tdW,
			maxWidth: tdW
		});
	});
}

function re_day_week_spdesign() {
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

	//ブラウザ判定
	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);
	
	var thisTable = $('.dayTable.type_week'),
			stickyTable = thisTable.find('.stickyTable'),
			tableH = stickyTable.find('th'),
			scheduleHeader = $('.schedule-header'),
			calenderList = $('#calender_list'),
			lCalendar = $('.l-calendar'),
			tableTd = thisTable.find('td'),
			win_width = $(window).width(),
			emptyCell = thisTable.find('.emptyTD');
	
	//横スクロール可能を示す画像
	if ( ($('#img_scroll_slide').hasClass('hidden')) && (calenderList.find('table thead').width() > win_width) && (thisTable.hasClass('type_week')) ) {
		$('#img_scroll_slide').removeClass('hidden');
	}
	
	if ( (!$('.dayTableTime').length) && (thisTable.hasClass('type_week')) ) {
		calenderList.append('<div class="dayTableTime"></div>');
		//SP時のみ左側予約枠名テーブル追加
		var elem = $('#timeTable tbody tr');
		//カレンダーのtrの数だけ繰り返す
		$.each(elem , function(i) {
			//thisのtd:first-childを
			var td = elem.find('td:first-child');
			//dayTableTime内の後ろへ追加
			$('.dayTableTime').append(td[i].outerHTML);
			return //追加は1回限り
		});
	}
	
	tableH.removeClass('sticky');
	var scheduleHeight = scheduleHeader.outerHeight();
	calenderList.on('load scroll' , function() {
		stickyTable.css({right: ''});
	}).css({ overflowX: '' });
	$('.dayTable.type_day.type_week').parent('div').css({ overflowX: 'scroll' });
	var scrEle_height = tableH.outerHeight();
	if (isEdge) {
		scheduleHeader.removeClass('sticky');
		var scrEle_height = scheduleHeader.outerHeight();
	}
	
	$(window).on('load scroll' , function(){
		var scheduleHeight = scheduleHeader.outerHeight(),
			scrollTop = $(window).scrollTop();
		if (!$('#img_scroll_slide').hasClass('hidden')) {
			var tableTop = parseInt(lCalendar.offset().top + $('#img_scroll_slide').outerHeight());
		} else {
			var tableTop = parseInt(lCalendar.offset().top);
		}
		if (scrollTop > tableTop) {
			stickyTable.css({ 
				top: scheduleHeight,
				paddingLeft: emptyCell.outerWidth()
			});
			emptyCell.removeClass('stop');
			var scheduleHeight = scheduleHeader.outerHeight();
			calenderList.css({ paddingTop: scrEle_height });
			if (!detectSticky() || isEdge) {
				stickyTable.addClass('ieFixed');
			} else {
				stickyTable.addClass('fixed');
			}
		} else {
			if (!detectSticky() || isEdge) {
				stickyTable.removeClass('ieFixed').css({ top: '' });
			} else {
				stickyTable.removeClass('fixed').css({ top: '' });
			}
			calenderList.css({ paddingTop: '' });
			emptyCell.addClass('stop');
			stickyTable.css({
				paddingLeft: ''
			});
		}
	});
	
	//テーブル追従ヘッダースクロール量同期
	var tableWrap = $('.dayTable.type_day.type_week').parent('div'),
			count = 0; //カウント初期値
	//スクロール値取得
	tableWrap.on('load scroll' , function() {
		count = $(this).scrollLeft();
		stickyTable.css({right: count});
		emptyCell.css({left: count}); 
	});
	
	//テーブル幅指定
	function tableWidth_sp() {
		tableTd.css({
			width: '',
			minWidth: '',
			maxWidth: ''
		});
		tableH.css({
			width: '',
			minWidth: '',
			maxWidth: ''
		});
		$('div.dayTableTime td').css({
			width: '',
			minWidth: '',
			maxWidth: ''
		});
	}
	tableWidth_sp();
	$(window).on('load resize' , function() {
		tableWidth_sp();
	});

}

function re_day_week_pc() {
	re_day_week_pcdesign();
	re_day_week_tableWidth(40);
}
function re_day_week_TB() {
	re_day_week_pcdesign();
	re_day_week_tableWidth(20);
}
function re_day_week_SP() {
	re_day_week_spdesign();
}

function re_day_week_trigger() {
	if ($(window).innerWidth() >= 1025) {
		re_day_week_pc();
	} else if ($(window).innerWidth() >= 768) {
		re_day_week_TB();
	} else {
		re_day_week_SP();
	}
    $(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
	  if (e['breakpoint'] === PCw) {
		//PC
		re_day_week_pc();
	  } else if (e['breakpoint'] === TBw) {
		//TB
		re_day_week_TB();
	  } else {
		//SP
		re_day_week_SP();
	  }
	});
}