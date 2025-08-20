/*JavaScript Document*/ 

function day_day_commondesign() {
	var table = $('#calender_list table.dayTable.type_day:not(.type_week)'),
			thisTable = $('.dayTable.type_day:not(.type_week)'),
			stickyTable = thisTable.find('.stickyTable'),
			scheduleHeader = $('.schedule-header'),
			calenderList = $('#calender_list'),
			tableH = thisTable.find('.stickyTable th'),
			lCalendar = $('.l-calendar'),
			thHeight = thisTable.find('.stickyTable th').height(),
			tableTop,
			scrollTop;

	//ブラウザ判定
	var ua = navigator.userAgent.toLowerCase(),
			isEdge = (ua.indexOf('edge') > -1);

	// position: stickyがブラウザで使えるかチェックするための関数
	function detectSticky() {
		const div = document.createElement('div');
		div.style.position = 'sticky';
		return div.style.position.indexOf('sticky') !== -1;
	}
	if (!detectSticky() || isEdge) {
		tableH.removeClass('sticky');
		if(isEdge) {
			scheduleHeader.removeClass('sticky');
		}
	}
	$(window).on('load scroll' , function() {
		tableTop = parseInt(lCalendar.offset().top + 30);
		scrollTop = $(window).scrollTop();
		if (scrollTop > tableTop) {
			if (!detectSticky() || isEdge) {
				var scheduleHeight = parseInt(scheduleHeader.outerHeight());
				stickyTable.css({ top: scheduleHeight }).addClass('fixed');
				if(isEdge) {
					scheduleHeader.addClass('fixed');
					calenderList.css({ paddingTop: scheduleHeight + thHeight});
				}
			} else {
				tableH.css({ top: scheduleHeader.outerHeight() });
			}
		} else {
			if (!detectSticky() || isEdge) {
				stickyTable.removeClass('fixed');
				if(isEdge) {
					scheduleHeader.removeClass('fixed');
					calenderList.css({ paddingTop: '' });
				}
			}
		}
	});
	
	$(window).on('load resize' , function() {
		var tableWidth = $(window).innerWidth();
		thisTable.find('.stickyTable th:not(:first-child)').css({ width: tableWidth });
	});
}

function day_day_tableWidth_fn(num) {
	var thisTable = $('.dayTable.type_day:not(.type_week)'),
			scheduleHeader = $('.schedule-header'),
			tableH = thisTable.find('.stickyTable th');
	//ブラウザ判定
	var ua = navigator.userAgent.toLowerCase(),
			isEdge = (ua.indexOf('edge') > -1);

	// position: stickyがブラウザで使えるかチェックするための関数
	function detectSticky() {
		const div = document.createElement('div');
		div.style.position = 'sticky';
		return div.style.position.indexOf('sticky') !== -1;
	}
	if (!detectSticky() || isEdge) {
		var tableWidth = $('.l-main').innerWidth() - num;
		if(isEdge) {
			scheduleHeader.css({ width: tableWidth });
		}
		tableH.css({ width: tableWidth / 2 });
		$(window).on('load resize' , function() {
			var tableWidth = $('.l-main').innerWidth() - num;
			if(isEdge) {
				scheduleHeader.css({ width: tableWidth });
			}
			tableH.css({ width: tableWidth / 2});
		});
	} else {
		$(window).on('load resize' , function() {
			tableH.css({ width: 'auto' });
		});
	}
}

function day_day_tableWidth_fn_sp() {
	var thisTable = $('.dayTable.type_day:not(.type_week)'),
			scheduleHeader = $('.schedule-header'),
			tableH = thisTable.find('.stickyTable th');
	//ブラウザ判定
	var ua = navigator.userAgent.toLowerCase(),
			isEdge = (ua.indexOf('edge') > -1);

	// position: stickyがブラウザで使えるかチェックするための関数
	function detectSticky() {
		const div = document.createElement('div');
		div.style.position = 'sticky';
		return div.style.position.indexOf('sticky') !== -1;
	}

	// .stickyが指定されている要素に対してposition: stickyを適用させる関数
	function callStickyState() {
		return new StickyState(thisTable.find('.sticky'));
	}
	$(window).on('load resize' , function() {
		var tableWidth = $(window).innerWidth();
		if(isEdge) {
			scheduleHeader.css({ width: tableWidth });
		}
		//tableH.css({ width: tableWidth });
	});
}

function re_day_day_PC() {
	day_day_commondesign();
	day_day_tableWidth_fn(40);
}
function re_day_day_TB() {
	day_day_commondesign();
	day_day_tableWidth_fn(20);
}
function re_day_day_SP() {
	day_day_commondesign();
	day_day_tableWidth_fn_sp();
}

function re_day_day_trigger() {
	//ブラウザ判定
	if ($(window).innerWidth() >= 1025) {
		re_day_day_PC();
	} else if ($(window).innerWidth() >= 768) {
		re_day_day_TB();
	} else {
		re_day_day_SP();
	}
	
	$(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
		if (e['breakpoint'] === PCw) {
			//PC
			re_day_day_PC();
		} else if (e['breakpoint'] === TBw) {
			//TB
			re_day_day_TB();	
		} else {
			//SP
			re_day_day_SP();
		}
	});
}



