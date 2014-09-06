<?php
class exchange_goodsAction extends baseAction
{
	public function index()
	{
	   
	    $ex_mod = D('exchange_goods');
		//搜索
		$where = '1=1 AND status=1 AND (begin_time <= '.time().' OR begin_time = 0) AND (end_time >= '.time().' OR end_time = 0)';
		
		import("ORG.Util.Page");
		$count = $ex_mod->where($where)->count();
		$p = new Page($count,20);
		$ex_list = $ex_mod->field('id,img,name,stock,buy_count,user_num,integral')->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();

		$page = $p->show();
		$this->assign('page',$page);
		$this->seo['seo_title'] = '积分兑换';
	    $this->seo['seo_title'] = $this->seo['seo_title'] . ' - ' . $this->setting['site_name'];
	    $this->seo['seo_keys'] = '积分兑换';
	    $this->seo['seo_desc'] = '积分兑换';
		
		$this->assign('ex_list',$ex_list);		
		$this->assign('seo',$this->seo);
		
		$model=new Model();
		//积分排行
		$integral_sql='select a.id as id,a.name as name, b.integral as integral from '.C('DB_PREFIX').'user as a left join '.C('DB_PREFIX').'user_info as b on a.id=b.uid order by b.integral desc limit 0,10';
		$top10integral=$model->query($integral_sql);	
	   		
		//兑换排行
		$ex_sql='select a.id as id,a.name as name, b.exchange_num as exchange_num from '.C('DB_PREFIX').'user as a left join '.C('DB_PREFIX').'user_info as b on a.id=b.uid order by b.integral desc limit 0,10';
		
		$top10ex=$model->query($ex_sql);
		$this->assign('top10integral',$top10integral);
		$this->assign('top10ex',$top10ex);
		//获取seo信息
		$this->nav_seo('exchange_goods','nav',5);
		
		$this->display();
	}
	//提交订单
	public function order(){
		$goods_id=isset($_POST['goods_id'])&&intval($_POST['goods_id'])?$_POST['goods_id']:0;
		$uid=isset($_COOKIE['user']['id'])?$_COOKIE['user']['id']:'';	
		$user_rel=$this->user_mod->where('id='.$uid.'')->find();

		$ex_goods_mod = D('exchange_goods');
		$ex_goods_rel=$ex_goods_mod->where('id='.$goods_id.'')->find();

//		//查询order表里面，用户购买商品的次数
		$ex_order_mod=D('exchange_order');

		$info_data=array();

		$info_data['address']=isset($_POST['address'])?$_POST['address']:'';
		$info_data['zip']=isset($_POST['zip'])?$_POST['zip']:'';
		$info_data['consignee']=isset($_POST['consignee'])?$_POST['consignee']:'';
		$info_data['mobile_phone']=isset($_POST['mobile_phone'])?$_POST['mobile_phone']:'';
		$info_data['fax_phone']=isset($_POST['fax_phone'])?$_POST['fax_phone']:'';
		$info_data['email']=isset($_POST['email'])?$_POST['email']:'';
		$info_data['qq']=isset($_POST['qq'])?$_POST['qq']:'';			
		$info_data['create_time']=time();
		$info_data['uid']=$uid;
		//更新收货人表
		if(!empty($info_data['address'])){
			$consignee_mod=D('user_consignee');   //收货人
			
			$consignee_rel=$consignee_mod->where('uid='.$uid.'')->find();
			if(count($consignee_rel)>0){  //执行更新操作
				$consignee_mod->where('uid='.$uid.'')->save($info_data);
			}
			else{
				$consignee_mod->add($info_data);   //入库操作
			}
		}
		
		//订单表入库
		$info_data['remark']=isset($_POST['remark'])?$_POST['remark']:'';	
		$info_data['data_name'] = $ex_goods_rel['name'];
		$info_data['sn'] = gmtTime().mt_rand(0,1000);
		$info_data['goods_status'] = 0;
		$info_data['order_score'] = $ex_goods_rel['integral'];
		$info_data['data_num'] = 1;		
		$info_data['user_name'] =$user_rel['name'];
		$info_data['goods_id'] = $goods_id;
		$info_data['create_time'] = time();
		$info_data['update_time'] = time();
		$ex_last_id=$ex_order_mod->add($info_data);  //增加订单
		//更新exchange_goods 以及user_info表
		if($ex_last_id){			
			$ex_goods_mod->where('id='.$goods_id.'')->setInc('buy_count',1);
			$this->user_info->where('uid='.$uid.'')->setDec('integral',$info_data['order_score']);
			//更新用户兑换记录
			$user_info['exchange_num']=$ex_order_mod->field('uid')->where('uid='.$uid.'')->count();
			$this->user_info->where('uid='.$uid.'')->save($user_info);
		}	
		exit('success');
	}
	public function check_info(){
		$goods_id=isset($_POST['goods_id'])&&intval($_POST['goods_id'])?$_POST['goods_id']:0;
		$uid=isset($_COOKIE['user']['id'])?$_COOKIE['user']['id']:'';	
		//$user_rel=$this->user_mod->where('id='.$uid.'')->find();
		$user_info_rel=$this->user_info->where('uid='.$uid.'')->find();
		if(count($user_info_rel)<=0){
			//调取登录窗口
			exit('not_login');
		}
		$score=$user_info_rel['integral'];		
		$ex_mod = D('exchange_goods');
		$ex_goods_rel=$ex_mod->where('id='.$goods_id.'')->find();
		if($goods_id==0){
			exit('no_goods');
		}
		if(count($ex_goods_rel)<=0){
			exit('no_goods');
		}
		//积分不足
		if($score<$ex_goods_rel['integral']){					
			exit('score_short');		
		}	
		if($ex_goods_rel['stock'] <= intval($ex_goods_rel['buy_count']))
		{
			exit('stock_short');		
		}

			//查询order表里面，用户购买商品的次数
		$ex_order_mod=D('exchange_order');
		
		$sql='SELECT SUM(data_num) as maxnum FROM '.C('DB_PREFIX').'exchange_order WHERE goods_id = '.$goods_id.' AND uid = '.$uid.'';		
		$ex_order_num=$ex_order_mod->query($sql);		
		if($ex_order_num[0]['maxnum'] >= intval($ex_goods_rel['user_num']))
		{
			exit('max_exchange');  //已经超过兑换次数
		}
	}
	
	
	public function order_dialog(){
		$uid=isset($_COOKIE['user']['id'])?$_COOKIE['user']['id']:'';	
		$uid_rel=$this->user_mod->where('id='.$uid.'')->find();
		if(count($uid_rel)<=0){
			//调取登录窗口
			exit('not_login');
		}		
		$consignee=D('user_consignee');
		
		$consignee_data=$consignee->where('uid='.$uid.'')->find();		
	//	print_r($consignee_data);
		$this->assign('consignee_data',$consignee_data);
		$this->display();
	}
}