// JavaScript Document

////////////     テーブルヘッダー追従処理     //////////////

//スケジュール共通の挙動
function re_time_commondesign() {
	var tableH_wrap = $('#timeTableHead'),
			timeTableTime = $('#timeTableTime'),
			time_width = timeTableTime.innerWidth(),
			tableH = $('#timeTableHead table'),
			emptyCell = timeTableTime.find('.emptyCell');
	tableH_wrap.addClass('absolute').css({ visibility: 'hidden' });
	tableH.find('th:first-child').css({
		width: time_width,
		maxWidth: time_width,
		minWidth: time_width
	});

	//追従ボタン幅取得
	function get_btn_width() {
		var scrl_btn_wrap = $('.calender-scroll-btn-wrap'),
				tableWidth = $('#calender_list').outerWidth();
		scrl_btn_wrap.css({ width: tableWidth });
	}

	get_btn_width();

	//追従テーブル幅取得
	function get_tableHeader_width() {
		var tableH = $('#timeTableHead table'),
				tableWrap = $('.timeTable-wrap'),
				tableWrapWidth = parseInt(tableWrap.outerWidth());
		tableH.css({ width: tableWrapWidth });
	}
	$(window).on('load resize', function(){
		get_tableHeader_width();
		get_btn_width();
	});
	
	emptyCell.css({ 
		height: tableH_wrap.outerHeight()
	});
}

//スケジュールPC,TBの挙動
function re_time_pcdesign() {
	var tableH_wrap = $('#timeTableHead'),
			tableH = $('#timeTableHead table'),
			tableWrap = $('.timeTable-wrap'),
			scheduleHeader = $('.schedule-header'),
			scrl_btn_wrap = $('.calender-scroll-btn-wrap'),
			timeTableTime = $('#timeTableTime'),
			tableWidth = $('#calender_list').outerWidth();
	$('#img_scroll_slide').addClass('hidden');
	//追従パーツのtop取得
	function get_followPart_top() {
		var tableH_wrap = $('#timeTableHead'),
				scheduleHeader = $('.schedule-header'),
				scheduleHeight = scheduleHeader.outerHeight();
		tableH_wrap.css({ top: scheduleHeight });
		if ($('#timeTable').outerWidth() === 980) {
			tableH.css({ tableLayout: 'fixed' });
		}
	}
	tableH.css({paddingLeft:''});
	get_followPart_top();
	$(window).on('load resize', function(){
		get_followPart_top();
		tableH.css({paddingLeft:''});
	});
	scrl_btn_wrap.show();
	timeTableTime.show();
	tableH_wrap.show();
	tableWrap.css({ 
		overflowX: 'hidden',
		overflowY: 'hidden'
	});
	tableH.css({
		overflowX: 'hidden',
		overflowY: 'hidden'
	});
	

	//Edge判別
	var uaB = navigator.userAgent.toLowerCase(),
			isEdge = (uaB.indexOf('edge') > -1);
	if(isEdge) {
		scheduleHeader.removeClass('sticky').css({ width: tableWidth });
	}

	//テーブルの幅取得（PC）
	function get_table_width() {
		var scrl_btn = $('.calender-scroll-btn'),
				btn = scrl_btn.outerWidth() * 2,
				btnW = btn + 75,
				lCalendar = $('.l-calendar'),
				tableH_wrap = $('#timeTableHead'),
				tableWrap = $('.timeTable-wrap'),
				timeTableTime = $('#timeTableTime'),
				timeTop = parseInt($('#timeTable thead').outerHeight()),
				width_T = lCalendar.outerWidth() - btnW;
		tableWrap.css({
			maxWidth: 980,
			width: width_T
		});
		tableH_wrap.css({
			maxWidth: 980,
			width: width_T
		});
		var timeLeft = parseInt($('#calender_list').outerWidth()) - parseInt(tableWrap.innerWidth());
		timeTableTime.css({
			left: timeLeft / 2,
			top: timeTop,
			paddingTop: ''
		});
	}
	get_table_width();
	$(window).on('load resize', function(){
		get_table_width();
		if(isEdge) {
			scheduleHeader.css({ width: tableWidth });
		}
	});

	//スクロールボタン長さ指定
	var table = $('#timeTable'),
			scrollHeight = $(window).height()/2,
			tblHeight = table.outerHeight(),
			scrl_btn = $('.calender-scroll-btn');
	if (tblHeight <= 350) {
		scrl_btn.css({ height: tblHeight - 20 });
	} else {
		scrl_btn.css({ height: scrollHeight });
	}

	//スケジュールoffset取得
	function tableTop_num_pctb() {
		var lCalendar = $('.l-calendar');
		return parseInt(lCalendar.offset().top + 30);
	}

	$(window).on('load scroll' , function(){
		var tableTop = tableTop_num_pctb(),
				scrollTop = $(window).scrollTop(),
				calenderList = $('#calender_list'),
				tableH_wrap = $('#timeTableHead'),
				timeTop = parseInt($('#timeTable thead').outerHeight()),
				emptyCell = timeTableTime.find('.emptyCell');
		get_followPart_top();
		if (scrollTop > tableTop) {
			var scheduleHeight = scheduleHeader.outerHeight();
			scrl_btn_wrap.css({ top: scheduleHeight + 10 }).addClass('is-active');
			tableH_wrap.addClass('fixed').removeClass('absolute').css({ visibility: 'visible' });
			emptyCell.addClass('fixed').removeClass('absolute').css({ top: scheduleHeight });
			if(isEdge) {
				scheduleHeader.css({ position: 'fixed' });
				calenderList.css({ paddingTop: scheduleHeight });
				timeTableTime.css({ top: timeTop + scheduleHeight });
			}
		} else {
			scrl_btn_wrap.css({ top: '' }).removeClass('is-active');
			tableH_wrap.addClass('absolute').removeClass('fixed').css({ visibility: 'hidden' });
			emptyCell.addClass('absolute').removeClass('fixed').css({ top: -tableH_wrap.outerHeight() });
			if(isEdge) {
				scheduleHeader.css({ position: '' });
				calenderList.css({ paddingTop: '' });
				timeTableTime.css({ top: timeTop });
			}
		}
	});
}

//スケジュールSPの挙動
function re_time_spdesign() {
	var win_width = $(window).width(),
			tableH = $('#timeTableHead table'),
			tableWrap = $('.timeTable-wrap'),
			scheduleHeader = $('.schedule-header'),
			scrl_btn_wrap = $('.calender-scroll-btn-wrap'),
			timeTableTime = $('#timeTableTime'),
			table = $('#timeTable'),
			calenderList = $('#calender_list'),
			emptyCell = timeTableTime.find('.emptyCell'),
			timeTop = parseInt($('#timeTable thead').outerHeight()),
			time_width = timeTableTime.width();
	//Edge判別
	var uaB = navigator.userAgent.toLowerCase(),
			isEdge = (uaB.indexOf('edge') > -1);
	//横スクロール可能を示す画像
	if ( ($('#img_scroll_slide').hasClass('hidden')) && (calenderList.find('table thead').width() > win_width) ) {
		$('#img_scroll_slide').removeClass('hidden');
	}
	scrl_btn_wrap.hide();
	table.css({ tableLayout: '' });
	tableWrap.css({
		overflowX: 'scroll',
		overflowY: 'visible'
	});
	tableH.css({
		overflowX: 'scroll',
		overflowY: 'visible',
		tableLayout: ''
	});
	calenderList.css({ overflow: 'hidden' });

	//追従パーツのtop取得
	function get_followPart_top() {
		var tableH_wrap = $('#timeTableHead');
		var scheduleHeader = $('.schedule-header');
		var scheduleHeight = scheduleHeader.outerHeight();
		tableH_wrap.css({ top: scheduleHeight });
	}

	get_followPart_top();

	//テーブルの幅取得（SP）
	function get_table_width_SP() {
		var tableH_wrap = $('#timeTableHead'),
				tableWrap = $('.timeTable-wrap'),
				timeTableTime = $('#timeTableTime'),
				stickyTable = tableH_wrap.find('.stickyTable');
		tableWrap.css({
			maxWidth: '',
			width: ''
		});
		tableH_wrap.css({
			maxWidth: '',
			width: ''
		});
		timeTableTime.css({
			left: 0,
			top: 0,
			paddingTop: timeTop
		});
		tableH.css({
			tableLayout: ''
		});
		//カレンダーの幅がデバイス以下の場合
		if (stickyTable.find('.day_div').outerWidth() + time_width < win_width) {
			tableH.css({
				paddingLeft:time_width - 1,
				width: '100%'
			});
			table.find('thead .day_div').css({
				width : '100%'
			});
			stickyTable.find('.day_div').css({
				width : '100%'
			});
			table.find('th:not(.emptyTD)').css({
				
			});
		}
	}
	get_table_width_SP();
	$(window).on('load resize', function(){
		get_table_width_SP();
	});

	function tableTop_num_sp() {
		var lCalendar = $('.l-calendar');
		if (!$('#img_scroll_slide').hasClass('hidden')) {
			return parseInt(lCalendar.offset().top + $('#img_scroll_slide').outerHeight());
		} else {
			return parseInt(lCalendar.offset().top);
		}
	}
	
	tableH.css({paddingLeft:time_width});
	$(window).on('load scroll' , function(){
		var tableTop = tableTop_num_sp(),
				scrollTop = $(window).scrollTop(),
				tableH_wrap = $('#timeTableHead'),
				scheduleHeight = scheduleHeader.outerHeight();
		get_followPart_top();
		if (scrollTop > tableTop) {
			tableH_wrap.addClass('fixed').removeClass('absolute').css({ visibility: 'visible' });
			scrl_btn_wrap.css({ top: '' }).addClass('');
			emptyCell.addClass('fixed').removeClass('absolute').css({ top: scheduleHeight });
			
			if(isEdge) {
				scheduleHeader.css({ position: 'fixed' });
				calenderList.css({ paddingTop: '' });
				timeTableTime.css({ top: '' });
			}
		} else {
			scrl_btn_wrap.css({ top: '' }).removeClass('is-active');
			tableH_wrap.addClass('absolute').removeClass('fixed').css({ visibility: 'hidden' });
			emptyCell.addClass('absolute').removeClass('fixed').css({ top: -timeTop });
			if(isEdge) {
				scheduleHeader.css({ position: '' });
				calenderList.css({ paddingTop: '' });
				timeTableTime.css({ top: '' });
			}
		}
	});
}

function re_time_scroll_pc() {
	var table = $('#timeTable'),
			tableH = $('#timeTableHead table'),
			tableWrap = $('.timeTable-wrap'),
			scrl_btn_wrap = $('.calender-scroll-btn-wrap'),
			tableWrapW = $('.timeTable-wrap').outerWidth(), //テーブル表示領域
			tblW = $('#timeTable').outerWidth(), //テーブル全体の幅
			tblScrlW = parseInt(tblW - tableWrapW), //スクロール可能領域
			tableLimit = table.outerWidth() - tableWrap.outerWidth(),
			loadCount = parseInt(tableH.css('margin-left')) * -1;
	
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

	//カウント初期値
	if (loadCount > 1) {
		var count = loadCount;
	} else {
		var count = 0;
	}
	//clearinterval対策
	var setItv = '';

	//スクロール値取得
	tableWrap.on('load scroll' , function() {
		count = $(this).scrollLeft();
		//テーブルヘッダー追従した際も横スクロール位置を維持
		tableH.css({ marginLeft: -count });
	});

	$('#left-button').css('opacity', '0.4');
	//移動設定
	var countUp = function() {
		if (checkBA === 'a') {
			var count2 = count + feed;
			tableWrap.scrollLeft(count2);
			if ((tableLimit - count) <= 2) {
				clearInterval(setItv.shift());
			}
		} else {
			var count2 = count - feed;
			tableWrap.scrollLeft(count2);
			if (count < 1) {
				clearInterval(setItv.shift());
			}
		}
		if (!scrl_btn_wrap.hasClass('btn_fixed')) {
			tableLimit = table.outerWidth() - tableWrap.outerWidth();
			if (count < 1) {
				$('#left-button').css({
					'opacity': 0.4,
					'pointer-events': 'none'
				});
				$('#right-button').css({
					'opacity': 1,
					'pointer-events': 'auto'
				});
			} else if ((tableLimit - count) <= 2) {
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

	//テンキー→で横スクロール
	$('body').addClass('windows');
	$(window).on('keydown', function(e) {
		if(e.keyCode === 39) {
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

	//戻る
	$('#left-button').mousedown(function() {
		checkBA = 'b';
		if (0 < count) {
			setItvF();
			if (setItv.length > 1) {
				clearInterval(setItv.shift());
			}
		}
	}).mouseup(function() {
		clearInterval(setItv.shift());	
	});

	//iPad時→ボタンタッチで戻る
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
	
}

function re_time_scroll_sp() {
	var tableH = $('#timeTableHead table'),
			tableWrap = $('.timeTable-wrap'),
			loadCount = parseInt(tableH.css('margin-left')) * -1,
			td_length = tableWrap.find('tbody td').length - 1;

	//カウント初期値
	if (loadCount > 1) {
		var count = loadCount;
	} else {
		var count = 0;
	}

	//スクロール値取得
	tableWrap.on('load scroll' , function() {
		if (td_length >= 3) {
			count = $(this).scrollLeft();
			tableH.css({ marginLeft: -count });
		}
	});
}

//横スクロールが発生しない場合
function re_time_layout_no_scrolling_PC() {
	var table = $('#timeTable'),
			td_length = table.find('tbody td').length,
			tableH = $('#timeTableHead table');
	if (td_length <= 11) {
		table.css({ tableLayout: 'fixed' });
		$('.calender-scroll-btn-wrap').addClass('btn_fixed');
		$(window).on('load resize', function(){
			var w = table.find('tbody td.line').outerWidth();
			tableH.css({ width: 100+'%'});
			tableH.find('tr:nth-child(2) th:not(:first-child)').css({
				width: w,
				maxWidth: w,
				minWidth: w
			});
			table.find('tr:nth-child(2) th:not(:first-child)').css({
				width: '',
				maxWidth: '',
				minWidth: ''
			});
		});
		if (td_length <= 4) {
			tableH.css({ tableLayout: 'fixed' });
		}
	}
}
function re_time_layout_no_scrolling_SP() {
	var table = $('#timeTable'),
			td_length = table.find('tbody td').length - 1,
			tableH = $('#timeTableHead table'),
			w = $('#timeTableTime').outerWidth(),
			tableWrap = $('.timeTable-wrap'),
			thW = parseInt(($(window).outerWidth() - w ) / td_length);
	if (td_length <= 4) {
		$('#img_scroll_slide').addClass('hidden');
		table.css({ tableLayout: '' });
		$('.calender-scroll-btn-wrap').addClass('btn_fixed');
		$(window).on('load resize', function(){
			table.find('tr:nth-child(2) th:not(:first-child)').css({
				width: '',
				maxWidth: '',
				minWidth: ''
			});
			tableH.find('tr:nth-child(2) th:not(:first-child)').css({
				width: '',
				maxWidth: '',
				minWidth: ''
			});
		});
	}
}

function re_time_PC_TB() {
	re_time_commondesign();
	re_time_pcdesign();
	re_time_scroll_pc();
	re_time_layout_no_scrolling_PC();
}

function re_time_SP() {
	re_time_commondesign();
	re_time_spdesign();
	re_time_scroll_sp();
	re_time_layout_no_scrolling_SP();
}	

function re_time_trigger() {
	if ($(window).innerWidth() >= 768) {
		re_time_PC_TB();
	} else {
		re_time_SP();
	}
	$(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
		if (e['breakpoint'] === PCw) {
			//PC
			re_time_PC_TB();
		} else if (e['breakpoint'] === TBw) {
			//TB
			re_time_PC_TB();
		} else {
			//SP
			re_time_SP();
		}
	});
}

//動的ロード時読み込み用トリガーJS
function re_load_scroll_trigger() {
	if ($(window).innerWidth() >= 768) {
		re_time_scroll_pc();
	} else {
		re_time_scroll_sp();
	}
	$(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
		if (e['breakpoint'] === PCw) {
			//PC
			re_time_scroll_pc();
		} else if (e['breakpoint'] === TBw) {
			//TB
			re_time_scroll_pc();
		} else {
			//SP
			re_time_scroll_sp();
		}
	});
}
