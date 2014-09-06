<?php
class searchAction extends baseAction {
    public function index() {        
        $keywords = isset($_REQUEST['keywords']) && trim($_REQUEST['keywords']) ? trim(setFormString($_REQUEST['keywords'])) :'';
        //暂时跳转的页面    	
    	if(strpos($keywords, 'http://')!== false){
    		header('Location: index.php?a=index&m=rebate&url='.$keywords);
    		exit;
    	}    	     
        $sortby = isset($_REQUEST['sortby']) && trim($_REQUEST['sortby']) ? trim(setFormString($_REQUEST['sortby'])) : '';
        $type=empty($_REQUEST['type'])?'guang':setFormString($_REQUEST['type']);
        $this->assign('type',$type);
        
        $items_mod = D('items');
        
        import("ORG.Util.Page");
        //$sql_where = "title LIKE '%" . $keywords . "%'";
        $sql_where='1=1 AND status=1';
        $sql_where.= !empty($_REQUEST['keywords']) ? " AND title LIKE '%" . trim($_REQUEST['keywords']) . "%'" :'';
        $sql_where.=' AND cid!=0';
    	 if(isset($_GET['cid'])){
        	$sql_where='cid=0';
        	$this->assign('cid', 'no'); 
        }
        switch ($sortby) {
            case 'likes' :
                $sql_order = "likes DESC";
                break;
            case 'time' :
                $sql_order = "add_time DESC";
                break;
            case 'sort_order':
            	$sql_order = "sort_order DESC";
            	break;
            default :
                $sql_order = "add_time DESC";
                break;
        }  
        //为空的话表示不是搜索框过来的
        if(empty($keywords)){
        	$this->nav_seo('search');
        }        
        //seo设置

        	$this->nav_seo('search','nav',1);
     
        $this->assign('search_keywords',explode(',',$this->setting['search_words']));
        $this->assign('keywords', $keywords);
        $this->assign('sortby', $sortby);        
        $count = $items_mod->where($sql_where)->count();
        $this->assign('items_total', $count);
        $this->waterfall($count, $sql_where,$sql_order);
    }
    public function nocid(){
   	  	$keywords = isset($_REQUEST['keywords']) && trim($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) :'';
        $sortby = isset($_REQUEST['sortby']) && trim($_REQUEST['sortby']) ? trim($_REQUEST['sortby']) : '';
        $type=empty($_REQUEST['type'])?'guang':$_REQUEST['type'];
        $this->assign('type',$type);
        
        $items_mod = D('items');
        
        import("ORG.Util.Page");
        //$sql_where = "title LIKE '%" . $keywords . "%'";
        $sql_where='1=1 AND status=1';
        $sql_where.= !empty($_REQUEST['keywords']) ? " AND title LIKE '%" . trim($_REQUEST['keywords']) . "%'" :'';
        $sql_where.=' AND cid!=0';
    	 if(isset($_GET['cid'])){
        	$sql_where='cid=0';
        	$this->assign('cid', 'no'); 
        }
        switch ($sortby) {
            case 'likes' :
                $sql_order = "likes DESC";
                break;
            case 'time' :
                $sql_order = "add_time DESC";
                break;
            case 'sort_order':
            	$sql_order = "sort_order DESC";
            	break;
            default :
                $sql_order = "add_time DESC";
                break;
        }  
        //为空的话表示不是搜索框过来的
        if(empty($keywords)){
        	$this->nav_seo('search');
        }        
        //seo设置
        $this->assign('search_keywords',explode(',',$this->setting['search_words']));
        $this->assign('keywords', $keywords);
        $this->assign('sortby', $sortby);        
        $count = $items_mod->where($sql_where)->count();
        $this->assign('items_total', $count);
        $this->nocidwaterfall($count, $sql_where,$sql_order);
    }
    //没有cid的时候调用的瀑布流
	function nocidwaterfall($count,$where,$order=""){
		import("ORG.Util.Page");
		$items_mod=D("items");
		
		$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
		$sp = !empty($_GET['sp']) ? intval($_GET['sp']) : 1;
		$sp >$this->setting['waterfall_sp'] && exit;
		
		$list_rows =$this->setting['waterfall_sp']* $this->setting['waterfall_items_num'];
		$s_list_rows =$this->setting['waterfall_items_num'];
		$show_sp = 0;
		
		$count > $s_list_rows && $show_sp = 1;
		$pager = new Page($count, $list_rows);
		
		$first_row = $pager->firstRow + $s_list_rows * ($sp - 1);
		$items_list = $items_mod->relation(true)->where($where)
			->limit($first_row . ',' . $s_list_rows)->order($order)
			->select();
            
		//print_r($items_list);
		//获取评论数
		foreach ($items_list as $key=>$val){
			$items_list[$key]['comments_num']=$this->items_comments_mod
				->where('items_id='.$val['id'].' and status=1')->count();
			//获取最新的三条评论
			$items_list[$key]['three_comments']=$this->user_comments_mod->where('pid='.$val['id'].' and status=1')->order("add_time DESC")->limit("0,3")->relation(true)->select();	
		    //获取三条喜欢此宝贝的人
            $like['items_id']=$val['id'];
            $items_list[$key]['likelist'] = $this->like_list_mod->where($like)->order('id desc')->limit(3)->select();
            $items_list[$key]['count'] = $this->like_list_mod->where($like)->count();
        }
		
		//print_r($items_list);
		//获取最新的三条评论		
		
		
		$this->assign('page', $pager->show_1());
		$this->assign('p', $p);
		$this->assign('show_sp', $show_sp);
		$this->assign('sp', $sp);
		$this->assign('items_list', $items_list);
		if($this->isAjax()&&$sp>1){
			header('Content-Type:text/html; charset=utf-8');
			echo($this->fetch('public:nocid_goods_list'));
		}else{
			$this->display();
		}		
	}
    
}