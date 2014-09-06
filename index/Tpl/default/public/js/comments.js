//添加评论
$(function(){ 
	$('.comments').each(function(){ 
		var context=$(this);
		var comments_box=$('.comments_box',context);
		var comments_btn=$('.comments_btn',context);
		var pid=comments_btn.attr('pid');
		var default_comment=comments_box.attr('def');
		var type=def.module+","+def.action;
        var comment=[];
        var comment_content = [];
        var info;
        var plhtml;
        var plnumber;
        var plcountnum=100;
		//page(def.root+"index.php?m=uc&a=comments&pid="+pid+"&type="+type);
        
		comments_box.focus(function(){ 
			if($.trim($(this).val())==default_comment){ 
				$(this).val("");												   
			}
		}).blur(function(){ 
			if($.trim($(this).val()).length==0){ 
				$(this).val(default_comment);
			}
		}).val(default_comment);
        //keydown(text).
		$(this).keyup(text);
        function text(event){
            var keycode = event.which;
            info = $(".comments_box").val();
 
            $(".comments_box").val(info.substring(0,plcountnum));
            plnumber = plcountnum - parseInt(info.length) < 0 ? 0 : plcountnum - parseInt(info.length);
            plhtml = "您还可以输入"+plnumber+"个汉字";
            if(keycode == 13 && parseInt(info.length) < plcountnum){
                $(".comments_box").height($(".comments_box").height()+15);
            }
            $(".plcount").html(plhtml);
    
        }
		comments_btn.click(function(){ 
		    
			if(!login())return;
			info=$.trim(comments_box.val());
			
			if(info.length==0 || info==default_comment){				
				messagebox('评论不能为空!','error');
				return;
			}
            if(comment_content[pid] == pid+info){
                messagebox('不能重复提交相同内容！!','error');					
                return;
            }
            if(Date.parse(new Date())-comment[def.uid] < (def.comment_time*1000)){
                messagebox('说话太快了，先歇歇吧!','error');
				return;
            }
            
            var cbutton = $(this);
            cbutton.removeClass("comments_btn");
            $.post(def.root+"index.php?m=uc&a=comments&act=add",{pid:pid,info:info,type:type},function(data){ 																	
            		page(def.root+"index.php?m=uc&a=comments&pid="+pid+"&type="+type);
    				cbpage();
                    $(".comments_box").height(28);
                    plhtml = "您还可以输入"+plcountnum+"个汉字";
                    $(".plcount").html(plhtml);
    				comments_box.val(default_comment);
                    comment[def.uid] = Date.parse(new Date()); 
                    comment_content[pid] = pid+info; 
                    cbutton.addClass("comments_btn");
   			},'json');	
        												 
		});
		//分页
		function page(url){ 
			var comments_list=$('.list',context);
			var pager="";
			if($('li',comments_list).size()!=0){
				pager=$('#page_wrap',context).html();
			}else{ 
				//第一次添加评论
				comments_list.height(32);
			}
			var height=comments_list.height();
			var width=comments_list.width();
			comments_list.append("<div class='loading'></div>");
			$('.loading',context).css({
				"height":height+"px",
				"width":width+"px"
			});
            
			$.post(url,function(data){ 
				data=data.data;		
				$('.list_wrap',context).html(data.list);
				$('#comments_count').html(data.count);
				cbpage();
				item_masonry();					
			},'json');		
		}
		
		function cbpage(){ 
			$('.page_num a',context).click(function(){
				$(this).click(function(){return false;});
				page($(this).attr('href'));
				return false;
			});		
		}
		cbpage();								 
	});
});