<?php
class cateAction extends baseAction {
	public function index() {	
		if($this->setting['display_b2c_ad']==1){
			//动态广告系统
			$miao_api = $this->miao_client();   //获取59秒api设置信息		
			$adv_data = $miao_api->AdsGet('', '468x60');
			if(count($adv_data)>0){
				$ad_rel=$adv_data['ads']['ad'];
				$ad_rel=getRandArray($ad_rel);
				if(count($ad_rel)>0){
					$this->assign('ad_rel',$ad_rel);
				}	
			}
		}
	
		$cid = isset($_GET['cid']) && intval($_GET['cid']) ? intval($_GET['cid']) :0;
		$items_mod = D('items');
		$items_cate_mod = D('items_cate');
		import("ORG.Util.Page");
		$sql_where = "1=1 AND status=1";

		$cate_res=$this->items_cate_mod->field('id,pid')->where("id=$cid")->find();

		$cate_res['level']=0;
		if(intval($cate_res['pid'])!=0){
			$cate_res=$this->items_cate_mod->field('id,pid')->where("id=".$cate_res['pid'])->find();   //父类
			$cate_res['level']=1;
			if(intval($cate_res['pid'])!=0){
				$cate_res=$this->items_cate_mod->field('id,pid')->where("id=".$cate_res['pid'])->find();
				$cate_res['level']=2;
			}
		}
		//分类列表		
		$res=get_items_cate_list($cate_res['id'],$cate_res['level'],0,'collect_miao');	
		$this->assign('cate_list',$res['list']);
		S("cate_list{$cid}",$res['list']);
		//print_r($res['list']);
		
		if($res['sort_list'][$cid]['level']>=2){			
			$sql_where.= " AND cid IN (" . $cid . ")";		
		}else{
			foreach($res['sort_list'] as $key=>$val){
				$ids[]=$val['id'];
			}
			
			$sql_where.= " AND cid IN (" . implode(',', $ids) . ")";     //获取商品
		}		
		if ($cid) {
			$cate_info = $items_cate_mod->field('pid,name,seo_title,seo_keys,seo_desc')->where('id=' . $cid)->find();
			if ('0' == $cate_info['pid']) {
				$pcid = $cid;
				$this->assign('pcate_info', $cate_info);
			} else {
				//暂时未发现在哪使用$scate = $items_cate_mod->where('pid=' . $cate_info['pid'])->select();
				$pcid = $cate_info['pid'];
				$pcate_info = $items_cate_mod->field('pid,name')->where('id=' . $cate_info['pid'])->find();
				$this->assign('pcate_info', $pcate_info);
				$this->assign('cate_info', $cate_info);
			}
			//暂时未发现在哪使用$this->assign('scate', $scate);
			$this->seo['seo_title'] = !empty($cate_info['seo_title']) ? $cate_info['seo_title'] : $cate_info['name'];
			$this->seo['seo_title'] = $this->seo['seo_title'] . ' - ' . $this->setting['site_name'];
			$this->seo['seo_keys'] = !empty($cate_info['seo_keys']) ? $cate_info['seo_keys'] : $cate_info['name'];
			!empty($cate_info['seo_desc']) && $this->seo['seo_desc'] = $cate_info['seo_desc'];
		}
        
		$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
		$sp = !empty($_GET['sp']) ? intval($_GET['sp']) : 1;
		$sp >$this->setting['waterfall_sp'] && exit;

		$list_rows =$this->setting['waterfall_sp']* $this->setting['waterfall_items_num'];

		$s_list_rows =$this->setting['waterfall_items_num'];		

		$show_sp = 0;
		$count = $items_mod->where($sql_where)->count();
		$this->assign('count', $count);

		$count > $s_list_rows && $show_sp = 1;
		$pager = new Page($count, $list_rows);
		$page = $pager->show_1();

		$first_row = $pager->firstRow + $s_list_rows * ($sp - 1);		
		$sid=rand(0,2);	
		if($sid==1){
			$order='sort_order ASC,sid DESC,id DESC';
		}else{		
			$order='sort_order ASC,sid ASC,id DESC';			
		}
		$items_list = $items_mod->relation(true)->where($sql_where)
			->limit($first_row . ',' . $s_list_rows)
			->order($order)->select(); 
		foreach ($items_list as $key=>$val){		
			$items_list[$key]['three_comments']=$this->user_comments_mod->where('pid='.$val['id'].' and status=1')->order("add_time DESC")->limit("0,3")->relation(true)->select();
		    //获取三条喜欢此宝贝的人
            $like['items_id']=$val['id'];
            $items_list[$key]['likelist'] = $this->like_list_mod->where($like)->order('id desc')->limit(3)->select();
            $items_list[$key]['count'] = $this->like_list_mod->where($like)->count();
        }
		$this->assign('page', $page);
		$this->assign('p', $p);
		$this->assign('show_sp', $show_sp);
		$this->assign('sp', $sp);
		$this->assign('pcid', $pcid);		
		$select_pid=$pcid;  //设置选择状态
		
		//print_r($items_list);
		
		//获取最新推荐商品		
		$this->assign('cid', $cid);
		$this->assign('items_list', $items_list);

		if($pcid){
			$cate_info = $items_cate_mod->field('id,pid')->where('id=' . $pcid)->find();
			if ('0' == $cate_info['pid']) {
				$pid_rel=$items_cate_mod->field('id')->where('pid=' . $pcid)->select();	
				foreach ($pid_rel as $val){
					$cids[]=$val['id'];
				}
				$where = "pid IN (" . implode(',', $cids) . ") AND recommend=1 AND status=1";     //获取商品
				
				$recommend_cate=$items_cate_mod->field('id,color,name')->where($where)->select();		
			}
			else {
				//如果不是0 则获取他的pid
				$first_id_rel=$items_cate_mod->field('pid')->where('id=' . $pcid)->find(); //获取一级分类的pid				
				$pid_rel=$items_cate_mod->field('id')->where('pid=' . $first_id_rel['pid'])->select();			
				$select_pid=$first_id_rel['pid'];  //设置选择状态
				foreach ($pid_rel as $val){
					$cids[]=$val['id'];
				}
				$sql_where = "pid IN (" . implode(',', $cids) . ") AND recommend=1 AND status=1";     //获取商品
				
				$recommend_cate=$items_cate_mod->field('id,color,name')->where($sql_where)->select();
			}
		}		
		$this->assign('recommend_cate',$recommend_cate);
		$this->assign('select_pid',$select_pid);
		
        $this->nav_seo('cate','items_cate',$_GET['cid']);

		if($this->isAjax()&&$sp>1){   //判断是否是ajax请求
			header('Content-Type:text/html; charset=utf-8');
			echo($this->fetch('public:goods_list'));
		}else{
			$this->display();
		}
	}
	public function tag() {
		$tag_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : exit;
		$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
		$sp = !empty($_GET['sp']) ? intval($_GET['sp']) : 1;
		$sp > 5 && exit;
		$list_rows = 200;
		$s_list_rows = 12;
		$show_sp = 0;
        
		$items_mod = D('items');
		$items_cate_mod = D('items_cate');
		$items_tags_mod = D('items_tags');
		import("ORG.Util.Page");
		$sql_where = '1=1';
		if ($tag_id) {
			$tag = $items_tags_mod->field('id,name')->where("id='" . $tag_id . "'")->find();
			$sql_where = 'iti.tag_id=' . $tag['id'].' AND '.C('DB_PREFIX').'items.status=1';
			$this->assign('tag', $tag);
            
			$this->seo['seo_title'] = !empty($tag['seo_title']) ? $tag['seo_title'] : $tag['name'];
			$this->seo['seo_title'] = $this->seo['seo_title'] . ' - ' . $this->setting['site_name'];
			$this->seo['seo_keys'] = !empty($tag['seo_keys']) ? $tag['seo_keys'] : $tag['name'];
			!empty($tag['seo_desc']) && $this->seo['seo_desc'] = $tag['seo_desc'];
		}

		//先计算大的分页
		$count = $items_mod->join("LEFT JOIN " . C('DB_PREFIX') . "items_tags_item as iti ON iti.item_id=" . C('DB_PREFIX') . "items.id")->where($sql_where)->count();
		$count > $s_list_rows && $show_sp = 1;
		$pager = new Page($count, $list_rows);
		$page = $pager->show_1();
		$first_row = $pager->firstRow + $s_list_rows * ($sp - 1);
		$items_list = $items_mod->field('id,title,sid,img,price,likes,comments,seller_name,cash_back_rate')->relation('items_site')->join("LEFT JOIN " . C('DB_PREFIX') . "items_tags_item as iti ON iti.item_id=" . C('DB_PREFIX') . "items.id")->where($sql_where)->limit($first_row . ',' . $s_list_rows)->order('add_time DESC')->select();	
	    foreach ($items_list as $key=>$val){			
			//获取最新的三条评论
			$items_list[$key]['three_comments']=$this->user_comments_mod->where('pid='.$val['id'].' and status=1')->order("add_time DESC")->limit("0,3")->relation(true)->select();	
		    //获取三条喜欢此宝贝的人
            $like['items_id']=$val['id'];
            $items_list[$key]['likelist'] = $this->like_list_mod->where($like)->order('id desc')->limit(3)->select();
            $items_list[$key]['count'] = $this->like_list_mod->where($like)->count();  
        }
		$this->assign('page', $page);
		$this->assign('p', $p);
		$this->assign('show_sp', $show_sp);
		$this->assign('sp', $sp);
		$this->assign('items_list', $items_list);
        
		//大分类
		$pcate = $items_cate_mod->where('pid=0')->select();
		$this->assign('pcate', $pcate);

		$this->assign('seo', $this->seo);
		$this->display();
	}

	public function like() {
		$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : '';
		$data = 0;
		if ($id) {
			$items_cate =M('items_cate');
			$data = $items_cate->where('id=' . $id)->setInc('item_likes');
		}
		$this->ajaxReturn($data);
	}
    function comment(){
        if(!$this->check_login()){
			$this->ajaxReturn("not_login");
		}
        $this->display();
    }
    function doComment(){
        $this->check_login();
        //解析URL
        $parse_url = parse_url($_POST['type']);
        $parse_str = parse_str($parse_url['query'],$option);
        $type = $option['m'].','.$option['a'];
        $result = trim($type,','); 

        //组装数据
        $replace = str_replace("\n"," ",$_POST['info']);
        $data['info']=htmlspecialchars(ReplaceKeywords(trim(strip_tags($replace))));  //评论替换
        $data['pid']=$_POST['pid'];
        //$data['type']=$result;
        $data['type']="item,index";
        $data['uid'] = $_COOKIE['user']['id'];
        $data['uname'] = $_COOKIE['user']['name'];
        $user_rel=$this->user_mod->field('id,name')->where("id='{$data['uid']}'")->find();
        $user_rel['face']=getUserFace($data['uid']);
        $data['status'] = 1;
        $data['add_time'] = time();
        if(M('UserComments')->add($data)){       		
			$arr=array(
						'id'=>$data['pid'],
						'comments'=>$this->user_comments_mod
						->where('pid='.$data['pid'].' and type="item,index"')
						->count(),
				);
			$this->items_mod->save($arr);			
            $this->ajaxReturn($user_rel);
        }else{
            echo 0;
        }
    }
}