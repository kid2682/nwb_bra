<?php
class miao_orderAction extends baseAction
{
	function index()
	{		
		$miao_order_mod = D('miao_order');	
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		//搜索
		$where = '1=1';
		if ($keyword!='') {
			$where .= " AND seller_name LIKE '%$keyword%' OR order_code LIKE '%$keyword%' OR username LIKE '%$keyword%'";
			$this->assign('keyword', $keyword);
		}	
		import("ORG.Util.Page");
		$count = $miao_order_mod->where($where)->count();
		$p = new Page($count,20);
		$miao_order_list = $miao_order_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('order_time desc')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('miao_order_list',$miao_order_list);
		$this->display();
	}
	//修改
	public function edit()
	{
		$miao_order_mod = D('miao_order');
		if( isset($_GET['id']) ){
			$miao_order_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}		
		$miao_order_info = $miao_order_mod->where('id='.$miao_order_id)->find();
		$this->assign('show_header', false);	
		$this->assign('miao_order_info',$miao_order_info);
		$this->display();
	}
	//更新
	public function update()
	{		
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		$miao_order_mod = D('miao_order');
		//获取以前的订单状态
		$miao_order_info = $miao_order_mod->where('id='.$_POST['id'])->find();		
		
		if(false === $data = $miao_order_mod->create()){
			$this->error($miao_order_mod->error());
		}
		$result = $miao_order_mod->save($data);
		if(false !== $result){
			//更新成功判断状态更新用户金钱以及返现记录			
			if($data['status']=="有效" && ($miao_order_info['status']=='未确认' || $miao_order_info['status']=='无效')){
				if($miao_order_info['uid']){
					$this->update_cash_integral($miao_order_info['cash_back'],0,$miao_order_info['uid']);	
				}				
			}
			if($miao_order_info['status']=="有效" && ($data['status']=='未确认' || $data['status']=='无效')){
				if($miao_order_info['uid']){
					$this->desc_cash_integral($miao_order_info['cash_back'],0,$miao_order_info['uid']);
				}				
			}						
			$this->success(L('operation_success'), '', '', 'edit');
		}else{
			$this->error(L('operation_failure'));
		}
	}
	//获取59秒订单
	public function getorder(){
		$date=date('Y-m-d');
		$this->assign('date',$date);
		$this->display();
	}
	//获取淘宝订单
	public function get_tao_order(){
		$date=date('Y-m-d');
		$this->assign('date',$date);
		$this->display();
	}		
	public function get_tao_order_jump(){
		$order_mod=D('miao_order');		
		$star_data= isset($_GET['star_data']) ? trim($_GET['star_data']) : '';
		$end_data = isset($_GET['end_data'])? trim($_GET['end_data']) :'';
		if(empty($star_data)||empty($end_data)){
			$this->collect_error('开始日期或者结束日期不能为空',U('miao_order/getorder'),'getorder');
		}		
		$star_ymd_data=date('Ymd',strtotime($star_data));
		$end_ymd_data=date('Ymd',strtotime($end_data));
		$tmp='';
		if($star_ymd_data>$end_ymd_data){
			$tmp=$end_ymd_data;
			$end_ymd_data=$star_ymd_data;
			$star_ymd_data=$tmp;
		}	
		//获取总共有多少条数据
		$pages = get_diff_date($star_data,$end_data);		
		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页		
		   //要获取的当前日期		
		$now_date=date('Ymd',strtotime("$star_ymd_data +$p day"));;   //要获取的当前日期	
		
		$tb_top = $this->taobao_client();
		$req = $tb_top->load_api('TaobaokeReportGetRequest');
		
		$req->setFields("trade_id,commission,outer_code,pay_time,pay_price,item_num,num_iid,seller_nick,real_pay_fee");
		$req->setDate($now_date);
		$req->setPageNo(1);
		$req->setPageSize(40);           //此处如果每天的订单数大于40 将会有问题
		$order_report = $tb_top->execute($req, $this->setting['tao_session']);
		
		$order_report=get_object_vars_final($order_report);	
		
		//print_r($order_report);
		if($order_report['taobaoke_report']['taobaoke_report_members']['taobaoke_report_member']){
			$order_report_data=$order_report['taobaoke_report']['taobaoke_report_members']['taobaoke_report_member'];
		}
		else{
			$order_report_data='';
		}	
		if(!empty($order_report_data)){   //不是二维数组转化为二维数组
			if(IsTwoArray($order_report_data)){
				$order_report_data=array($order_report_data);
			}
		}
		
		$add_num=0;		
		$update_num=0;			
		if(count($order_report_data)>0){
			foreach ($order_report_data as $item) {
				
				if($this->setting['cashback_type']==1){
					$fanxian_bili=100;	
				}else{
					$fanxian_bili=$this->setting['tb_fanxian_bili'];
				}
				$cashbac=cashback_jifenbao($item['commission'],$fanxian_bili,$this->setting['cashback_rate'],$this->setting['integralback_rate']);
				//获取用户名
				if(!$item['outer_code'] || !is_numeric($item['outer_code'])){    //如果outcode为空的话，从服务器获取的内容是一个数组
					$item['outer_code']=0;
				}		
				$uid=$item['outer_code'];				
				
				$user_info_data=$this->user_mode->where("id='{$uid}'")->find();			
				if($user_info_data){
					$username=$user_info_data['name'];					
				}
				else{
					$username='';
					$uid='';
				}	
				$data=array(
				'uid'=>$uid,        //暂时放这个             out_code
				'username'=>$username,
				'order_id'=>$item['num_iid'],  //59秒开放平台会返回order_id  淘宝没有order_id  用num_iid取代  
				'order_code'=>$item['trade_id'],
				'seller_name'=>$item['seller_nick'],
				'order_time'=>$item['pay_time'],
				'item_price'=>$item['pay_price']*$item['item_num'],
				'commission'=>$item['commission']*$item['item_num'],
				'cash_back_jifenbao'=>$cashbac['cacheback']*$item['item_num'],   //返现，这里通过公共函数计算
				'status'=>'有效',
				'item_count'=>$item['item_num'],
				);					
				$one_rel=$order_mod->where("order_id='{$item['num_iid']}' and order_code='{$item['trade_id']}'")->find();							
				//如果此数据存在则执行更新操作
				if(count($one_rel)>0){
					//查看看该订单是否已经确认 并更新完毕				
					if($one_rel['is_update']==0){
						//执行 update_cash_integral 方法
						$this->update_cash_jifenbao_integral($cashbac['cacheback'],$cashbac['integralback'],$data['uid']);							
						$data['is_update']=1;  //1表示此数据已经返现了，再次同步的是不将不进行返现
					}					
					$order_mod->where("order_id='{$item['num_iid']}' and order_code='{$item['trade_id']}'")->save($data);
					$update_num++; 
				}
				else{						
					$this->update_cash_jifenbao_integral($cashbac['cacheback'],$cashbac['integralback'],$data['uid']);
					$data['is_update']=1;  //1表示此数据已经返现了，再次同步的是不将不进行返现								
					$order_mod->add($data);	
					$add_num++;
				}		
				
			}	
		}		
		if ($p>=$pages) {
			//记录采集时间				
			$this->collect_success('订单获取完成', '', 'getorder');
		} else {			
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，'.$now_date.'插入<em class="blue">'.$add_num.'</em>条数据,更新<em class="blue">'.$update_num.'</em>条数据,共 <em class="blue">'.$pages.'</em> 页', U('miao_order/get_tao_order_jump', array('star_data'=>$star_data,'end_data'=>$end_data,'p'=>$p+1)));
		}
	}	
	//采集59秒数据跳转页面
	public function getorder_jump()
	{
		$order_mod=D('miao_order');		
		$star_data= isset($_GET['star_data']) ? trim($_GET['star_data']) : '';
		$end_data = isset($_GET['end_data'])? trim($_GET['end_data']) :'';
		if(empty($star_data)||empty($end_data)){
			$this->collect_error('开始日期或者结束日期不能为空',U('miao_order/getorder'),'getorder');
		}		
		$star_ymd_data=date('Ymd',strtotime($end_data));
		$end_ymd_data=date('Ymd',strtotime($star_data));

		$tmp='';
		if($star_ymd_data>$end_ymd_data){
			$tmp=$end_ymd_data;
			$end_ymd_data=$star_ymd_data;
			$star_ymd_data=$tmp;
		}
		
		//获取总共有多少条数据
		$pages = get_diff_date($star_data,$end_data);	
		
		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页		
		$now_date=date('Ymd',strtotime("$star_ymd_data +$p day"));;   //要获取的当前日期	
		
		$miao_api = $this->miao_client();   //获取59秒api设置信息
		$order_report=$miao_api->ListOrderReport($now_date);
		$order_report_data=$order_report['orders']['order'];
		if(!empty($order_report_data)){
			if(IsTwoArray($order_report_data)){
				$order_report_data=array($order_report_data);
			}
		}	
		$add_num=0;		
		$update_num=0;			
		if(count($order_report_data)>0){
			foreach ($order_report_data as $item) {
				$cashbac=cashback($item['commission'],$this->setting['cashback_rate'],$this->setting['integralback_rate']);
				//获取用户名
				if(is_array($item['outer_code'])){    //如果outcode为空的话，从服务器获取的内容是一个数组
					$item['outer_code']=0;
				}				
				$user_info_data=$this->user_mode->where("id='{$item['outer_code']}'")->find();				
				$data=array(
				'uid'=>$user_info_data['id'],        //暂时放这个             out_code
				'username'=>$user_info_data['name'],
				'order_id'=>$item['order_id'],
				'order_code'=>$item['order_code'],
				'seller_name'=>$item['seller_name'],
				'order_time'=>$item['created'],
				'item_price'=>$item['order_amount'],
				'commission'=>$item['commission'],
				'cash_back'=>$cashbac['cacheback'],   //返现，这里通过公共函数计算
				'status'=>$item['status'],
				'item_count'=>1
				);					
				$one_rel=$order_mod->where("order_id='{$item['order_id']}' and order_code='{$item['order_code']}'")->find();
				//如果此数据存在则执行更新操作
				if(count($one_rel)>0){
					//查看看该订单是否已经确认 并更新完毕
					if($data['status']=='有效'){
						if($one_rel['is_update']==0){
							//执行 update_cash_integral 方法
							$this->update_cash_integral($cashbac['cacheback'],$cashbac['integralback'],$data['uid']);							
							$data['is_update']=1;  //1表示此数据已经返现了，再次同步的是不将不进行返现
						}
					}
					$order_mod->where("order_id='{$item['order_id']}' and order_code='{$item['order_code']}'")->save($data);
					$update_num++; 
				}
				else{	
					if($data['status']=='有效'){					
						//执行 update_cash_integral 方法
						$this->update_cash_integral($cashbac['cacheback'],$cashbac['integralback'],$data['uid']);
						$data['is_update']=1;  //1表示此数据已经返现了，再次同步的是不将不进行返现
					}					
					$order_mod->add($data);	
					$add_num++;				
				}			
				
			}	
		}		
		if ($p>=$pages) {
			//记录采集时间				
			$this->collect_success('订单获取完成', '', 'getorder');
		} else {			
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，'.$now_date.'插入<em class="blue">'.$add_num.'</em>条数据,更新<em class="blue">'.$update_num.'</em>条数据,共 <em class="blue">'.$pages.'</em> 页', U('miao_order/getorder_jump', array('star_data'=>$star_data,'end_data'=>$end_data,'p'=>$p+1)));
		}
	}
	//增加返现记录以及增加用户积分与金钱
	private function update_cash_integral($money,$integral,$uid){
		//echo $money.'---'.$integral.'--'.$uid;
		$user_mode=$this->user_mode;		
		$user_info_mode=$this->user_info;		
		
		$user_rel=$user_mode->where("id=$uid")->field('id,name')->find();
		if($user_rel){   //如果此用户存在的话执行操作
			$user_info_mode->where("uid=$uid")->setInc('money',$money); // 增加用户金钱
			$user_info_mode->where("uid=$uid")->setInc('integral',ceil($integral));  //增加用户积分
			//获取用户信息			
			//更新返现记录表
			$cash_back_log=D('cash_back_log');
			$time=time();
			$last_info=$cash_back_log->where("uid='{$uid}'")->order('id desc')->limit(1)->find();		
			if(count($last_info)>0){  //如果存在这条数据的换执行入库操作
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_money'=>$last_info['after_money'],
					'after_money'=>$last_info['after_money']+$money,
					'in_money'=>$money,
					'out_money'=>0,
					'after_jifenbao'=>$last_info['after_jifenbao'],
					'type'=>1,
					'time'=>$time,
					'info'=>'返现收入',
					'sign'=>md5($uid.$user_rel['name'].$time),
				);
				
			}
			else{
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_money'=>0,
					'after_money'=>$money,
					'in_money'=>$money,
					'out_money'=>0,
					'after_jifenbao'=>0,
					'type'=>1,
					'info'=>'返现收入',
					'time'=>$time
				);
			}
			$cash_back_log->add($log_data);
		}
	}	
	//增加返现记录以及增加用户积分与金钱
	private function desc_cash_integral($money,$integral,$uid,$info){
		//echo $money.'---'.$integral.'--'.$uid;
		$user_mode=$this->user_mode;		
		$user_info_mode=$this->user_info;			
		
		$user_rel=$user_mode->where("id=$uid")->field('id,name')->find();
		if($user_rel){   //如果此用户存在的话执行操作
			$user_info_mode->where("uid=$uid")->setDec('integral',ceil($integral));  //增加用户积分
			$user_info_mode->where("uid=$uid")->setDec('money',$money); // 增加用户金钱
			//获取用户信息
			
			//更新返现记录表
			$cash_back_log=D('cash_back_log');
			$time=time();
			$last_info=$cash_back_log->where('uid='.$uid.'')->order('id desc')->limit(1)->find();		
			if(count($last_info)>0){  //如果存在这条数据的换执行入库操作
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_money'=>$last_info['after_money'],
					'after_money'=>$last_info['after_money']-$money,
					'in_money'=>0,
					'out_money'=>$money,
					'after_jifenbao'=>$last_info['after_jifenbao'],
					'type'=>2,      //1表示收入  2表示支出
					'time'=>$time,
					'info'=>$info.'支出',
					'sign'=>md5($uid.$user_rel['name'].$time),
				);
				
			}			
			$cash_back_log->add($log_data);
		}
	}	
	
	
	
	
	//增加返现记录以及增加用户积分与集分宝   （用于淘宝数据）
	private function update_cash_jifenbao_integral($jifenbao,$integral,$uid){	
		$user_mode=$this->user_mode;		
		$user_info_mode=$this->user_info;
		$user_rel=$user_mode->where("id=$uid")->field('id,name')->find();	
		if($user_rel){   //如果此用户存在的话执行操作
			$user_info_mode->where("uid=$uid")->setInc('integral',ceil($integral));  //增加用户积分
			$user_info_mode->where("uid=$uid")->setInc('jifenbao',ceil($jifenbao)); // 增加用户金钱
			//获取用户信息
			
			//更新返现记录表
			$cash_back_log=D('cash_back_log');
			$time=time();
			$last_info=$cash_back_log->where("uid='{$uid}'")->order('id desc')->limit(1)->find();
			if(count($last_info)>0){  //如果存在这条数据的换执行入库操作
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_jifenbao'=>$last_info['after_jifenbao'],
					'after_jifenbao'=>$last_info['after_jifenbao']+$jifenbao,
					'after_money'=>$last_info['after_money'],
					'in_jifenbao'=>$jifenbao,
					'out_jifenbao'=>0,
					'type'=>1,
					'time'=>$time,
					'info'=>'返现收入',
					'sign'=>md5($uid.$user_rel['name'].$time),
				);
				
			}
			else{
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_jifenbao'=>0,
					'after_jifenbao'=>$jifenbao,
					'in_jifenbao'=>$jifenbao,
					'out_jifenbao'=>0,
					'after_money'=>0,
					'type'=>1,
					'info'=>'返现收入',
					'time'=>$time
				);
			}
			$cash_back_log->add($log_data);
		}
	}	
	//增加返现记录以及减少用户积分与集分宝
	private function desc_cash_jifenbao_integral($jifenbao,$integral,$uid,$info){
		//echo $money.'---'.$integral.'--'.$uid;
		$user_mode=$this->user_mode;		
		$user_info_mode=$this->user_info;	
		//获取用户信息
		$user_rel=$user_mode->where("id=$uid")->field('id,name')->find();		
		if($user_rel){   //如果此用户存在的话执行操作
			$user_info_mode->where("uid=$uid")->setDec('integral',ceil($integral));  //增加用户积分			
			$user_info_mode->where("uid=$uid")->setDec('jifenbao',$jifenbao); // 增加用户金钱
			//更新返现记录表
			$cash_back_log=D('cash_back_log');
			$time=time();
			$last_info=$cash_back_log->where('uid='.$uid.'')->order('id desc')->limit(1)->find();		
			if(count($last_info)>0){  //如果存在这条数据的换执行入库操作
				$log_data=array(
					'uid'=>$uid,
					'uname'=>$user_rel['name'],
					'before_jifenbao'=>$last_info['after_jifenbao'],
					'after_jifenbao'=>$last_info['after_jifenbao']-$jifenbao,
					'in_jifenbao'=>0,
					'out_jifenbao'=>$jifenbao,
					'after_money'=>$last_info['after_money'],
					'type'=>2,      //1表示收入  2表示支出
					'time'=>$time,
					'info'=>$info.'支出',
					'sign'=>md5($uid.$user_rel['name'].$time),
				);
				
			}			
			$cash_back_log->add($log_data);
		}
	}	
	
	//采集成功跳转
	public function collect_success($message, $jump_url, $dialog='')
	{
		$this->assign('message', $message);
		if(!empty($jump_url)) $this->assign('jump_url', $jump_url);
		if(!empty($dialog)) $this->assign('dialog', $dialog);
		$this->display(APP_PATH.'Tpl/'.C('DEFAULT_THEME').'/miao_order/collect_success.html');
		exit;
	}
	public function collect_error($message,$jump_url,$dialog='')
	{
		$this->assign('message', $message);		
		if(!empty($jump_url)) $this->assign('jump_url', $jump_url);
		if(!empty($dialog)) $this->assign('dialog', $dialog);
		$this->display(APP_PATH.'Tpl/'.C('DEFAULT_THEME').'/miao_order/collect_error.html');		
		exit;		
	}
}
?>