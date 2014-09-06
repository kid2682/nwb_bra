<?php
class itemAction extends baseAction {
	public function index() {
		$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error("404");
		$this->assign('items_id',$id);
		$items_mod = D('items');
		
		//增加浏览次数
		$items_mod->where("id='{$id}'")->setInc('browse_num',1); 		
		$items_cate_mod = D('items_cate');
        $items_like_list = M("like_list");
        //喜欢此宝贝的人
        if(!empty($id)){
            $like['items_id']=$id;
            $likelist = $items_like_list->where($like)->limit(10)->select();
            $this->assign("likelist",$likelist);
        }
		$item = $items_mod->field('id,uid,cid,sid,item_key,title,bimg,price,likes,comments,browse_num,add_time,cash_back_rate,seller_name')->where("id=".$id)->find();
		//获取此商品对应的商家logo
		$seller_list=D('seller_list');
		$seller_rel=$seller_list->field('sid,net_logo,name')->where("name='{$item['seller_name']}'")->find();
		//print_r($seller_rel);
		if($seller_rel){
			$this->assign('seller_logo',$seller_rel['net_logo']);
		}
		if($item['sid']==2){
			$iid=substr($item['item_key'], 5);//用于判断此商品是否存在，如果不存在执行下架操作 59秒的     
		}
		else{
			$iid=substr($item['item_key'], 7);//用于判断此商品是否存在，如果不存在执行下架操作        淘宝的
		}		
		$this->assign('iid',$iid);
		$this->assign('sid',$item['sid']);		
		$sids=$seller_rel['sid'];
		
		$item['items_cate'] = $items_mod->relationGet("items_cate");
		$item['items_site'] = $items_mod->relationGet("items_site");
		$item['items_tags'] = $items_mod->relationGet("items_tags");	
        
		$tag_str = '';
		foreach ($item['items_tags'] as $tag) {
			$tag_str .= $tag['name'] . ' ';
		}        
		$this->seo['seo_title'] = !empty($item['seo_title']) ? $item['seo_title'] : $item['title'];
		$this->seo['seo_title'] = $this->seo['seo_title'] . ' - ' . $this->setting['site_name'];
		$this->seo['seo_keys'] = !empty($item['seo_keys']) ? $item['seo_keys'] : $tag_str;
		$item['seo_desc'] && $this->seo['seo_desc'] = $item['seo_desc'];

		//比如这个商品时女装下面的，则获取父类是女装的商品分类
		$siblings_cate_group = $items_cate_mod->field('id,name,item_nums')->where("pid=" . $item['items_cate']['pid'] . " AND is_hots=1")->limit('0,4')->order("status DESC,ordid DESC")->select();
        foreach ($siblings_cate_group as $key => $val) {
			$siblings_cate_group[$key]['items'] = $this->get_group_items($val['id']);
		}
        
		//获取对应api商家下面的相关数据
		$items_list = $items_mod->field('id,title,sid,img,price,likes,comments,seller_name,cash_back_rate')->relation('items_site')->where('cid=' . $item['cid'])->limit('0,20')->select(); //同类商品		
		foreach ($items_list as $key=>$val){			
			//获取最新的三条评论
			$items_list[$key]['three_comments']=$this->user_comments_mod->where('pid='.$val['id'].' and status=1')->order("add_time DESC")->limit("0,3")->relation(true)->select();	
		    //获取三条喜欢此宝贝的人
            $like['items_id']=$val['id'];
            $items_list[$key]['likelist'] = $this->like_list_mod->where($like)->order('id desc')->limit(3)->select();
            $items_list[$key]['count'] = $this->like_list_mod->where($like)->count();
        }
		
        $this_cate_group = $this->get_group_items($item['cid']); //所在分类展示
        
		$source_group = $this->get_group_items_bysource($item['sid']); //相同来源展示
		$items_mod->where('id=' . $id)->setInc('hits'); //浏览次
		
		//判断来源，执行不同的跳转
		if($item['sid']==1){
			$this->assign('site','tao');
		}
		else if($item['sid']==2){
			$this->assign('site','b2c');
		}
        import("ORG.Util.Page");
        $user_comments_mod=D('user_comments');
		$act=$_REQUEST['act'];
		$type=MODULE_NAME.','.ACTION_NAME;
		$pid=empty($_REQUEST['id'])?0:intval($_REQUEST['id']);
		$where="type='".$type."' and pid=$pid and status=1";

		$count = $user_comments_mod->where($where)->count();
		$p = new Page($count,8);
		$list=$user_comments_mod->where($where)->relation('user')->where($where)
		->order("id desc")->limit($p->firstRow.','.$p->listRows)
		->select();
        
		$this->assign('comments',array('list'=>$list,'page'=>$p->show_1(),'count'=>$count));
        if($this->isAjax()){
			$this->ajaxReturn(array('list'=>$this->fetch('uc:comments_list'),
					'count'=>$count));
		}	
		//文章动态	
		if(S('item_article'.$id)){
            $art_list = S('item_article'.$id);
        }else{
            $article_mod = D('article');
			$count=$article_mod->count();			 
			$rand=rand(0,($count-10));
			$art_list = $article_mod->field('id,title')->limit("$rand,8")->select();
			S('item_article'.$id,$art_list,'3600');  //缓存小时
		}	
		$this->assign('article_list',$art_list);
		
		//$this->assign('seo', $this->seo);
        $this->nav_seo('item','items',$_GET['id'],$item['items_tags']);
		$this->assign('item', $item); 
		$this->assign('items_list', $items_list);
		$this->assign('siblings_cate_group', $siblings_cate_group);
		$this->assign('this_cate_group', $this_cate_group);
		$this->assign('source_group', $source_group);
		$this->display();
        //保存商品图片
        //此方法有问题
//        if($this->setting['goods_save_images'] &&$item['sid']==2&& !isImages($item['id'])){
//            calculation($item["bimg"],ROOT_PATH."/data/items/{$item['id']}/",array('64','210','450'));
//        }
	}
	//用于商品跳转到b2c
	function b2c(){
	   $id=intval($_REQUEST['id']);
	   $uid=$_REQUEST['uid']?intval($_REQUEST['uid']):'';
	   $res=$this->items_mod->where('id='.$id)->find();
	   $url=$res['url'];
	   //如果开启返现 1开启
	   if($this->setting['is_cashback']==1){
	   	   //如果用户登录则可以获取全部返现，如果用户没有登录，则把返现返给发布者
		   if(isset($_COOKIE['user']['id'])){
		   		$user_count=$this->user_mod->where("id = {$_COOKIE['user']['id']}")->count();
		   		if($user_count>0){
		   			$url=$url.$_COOKIE['user']['id'];          //购买者
		   		}		   	
		   }		  
	   }	   
	   if($res){
	       redirect($url);
	   }
	   exit;
	}
	//用于商家大全，和促销活动url跳转	
	function url(){
		$id=(intval($_GET['url']));		
		$seller_list_mod = D('seller_list');
		$rel=$seller_list_mod->where("id={$id}")->find();
		$url=$rel['click_url'];
		if(strpos($url, 'r.59miao.com') !== false){
			if($this->setting['is_cashback']==1){   //如果开启返现
				if(isset($_COOKIE['user']['id'])){  //如果用户登录
					$url=$url.$_COOKIE['user']['id'];
				}
			}
		}
		redirect($url);	
		exit;
				
	}
	function urlpromo(){
		$url=urldecode(base64_decode($_GET['url']));
		if($this->setting['is_cashback']==1){   //如果开启返现
			if(isset($_COOKIE['user']['id'])){  //如果用户登录
				$url=$url.$_COOKIE['user']['id'];
			}
		}
		redirect($url);	
		exit;
				
	}	
	//fanli返利商品跳转
	function fanli(){
	   $iid=trim($_GET['iid']);	   	   
	   //跳转的时候检测用户，执行返利	   
	   //如果开启返现 1开启	  
	   	   //如果用户登录则可以获取全部返现，如果用户没有登录，则把返现返给发布者
   	   $tb_top = $this->taobao_client();
	   $req = $tb_top->load_api('TaobaokeItemsDetailGetRequest');				
	   $req->setFields("num_iid,detail_url,click_url");
	   $req->setPid($this->setting['taobao_pid']);
	   $req->setNick($this->setting['taobao_usernick']);
	   if(!empty($_COOKIE['cooperate_id'])){
		 $req->setOuterCode($_COOKIE['cooperate_id']);
	   }
	   $req->setNumIids($iid);
	   $resp = $tb_top->execute($req);	
	   $item_rel=get_object_vars_final($resp);	  
	   $url=$item_rel['taobaoke_item_details']['taobaoke_item_detail']['click_url'];
	   redirect($url);
	   exit;
	   
	}
	
	function img(){
	   $id=intval($_REQUEST['id']);
	   $type=$_REQUEST['type'];
	   $res=$this->items_mod->where('id='.$id)->find();
       if($res){
           header("content-type:image/jpg");
           print_r(file_get_contents($res[$type]));
       }
	}
	function checkItem(){		
		$iid=trim($_REQUEST['iid']);	
		$sid=trim($_REQUEST['sid']);
		if($sid==1){//检测淘宝数据是否存在			
			$tb_top = $this->taobao_client();
			$req = $tb_top->load_api('TaobaokeItemsDetailGetRequest');
			
			$req->setFields("num_iid,detail_url");
			$req->setPid($this->setting['taobao_pid']);
			$req->setNick($this->setting['taobao_usernick']);
			$req->setNumIids($iid);
			
			$resp = $tb_top->execute($req);			
			if($resp->total_results==0){
				$item_mod=$this->items_mod;
				$item_key='taobao_'.$iid;
				$data=array('status'=>0);
				$item_mod->where("item_key='{$item_key}'")->save($data);
				exit(' ');			
			}			
			
		}else if($sid==2){  //检测59秒数据是否存
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$data = $miao_api->ListItemsDetail($iid);
			//下架商品		
			if(count($data['items']['item'])==0){
				$item_mod=$this->items_mod;
				$item_key='miao_'.$iid;
				$data=array('status'=>0);
				$item_mod->where("item_key='{$item_key}'")->save($data);
				exit(' ');		
			}
		}
		exit(' ');
		
	}
	//跳转到淘宝
	function tao(){
	   $id=intval($_REQUEST['id']);
	   $res=$this->items_mod->where('id='.$id)->find();
	   //跳转的时候检测用户，执行返利	   
	   //如果开启返现 1开启
	   if($this->setting['is_cashback']==1){
	   	   //如果用户登录则可以获取全部返现，如果用户没有登录，则把返现返给发布者
		   if(isset($_COOKIE['user']['id'])){
		   		$iid=substr($res['item_key'], 7);
		 	  	$tb_top = $this->taobao_client();
				$req = $tb_top->load_api('TaobaokeItemsDetailGetRequest');				
				$req->setFields("num_iid,detail_url,click_url");
				$req->setPid($this->setting['taobao_pid']);
				$req->setNick($this->setting['taobao_usernick']);
				$req->setOuterCode($_COOKIE['user']['id']);
				$req->setNumIids($iid);				
				$resp = $tb_top->execute($req);	
				$item_rel=get_object_vars_final($resp);				
				$url=$item_rel['taobaoke_item_details']['taobaoke_item_detail']['click_url'];	
				if(!empty($url)){
					redirect($url);
					exit;
				}
		   }
	   }	   
	   if($res){
	       redirect($res['url']);
	   }
	}
	//获取用户的uid
	function getUserId(){
		$name=trim($_REQUEST['name']);
		$user_mod=$this->user_mod;
		$user_rel=$user_mod->where("name='{$name}'")->find();
		if($user_rel){
			$this->ajaxReturn($user_rel['id']);
		}
		else{
			$this->ajaxReturn('no_user');
		}			
	}
	
}