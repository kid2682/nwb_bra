<?php
class auto_collectAction extends baseAction{
	//b2c自动采集
	public function index(){
		//file_put_contents('test.txt', $_GET['id'].'nihao');	
		set_time_limit(0);
		$this->autoCollect();
		
	}
	//淘宝自动采集
	public function taoindex(){
		set_time_limit(0);
		$this->autoCollectTao();
	}
    //自动采集b2c
    function autoCollect(){
       		date_default_timezone_set('Asia/Shanghai');       	
       		$auto_collect_mod=D('auto_collect');
            $p_rel=$auto_collect_mod->where("id='p'")->find();
            $cate = $this->item_cate(); 
            $p=$p_rel['value'];          
			foreach ($cate as $key=>$value){
				$this->collect_items($value['id'], $value['keywords'],$p);
			}
			if($p<98){ 
            	$auto_collect_mod->where("id='p'")->save(array('value'=>$p+1));	
            } 
            else{//当页数大于98页的时候重新开始从第一页采集
            	$auto_collect_mod->where("id='p'")->save(array('value'=>1));	
            }  
            return;
    }
	//自动采集淘宝
    function autoCollectTao(){
       		date_default_timezone_set('Asia/Shanghai');       	
       		$auto_collect_mod=D('auto_collect');
            $p_rel=$auto_collect_mod->where("id='i'")->find();
            $cate = $this->item_cate(); 
            $i=$p_rel['value'];          
			foreach ($cate as $key=>$value){
				$this->collect_items_tao($value['id'], $value['keywords'],$i);
			}
			if($i<40){ 
            	$auto_collect_mod->where("id='i'")->save(array('value'=>$i+1));	
            } 
            else{//当页数大于98页的时候重新开始从第一页采集
            	$auto_collect_mod->where("id='i'")->save(array('value'=>1));	
            }  
            return;
    }
    //获取三级分类
    function item_cate(){
    	if(S('auot_collect_item_category')){
    		 $item_category = S('auot_collect_item_category');
    	}
    	else{
    		$item_cate_mod=$this->items_cate_mod;
			$rel1=$item_cate_mod->where("pid=0")->select();
			$instr='';
			foreach ($rel1 as $value){
				$instr.=$value['id'].',';
			}
			$instr=substr($instr, 0,-1);			
			$item_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0")->order('id desc')->select();
			S('auot_collect_item_category',$item_category,'3600');
    	}       
		return $item_category; 
    }
	//59秒数据采集入库
	private function miao_collect_insert($item, $cate_id)
	{
		$items_mod = D('items');
		$items_tags_mod = D('items_tags');
		$items_tags_item_mod = D('items_tags_item');
		//需要判断商品是否已经存在
		$isset = $items_mod->where("item_key='".$item['item_key']."'")->getField('id');
		if ($isset) {
			return;
		}
		$add_time = time();
		
		if (strpos($item['pic_url'], 'taobao') !== false){             
			$item['img'] =  $item['pic_url'].'_210x1000.jpg';
			$item['simg'] = $item['pic_url'].'_64x64.jpg';	
			//$item['bimg'] = $item['pic_url'].'_460x460.jpg';
			$item['bimg'] = $item['pic_url'];	
        }else{        	
			$item['img'] = str_replace('.jpg', '_210x1000.jpg', $item['pic_url']);
			$item['simg'] = str_replace('.jpg', '_60x60.jpg', $item['pic_url']);
			//$item['bimg'] = str_replace('.jpg', '_460x460.jpg', $item['pic_url']);
			$item['bimg'] = $item['pic_url'];	
        }
		if($item['popular']==0){
			$item['popular']=1;
		}
		
		$item_id = $items_mod->add(array(
		    'title' => ReplaceKeywords(strip_tags($item['title'])),
		    'cid' => $cate_id,
		    'sid' => $item['sid'],
		    'item_key' => $item['item_key'],
		    'img' => $item['img'],
		    'simg' => $item['simg'],
		    'bimg' => $item['bimg'],
		    'price' => $item['price'],
		    'url' => $item['click_url'],
		    'likes' => $item['popular'],
			'seller_name'=>$item['seller_name'],
			'cash_back_rate'=>$item['cashback_scope'],
		    'haves' => 1,
		    'add_time' => $add_time,		
		));
	}
	/*
	 *
	 * $cate_id 采集的分类id
	 * $keywords 采集的关键字
	 * $p 采集的开始数
	 * $num 采集的数量
	 * 
	 * */
	
	public function collect_items($cate_id,$keywords,$p=1,$num=10)
	{	
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_miao_mod = D('collect_miao');
		$miao_api = $this->miao_client();   //获取59秒api设置信息		
		$data=$miao_api->ListItemsSearch('',$keywords, '', '0', $p, $num);
		$goods_list= $data['items_search']['items']['item'];
		$sid = $items_site_mod->where("alias='miao'")->getField('id');
		$items_nums = 0;
		foreach ($goods_list as $item) {
			$item = (array)$item;
			$item['item_key'] = 'miao_'.$item['iid'];   //用于判断此商品是否存在
			$item['sid'] = $sid;
			$this->miao_collect_insert($item, $cate_id);   //数据入库
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}
		//记录采集时间
		$islog = $collect_miao_mod->where('cate_id='.$cate_id)->count();
		if ($islog) {
			$collect_miao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
		} else {
			$collect_miao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
		}
	}
	
	
	/*
	 *
	 * $cate_id 采集的分类id
	 * $keywords 采集的关键字
	 * $p 采集的开始数
	 * $num 采集的数量
	 * 
	 * */
	
	public function collect_items_tao($cate_id,$keywords,$i=1,$num=10)
	{	
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_taobao_mod = D('collect_taobao');
		
		$tb_top = $this->taobao_client();
		$req = $tb_top->load_api('TaobaokeItemsGetRequest');
		$req->setFields("num_iid,title,nick,pic_url,price,click_url,shop_click_url,seller_credit_score,item_location,volume,commission");		
		$req->setPid($this->setting['taobao_pid']);
		$req->setNick($this->setting['taobao_usernick']);
		$req->setStartCredit($this->setting['levelstart']);
		$req->setEndCredit($this->setting['levelend']);
		$req->setStartCommissionRate($this->setting['commission_rate_min']);
		$req->setEndCommissionRate($this->setting['commission_rate_max']);
		$req->setKeyword($keywords);
		
		$req->setPageNo($i);
		$req->setPageSize($num);
		$resp = $tb_top->execute($req);
		$goods_list = (array)$resp->taobaoke_items;
		$sid = $items_site_mod->where("alias='taobao'")->getField('id');		
		$items_nums = 0;
		foreach ($goods_list['taobaoke_item'] as $item) {
			$item = (array)$item;
			$item['item_key'] = 'taobao_'.$item['num_iid'];
			$item['sid'] = $sid;
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}
		//记录采集时间
		$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
		if ($islog) {
			$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
		} else {
			$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
		}			
	}
	//淘宝数据采集入库
	private function tao_collect_insert($item, $cate_id)
	{
		$items_mod = D('items');
		$items_tags_mod = D('items_tags');
		$items_tags_item_mod = D('items_tags_item');
		
		//需要判断商品是否已经存在
		$isset = $items_mod->where("item_key='".$item['item_key']."'")->getField('id');
		if ($isset) {
			return;
		}
		$add_time = time();
		$item_id = $items_mod->add(array(
		    'title' => ReplaceKeywords(strip_tags($item['title'])),
		    'cid' => $cate_id,
		    'sid' => $item['sid'],
		    'item_key' => $item['item_key'],
		    'img' => $item['pic_url'].'_210x1000.jpg',
		    'simg' => $item['pic_url'].'_64x64.jpg',
		    'bimg' => $item['pic_url'],
		    'price' => $item['price'],
		    'url' => $item['click_url'],
		    'likes' => $item['volume'],
			'seller_name'=>'淘宝',
			'cash_back_rate'=>round($item['commission'],2).'元',
		    'haves' => $item['volume'],
		    'add_time' => $add_time,
		
		));
		//处理标签
		$tags = $items_tags_mod->get_tags_by_title(strip_tags($item['title']));
		if ($tags) {
			$tags = array_unique($tags);
			foreach ($tags as $tag) {
				$isset_id = $items_tags_mod->where("name='".$tag."'")->getField('id');
				if ($isset_id) {
					$items_tags_mod->where('id='.$isset_id)->setInc('item_nums');
					$items_tags_item_mod->add(array(
			            'item_id' => $item_id,
			            'tag_id' => $isset_id
					));
				} else {
					$tag_id = $items_tags_mod->add(array('name'=>$tag));
					$items_tags_item_mod->add(array(
			            'item_id' => $item_id,
			            'tag_id' => $tag_id
					));
				}
			}
		}
	}
	
	public function collect_seller_list($page=1,$date=''){
		$miao_api=$this->miao_client();	  //初始化api接口
		$seller_list=D('seller_list');	  //商家列表	
		$seller_cate=D('seller_cate');	  //商家列表
		$seller_list_cate=D('seller_list_cate');	  //商家对应分类		
		$page_size=20;
		//$page = $page;//当前页	
		if(empty($date)){
			$date=date('Ymd',time());
		}
		//$date = isset($_GET['date']) && intval($_GET['date']) ? intval($_GET['date']) : date('Ymd',time());	//当前更新的时间		
		//调取数据入库
		$data=$this->ShopListGet($miao_api, $page, $page_size);   //获取api商家数据		
		$data=setFormString($data);		 //对数组进行转义		
		if(isset($data[0])){
			foreach ($data as $k=>$v){
				//$cid=$value['cid'];				
				$sid=$v['sid'];						
				$name=$v['name'];			
				$net_logo=$v['logo'];
				//如果不是二维数组，转换为二维数组
				if(IsTwoArray($v['sellercats']['sellercat'])){
					$v['sellercats']['sellercat']=array($v['sellercats']['sellercat']);
				}	
				$cid='';
				$_where =("sid=$sid");	
				$has_data='';
				$has_data=$seller_list->where($_where)->find();
				//print_r($v['sellercats']['sellercat']);
				
				if(empty($has_data)){    //判断数据是否存在如果存在则不插入数据库		
					if(count($v['sellercats']['sellercat'])>0){
						foreach ($v['sellercats']['sellercat'] as $value){		
								$Auto_increment_id=$seller_list->query('SHOW TABLE STATUS LIKE \''.C('DB_PREFIX').'seller_list\'');					
								$cate_id= $seller_cate->where("cid='{$value['cid']}'")->field('id')->find();  //分类id							
								$list_cate_data=array(
									'list_id'=>$Auto_increment_id[0]['Auto_increment'],
									'cate_id'=>$cate_id['id']
								);							
								$seller_list_cate->add($list_cate_data);											
						}	
					}
				}				
				$click_url=$v['click_url'];
				$sort=10;
				$desc=$v['desc'];
				if(is_array($desc)){
					$desc=$name;
				}						
				$status=1;
				//是否免费送货 
				if($v['freeshipment']=='False'){
					$freeshipment=0; 
				}
				else{
					$freeshipment=1;
				}
				//是否支持分期付款 
				if($v['installment']=='False'){
					$installment=0;
				}
				else{
					$installment=1;
				}
				//是否有发票
				if($v['has_invoice']=='False'){
					$has_invoice=0; 
				}
				else{
					$has_invoice=1;
				}
				$cash_back_rate=$v['cashbacks']['cashback']['scope'];
				
				$_updateData=array(
					'sid'=>$sid,							
					'name'=>$name,
					'net_logo'=>$net_logo,					
					'click_url'=>$click_url,											
					'status'=>$status,
					'sort'=>$sort,	
					'description'=>$desc,
					'freeshipment'=>$freeshipment,
					'installment'=>$installment,
					'has_invoice'=>$has_invoice,
					'cash_back_rate'=>$cash_back_rate,
					'update_time'=>$date								
				);								
				if(count($has_data)>0){   //如果有数据执行更新操作操作
					$seller_list->where($_where)->save($_updateData);				
				}
				else{				//如果有数据执行增加操作
					$seller_list->where($_where)->add($_updateData);
				}
			}
//			$this->collect_success('正在采集第 <em class="blue">'.$page.'</em> 页，请稍后',
//			U('seller_list/addb2cdata', array('page'=>$page+1,'date'=>$date)));		
			$this->collect_seller_list($page+1,$date);
		}
		else{		
			//采集完成删除下架商家
			$seller_list_mod=D('seller_list');
			$seller_list_cate_mod=D('seller_list_cate');
			$rel=$seller_list_mod->where("update_time!='{$date}'")->select();
			$ids='';
			foreach ($rel as $value){
				$ids.=$value['id'].',';
				//$seller_list_cate_mod->where("list_id='{$value['id']}'")->delete();          //删除商家信息
				
				$Data=array(
					'status'=>0,	
				);	
				$seller_list_mod->where("id='{$value['id']}'")->save($Data);
			}
			//$ids=substr($ids, 0,-1);
			//$result=$seller_list_mod->delete($ids);
			
			//status
			$this->collect_success('数据同步完成', '', 'addb2cdata');			
		}		
	}
	private function ShopListGet($miao_api,$page_no,$page_size){
		$data = $miao_api->ListShopListGet('',Array('page_no' =>$page_no, 'page_size' =>$page_size));
		return $data['shops']['shop'];
	}
	
}
?>