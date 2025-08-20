// JavaScript Document
function calendar_month() {
	//グローバル変数
	var l_main = $('.l-main'),
			calender_list = $('#calender_list'),
			thisTable = $('.month_table'),
			schedule_wrap = $('.schedule-header-wrap'),
			stickyElm = thisTable.find('thead th.sticky'),
			stickyTable = thisTable.find('.stickyTable'),
			calendar_wrap = $('.calendar-wrap'),
			scheduleHeader = schedule_wrap.outerHeight(),
			reservation_filter = $('.schedule-header'),
			reservation_filter_height = reservation_filter.outerHeight(),
			header = $('.cmn-header').height(),
			th_height = thisTable.find('thead th').outerHeight(),
			width_T,
			tableTop,
			scrollTop;
	
	function tableHeaderW() {
		width_T = l_main.innerWidth() - 40;
		if (!detectSticky() || isEdge) {
			var tdW = width_T / 7;
		} else {
			var tdW = parseInt(width_T / 7 );
		}
		$('#calender_list table.month_table th , #calender_list table.month_table td').css({
			width: tdW,
			minWidth: tdW,
			maxWidth: tdW
		});
	}

	//スケジュールテーブルの幅取得
	function scheduleHeaderW() {
		width_T = l_main.innerWidth() - 40;
		schedule_wrap.css({ width: width_T });
	}

	//予約しぼり込みの高さ取得
	function showBtn_fn() {
		return parseInt(schedule_wrap.outerHeight());
	}

	//ブラウザのスクロールトップ位置取得
	function scr_top() {
		return $(window).scrollTop();
	}

	//tableのスクロールトップ位置取得
	function table_top() {
		return parseInt(calendar_wrap.offset().top - header);
	}

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

	//Edge判定
	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);

	function commondesign() {
		
		//予約しぼり込みの開閉ボタンクリックするごとに高さを再取得
		var showBtnClick = showBtn_fn();
		if (!detectSticky() || isEdge) {
			stickyTable.css({ top: showBtnClick + header });
		} else {
			stickyElm.css({ top: showBtnClick + header -1 });
		}
		calender_list.css({ paddingTop: showBtnClick });
		schedule_wrap.find('.showBtn').on('click', function(){
			showBtnClick = showBtn_fn();
			stickyTable.css({ top: showBtnClick});
			if ($(this).hasClass('opend')) {
				if (!detectSticky() || isEdge) {
					stickyTable.css({ top: showBtnClick + header + reservation_filter_height});
				} else {
					stickyElm.css({ top: showBtnClick + header + reservation_filter_height -1});
				}
				calender_list.css({ paddingTop: showBtnClick + reservation_filter_height });
			} else {
				if (!detectSticky() || isEdge) {
					stickyTable.css({ top: showBtnClick + header - reservation_filter_height });
				} else {
					stickyElm.css({ top: showBtnClick + header - reservation_filter_height -1});
				}
				calender_list.css({ paddingTop: showBtnClick - reservation_filter_height });
			}
		});

		$(window).on('load resize' , function(){
			scheduleHeaderW(); //予約しぼり込み幅リサイズで再取得
			tableHeaderW(); //カレンダー幅リサイズで再取得
		});

		// position: stickyが使えない場合またはEdgeの場合の処理
		if (!detectSticky() || isEdge) {
			thisTable.find('th').removeClass('sticky');
		}

		//ヘッダー追従時の挙動
		$(window).on('load scroll' , function () {
			var win_top = scr_top();
			var tableTop = table_top();
			if (win_top > tableTop) {
				if (!detectSticky() || isEdge) {
					stickyTable.addClass('fixed');
					calender_list.css({ marginTop: th_height });
				}
				schedule_wrap.css({ top: header }).addClass('fixed');
			} else {
				schedule_wrap.css({ top: 0 }).removeClass('fixed');
				if (!detectSticky() || isEdge) {
					stickyTable.removeClass('fixed');
					calender_list.css({ marginTop: '' });
				}
			}
		});
	}

	commondesign();
}

function cal_month_trigger() {
     calendar_month();
    $(window).trigger('resize');
}