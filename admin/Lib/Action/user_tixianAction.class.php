<?php
class user_tixianAction extends baseAction
{
	function index()
	{		
		$user_tixian_mod = D('user_tixian');	
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		$is_money = isset($_GET['is_money']) ? intval($_GET['is_money']) : '-1';
		$status = isset($_GET['status']) ? intval($_GET['status']) : '-1';
		
		//搜索
		$where = '1=1';
		$is_money >= 0 && $where .= " AND is_money=" . $is_money;
		$status >= 0 && $where .= " AND status=" . $status;
		if ($keyword!='') {
			//$where .= " AND seller_name LIKE '%$keyword%' OR order_code LIKE '%$keyword%' OR username LIKE '%$keyword%'";
			$this->assign('keyword', $keyword);
		}	
		import("ORG.Util.Page");
		$count = $user_tixian_mod->where($where)->count();
		$p = new Page($count,20);
		$tixian_list = $user_tixian_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('addtime desc')->select();
		
		
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('is_money',$is_money);
		$this->assign('status',$status);
		$this->assign('tixian_list',$tixian_list);
		$this->display();
	}
	//确认提现
	public function ok(){
		$user_tixian_mod = D('user_tixian');
		if( isset($_GET['id']) ){
			$tixian_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}
		if(isset($_GET['type'])&&$_GET['type']==2){
			$type=2;
		}else{
			$type=1;
		}		
		$tixian_info = $user_tixian_mod->where('id='.$tixian_id)->find();
        
		$this->assign('show_header', false);
		$this->assign('type', $type);	
		$this->assign('tixian_info',$tixian_info);
		$this->display();
	}
	//退回   执行退回的时候用户表 和资金记录表都应该写入数据
	public function back(){
		$user_tixian_mod = D('user_tixian');
		if( isset($_GET['id']) ){
			$tixian_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}		
		$tixian_info = $user_tixian_mod->where('id='.$tixian_id)->find();	
		if(isset($_GET['type'])&&$_GET['type']==2){
			$type=2;
		}else{
			$type=1;
		}	
		$this->assign('show_header', false);
		$this->assign('type', $type);	
		$this->assign('tixian_info',$tixian_info);
		$this->display();
	}
	//更新提现失败
	public function back_update()
	{	
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}	
		$type=intval($_REQUEST['type']);		
		//获取用户uid	
		$tixian_id = isset($_POST['id']) && intval($_POST['id']) ? intval($_POST['id']) : $this->error(L('please_select'));
		$user_tixian_mod = D('user_tixian');		
		$tixian_info = $user_tixian_mod->where('id='.$tixian_id)->find();

		$uid=$tixian_info['uid'];
		$money=$tixian_info['money'];
		$jifenbao=$tixian_info['jifenbao'];
		if(false === $data = $user_tixian_mod->create()){
			$this->error($user_tixian_mod->error());
		}
		$result = $user_tixian_mod->where('id='.$tixian_id)->save($data);
		/* 
        发送站内信
        array(to_user,form_user,title,content,date)
        您有一笔提现申请失败，原因是：[why]
        */
        //0未审核 1提现成功 2提现失败 
  
        $map['key'] = 'msg_tixianfail';
        $msgtitle = "提现失败短信";
        $fromUser = $_SESSION['admin_info']['user_name'];
        $content = M("user_setmsg")->where($map)->find();
        $msgcontent = str_replace("[why]", $_POST['reply'], $content);
        $sendmsg = array("to_user"=>"{$_POST['uname']}","from_user"=>"{$fromUser}","title"=>"{$msgtitle}","content"=>"{$msgcontent['val']}","date"=>time());

        parent::sendMsg($sendmsg);
        
        if(false !== $result){
        	
        	if($type==2){      //2表示集分宝回滚       		
        		
        		//执行资金回滚操作			
				$user_rel=$this->user_mode->where("id='{$uid}'")->find();
				//更新用户资金表
				$this->user_info->where("uid=$uid")->setInc('jifenbao',$jifenbao); // 减少用户金钱							
				//更新返现记录表
				$cash_back_log=D('cash_back_log');
				
				$last_info=$cash_back_log->where("uid='{$uid}'")->order('id desc')->limit(1)->find();
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_jifenbao'=>$last_info['after_jifenbao'],
					'after_jifenbao'=>$last_info['after_jifenbao']+$jifenbao,
					'after_money'=>$last_info['after_money'],
					'in_jifenbao'=>$jifenbao,
					'out_jifenbao'=>0,
					'type'=>1,   //1表示收入，2表示支出
					'time'=>time(),
					'info'=>'提现失败,资金退回',
					'sign'=>md5($uid.$user_rel['name'].time()),
				);
				$cash_back_log->add($log_data);	
        		
        	}else{//金钱回滚        		
        		//执行资金回滚操作			
				$user_rel=$this->user_mode->where("id='{$uid}'")->find();
				//更新用户资金表
				$this->user_info->where("uid=$uid")->setInc('money',$money); // 减少用户金钱							
				//更新返现记录表
				$cash_back_log=D('cash_back_log');
				
				$last_info=$cash_back_log->where("uid='{$uid}'")->order('id desc')->limit(1)->find();
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_money'=>$last_info['after_money'],
					'after_money'=>$last_info['after_money']+$money,
					'in_money'=>$money,
					'out_money'=>0,
					'after_jifenbao'=>$last_info['after_jifenbao'],
					'type'=>1,   //1表示收入，2表示支出
					'time'=>time(),
					'info'=>'提现失败,资金退回',
					'sign'=>md5($uid.$user_rel['name'].time()),
				);
				$cash_back_log->add($log_data);	        		
        	}
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}

	}
	//更新提现成功
	public function ok_update()
	{		
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}		
		$type=$_REQUEST['type'];     //判断是集分宝还是其他的提现
		$user_tixian_mod = D('user_tixian');
		if(false === $data = $user_tixian_mod->create()){
			$this->error($user_tixian_mod->error());
		}

		$result = $user_tixian_mod->save($data);
        /* 
        发送站内信
        array(to_user,form_user,title,content,date)
        尊敬的[ddusername]，您好：您的提现申请已经受理完毕！
        本次提现金额[txje]已经支付到您提供的账户，查看明细进入“我的账户明细”！[addition]
        */
        //0未审核 1提现成功 2提现失败 

        $patterns[0] = "/\[ddusername\]/";
        $patterns[1] = "/\[txje\]/";
        $patterns[2] = "/\[addition\]/";
        
        $replacements[0] = $_POST['uname'];
        if($type==2){      //2表示集分宝提现
        	
        	if($this->setting['cashback_type']==1){
        		$replacements[1] = $_POST['jifenbao'].$this->setting['tb_fanxian_unit'].'集分宝';
        	}else{
        		$replacements[1] = $_POST['jifenbao'].$this->setting['tb_fanxian_unit'].$this->setting['tb_fanxian_name'];
        	}
        	
        }else{
        	$replacements[1] = $_POST['money'].'元';	
        }
        
        $replacements[2] = $_POST['reply'];
                
        $map['key'] = 'msg_tixianok';
        $msgtitle = "提现成功短信";
        $fromUser = $_SESSION['admin_info']['user_name'];
        $content = M("user_setmsg")->where($map)->find();
        $msgcontent = preg_replace($patterns, $replacements, $content);
        $sendmsg = array("to_user"=>"{$data['uname']}","from_user"=>"{$fromUser}","title"=>"{$msgtitle}","content"=>"{$msgcontent['val']}","date"=>time());
        
        parent::sendMsg($sendmsg);
        
        
		if(false !== $result){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}

	}
	function outputcvs(){
		header("Content-type: text/html; charset=gbk");
		$user_tixian_mod = D('user_tixian');	
		
		$where="status=0 AND is_money=2";
		
		$tixian_list = $user_tixian_mod->where($where)->order('addtime desc')->select();
				
		
		//$this->assign('tixian_list',$tixian_list);
		//print_r($tixian_list);
		$s="收款帐号,发放集分宝数（个）\n";
		foreach($tixian_list as $v){
			$s.= $v['alipay'].",".(int)$v['jifenbao']."\n";
		}		
		$s=iconv('utf-8','gbk',$s);
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=jifenbao.csv");
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $s;
		
		
	}
}
?>