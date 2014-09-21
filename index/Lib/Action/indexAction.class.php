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

		//轮播器
        $ad_list = $focus_mod->where('cate_id=1 AND status=1')->order('ordid DESC')->select();
        $this->assign('ad_list', $ad_list);
		$this->assign('seo', $this->seo);
        $sql_where='1=1 AND status=1';
        $sql_where.=' AND cid!=0';
        $this->waterfall(10, $sql_where);
		//$this->display();
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