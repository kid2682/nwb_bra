<?php
class promoAction extends baseAction{
	public function index(){
		$miao_api = $this->miao_client();   //获取59秒api设置信息		
		if($this->setting['display_b2c_ad']==1){
			//动态广告系统
			//$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$adv_data = $miao_api->AdsGet('', '468x60');
			$ad_rel=$adv_data['ads']['ad'];
			$ad_rel=getRandArray($ad_rel);	
	       		 if(count($ad_rel)>0){
				$this->assign('ad_rel',$ad_rel);
			}
		}
		$page_size = 18;			
		if(isset($_GET['p'])){
			$p=$_GET['p'];
		}			
		if(!$p || is_null($p))
		{
			$p =1;
		}			
		$data = $miao_api->ListPromosListGet(null,null, '', $p, $page_size);		
		$total_results = $data['total_results'];  //总记录
		$promos = $data['promos']['promo'];
		$p_array=array();
		if(is_array($promos)){
			foreach ($promos as $v){
				$v['seller_url']=base64_encode(urlencode($v['seller_url']));
				$v['click_url']=base64_encode(urlencode($v['click_url']));
				$p_array[]=$v;
			}
		}		
		import("ORG.Util.Page");
		$count = $total_results;
		$p = new Page($count,$page_size);		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('promos',$p_array);
		
		//print_r($promos);
		$promo_parent=$miao_api->ListPromoCats();
		$promo_parent=$promo_parent['promo_cats']['promo_cat'];		
		$this->assign('promo_cate',$promo_parent);  //分类
		//获取seo
		$this->nav_seo('promo','nav',3);
		$this->display();
	}
	//分类列表
	public function cate(){		
		$miao_api = $this->miao_client();   //获取59秒api设置信息
		$cid=isset($_GET['cid'])?intval($_GET['cid']):0;
		$pid=isset($_GET['pid'])?intval($_GET['pid']):0;
		
		$page_size = 18;			
		if(isset($_GET['p'])){
			$p=$_GET['p'];
		}			
		if(!$p || is_null($p))
		{
			$p =1;
		}	
		//用于父类选中
		if(empty($pid)){
			$this->assign('cid',$cid);	
		}else{
			$this->assign('cid',$pid);
		}			
		//分配url的cid用于子类选中
		$this->assign('real_cid',$cid);
		$data = $miao_api->ListPromosListGet(null,null, $cid, $p, $page_size);		
		$total_results = $data['total_results'];  //总记录
		$promos = $data['promos']['promo'];		
		if(isset($promos['pid']))     //判断是一维数组还是二维数组
		{
			$promos = Array($promos);
		}			

		$p_array=array();
		if(is_array($promos)){
			foreach ($promos as $v){
				$v['seller_url']=base64_encode(urlencode($v['seller_url']));
				$v['click_url']=base64_encode(urlencode($v['click_url']));
				$p_array[]=$v;
			}
		}
		
		import("ORG.Util.Page");
		$count = $total_results;
		$p = new Page($count,$page_size);		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('promos',$p_array);
		
		
		
		//父分类
		$promo_parent=$miao_api->ListPromoCats();
		$promo_parent=$promo_parent['promo_cats']['promo_cat'];		
		$this->assign('promo_cate',$promo_parent);  		
		//子分类		
		if(empty($pid)){
			$promo_child=$miao_api->ListPromoCats('',$cid); 		
			
		}else{
			$promo_child=$miao_api->ListPromoCats('',$pid); 		
		}
		$child_data=$promo_child['promo_cats']['promo_cat'];	

		$curent_promo=$miao_api->ListCurentPromoCats('',$cid); 
		
		
		$curent_promo_name=$curent_promo['promo_cats']['promo_cat']['name'];
		//seo信息
		$this->nav_seo('promo','nav',3);
		
		
		$this->assign('curent_name',$curent_promo_name);
		
		$this->assign('child_data',$child_data);
		$this->display();
	}
	
}
?>