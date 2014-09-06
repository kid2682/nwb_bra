<?php
class indexAction extends baseAction {
	function index() {	
	    //分类数据
		$focus_mod = D('focus');
        if(S('index_group_cates')){
            $index_group_cates = S('index_group_cates');
        }else{
            $index_group_cates = $this->get_index_group_cates();
            S('index_group_cates',$index_group_cates,C('INDEX_GROUP_CATES'));
		}
        $this->assign('index_group_cates', $index_group_cates);
		//热门活动
		$article_mod = M('article');
        //头条
		$top_actives = $article_mod->cache('TOP_ACTIVES',C('TOP_ACTIVES'))->where('is_best=1')->order('add_time DESC')->find();           
		$this->assign('top_actives', $top_actives);
        //列表
		$hot_actives = $article_mod->cache('HOT_ACTIVES',C('TOP_ACTIVES'))->where('is_hot=1')->limit('1,5')
			->order('is_hot DESC,add_time DESC')->select();
		$this->assign('hot_actives', $hot_actives);
		//轮播器
        $ad_list = $focus_mod->where('cate_id=1 AND status=1')->order('ordid DESC')->select();
        $this->assign('ad_list', $ad_list);
		$this->assign('seo', $this->seo);
		//调取最新喜欢的数据
	  	if(S('index_lately_like')){
            $lately_like = S('index_lately_like');
        }else{
            $lately_like = $this->lately_like();
            S('index_lately_like',$lately_like,'180');  //缓存3分钟
		}
		$this->assign('lately_like',$lately_like);
		//获取返现商家信息
		if(S('index_rec_seller')){
            $rec_seller = S('index_rec_seller');
        }else{
            $rec_seller = $this->rec_seller();
            S('index_rec_seller',$rec_seller,'300');  //缓存5分钟
		}
		if(count($rec_seller)<=0){
			$rec_seller='';
		}
		
		if($this->setting['display_b2c_ad']==1){
			//动态广告系统
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$adv_data = $miao_api->AdsGet('', '468x60');
			$ad_rel=$adv_data['ads']['ad'];
			$ad_rel=getRandArray($ad_rel);		
			//print_r($ad_rel);
			if(count($ad_rel)>0){
				$this->assign('ad_rel',$ad_rel);
			}
		}
		$this->assign('rec_seller',$rec_seller);   //推荐商家		
		$this->display();
	}
	function get_index_group_cates() {
		$items_cate_mod = M('items_cate');
		$items=M("items");
		//查找需要显示的大分类
		$index_group_cates = $items_cate_mod->where("pid=0 AND is_hots=1 and status=1")->order('ordid ASC')->select();

		foreach ($index_group_cates as $key => $val) {
			//排序查找子分类
			//二级分类
			$cate2=$items_cate_mod
				->where("pid=" . $val['id']." and status=1")->limit("0,10")
				->order("ordid ASC")->select();
			$ids="-1";
			foreach($cate2 as $cate2_key=>$cate2_val){
				$ids.=','.$cate2_val['id'];
			}
			//三级分类
			$index_group_cates[$key]['s'] = $items_cate_mod
				->where("pid in(".$ids.") and status=1")->limit("0,10")
				->order("ordid ASC")->select();
			//print_r($items_cate_mod->getLastSql());exit;
			//查找需要首页显示的子分类
			$g_result = $items_cate_mod
				->where("pid in(" . $ids. ") AND is_hots=1 and status=1")
				->order("ordid ASC")->select();
			foreach ($g_result as $gkey => $gval) {
				$g_result[$gkey]['items'] = $this->get_group_items($gval['id']);
			}
			//查询下面9个显示首页的商品图片
			$index_group_cates[$key]['g'] = $g_result;
		}
		//print_r($index_group_cates);exit;
		return $index_group_cates;
	}
	private function lately_like(){
		$like_list_mod=$this->like_list_mod;

		$like_list=$like_list_mod->order('add_time DESC')->limit(28)->select();		
		
		$like_list_array=array();
		
		foreach ($like_list as $value){
			$item_info=$this->items_mod->where("id={$value['items_id']}")->find();
			$user_info=$this->user_mod->where("id={$value['uid']}")->relation('user_info')->find();
			//模拟最新喜欢的时间
			$time=ceil((time()-$value['add_time'])/60);
			if(!empty($this->setting['lately_like_max'])&&is_numeric($this->setting['lately_like_max'])){
				if($time>$this->setting['lately_like_max']){
					if(!empty($this->setting['lately_like_rand'])){		
						$str=str_replace('，', ',', $this->setting['lately_like_rand']);						
						$arr=explode(',', $str);						
						$time=rand($arr[0], $arr['1']);
					}
				}
			}
			if(!is_numeric($time)){
				$time=ceil((time()-$value['add_time'])/60);
			}
			$like_list_array[]=array(
				'title'=>$item_info['title'],
				'img'=>$item_info['img'],
				'items_id'=>$value['items_id'],
				'uid'=>$value['uid'],
				'time'=>$time,
				'user_img'=>'data/user/avatar.gif',
				'uname'=>$user_info['name'],
			);			
		}		
		return $like_list_array;		
	}
	private function rec_seller(){
		//推荐返利商家
		$seller_list_mod=D('seller_list');
		$rec_seller=$seller_list_mod->cache('REC_SELLER',C('REC_SELLER'))->where("status='1' AND recommend='1'")->select();
		
		$rec_seller_arr=array();
		foreach ($rec_seller as $val){
			$val['click_url']=base64_encode($val['click_url']);
			$rec_seller_arr[]=$val;
		}		
		return $rec_seller_arr;		
	}
}