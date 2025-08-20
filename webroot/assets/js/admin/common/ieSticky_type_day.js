// JavaScript Document
function calendar_day() {
	//グローバル変数
	var l_main = $('.l-main'),
			thisTable = $('.type_day:not(.type_time)'),
			calender_list = $('#calender_list'),
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
			scrollTop,
			tableW = l_main.width(),
			th_first_width = 120;
	
	//スケジュールテーブルの幅取得
	function scheduleHeaderW() {
		width_T = parseInt(l_main.innerWidth() - 40);
		schedule_wrap.css({ width: width_T });
	}

	//テーブルセル可変箇所の幅取得
	function calender_listW() {
	 var w = parseInt(tableW - th_first_width);
	 thisTable.find('th:not(:first-child)').css({
		 width: tableW - th_first_width,
		 maxWidth: tableW - th_first_width,
		 minWidth: tableW - th_first_width
	 });
	 thisTable.find('td:not(:first-child)').css({
		 width: w,
		 maxWidth: w,
		 minWidth: w
	 });
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
	var ua = navigator.userAgent.toLowerCase();
	var isEdge = (ua.indexOf('edge') > -1);

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
					stickyElm.css({ top: showBtnClick + header + reservation_filter_height -1 });
				}
				calender_list.css({ paddingTop: showBtnClick + reservation_filter_height });
			} else {
				if (!detectSticky() || isEdge) {
					stickyTable.css({ top: showBtnClick + header - reservation_filter_height });
				} else {
					stickyElm.css({ top: showBtnClick + header - reservation_filter_height -1 });
				}
				calender_list.css({ paddingTop: showBtnClick - reservation_filter_height });
			}
		});

		//予約しぼり込み幅リサイズで再取得
		$(window).on('load resize' , function(){
			scheduleHeaderW();
		});

		// position: stickyが使えない場合またはEdgeの場合の処理
		if (!detectSticky() || isEdge) {
			thisTable.find('th').removeClass('sticky');
			thisTable.find('th:first-child').css({
			 width: th_first_width,
			 maxWidth: th_first_width,
			 minWidth: th_first_width
			});
			thisTable.find('td:first-child').css({
			 width: th_first_width,
			 maxWidth: th_first_width,
			 minWidth: th_first_width
			});

			//カレンダー幅リサイズで再取得
			calender_listW();
			$(window).on('load resize' , function(){
				calender_listW();
				tableW = l_main.width()
				thisTable.find('th:not(:first-child)').css({ width: tableW});
			});
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

function cal_day_trigger() {
     calendar_day();
    $(window).trigger('resize');
}