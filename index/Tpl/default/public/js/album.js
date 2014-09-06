//加入专辑

	$('.img_album_btn,.img_album_btn_c').live("click",function(){ 
		var iid = $(this).attr('iid');
		var btn=$(this);
       
		if(def.user_id==null){ 
			login();
			return;	
		}
        
        var item_t = $(this).parent().parent().parent();
        var height = item_t.height();
        var width = item_t.width();
        item_t.append("<div class='loading'></div>");
			$('.loading').css({
				"height":height+"px",
				"width":width+"px"
			});
		$.post(def.root+"index.php?m=album&a=album_items_add_dialog",function(data){ 
			try{ 
				var error= eval("("+data+")");
				if(error.data=='not_login'){ 
					login();
					return;	
				}
			}catch(e){}		
            $('.loading').remove();
			var album_items_add_dialg=art.dialog({
				title:'加入专辑',
				id:'album_items_add_dialg',
				content:data,
				lock:true
			});	
			//console.log(album_items_add_dialg);
			var $dlg=$(".album_items_add_dialog");
            var img = $('.encode_url',btn.parent().parent()).parent().html();
            if(!img){
                img = $('.encode_url').parent().html();
            }
			$('.thumb',$dlg).html(img);
            //创建专辑
            $(".album_div_create_buootm").unbind("click").bind("click",function(){
                $(".album_div_create_list").toggle();
                //选择专辑
                $(".album_div_create_list ul li a").each(function(){
                    $(this).unbind("click").bind("click",function(){
                        $(".album_div_create_name").val($(this).html());
                        $(".album_div_create_list").hide();
                        $(".pid").val($(this).attr('pid'));
                    }); 
                });
            });
            
            $(".create_album").unbind("click").bind("click",function(){
                    
                    var title =$('.title').val();
    				var cid=$('.cid').val();
    				var remark='';	
                    
                    if(!title){
                        messagebox('专辑标题必须填写!','error');
                        return false;
                    }	
                         		
    				$.post(def.root+"index.php?m=uc&a=album_info", 
    					{act:'add',title:title,cid:cid,remark:remark,dosubmit:'dosubmit'},
    					function(data){
    						if(data.data=='success'){ 
    							$(".album_show").hide();
                                $(".showdiv").hide();
                                $(".album_div_create_name").show();
                                $(".album_div_create_buootm").show();
                                $(".album_div_create_list").css({ border: "1px solid #ccc"});
                                $(".album_div_create_list ul").css("margin","5px").css("height","60px").css("border-bottom","1px");
                                
                                var option = "<li><a href=\"javascript:void(0)\" pid=\""+data.info.id+"\">"+data.info.title+"</a></li>";               
                                $(".album_div_create_list ul").append(option);
                                $('.title').val("");
                                $(".album_div_create_list").hide();
                                $(".album_div_create_name").val(data.info.title);
                                $(".pid").val(data.info.id);
                                
    						} 
    						else{
    							messagebox('对不起专辑最多可创建36个!','error');
    						}
    					},
    				'json'); 																	

                });
                
                
			$('.submit',$dlg).click(function(){ 
	            item_t.append("<div class='loading'></div>");
    			$('.loading').css({
    				"height":height+"px",
    				"width":width+"px"
    			});
                
				$.post(def.root+"index.php?m=album&a=items", 
					{act:'add',items_id:iid,pid:$('.pid',$dlg).val(),remark:$('textarea',$dlg).val()},
					function(data){
					    $('.loading').remove();
						if(data.data=='yet_exist'){ 
							messagebox('已经添加到该专辑了!','error');				
							return;
						}
						album_items_add_dialg.close();
						messagebox('添加成功!');
					},
				'json'); 																	
			});						
		});							   
	});	


//创建专辑
$(function(){
	$('#create_album,#create_album_i').click(function(){	
		var btn=$(this);
		if(def.user_id==null){ 
			login();
			return;	
		}
		$.post(def.root+"index.php?m=uc&a=album_info&uid="+def.user_id,function(data){ 
			try{ 
				var error= eval("("+data+")");
				if(error.data=='not_login'){ 
					login();
					return;	
				}
			}catch(e){}
			
			var create_album_dialg=art.dialog({
				title:'创建新专辑',
				id:'create_album_dialg',
				content:data,
				lock:true
			});		
			var $dlg=$(".create_album_dialg");
            
			$('#submit').click(function(){ 
				var title =$('#title').val();
				var cid=$('#cid').val();
				var remark=$('#remark').val();	
                if(!title){
                    messagebox('专辑标题必须填写!','error');
                    return;
                }	
                		
				$.post(def.root+"index.php?m=uc&a=album_info", 
					{act:'add',title:title,cid:cid,remark:remark,dosubmit:'dosubmit'},
					function(data){
						if(data.data=='success'){ 
							create_album_dialg.close();
							window.location.href=def.root+"index.php?a=index&m=uc&uid="+def.user_id;
							messagebox('添加成功!');
						}
						else{
							create_album_dialg.close();
							messagebox('对不起专辑最多可创建36个!','error');
						}
					},
				'json'); 																	
			});						
		});							   
	});	
});
//编辑专辑
$(function(){
	$('.edit_album').click(function(){
		var album_id = $(this).attr('album_id');		
		if(def.user_id==null){ 
			login();
			return;	
		}
		$.post(def.root+"index.php?m=uc&a=album_info&id="+album_id,{act:'edit'},function(data){ 
			try{ 
				var error= eval("("+data+")");
				if(error.data=='not_login'){ 
					login();
					return;	
				}
			}catch(e){}
			
			var edit_album_dialg=art.dialog({
				title:'修改专辑',
				id:'edit_album_dialg',
				content:data,
				lock:true
			});				
			var $dlg=$(".edit_album_dialg");		
			$('#submit').click(function(){ 
				var id=$('#id').val();
				var title =$('#title').val();
				var cid=$('#cid').val();
				var remark=$('#remark').val();
                if(!title){
                    messagebox('专辑标题必须填写!','error');
                    return;
                }				
				$.post(def.root+"index.php?m=uc&a=album_info", 
					{act:'edit',id:id,title:title,cid:cid,remark:remark,dosubmit:'dosubmit'},
					function(data){
						if(data.data=='success'){ 
							edit_album_dialg.close();	
							window.location.href=def.root+"index.php?a=index&m=uc&uid="+def.user_id;
							messagebox('修改成功!');
						}
						else{
							edit_album_dialg.close();
							messagebox('对不起修改失败!','error');
						}
					},
				'json'); 																	
			});						
		});							   
	});	
});
$(function(){	
	$('.album_list').hover(function(){		
		$(this).children('.abl_smalls').css("opacity","1");
	},function(){
		$(this).children('.abl_smalls').css("opacity","0.5");
	})
})

