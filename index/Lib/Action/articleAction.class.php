<?php
class articleAction extends baseAction
{
	public function index()
	{
	    $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error("404");
	    $article_mod = D('article');
	    $article_cate_mod = D('article_cate');
	    $art_info = $article_mod->find($id);
	    //获取相关文章
	    $art_list = $article_mod->where('cate_id='.$art_info['cate_id'])->limit('0,10')->select();

	    $this->assign('art_info', $art_info);
	    $this->assign('art_list', $art_list);

	    $this->seo['seo_title'] = !empty($art_info['seo_title']) ? $art_info['seo_title'] : $art_info['title'];
	    $this->seo['seo_title'] = $this->seo['seo_title'] . ' - ' . $this->setting['site_name'];
	    $this->seo['seo_keys'] = !empty($art_info['seo_keys']) ? $art_info['seo_keys'] : $art_info['title'];
	    $art_info['seo_desc'] && $this->seo['seo_desc'] = $art_info['seo_desc'];
	    $this->assign('seo',$this->seo);
	    
	    //获取分类
	    $result = $article_cate_mod->order('sort_order ASC')->select();
    	$article_cate_list = array();
    	foreach ($result as $val) {
    	    if ($val['pid']==0) {
    	        $article_cate_list['parent'][$val['id']] = $val;
    	    } else {    	    	
    	        $article_cate_list['sub'][$val['pid']][] = $val;
    	    }
    	}
    	$this->assign('article_cate_list',$article_cate_list);	

    	//获取当前文章的上一个文章
    	$pre_artile=$article_mod->field('id,title')->where("id<{$id}")->find();
    	$next_artile=$article_mod->field('id,title')->where("id>{$id}")->find(); 
    	$this->assign('pre_artile',$pre_artile);
    	$this->assign('next_artile',$next_artile);	
    	
    	if(S('content_item'.$id)){
            $art_list = S('content_item'.$id);
        }else{
        	$item_mod = $this->items_mod;
			$count=$item_mod->count();			 
			$rand=rand(0,($count-12));
			$art_list = $item_mod->limit("$rand,8")->select();		
			S('content_item'.$id,$art_list,'3600');  //缓存小时
		}	
		$this->assign('items_list',$art_list);
    	
    	//获取当前文章的下一个文章
	    $this->display();
	}

}