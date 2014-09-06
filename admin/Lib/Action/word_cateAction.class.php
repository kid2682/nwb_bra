<?php

class word_cateAction extends baseAction
{
	function index()
	{
		$word_cate_mod = D('word_cate');		
		$word_cate_list = $word_cate_mod->select();
		$this->assign('word_cate_list',$word_cate_list);
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=word_cate&a=add\', title:\'添加分类\', width:\'500\', height:\'170\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);','添加分类');
		$this->assign('big_menu',$big_menu);	
		$this->display();
	}
  //增加
	function add()
	{
	    if(isset($_POST['dosubmit'])){		    	
 			$word_cate_mod = D('word_cate');
 			if(!isset($_POST['name'])||($_POST['name']=='')){
				$this->error(L('word_cate_name_require'));
			}		
			$result = $word_cate_mod->where("name='".$_POST['name']."'")->count();
			if($result){
			    $this->error(L('word_cate_name_exist'));
			}
 			if($word_cate_mod->create()){
 				$rel = $word_cate_mod->add();
 				if(false !== $rel){ 					
 					$this->success(L('operation_success'), '', '', 'add');
 				} 
 				else{
 				  $this->error(L('operation_failure'));	
 				}
 			}
 			else{
 				$this->error($word_cate_mod->getError());
 			}
	    	

	    }else{		   
			$this->display();
	    }
	}
	//修改
	function edit()
	{
		if(isset($_POST['dosubmit'])){
			$word_cate_mod = D('word_cate');
			$count=$word_cate_mod->where("id!=".$_POST['id']." and name='".$_POST['name']."'")->count();
			if($count>0){
				$this->error(L('word_cate_name_exist'));
			}		
			if (false === $word_cate_mod->create()) {
				$this->error($word_cate_mod->getError());
			}
			$result = $word_cate_mod->save();
			if(false !== $result){
					$this->success(L('operation_success'), '', '', 'edit');
				}else{
					$this->error(L('operation_failure'));
			}
		}else{
			if( isset($_GET['id']) ){
				$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error('参数错误');
			}
		    $word_cate_mod = D('word_cate');
			$word_cate_info = $word_cate_mod->where('id='.$id)->find();		
			$this->assign('word_cate_info', $word_cate_info);			
			$this->display();
		}
	}
	//删除
	function delete()	{
		if((!isset($_GET['id']) || empty($_GET['id'])) && (!isset($_POST['id']) || empty($_POST['id']))) {
            $this->error(L('no_delete_word_cate'));
		}
		$word_cate_mod = D('word_cate');
		if (isset($_POST['id']) && is_array($_POST['id'])) {
		    $ids = implode(',', $_POST['id']);
		    $word_cate_mod->delete($ids);
		} else {
			$id = intval($_GET['id']);
			$word_cate_mod->delete($id);
		}
		$this->success(L('operation_success'));
	}
}
?>