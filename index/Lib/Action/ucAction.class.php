<?php
class ucAction extends baseAction{
	private $_action = array('account_basic','account_sns',
			'account_pwd','account_invitation','album_info');
	public function _initialize() {		
		parent::_initialize();		
		if(($_REQUEST['act']=='del'||$_REQUEST['act']=='add'||$_REQUEST['act']=='edit'||
				in_array(ACTION_NAME, $this->_action))
				&&!$this->check_login()
		){
			if($this->isAjax()){
				$this->ajaxReturn("not_login");   //ajax 返回没有登录信息
			}else{
				header('Location:'.U('uc/login'));
			}
		}
	}
	function uc_login_check(){
		if(!$this->check_login()){          //检测是否登录的类文件
			if($this->isAjax()){
				$this->ajaxReturn("not_login");
			}else{
				header('Location:'.U('uc/login'));
			}
		}
	}
	function index(){
		$this->uc_login_check();
		$album_mod=D('album');
		$album_items_mod=D('album_items');
		
		$items_comments_mod=D("items_comments");
		$like_list_mod=D('LikeListView');
        $items_mod=D("items");

		import("ORG.Util.Page");
		$where="uid=".$this->uid;
		$this->assign('album_num',$album_mod->where($where)->count());		
	        $res=$album_mod->where($where)->limit("0,50")->order("id desc")->select();	        
			foreach($res as $key=>$val){
				$res2=$album_items_mod->where("pid=".$val['id'])->order("id desc")
				->limit("0,6")->select();
				$items=array();
				$like_num=0;
				$comment_num=0;
				foreach($res2 as $key2=>$val2){
					$img=$items_mod->field("id,title,likes,img,simg,bimg")->where("id=".$val2['items_id'])
					->find();
					if($key2==0){
					   	
                        if (strpos($img['bimg'], 'taobao') !== false){ 
							$img['simg'] = $img['bimg'].'_160x160.jpg';
				        }else{
							$img['simg'] = str_replace('.jpg', '_160x160.jpg', $img['bimg']);
				        }
					}
                    
					$items[]=array('img'=>$img['simg'],'id'=>$img['id'],'title'=>$img['title']);
					$like_num=$like_num+$img['likes'];
	
					$comment_num=$comment_num+$this->user_comments_mod
					->where('pid='.$val2['items_id'].' and type="item,index"')
					->count();
				}
				$total_num=intval(6-count($items));
				for($num=0;$num<$total_num;$num++){
				    $items[]=array('img'=>$this->site_root."data/none_pic_v3.png");
				}
	            
				$res[$key]['items']=$items;
				$res[$key]['like_num']=$like_num;
				$res[$key]['comment_num']=$comment_num;
				$res[$key]['number'] = $album_items_mod->where("pid=".$val['id'])->count();
			}
		$this->assign('album_list',$res);
        //用户喜欢->union('SELECT id FROM pp_items limit 5')
        $map['uid'] = $this->uid;
		$likelist=$like_list_mod->where($map)->order('LikeList.id desc')->limit(8)->select();
        $this->assign('likelist', $likelist);
        //用户分享
        $itemslist = $items_mod->field('id,img,url,add_time')->where($map)->order('id desc')->limit(3)->select();
        $this->assign('itemslist', $itemslist);   
        $this->assign('seo',$this->ucNavSeo('index',$_GET['uid']));
		$this->display();
	}	
	public function home(){		
		$this->display();
	}
	function me(){				
		$uid=isset($_GET['uid'])&&is_numeric($_GET['uid'])?$_GET['uid']:'';
		if(empty($uid)){
			header('location:'.u('index/index'));
		}
		if($uid==$_COOKIE['user']['id']){  //自己显示所有好友信息
			$user_follow_mod=D('user_follow');
			$res=$user_follow_mod->where("uid=".$uid)->select();
			foreach($res as $key=>$val){
				$ids[]=$val['fans_id'];
			}
			$where="uid in(".implode(',', $ids).")";
		}
		else{ //显示他自己的分享			
			$where="uid ='{$uid}'";	
		}
		$user_history_mod=D('user_history');		
		$count=$user_history_mod->where($where)->order('add_time desc')->count();		
		$pager=$this->pager($count);
		$res=$user_history_mod->where($where)->order('add_time desc')
		->limit($pager->firstRow.",".$pager->listRows)
		->order("id desc")
		->select();  		
		$this->assign('history_list',$res);
        $this->assign('seo',$this->ucNavSeo('me',$_GET['uid']));
		$this->display();
	}
	function album(){
		$this->uc_login_check();
		$album_mod=D('album');
		$album_items_mod=D('album_items');
		$items_mod=D("items");
		$items_comments_mod=D("items_comments");
		$user_follow_mod=D('user_follow');
		$user_mod=D('user');

		$type=empty($_REQUEST['type'])?'index':$_REQUEST['type'];

		$this->assign('type',$type);
		import("ORG.Util.Page");

		if($type=='follow'){
			$res=$user_follow_mod->where("uid=".$this->uid)->select();
			foreach($res as $key=>$val){
				$ids[]=$val['fans_id'];
			}
			$where="uid in(".implode(',', $ids).")";
		}else{
			$where="uid=".$this->uid;
		}

		$count=$album_mod->where($where)->count();
		$p= new Page($count,10);
		$res=$album_mod->where($where)->limit($p->firstRow.','.$p->listRows)
		->order("id desc")->select();
        
		foreach($res as $key=>$val){
			$res2=$album_items_mod->where("pid=".$val['id'])->order("id desc")
			->limit("8")->select();
			$items=array();
			$like_num=0;
			$comment_num=0;
			foreach($res2 as $key2=>$val2){
				$img=$items_mod->field("likes,img")->where("id=".$val2['items_id'])
				->find();
				$items[]=$img['img'];
				$like_num=$like_num+$img['likes'];

				$comment_num=$comment_num+$this->user_comments_mod
				->where('pid='.$val2['items_id'].' and type="item,index"')
				->count();
			}
			$total_num=intval(8-count($items));
			for($num=0;$num<$total_num;$num++){
				$items[]=$this->site_root."data/none_pic_v3.png";
			}
			$res[$key]['items']=$items;
			$res[$key]['like_num']=$like_num;
			$res[$key]['comment_num']=$comment_num;
			$res[$key]['user']=$user_mod->where('id='.$val['uid'])->find();
            $res[$key]['number'] = $album_items_mod->where("pid=".$val['id'])->count();
		}
        $this->assign('seo',$this->ucNavSeo('album',$_GET['uid']));
		$this->assign('album_list',$res);
		$this->assign('page',$p->show_1());
		$this->display();
	}
	function album_info(){
		$this->uc_login_check();
		$act=empty($_REQUEST['act'])?'add':$_REQUEST['act'];
		$id=setFormString($_REQUEST['id']);

		$album_mod=D('album');
		$album_items_mod=D('album_items');

		if($act=='del'){
			$album_items_mod->where('pid='.$id)->delete();
			$album_mod->where('id='.$id)->delete();

			$this->update_user_assoc_num('album');
			header("location:".u('uc/index'));
		}else if($act=='edit'){
			$res=$album_mod->where('id='.$id.' and uid='.$_COOKIE['user']['id'])
			->find();
			$this->assign('album',$res);
		}
		if(!empty($_POST['dosubmit'])){
			$data=$album_mod->create();
            $map['uid']=$_COOKIE['user']['id'];
            $album_count = $album_mod->where($map)->count();
            if($album_count > 32){
                $this->ajaxReturn('error');
                die();
            }
			if($act=='add'){
				$data['add_time']=time();
				$data['uid']=$_COOKIE['user']['id'];
				$data['id']=$album_mod->add($data);
			}else if($act=='edit'){
				$album_mod->save($data);
			}
			$this->update_user_assoc_num('album');  //更新用户表中的专辑
			$this->ajaxReturn('success',$data,1);
		}
		$album_cate_mod=D('album_cate');
		$this->assign('cate',$album_cate_mod->where("status=1")->order("sort_order ASC")->select());
		$this->assign('act',$act);
		$this->display();
	}
	function album_items(){
		$this->uc_login_check();
		$album_items_mod=D('album_items');
		$album_mod=D('album');
		$user_mod=D('user');
		if(empty($_REQUEST['id']))header('location:'.$this->site_root);

		$id= intval($_REQUEST['id']);
		$count = $album_items_mod->where('pid='.$id)->count();
		$res=$album_items_mod->where('pid='.$id)->select();
		$ids=array();
		foreach($res as $val){
			$ids[]=$val['items_id'];
		}
		$where='id in('.implode(",",$ids).')';

		$res=$user_mod->where('id='.$this->uid)->find();
		$info['album_who']=$res["name"]."的专辑";
		$res=$album_mod->where('id='.$id)->find();
		$info['album_title']=$res["title"];

		$this->assign('info',$info);
		$this->waterfall($count,$where);
	}
	function like(){
		//$this->uc_login_check();		
		$user_mod=$this->user_info;
		$like_list_mod=$this->like_list_mod;
		$act=$_REQUEST['act'];
		$id=intval($_REQUEST['id']);
		if($act=='del'){				
			$res=$like_list_mod->del(intval($_REQUEST['id']));
			if(intval($res)>0){
				//更新用户表的喜欢数量
				$count=$like_list_mod->where('uid='.$_COOKIE['user']['id'])->count();
				$data=array('like_num'=>$count);
				$user_mod->where('uid='.$_COOKIE['user']['id'])->save($data);
				
				//更新item表的喜欢数量
				//$this->items_mod
				$this->items_mod->where("id='{$_REQUEST['id']}'")->setDec('likes');
			}
			$this->ajaxReturn($res);
		}else if($act=='add'){
			$items_cate_mod=D('items_cate');
			$user_history_mod=D('user_history');

			$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : '';

			if(check_favorite('like_list', $id)){
				$this->ajaxReturn('yet_exist');
			}

			$like_num= 0;
			if ($id) {
				$items_mod = D('items');
				$like_num = $items_mod->where('id=' . $id)->setInc('likes');
				$items_mod->where('id=' . $id)->setField('status',1);
					
				$items=$items_mod->where('id=' . $id)->find();
				$items_cate_mod->where('id='.$items['cid'])->setInc('item_likes');
					
				$data=array();
				$data['uid']=$_COOKIE['user']['id'];
				$data['uname']=$_COOKIE['user']['name'];
				$data['add_time']=time();
				$data['info']="喜欢了一个宝贝~<br/>"
				."<a href='".u("item/index",array('id'=>$id))."' target='_blank'>"
				."<img src='".$items['img']."'/></a>";
					
				$user_history_mod->add($data);

				$data=array();
				$count=$like_list_mod->where('uid='.$_COOKIE['user']['id'])->count();
				$data=array('like_num'=>$count);

				$user_mod->where('uid='.$_COOKIE['user']['id'])->save($data);

			}
			$this->ajaxReturn($like_num);
		}
		$where='uid='.$this->uid;
		$count = $like_list_mod->where($where)->count();
		$res=$like_list_mod->where($where)->select();
		$ids=array();
		foreach($res as $val){
			$ids[]=$val['items_id'];
		}
        $like_list_mod=D('LikeListView');
        $items_mod=D("items");
        //最近喜欢、分享了……
        //用户喜欢->union('SELECT id FROM pp_items limit 5')
        $map['uid'] = $this->uid;
		$likelist=$like_list_mod->where($map)->order('LikeList.id desc')->limit(8)->select();
        $this->assign('likelist', $likelist);
        //用户分享
        $itemslist = $items_mod->field('id,img,url,add_time')->where($map)->order('id desc')->limit(3)->select();
        $this->assign('itemslist', $itemslist);
        
        $this->assign('seo',$this->ucNavSeo('like',$_GET['uid']));
		$this->waterfall($count,'id in('.implode(",",$ids).')','add_time desc');
	}
	function share(){
		$this->uc_login_check();
		$items_mod=D('items');
		$user_mod=$this->user_info;
        $like_list_mod=D('LikeListView');      
        
		$act=$_REQUEST['act'];
		if($act=='del'){
			$res=$this->items_mod->del(intval($_REQUEST['id']));
			if(intval($res)>0){
				$count=$items_mod->where('uid='.$_COOKIE['user']['id'])->count();
				$data=array('share_num'=>$count);
				$user_mod->where('uid='.$_COOKIE['user']['id'])->save($data);
			}
			$this->ajaxReturn($res);
		}else if($act=='add'){
			$items_cate_mod = D('items_cate');
			$items_site_mod = D('items_site');
			$items_tags_mod = D('items_tags');
			$items_tags_item_mod = D('items_tags_item');
			$items_user_mod = D('items_user');
			$user_history_mod=D('user_history');

			if (false === $data = $items_mod->create()) {
				$this->ajaxReturn('data_create_error');
			}		
			$data['title']=ReplaceKeywords(strip_tags($data['title']));  //替换屏蔽的关键词
			if(empty($data['remark'])){
				$data['remark']=ReplaceKeywords(strip_tags($data['title']));
			}
			else{
				$data['remark']=ReplaceKeywords(strip_tags($data['remark']));
			}
			$data['add_time'] = time();
			$author = isset($_POST['author']) ? $_POST['author'] : '';

			$data['sid'] = $items_site_mod->where("alias='" . $author. "'")->getField('id');
			$data['uid']=$_COOKIE['user']['id'];

			$new_item_id = $items_mod->add($data);
			$items_cate_mod->where('id='.$data['cid'])->setInc('item_nums');
			//item_user 记录用户分享的商品,第一次分享，执行这里的操作
			$item_user_data=array(
				'iid'=>substr($data['item_key'], 5),
				'uid'=>$data['uid'],
				'item_id'=>$new_item_id,
				'add_time'=>time()
			);
			$items_user_mod->add($item_user_data);

			$count=$items_mod->where('uid='.$_COOKIE['user']['id'])->count();
			$data=array('share_num'=>$count);
			$user_mod->where('uid='.$_COOKIE['user']['id'])->save($data);
            
			//动态
			$res = $items_mod->where('id=' . $new_item_id)->find();
			$data=array();
			$data['uid']=$_COOKIE['user']['id'];
			$data['uname']=$_COOKIE['user']['name'];
			$data['add_time']=time();
			$data['info']="分享了了一个宝贝~<br/>"
			."<a href='".u("item/index",array('id'=>$new_item_id))."' target='_blank'>"
			."<img src='".$res['img']."'/></a>";

			$user_history_mod->add($data);

			if ($new_item_id) {
				$tags =$_POST['tags'];
				if ($tags) {
					$tags_arr = explode(' ', $tags);
					$tags_arr = array_unique($tags_arr);
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
				$items_cate_mod->setInc('item_nums', 1);
			}
            
            //赠送积分
            $map['uid']=$_COOKIE['user']['id'];
            M('userInfo')->where($map)->setInc("integral",$this->setting['share_goods_score']);
        
			$this->ajaxReturn('success');
		}
		$where='uid='.$this->uid;
		$count = $items_mod->where($where)->order('add_time desc')->count();		
        $this->assign('seo',$this->ucNavSeo('share',$_GET['uid']));
        
        //最近喜欢、分享了……
        //用户喜欢->union('SELECT id FROM pp_items limit 5')
        $map['uid'] = $this->uid;
		$likelist=$like_list_mod->where($map)->order('LikeList.id desc')->limit(8)->select();		
        $this->assign('likelist', $likelist);
        //用户分享
        $itemslist = $items_mod->field('id,img,url,add_time')->where($map)->order('id desc')->limit(3)->select();
        $this->assign('itemslist', $itemslist);
        $item_user_mod=D('items_user');
        $item_ids=$item_user_mod->field('item_id')->where($where)->select();
        $inid='';
        foreach ($item_ids as $key => $value) {
        	$inid.=$value['item_id'].',';
        }
        $inid=substr($inid, 0,-1);       
        $where="id in ({$inid})";
		$this->waterfall($count, $where,'add_time desc');
	}
	function nocid_share(){
		$items_mod=$this->items_mod;
		$items_user_mod = D('items_user');
		if (false === $data = $items_mod->create()) {
			$this->ajaxReturn('data_create_error');
		}			
		$data['add_time']=time();
		$data['uid']=$_COOKIE['user']['id'];
		$affect_row=$items_mod->where("id='{$data['id']}'")->save($data);
		if($affect_row){			
			$item_user_data=array(
				'iid'=>substr($data['item_key'], 5),
				'uid'=>$_COOKIE['user']['id'],
				'item_id'=>$data['id'],
				'add_time'=>time()
			);
			$items_user_affect_row=$items_user_mod->add($item_user_data);
			if($items_user_affect_row){
				//赠送积分
	            $map['uid']=$_COOKIE['user']['id'];
	            M('userInfo')->where($map)->setInc("integral",$this->setting['share_goods_score']);
				$this->ajaxReturn('success');
			}
		}
		$this->ajaxReturn('error');

		
	}
	function account_basic(){
	    $this->uc_login_check();
	    $map['id'] = $_COOKIE['user']['id'];	
        $user =$this->user_mod->where($map)->relation('user_info')->find();    
        $this->assign("user",$user);
        $this->assign('seo',$this->ucNavSeo('account_basic',$_GET['uid']));
		$this->display();
	}
	//修改基本信息
    function doBasic(){
    	//引入ucenter相关文件
    	$this->require_uc();
        $this->uc_login_check();
        $user_mod=$this->user_mod;
        $user_info_mod=$this->user_info;
        $datauser['id'] = $_POST['id'];
            $datauser['name'] = $_POST['name'];
            $datauser['email'] = $_POST['email'];
            if($this->setting['ucenterlogin']){
                $ucresult = uc_user_edit($_POST['name'],'','',$_POST['email'],1);
            }
            $data['id'] = $_POST['id'];
            $data['realname'] = $_POST['realname'];
            $data['alipay'] = $_POST['alipay'];
            $data['uid'] = $_POST['id'];
            $data['sex'] = $_POST['sex'];
            $data['qq'] = $_POST['qq'];
            $data['address']=$_POST['province'].'|'.$_POST['city'];
            $data['brithday']=$_POST['year'].'|'.$_POST['month'].'|'.$_POST['day'];
            $data['constellation'] = $_POST['constellation'];
            $data['job'] = $_POST['job'];
            
            $user_rel=$user_mod->where("id='{$data['id']}'")->save($datauser);
            $user_info_rel=$user_info_mod->where("uid='{$data['uid']}'")->save($data);            
			if($user_rel || $user_info_rel){
				echo 1;
			}else{
				echo 0;
			}
			   
			exit;
    }
	function account_sns(){
		$res=$this->user_openid_mod->where('uid='.$_COOKIE['user']['id'])->select();
		foreach ($res as $key=>$val){
			$this->assign('bind_'.$val['type'],true);
		}
        $this->assign('seo',$this->ucNavSeo('account_sns',$_GET['uid']));
		$this->display();
	}
	function account_pwd(){
	    //引入配置文件、类库
        $this->require_uc();
		if (isset($_POST['dosubmit'])) {
			$passwd=trim($this->user_mod->where('id='.$_COOKIE['user']['id'])->getField('passwd'));
            
			if(trim($passwd)!=md5(trim($_POST['passwd']))){
				$this->assign('err',array('err'=>0,'msg'=>'当前密码错误!'));
			}else{
			 if($this->setting['ucenterlogin']){
			    $ucresult = uc_user_edit($_COOKIE['user']['name'],$_POST['passwd'],$_POST['new_pwd'],'');
                if($ucresult == -1) {
                	$this->assign('err',array('err'=>1,'msg'=>'旧密码不正确!'));
                } elseif($ucresult == -4) {
                	$this->assign('err',array('err'=>1,'msg'=>'Email 格式有误!'));
                } elseif($ucresult == -5) {
                    $this->assign('err',array('err'=>1,'msg'=>'Email 不允许注册!'));
                } elseif($ucresult == -6) {
                    $this->assign('err',array('err'=>1,'msg'=>'该 Email 已经被注册!'));
                }
              }  
                
				$data=array('passwd'=>md5(trim($_POST['new_pwd'])));
				$this->user_mod->where('id='.$_COOKIE['user']['id'])->save($data);
				$this->assign('err',array('err'=>1,'msg'=>'修改成功!'));
			     
            }
		}
        $this->assign('seo',$this->ucNavSeo('account_pwd',$_GET['uid']));
		$this->display();
	}
	//商品兑换列表
	function account_exchange(){
		$this->uc_login_check();		
		$status=isset($_GET['status'])&&!empty($_GET['status'])?$_GET['status']:0;
		$where='1=1';
		if($status!=2){
			$where.= " AND goods_status='{$status}'";
			$this->assign('status',$status);	
		}elseif($status==2){
			$this->assign('status',2);
		}else{
			$this->assign('status',0);
		}		
		$ex_order_mod = D('exchange_order');		
		$where.= " AND uid='{$_COOKIE['user']['id']}'";	
		import("ORG.Util.Page");
		$count = $ex_order_mod->where($where)->count();
		$p = new Page($count,5);
		$ex_order_list = $ex_order_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();				
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('ex_order_list',$ex_order_list);
        $this->assign('seo',$this->ucNavSeo('account_exchange',$_GET['uid']));
		$this->display();
	}
	//佣金列表
	//commission
	
	function account_commission(){
		$this->uc_login_check();
		$order_mod = D('miao_order');
		//搜索
		$where = '1=1';
		if(isset($_GET['status'])&&trim($_GET['status'])=='no'){
			$where.= " AND status='未确认'";	
		}
		$where.= " AND uid='{$_COOKIE['user']['id']}'";		
		import("ORG.Util.Page");
		$count = $order_mod->where($where)->count();
		$p = new Page($count,10);
		$order_list = $order_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('order_time desc')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('order_list',$order_list);
		$this->assign('seo',$this->ucNavSeo('account_commission',$_GET['uid']));
		$this->display();		
	}
    
	//提现
	function account_get_cash(){	
       	$this->uc_login_check();
		if(isset($_GET['type'])){
			$type=intval($_GET['type']);
		}else{
			$type=1;//1表示提现，2表示提集分宝	
		}
		if($type==1){
			//提现
			$this->assign('type',1);
			$this->tixian();
		}else{
			$this->tijifenbao();
		}	
		
	} 
	private function tixian(){		
        $uid=$_COOKIE['user']['id'];
		$cash_log=D('cash_back_log');  
		if(isset($_POST['dosubmit'])){
			$user_rel=$this->user_mod->where("id='{$uid}'")->relation('user_info')->find();	
			$log_rel=$cash_log->where("uid='{$uid}' AND after_money!=''")->order("id desc")->find();          //获取用户收支记录
			
			$user_tixian=D('user_tixian');
			$data=$user_tixian->create();
            if(!is_numeric($data['money']) || $data['money'] < $this->setting["lowest_get_cash"]){
				$this->assign('err',array('err'=>0,'msg'=>"输入金钱有误,最低提现金额不得小于{$this->setting['lowest_get_cash']}！"));	
			}elseif($data['money']>$user_rel['money']){
				$this->assign('err',array('err'=>0,'msg'=>'提现金额超出现有资金'));			
			}elseif((isset($log_rel['after_money'])&&($user_rel['money']!=$log_rel['after_money'])) || (!isset($log_rel['after_money'])&&$user_rel['money']!=0)){
				$this->assign('err',array('err'=>0,'msg'=>'您的资金异常,请与管理员联系'));
			}else{
				$data['addtime']=time();
				$data['ip']=$_SERVER['REMOTE_ADDR'];
				$data['uid']=$uid;
				$data['uname']=$user_rel['name'];
				$data['is_money']='1';
				if($user_tixian->add($data)){
					//更新用户资金表
						$this->user_info->where("uid=$uid")->setDec('money',$data['money']); // 减少用户金钱							
						//更新返现记录表
						$cash_back_log=$cash_log;						
						$last_info=$cash_back_log->where("uid='{$uid}'")->order('id desc')->limit(1)->find();
						$log_data=array(
							'uid'=>$uid,
							'uname'=>$user_rel['name'],
							'before_money'=>$last_info['after_money'],
							'after_money'=>$last_info['after_money']-$data['money'],
							'in_money'=>0,
							'out_money'=>$data['money'],
							'after_jifenbao'=>$last_info['after_jifenbao'],
							'type'=>2,   //1表示收入，2表示支出
							'time'=>time(),
							'info'=>'提现支出',
							'sign'=>md5($uid.$user_rel['name'].time()),
						);
						$cash_back_log->add($log_data);				
					$this->assign('err',array('err'=>1,'msg'=>'恭喜您提现成功，等待管理员审核'));	
				}
			}			
		}
		//查询现有金额
		$user_info=$this->user_info->where("uid='{$uid}'")->find();		
		$this->assign('money',$user_info['money']);
		$this->assign('realname',$user_info['realname']);	
		$this->assign('alipay',$user_info['alipay']);		
        $this->assign('seo',$this->ucNavSeo('account_get_cash',$_GET['uid']));
		$this->display();
	}
	private function tijifenbao(){
        $uid=$_COOKIE['user']['id'];
		$cash_log=D('cash_back_log');  
		if(isset($_POST['dosubmit'])){
			$user_rel=$this->user_mod->where("id='{$uid}'")->relation('user_info')->find();	
			$log_rel=$cash_log->where("uid='{$uid}' AND after_jifenbao!=''")->order("id desc")->find();          //获取用户收支记录			
			$user_tixian=D('user_tixian');
			$data=$user_tixian->create();
            if(!is_numeric($data['jifenbao']) || $data['jifenbao'] < $this->setting["lowest_get_jifen_cash"]){
				$this->assign('err',array('err'=>0,'msg'=>"输入金钱有误,最低提现金额不得小于{$this->setting['lowest_get_jifen_cash']}！"));	
			}elseif($data['jifenbao']>$user_rel['jifenbao']){
				$this->assign('err',array('err'=>0,'msg'=>'提现金额超出现有金额'));			
			}elseif((isset($log_rel['after_jifenbao'])&&($user_rel['jifenbao']!=$log_rel['after_jifenbao'])) || (!isset($log_rel['after_jifenbao'])&&$user_rel['jifenbao']!=0)){
				$this->assign('err',array('err'=>0,'msg'=>'您的资金异常,请与管理员联系'));
			}else{
				$data['addtime']=time();
				$data['ip']=$_SERVER['REMOTE_ADDR'];
				$data['uid']=$uid;
				$data['uname']=$user_rel['name'];
				$data['is_money']='2';     //2表示集分宝
				if($user_tixian->add($data)){
						//更新用户资金表
						$this->user_info->where("uid=$uid")->setDec('jifenbao',$data['jifenbao']); // 减少用户集分宝 						
						//更新返现记录表
						$cash_back_log=$cash_log;						
						$last_info=$cash_back_log->where("uid='{$uid}'")->order('id desc')->limit(1)->find();
						$log_data=array(
							'uid'=>$uid,
							'uname'=>$user_rel['name'],
							'before_jifenbao'=>$last_info['after_jifenbao'],
							'after_jifenbao'=>$last_info['after_jifenbao']-$data['jifenbao'],
							'after_money'=>$last_info['after_money'],
							'in_jifenbao'=>0,
							'out_jifenbao'=>$data['jifenbao'],
							'type'=>2,   //1表示收入，2表示支出
							'time'=>time(),
							'info'=>'提现支出',
							'sign'=>md5($uid.$user_rel['name'].time()),
						);
						$cash_back_log->add($log_data);				
					$this->assign('err',array('err'=>1,'msg'=>'恭喜您提现成功，等待管理员审核'));	
				}
			}			
		}
		//查询现有金额
		$user_info=$this->user_info->where("uid='{$uid}'")->find();		
		$this->assign('jifenbao',$user_info['jifenbao']);
		$this->assign('realname',$user_info['realname']);	
		$this->assign('alipay',$user_info['alipay']);		
        $this->assign('seo',$this->ucNavSeo('account_get_cash',$_GET['uid']));
		$this->display();
	}      
	//提现记录
	function account_user_tixian(){
		$this->uc_login_check();
		$user_tixian_mod = D('user_tixian');
		//搜索
		$where = '1=1';		
		$where.= " AND uid='{$_COOKIE['user']['id']}'";		
		import("ORG.Util.Page");
		$count = $user_tixian_mod->where($where)->count();
		$p = new Page($count,10);
		$tixian_list = $user_tixian_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('addtime desc')->select();

		//print_r($tixian_list);
		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('tixian_list',$tixian_list);
        $this->assign('seo',$this->ucNavSeo('account_user_tixian',$_GET['uid']));		
		$this->display();
	}
    
	function account_cash_details(){
		$this->uc_login_check();		
		$type=isset($_GET['type'])?trim($_GET['type']):'1';		
		$log_mod = D('cash_back_log');
		$where = '1=1';		
		$where .= " AND type='{$type}'";
		$where.= " AND uid='{$_COOKIE['user']['id']}'";	
		$this->assign('type', $type);
	
		import("ORG.Util.Page");
		$count = $log_mod->where($where)->count();
		$p = new Page($count,10);
		$details_list = $log_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('details_list',$details_list);
        $this->assign('seo',$this->ucNavSeo('account_cash_details',$_GET['uid']));	
		$this->display();
		
	}
	function account_invitation(){
		$this->assign('share',array(
				'uc_url'=>'http://'.u('uc/index',array('uid'=>$_COOKIE['user']['id'])),
				'info'=>'女人天生爱购物，和我们一起来V购吧！~'.$this->site_domain
		));
        $this->assign('seo',$this->ucNavSeo('account_invitation',$_GET['uid']));	
		$this->display();
	}
    //短信息
   function account_message(){
        $this->uc_login_check();	
        $type = intval($_GET['type']) ? intval($_GET['type']) : 1;
        $model = M('UserMsg');
        import("ORG.Util.Page");
        if($type == 1){
            $where['to_user'] = $_COOKIE['user']['name'];
            $where['del'] = 0;
    		$count = $model->where($where)->count();
    		$p = new Page($count,10);
    		$msg_list = $model->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();		
    		$page = $p->show();
        }elseif($type == 2){
            $where['from_user'] = $_COOKIE['user']['name'];
            $where['del'] = 0;
    		$count = $model->where($where)->count();
    		$p = new Page($count,10);
    		$msg_list = $model->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();		
    		$page = $p->show();
        }
		$this->assign('page',$page);	
		$this->assign('msg_list',$msg_list);
        $this->assign('seo',$this->ucNavSeo('account_message',$_GET['uid']));	
		$this->display();
    }
    function delMsg(){
        //删除短信.
        $model = M('UserMsg');
        if(is_numeric($_POST['delid'])){
            $map['id'] = intval($_POST['delid']);
            //$map['id'] = array('in',$id);
            //$result = $model->where($map)->delete();
            $result = $model->where($map)->setField('del','1');
            
            if($result){
                echo 1;
                //$this->success("删除短信成功！");
            }else{
                echo 0;
                //$this->error("删除短信失败！");
            }
        }
    }	
    //发送短信
    function sendmsg(){
        if(!$this->check_login()){
			$this->ajaxReturn("not_login");
		}
        if($_GET['iid']){
            $info = M("UserMsg")->where("id={$_GET['iid']}")->find();
            $this->assign("info",$info);
        }
  
        $this->display();
    }
    function doSendMsg(){        
        $this->uc_login_check();		
        $info["to_user"] = $_POST['to_user'];
        $info["from_user"] = $_COOKIE["user"]["name"];
        $info["title"] = $_POST['title'];
        $info["content"] = $_POST['content'];
        $info["date"] = time();
        parent::sendMsg($info);
        echo 1;
            
    }
	function get_share_dialog(){
		$this->assign('cate_list',$this->get_cate_list());
		$this->display();
	}
	function items_collect(){
		$this->uc_login_check();  //检测用户是否登录		
		$itemcollect_mod = D('itemcollect');
		$items_cate_mod = D('items_cate');
		$items_tags_mod = D('items_tags');
		$items_mod = D('items');
		$items_user_mod = D('items_user');

		$url = isset($_REQUEST['url']) ? trim($_REQUEST['url']) : '';	
		$url = url_parse($url);		
		if (strpos($url, 'tmall.com')!== false || strpos($url, 'taobao.com')!== false){  //说明此商品是淘宝的商品
			$num_iid=get_id($url);
			$key = 'taobao_' . $num_iid;  //item_key
			$tb_top = $this->taobao_client();
			$req = $tb_top->load_api('TaobaokeItemsDetailGetRequest');			
			$req->setFields("num_iid,detail_url,title,nick,pic_url,price,click_url ");
			$req->setPid($this->setting['taobao_pid']);
			$req->setNick($this->setting['taobao_usernick']);
			$req->setNumIids($num_iid);			
			$resp = get_object_vars_final($tb_top->execute($req));
			if(!is_array($resp)){
				$this->ajaxReturn(array('err'=>'remote_not_exist'));
			}else{
				$data=$resp['taobaoke_item_details']['taobaoke_item_detail'];
			}	
			if(!is_array($data)){
				$this->ajaxReturn(array('err'=>'remote_not_exist'));
			}
			
			$commission=$this->get_commission($data['item']['title'],$data['item']['num_iid'],$p='commission');		
			$data['title']=$data['item']['title'];
			$data['price'] = $data['item']['price'];
			
			$data['img'] =  $data['item']['pic_url'].'_210x1000.jpg';
			$data['simg'] = $data['item']['pic_url'].'_64x64.jpg';				
			$data['bimg'] = $data['item']['pic_url'];	
			$data['seller_name'] = $data['item']['nick'];
			//返现金额		
			if(empty($commission)){
				$commission=0;
			}	  
			$data['cash_back_rate'] =$commission.'元';  
			$data['url'] = $data['click_url'];
			$data['author']='taobao';
			$data['item_key']='taobao_'.$num_iid;
			$tags = $items_tags_mod->get_tags_by_title($data['item']['title']);
			$data['cid'] = $items_cate_mod->get_cid_by_tags($tags);
			$data['tags'] = implode(' ', $tags);			
			$item_user_id = $items_user_mod->where("iid='{$num_iid}' AND uid='{$_COOKIE['user']['id']}'")->getField('id');
			//此人已经分享过此商品了
		    if($item_user_id){
				$this->ajaxReturn(array('err'=>'yet_exist'));
			}
			//此人没有分享过这个商品
			//如果这个商品存在，则不弹窗		
			$items_data = $items_mod->where("item_key='{$data['item_key']}'")->find();
			if($items_data){
				$item_user_data=array(
					'iid'=>substr($items_data['item_key'], 7),
					'item_id'=>$items_data['id'],
					'uid'=>$_COOKIE['user']['id'],
					'add_time'=>time()
				);
				$items_user_rel=$items_user_mod->add($item_user_data);
				if($items_user_rel){
					 //分享成功赠送积分
			        $map['uid']=$_COOKIE['user']['id'];
			        M('userInfo')->where($map)->setInc("integral",$this->setting['share_goods_score']);
					$this->ajaxReturn(array('err'=>'share_yes'));
				}
			}
			
		}
		else{//59miao 的商品开始			
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$data = $miao_api->ListItemsDetail('',$url);	
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
	        $data['seller_name'] = $data['seller_name'];
			$data['cash_back_rate'] = $data['cashback_scope'];        
			$data['url'] = $data['click_url'];
			$data['author']='miao';
			$data['item_key']='miao_'.$data['iid'];
			$tags = $items_tags_mod->get_tags_by_title($data['title']);
			$data['cid'] = $items_cate_mod->get_cid_by_tags($tags);
			$data['tags'] = implode(' ', $tags);
			$item_user_id = $items_user_mod->where("iid='{$data['iid']}' AND uid='{$_COOKIE['user']['id']}'")->getField('id');
			//此人已经分享过此商品了
		    if($item_user_id){
				$this->ajaxReturn(array('err'=>'yet_exist'));
			}
			//此人没有分享过这个商品
			//如果这个商品存在，则不弹窗		
			$items_data = $items_mod->where("item_key='{$data['item_key']}'")->find();
			if($items_data){
				$item_user_data=array(
					'iid'=>substr($items_data['item_key'], 5),
					'item_id'=>$items_data['id'],
					'uid'=>$_COOKIE['user']['id'],
					'add_time'=>time()
				);
				$items_user_rel=$items_user_mod->add($item_user_data);
				if($items_user_rel){
					 //分享成功赠送积分
			        $map['uid']=$_COOKIE['user']['id'];
			        M('userInfo')->where($map)->setInc("integral",$this->setting['share_goods_score']);
					$this->ajaxReturn(array('err'=>'share_yes'));
				}
			}
			//59miao 的商品结束
		}	
		$this->ajaxReturn($data);
	}	
	//淘宝登陆
	function tao_login(){
		//https://oauth.taobao.com/authorize?response_type=code&client_id=12575988&redirect_uri=http://www.97bijia.com/oauthLogin.php&state=1		
		$redirect_url = $this->site_root . "index.php?m=taologin&state=1";	
        $login_url = "https://oauth.taobao.com/authorize?response_type=code&client_id={$this->setting['taobao_appkey']}&redirect_uri=$redirect_url";
        header("Location:$login_url");       
	}
	//POST请求函数
	function curl($url, $postFields = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (is_array($postFields) && 0 < count($postFields))
		{
			$postBodyString = "";
			foreach ($postFields as $k => $v)
			{
				$postBodyString .= "$k=" . urlencode($v) . "&"; 
			}
			unset($k, $v);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
 			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
		}
		$reponse = curl_exec($ch);
		if (curl_errno($ch)){
			throw new Exception(curl_error($ch),0);
		}
		else{
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode){
				throw new Exception($reponse,$httpStatusCode);
			}
		}
		curl_close($ch);
		return $reponse;
	}	
	function qq_login(){
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'callback';
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        $redirect_uri = $this->site_root . "index.php?m=uc&a=qq_" . $type;

        $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
                . $this->setting['qq_app_key'] . "&redirect_uri=" . urlencode($redirect_uri)
                . "&state=" . $_SESSION['state'];
        header("Location:$login_url");		
	}
	function qq_callback(){
		if($_REQUEST['state'] == $_SESSION['state']) //csrf
		{
			$token_url = "https://graph.qq.com/oauth2.0/token";
			$aGetParam = array(
					"grant_type"    =>    "authorization_code",
					"client_id"        =>    $this->setting['qq_app_key'],
					"client_secret"    =>    $this->setting['qq_app_Secret'],
					"code"            =>   $_REQUEST["code"],
					"redirect_uri"    =>  $this->site_root."index.php?m=uc&a=qq_callback"
			);

			$res = $this->get($token_url,$aGetParam);
			if(trim($res)==''){
				exit('无法获取认证！<br/>');
			}
			if (strpos($res, "callback") !== false)
			{
				$lpos = strpos($res, "(");
				$rpos = strrpos($res, ")");
				$res  = substr($res, $lpos + 1, $rpos - $lpos -1);
				$msg = json_decode($res);
				if (isset($msg->error))
				{
					echo "<h3>error:</h3>" . $msg->error;
					echo "<h3>msg  :</h3>" . $msg->error_description;
					exit;
				}
			}
			parse_str($res, $res);
			$_SESSION["access_token"] = $res['access_token'];
		}
		$url = "https://graph.qq.com/oauth2.0/me";

		$str=$this->get($url,array('access_token'=>$_SESSION['access_token']));
		if (strpos($str, "callback") !== false)
		{
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
		}
		$res = json_decode($str);

		$_SESSION['openid']=$res->openid;

		$user_openid=$this->user_openid_mod->where("openid='".$res->openid."'")->find();
		$is_new=false;

		if($user_openid){
			$user_rel=$this->user_mod->where('id='.$user_openid['uid'])->find();
			if(count($user_rel)>0){
				//第二次登录
				//$_COOKIE['user']['id']=$user_openid['uid'];
				
				$last_time=time();
				$key=md5($user_openid['uid'].$user_openid['uname'].$last_time);				
				cookie('user[id]',$user_openid['uid'],3600*24*7);
				cookie('user[name]',$user_openid['uname'],3600*24*7);
				cookie('user[login_time]',$last_time,3600*24*7);				
				cookie('user[key]',$key,3600*24*7);		
				
				$data=array('last_time'=>time(),'last_ip'=>$_SERVER['REMOTE_ADDR']);
				$this->user_mod->where('id='.$_COOKIE['user']['id'])->save($data);	
				//存在这条数据的话，判断状态			
				if($user_rel['status']==0){					
					header('Location:'.U('uc/sign'));exit;
				}else{
					//登录成功送积分
				    if(date("Ymd",$user_rel['last_time']) != date("Ymd",time())){
                        $map['uid']=$user_rel['id'];
                        M('userInfo')->where($map)->setInc("integral",$this->setting['user_login_score']);
                    }
                    header('Location:'.U('index/index'));exit;
				}				
				
			}else{
				$this->user_openid_mod->where("openid='".$res->openid."'")->delete();
				$is_new=true;
			}
		}else{
			$is_new=true;
		}
		if($is_new){
			$url="https://graph.qq.com/user/get_user_info?"
			."access_token=".$_SESSION['access_token']
			."&openid=".$_SESSION['openid']
			."&oauth_consumer_key=".$this->setting['qq_app_key']
			."&format=json";
			$url="https://graph.qq.com/user/get_user_info";
			$param=array(
					'access_token'=>$_SESSION['access_token'],
					"openid"=>$_SESSION['openid'],
					"oauth_consumer_key"=>$this->setting['qq_app_key'],
					"format"=>'json',
			);

			$res= $this->get($url,$param);

			if($res==false){
			 exit('获取用户信息失败！');
			}
			$res=json_decode($res);
			
			$qq_info=array('user_info'=>$res);
			$data=array(
					'name'=>$res->nickname,
					//'img'=>$res->figureurl_2,
					'last_time'=>time(),
					'last_ip'=>$_SERVER['REMOTE_ADDR'],
					'add_time'=>time(),
					'status'=>0,
					'ip'=>$_SERVER['REMOTE_ADDR'],
			);
			
			//$_SESSION['user_id']=$this->user_mod->add($data);
			
			$last_uid=$this->user_mod->add($data);	
			$last_time=time();
			$key=md5($last_uid.$data['name'].$last_time);				
			cookie('user[id]',$last_uid,3600*24*7);
			cookie('user[name]',$data['name'],3600*24*7);
			cookie('user[login_time]',$last_time,3600*24*7);				
			cookie('user[key]',$key,3600*24*7);				
			$data=array(
					'type'=>'qq',
					'uid'=>$last_uid,
					'uname'=>$data['name'],
					'openid'=>$_SESSION['openid'],
					'info'=>serialize($qq_info),
			);
//			//保存用户头像			
//		
//	        import("ORG.Util.Face");
//	        $array = array("80"=>"m_","60"=>"z_","35"=>"s_");    
//	        $savename = "./data/user/".$last_uid."/";
//	        isDir($savename);
//	        foreach($array as $k=>$v){
//	            $savename = "./data/user/".$last_uid."/".$v.$last_uid.".jpg";   
//	            $obj = new Face($res->figureurl_2,$k,$k,0,0,$k,$savename);  
//	        }			
			//增加加user_info表
			$user_info_data=array(
					'uid'=>$last_uid,
					'info'=>'这个人很懒，什么都没有留下'
			);
			$this->user_info->add($user_info_data);
			$this->user_openid_mod->add($data);
			header('Location:'.U('uc/sign'));exit;
		}
		$_SESSION['login_type']='qq';
		//增加登录次数
        $this->user_mod->where('id='.$last_uid)->setInc('login_count',1);
      	header('Location:'.U('index/index'));exit;
	}
	function qq_bind(){

		if($_REQUEST['state'] == $_SESSION['state'])
		{

			$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
			. "client_id=" .$this->setting['qq_app_key']
			. "&redirect_uri=" . urlencode($this->site_root."index.php?m=uc&a=qq_callback")
			. "&client_secret=" .$this->setting['qq_app_Secret']
			. "&code=" . $_REQUEST["code"];

			$res = array();
			parse_str(file_get_contents($token_url), $res);
		}
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$res['access_token'];
		$str  = file_get_contents($graph_url);
		if (strpos($str, "callback") !== false)
		{
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
		}
		$res = json_decode($str);
		$qq_info=array('user_info'=>$res);
        //此处更改为$_COOKIE['user']['id']
		//$user_openid=$this->user_openid_mod->where("openid='".$res->openid."' and uid=".$_SESSION['user_id'])->find();
        $user_openid=$this->user_openid_mod->where("openid='".$res->openid."' and uid=".$_COOKIE['user']['id'])->find();

		if($user_openid){
			exit("已经绑定!");
		}else{
			$data=array(
					'type'=>'qq',

					'uid'=>$_COOKIE['user']['id'],
					'name'=>$res->nickname,
                    'openid'=>$res->openid,
					'info'=>serialize($qq_info),
			);
			$this->user_openid_mod->add($data);
		}
		header('Location:'.U('uc/account_sns'));exit;
	}
	function sns_unbind(){
		$type=$_REQUEST['type'];
		if(!isset($type))exit;

		$this->user_openid_mod->where('uid='.$_COOKIE['user']['id']." and type='$type'")->delete();
        header('Location:'.U('uc/account_sns'));exit;
	}
	function sina_login(){
		require_once ROOT_PATH.'/includes/saetv2.ex.class.php';
		$type=isset($_REQUEST['type'])?$_REQUEST['type']:'callback';
		$redirect_uri=$this->site_root."index.php?m=uc&a=sina_".$type;
        $o = new SaeTOAuthV2($this->setting['sina_app_key'] ,$this->setting['sina_app_Secret']);
        $login_url=$o->getAuthorizeURL($redirect_uri);
		
        header("Location:$login_url");
	}

	function sina_callback(){
		require_once ROOT_PATH.'/includes/saetv2.ex.class.php';        
		$o = new SaeTOAuthV2($this->setting['sina_app_key'],$this->setting['sina_app_Secret']);        
		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] =$this->site_root."index.php?m=uc&a=sina_callback";
            
			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
                
			}
           
            //$token = $o->getAccessToken( 'code', $keys ) ;
            
            if($token){
                $c = new SaeTClientV2( $this->setting['sina_app_key'] ,
				$this->setting['sina_app_Secret'] ,
				$token['access_token'] ,'' );
                
            }else{
                echo '登录失败!';exit;
            }
		}
		else{
			$url=U('index/index');
			header("Location:$url");
		}
		//$_SESSION['token'] = $token;
		//$_SESSION['access_token']=$token['access_token'];
		$_SESSION['openid']=$token['uid'];
        cookie('access_token',$token['access_token'],3600*24*7);
		$user_openid=$this->user_openid_mod->field('uid,uname,openid')->where("openid='".$token['uid']."'")->find();
        
		$is_new=false;
		if($user_openid){
			$user_rel=$this->user_mod->where('id='.$user_openid['uid'])->find();
			if(count($user_rel)>0){				
				$last_time=time();
				$key=md5($user_openid['uid'].$user_openid['uname'].$last_time);				
				cookie('user[id]',$user_openid['uid'],3600*24*7);
				cookie('user[name]',$user_openid['uname'],3600*24*7);
				cookie('user[login_time]',$last_time,3600*24*7);				
				cookie('user[key]',$key,3600*24*7);					
				$data=array('last_time'=>time(),'last_ip'=>$_SERVER['REMOTE_ADDR']);   //更新用户信息
				
			    $this->user_mod->where('id='.$_COOKIE['user']['id'])->save($data); 
			    if($user_rel['status']==0){//没有审核可以登录，但是不送积分
					header('Location:'.U('uc/sign'));
					exit;
				}else{ //登录成功送积分
				    if(date("Ymd",$user_rel['last_time']) != date("Ymd",time())){
                        $map['uid']=$user_rel['id'];
                        M('userInfo')->where($map)->setInc("integral",$this->setting['user_login_score']);
                    }
                    header('Location:'.U('index/index'));exit;
				}	
            }else{
				$this->user_openid_mod->where("openid='".$token['uid']."'")->delete();
				$is_new=true;
			}
		}else{
			$is_new=true;
		}
		if($is_new){
			$res=$c->show_user_by_id($token['uid']);
			if($res['error_code']==21321){
				$this->error('新浪微博网站接入审核未通过，无法获取该账户资料！');exit;
			}
			$sina_info=array('user_info'=>$res);
			$data=array(
					'name'=>$res['screen_name'],
					//'img'=>$res['profile_image_url'],
					'last_time'=>time(),
					'last_ip'=>$_SERVER['REMOTE_ADDR'],
					'add_time'=>time(),
					'ip'=>$_SERVER['REMOTE_ADDR'],
					'status'=>0
			);			
			//登录成功
			$last_uid=$this->user_mod->add($data);			
			$last_time=time();
			$key=md5($last_uid.$data['name'].$last_time);				
			cookie('user[id]',$last_uid,3600*24*7);
			cookie('user[name]',$data['name'],3600*24*7);
			cookie('user[login_time]',$last_time,3600*24*7);				
			cookie('user[key]',$key,3600*24*7);			
			$data=array(
					'type'=>'sina',
					'uid'=>$last_uid,
					'uname'=>$data['name'],
					'openid'=>$_SESSION['openid'],
					'info'=>serialize($sina_info),
			);			
			//增加加user_info表
			$user_info_data=array(
					'uid'=>$last_uid,
					'info'=>'这个人很懒，什么都没有留下'
			);
			$this->user_info->add($user_info_data);
			$this->user_openid_mod->add($data);
			header('Location:'.U('uc/sign'));exit;
		}
		$_SESSION['login_type']='sina';
		//增加登录次数
        $this->user_mod->where('id='.$last_uid)->setInc('login_count',1);
        
		//更新用户积分
		header('Location:'.U('index/index'));exit;
	}

	function sina_bind(){
		require_once ROOT_PATH.'/includes/saetv2.ex.class.php';
		$o = new SaeTOAuthV2($this->setting['sina_app_key'],$this->setting['sina_app_Secret']);
		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] =$this->site_root."index.php?m=uc&a=sina_callback";

			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {

			}
		}
		$c = new SaeTClientV2( $this->setting['sina_app_key'] ,
				$this->setting['sina_app_Secret'] ,
				$token['access_token'] ,'' );
		if(!$token){
			echo '登录失败!';exit;
		}
        //此处修改为$_COOKIE['user']['id']
		//$user_openid=$this->user_openid_mod->where("openid='".$token['uid']."' and uid=".$_SESSION['user_id'])->find();
        $user_openid=$this->user_openid_mod->where("openid='".$token['uid']."' and uid=".$_COOKIE['user']['id'])->find();
		if($user_openid){
			exit("已经绑定!");
		}else{
			$res=$c->show_user_by_id($token['uid']);
			if($res){
				$sina_info=array('user_info'=>$res);
				$data=array(
						'type'=>'sina',
						'uid'=>$_COOKIE['user']['id'],
						'uname'=>$res['screen_name'],
						'openid'=>$token['uid'],
						'info'=>serialize($sina_info),
				);
				$this->user_openid_mod->add($data);
			}else{
				echo '获取用户信息失败!';exit;
			}
		}
		header('Location:'.U('uc/account_sns'));exit;
	}
	function login(){
	    //引入配置文件、类库      
        $this->require_uc();
        //判断用户是否登陆
        if($this->check_login())header('Location:'.U('index/index'));
        //登陆
        if (isset($_POST['dosubmit']) || $this->isAjax()){
            $name = trim($_POST['name']);
            $passwd = trim($_POST['passwd']);
            
            if($_SESSION['verify']!=md5(trim($_POST['verify']))){
            	if($this->isAjax()){
                   $this->ajaxReturn(array('err'=>0,'msg'=>'验证码错误'));
                }else{
                   $this->assign('err',array('err'=>0,'msg'=>'验证码错误'));
                }				$this->display();
                exit();
            } 
            $user=$this->user_mod->where("name='".$name."' and passwd='".md5($passwd)."' and status=1")->find();
            if(!$user && !$this->setting['ucenterlogin']){
                    if($this->isAjax() && empty($user)){
                        $this->ajaxReturn(array('err'=>0,'msg'=>'昵称或密码错误,或者此用户被屏蔽!'));
                    }else{
                        $this->assign('err',array('err'=>0,'msg'=>'昵称或密码错误,或者此用户被屏蔽!'));
                    }
            }
            if($this->setting['ucenterlogin']){
                //通过接口判断登录帐号的正确性，返回值为数组
            	list($uid, $username, $password, $email) = uc_user_login($name,$passwd);
                
            	setcookie('Ucenter_auth', '', -86400);
            	if($uid > 0) { //ucenter 里存在用户 登录成功           	   
                    $password = md5($_POST['passwd']);
                    //当UC存在用户,而wg不存在时,就注册一个    
                    if(!$user){
                        //会员的默认信息
                        $data = array("name"=>$name,"passwd"=>$password,"email"=>$email,"ip"=>$_SERVER["REMOTE_ADDR"],"add_time"=>time(),"user_info" => array("sex" =>  "2","integral" => $this->setting['user_register_score']));
                        $id=$this->user_mod->relation('user_info')->add($data);	
                        $user = array('id'=>$id,'name'=>$name);
                    }
                    //设置本程序cookie
                    $last_time=time();
    				$key=md5($user['id'].$user['name'].$last_time);
    				cookie('user[id]',$user['id'],3600*24*7);
    				cookie('user[name]',$user['name'],3600*24*7);
    				cookie('user[login_time]',$last_time,3600*24*7);				
    				cookie('user[key]',$key,3600*24*7);	
    				$user_data=array(
    					'last_time'=>$last_time,
    					'last_ip'=>$_SERVER['REMOTE_ADDR']
    				);
                    //更新用户积分
                    if(date("Ymd",$user['last_time']) != date("Ymd",time())){
                        $map['uid']=$user['id'];
                        M('userInfo')->where($map)->setInc("integral",$this->setting['user_login_score']);
                    }
    				//更新用户信息
    				$this->user_mod->where("name='".trim($_POST['name'])."' and passwd='".md5(trim($_POST['passwd']))."'")->save($user_data);							
    				$url = U('index/index');
    				//用户登陆成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
            		setcookie('Ucenter_auth', uc_authcode($uid."\t".$username, 'ENCODE'));
            		//生成同步登录的代码
            		echo $ucsynlogin = uc_user_synlogin($uid);          				
                    if($this->isAjax() && !empty($user)){
                        $this->ajaxReturn(array('err'=>1,'msg'=>'登录成功!'));
                    }elseif($this->isAjax() && empty($user)){
                        $this->ajaxReturn(array('err'=>0,'msg'=>'昵称或密码错误!'));
                    }elseif(isset($_POST['dosubmit'])){
                    	//增加登录次数
                    	$this->user_mod->where('id='.$user['id'])->setInc('login_count',1);
                        echo "<script>location='{$url}';</script>";
                    }else{
        				$this->assign('err',array('err'=>0,'msg'=>'昵称或密码错误!'));
        			}
    
            	} else if($uid == -1) { 
            		if($user){//如果本程序有用户而ucenter里面没有用户的时候给ucenter注册一个同时登录成功          		         
                        $rid = uc_user_register($name, $_POST['passwd'], $user['email']);
                        if($rid > 0){
                        	$uid = $rid;
                        }                       
					 	$last_time=time();
	    				$key=md5($user['id'].$user['name'].$last_time);
	    				cookie('user[id]',$user['id'],3600*24*7);
	    				cookie('user[name]',$user['name'],3600*24*7);
	    				cookie('user[login_time]',$last_time,3600*24*7);				
	    				cookie('user[key]',$key,3600*24*7);	
	    											
	    				$user_data=array(
	    					'last_time'=>$last_time,
	    					'last_ip'=>$_SERVER['REMOTE_ADDR']
	    				);
	                    //更新用户积分
	                    if(date("Ymd",$user['last_time']) != date("Ymd",time())){
	                        $map['uid']=$user['id'];
	                        M('userInfo')->where($map)->setInc("integral",$this->setting['user_login_score']);
	                    }
	    				//更新用户信息
	    				$this->user_mod->where("name='".trim($_POST['name'])."' and passwd='".md5(trim($_POST['passwd']))."'")->save($user_data);							
	    				$url = U('index/index');
	    				
	    				
	            		//用户登陆成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
	            		setcookie('Ucenter_auth', uc_authcode($uid."\t".$name, 'ENCODE'));
	            		//生成同步登录的代码
	            		echo $ucsynlogin = uc_user_synlogin($uid);	                   
	                    if($this->isAjax() && !empty($user)){
	                        $this->ajaxReturn(array('err'=>1,'msg'=>'登录成功!'));
	                    }elseif($this->isAjax() && empty($user)){
	                        $this->ajaxReturn(array('err'=>0,'msg'=>'昵称或密码错误!'));
	                    }elseif(isset($_POST['dosubmit'])){
	                    	//增加用户登录次数
                    		$this->user_mod->where('id='.$user['id'])->setInc('login_count',1);
	                        echo "<script>location='{$url}';</script>";
	                    }else{
	        				$this->assign('err',array('err'=>0,'msg'=>'昵称或密码错误!'));
	        			}	                        
	            	}
	            	else{
	            		$this->assign('err',array('err'=>0,'msg'=>'用户不存在,或者被删除!'));
	            	}
                    
            	} elseif($uid == -2) {
                    $this->assign('err',array('err'=>0,'msg'=>'密码错误!'));
            	} else {
                    $this->assign('err',array('err'=>0,'msg'=>'未定义'));
            	}
                
            }else{ //不开启ucenter登录，执行直接登录
                if($user){
                    $last_time=time();
    				$key=md5($user['id'].$user['name'].$last_time);
    				cookie('user[id]',$user['id'],3600*24*7);
    				cookie('user[name]',$user['name'],3600*24*7);
    				cookie('user[login_time]',$last_time,3600*24*7);				
    				cookie('user[key]',$key,3600*24*7);	
    											
    				$user_data=array(
    					'last_time'=>$last_time,
    					'last_ip'=>$_SERVER['REMOTE_ADDR']
    				);
                    //更新用户积分
                    if(date("Ymd",$user['last_time']) != date("Ymd",time())){
                        $map['uid']=$user['id'];
                        M('userInfo')->where($map)->setInc("integral",$this->setting['user_login_score']);
                    }
    				//更新用户信息
    				$this->user_mod->where("name='".trim($_POST['name'])."' and passwd='".md5(trim($_POST['passwd']))."'")->save($user_data);							
    				$url = U('index/index');
                    if($this->isAjax() && !empty($user)){
                        $this->ajaxReturn(array('err'=>1,'msg'=>'登录成功!'));
                    }elseif($this->isAjax() && empty($user)){
                        $this->ajaxReturn(array('err'=>0,'msg'=>'昵称或密码错误!'));
                    }elseif(isset($_POST['dosubmit'])){
                    	//增加用户登录次数
                    	$this->user_mod->where('id='.$user['id'])->setInc('login_count',1);                    	
                        echo "<script>location='{$url}';</script>";
                    }else{
        				$this->assign('err',array('err'=>0,'msg'=>'昵称或密码错误!'));
        			}
                }
            }
            
        }        
        
		$this->display();
	}    
	function register(){		
		if (isset($_POST)) $_POST = setHtmlspecialchars(setFormString($_POST));
		if($this->check_login()){
			header('location:'.u('index/index'));
		}		
        //引入配置文件、类库
    	$this->require_uc();        
		if (isset($_POST['dosubmit'])) {				
		  if($this->setting['ucenterlogin']){
		    //检查UCENTER中是否有此用户  
		    $ucresult = uc_user_checkname(trim($_POST['name']));
            if($ucresult == -1) {
            	$this->assign('err',array('err'=>0,'msg'=>'用户名不合法!'));
            } elseif($ucresult == -2) {
                $this->assign('err',array('err'=>0,'msg'=>'包含要允许注册的词语!'));
            } elseif($ucresult == -3) {
                $this->assign('err',array('err'=>0,'msg'=>'用户名已经存在!'));
            }
          }  
			$data=$this->user_mod->create();
			$this->assign('data',$data);
			$flag=true;
			if($_SESSION['verify']==md5(trim($_POST['verify']))){
				if($this->user_mod->where("name='".trim($data['name'])."'")->count()){
					$this->assign('err',array('err'=>0,'msg'=>'昵称已存在!'));
					$flag=false;
				}else if(strlen(trim($data['email']))>0){
					if($this->user_mod->where("email='".trim($data['email'])."'")->count()){
						$this->assign('err',array('err'=>0,'msg'=>'邮箱已经存在!'));
						$flag=false;
					}
				}
			}
			else{
				$this->assign('err',array('err'=>0,'msg'=>'验证码不正确!'));
				$flag=false;
			}

			if($flag){							
                if($this->setting['ucenterlogin']){
                    $uid = uc_user_register($_POST['name'], $_POST['passwd'], $_POST['email']);                    
                }
                $data['ip']=$_SERVER['REMOTE_ADDR'];
				$data['add_time']=time();
				$data['passwd']=md5(trim($data['passwd']));
                $data['user_info']['sex']=$_POST['sex'];
				$data['user_info']['integral']=$this->setting['user_register_score'];
                if($this->setting['ucenterlogin']){
                    if($uid <= 0) {
                    	if($uid == -1) {
                    	   $this->assign('err',array('err'=>0,'msg'=>'用户名不合法!'));
                    	} elseif($uid == -2) {
                    	   $this->assign('err',array('err'=>0,'msg'=>'包含要允许注册的词语!'));
                    	} elseif($uid == -3) {
                    	   $this->assign('err',array('err'=>0,'msg'=>'用户名已经存在!'));
                    	} elseif($uid == -4) {
                    	   $this->assign('err',array('err'=>0,'msg'=>'Email 格式有误!'));
                    	} elseif($uid == -5) {
                    	   $this->assign('err',array('err'=>0,'msg'=>'Email 不允许注册'));
                    	} elseif($uid == -6) {
                            $this->assign('err',array('err'=>0,'msg'=>'该 Email 已经被注册'));
                    	} else {
                    	   $this->assign('err',array('err'=>0,'msg'=>'未定义'));
                    	}
                    } else { 
                        $id=$this->user_mod->relation('user_info')->add($data);     
                        //用户登陆成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
	            		setcookie('Ucenter_auth', uc_authcode($uid."\t".$data['name'], 'ENCODE'));                   
                    }
                }else{
                    $id=$this->user_mod->relation('user_info')->add($data);
                }
				//$_SESSION['user_id']=$id;   //注册以后同步dengl
				/* 
                发送站内信
                array(to_user,form_user,title,content,date)
                注册短信：尊敬的[name]您好:欢迎注册[WEBTITLE],凡是通过[WEBTITLE]提供的链接去淘宝购物进行购物，
                都将享受到1%到50%成交额的返现，推广其他用户，即可获取被推广用户返现额的[tg]%的推广佣金，
                推广越多挣钱越轻松。祝您购物愉快！也欢迎您把我们的网站告诉更多的淘宝买家，谢谢！
                注册送积分：恭喜您，您获得本站注册赠送积分[ZSJIFEN]。
                */
                $patterns[0] = "/\[name\]/";
                $patterns[1] = "/\[WEBTITLE\]/";
                $patterns[2] = "/\[tg\]/";
                
                $replacements[2] = $data['name'];
                $replacements[1] = $this->setting['site_name'];
                $replacements[0] = $this->setting["cashback_rate"];
                
                
                $map['key'] = 'msg_zhuce';
                $msgtitle = "用户注册短信";
                $fromUser = getAdminUserName();
                $content = M("user_setmsg")->where($map)->find();
                $msgcontent = preg_replace($patterns, $replacements, $content);
                $sendmsg = array("to_user"=>"{$data['name']}","from_user"=>"{$fromUser}","title"=>"{$msgtitle}","content"=>"{$msgcontent['val']}","date"=>time());
   
                parent::sendMsg($sendmsg);
                
                //送积分
                $map['key'] = 'msg_zsjifen';
                $msgtitle = "赠送积分短信";
                $jifen = M('setting')->where("name='user_register_score'")->find();
                $content = M("user_setmsg")->where($map)->find();
                $msgcontent = str_replace("[ZSJIFEN]", $jifen['data'], $content);
                $sendmsg = array("to_user"=>"{$data['name']}","from_user"=>"{$fromUser}","title"=>"{$msgtitle}","content"=>"{$msgcontent['val']}","date"=>time());
                parent::sendMsg($sendmsg);
   
//				$last_time=$data['add_time'];
//				$key=md5($id.$data['name'].$last_time);
//				cookie('user[id]',$id);
//				cookie('user[name]',$data['name']);
//				cookie('user[login_time]',$last_time);				
//				cookie('user[key]',$key);	
//				
				
				//设置本程序cookie
                $last_time=time();
    			$key=md5($id.$data['name'].$last_time);
    			cookie('user[id]',$id,3600*24*7);
    			cookie('user[name]',$data['name'],3600*24*7);
    			cookie('user[login_time]',$last_time,3600*24*7);				
    			cookie('user[key]',$key,3600*24*7);
//				if($this->setting['ucenterlogin']){
//                   echo $ucsynlogin = uc_user_synlogin($uid);	  //生成同步登录的代码                    
//                }
				
				//如果购买商品的时候跳转过来的则跳转回购买商品页面
				if(isset($_GET['item_id'])&&intval($_GET['item_id'])!=''){	
					$item_id=intval($_GET['item_id']);					
					header('location:'.u('item/index',array('id'=>$item_id)));
				}else{
					header('location:'.u('uc/index'));
				}	
			}
		}
		$this->display();
	}
    function passverify(){
        if($_POST['name']){
            $map['name'] = htmlentities(trim($_POST['name']));
            echo M('user')->field('id')->where($map)->find() ? 0 : 1;
        }
    }
	function logout(){
        //引入配置文件、类库
        $this->require_uc();            
		if($_SESSION['login_type']=='sina'){
			$url="https://api.weibo.com/2/account/end_session.json?"
			."access_token=".$_COOKIE['access_token']
			."&source".$this->setting['sina_app_key'];
			$res=file_get_contents($url);
		}
	    $_SESSION['login_type'] ='';
        cookie('access_token',null);
        cookie('user[id]',null);
		cookie('user[name]',null);
		cookie('user[login_time]',null);
		cookie('user[key]',null);
        if($this->setting['ucenterlogin']){
    		setcookie('Ucenter_auth', '', -86400);
            //生成同步退出的代码
            echo $ucsynlogout = uc_user_synlogout();
        }
        $url = U('index/index');
        echo "<script>location='{$url}';</script>";
        //header('Location:'.U('index/index'));
		//header('Location:'.urldecode($_COOKIE['redirect']));
	}
	function sign(){
		if(!$this->check_login()){
			header('location:'.u('index/index'));
		}
		if (isset($_POST['dosubmit'])){
			$count=$this->user_mod->where('id!='.$_COOKIE['user']['id']." and name='".trim($_POST['name'])."'")->count();
			if($count){
				$this->assign('err',array('err'=>0,'msg'=>'昵称已经存在!'));
				$this->display();
				exit;
			}
			if(strlen(trim($_POST['passwd']))<6){
				$this->assign('err',array('err'=>0,'msg'=>'密码至少为6位!'));
				$this->display();
				exit;
			}
			$data=array(
					'name'=>trim($_POST['name']),
					'passwd'=>md5(trim($_POST['passwd'])),
					'status'=>1
			);
			$data_info['integral']=$this->setting['user_register_score'];//注册成功增加用户积分			
			$this->assign('uid',$_COOKIE['user']['id']);
			$this->user_mod->where('id='.$_COOKIE['user']['id'])->save($data);
			$this->user_info->where('uid='.$_COOKIE['user']['id'])->save($data_info);
			header('Location:'.U('uc/index'));
		}
		$this->display();
	}
	function add_comment(){
		$uid=$_POST['uid'];
		if($uid!=$_SESSION['user_id'])exit;
		$data=$this->items_comments_mod->create();
		$data['add_time']=time();
		$data['info']=$this->remove_html($data['info']);

		$this->items_comments_mod->add($data);

		$items_mod = D('items');
		$items_mod->where('id=' . $data['items_id'])->setInc('comments');
		$this->ajaxReturn('提交成功!');
	}
	function follow(){
		$act=$_REQUEST['act'];
		$user_follow_mod=D('user_follow');
		$user_mod=$this->user_mod;
		$user_info=$this->user_info;
		$like_list_mod=D('LikeListView');
        $items_mod=D("items");
		$user_history_mod=D('user_history');

		if($act=='add'){
			$data=$user_follow_mod->create();
			if(intval($data['fans_id'])==$_COOKIE['user']['id'])exit;

			$data['uid']=$_COOKIE['user']['id'];
			$data['add_time']=time();
			$user_follow_mod->add($data);
			$fans_id=$data['fans_id'];			
			$u=$user_mod->where('id='.$fans_id)->find();
			//动态
			$data=array();
			$href=U('uc/index',array('uid'=>$u['id']));
			$name=$u['name'];

			$data['uid']=$_COOKIE['user']['id'];
			$data['uname']=$_COOKIE['user']['name'];
			$data['add_time']=time();
			$data['info']="关注了<a href='$href'>@$name</a>";

			$user_history_mod->add($data);
		}else if($act=='del'){
			$fans_id=intval($_REQUEST['fans_id']);
			$user_follow_mod->where("fans_id=".intval($_REQUEST['fans_id'])
					." and uid=".$_COOKIE['user']['id'])
					->delete();
			
		}
		if($act=='add'||$act=='del'){
			//更新自己的关注数
			$data=array();
			$data['follow_num']=$user_follow_mod
			->where('uid='.$_COOKIE['user']['id'])->count();
			$user_info->where('uid='.$_COOKIE['user']['id'])->save($data);
			//print_r($user_mod->getLastSql());//exit;
			//更新被关注人的粉丝数
			$data=array();
			$data['fans_num']=$user_follow_mod
			->where('fans_id='.$fans_id)->count();
			$user_info->where('uid='.$fans_id)->save($data);
			$this->ajaxReturn('success');
		}		
		$where ="uid='{$this->uid}'";
		$count=$user_follow_mod->where($where)->count();

		$pager=$this->pager($count);

		$res=$user_follow_mod->where($where)
		->limit($pager->firstRow.",".$pager->listRows)
		->order("id desc")
		->select();
		foreach($res as $key=>$val){
			//我是否关注了ta
			$res[$key]['is_follow']=$this->user_follow_mod
			->where('fans_id='.$res[$key]['fans_id'].' and uid='.$_COOKIE['user']['id'])
			->count()>0;
			//该用户最新动态
			$user_rel=$this->user_mod->where("id={$res[$key]['fans_id']}")->relation('user_info')->select();
			$res[$key]['user_info']=$user_rel[0];
		}		
		$this->assign('list',$res);

        //最近喜欢、分享了……
        //用户喜欢->union('SELECT id FROM pp_items limit 5')
        $map['uid'] = $this->uid;
		$likelist=$like_list_mod->where($map)->order('LikeList.id desc')->limit(8)->select();
        $this->assign('likelist', $likelist);
        //用户分享
        $itemslist = $items_mod->field('id,img,url,add_time')->where($map)->order('id desc')->limit(3)->select();
        $this->assign('itemslist', $itemslist);
		$this->display();
	}
	function fans(){
		$user_follow_mod=D('user_follow');
		$user_mod=D('user');
        $like_list_mod=D('LikeListView');
        $items_mod=D("items");

		$where="fans_id=".$this->uid;
		$count=$user_follow_mod->where($where)->count();

		$pager=$this->pager($count);

		$res=$user_follow_mod->where($where)
		->limit($pager->firstRow.",".$pager->listRows)
		->order("id desc")
		->select();
		foreach($res as $key=>$val){			
			$res[$key]['is_follow']=$this->user_follow_mod
			->where('fans_id='.$res[$key]['uid'].' and uid='.$_COOKIE['user']['id'])
			->count()>0;
			//该用户最新动态
			$user_rel=$this->user_mod->field('id,name')->where("id={$res[$key]['uid']}")->relation('user_info')->select();
			$res[$key]['user_info']=$user_rel[0];
			
		}

        $this->assign('list',$res);
        //最近喜欢、分享了……
        //用户喜欢->union('SELECT id FROM pp_items limit 5')
        $map['uid'] = $this->uid;
		$likelist=$like_list_mod->where($map)->order('LikeList.id desc')->limit(8)->select();
        $this->assign('likelist', $likelist);
        //用户分享
        $itemslist = $items_mod->field('id,img,url,add_time')->where($map)->order('id desc')->limit(3)->select();
        $this->assign('itemslist', $itemslist);
        
		$this->display();
	}
    function gz(){
        $uid = $_POST['uid'];
        $u = $this->user_mod->where('id='.$uid)->field("id")->relation('user_info')->find();
        $this->ajaxReturn($u,"获取数据成功！",1);
    }
	function comments(){
		import("ORG.Util.Page");
		$user_comments_mod=D('user_comments');
		$act=$_REQUEST['act'];
		$type=$_REQUEST['type'];
		$pid=empty($_REQUEST['pid'])?0:intval($_REQUEST['pid']);
        
		if($act=='add'){
			if(empty($_COOKIE['user']['id']))exit;
			$data=$user_comments_mod->create();
			$replace = str_replace("\n"," ",$data['info']);
			$data['info']=htmlspecialchars(ReplaceKeywords($replace));
			$data['add_time']=time();
			$data['uid']=$_COOKIE['user']['id'];	
			$data['uname']=$_COOKIE['user']['name'];		
			$user_comments_mod->add($data);
			if($data['type']=='item,index'){
				$arr=array(
						'id'=>$data['pid'],
						'comments'=>$this->user_comments_mod
						->where('pid='.$data['pid'].' and type="item,index"')
						->count(),
				);
				$this->items_mod->save($arr);
			}
			return;
		}
  
		$where="type='".$_REQUEST['type']."' and pid=$pid and status=1";

		$count = $user_comments_mod->where($where)->count();
		$p = new Page($count,8);
		$list=$user_comments_mod->where($where)->relation('user')->where($where)
		->order("id desc")->limit($p->firstRow.','.$p->listRows)
		->select();
      
		$this->assign('comments',array('list'=>$list,'page'=>$p->show_1(),'count'=>$count,'type' => $type));
		if($this->isAjax()){
			$this->ajaxReturn(array('list'=>$this->fetch('comments_list'),
					'count'=>$count));
		}
	}

	function share_result_dialog(){
		$cate_list=$this->items_cate_mod->get_top2_list();		
		$this->assign('cate_list', $cate_list);
		$this->display();
	}
	function nocid_share_result_dialog(){
		$cate_list=$this->items_cate_mod->get_top2_list();		
		$this->assign('cate_list', $cate_list);
		$this->display();
	}
    /*
    * 获取子分类至获取单级的分类
    */
    public function get_child_cates() {        
        $items_cate_mod = $this->items_cate_mod;
        $parent_id = $this->_get('parent_id', 'intval');
        $cate_list = $items_cate_mod->field('id,name')->where(array ('pid' => $parent_id))->order('ordid asc')->select();
        $content = "";
        foreach ($cate_list as $val) {
            $content .= "<option value='" . $val['id'] . "'>" . $val['name'] . "</option>";
        }
       echo $content;
       exit;
      //  echo json_encode($data);
        
    }
	function get_last_history($uid){
		$user_history_mod=D('user_history');
		$res=$user_history_mod->where("uid=$uid")->order("id desc")->find();
		return $res['info'];
		
	}
	//用户提示信息
	function user_tip(){
		$fans_id=intval($_POST['uid']);		
		if(empty($fans_id)){
			return ;
		}
		if(intval($fans_id)==$_COOKIE['user']['id'])
		{
			$this->assign('own','1');
		}
		else{
			$this->assign('own','0');
		}			
		$user_follow_mod=D('user_follow');
		$uid=isset($_COOKIE['user']['id'])?$_COOKIE['user']['id']:0;
		$fan_uid=$user_follow_mod->field('fans_id')->where('uid='.$uid.' AND fans_id='.$fans_id.'')->select();	
		//存在表示已经关注
		if(count($fan_uid)>0){
			$this->assign('has_fan','1');
		}
		else{
			$this->assign('has_fan','0');
		}
				
//			$data['add_time']=time();
//			$user_follow_mod->add($data);
		
		
		//查询用户信息
		$user_info=$this->user_mod->relation('user_info')->where('id='.$fans_id.'')->find();	
		if(count($user_info)<=0){
			return ;
		}
		$this->assign('user_info',$user_info);
		$this->display();
		
	}
	
	//用户评论信息
	function user_info_dialog(){
		if(!$this->check_login()){
			$this->ajaxReturn("not_login");
		}	
		if(isset($_POST['act'])&&$_POST['act']=='update'){
			$user_info=$this->user_info;
			$data=$user_info->create();					
			if($data['uid']==$_COOKIE['user']['id']){
				$rel=$user_info->where("uid='{$data['uid']}'")->save($data);				
				if($rel){
					$this->ajaxReturn("success");
				}
				else{
					$this->ajaxReturn("error");						
				}
			}
			else{
				$this->ajaxReturn("error");
			}			
		}		
		$res=$this->user_info->where('uid='.$_COOKIE['user']['id'])->find();	
		$this->assign('list',$res);
		$this->display();
	}
	//找回密码
	public function find_password(){
		if(isset($_POST['dosubmit'])&&$_POST['find_password']=='find_password'){
			$find_password_log_mod=D('find_password_log');	
			$uname=trim($_POST['name']);
			$email=trim($_POST['email']);
			$user_rel=$this->user_mod->where("name='{$uname}' AND email='{$email}'")->find();
			//执行入库操作
			if($user_rel>0){					
				$uid=$user_rel['id'];
				$create_time=time();
				$ip=$_SERVER['REMOTE_ADDR'];
				$md5_data=md5(rand(0, 1000).$uid.$create_time.$ip);					
				$add_data=array(
					'uid'=>$uid,
					'md5'=>$md5_data,
					'create_time'=>$create_time,
					'ip'=>$ip					
				);
				if($find_password_log_mod->add($add_data)){
					$url=$this->setting['site_domain'].'?a=ac_pwd&m=uc&k='.$md5_data;
					$address=$email;
					$title='找回'.$user_rel['name'].'在'.$this->setting['site_name'].'的密码';
					$message='<p>'.$user_rel['name'].' 您好:</p>
					<p>请点击下面的地址或将下面的地址输入到浏览器地址栏完成取回密码操作。 (注意：如果您没有进行过取回密码操作，请不要点击此链接)</p>	
					<p><a href="'.$url.'" target="_blank">'.$url.'</a></p>		
					<p>(本地址在24小时内有效)</p>								
					';
					//增加成功 发送邮件
					$this->sendMail($address, $title, $message);
					$this->assign('err',array('err'=>1,'msg'=>'恭喜您,提交信息成功 ,请查收邮件'));
				}		
				
			}
			else{
				$this->assign('err',array('err'=>0,'msg'=>'您的用户名或者邮箱输入错误'));
			}				
		
		}
		$this->display();
	}	
	//点击找回密码执行找回操作
	public function ac_pwd(){	
		//执行修改密码操作				
		if(isset($_POST['dosubmit'])&&isset($_POST['k'])){			
				$k=setFormString($_POST['k']);
				$pass=setFormString(trim($_POST['password']));
				$rpass=setFormString(trim($_POST['rpassword']));
				
				$pass_log_mod=D('find_password_log');		
				$rel=$pass_log_mod->where("md5='{$k}'")->find();
				if(count($rel)>0){				
					if($pass==$rpass){
						if($rel['status']==0){   //只有状态为0的时候才可以激活
							//修改密码
							$user_mod=$this->user_mod;
							$data=array();
							$data['passwd']=md5($pass);		
							//判断原来的密码和现在的是否相等
							$user_rel=$user_mod->where("id='{$rel['uid']}' AND passwd='{$data['passwd']}'")->find();
							if($user_rel){
								$this->error('此密码与原来的密码相同');							
							}							
							$user_rel=$user_mod->where("id='{$rel['uid']}'")->save($data);
							if($user_rel){
								//修改状态
								$status=array();
								$status['status']=1;
								$pass_log_mod->where("md5='{$k}'")->save($status);
								$this->assign('err',array('err'=>'success','msg'=>'恭喜您，修改密码成功，请重新登录'));
								$this->assign('url',$this->setting['site_domain'].'/index.php?m=uc&a=login');
							}
							else{
								$this->assign('err',array('err'=>'error','msg'=>'修改密码失败请重新修改'));
								$this->assign('url',$this->setting['site_domain'].'/index.php?a=find_password&m=uc');
							}		
						}										
					}
				}
				else{
					$this->assign('err',array('err'=>'error','msg'=>'请通过正规的方式修改密码'));
					$this->assign('url',$this->setting['site_domain']);
				}
		}		
		else{			
			//执行修改显示操作		
			if(!isset($_GET['k'])){
				$this->assign('url',$this->setting['site_domain']);
				$this->assign('err',array('err'=>'error','msg'=>'您的链接地址不正确，请通过正规方式找回密码'));
			}
			else{				
				$k=trim($_GET['k']);
				$pass_log_mod=D('find_password_log');
				
				$rel=$pass_log_mod->where("md5='{$k}'")->find();
				if(count($rel>0)){
					
					if($rel['status']==0){
						$now_time=time();			
						if(($now_time-$rel['create_time'])>60*60*2){
							$this->assign('url',$this->setting['site_domain']);
							$this->assign('err',array('err'=>'error','msg'=>'您的链接地址已经过期，请重新找回密码'));
							//错误，时间大于俩小时
						}
						else{
							$this->assign('k',$k);
							//执行修改密码操作
							$this->assign('ac_pwd','重置密码');
						}
					}
					else{
						$this->assign('url',$this->setting['site_domain']);
						$this->assign('err',array('err'=>'error','msg'=>'您已经激活过密码了此链接不可用'));	
					}
					
					
				}
				else{
					$this->assign('url',$this->setting['site_domain']);
					$this->assign('err',array('err'=>'error','msg'=>'您的链接地址不正确，请通过正规方式找回密码'));			
				}
			}
			
		}	
		
		$this->display();
	}
	public function account_face(){
	   $this->uc_login_check();
       $face = getUserFace($_COOKIE['user']['id'],'all');
       $this->assign('face',$face);
       $this->assign('seo',$this->ucNavSeo('account_face',$_GET['uid']));
       $this->display();
	}
    
    public function upload(){
       $this->uc_login_check();
       
       import("ORG.Net.UploadFile");
       $upload = new UploadFile(); // 实例化上传类

       $upload->maxSize  = 103000 ; // 设置附件上传大小
       $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
       $upload->savePath = ROOT_PATH.'/data/user/'; // 设置附件上传目录
       //$upload->saveRule = time();
       $upload->uploadReplace = true;
       $upload->thumb = true;//：是否需要对图片文件进行缩略图处理，默认为false
       $upload->thumbMaxWidth = 500;//：缩略图的最大宽度，多个使用逗号分隔
       $upload->thumbMaxHeight = 500;//：缩略图的最大高度，多个使用逗号分隔
       $upload->thumbFile = "{$_COOKIE['user']['id']}";//：指定缩略图的文件名
       $upload->thumbPrefix = 't_';//：缩略图的文件前缀，默认为thumb_
       $upload->thumbRemoveOrigin = true;//：生成缩略图后是否删除原图
       $upload->thumbPath = ROOT_PATH."/data/user/{$_COOKIE['user']['id']}/";
       $upload->autoSub = true;//：是否使用子目录保存上传文件
       $upload->subType = 'custom';//ROOT_PATH."/data/user/{$_COOKIE['user']['id']}/";//：子目录创建方式，默认为hash，可以设置为hash或者date
       $upload->custom = $_COOKIE['user']['id'];
 
       if(!$upload->upload()) { // 上传错误提示错误信息
           $this->error($upload->getErrorMsg());
       }else{ // 上传成功获取上传文件信息        
           $info =  $upload->getUploadFileInfo();        
       }   
       $_SESSION['face'] = SITE_ROOT."data/user/".$_COOKIE['user']['id']."/t_".$info[0]["name"];
       $url = U('uc/showface');
       echo "<script>location='".$url."'</script>";
       //header("location:");
    }
    public function showface(){
    	list($src_width, $src_height) = getimagesize($_SESSION['face']);
    	$this->assign('src_width',$src_width);
    	$this->assign('src_height',$src_height);
        $this->uc_login_check();
        
        $this->display();
    }
    public function doShowFace(){

        $this->uc_login_check();
        import("ORG.Util.Face");
        $array = array("80"=>"m_","60"=>"z_","35"=>"s_");
        $image = str_replace(SITE_ROOT,"",$_POST['image']);
        
        foreach($array as $k=>$v){
            $savename = "./".dirname($image)."/".$v.$_COOKIE['user']['id'].".jpg";
            $obj = new Face("./".$image,$_POST['w'],$_POST['h'],$_POST['x1'],$_POST['y1'],$k,$savename);  
            if(file_exists($savename)){
                $face[$k]=$savename;
            }
        }
        unset($_SESSION['face']);
        $url = U('uc/completeface');
        echo "<script>location='".$url."'</script>";
        //header("location:".U('uc/completeface'));
        //$this->success('上传成功',U('uc/account_face')); 
    }
    //剪裁修改完成
    function completeface(){
        $face = getUserFace($_COOKIE['user']['id'],'all');
        $this->assign('face',$face);
        $this->display();
    }
    //检测用户名    
    public function check_user(){
    	
    	$name=setFormString(trim($_GET['name']));    	
    	$user_rel=$this->user_mod->where("name='{$name}'")->find();
    	if($user_rel){
    		exit('1');  //用户已经存在
    	}
    	else{
    		exit('0');   //可以注册
    	}   	
    	
    }
    //检测邮箱    
    public function check_email(){    	
    	$email=setFormString(trim($_GET['email']));    	
    	$email_rel=$this->user_mod->where("email='{$email}'")->find();
    	if($email_rel){
    		exit('1');  //邮箱已经存在
    	}
    	else{
    		exit('0');   //可以注册
    	}   	
    	
    }
    public function check_code(){     		
    	$verify=setFormString(trim($_GET['verify']));    	    	
    	if($_SESSION['verify']!=md5(trim($_GET['verify']))){
    		exit('1');  //验证码不正确
    	}
    	else{
    		exit('0');   //验证码正确
    	}   	
     }
}
