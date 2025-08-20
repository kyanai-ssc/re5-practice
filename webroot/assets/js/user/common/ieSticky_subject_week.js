/*JavaScript Document*/

function re_subject_week_pcdesign() {
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

	var ua = navigator.userAgent.toLowerCase(),
			isEdge = (ua.indexOf('edge') > -1),
			isFirefox = (ua.indexOf('firefox') > -1);
	
	var calenderList = $('#calender_list'),
			thisTable = $('.subject_table.type_week'),
			lCalendar = $('.l-calendar'),
			stickyTable = thisTable.find('.stickyTable'),
			tableH = stickyTable.find('th'),
			scheduleHeader = $('.schedule-header'),
			emptyCell = stickyTable.find('.emptyCell'),
			columnW = thisTable.find('.column').outerWidth();
	
	$('#img_scroll_slide').addClass('hidden');
	$('.subjectWeek').remove();
	thisTable.find('th').removeClass('sticky');
	emptyCell.css({
		width: columnW,
		minWidth: columnW,
		maxWidth: columnW
	});
	
	function tableWidth_fn() {
		var tableWidth =  calenderList.outerWidth(),
				tableH_fixed = thisTable.find('.stickyTable').find('th:not(:first-child)'),
				tableB_fixed = thisTable.find('td:not(:first-child)'),
				scheduleHeader = $('.schedule-header');
		if(isEdge) {
			scheduleHeader.css({ width: tableWidth });
		}
		if (!detectSticky() || isEdge || isFirefox) {
			var tdW = (tableWidth - columnW) / 7;
		} else {
			var tdW = parseInt((tableWidth - columnW) / 7);
		}
		
		tableB_fixed.css({
			width: tdW,
			minWidth: tdW,
			maxWidth: tdW
		});
		tableH_fixed.css({
			width: tdW,
			minWidth: tdW,
			maxWidth: tdW
		});
	}
	tableWidth_fn();
  $(window).on('load resize', function() {
    tableWidth_fn();
  });
	calenderList.on('load scroll' , function() {
		stickyTable.css({right: ''});
  }).css({ overflowX: '' }).unwrap('.cal_wrap');
	var t = scheduleHeader.outerHeight();
	stickyTable.css({ top: t });
	if(isEdge) {
		scheduleHeader.removeClass('sticky');
	}

	$(window).on('load scroll' , function(){
		var tableTop = parseInt(lCalendar.offset().top + 30),
				scrollTop = $(window).scrollTop();
		if (scrollTop > tableTop) {
			if (!detectSticky() || isEdge) {
				stickyTable.addClass('ieFixed');
				if(isEdge) {
					var scheduleHeight = parseInt(scheduleHeader.outerHeight() + tableH.outerHeight());
					scheduleHeader.addClass('fixed');
				}	
			} else {
				stickyTable.addClass('fixed');
			}
			thisTable.css({ paddingTop: tableH.outerHeight() });
		} else {
			stickyTable.removeClass('ieFixed');
			if (!detectSticky() || isEdge) {
				stickyTable.removeClass('ieFixed');
				if (isEdge) {
					scheduleHeader.removeClass('fixed');
				}
			} else {
				stickyTable.removeClass('fixed');
			}
			thisTable.css({ paddingTop: '' });
		}
	});
}

function re_subject_week_spdesign() {
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

	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);
	
	var thisTable = $('.subject_table.type_week'),
			stickyTable = thisTable.find('.stickyTable'),
			tableH = stickyTable.find('th'),
			scheduleHeader = $('.schedule-header'),
			calenderList = $('#calender_list'),
			lCalendar = $('.l-calendar'),
			win_width = $(window).width(),
			emptyCell = stickyTable.find('.emptyCell'),
			columnW = thisTable.find('.column').outerWidth();
	
  //SPの場合
	if ( ($('#img_scroll_slide').hasClass('hidden')) && (calenderList.find('table thead').width() > win_width) && (thisTable.hasClass('type_week')) ) {
		//横スクロール可能を示す画像
		$('#img_scroll_slide').removeClass('hidden');
	}
	if (!($('.subjectWeek').length) && (thisTable.hasClass('type_week') )) {
		calenderList.wrap('<div class="cal_wrap"></div>');	
		$('.cal_wrap').append('<div class="subjectWeek"></div>');
	}
	$('.subjectWeek').css({ top:tableH.outerHeight() })
	$('#calender_list').css({ overflowX: 'scroll' });
	calenderList.find(thisTable).find('th').removeClass('sticky');
	
	//テーブルの幅取得（SP）
	function get_table_width_sp() {
		var tableH_fixed = thisTable.find('.stickyTable').find('th:not(:first-child)'),
				tableB_fixed = thisTable.find('td:not(:first-child)');
	  if(isEdge) {
			scheduleHeader.css({ width: '' });
		}
		tableB_fixed.css({
			width: '',
			minWidth: '',
			maxWidth: ''
		});
		tableH_fixed.css({
			width: '',
			minWidth: '',
			maxWidth: ''
		});
	}
  get_table_width_sp();
  $(window).on('load resize', function() {
    get_table_width_sp();
  });
	emptyCell.css({
		width: columnW,
		minWidth: columnW,
		maxWidth: columnW,
		left: 0,
		top: 0
	});
	$(window).on('load scroll', function(){
		if (!$('#img_scroll_slide').hasClass('hidden')) {
			var tableTop = parseInt(lCalendar.offset().top + $('#img_scroll_slide').outerHeight());
		} else {
			var tableTop = parseInt(lCalendar.offset().top);
		}
		var scrollTop = $(window).scrollTop();
		if (scrollTop > tableTop) {
			thisTable.css({ paddingTop: tableH.outerHeight() });
			calenderList.find('th').outerHeight();
			var scheduleHeight = scheduleHeader.outerHeight();
			if (!detectSticky() || isEdge) {
				stickyTable.addClass('ieFixed');
			} else {
				stickyTable.addClass('fixed');
			}
			stickyTable.css({ 
				top: scheduleHeight,
				paddingLeft: columnW
			})
		} else {
			if (!detectSticky() || isEdge) {
				stickyTable.removeClass('ieFixed');
			} else {
				stickyTable.removeClass('fixed');
			}
			thisTable.css({ paddingTop: '' });
		}
	});
	
	var count = 0; //カウント初期値
	  //スクロール値取得
	  calenderList.on('load scroll' , function() {
		count = $(this).scrollLeft();
			stickyTable.css({right: count});
			emptyCell.css({left: count});
	  });
	
	//SP時のみ左側予約枠名テーブル追加
	var thisTable = $('.subject_table'),
			tr = thisTable.find('tbody tr');
	//カレンダーのtrの数だけ繰り返す
	if (!$('.subjectWeek .column ').length) {
		$.each(tr , function(i) {
			//thisのtd:first-childを
			var td = tr.find('td.column.time');
			//subjectWeek内の後ろへ後ろへ追加
			$('.subjectWeek').append(td[i].outerHTML);
			return //追加は1回限り
		});
	}
}

function re_subject_week_trigger() {
	if ($(window).innerWidth() >= 1025) {
		re_subject_week_pcdesign();
	} else if ($(window).innerWidth() >= 768) {
		re_subject_week_pcdesign();
	} else {
		re_subject_week_spdesign();
	}
  $(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
	  if (e['breakpoint'] === PCw) {
		//PC
		re_subject_week_pcdesign();

	  } else if (e['breakpoint'] === TBw) {
		//TB
		re_subject_week_pcdesign();

	  } else {
		//SP
		re_subject_week_spdesign();
	  }
	});
}