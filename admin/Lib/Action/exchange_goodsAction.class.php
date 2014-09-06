<?php

class exchange_goodsAction extends baseAction
{	//显示列表
	public function index()
	{
		$ex_goods_mod = D('exchange_goods');	
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		$goods_type=isset($_GET['goods_type'])?intval($_GET['goods_type']):'';	
		//搜索
		$where = '1=1';
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}
		if($goods_type!=''){
			$where .= " AND goods_type=$goods_type";
			$this->assign('goods_type', $goods_type);
		}		
		import("ORG.Util.Page");
		$count = $ex_goods_mod->where($where)->count();
		$p = new Page($count,20);
		$ex_goods_list = $ex_goods_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('ex_goods_list',$ex_goods_list);
		$this->display();
	}
	//修改
	public function edit()
	{
		$ex_goods_mod = D('exchange_goods');
		if( isset($_GET['id']) ){
			$ex_goods_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}		
		$ex_goods_info = $ex_goods_mod->where('id='.$ex_goods_id)->find();
		$this->assign('show_header', false);	
		$this->assign('ex_goods_info',$ex_goods_info);
		$this->display();
	}
	//更新
	public function update()
	{		
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		$ex_goods_mod = D('exchange_goods');
		$id=intval($_POST['id']);
		//获取原图片			
		$old_img=$ex_goods_mod->where("id='{$id}'")->getField('img');
		if(false === $data = $ex_goods_mod->create()){
			$this->error($ex_goods_mod->error());
		}
		//时间转化为时间戳
		$data['begin_time']=strtotime($data['begin_time']);
		$data['end_time']=strtotime($data['end_time']);	
		
		
		if ($_FILES['img']['name']!='') {
			$upload_list = $this->_upload('exchangegoods');
			$data['img'] = $upload_list;
			//删除老图片
			$img_dir=$old_img;
			if(file_exists($img_dir)){
				@unlink($img_dir);
			}
			
		}
		$result = $ex_goods_mod->where("id='{$id}'")->save($data);
		if(false !== $result){
			$this->success(L('operation_success'),U('exchange_goods/index'));
		}else{
			$this->error(L('operation_failure'));
		}
	}
	//增加
	public function add()
	{		
		$this->display();
	}
	//插入数据
	public function insert()
	{
		$ex_goods_mod = D('exchange_goods');
		if(false === $data = $ex_goods_mod->create()){
			$this->error($ex_goods_mod->error());
		}
		$data['begin_time']=strtotime($data['begin_time']);
		$data['end_time']=strtotime($data['end_time']);		
		
		//如果seo信息为空的话执行
		if(empty($data['seo_title'])){
			$data['seo_title']=$data['name'];
		}
		if(empty($data['seo_keys'])){
			$data['seo_keys']=$data['name'];
		}
		if(empty($data['seo_desc'])){
			$data['seo_desc']=$data['name'];
		}		
		
		if ($_FILES['img']['name']!='') {
			$upload_list = $this->_upload('exchangegoods');
			$data['img'] = $upload_list;
		}	
		$result = $ex_goods_mod->add($data);
		if($result){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}
	//删除数据
	public function delete()
	{	$ex_goods_mod = D('exchange_goods');
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要删除的数据');
		}
		if( isset($_POST['id'])&&is_array($_POST['id']) ){
			$cate_ids = implode(',',$_POST['id']);			
			 foreach($_POST['id'] as $val){
			 	$img=$ex_goods_mod->where('id='.$val)->getField('img');	
			 	 $file=$img;
			 	if(file_exists($file)){		 	
					@unlink($file);
			 	}
			}				
			$ex_goods_mod->delete($cate_ids);
		}else{
			$cate_id = intval($_REQUEST['id']);
			$img=$ex_goods_mod->where('id='.$cate_id)->getField('img');	
			$file=$img;
		 	if(file_exists($file)){		 	
				@unlink($file);
		 	}         
			$ex_goods_mod->where('id='.$cate_id)->delete();
		}
		$this->success(L('operation_success'));
	}
}
?>