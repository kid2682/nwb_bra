function item_masonry() {
    if((def.module=="cate" || def.module=="search") && def.masonry == 1){
        var cWidth = $(window).width();
    	var w  = 241*Math.floor(cWidth/241)-15;
    	$(".wrapper,.content,.masonry").width(w);
        $(window).resize(function(){
            cWidth = $(window).width();
        	w  = 241*Math.floor(cWidth/241)-15;
            if(w<949){return;}
        	$(".wrapper,.content,.masonry").width(w);
            $('.infinite_scroll').masonry({
                itemSelector: '.masonry_brick',
            	columnWidth: 226,
            	gutterWidth: 15
           	});
        });
    }  
    
    
    var $container = $('.infinite_scroll');
	$container.masonry({
	   		itemSelector: '.masonry_brick',
    		columnWidth: 226,
    		gutterWidth: 15
	 });
	$container.imagesLoaded( function(){
	  $container.masonry({
	   		itemSelector: '.masonry_brick',
    		columnWidth: 226,
    		gutterWidth: 15
	  });
	});
}
/**/
//商品列表
$(function() {
	function user_callback() {
		if (def.user_id == null || def.uid != def.user_id) return;
		function append() {
			$('.item_list .item').append("<div class='del'></div>").hover(function() {
				$('.del', this).show();
			},
			function() {
				$('.del', this).hide();
			});
		}
		function handler(url) {
			$('.item_list .del').click(function() {
				if (confirm("确定要删除吗?")) {
					var id = $(this).parent().attr('iid');
					$.post(url, {
						id: id
					},
					function(data) {
						if (data.data > 0) {
							window.location.href = "";
						} else {
							alert("删除错误!");
						}
					},
					"json");
				}
			});
		}
		if (def.module == "uc" && def.action == 'like') {
			append();
			handler(def.root + "index.php?act=del&m=uc&a=like");
		} else if (def.module == "album" && def.action == "details") {
			append();
			handler(def.root + "index.php?act=del&m=album&a=items&pid=" + $.url().param('id'));
		}
	}
	function item_callback() {
	//伪本地化图片
		if(def.local_images==1){
			$('.no_encode_url').each(function() {
				var url = $(this).attr('url') || "";
				var tag = $(this).attr('tagName').toLowerCase();
				if (tag == 'img') {
					$(this).attr('src', url);				
				} else if (tag == 'a') {
					$(this).attr('href', base64_decode(url));
				}
			});
		}
		else{
			$('.encode_url').each(function() {
				var url = $(this).attr('url') || "";
				var tag = $(this).attr('tagName').toLowerCase();
				if (tag == 'img') {
					$(this).attr('src', base64_decode(url));				
				} else if (tag == 'a') {
					$(this).attr('href', base64_decode(url));
				}
			});
		}		
		user_callback();
		//album_callback();
		//item_comment_callback();

		$('.item').mouseover(function() {
			$('.btns', this).show();
		}).mouseout(function() {
			$('.btns', this).hide();
		});
		item_masonry();
		jq_corner();
	}

	item_callback();

	$('.item').fadeIn("fast");
	var sp = 1;
	$(".infinite_scroll").infinitescroll({
		navSelector: "#more",
		nextSelector: "#more a",
		itemSelector: ".item",
		bufferPx: 500,
		loading: {
			img: def.root + "statics/images/masonry_loading.gif",
			msgText: "加载更多商品",
			finishedMsg: '',
			finished: function() {
				sp++;
				if (sp >= def.waterfall_sp) {
					$("#more").remove();
					$("#infscr-loading").hide();
					$("#page").show();
					$(window).unbind('.infscr');
				}
			}
		},
		errorCallback: function() {
			$("#page").show();
		}
	},
	function(newElements) {
		var $newElems = $(newElements);
		$('.infinite_scroll').masonry('appended', $newElems, false);
		$newElems.fadeIn(100);
		item_callback();
		return;
		$newElems.find('.img img').lazyload({
			threshold: 300,
			effect: "fadeIn",
			placeholder: def.root + "statics/images/grey.gif"
		});
	});
});

//商品喜欢
$(function() {
	$(".like_it,.img_like_btn,.like_btn").live("click",
	function() {
	    var t = $(this);
		var iid = $(this).attr('iid');

		var btn = $(this);
		$.get(def.root + "index.php?m=uc&a=like&act=add", {
			id: iid
		},
		function(data) {
			if (data.data == 'not_login') {
				login();
				return;
			} else if (data.data == 'yet_exist') {
				//tips(btn.parent(), "已经喜欢过啦！ <span>[<a href=\"javascript:void(0);\" onclick=\"like_del(this)\" id=" + iid + "> 删除  </a>]</span>", 'yes');
				//messagebox('您已经喜欢过了哦！');
                t.parent().siblings(".like_already").html("喜欢过了").fadeIn().fadeOut(3000);
                t.parent().siblings(".like_already_list").html("喜欢过了").fadeIn().fadeOut(3000);
				return;
			} else if (data.data > 0) {
				$("#like_num_" + iid).html(parseInt($("#like_num_" + iid).html()) + 1);
                t.parent().siblings(".like_already").html("喜欢+1").fadeIn().fadeOut(3000);
                t.parent().siblings(".like_already_list").html("喜欢+1").fadeIn().fadeOut(3000);
				return;
			}
		},
		'json');
	});
});
//删除喜欢
function like_del(ele) {
	var iid = ele.id;
	var btn = $(this);
	if (def.user_id == null) {
		login();
		return;
	}
	$.post(def.root + "index.php?act=del&m=uc&a=like", {
		id: iid
	},
	function(data) {
		if (data.data > 0) {
			//隐藏弹出框  tips()函数中创建的俩个div
			$('.tips').fadeOut();
			$('.append').remove();
			
			$("#like_num_" + iid).html(parseInt($("#like_num_" + iid).html()) - 1);
		} else {
			messagebox('对不起删除失败！!', 'error');
		}
	},
	"json");
}
//重新加载页面
function reloadpage() {
	item_masonry();
}

//获取评论
//function item_comment_callback(){
var comment_content = [];
var i = 0;
var comment = [];
var info;
var plhtml;
var plnumber;
//var width = ($(window).width()-32)/2;
//var height = ($(window).height()-32)/2;
$(function() {
	$('.item_commnets').live("click",
	function() {
	    $(".item_commnets").unbind( "click" );
		var item = $(this).parent().parent().parent();
        var height = item.height();
        var width = item.width();
        
        var iid = $(this).attr('iid');
		var btn = $(this);
		if (def.user_id == null) {
			login();
			return;
		}
        /*
        item.append("<div class='loading'></div>");
		$('.loading').css({
		  "height":height+"px",
		  "width":width+"px"
		});
        $('.loading').remove();
        */
        var comments = $("#comment_"+iid);
        comments.toggle();
        $(".plcontent_"+iid).focus();
        item_masonry();
        
		//$.post(def.root + "index.php?m=cate&a=comment",function(data) {
		  /*
            
			try {
				var error = eval("(" + data + ")");
				if (error.data == 'not_login') {
					login();
					return;
				}
			} catch(e) {}

			var item_commnet_add_dialg = art.dialog({
				title: '评论',
				id: 'item_commnet_add_dialg',
				content: data,
				lock: true
			});
            */
			$(".plcontent_"+iid).keydown(text).keyup(text);
			function text() {
				info = $(this).val();
				$(this).val(info.substring(0, 100));
				plnumber = 100 - parseInt(info.length) < 0 ? 0 : 100 - parseInt(info.length);
				plhtml = "您还可以输入" + plnumber + "个汉字";
				$(".plcount_"+iid).html(plhtml);
			}
			$('.thumb').html(btn.parent().parent().siblings('.item_t').children('.img').children('a').html());

            //$('.onsubmit').bind("click");
			$('.onsubmit').unbind('click').bind("click",function() {

				//info = $('.plcontent').val();
                //alert($('.onsubmit').parent().parent().parent().siblings().html());return;
				var t = $(this);
                var pid = iid;
				var type = document.URL;
				//var cm = parseInt($('#cm_' + pid).html().replace('(', '').replace(')', ''));
                var cm = parseInt($('#cm_' + pid).html());
				cm++;
				if (!info) {
					messagebox('评论不能为空!', 'error');
					return false;
				}
				if (comment_content[pid] == pid + info) {
					messagebox('不能重复提交相同内容！!', 'error');
					return;
				}
				if (Date.parse(new Date()) - comment[def.uid] < (def.comment_time * 1000)) {
					messagebox('说话太快了，先歇歇吧!', 'error');
					return;
				}
                item.append("<div class='loading'></div>");
    			$('.loading').css({
    				"height":height+"px",
    				"width":width+"px"
    			});
				//item_commnet_add_dialg.close();
				$.post(def.root + "index.php?m=cate&a=doComment", {
					info: info,
					pid: pid,
					type: type
				},
				function(data) {
					if (data == 0) {
						messagebox('评论失败！!', 'error');
                        $('.loading').remove();
						return;
					}
	                $(".plcontent_"+iid).val("");
                    $(".plcount_"+iid).html("100/100");
					//item_commnet_add_dialg.close();
					comment_content[pid] = pid + info;
					comment[def.uid] = Date.parse(new Date());
					$('#cm_' + pid).html(cm);
                    
					t.parent().parent().parent().siblings(".clearfix:first").after('<div class="clearfix comm_share"><div class="avatar"><a href="' + def.root + 'index.php?a=index&amp;m=uc&amp;uid=' + def.user_id + '"><img width="32px" height="32px" uid="' + def.user_id + '" class="tipuser" src="' + data.data.face + '"></a></div><p><a href="' + def.root + 'index.php?a=index&amp;m=uc&amp;uid=' + def.user_id + '"><em>' + data.data.name + '</em></a><span>' + info + '</span></p></div>');
					reloadpage();
                    $('.loading').remove();
                    
					return;
				},
				'json');
                
			});
            
		//});

	});
});

//程序跳转弹出提示
/*
 * $(function(){	
	$('.jump').click(function(){		
		var item_id=$(this).attr('item_id');	
		var seller_name=$(this).attr('seller_name');
		var seller_logo=$(this).attr('seller_logo');
		if (def.user_id == null){
			$.post(def.root+'index.php?m=item&a=jump',{id:item_id,seller_name:seller_name,seller_logo:seller_logo},function(data){ 
				var jump_dialog=art.dialog({ 
					id:'jump',
					title:'您还尚未登录，只有登录后购买才能获得返利！',
					content:data,
					lock:true,	
					beforeunload: function () {
						window.location.href=def.root+"index.php?m=item&a=jumppage&id="+item_id;
				    }

				});
				$('#fast_login').click(function(){
					var name=$.trim($('#login_username').val());
					var passwd=$.trim($('#login_password').val());					
					$.post(def.root+'index.php?m=uc&a=login',{name:name,passwd:passwd},function(data){ 
						data=data.data;
						if(data.err=="0"){ 
							$('.hint').html(data.msg);
							return;
						}						
						jump_dialog.close();
						window.location.href=def.root+"index.php?m=item&a=jumppage&id="+item_id;
						//执行跳转操作
					},'json');
				});
				
				
			});
		}
		else{
			//直接跳转
			window.location.href=def.root+"index.php?m=item&a=jumppage&id="+item_id;
		}	
	
	});	
})
 * */
