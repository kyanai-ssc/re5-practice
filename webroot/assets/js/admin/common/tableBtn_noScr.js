// JavaScript Document
/*

	横スクロールボタン無しのヘッダー追従テーブル
	
*/
$(function() {
	
	var table_exists = $('.fixedTable-wrap').length;
	if (table_exists) {
		//変数
		var l_main = $('.l-main'),
				tableH = $('.fixedTableHead thead'),
				tableH_table = $('.fixedTableHead table'),
				tableH_wrap = $('.fixedTableHead'),
				tableL = $('.fixedTableLeft'),
				tableLH = $('.fixedTableLeft thead'),
				fixedBody_wrap = $('.fixedBody_wrap'),
				header = parseInt($('.cmn-header').outerHeight()),
				fixedBody = $('.fixedTableBody'),
				fixedHeaderH = parseInt($('.fixedTable-option').outerHeight()),
				tableLwidth = parseInt($('.fixedTableBody td.fixedElm').outerWidth()),
				width_T = parseInt(l_main.innerWidth()),
				fixedTableWrap = $('.fixedTable-wrap'),
				scrollTop = 0,
				fixedTableLeftW = $('.fixedTableLeft').outerWidth();

		//thの高さを合わせる
		function thHeight() {
			var elmH;
			if (($('.fixedElm').outerHeight()) > ($('.fixedTableHead th').outerHeight())) {
				elmH = $('.fixedElm').outerHeight();
			} else {
				elmH = $('.fixedTableHead th').outerHeight();
			}
			$('.fixedElm, .fixedTableHead th, .fixedTableHead, .fixedHead_wrap').css({ height:elmH });
			tableH_table.css({ height:elmH });
		}

		//tdの高さを合わせる
		function tdHeight() {
			var left =  tableL.find('tbody tr');
			var body =  fixedBody.find('tbody tr');
			var elmD;

			$.each($('.fixedTableBody tbody tr'), function(index) {
				var lH = left.eq(index).find('td').outerHeight()+20;
				var bH = body.eq(index).find('td').outerHeight()+20;
				if (lH > bH){
					left.eq(index).find('td').css({
						height:parseInt(lH)
					});
					body.eq(index).find('td').css({
						height:parseInt(lH)
					});
				} else {
					left.eq(index).find('td').css({
						height:parseInt(bH)
					});
					body.eq(index).find('td').css({
						height:parseInt(bH)
					});
				}
			});
		}

		//テーブルのTOP位置取得
		function tableTop_fn() {
			return parseInt(fixedTableWrap.offset().top) - header;
		}

		//ブラウザのスクロールトップ位置取得
		function scr_top() {
			return $(window).scrollTop();
		}

		//.fixedBody_wrapにwrap
		function fixedBody_wrap_fn() {
			fixedBody.wrap('<div class="fixedBody_wrap"></div>');
		}

		//.fixedHead_wrapにwrap
		function fixedHead_wrap_fn() {
			tableH_wrap.wrap('<div class="fixedHead_wrap"></div>');
		}
		
		function table_width_fn() {
			width_T = parseInt(l_main.innerWidth());
			var width_T_sum =  width_T - 40;
			fixedBody_wrap.css({
				maxWidth: width_T_sum,
				width: width_T_sum
			});
			tableH_wrap.css({
				maxWidth: width_T_sum,
				width: width_T_sum
			});
			var fixedBody_w = fixedBody.outerWidth();
			if (width_T_sum == fixedBody_w) {
				$('.fixedTable-arrow').addClass('btn_fixed');
			} else {
				$('.fixedTable-arrow').removeClass('btn_fixed');
			}
		}

		function table_fn() {

			fixedBody_wrap_fn();
			var fixedBody_wrap = $('.fixedBody_wrap');

			fixedHead_wrap_fn();

			tableH.css({ top: 0 });
			$(window).on('load scroll', function(){
				//パネルの開閉ボタンクリックでテーブル位置再取得
				var tableTop = tableTop_fn();
				$('.ttl-panel-show .showBtn').click(function() {
					if ($('.showBtn').hasClass('opend')) {
						tableTop = tableTop_fn();
					} else {
						tableTop = tableTop_fn();
					}
				});

				//スクロールごとにスクロール位置取得
				scrollTop = scr_top();

				//スクロール位置がテーブルヘッダーを超えたとき
				if (scrollTop > tableTop) {
					//追従テーブルヘッダーfixedで追従
					tableH_wrap.addClass('tbFixed').css({ top: header });
					$('.fixedTable-option').css({ top: header }).removeClass('tbRela').addClass('tbFixed');
				} else {
					tableH_wrap.css({ top: '' });
					fixedBody_wrap.css({ marginTop: '' });
					tableH_wrap.removeClass('tbFixed');
					$('.fixedTable-option').css({ top: '' }).addClass('tbRela');
					tableH_table.css({ top: '' });
				}
			});
		}
		
		//発火
		tdHeight();
		table_fn();
		thHeight();
		table_width_fn();
		$(window).on('resize', function() {
			table_width_fn();
		});
	}
});
