<?php
class userAction extends baseAction{
	public function index(){
        $mod=D("user"); 
        $pagesize=20;        
        import("ORG.Util.Page");
		$where=" 1=1 ";
		if(isset($_REQUEST['keyword'])){
			$keys = $_REQUEST['keyword'];
			$this->assign('keyword',$keys);
			$where.=" and name like '%$keys%'";
		}
		$count=$mod->relation('user_info')->where($where)->count();		
		$p = new Page($count,$pagesize);		
		$list=$mod->relation('user_info')->where($where)->order("last_time desc")->limit($p->firstRow.','.$p->listRows)->select();
		$page=$p->show();  
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->display();
	}
	function edit() {
		if (isset($_POST['dosubmit'])) {
			$mod = D('user');
			$info_mod = D('user_info');
			$user_data = $mod->create();
			$info_data = $info_mod->create();			
			$user_data['user_info']=$info_data;
			$pass=trim($_REQUEST['password']);
			
			if(!empty($pass)){
				$user_data['passwd']=md5(trim($_REQUEST['password']));
			}
			$result_info=$mod->where("id=". $user_data['id'])->relation('user_info')->save($user_data);
			if(false !== $result_info){
				$this->success(L('operation_success'), '', '', 'edit');
			}else{				
				$this->success(L('operation_failure'));
			}
		} else {
			$mod = D('user');
			$info_mod = M('user_info');
			if (isset($_GET['id'])) {
				$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error('请选择要编辑的链接');
			}
			$user = $mod->where('id='. $id)->relation('user_info')->find();		
			$this->assign('info', $user);
			$this->assign('show_header', false);
			$this->display();
		}
	}
	public function setscore(){
		$setting_mod = M('setting');
		if (isset($_POST['dosubmit'])) {
			$setscore['user_register_score'] = isset($_POST['user_register_score']) && trim($_POST['user_register_score']) ? trim($_POST['user_register_score']) : $this->error('注册积分填写错误');
			$setscore['user_login_score'] = isset($_POST['user_login_score']) && trim($_POST['user_login_score']) ? trim($_POST['user_login_score']) : $this->error('登陆积分填写错误');
			$setscore['share_goods_score'] = isset($_POST['share_goods_score']) && trim($_POST['share_goods_score']) ? trim($_POST['share_goods_score']) : $this->error('分享商品积分填写错误');					
			foreach( $setscore as $key=>$val ){				
				$setting_mod->where("name='$key'")->save(array('data'=>$val));				
			}			
			$this->success('修改成功', U('user/setscore'));
		}
		$res = $setting_mod->where("name='user_register_score' OR name='user_login_score' OR name='share_goods_score' OR name='delete_share_goods_score'")->select();
		foreach( $res as $val )
		{
			$setscore[$val['name']] = $val['data'];
		}
		$this->assign('setscore',$setscore);
		$this->display();
	}
	public function delete()
    {
		$user_mod = D('user');
		
		if(!isset($_POST['id']) || empty($_POST['id'])) {
            $this->error('请选择要删除的数据！');
		}	
		if( isset($_POST['id'])&&is_array($_POST['id']) ){			
			foreach( $_POST['id'] as $val ){
				$user_mod->relation('user_info')->delete($val);					
			}			
		}else{
			$id = intval($_POST['id']);			
		    $user_mod->where('id='.$id)->ralation('user_info')->delete();		
		}
		$this->success(L('operation_success'));
    }
    	//批量添加分类数据
	function add_all()
	{
		set_time_limit(0);
		if(isset($_POST['dosubmit'])){
			$user_mod = D('user');
			$user_info_mod = D('user_info');
			$items_mod = D('items');
			$items_like_mod = D('like_list');
			if( false === $vo = $user_mod->create() ){
				$this->error( $user_mod->error() );
			}
			if($vo['name']==''){
				$this->error('名称不能为空');
			}			
			$_name = str_replace('|', '', trim($vo['name']));
			$_name_arr = array_unique(explode("\r\n", $_name));	
			foreach($_name_arr as $key=>$val){
				$result = $user_mod->where("name='{$val}'")->count();
				if($result==0){//如果不存在执行插入操作	
					//增加会员				
					$vo['name']=trim($val);
					$vo['passwd']=md5('abc-123');					
					$vo['ip']=get_client_ip();
					$vo['add_time']=time();	
					$vo['is_majia']=1;												
	                $user_info['sex']=1;
					$user_info['integral']=0;
					$user_info['info']='我是'.$vo['name'].',我的美丽我做主,我在乐活城喜欢的宝贝分享给大家，希望对大家的美丽搭配购物有帮助';					
					$id=$user_mod->add($vo);
					$user_info['uid']=$id;		
					$user_info_mod->add($user_info);	
					$count=	$items_mod->count();			
					//增加会员喜欢
					$rand_item=rand(1,$count-200);
					$like_item = $items_mod->field('id')->where('status=1')->limit(''.$rand_item.',70')->select();
					//echo $items_mod->getLastSql();					
					foreach ($like_item as $key=>$value){
						$like_arr=array(
							'items_id'=>$value['id'],
							'uid'=>$id						
						);
						$items_like_mod->add($like_arr);
						$like_num = $items_mod->where('id=' . $id)->setInc('likes');
					}
				}
			
			}		
			$this->success(L('operation_success'));			
		}
		//dump($this->cate_list['sort_list']);
		$this->assign('items_cate_list',$this->cate_list['sort_list']);
		$this->assign('show_header', false);
		$this->display();
	}
    
}