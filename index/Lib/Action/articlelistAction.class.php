<?php
class articlelistAction extends baseAction
{
	public function index()
	{
		$cid=isset($_GET['cid'])?intval($_GET['cid']):'';
	    $article_cate_mod = D('article_cate');
	    $article_mod = D('article');        
    	$result = $article_cate_mod->order('sort_order ASC')->select();    	
    	
    	$cate_info = $article_cate_mod->where("id=$cid")->order('sort_order ASC')->find(); 	
    	
    	//显示配置seo信息
    	$this->seo['seo_title'] = !empty($cate_info['seo_title']) ? $cate_info['seo_title'] : $cate_info['name'];
	    $this->seo['seo_title'] = $this->seo['seo_title'] . ' - ' . $this->setting['site_name'];
	    $this->seo['seo_keys'] = !empty($cate_info['seo_keys']) ? $cate_info['seo_keys'] : $cate_info['name'];	    
	    $this->seo['seo_desc'] = !empty($cate_info['seo_desc']) ? $cate_info['seo_desc'] : $cate_info['name'];	    
	    $this->assign('seo',$this->seo); 
    	$article_cate_list = array();
    	foreach ($result as $val) {
    	    if ($val['pid']==0) {
    	        $article_cate_list['parent'][$val['id']] = $val;
    	    } else {    	    	
    	        $article_cate_list['sub'][$val['pid']][] = $val;
    	    }
    	}
    	$where='1=1';
    	if(!empty($cid))
    	{
    		$where.=" AND cate_id={$cid}";
    	}   
    	import("ORG.Util.Page");
		$count = $article_mod->where($where)->count();		
		$p = new Page($count,$this->setting['article_count']);
		$article_list = $article_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('add_time DESC,ordid ASC')->select();
    	$page = $p->show();    	
    	$this->assign('article_cate_list',$article_cate_list);
    	$this->assign('page',$page);    	
		$this->assign('article_list',$article_list);	
	    $this->display();
	}
}