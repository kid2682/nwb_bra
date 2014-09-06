/**
 * 
 */
var obj=new Object();
var t;
var user=[];
$(function(){
	$(".tipuser").live('mouseover',function(){	
            
            obj.default_html = $("#user_info_tip").html();		
            var thisobj=this;
    		var uid=$(this).attr("uid");	
            t = setTimeout(function(){ 			
                UserTipShow(thisobj,obj.default_html);
                if(user[uid]){
                    UserTipShow(thisobj,user[uid]);
                }else{
                    //obj.User_Tip_Ajax=$.post(def.root+"index.php?a=user_tip&m=uc", {uid:uid},
            		$.post(def.root+"index.php?a=user_tip&m=uc", {uid:uid},
                    function(html){			
            			if(html!='')
            			{
            				UserTipShow(thisobj,html);
                            user[uid] = html;
            			}/*
            			else{
            				$("#user_info_tip").hide();
            				ClearUserTipAjax();
            			}*/
                    });
                }
                 
	       },200);
		}).live('mouseout',function(){
		    if(t){
		      clearTimeout(t);
		    }
			var fun = function(){
				$("#user_info_tip").hide();
			};
			obj.time_out = setTimeout(fun,50);
			$(".tip_toolbar").html;   //清空里面的html
			ClearUserTipAjax();			
	});
	
	$("#user_info_tip").hover(function(){	
		clearTimeout(obj.time_out);
		$("#user_info_tip").show();
	},function(){
		$("#user_info_tip").hide();
		$(".tip_toolbar").html;   //清空里面的html
	});
});

function UserTipShow(obj,html)
{
	$("#user_info_tip").html(html);
	$("#user_info_tip").show();
	
	var w = 302;
	var offset = $(obj).offset();
	var left = offset.left;
	var top = offset.top - $("#user_info_tip").height();
	var width = $(document).width() - 30;
	
	if(left + w > width)
		left = left - w + $(obj).width();
	else if(left < 30)
		left = 30;
	var c = offset.left - left + $(obj).width() / 2 - 8;
	
	$("#user_info_tip").css({"top":top,"left":left});
	$("#user_info_tip .tip_arrow").css({"margin-left":c});
}
//
function ClearUserTipAjax()
{
	if(obj.User_Tip_Ajax != null)
	{
		obj.User_Tip_Ajax.abort();
		obj.User_Tip_Ajax = null;
	}
}

//关注
function user_follow(obj,uid){
	if(def.user_id==null){ 
		login();
		return;	
	}
	var fans_id=uid;
	if(fans_id==def.user_id){
		messagebox('不能关注自己噢!','error');
		return;
	}
	$.post(def.root+'index.php?m=uc&a=follow&act=add',{fans_id:fans_id},function(data){ 
		if(data.data=='success'){ 
		    user[fans_id] =null;
			$(".tip_toolbar").html('<span class="fl icrad_add">已关注</span><a class="follow_del" onclick="delete_follow(this,'+fans_id+');" href="javascript:;">取消</a>')		
        }
	},'json');
	
	
}
//取消关注
function delete_follow(obj,uid){ 
		var fans_id=uid;
		if(fans_id==def.user_id){			
			return;
		}
		$.post(def.root+'index.php?m=uc&a=follow&act=del',{fans_id:fans_id},function(data){ 
			if(data.data=='success'){ 
			    user[fans_id] =null;
				$(".tip_toolbar").html('<a class="green_button" href="javascript:;" onclick="user_follow(this,'+fans_id+');" >+加关注</a>')
			}
		},'json');
	
}


