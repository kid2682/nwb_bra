/**
 * 
 */

$(function(){
	$(".search_share").live("click", function(){	    
			if(def.user_id==null){ 
				login();
				return;	
			}
			var iid=$(this).attr('iid');
			var title=$(this).parent().parent().siblings().children('.title').html();
			var simg=$(this).parent().parent().siblings().children('.img').children('a').children('img').attr('src');
			var tags;
	        var item = $(this).parent().parent().parent();
	        var height = item.height();
	        var width = item.width();
	        item.append("<div class='loading'></div>");
			$('.loading').css({
			  "height":height+"px",
			  "width":width+"px"
			});
			$.post(def.root+'index.php?m=bsearch&a=getTags',{iid:iid,title:title},function(data){
				data=data.data;	
	            $('.loading').remove();		
				if(data=='not_login'){ 
					login();
					return;	
				}else if(data.err=='no_iid'){				
					messagebox('此商品异常，请分享其他商品','error');				
					return;
				}
				else if(data.err=='yet_exist'){
					messagebox('您已经分享过该商品了哦！','error');				
					return;
				}else if(data.err=='no_cid'){	//被人点击以后没有分类		
					//messagebox('此商品还没有被人分享过呢！','error');	
	                    					
						$.post(def.root+'index.php?m=uc&a=nocid_share_result_dialog',function(content){ 				
							var share_result_dialog=art.dialog({
								id:'share_result_dialog',
								title:'嗯~ 不错就分享一下吧 ！',
								content:content,
								lock:true
							});	
							$('#share_result_dialog .title').html(title);						
							$('#share_result_dialog .img').html("<img src='"+simg+"' width=\"140\" />");
			                  
							$('.commit').click(function(){		
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
								var cid=$("select[name='cid']",dialog).val();							
								var remark=$(".remark",dialog).val();
								var id=data.item_id;							
								var iid=data.iid;							
								//console.log(data);return;
								$.post(def.root+'index.php?m=uc&a=nocid_share&act=index',{cid:cid,remark:remark,id:id,item_key:iid},function(data){
									data=data.data;
									if(data=='success'){
										share_result_dialog.close();
										messagebox('恭喜您添加该商品成功');
	                                    	
										//window.location.href=def.root+"index.php?m=uc&a=share";
									}
									$(this).attr('disabled','');
								},'json');
							});
						});	
					return;
				}else if(data.err=='share_yes'){
					messagebox('恭喜您，分享成功');				
					return;
				}else{
					tags=data.tags;
				}
				//弹窗
				$.post(def.root+'index.php?m=uc&a=share_result_dialog',function(content){ 				
					var share_result_dialog=art.dialog({
						id:'share_result_dialog',
						title:'嗯~ 不错就分享一下吧 ！',
						content:content,
						lock:true
					});			
					
					$('#share_result_dialog .title').html(title);
					$('#share_result_dialog .tags').val(tags);
					$('#share_result_dialog .img').html("<img src='"+simg+"' width=\"140\" />");
					
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
								messagebox('恭喜您添加改商品成功');
								//window.location.href=def.root+"index.php?m=uc&a=share";
							}
							$(this).attr('disabled','');
						},'json');
					});
				});		
				
				
			},'json');		
			
		});

	});

		