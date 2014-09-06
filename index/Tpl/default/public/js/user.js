//关注
$(function(){ 

	function add_follow(){ 
		$(".add_follow").click(function(){
			var context=$(this);
			if(def.user_id==null){ 
				login();
				return;	
			}	
			if($.trim($(this).attr("class"))!="add_follow")return;
			
			var fans_id=$(this).attr('fans_id');
            var fans_uid=$(this).attr('fans_uid');
			if(fans_id==def.user_id){
				alert('不能关注自己!');
				return;
			}
			$.post(def.root+'index.php?m=uc&a=follow&act=add',{fans_id:fans_id},function(data){ 
				if(data.data=='success'){ 
				    if(fans_uid)fans_id=fans_uid;
                    $.post(def.root+'index.php?m=uc&a=gz',{uid:fans_id},function(json){ 
				        $(".gz").html(json.data.follow_num);
				        $(".qxgz").html(json.data.fans_num);
                        
				    },'json');
                    
					messagebox("关注成功!");
					context.addClass('yet');
					context.unbind("click");
					del_follow();
				}
			},'json');
		});	
	}
	function del_follow(){ 
		$('.add_follow.yet').hover(function(){ 
			$(this).addClass("bg_none");
			$(this).html('取消');
		},function(){
			$(this).removeClass('bg_none');
			$(this).html('');
		}).click(function(){ 
			var context=$(this);
			var fans_id=$(this).attr('fans_id');
            var fans_uid=$(this).attr('fans_uid');
			$.post(def.root+'index.php?m=uc&a=follow&act=del',{fans_id:fans_id},function(data){ 
				if(data.data=='success'){ 
				    if(fans_uid)fans_id=fans_uid;
				    $.post(def.root+'index.php?m=uc&a=gz',{uid:fans_id},function(json){ 
				        $(".gz").html(json.data.follow_num);
				        $(".qxgz").html(json.data.fans_num);
                        
				    },'json');
				    
					messagebox("成功取消关注该用户!");
					context.removeClass('yet');
					context.unbind("mouseover");
					context.unbind("click");
					add_follow();
				}
			},'json');
		});	 	
	}
	add_follow();
	del_follow();
	 
});



//个性签名

$(function(){
	$('#user_info').click(function(){
		var uid = $(this).attr('uid');		
		var btn=$(this);
		if(def.user_id==null){ 
			login();
			return;	
		}
		if(def.user_id!=uid){   //判断用户名是否异常
			login();
			return;	
		}
		$.post(def.root+"index.php?m=uc&a=user_info_dialog",function(data){ 
			try{ 
				var error= eval("("+data+")");
				if(error.data=='not_login'){ 
					login();
					return;	
				}
			}catch(e){}
			
			var user_info_dialg=art.dialog({
				title:'个性签名',
				id:'user_info_dialg',
				content:data,
				lock:true
			});	
			//console.log(album_items_add_dialg);
			var $dlg=$(".user_info_dialg");	
			$('#info_submit').click(function(){ 				
				var uid=$.trim($('#uid').val());  //地址
				var info=$.trim($('#info').val());   //邮编
				$.post(def.root+"index.php?m=uc&a=user_info_dialog", 
					{act:'update',uid:uid,info:info},
					function(data){							
						if(data.data=='success'){								
							messagebox('修改成功!');
							user_info_dialg.close();
							$("#user_info_span").html(info);			
						}
						else{
							messagebox('修改失败!');
							user_info_dialg.close();							
						}
					},
				'json'); 																	
			});						
		});							   
	});	
});
//+推荐
$(function(){
    $(".add_recommend").click(function(){
        var uid = $(this).attr('fans_id');
        var album_id = $(this).attr('album_id');
        
        $.get(def.root + "index.php?m=album&a=albumRecommend", {uid: uid,album_id:album_id},
		function(data) {
			if (data.data == 'not_login') {
				login();
				return;
			} else if (data.data == '0') {
                $(".recommend_already").html("推荐过了").fadeIn().fadeOut(3000);
				return;
			} else {
                $(".recommend_already").html("推荐+1").fadeIn().fadeOut(3000);
                
				return;
			}
		},
		'json');
    })
})