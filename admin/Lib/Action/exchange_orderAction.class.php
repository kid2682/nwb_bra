<?php
class exchange_orderAction extends baseAction
{	//显示列表
	public function index()
	{
		$ex_order_mod = D('exchange_order');	
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';		
		//搜索
		$where = '1=1';
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}		
		import("ORG.Util.Page");
		$count = $ex_order_mod->where($where)->count();
		$p = new Page($count,20);
		$ex_order_list = $ex_order_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('ex_order_list',$ex_order_list);
		$this->display();
	}
	//修改
	public function edit()
	{
		$ex_order_mod = D('exchange_order');
		if(isset($_GET['id']) ){
			$ex_goods_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}		
		$ex_order_info = $ex_order_mod->where('id='.$ex_goods_id)->find();
        
        $this->assign('show_header', false);	
		$this->assign('ex_order_info',$ex_order_info);
		$this->display();
	}
	//更新
	public function update()
	{		
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
        
		$ex_order_mod = D('exchange_order');
		$id=intval($_POST['id']);		
		if(false === $data = $ex_order_mod->create()){
			$this->error($ex_order_mod->error());
		}
        /* 
        发送站内信
        array(to_user,form_user,title,content,date)
        您在本站使用积分兑换的商品订单[STATE]。
        */
        //0未发货 1部分发货 2全部发货 3部分退货 4全部退货 
        switch($_POST['goods_status']){
            case 0:
                $state = "未发货";
                break;
            case 1:
                $state = "部分发货";
                break;
            case 2:
                $state = "全部发货";
                break;
            case 3:
                $state = "部分退货";
                break;
            case 4:
                $state = "全部退货";
                break;
        }
        $map['key'] = 'msg_dhjifen';
        $msgtitle = "积分兑换短信";
        $content = M("user_setmsg")->where($map)->find();
        $msgcontent = str_replace("[STATE]",$state,$content['val']);
        $sendmsg = array("to_user"=>"{$_POST['to_user']}","form_user"=>"{$_SESSION ['admin_info']['user_name']}","title"=>"{$msgtitle}","content"=>"{$msgcontent['val']}","date"=>time());
        parent::sendMsg($sendmsg);
        
        
		$result = $ex_order_mod->where("id='{$id}'")->save($data);
		if(false !== $result){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}	
	//删除数据
//	public function delete()
//	{	$ex_order_mod = D('exchange_order');
//		if((!isset($_POST['id']) || empty($_POST['id']))) {
//			$this->error('请选择要删除的数据');
//		}
//		if( isset($_POST['id'])&&is_array($_POST['id']) ){
//			$cate_ids = implode(',',$_POST['id']);			
//			 foreach($_POST['id'] as $val){
//			 	$img=$ex_order_mod->where('id='.$val)->getField('img');	
//			 	 $file=$img;
//			 	if(file_exists($file)){		 	
//					@unlink($file);
//			 	}
//			}				
//			$ex_order_mod->delete($cate_ids);
//		}else{
//			$cate_id = intval($_REQUEST['id']);
//			$img=$ex_order_mod->where('id='.$cate_id)->getField('img');	
//			$file=$img;
//		 	if(file_exists($file)){		 	
//				@unlink($file);
//		 	}         
//			$ex_order_mod->where('id='.$cate_id)->delete();
//		}
//		$this->success(L('operation_success'));
//	}
}
?>