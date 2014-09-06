//回复短信息
function message_callback($id){	

    if(def.user_id==null){ 
			login();
			return;	
		}
    
        $.post(def.root+"index.php?m=uc&a=sendmsg&iid="+$id,function(data){ 

            try{ 
				var error= eval("("+data+")");
				if(error.data=='not_login'){ 
					login();
					return;	
				}
			}catch(e){}
			
			var album_items_add_dialg=art.dialog({
				title:'发送短信',
				id:'album_items_add_dialg',
				content:data,
				lock:true
			});	
            
            
			//var $dlg=$(".album_items_add_dialog");
			//$('.thumb',$dlg).html($('.encode_url',btn.parent().parent()).parent().html());
			$('.submit').click(function(){ 
			    var to_user = $("#to_user").val();
                var title = $("#title").val();
                var content = $("#content").val();
                if(!to_user){
                    messagebox('请填写联系人!');
                    return;
                }
                if(!title){
                    messagebox('请填写标题!');
                    return;
                }
                if(!content){
                    messagebox('请填写内容!');
                    return;
                }
				$.post(def.root+"index.php?m=uc&a=doSendMsg", 
					{to_user:to_user,title:title,content:content},function(data){

						if(data==0){ 
							alert("发送信息失败");						
							return;
						}
						album_items_add_dialg.close();
						messagebox('发送信息成功!');
					},
				'json'); 																	
			});						
		});	

}
function message_delback($id){
    if(this.confirm("确认要删除此条信息吗？")){
        $.post(def.root+"index.php?m=uc&a=delMsg",{delid:$id},function(data){ 
 
            if(data==0){ 
        		alert("删除失败");						
        		return;
            }
            messagebox('删除成功!');
            $("#tr_"+$id).remove();
        });
    }
}