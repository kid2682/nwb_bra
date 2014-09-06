<?php

class items_cateAction extends baseAction
{
	public $cate_list;
	function _initialize(){
		parent::_initialize();
		//每次显示的时候清除缓存
		if(is_dir("./admin/Runtime")){
          deleteCacheData("./admin/Runtime"); 
        }	
		$this->cate_list=get_items_cate_list('0','0','1','collect_miao');
	}
	//分类列表
	function index()
	{		
		$items_cate_mod = M('items_cate');
		$items_mod = D('items');
		$result = $items_cate_mod->order('ordid ASC')->select();	
		$this->assign('items_cate_list',$this->cate_list['sort_list']);
		$this->display();
	}
	//导入分类
	function import(){
		$items_cate_mod = M('items_cate');	
		if(isset($_POST['dosubmit'])&&isset($_POST['item_cate'])){
			//print_r($_POST['item_cate']);
			$data=array();
			$data['import_status']=0;
			$data['status']=0;
			//把所有的一级分类的import_status设置为0
			$update_rel=$items_cate_mod->where("pid=0")->save($data);

			$data['import_status']=1;
			$data['status']=1;
			foreach ($_POST['item_cate'] as $v){
				$items_cate_mod->where("id={$v}")->save($data);
			}
		}		
		$rel=$items_cate_mod->where("pid=0")->select();		
		
		$item_cate_list=setArrayFormItem($rel,'id','name');
		
		$str='';		
		foreach ($rel as $value){	
			if($value['import_status']==1){
				$str.=$value['id'].',';
			}			
		}		
		$str=substr($str,0,-1);		
		$item_cate_list_select=explode(',',$str);		
		$this->assign('item_cate_list',$item_cate_list);		
		$this->assign('item_cate_list_select',$item_cate_list_select);			
 		$this->display();		
	}	

	//添加分类数据
	function add()
	{
		if(isset($_POST['dosubmit'])){
			$items_cate_mod = M('items_cate');
			if( false === $vo = $items_cate_mod->create() ){
				$this->error( $items_cate_mod->error() );
			}
			if($vo['name']==''){
				$this->error('分类名称不能为空');
			}
			$result = $items_cate_mod->where("name='".$vo['name']."' AND pid='".$vo['pid']."'")->count();
			if($result != 0){
				$this->error('该分类已经存在');
			}

			if ($_FILES['img']['name'] != '') {	
				$upload_list = $this->_upload('items_cate');				
				$vo['img'] = $upload_list;
			}
			//保存当前数据
			$items_cate_id = $items_cate_mod->add($vo);
			$this->success(L('operation_success'));			
		}
		//dump($this->cate_list['sort_list']);
		$this->assign('items_cate_list',$this->cate_list['sort_list']);
		$this->assign('show_header', false);
		$this->display('edit');
	}

	function delete()
	{
		if((!isset($_GET['id']) || empty($_GET['id'])) && (!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要删除的分类！');
		}
		$items_cate_mod = M('items_cate');
		if (isset($_POST['id']) && is_array($_POST['id'])) {
			/*
			 foreach($_POST['id'] as $val){
			@unlink(ROOT_PATH."/data/items_cate/".$items_cate_mod->where('id='.$val)->getField('img'));
			}
			*/
			$items_cate_mod->delete(implode(',', $_POST['id']));
		} else {
			$items_cate_id = intval($_GET['id']);
			/*
			 @unlink(ROOT_PATH."/data/items_cate/".$items_cate_mod->where('id='.$items_cate_id)->getField('img'));
			*/
			$items_cate_mod->delete($items_cate_id);
		}

		$this->success(L('operation_success'));
	}

	function edit()
	{
		if(isset($_POST['dosubmit'])){
			$items_cate_mod = M('items_cate');

			$old_items_cate = $items_cate_mod->where('id='.$_POST['id'])->find();
			//名称不能重复
			if ($_POST['name'] != $old_items_cate['name']) {
				if ($this->_items_cate_exists($_POST['name'], $_POST['pid'], $_POST['id'])) {
					$this->error('分类名称重复！');
				}
			}

			//获取此分类和他的所有下级分类id
			$vids = array();
			$children[] = $old_items_cate['id'];
			$vr = $items_cate_mod->where('pid='.$old_items_cate['id'])->select();
			foreach ($vr as $val) {
				$children[] = $val['id'];
			}
			if (in_array($_POST['pid'], $children)) {
				$this->error('所选择的上级分类不能是当前分类或者当前分类的下级分类！');
			}

			$vo = $items_cate_mod->create();
			if ($_FILES['img']['name'] != '') {
				
				$upload_list = $this->_upload('items_cate');				
				$vo['img'] = $upload_list;
				//删去老图片
				$img_dir=$old_items_cate['img'];
				if(file_exists($img_dir)){
					@unlink($img_dir);
				}	
				
			}

			if( !isset($_POST['is_hots']) ){
				$vo['is_hots'] = 0;
			}
			if( !isset($_POST['status']) ){
				$vo['status'] = 0;
			}
			$result = $items_cate_mod->save($vo);
			if(false !== $result){
				$this->success('修改成功',U('items_cate/index'),1);
			}else{
				$this->error('修改失败',U('items_cate/index'));
			}
		}
		$this->assign('items_cate_list',$this->cate_list['sort_list']);
		$items_cate_mod = M('items_cate');
		if( isset($_GET['id']) ){
			$items_cate_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select').L('article_name'));
		}
		$items_cate_info = $items_cate_mod->where('id='.$items_cate_id)->find();
		
		//print_r($items_cate_info);

		$this->assign('items_cate_info',$items_cate_info);
		$this->assign('show_header', false);
		$this->display();
	}
	//批量添加分类数据
	function add_all()
	{
		if(isset($_POST['dosubmit'])){
			$items_cate_mod = M('items_cate');
			if( false === $vo = $items_cate_mod->create() ){
				$this->error( $items_cate_mod->error() );
			}
			if($vo['name']==''){
				$this->error('分类名称不能为空');
			}
			
			$_name = str_replace('|', '', trim($vo['name']));
			$_name_arr = array_unique(explode("\r\n", $_name));			
			$keywords=str_replace('|', '', trim($vo['keywords']));
			$keywords_arr = array_unique(explode("\r\n", $keywords));			
			foreach($_name_arr as $key=>$val){
				$result = $items_cate_mod->where("name='".$val."' AND pid='".$vo['pid']."'")->count();
				if($result==0){//如果不存在执行插入操作		
					if(count($_name_arr)==count($keywords_arr)){
						$vo['keywords']=trim($keywords_arr[$key]);
					}else{
						$vo['keywords']=trim($val);
					}
					$vo['name']=trim($val);					
					//保存当前数据
					$items_cate_id = $items_cate_mod->add($vo);				
					
				}
			
			}		
			$this->success(L('operation_success'));			
		}
		//dump($this->cate_list['sort_list']);
		$this->assign('items_cate_list',$this->cate_list['sort_list']);
		$this->assign('show_header', false);
		$this->display();
	}
	private function _items_cate_exists($name, $pid, $id=0)
	{
		$result = M('items_cate')->where("name='".$name."' AND pid='".$pid."' AND id<>'".$id."'")->count();
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	function sort_order()
	{
		$items_cate_mod = M('items_cate');
		if (isset($_POST['listorders'])) {
			foreach ($_POST['listorders'] as $id=>$sort_order) {
				$data['ordid'] = $sort_order;
				$items_cate_mod->where('id='.$id)->save($data);
			}
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}
}
?>