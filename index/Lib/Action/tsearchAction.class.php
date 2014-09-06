<?php
class tsearchAction extends baseAction {
    public function index(){     	
    	$keywords = isset($_REQUEST['keywords']) && trim($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) :''; 
    	$keywords=strip_tags(urldecode($keywords));
    	
    	//暂时跳转的页面    	
    	if(strpos($keywords, 'http://')!== false){
    		header('Location: index.php?a=index&m=rebate&url='.$keywords);
    		exit;
    	} 
    	$appkey=$this->setting['taobao_appkey'];
    	$keywords=urlencode($keywords);
    	$pid=$this->setting['taobao_pid'];
    	$apmc=$appkey;
    	$spmd=0;
   		if($this->setting['is_cashback']==1){   //如果开启返现
			if(isset($_COOKIE['user']['id'])){  //如果用户登录
				$outer_code=$_COOKIE['user']['id'];
			}
		}
		$tao_search_pid=$this->setting['taobao_search_pid'];
		$url='http://s8.taobao.com/search?pid='.$tao_search_pid.'&commend=all&sort=sale-desc&q='.$keywords.'&unid='.$outer_code.'&taoke_type=1&spm=2014.'.$appkey.'.'.$apmc.'.'.$spmd.'';		
    	redirect($url);	
    	exit;
    }
}
?>