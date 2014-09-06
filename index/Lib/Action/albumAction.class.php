<?php
class albumAction extends baseAction{
	function index(){
		if($this->setting['display_b2c_ad']==1){
			//动态广告系统
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$adv_data = $miao_api->AdsGet('', '468x60');
			$ad_rel=$adv_data['ads']['ad'];
			$ad_rel=getRandArray($ad_rel);	
	     		if(count($ad_rel)>0){
				$this->assign('ad_rel',$ad_rel);
			}
		}
		$album_cate_mod=D('album_cate');
		$cid = isset($_GET['cid']) && intval($_GET['cid']) ? intval($_GET['cid']) :0;
		$album_cate=$album_cate_mod->field('id,title')->where("status=1")->order("sort_order ASC")->select();

		$this->assign('album_cate',$album_cate);
		if(is_null($_REQUEST['cid'])){
			$where='recommend=1';
			$this->assign('cid',-1);
		}else{
			$where="cid=$cid";
			$this->assign('cid',$cid);
		}		
		//seo信息
		$this->album_seo($_GET['cid'],null,null,1);
		
		$where.=" and status=1";
		
        $this->get_album_list($where,6);
        
	}
	function details(){
		if(empty($_REQUEST['id'])||empty($_REQUEST['uid']) ||!is_numeric($_REQUEST['id']))
		header('location:'.$this->site_root);
		$album_items_mod=D('album_items');
		$album_mod=D('album');
		$user_mod=D('user');
		$id= intval($_REQUEST['id']);
		$count = $album_items_mod->where('pid='.$id)->count();
		$res=$album_items_mod->field('items_id')->where('pid='.$id)->select();
        	
		$ids=array();
		foreach($res as $val){
			$ids[]=$val['items_id'];
		}
		$where='id in('.implode(",",$ids).')';

		$user_res=$user_mod->field('id,name,status')->where('id='.$this->uid)->find();
        
		$info['album_who']=$user_res["name"]."的专辑";

		$res=$album_mod->where('id='.$id)->find();
		$info['album_title']=$res["title"];
		$info['album_id']=$id;
		$info['remark']=$res['remark'];
		
		$info=array_merge($info,$user_res);
		$this->assign('info',$info);
        $this->album_seo(null,$_GET['id'],$_GET['uid']);
		$this->waterfall($count,$where);
	}
	function album_items_add_dialog(){
		if(!$this->check_login()){
			$this->ajaxReturn("not_login");
		}
		$album_mod=D('album');
		$res=$album_mod->where('uid='.$_COOKIE['user']['id'])->select();
		$this->assign('list',$res);
        //专辑分类
        $cate = M('album_cate')->where("status=1")->order('sort_order')->select();
        $this->assign("cate",$cate);
		$this->display();
	}
	function items(){
		$act=$_REQUEST['act'];
		$album_items_mod=D('album_items');
		$album_mod=D('album');
		$user_history_mod=D('user_history');
		$items_mod=D('items');
        
		if($act=='add'){
			$data=$album_items_mod->create();
			$count=$album_items_mod
			->where("items_id=".$data['items_id']." and pid=".$data['pid'])
			->count();
			if($count>0){
				$this->ajaxReturn("yet_exist");
			}
			$data['add_time']=time();
			if(intval($data['pid'])==0){
				$data['pid']=$album_mod->add(array(
						'uid'=>$_COOKIE['user']['id'],
						'add_time'=>time(),
				));
			}
			$album_items_mod->add($data);
			$items_id=$data['items_id'];
			$res=$items_mod->where('id='.$items_id)->find();
			$data=array();
			$data['uid']=$_COOKIE['user']['id'];
			$data['add_time']=time();
			$data['info']="添加了一个宝贝到专辑中~<br/>"
			."<a href='".u("item/index",array('id'=>$items_id))."' target='_blank'>"
			."<img src='".$res['img']."'/></a>";
			$user_history_mod->add($data);

			$this->ajaxReturn('success');
		}else if($act=='del'){
			//是否是自己的专辑
			$count=$album_mod->where('id='.intval($_REQUEST['pid'])
					.' and uid='.$_COOKIE['user']['id'])
					->count();
			if($count==0)return;
			$res=$album_items_mod->where('items_id='.intval($_REQUEST['id']))
			->delete();

			$this->ajaxReturn($res);
		}
	}
    //推荐专辑
    function albumRecommend(){
        if(!$this->check_login()){
			$this->ajaxReturn("not_login");
		}
        $album_recommend = M('album_recommend');
        $album = M('album');
        $map['uid'] = intval($_GET['uid']);
        $map['album_id'] = intval($_GET['album_id']);
        $info = $album_recommend->where($map)->count();
        if($info > 0){
            $this->ajaxReturn("0");
        }else{
            $album_recommend->add($map);
            $album->where("id=".$map['album_id'])->setInc("recommend_count",1);
            $this->ajaxReturn("1");
        }
    }
}