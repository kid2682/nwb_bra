<?php
class bsearchAction extends baseAction {
    public function index(){     	
    	$keywords = isset($_REQUEST['keywords']) && trim($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) :''; 
    	$keywords=strip_tags(urldecode($keywords));    	
    	//暂时跳转的页面    	
    	if(strpos($keywords, 'http://')!== false){
    		header('Location: index.php?a=index&m=rebate&url='.$keywords);
    		exit;
    	}    	    	
    	$cid=isset($_REQUEST['cid'])?intval($_REQUEST['cid']):'';
    	$sid=isset($_REQUEST['sid'])?intval($_REQUEST['sid']):'';    	
    	$miao_api = $this->miao_client();   //获取59秒api设置信息	    		
		$page_size = 40;			
		if(isset($_GET['p'])){
			$p=$_GET['p'];
		}			
		if(!$p || is_null($p))
		{
			$p =1;
		}			
		$data = $miao_api->ListItemsSearch('',$keywords, $cid, $sid, $p, 40);		
		$total_results = $data['total_results'];  //总记录
		$search_list = $data['items_search']['items']['item'];		
		if(IsTwoArray($search_list) && count($search_list)>0){
			$search_list=array($search_list);
		}
		$item_categories = $data['items_search']['item_categories']['item_category'];		
		$search_arr=array();
		if(is_array($search_list)){
			foreach ($search_list as $val){
				$val['click_url']=base64_encode(urlencode($val['click_url']));
				$val['seller_url']=base64_encode(urlencode($val['seller_url']));
				$val['pic_url']=str_replace('.jpg', '_210x1000.jpg', $val['pic_url']);
				$search_arr[]=$val;
			} 
		}
		//print_r($search_arr);
		import("ORG.Util.Page");
		if($total_results>99*40){
			$count = 99*40;
		}else{
			$count = $total_results;
		}	
		
		if(count($search_arr)==0){
			$search_arr='';
		}	
		$p = new Page($count,$page_size);		
		$page = $p->show_1();
		$this->assign('keywords',$keywords);
		$this->assign('page',$page);
		$this->assign('items_list',$search_arr);
		$this->assign('cid',$cid);
		$this->assign('sid',$sid);
		//搜索数据分类		
		if(IsTwoArray($item_categories)){
			$item_categories=array($item_categories);
		}        
		$this->assign('item_categories',$item_categories);		
		$this->display();
    }  
    public function getTags(){
    	$this->uc_login_check();     	
    	$iid=isset($_REQUEST['iid'])?trim($_REQUEST['iid']):'';    	
    	$title=isset($_REQUEST['title'])?trim($_REQUEST['title']):''; 
    	if(empty($iid)){
    		$this->ajaxReturn(array('err'=>'noiid'));
    	}
    	else{
    		$items_user_mod = D('items_user');
    		
    		$item_user_id = $items_user_mod->where("iid='{$iid}' AND uid='{$_COOKIE['user']['id']}'")->getField('id');
			//此人已经分享过此商品了
		    if($item_user_id){
				$this->ajaxReturn(array('err'=>'yet_exist'));
			}
			//此人没有分享过这个商品
			//如果这个商品存在，则不弹窗		
			$item_key='miao_'.$iid;
    		$item_mod=$this->items_mod;
			$items_data = $item_mod->where("item_key='{$item_key}'")->find();
			//如果这个商品存在，则不弹窗分享，直接分享成功
			if($items_data){
				//判断商品的cid是否为空,如果为空，表示该商品没有被分享
				if($items_data['cid']==0){
					$this->ajaxReturn(array('err'=>'no_cid','item_id'=>$items_data['id'],'iid'=>$items_data['item_key']));
				}else{
					$item_user_data=array(
						'iid'=>substr($items_data['item_key'], 5),
						'uid'=>$_COOKIE['user']['id'],
						'item_id'=>$items_data['id'],
						'add_time'=>time()
						
					);
					$items_user_rel=$items_user_mod->add($item_user_data);
					if($items_user_rel){
						//赠送积分
			            $map['uid']=$_COOKIE['user']['id'];
			            M('userInfo')->where($map)->setInc("integral",$this->setting['share_goods_score']);
						$this->ajaxReturn(array('err'=>'share_yes'));
					}
				}

			}
    	}    
		$items_tags_mod = D('items_tags');
    	$miao_api = $this->miao_client();   //获取59秒api设置信息		
    	$data = $miao_api->ListItemsDetail($iid);
    	if(!is_array($data)){
			$this->ajaxReturn(array('err'=>'remote_not_exist'));
		}else{
			$data=$data['items']['item'];	
		}	
		if(!is_array($data)){
			$this->ajaxReturn(array('err'=>'remote_not_exist'));
		}		
		$data['price'] = $data['price'];
    	if (strpos($data['pic_url'], 'taobao') !== false){             
			$data['img'] =  $data['pic_url'].'_210x1000.jpg';
			$data['simg'] = $data['pic_url'].'_64x64.jpg';	
			//$data['bimg'] = $data['pic_url'].'_460x460.jpg';	
			$data['bimg'] = $data['pic_url'];	
        }else{        	
			$data['img'] = str_replace('.jpg', '_210x1000.jpg', $data['pic_url']);
			$data['simg'] = str_replace('.jpg', '_60x60.jpg', $data['pic_url']);
			//$data['bimg'] = str_replace('.jpg', '_460x460.jpg', $data['pic_url']);
			$data['bimg'] = $data['pic_url'];	
        }
		$data['url'] = $data['click_url'];
		$data['author']='miao';
		$data['item_key']='miao_'.$data['iid'];
		$tags = $items_tags_mod->get_tags_by_title($data['title']);		
		$data['tags'] = implode(' ', $tags);
		$this->ajaxReturn($data);
    } 
	private function uc_login_check(){
		if(is_null($this->uid)){
			if($this->isAjax()){
				$this->ajaxReturn("not_login");
			}
		}
	}
	public function jumpGetGoods(){	   
		$iid=isset($_REQUEST['iid'])?trim($_REQUEST['iid']):''; 		
		$item_mod=$this->items_mod;
		$item_rel=$item_mod->where("item_key='miao_{$iid}'")->find();
		if($item_rel){
			header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->site_root.'/index.php?a=index&m=item&id='.$item_rel['id']);
            //echo file_get_contents($this->site_root.'/index.php?a=index&m=item&id='.$item_rel['id']);
		}else{			
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
	    	$data = $miao_api->ListItemsDetail($iid); 	    
			$data=$data['items']['item'];				
			$addData=array();		
			if(is_array($data)){
				$addData['price'] = $data['price'];
				if (strpos($data['pic_url'], 'taobao') !== false){             
					$addData['img'] =  $data['pic_url'].'_210x1000.jpg';
					$addData['simg'] = $data['pic_url'].'_64x64.jpg';	
					//$addData['bimg'] = $data['pic_url'].'_460x460.jpg';	
					$addData['bimg'] = $data['pic_url'];	
		        }else{        	
					$addData['img'] = str_replace('.jpg', '_210x1000.jpg', $data['pic_url']);
					$addData['simg'] = str_replace('.jpg', '_60x60.jpg', $data['pic_url']);
					//$addData['bimg'] = str_replace('.jpg', '_460x460.jpg', $data['pic_url']);		
					$addData['bimg'] = $data['pic_url'];	
		        }
				$addData['url'] = $data['click_url'];				
				$addData['item_key']='miao_'.$data['iid'];
				//$data['tags']= $items_tags_mod->get_tags_by_title($data['title']);
				$addData['title']=$data['title'];	
				$addData['seller_name'] = $data['seller_name'];
				$addData['cash_back_rate'] = $data['cashback_scope'];	
				$item_id=$this->add($addData);
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.$this->site_root.'/index.php?a=index&m=item&id='.$item_id);
			
			}	
		}
	}
	private function add($data){			
			$items_site_mod = D('items_site');
			$items_tags_mod = D('items_tags');
			$items_tags_item_mod = D('items_tags_item');			
			$items_mod=$this->items_mod;
			$data['title']=ReplaceKeywords($data['title']);  //替换屏蔽的关键词				
			$data['add_time'] = time();
			
			$data['sid'] = $items_site_mod->where("alias='miao'")->getField('id');			
			$data['cid']=0;
			$new_item_id = $items_mod->add($data);				
			
			if ($new_item_id) {
				$tags = $items_tags_mod->get_tags_by_title($data['title']);			
				if ($tags) {					
					$tags_arr = array_unique($tags);					
					foreach ($tags_arr as $tag) {
						$isset_id = $items_tags_mod->where("name='" . $tag . "'")->getField('id');
						if ($isset_id) {
							$items_tags_mod->where('id=' . $isset_id)->setInc('item_nums');
							$items_tags_item_mod->add(array(
									'item_id' => $new_item_id,
									'tag_id' => $isset_id
							));
						} else {
							$tag_id = $items_tags_mod->add(array('name' => $tag));
							$items_tags_item_mod->add(array(
									'item_id' => $new_item_id,
									'tag_id' => $tag_id
							));
						}
					}
				}			
			}
			return $new_item_id;
			
	}
}