<?php
class rebateAction extends baseAction{
	public function index(){
		$url = isset($_REQUEST['url']) ? trim($_REQUEST['url']) : '';	
		$url = url_parse(urldecode($url));
		//echo $url;			
		if (strpos($url, 'tmall.com')!== false || strpos($url, 'taobao.com')!== false){  //说明此商品是淘宝的商品			
			$num_iid=get_id($url);	
			if(empty($num_iid)||!is_numeric($num_iid)){//没有查到该商品
				$this->assign('url',$url);
				$this->assign('nogoods',1);
				$this->display();
				exit;
			}				
			$tb_top = $this->taobao_client();
			$req = $tb_top->load_api('TaobaokeItemsDetailGetRequest');			
			$req->setFields("num_iid,detail_url,title,nick,pic_url,price,click_url,shop_click_url,item_img");
			$req->setPid($this->setting['taobao_pid']);
			$req->setNick($this->setting['taobao_usernick']);
			$req->setNumIids($num_iid);			
			$resp = get_object_vars_final($tb_top->execute($req));			
			$data=$resp['taobaoke_item_details']['taobaoke_item_detail'];
			if(count($data)<=0){
				$this->assign('url',$url);
				$this->assign('nogoods',1);
				$this->display();
				exit;
			}
			$this->assign('data',$data);
			$this->assign('num_iid',$num_iid);
			$this->assign('site','tao');
			$this->assign('item',$data['item']);
			$this->assign('cashback_rate',$this->setting['cashback_rate']);
			$this->assign('tb_fanxian_bili',$this->setting['tb_fanxian_bili']);				
			//获取相关商品
			$this->_getRelateitem($tb_top, $num_iid);			
			//获取淘宝返现金额
			$app_key = $this->setting['taobao_appkey'];
			$secret = $this->setting['taobao_appsecret'];	
			$timestamp=time()."000";
			$message = $secret.'app_key'.$app_key.'timestamp'.$timestamp.$secret;
			$mysign=strtoupper(hash_hmac("md5",$message,$secret));
			setcookie("timestamp",$timestamp);
			setcookie("sign",$mysign);
			$this->display();
			exit;			
		}
		else{//59miao 的商品开始	
			$seller_list_mod=D('seller_list');
			//推荐商家
			$rec_seller=$seller_list_mod->field('id,click_url,name,net_logo,site_logo,cash_back_rate')->where("status='1' AND recommend='1'")->order('sort asc,id asc')->select();
			$this->assign('rec_seller',$rec_seller);
			
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$data = $miao_api->ListItemsDetail('',$url);
			//print_r($data);
			if($data['total_results']==0){		
				$this->getSellerInfo($seller_list_mod,$url);	
				$this->assign('url',$url);				
				$this->display('b2c_nogoods');
				exit;
			}else{
				$data=$data['items']['item'];	
			}
			if(count($data)==0){
				$this->getSellerInfo($seller_list_mod,$url);
				$this->assign('url',$url);				
				$this->display('b2c_nogoods');
				exit;
			}
		
			$data['cashback_desc']=str_replace('京东商城不允许将订单返现给用户！', '', $data['cashback_desc']);
			if(!is_array($data)){
				$this->assign('url',$url);				
				$this->display('b2c_nogoods');
				exit;
			}
			$data['seller_url']=base64_encode(urlencode($data['seller_url']));
			$sid=$data['sid'];
			$this->assign('item',$data);	
			//获取当前商家和推荐商家
			$now_seller=$seller_list_mod->field('name,net_logo')->where("sid='{$sid}'")->find();			
			$this->assign('net_logo',$now_seller['net_logo']);
			//当前商家	
			$this->display('b2c');	
		}	
	}	
	private function getSellerInfo($model,$url){
		$domain_arr=explode('.', $url);
		$domain=$domain_arr['1'];
		//如果输入的url地址不对的话推荐到当当	
		if(empty($domain)){
			$domain='dangdang';
		}	
		$where= "site_url LIKE '%".$domain."%'";
		$seler_info=$model->where($where)->find();
		//如果查不到数据的话推荐去购物网站购物
		if(empty($seler_info)){
			$seler_info=$model->where("status='1' AND recommend='1'")->order('sort asc,id asc')->find();
		}		
		if($seler_info['cashback_desc']){
			$seler_info['cashback_desc']=str_replace('京东商城不允许将订单返现给用户！', '', $seler_info['cashback_desc']);	
		}		
		$this->assign('item',$seler_info);	
	}
	
	private function _getRelateitem($tb_top,$num_iid){				
			$reqRelate= $tb_top->load_api('TaobaokeItemsRelateGetRequest');		
			$reqRelate->setFields("title,pic_url,price,num_iid,click_url,commission,commission_rate,commission_num,commission_volume,volume");
			$reqRelate->setPid($this->setting['taobao_pid']);
			$reqRelate->setNick($this->setting['taobao_usernick']);			
			$reqRelate->setRelateType(1);
			$reqRelate->setNumIid($num_iid);
			$reqRelate->setMaxCount(26);
			$reqRelate = get_object_vars_final($tb_top->execute($reqRelate));
			
			$taobaoke_items_relate=$reqRelate['taobaoke_items']['taobaoke_item'];		
		
				
			$relate_items=$taobaoke_items_relate;
			foreach ($relate_items as $key=>$value){	
					
				$relate_items[$key]['pic_url_210']=$value['pic_url'].'_210x1000.jpg';
			}
			//print_r($relate_items);
			$this->assign('relate_items',$relate_items);
	}
}
?>