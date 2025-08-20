// JavaScript Document
function re_month_commondesign() {
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
	//UA判定
	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);
	
	var stickyT = $('.schedule-header');
	
	if (!detectSticky() || isEdge) {
		
	} else {
		
	}
}

function re_month_pcdesign() {
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
	//UA判定
	var ua = navigator.userAgent.toLowerCase(),
			isEdge = (ua.indexOf('edge') > -1),
			isFirefox = (ua.indexOf('firefox') > -1);
	
	var thisTable = $('.month_table'),
			stickyT = $('.schedule-header'),
			calenderList = $('#calender_list'),
			stickyTable = thisTable.find('.stickyTable'),
			table_th = thisTable.find('th');
	if (!detectSticky() || isEdge) {
		//IE
		stickyTable.css({ top: stickyT.outerHeight() });
		table_th.removeClass('sticky');
		if(isEdge) {
			$('.schedule-header').removeClass('sticky');
		}
		function table_th_width_PCTB() {
			var calenderList = $('#calender_list'),
					tableWidth = calenderList.outerWidth(),
					table_td = thisTable.find('td'),
					table_th = thisTable.find('th'),
					tdW = (tableWidth / 7);
			table_th.css({
				width: tdW,
				minWidth: tdW,
				maxWidth: tdW
			});
			if(isEdge) {
				$('.schedule-header').css({ width: tableWidth });
			}
		}
		table_th_width_PCTB();
		$(window).on('load resize' , function() {
			table_th_width_PCTB();
		});
		
		$(window).on('load scroll' , function(){
			var tableTop = parseInt($('.l-calendar').offset().top + 30),
					scrollTop = $(window).scrollTop();
			if (scrollTop > tableTop) {
				//追従
				stickyTable.css({ top: stickyT.outerHeight()}).addClass('fixed');
				if(isEdge) {
					$('.schedule-header').addClass('fixed');
					var scheduleHeight = parseInt($('.schedule-header').outerHeight() + stickyTable.outerHeight());
					calenderList.css({ paddingTop: scheduleHeight });
				}
			} else {
				//追従解除
				stickyTable.removeClass('fixed');
				if(isEdge) {
					$('.schedule-header').removeClass('fixed');
					calenderList.css({ paddingTop: '' });
				}
			}
		});
		
	}  else {
		//IE以外
		table_th.css({ top: stickyT.outerHeight() }).addClass('sticky');
	}
}

function re_month_spdesign() {
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
	//UA判定
	var ua = navigator.userAgent.toLowerCase(),
		isEdge = (ua.indexOf('edge') > -1);
	
	var thisTable = $('.month_table'),
			stickyT = $('.schedule-header'),
			calenderList = $('#calender_list'),
			stickyTable = thisTable.find('.stickyTable'),
			table_th = thisTable.find('th');
	calenderList.css({ overflowY: '' });
	if (!detectSticky() || isEdge) {
		stickyTable.css({ top: stickyT.outerHeight() });
		table_th.removeClass('sticky');
		if(isEdge) {
			stickyT.removeClass('sticky');
		}
		function table_th_width_SP() {
			var calenderList = $('#calender_list'),
				tableWidth = calenderList.outerWidth(),
				table_td = thisTable.find('td'),
				table_th = thisTable.find('th'),
				tdW = tableWidth / 7;
			table_th.css({
				width: tdW,
				minWidth: tdW,
				maxWidth: tdW
			});
			table_td.css({
				width: tdW,
				minWidth: tdW,
				maxWidth: tdW
			});
			if(isEdge) {
				stickyT.css({ width: tableWidth });
			}
		}
		table_th_width_SP();
		$(window).on('load resize' , function() {
			table_th_width_SP();
		});
		$(window).on('load scroll' , function(){
			var tableTop = parseInt($('.l-calendar').offset().top),
				scrollTop = $(window).scrollTop();
			if (scrollTop > tableTop) {
				//追従
				stickyTable.css({ top: stickyT.outerHeight() }).addClass('fixed');
				if(isEdge) {
					stickyT.addClass('fixed');
					var scheduleHeight = parseInt(stickyT.outerHeight() + stickyTable.outerHeight());
					calenderList.css({ paddingTop: scheduleHeight });
				}
			} else {
				//追従解除
				stickyTable.removeClass('fixed');
				if(isEdge) {
					stickyT.removeClass('fixed');
					calenderList.css({ paddingTop: '' });
				}
			}
		});
	} else {
		table_th.addClass('sticky').css({ top: stickyT.outerHeight() });
	}
	$(document).on('click', '.btn-colorTip', function() {
    if($('.colorTip').hasClass('is-open')) {
			stickyT = $('.schedule-header');
		} else {
			stickyT = $('.schedule-header');
		}
		if (!detectSticky() || isEdge) {
			stickyTable.css({ top: stickyT.outerHeight() });
		} else {
			table_th.css({ top: stickyT.outerHeight() });
		}
  });
}

function list_fn_pctb() {
	$('.list-typeA .list_body_line_wrap').each(function(i, elem) {
		var baseElm = $(this).find('.list_body_line').find('li:first-child'),
				baseElmLater = $(this).find('.list_body_line').find('li:last-child'),
				changeElm = $(this).find('.b-status');
		baseElmLater.after(changeElm);
	});
}

function list_fn_sp() {
	$('.list-typeA .list_body_line_wrap').each(function(i, elem) {
		var baseElm = $(this).find('.list_body_line').find('li:first-child'),
				changeElm = $(this).find('.b-status');
		baseElm.after(changeElm);
	});	
}

function re_month_trigger() {
	if ($(window).innerWidth() >= 768) {
		re_month_pcdesign();
	} else {
		re_month_spdesign();
	}
    $(window).trigger('resize, scroll');
	//発火タイミング
	axia.addEventListener( 'breakpoints', function( e ){
		if (e['breakpoint'] === PCw) {
			//PC
			re_month_pcdesign();
			list_fn_pctb();
		} else if (e['breakpoint'] === TBw) {
			//TB
			re_month_pcdesign();
			list_fn_pctb();
		} else {
			//SP
			re_month_spdesign();
			list_fn_sp()
		}
	});
}

function re_month_list_trigger() {
	if ($(window).innerWidth() >= 768) {
		list_fn_pctb();
	} else {
		list_fn_sp();
	}
}