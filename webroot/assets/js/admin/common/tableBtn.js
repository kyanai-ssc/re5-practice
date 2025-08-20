// JavaScript Document
$(function() {
	//編集のアイコン数によってセル幅可変
	var toolIcn_exists = $('td.tool').length;
	if (toolIcn_exists) {
		(function tool_item_width() {
			var toolIcon = new Array();
			$('.cmn-table td.tool').each(function(index) {
				toolIcon[index] = $(this).find('li').length;
			});
			var maxL = Math.max.apply(null,toolIcon);
			if (maxL <= 2) {
				$('.cmn-table th.tool , .cmn-table td.tool').css({
					width:90,
					minWidth:90
				});
			} else {
				$('.cmn-table th.tool , .cmn-table td.tool').css({
					width:maxL * 35,
					minWidth:maxL * 35
				});
			}
		})();
	}
	
	var table_exists = $('.fixedTable-wrap').length;
	if (table_exists) {
		var l_main = $('.l-main'),
				tableH = $('.fixedTableHead thead'),
				tableH_table = $('.fixedTableHead table'),
				tableH_wrap = $('.fixedTableHead'),
				tableL = $('.fixedTableLeft'),
				tableLH = $('.fixedTableLeft thead'),
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
                elmH = Math.trunc(elmH);
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

			$.each($('.fixedTableBody tbody tr'), function(index) {
				var lH = parseInt(left.eq(index).find('td').outerHeight()+35);
				var bH = parseInt(body.eq(index).find('td').outerHeight()+35);
				if (lH > bH){
					left.eq(index).find('td').css({
						height:lH
					});
					body.eq(index).find('td').css({
						height:lH
					});
				} else {
					left.eq(index).find('td').css({
						height:bH
					});
					body.eq(index).find('td').css({
						height:bH
					});
				}
			});
		}
		
		//横スクロールボタン幅設定
		function scr_btn_width() {
			$('.fixedTable-option').css({
				width: width_T - 40
			});
			$(window).on('resize', function(){
				width_T = parseInt(l_main.innerWidth());
				$('.fixedTable-option').css({
					width: width_T - 40
				});
			});
			var table_height = fixedBody.outerHeight();
			if (table_height <= 440) {
				$('.fixedTable-scroll-btn').css({ height: table_height });
			} else {
				var scrollHeight = $(window).height()/2;
				$('.fixedTable-scroll-btn').css({ height: scrollHeight });
			}
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
		
		function scl_btn_fixed(num) {
			width_T = parseInt(l_main.innerWidth());
			var width_T_sum =  width_T - 120 - fixedTableLeftW;
			var fixedBody_w = fixedBody.outerWidth() - num;
			if (width_T_sum === fixedBody_w) {
				$('.fixedTable-arrow').addClass('btn_fixed');
			} else {
				$('.fixedTable-arrow').removeClass('btn_fixed');
			}
		}

		function table_fn() {
			scr_btn_width();

			fixedBody_wrap_fn();
			var fixedBody_wrap = $('.fixedBody_wrap');

			fixedHead_wrap_fn();

			tableH.css({ top: 0 });

			if (tableL.is(':hidden')) {
				//左追従テーブルが無い場合
				tableH_wrap.css({ marginLeft: 40 - 1 });
				fixedBody_wrap.css({ marginLeft: 40 - 1 });
				$(window).on('load resize', function(){
					width_T = parseInt(l_main.innerWidth());
					var width_T_sum =  width_T - 120;
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
				});
			} else {
				//左追従テーブルがある場合
				tableH_wrap.css({ marginLeft: fixedTableLeftW + 40 - 1 });
				fixedBody_wrap.css({ marginLeft: 40 + fixedTableLeftW - 1 });
				tableLH.css({ top: fixedHeaderH + header });
				tableL.css({
					top: 0,
					width: tableLwidth,
					maxWidth: tableLwidth,
					minWidth: tableLwidth
				});
				scl_btn_fixed(41);
				var width_T_sum =  width_T - 120 - fixedTableLeftW;
				fixedBody_wrap.css({
					maxWidth: width_T_sum,
					width: width_T_sum
				});
				tableH_wrap.css({
					maxWidth: width_T_sum,
					width: width_T_sum
				});
				$(window).on('load resize', function(){
					scl_btn_fixed(0);
					var width_T_sum =  width_T - 120 - fixedTableLeftW;
					fixedBody_wrap.css({
						maxWidth: width_T_sum,
						width: width_T_sum
					});
					tableH_wrap.css({
						maxWidth: width_T_sum,
						width: width_T_sum
					});
				});
			}	

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
					tableH_wrap.addClass('tbFixed').css({ top: fixedHeaderH + header });
					$('.fixedTable-option').css({ top: header }).removeClass('tbRela').addClass('tbFixed');
					tableLH.addClass('tbFixed');
					tableL.css({ top: parseInt(tableLH.outerHeight()) });
				} else {
					tableH_wrap.css({ top: '' });
					fixedBody_wrap.css({ marginTop: '' });
					tableH_wrap.removeClass('tbFixed');
					$('.fixedTable-option').css({ top: '' }).addClass('tbRela');
					tableH_table.css({ top: '' });
					tableL.css({ top: 0 });
					tableLH.removeClass('tbFixed');
				}
			});
		}

		function tbl_scroll() {
			////////////     横スクロールボタン     //////////////
			//コマ数・送りの設定
			var feed = 15;
			var frame = 15;

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

			//横幅取得・設定
			var fixedTableLeftW = $('.fixedTableLeft').outerWidth();
			var fixedBody_wrap = $('.fixedBody_wrap');
			if (tableL.is(':hidden')) {
				var tbleWrapW = width_T - 120; //テーブル表示領域
			} else {
				var tbleWrapW = width_T - 120 - fixedTableLeftW; //テーブル表示領域	
			}
			var tbleW = fixedBody.outerWidth(); //非表示領域含めたテーブル全体の幅
			var tblScrlW = parseInt(tbleW - tbleWrapW); //スクロール可能領域

			//カウント初期値
			var count = 0;
			//clearinterval対策
			var setItv = '';

			//スクロール値取得
			fixedBody_wrap.scroll(function() {
				count = $(this).scrollLeft();
				//テーブルヘッダー追従した際も横スクロール位置を維持
				tableH_table.css({ marginLeft: - count });
			});

			//ボタンスタイル初期設定
			$('#left-button').css('opacity', '0.4');

			//移動設定
			var countUp = function() {
				if (checkBA === 'a') {
					var count2 = count + feed;
					fixedBody_wrap.scrollLeft(count2);
				} else {
					var count2 = count - feed;
					fixedBody_wrap.scrollLeft(count2);
				}
				if (!$('.fixedTable-arrow').hasClass('btn_fixed')) {
					if (tableL.is(':hidden')) {
						var tableLimit = tableH_table.outerWidth() - (width_T - 120);
					} else {
						var tableLimit = tableH_table.outerWidth() - (width_T - 120 - fixedTableLeftW);
					}
					//テーブルの端でボタンスタイル変化
					if (count <= 0) {
						$('#left-button').css({
							'opacity': 0.4,
							'pointer-events': 'none'
						});
						$('#right-button').css({
							'opacity': 1,
							'pointer-events': 'auto'
						});
					} else if (count > tableLimit && (count - tableLimit) <= 2) {
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
				} else {
					$('#left-button').css({
						'opacity': '',
						'pointer-events': ''
					});
					$('#right-button').css({
						'opacity': '',
						'pointer-events': ''
					}); 
				}
			};

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
				/*if (count <= tblScrlW) {
					if (setItv.length > 0) {
						clearInterval(setItv.shift());
					}
				}*/
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
						if (count <= tblScrlW) {
							if (setItv.length > 0) {
								clearInterval(setItv.shift());
							}
						}
					}
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
					if (count <= tblScrlW) {
								if (setItv.length > 0) {
										clearInterval(setItv.shift());
								}
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
				/*if (0 <= count) {
					if (setItv.length > 0) {
						clearInterval(setItv.shift());
					}
				}*/
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
					if (0 <= count) {
						clearInterval(setItv);
					}
				}
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
					if (0 <= count) {
							if (setItv.length > 0) {
									clearInterval(setItv.shift());
							}
					}
			});
		}

		//発火
		tdHeight();
		table_fn();
		thHeight();
		$(window).on('resize', function() {
			tbl_scroll();		
		});
		tbl_scroll();	
	}
});
