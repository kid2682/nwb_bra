<?php

class wordAction extends baseAction
{
	function index()
	{
		$banword_mod = D('word');
		$word_cate_mod=D('word_cate');
		$where = '1=1';
		import("ORG.Util.Page");
		$count = $banword_mod->relation(true)->where($where)->count();
		$p = new Page($count,20);
		$banword_list = $banword_mod->relation(true)->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('banword_list',$banword_list);
		//$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=word&a=add\', title:\'添加关键词\', width:\'500\', height:\'170\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);','添加关键词');
		//$this->assign('big_menu',$big_menu);
		$this->display();
	}
  //增加
	function add()
	{
		$word_cate_list =  D('word_cate')->where('status=1')->select();
		$list_name=setArrayFormItem($word_cate_list,'id','name');
		$this->assign("word_cate_list",$list_name);
	    if(isset($_POST['dosubmit'])){		    	
 			$banword_mod = D('word');
 			if(!isset($_POST['word'])||($_POST['word']=='')){
				$this->error(L('banword_title_require'));
			}		
			$result = $banword_mod->where("word='".$_POST['word']."'")->count();
			if($result){
			    $this->error(L('banword_title_exist'));
			}
 			if($banword_mod->create()){
 				$rel = $banword_mod->add();
 				if(false !== $rel){ 					
 					$this->success(L('operation_success'), '', '', 'add');
 				} 
 				else{
 				  $this->error(L('operation_failure'));	
 				}
 			}
 			else{
 				$this->error($banword_mod->getError());
 			}
	    	

	    }else{		   
			$this->display();
	    }
	}
	//修改
	function edit()
	{
		$word_cate_list =  D('word_cate')->where('status=1')->select();
		$list_name=setArrayFormItem($word_cate_list,'id','name');
		$this->assign("word_cate_list",$list_name);
		if(isset($_POST['dosubmit'])){
			$banword_mod = D('word');
			$count=$banword_mod->where("id!=".$_POST['id']." and word='".$_POST['word']."'")->count();
			if($count>0){
				$this->error(L('banword_title_exist'));
			}		
			if (false === $banword_mod->create()) {
				$this->error($banword_mod->getError());
			}
			$result = $banword_mod->save();
			if(false !== $result){
				$this->success(L('operation_success'), '', '', 'edit');
			}else{
				$this->error(L('operation_failure'));
			}
		}else{
			if( isset($_GET['id']) ){
				$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error('参数错误');
			}
		    $banword_mod = D('word');
			$banword_info = $banword_mod->where('id='.$id)->find();	
			$this->assign('banword_info_cid',$banword_info['cid']);
			$this->assign('banword_info', $banword_info);			
			$this->display();
		}
	}
	//删除
	function delete()	{
		if((!isset($_GET['id']) || empty($_GET['id'])) && (!isset($_POST['id']) || empty($_POST['id']))) {
            $this->error(L('no_delete_banword'));
		}
		$banword_mod = D('word');
		if (isset($_POST['id']) && is_array($_POST['id'])) {
		    $ids = implode(',', $_POST['id']);
		    $banword_mod->delete($ids);
		} else {
			$id = intval($_GET['id']);
			$banword_mod->delete($id);
		}
		$this->success(L('operation_success'));
	}
	public function export() {
		$word_moe=D('word');
		$alldata=$word_moe->select();
		$str='';
		foreach($alldata as $value){
			$str.=$value['word'].'|'.$value['replacement'].',';
		}
		$str=substr($str, 0,-1);		
		if(file_put_contents('./data/word.txt', $str)){
			$this->success(L('operation_success'));
		}
	}
	

}
?>