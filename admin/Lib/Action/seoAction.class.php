<?php

class seoAction extends baseAction
{
    //显示列表
    public function index(){
        $this->showindex("网站首页");
    }
    public function seller()
	{
		$this->showindex("返现商家");
	}
    public function search()
	{
		$this->showindex("逛宝贝");
	}
    public function album()
	{
		$this->showindex("专辑");
	}
    public function promo()
	{
		$this->showindex("促销活动页");
	}
    public function exchange_goods()
	{
		$this->showindex("积分商城");
	}
    public function cate()
	{
		$this->showindex("商品分类页");
	}
    public function item()
	{
		$this->showindex("商品详细页");
	}
	public function showindex($title)
	{
		$map['actionname'] = ACTION_NAME;
        $info = $this->seo_mod->where($map)->find();
        $this->assign('info',$info);
        $this->assign('title',$title);
        $this->display("index");
	}
	//修改
	public function edit()
	{
	   $this->seo_mod->create();
       $status = $_POST['id'] ? $this->seo_mod->save() : $this->seo_mod->add();
       
       if($status){
            $this->success("操作成功！");
       }else{
            $this->error("操作失败！");
       }
	}
	//伪静态设置
	function rewrite(){
		$this->assign('set',$this->setting);
		$this->display();
	}
	//设置seo信息
	function doEditSeo(){
        if(isset($_POST["site"]["url_model"])){
            $_POST["site"]["url_model"];
            $address = ROOT_PATH."/index/Conf/config.php";
            $configFile = file_get_contents($address);
            $newFile = str_replace("'URL_MODEL' => {$this->setting['url_model']}","'URL_MODEL' => {$_POST['site']['url_model']}",$configFile);
            if(!file_put_contents($address,$newFile)){
            	$this->error("保存伪静态模式错误！请查看目录是否有修改权限");
            }
            if(unlink(ROOT_PATH."/index/Runtime/~runtime.php")){
               // $success = "保存数据成功";
            }
            //保存伪静态后缀
            if(!empty($_POST['site']['html_suffix'])&&trim($_POST['site']['html_suffix'])!='/'){
	            $_br = "\r\n";
				$_tab = "\t";		
				$_profile = '<?php'.$_br;
				$_profile .= $_tab."//seo后缀配置文件".$_br;
				
				$_profile .= $_tab."return array(
									'URL_HTML_SUFFIX'=>'{$_POST['site']['html_suffix']}'
				);".$_br;				
						
				$_profile .= $_br;
				$_profile .= '?>'.$_br;		
				if (!file_put_contents(ROOT_PATH."/index/Conf/html_suffix.php", $_profile)) {
					 $this->error("保存后缀配置文件失败，请重试！");
				}			
            }else{
            	$_br = "\r\n";
				$_tab = "\t";		
				$_profile = '<?php'.$_br;
				$_profile .= $_tab."//seo后缀配置文件".$_br;
				
				$_profile .= $_tab."return array(
									
				);".$_br;				
						
				$_profile .= $_br;
				$_profile .= '?>'.$_br;		
				if (!file_put_contents(ROOT_PATH."/index/Conf/html_suffix.php", $_profile)) {
					 $this->error("保存后缀配置文件失败，请重试！");
				}	
            }
             
            $map["name"] = 'url_model';
            M('setting')->where($map)->setField('data',$_POST['site']['url_model']);
            $map["name"] = 'html_suffix';
            M('setting')->where($map)->setField('data',trim($_POST['site']['html_suffix'])); 
            $this->success("保存数据成功！");
            
        }
	}
	 //显示伪静态信息
 	function showRewriteRules(){  	
    	$where=array('name'=>'html_suffix');
    	$setting_mod=D('setting');
    	$html_suffix_rel=$setting_mod->where($where)->find();
    	$this->assign('suffix',$html_suffix_rel['data']);  //  

    	$where=array('name'=>'url_model');    	
    	$url_model_rel=$setting_mod->where($where)->find();
    	$this->assign('url_model',$url_model_rel['data']);  //  
    	$this->display();
    }
    function sitemap(){
        if(!empty($_POST['dosubmit'])){
            //促销活动
            $miao_api = $this->miao_client();   //获取59秒api设置信息		
        	$promo_parent=$miao_api->ListPromoCats();	
            foreach($promo_parent["promo_cats"]["promo_cat"] as $v){
                $class[2] .= "<a href='/index.php?a=cate&m=promo&cid=".$v['cid']."'>{$v['name']}</a>";
            }		
            //导航
            $nav_cate = M("nav")->where("is_show=1")->order("sort_order")->select();
            //逛宝贝热门搜索
            $setting_cate = M('setting')->where("name='search_words'")->select();
            $setting = explode(",",$setting_cate[0]['data']);
            foreach($setting as $v){
                $class[0] .= "<a href='/index.php?a=index&m=search&keywords=".$v."'>{$v}</a>";
            }
            
            //专辑分类
            $album_cate = M("album_cate")->where("status=1")->order("sort_order")->select();
            foreach($album_cate as $v){
                $class[1] .= "<a href='/index.php?m=album&a=index&cid=".$v['id']."'>{$v['title']}</a>";
            }
            //返现商家
            $seller_cate = M("seller_cate")->where("status=1")->order("sort")->select();
            foreach($seller_cate as $v){
                $class[3] .="<a href='/index.php?m=seller&a=index&id=".$v['id']."'>{$v['name']}</a>";
            }
            //读取标签
            $getlabel = file_get_contents("./data/label.txt");
            $label = explode(",",$getlabel);
            //所有标签
            $items_tags = M("items_tags")->where("status=1")->select();
            foreach($items_tags as $v){
                if(!preg_match("/\w|【|】|、|（|）|＼/",$v['name'])){
                    if(in_array($v['name'],$label)){
                        $tags .="<a href='/index.php?m=cate&a=tag&id=".$v['id']."'>{$v['name']}</a>";
                    }
                }
            }           
            $html = "<div id='sitemap'><div><h3>首页</h3></div>";
            foreach($nav_cate as $k=>$v){
                if($v['items_cate_id'] > 0){
                    $class[$k] = $this->get_itmes_cate($v['items_cate_id']);
                }
                $html .="<div><h3>".$v['name']."</h3>".$class[$k]."</div>";
                
            }
            $html .= "<div><h3>热门标签</h3>".$tags."</div></div>";
            //$html .= "<div><h3>所有标签</h3>".$tags."</div></div>";
            
            /*dump($html);exit;
            $html = "<div id='sitemap'><div><h3>首页</h3></div>";
            $html .= "<div><h3>逛宝贝</h3></div>";
            $html .= "<div><h3>专辑</h3>".$album."</div>";
            $html .= "<div><h3>促销活动</h3></div>";
            $html .= "<div><h3>返现商家</h3>".$seller."</div>";
            $html .= "<div><h3>积分商城</h3></div>";
            $html .= "<div><h3>所有标签</h3>".$tags."</div></div>";
            */
            //$info = M('article')->where("id=2")->setField("info","{$html}");
            $content = file_get_contents("sitemap.tpl");
            $newhtml = str_replace('{content}',$html,$content);
            if(file_put_contents("sitemap.html",$newhtml)){
                $this->success("提交成功！");   
            }else{
                $this->error("提交失败！");
            }
        }
        $this->display();
    }
    function get_itmes_cate($pid){
        $model = M('items_cate');
        $map["status"] = 1;
        $map["pid"] = $pid;
        $list = $model->field('id')->where($map)->select();
        foreach($list as $v){
            $id .= $v['id'].",";
        }
        $pid = trim($id,",");
        $maps["status"] = 1;
        $maps["pid"] = array('in',$pid);
        $lists = $model->field('id,name')->where($maps)->select();
        foreach($lists as $v){
            $str .= "<a href='/index.php?a=index&m=cate&cid=".$v['id']."'>{$v['name']}</a>"; 
        }
        return $str;
    }
    function test(){
        $items_tags = M("items_tags")->where("status=1")->select();
        foreach($items_tags as $v){
            if(!preg_match("/\w|【|】|、|（|）|＼/",$v['name'])){
            echo $v['name']."&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;http://www.qianbao.cn/index.php?m=cate&a=tag&id=".$v['id']."</br>";
            }
        }
    }
}
?>