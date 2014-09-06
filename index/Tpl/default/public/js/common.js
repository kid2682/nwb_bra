var Browser_Name = navigator.appName;
var Browser_Version = parseFloat(navigator.appVersion);
var Browser_Agent = navigator.userAgent;
var Actual_Version, Actual_Name;
var is_IE = (Browser_Name == "Microsoft Internet Explorer");

if(is_IE) {
	var Version_Start = Browser_Agent.indexOf("MSIE");
	var Version_End = Browser_Agent.indexOf(";", Version_Start);
	Actual_Version = Browser_Agent.substring(Version_Start + 5, Version_End);
	Actual_Name = Browser_Name;
	if(Browser_Agent.indexOf("Maxthon") != -1) {
		Actual_Name += "(Maxthon)";
	}
}
function addBookmark(title,url) 
{
	if($.browser.webkit){ 
		alert("请用CTRL+D收藏本网页！");
		return true;
	}
    if (window.sidebar){ 
        window.sidebar.addPanel(title, url,""); 
    } 
    else if( document.all ){
        window.external.AddFavorite( url, title);
    } 
    else if( window.opera && window.print ){
        return true;
    }
}
function SetHome(obj,url){ 
	if($.browser.webkit){ 
		alert("请手动设置，webkit浏览器暂不支持！");
		return true;
	}
	try{ 
		obj.style.behavior='url(#default#homepage)';
		obj.setHomePage(url); 
	} 
	catch(e){ 
		if(window.netscape) { 
			try { 
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); 
			} 
			catch (e) { 
				alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。"); 
			} 
			var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch); 
			prefs.setCharPref('browser.startup.homepage',url); 
		 } 
	} 
} 
function jq_corner(){ 
	try{ 
		$('.jq_corner').corner();	
	}catch(e){}
}

$(function(){ 
	$('input[type="text"],textarea').focus(function(){ 
		$(this).addClass('input_on');
	}).blur(function(){
		$(this).removeClass('input_on');
	});
	jq_corner();
});
//禁止选择一级分类
function check_cate(obj){ 
	var level=parseInt($("option:selected",$(obj)).attr('level'));
	var pid=parseInt($("option:selected",$(obj)).attr('pid'));
	if(pid==0||level==0||level==1){		
		messagebox('请选择三级分类！','error');
		$('option[value="0"]',$(obj)).attr('selected','selected');
	}
}
//记录点击前url
$(function(){ 
	uc_share(".top_share");
	$('.url_cookie').click(function(){ 
		$.cookie('redirect',window.location.href,{path:'/'});
	});
	$('.login_list a').click(function(){ 
		$.cookie('redirect',window.location.href,{path:'/'});	
	});	
 
    $(window).scroll(function(){
		$(window).scrollTop()>0 ? $("#gotopbtn").css('display',''): $("#gotopbtn").css('display','none');
	});
    $("#gotopbtn").click(function(){
        var goTop=setInterval(function(){
            $(window).scrollTop($(window).scrollTop()/3);
            if($(window).scrollTop()<1){clearInterval(goTop)};
        },10);
	}) ;
});

//提示框
var hMessagebox;
function messagebox(s,cls){ 
	cls=typeof cls=='undefined'?'yes':cls;
	var html="<div class='d-"+cls+"'>"+s+"</div>";
	hMessagebox=art.dialog({
		id:'messagebox',
		title:false,
		content:html,
		lock:true
	});	
	hMessagebox.time(2000);
}
function login(){ 
	if(parseInt(def.user_id)>0)return true;
	$.post(def.root+'index.php?m=uc&a=login_dialog',function(data){ 
		art.dialog({ 
			id:'login',
			title:'登录',
			content:data,
			lock:true
		});
		var context=$('#login_dialog');
		$('.login_list a').click(function(){ 
			$.cookie('redirect',window.location.href,{path:'/'});								  
		});
		$('.submit',context).click(function(){ 
			var name=$.trim($('#name',context).val());
			var passwd=$.trim($('#passwd',context).val());
			var verify=$.trim($('#verify',context).val());
			
			$.post(def.root+'index.php?m=uc&a=login',{name:name,passwd:passwd,verify:verify},function(data){ 
				data=data.data;
				if(data.err=="0"){ 
					$('.hint',context).html(data.msg);
					return;
				}
				window.location.href=document.URL;
			},'json');
		});
		return false;	
	});
}
//搜索
function check_search(obj){ 
	var context=$(obj);
	var keywords=$.trim($("input[name='keywords']",context).val());
	if(keywords.length==0){
		alert('请输入搜索名称');
		return false;
	}	
	window.location.href=def.root+"index.php?m=bsearch&keywords="+encodeURIComponent(keywords);
	return false;
}
//创建提示
function tips(context,msg,err){ 	
	var html="<div class='append'><div class='tips'><div class='tips_content'>"+msg+"</div></div></div>";
	$('.append',context).remove();
	context.append(html);
	$('.tips_content',context).addClass(err);
	$('.tips',context).show();
	//return;
	setTimeout(function(){ 
		$('.tips',context).fadeOut();
		$('.append',context).remove();
	},3000);
}
/*
分享宝贝
*/
function uc_share(mixed){
	var context=$(mixed);
	function _callback(){ 
		$('.close',context).click(function(){ 
			$('.dialog',context).hide();											 
		});
		$('.submit',context).click(function(){ 
			var submit=$(this);
			
			submit.addClass('on').attr('disabled','disabled');
			$('.hint',context).html('宝贝信息抓取中…').show();
			var url=$.trim($('.url',context).val());
			if(url.length==0){ 
				$('.hint',context).html("<span class='error'>请输入网址!</span>");
				submit.removeClass('on').attr('disabled','');
				return;
			}
			$.post(def.root+'index.php?m=uc&a=items_collect',{url:url},function(data){				
				data=data.data;
				submit.removeClass('on').attr('disabled','');
				if(data.code){
					$('.hint',context).html("<span class='error'>配置错误("+data.msg+")!</span>");
					return;
				}					
				if(data.err=='yet_exist'){ 
					$('.hint',context).html("<span class='error'>您已经分享过改商品了，请分享其他商品吧！</span> ");
					return;
				}
				if(data.err=='share_yes'){ //分享成功					
					window.location.href=def.root+"index.php?m=uc&a=share";
					return;
				}
				if(data.err=='remote_not_exist'){					
					$('.hint',context).html("<span class='error'>抓取失败，网址错误，请输正确的商品详情网址!</span>");
					return;
				}			
				$('.hint',context).hide();
				$('.dialog',context).hide();

				data.user_id=def.user_id;
				$.post(def.root+'index.php?m=uc&a=share_result_dialog',function(content){ 
					var share_result_dialog=art.dialog({
						id:'share_result_dialog',
						title:'嗯~ 就是它吧',
						content:content,
						lock:true
					});			
					
					$('#share_result_dialog .title').html(data.title);
					$('#share_result_dialog .tags').val(data.tags);
					$('#share_result_dialog .img').html("<img src='"+data.img+"' width=\"200\" />");
					
					var dialog=$('#share_result_dialog');
					
					$('.commit',dialog).click(function(){ 
						var parent_cid=parseInt($("#share_result_dialog #parent_cate").val());
						var second_cid=parseInt($("#share_result_dialog #second_cate").val());
						if(parent_cid==0){ 
							messagebox('请选择一级分类！','error');
							return;
						}
						if(second_cid==0){ 
							messagebox('请选择二级分类！','error');
							return;
						}						
						$(this).attr('disabled','disabled');
						data.cid=$("select[name='cid']",dialog).val();
						data.tags=$(".tags",dialog).val();
						data.remark=$(".remark",dialog).val();
						//console.log(data);return;
						$.post(def.root+'index.php?m=uc&a=share&act=add',data,function(data){
							data=data.data;
							if(data=='success'){
								share_result_dialog.close();
								window.location.href=def.root+"index.php?m=uc&a=share";
							}
							$(this).attr('disabled','');
						},'json');							  
					});						
				});
			},'json');											   
		});		
	}	
    //分享宝贝
	$('.button,.uc_share_btn',context).click(function(){
		$.post(def.root+'index.php?m=uc&a=share_dialog',function(data){
			var html="<div class='append'>"+data+"</div>";
			$(".append",context).remove();
			context.append(html);
			_callback();
			if($('.dialog:visible',context).size()>0){ 
				$('.dialog',context).hide();
			}else{ 
				$('.dialog',context).show();	
			}			
		});

	});	
	
    //推荐您的宝贝
    $("#button").click(function(){
        if(def.user_id==null){ 
			login();
			return;	
		}
        $.post(def.root+'index.php?m=uc&a=share_dialog',function(data){
			var html="<div class='append'>"+data+"</div>";
			$(".append",context).remove();
			context.append(html);
			_callback();
			if($('.dialog:visible',context).size()>0){ 
				$('.dialog',context).hide();
			}else{ 
				$('.dialog',context).show();	
			}			
		});
    })
}
function get_child_cates(obj)
{
	$('#second_cate').css("display","");
	var parent_id = $(obj).val();
	if( parent_id ){
		$.get('?m=uc&a=get_child_cates&parent_id='+parent_id,function(data){				
			$('#second_cate').html(data);
	    });		
    }
}
$(function(){
	$('.l1').hover(function(){		
		$(this).children('.user_relation').children('ul').show();
//		$(this).children('.user_relation').children('ul').hover(function(){
//			$(this).show();
//		},function(){
//			$(this).hide();
//		})
	},function(){
		$(this).children('.user_relation').children('ul').hide();
	});
})

