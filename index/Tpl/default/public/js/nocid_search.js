$(function(){
	$(".search_share").live("click", function(){
		var id=$(this).attr('iid');			
		var iid=$(this).attr('item_key');	
		var title=$(this).parent().parent().siblings('.title').children().html();		
		var simg=$(this).parent().parent().children('a').children('img').attr('src');
		$.post(def.root+'index.php?m=uc&a=nocid_share_result_dialog',function(content){ 				
			var share_result_dialog=art.dialog({
				id:'share_result_dialog',
				title:'嗯~ 不错就分享一下吧 ！',
				content:content,
				lock:true
			});	
			$('#share_result_dialog .title').html(title);						
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
				var cid=$("select[name='cid']",dialog).val();				
				var remark=$(".remark",dialog).val();									
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
	});
})