<?php
class baseAction extends Action {
	public $seo = array();
	public $setting=array();
	public $like_list_mod;
	public $user_mod;
	public $user_info;
	public $items_mod;
	public $items_cate_mod;
	public $items_comments_mod;
	public $user_openid_mod;
	public $setting_mod;
	public $site_root;
	public $user_follow_mod;
	public $user_comments_mod;
	protected $config;
	public $uid;
	public $user;//自己
	public $u;//别人	
	public $nav_mod;
    protected $seo_mod;
	protected $article_mod;
	public function _initialize() {
		if (isset($_GET)) $_GET = setHtmlspecialchars(setFormString($_GET));
		//if (isset($_POST)) $_POST = setHtmlspecialchars(setFormString($_POST));
		//print_r($_COOKIE['user']);		
		include ROOT_PATH.'/includes/lib_common.php';		
		$this->setting_mod=D("setting");
		$this->like_list_mod=D('like_list');
		$this->user_mod=D('user_user');
		$this->user_info=D('user_info');
		$this->items_mod=D('items');
		$this->items_cate_mod=D('items_cate');
		$this->items_comments_mod=D('items_comments');
		$this->user_openid_mod=D('user_openid');
		$this->user_follow_mod=D('user_follow');
		$this->user_comments_mod=D('user_comments');
		$this->seo_mod = M('seo');
        $this->article_mod = M('article');
		$this->config=array('debug'=>0);
		$this->site_root="http://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']==80?'':':'.$_SERVER['SERVER_PORT']).__ROOT__."/";
		define("SITE_ROOT",$this->site_root);	
		//系统基本设置信息		
		if(S('basic_setting')){
            $set = S('basic_setting');
        }else{
        	//网站配置
			$setting_mod = M('setting');
			$setting = $setting_mod->select();
			foreach ($setting as $val) {
				$set[$val['name']] = $val['data'];
			} 	           
            S('basic_setting',$set,'3600');
		}
		$this->setting = $set;
		//判断是否是蜘蛛访问，屏蔽蜘蛛
		$ban_spider=$this->setting['ban_spider'];
		if(!empty($ban_spider)){			
			banspider($ban_spider);
		}	
		//判断是否允许ip访问
		$banip=getBanip();
		if($banip){			
			foreach ($banip as $key=>$value){
				banip($value[0], $value[1]);
			}
		}
        //判断网站是否关闭
        if(!$this->setting["site_status"]){
            $this->siteStatus($this->setting["closed_reason"]);
        }        
		$this->assign('site_domain', $this->setting['site_domain']);
		$this->assign('site_name', $this->setting['site_name']);
		$this->assign('site_logo', $this->setting['site_logo']);		
		$this->assign('site_icp', $this->setting['site_icp']);
		$this->assign('site_share', $this->setting['site_share']);
		$this->assign('statistics_code', $this->setting['statistics_code']);
		$this->assign('site_name', $this->setting['site_name']);
		//是否显示b2c广告
		$this->assign('display_b2c_ad', $this->setting['display_b2c_ad']);
		//返现形式
		$this->assign('cashback_type', $this->setting['cashback_type']);
		//tb_fanxian_name 淘宝返现名称
		$this->assign('tb_fanxian_name', $this->setting['tb_fanxian_name']);
		//tb_fanxian_unit 淘宝返现单位
		$this->assign('tb_fanxian_unit', $this->setting['tb_fanxian_unit']);
		//tb_fanxian_bili  淘宝返现比例
		$this->assign('tb_fanxian_bili', $this->setting['tb_fanxian_bili']);
		//cashback_rate  返现率
		$this->assign('cashback_rate', $this->setting['cashback_rate']);
		
		$this->assign('taobao_search_pid',$this->setting['taobao_search_pid']);
		
		
        $default_kw=explode(",",rtrim($this->setting['default_kw']));
        $this->assign('default_kw', $default_kw[mt_rand(0,count($default_kw)-1)]);
		//是否返现
		$this->assign('is_cashback', $this->setting['is_cashback']);		
		//url模式
		//C('URL_MODEL',$this->setting['url_model']);
		//SEO
		$this->seo['seo_title'] = $this->setting['site_title'];
		$this->seo['seo_keys'] = $this->setting['site_keyword'];
		$this->seo['seo_desc'] = $this->setting['site_description'];
        $this->seo['goods_save_images'] = !empty($this->setting['goods_save_images'])?1:0;
        $this->seo['sina_app_key'] = (!empty($this->setting['sina_app_key']) && !empty($this->setting['sina_app_Secret']))?1:0;;
        $this->seo['qq_app_key'] = (!empty($this->setting['qq_app_key']) && !empty($this->setting['qq_app_Secret']))?1:0;;
		$this->assign('seo', $this->seo);	
        $this->assign('fabout',$this->about());
		//关注我们
		$follow_us = array(
            'weibo_url' => $this->setting['weibo_url'],
            'qqweibo_url' => $this->setting['qqweibo_url'],
            'renren_url' => $this->setting['renren_url'],
            '163_url' => $this->setting['163_url'],
            'qqzone_url' => $this->setting['qqzone_url'],
            'douban_url' => $this->setting['douban_url'],
		);
		$this->assign('follow_us', $follow_us);
		//头部导航
		if(S('basic_nav')){
            $nav_list = S('basic_nav');
        }else{        	
			$this->nav_mod = M('nav');		
			$nav_list['main'] = $this->nav_mod->order('sort_order ASC')->where('is_show=1 AND type=1')->select();				           
            S('basic_nav',$nav_list,'3600');
		}
		$this->assign('nav_list', $nav_list);
		//友情链接
		if(S('basic_flink')){
            $flink_list = S('basic_flink');
        }else{        	
			$flink_mod = M('flink');
			$flink_list = $flink_mod->where("status=1 and cate_id=1")->order('ordid asc')->select();				           
            S('basic_flink',$flink_list,'3600');
		}
		$this->assign('flink_list', $flink_list);
		//合作伙伴
		if(S('basic_huoban')){
            $huoban_list = S('basic_huoban');
        }else{        	
			$flink_mod = M('flink');
			$huoban_list = $flink_mod->where("status=1 and cate_id=2")->order('ordid asc')->select();				           
            S('basic_huoban',$huoban_list,'3600');
		}
		
		$this->assign('huoban_list', $huoban_list);
		$this->uid=empty($_REQUEST['uid'])?$_COOKIE['user']['id']:intval($_REQUEST['uid']);
		$this->assign('uid',$this->uid);
		
		if($this->check_login()){
			$this->user=$this->user_mod->where('id='.$_COOKIE['user']['id'])->find();
			if(trim($this->user['img'])=="")
			{
				$this->user['img']=$this->site_root."data/user/avatar.gif";
			}
			//print_r($this->user);
            //如果用户登录，每次操作重新写入一次COOKIE
			//writeCookie($this->user['id'],$this->user['name']);
			$this->assign('user',$this->user);
		}
		//当前浏览页的用户信息
		$this->u=$this->user_mod->where('id='.$this->uid)->relation('user_info')->find();
		if(trim($this->u['img'])=="")
		{
			$this->u['img']=$this->site_root."data/user/avatar.gif";
		}
		//我是否关注了ta
		$this->u['is_follow']=$this->user_follow_mod
			->where('fans_id='.$this->uid.' and uid='.$_COOKIE['user']['id'])
			->count()>0;
		//print_r($this->u);exit;
		
		$this->assign('u',$this->u);
		if($this->uid==$_COOKIE['user']['id']){
			$this->assign('me',true);
		}		
		$this->assign('cate_list',$this->get_cate_list());
		$this->assign('module_name',MODULE_NAME);
		$this->assign('def',$this->js_init());
		$this->assign('request',$_REQUEST);
        //通用SEO
        $this->assign('siteseo',$this->siteseo());  
        //自动采集商品
        if($this->setting['goods_collect']==1){
        	date_default_timezone_set('Asia/Shanghai');
        	if(date("G",time())>=$this->setting['collect_time']){     //当当前时间大于设定的时间的时候第一个访问的将开始采集操作

        		$i=rand(0, 2);
        		if($i==1){
        			$this->auto_collect_tao();	
        		}else{
        			$this->auto_collect_b2c();	
        		}
        	}
        }
        //自动同步商家
        if($this->setting['seller_list_collect']==1){
        	$this->auto_collect_seller();
        }      
      
        
        
	}
	//自动同步商家	
	private function auto_collect_seller(){
		$seller_list_mod=D('seller_list');
        $rel=$seller_list_mod->field('update_time')->order('id desc')->find();
        if(!$rel || (date('Ymd',time())-$rel['update_time'])>$this->setting['seller_list_collect_time']){        		
        		$host=$_SERVER['SERVER_NAME'];				
	   			$port=$_SERVER['SERVER_PORT']==80?'80':':'.$_SERVER['SERVER_PORT'];	   			
	            $root=__ROOT__;    
	            $root=$root.'/';	                        	 	
		        $content='';		            
				$fp = fsockopen($host, $port, $errno, $errstr, 30);
				if (!$fp) {
						
				} else {					
					$out = "POST /index.php?m=auto_collect&a=collect_seller_list".$content." HTTP/1.1\r\n";						
					$out .= "Host:$host\r\n";
					$out .= "Content-Length: ". strlen($content) ."\r\n";
					$out .= "Connection: Close\r\n\r\n";
					$out .= $content;
					 $out .= "\r\n\r\n";
					fwrite($fp, $out);
					fclose($fp);
				}	   				
        	}
	}
	//自动同步 b2c商品
	private function auto_collect_b2c(){			
            $auto_collect_date_mod=D('auto_collect_date');
            $date=date('Ymd',time());
            $rel=$auto_collect_date_mod->where("add_date='{$date}'")->find();
            if(!$rel){ //执行程序动态采集 //异步采集   				
           		$host=$_SERVER['SERVER_NAME'];				
   				$port=$_SERVER['SERVER_PORT']==80?'80':':'.$_SERVER['SERVER_PORT'];	   			
            	$root=__ROOT__;   
       			$root=$root.'/';	  	
	            $content='';	
				$fp = fsockopen($host, $port, $errno, $errstr, 30);
				if (!$fp) {
					//echo "$errstr ($errno)<br />\n";
				} else {					
					$out = "POST /index.php?m=auto_collect&a=index".$content." HTTP/1.1\r\n";
					$out .= "Host:$host\r\n";
					$out .= "Content-Length: ". strlen($content) ."\r\n";
					$out .= "Connection: Close\r\n\r\n";
					$out .= $content;
					$out .= "\r\n\r\n";
					fwrite($fp, $out);
					fclose($fp);
					$auto_collect_date_mod->add(array('add_date'=>$date));
				}	
            }     
       
	}	
	//自动采集淘宝	
	private function auto_collect_tao(){
		 	$auto_collect_date_mod=D('auto_collect_date');
            $date=date('Ymd',time());
            $rel=$auto_collect_date_mod->where("add_date='{$date}'")->find();
            if(!$rel){ //执行程序动态采集 //异步采集   				
           		$host=$_SERVER['SERVER_NAME'];				
   				$port=$_SERVER['SERVER_PORT']==80?'80':':'.$_SERVER['SERVER_PORT'];	   			
            	$root=__ROOT__;   
       			$root=$root.'/';	  	
	            $content='';	
				$fp = fsockopen($host, $port, $errno, $errstr, 30);
				if (!$fp) {
					//echo "$errstr ($errno)<br />\n";
				} else {					
					$out = "POST /index.php?m=auto_collect&a=taoindex".$content." HTTP/1.1\r\n";
					$out .= "Host:$host\r\n";
					$out .= "Content-Length: ". strlen($content) ."\r\n";
					$out .= "Connection: Close\r\n\r\n";
					$out .= $content;
					$out .= "\r\n\r\n";
					fwrite($fp, $out);
					fclose($fp);
					$auto_collect_date_mod->add(array('add_date'=>$date));
				}	
            }     
	}	
	function get_group_items($cid) {
		$items_mod = M('items');
		$items_result = $items_mod->field('id,title,simg,bimg')
			->where("cid=" . $cid)->limit("0,6")
			->order("is_index DESC,likes DESC")
			->select();	
		$rel=array();
		foreach ($items_result as $key=>$value){			
			if($key==0){
				if (strpos($value['bimg'], 'taobao') !== false){            
					
					$value['simg'] = $value['bimg'].'_160x160.jpg';
		        }else{
					$value['simg'] = str_replace('.jpg', '_160x160.jpg', $value['bimg']);
		        }
			}
			$rel[]=$value;			
		}		
		return $rel;
	}

	function get_group_items_bysource($sid) {
		$items_mod = M('items');
		$items_result = $items_mod->field('simg')->where("sid=" . $sid)->limit("0,9")->order("is_index DESC,likes DESC")->select();
		
        return $items_result;
	}

	protected function error($message, $url_forward = '', $ms = 3, $ajax = false) {
		$this->jumpUrl = $url_forward;
		$this->waitSecond = $ms;
		parent::error($message, $ajax);
	}
	protected function success($message, $url_forward = '', $ms = 3, $dialog = false, $ajax = false, $returnjs = '') {
		$this->jumpUrl = $url_forward;
		$this->waitSecond = $ms;
		$this->assign('dialog', $dialog);
		$this->assign('returnjs', $returnjs);
		parent::success($message, $ajax);
	}

	protected function check_login(){
		//检测登录
		if($this->setting['ucenterlogin']){
			//引入配置文件、类库
	       	$this->require_uc();
			if(!empty($_COOKIE['Ucenter_auth'])) {
				list($uc_uid, $uc_username) = explode("\t", uc_authcode($_COOKIE['Ucenter_auth'], 'DECODE'));					
				$user=$this->user_mod->where("name='".$uc_username."'")->find();  //获取用户信息 同步discuz那别登录过来的用户				
				if($user){ //生成本系统的用户信息
					if(empty($_COOKIE['user']['id'])&&empty($_COOKIE['user']['name'])){
						$last_time=time();
	    				$key=md5($user['id'].$user['name'].$last_time);
	    				cookie('user[id]',$user['id'],3600*24*7);
	    				cookie('user[name]',$user['name'],3600*24*7);
	    				cookie('user[login_time]',$last_time,3600*24*7);				
	    				cookie('user[key]',$key,3600*24*7);	
	    				$url = U('index/index');
       					echo "<script>location='{$url}';</script>";
					}					
    				return true;
				}
				else{//不存在表示ucenter退出了，本程序同步退出
					cookie('user[id]',null);
					cookie('user[name]',null);
					cookie('user[login_time]',null);
					cookie('user[key]',null);	
					return false;
				}
				
			}
			else{
				return false;
			}
		}else{
			if(isset($_COOKIE['user']['id'])&&$this->user_mod->where("id='{$_COOKIE['user']['id']}'")->count()>0&&check_cookie()){			
				return true;
			}else{
				return false;
			}	
		}
	}
	protected function get_cate_list(){
		$items_cate_mod = D('items_cate');
		$result = $items_cate_mod->order('ordid ASC')->select();
		$cate_list = array();
		foreach ($result as $val) {
			if ($val['pid'] == 0) {
				$cate_list['parent'][$val['id']] = $val;
			} else {
				$cate_list['sub'][$val['pid']][] = $val;
			}
		}
		return $cate_list;
	}
	function remove_html($string, $sublen){
		$string = strip_tags($string);
		$string = preg_replace ('/\n/is', '', $string);
		$string = preg_replace ('/ |　/is', '', $string);
		$string = preg_replace ('/&nbsp;/is', '', $string);
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);
		if(count($t_string[0]) - 0 > $sublen) $string = join('', array_slice($t_string[0], 0, $sublen));
		else $string = join('', array_slice($t_string[0], 0, $sublen));
		return $string;
	}
	//用于js里面显示一些常用信息
	function js_init(){
		$port=$_SERVER['SERVER_PORT']==80?'':':'.$_SERVER['SERVER_PORT'];

		return json_encode(array(
            "app"=>__APP__,
            "root"=>"http://".$_SERVER['SERVER_NAME'].$port.__ROOT__."/",
            "user_id"=>$_COOKIE['user']['id'],
            "uid"=>$this->uid,
            "module"=>MODULE_NAME,
            "action"=>ACTION_NAME,
            "tmpl"=>"http://".$_SERVER['SERVER_NAME'].$port.__TMPL__,
		    "waterfall_sp"=>$this->setting['waterfall_sp'],
            "comment_time"=>$this->setting['comment_time'],
            "masonry"=>$this->setting['show_masonry'],
			"local_images"=>$this->setting['goods_save_images'],
		));
	}
	/*
	 * GET请求
	 */
	function get($sUrl,$aGetParam){

		$oCurl = curl_init();
		if(stripos($sUrl,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		$aGet = array();
		foreach($aGetParam as $key=>$val){
			$aGet[] = $key."=".urlencode($val);
		}
		curl_setopt($oCurl, CURLOPT_URL, $sUrl."?".join("&",$aGet));
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($this->config["debug"])===1){
			echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>GET参数:</td><td><pre>".var_export($aGetParam,true)."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
			if(intval($aStatus["http_code"])==200){
				echo "<tr><td class='narrow-label'>返回结果:</td><td><pre>".$sContent."</pre></td></tr>";
				if((@$aResult = json_decode($sContent,true))){
					echo "<tr><td class='narrow-label'>结果集合解析:</td><td><pre>".var_export($aResult,true)."</pre></td></tr>";
				}
			}
		}
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			echo "<tr><td class='narrow-label'>返回出错:</td><td><pre>".$aStatus["http_code"].",请检查参数或者确实是腾讯服务器出错咯。</pre></td></tr>";
			return FALSE;
		}
	}

	/*
	 * POST 请求
	 */
	function post($sUrl,$aPOSTParam){

		$oCurl = curl_init();
		if(stripos($sUrl,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		$aPOST = array();
		foreach($aPOSTParam as $key=>$val){
			$aPOST[] = $key."=".urlencode($val);
		}
		curl_setopt($oCurl, CURLOPT_URL, $sUrl);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, join("&", $aPOST));
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);

		if(intval($this->config["debug"])===1){
			echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>POST参数:</td><td><pre>".var_export($aPOSTParam,true)."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
			if(intval($aStatus["http_code"])==200){
				echo "<tr><td class='narrow-label'>返回结果:</td><td><pre>".$sContent."</pre></td></tr>";
				if((@$aResult = json_decode($sContent,true))){
					echo "<tr><td class='narrow-label'>结果集合解析:</td><td><pre>".var_export($aResult,true)."</pre></td></tr>";
				}
			}
		}
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			echo "<tr><td class='narrow-label'>返回出错:</td><td><pre>".$aStatus["http_code"].",请检查参数或者确实是腾讯服务器出错咯。</pre></td></tr>";
			return FALSE;
		}
	}

	/*
	 * 上传图片
	 */
	function upload($sUrl,$aPOSTParam,$aFileParam){
		//防止请求超时

		set_time_limit(0);
		$oCurl = curl_init();
		if(stripos($sUrl,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		$aPOSTField = array();
		foreach($aPOSTParam as $key=>$val){
			$aPOSTField[$key]= $val;
		}
		foreach($aFileParam as $key=>$val){
			$aPOSTField[$key] = "@".$val; //此处对应的是文件的绝对地址
		}
		curl_setopt($oCurl, CURLOPT_URL, $sUrl);
		curl_setopt($oCurl, CURLOPT_POST, true);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $aPOSTField);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($this->config["debug"])===1){
			echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>POST参数:</td><td><pre>".var_export($aPOSTParam,true)."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>文件参数:</td><td><pre>".var_export($aFileParam,true)."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
			if(intval($aStatus["http_code"])==200){
				echo "<tr><td class='narrow-label'>返回结果:</td><td><pre>".$sContent."</pre></td></tr>";
				if((@$aResult = json_decode($sContent,true))){
					echo "<tr><td class='narrow-label'>结果集合解析:</td><td><pre>".var_export($aResult,true)."</pre></td></tr>";
				}
			}
		}
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			echo "<tr><td class='narrow-label'>返回出错:</td><td><pre>".$aStatus["http_code"].",请检查参数或者确实是腾讯服务器出错咯。</pre></td></tr>";
			return FALSE;
		}
	}

	function download($sUrl,$sFileName){
		$oCurl = curl_init();

		set_time_limit(0);
		$oCurl = curl_init();
		if(stripos($sUrl,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		curl_setopt($oCurl, CURLOPT_USERAGENT, $_SERVER["USER_AGENT"] ? $_SERVER["USER_AGENT"] : "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.7) Gecko/20100625 Firefox/3.6.7");
		curl_setopt($oCurl, CURLOPT_URL, $sUrl);
		curl_setopt($oCurl, CURLOPT_REFERER, $sUrl);
		curl_setopt($oCurl, CURLOPT_AUTOREFERER, true);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		file_put_contents($sFileName,$sContent);
		if(intval($this->config["debug"])===1){
			echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
			echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
		}
		return(intval($aStatus["http_code"])==200);
	}
	function alert($str){
		print_r("<script type='text/javascript'>alert(".$str.");</script>");
	}
	function pager($count,$pagesize=20){
		import("ORG.Util.Page");
		$pager=new Page($count,$pagesize);
		$this->assign('page', $pager->show_1());
		return $pager;
	}
	/*
	 * 商品瀑布流
	 * */
	function waterfall($count,$where,$order=""){
		import("ORG.Util.Page");
		$items_mod=D("items");
		
		$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
		$sp = !empty($_GET['sp']) ? intval($_GET['sp']) : 1;
		$sp >$this->setting['waterfall_sp'] && exit;
		
		$list_rows =$this->setting['waterfall_sp']* $this->setting['waterfall_items_num'];
		$s_list_rows =$this->setting['waterfall_items_num'];   //每页请求的数
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
        //dump($items_list);
		$this->assign('page', $pager->show_1());
		$this->assign('p', $p);
		$this->assign('show_sp', $show_sp);
		$this->assign('sp', $sp);
		$this->assign('items_list', $items_list);
		if($this->isAjax()&&$sp>1){
			header('Content-Type:text/html; charset=utf-8');
			echo($this->fetch('public:goods_list'));
		}else{
			$this->display();
		}		
	}
	
	/*
	 * 获取专辑小图
	 * */
	function get_album_list($where,$img_num=9){
		import("ORG.Util.Page");
		$album_mod=D("album");
		$album_items_mod=D('album_items');
		$items_mod=D("items");
		$items_comments_mod=D("items_comments");
		
		$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
		
		$count=$album_mod->where($where)->count();
		$pager = new Page($count,20);
		//专辑列表页面
		$cid = isset($_GET['cid']) && intval($_GET['cid']) ? intval($_GET['cid']) :0;
		
		if(S('basic_album_list'.$cid)){
            $res = S('basic_album_list'.$cid);
        }else{        	
	        $res=$album_mod->where($where)
				->limit($pager->firstRow. ',' .$pager->listRows)
				->order("sort_order desc,add_time desc")->select();
			foreach($res as $key=>$val){
				$res[$key]['items_num']=$album_items_mod->where("pid=".$val['id'])->count();
				
				$res2=$album_items_mod->where("pid=".$val['id'])->order("id desc")
				->limit("0,".$img_num)->select();
	
				$items=array();
				$like_num=0;
				$comment_num=0;
				foreach($res2 as $key2=>$val2){
					$img=$items_mod->field("likes,simg,bimg")->where("id=".$val2['items_id'])
					->find();
					if($key2==0){						
						if (strpos($img['bimg'], 'taobao') !== false){ 
							$items[] = $img['bimg'].'_160x160.jpg';
				        }else{
							$items[] = str_replace('.jpg', '_160x160.jpg', $img['bimg']);
				        }
					} 
					else{
						$items[]=$img['simg']; 	//小图
					}  
					$like_num=$like_num+$img['likes'];
			
					$comment_num=$comment_num+$items_comments_mod
					->where('items_id='.$val2['items_id'])->count();
				}
	            
				$total_num=($img_num-count($items));
				for($num=0;$num<$total_num;$num++){
					$items[]=$this->site_root."data/none_pic_v3.png";
				}
	                 
				$res[$key]['items']=$items;
				$res[$key]['like_num']=$like_num;
				$res[$key]['comment_num']=$comment_num;
			}
            S('basic_album_list'.$cid,$res,'3600');
		}	
		$this->assign('page', $pager->show_1());
		$this->assign('p', $p);
		$this->assign('album_list', $res);
		$this->display();
	}
	function update_user_assoc_num($table,$field=null){
		$mod=D($table);
		$field=is_null($field)?$table:$field;
		
		$data=array(
			$field.'_num'=>$mod->where('uid='.$_COOKIE['user']['id'])->count(),
		);
		$this->user_info->where('uid='.$_COOKIE['user']['id'])->save($data);		
	}
	//发送邮件
	/*address 表示收件人地址
	 *title 表示邮件标题
	 *message表示邮件内容
	 * 
	 * */
	public function sendMail($address,$title,$message){ 
		vendor('mail.mail');
		$message   = preg_replace('/\\\\/','', $message);
		$mail=new PHPMailer(); 
		$mail->IsSMTP();     // 设置PHPMailer使用SMTP服务器发送Email    
		$mail->CharSet='UTF-8';     // 设置邮件的字符编码，若不指定，则为'UTF-8'    
		$mail->Port= $this->setting['mail_port'];    //端口号
		$mail->AddAddress($address);   // 添加收件人地址，可以多次使用来添加多个收件人    
		$mail->IsHTML(true); // send as HTML
		$mail->Body=$message;     // 设置邮件正文    		
		//$mail->MsgHTML=$message;
		$mail->From=$this->setting['mail_username'];    // 设置邮件头的From字段。  
		$mail->FromName=$this->setting['mail_fromname'];   // 设置发件人名字    
		$mail->Subject=$title;     // 设置邮件标题    
		$mail->Host=$this->setting['mail_smtp'];        // 设置SMTP服务器。    
		$mail->SMTPAuth=true;   // 设置为“需要验证”
		$mail->Username=$this->setting['mail_username'];     // 设置用户名和密码。    
		$mail->Password=$this->setting['mail_password'];    // 发送邮件。   		
		return($mail->Send());
	}
	//配置淘宝的基本信息
	public function taobao_client()
	{
		vendor('Taobaotop.TopClient');
		vendor('Taobaotop.RequestCheckUtil');
		vendor('Taobaotop.Logger');
		$tb_top = new TopClient;
		$tb_top->appkey = $this->setting['taobao_appkey'];
		$tb_top->secretKey = $this->setting['taobao_appsecret'];
		return $tb_top;
	}
	//配置59miao  api基本信息	
	public function miao_client()
	{
		define('API_CACHETIME','24');  //缓存时间默认为小时   0表示不缓存
		define('API_CACHEPATH','Apicache'); //缓存目录
		define('CHARSET','UTF-8');  //编码
		define('APIURL','http://api.59miao.com/Router/Rest?');  //请求地址http://gw.api.59miao.com/Router/Rest
		define('API_CLEARCACHE','1 23 * *');   //自动清除缓存时间
		vendor('api59miao.init');	
		$appkey = $this->setting['miao_appkey'];
		$appsecret = $this->setting['miao_appsecret'];			
		//引入59秒api文件	
		$AppKeySecret=Api59miao_Toos::GetAppkeySecret($appkey,$appsecret);   //获取appkey appsecret
		$_api59miao=new Api59miao($AppKeySecret);			
		return $_api59miao;
	}
  
	
	
    //发送短信
    function sendMsg($array){
        if(is_array($array)){
            M('UserMsg')->add($array);
        }
        return;
    }
    public function album_seo($cid='',$id='',$uid='',$album=''){
        $model = M('nav');
        $nav['id']=2;
        $info = $model->field("seo_title,seo_keys,seo_desc")->where($nav)->find();
        $map['id'] = $cid;
        $album_cate_id = M('album_cate')->field('title')->where($map)->find();
        $info['title'] = $album_cate_id['title'];
        $album_id = M('album')->field('title')->where("id=".$id)->find();
        $info['name'] = $album_id['title'];
        $info['uname'] = $uid ? getUserName($uid)."的专辑":'';
        $info['album'] = $album ? "专辑" : '';
        if($info['seo_title']){
            $this->seo['seo_title'] =$info['seo_title'];
        }else{
            $this->seo['seo_title'] =$this->replace_keyword($info,0,$this->siteseo['album']['title']);
        }   
            
        if($info['seo_keys']){
            $this->seo['seo_keys'] =$info['seo_keys'];
        }else{
            $this->seo['seo_keys'] =$this->replace_keyword($info,1,$this->siteseo['album']['keywords']);
        } 
            
        if($info['seo_desc']){
            $this->seo['seo_desc'] =$info['seo_desc'];
        }else{
            $this->seo['seo_desc'] =$this->replace_keyword($info,2,$this->siteseo['album']['description']);
        } 	
 
        $this->assign('seo', $this->seo);
    }
    public function nav_seo($moudel,$model,$action,$infos=''){ 
        
        $map['id'] = $action;
        $model = M($model);
        $items_mod = D('items');
  		$info=$model->where($map)->find();
        $pid = $model->where("id=".$info['pid'])->find();
        $info['pname'] = $pid['name'];
        $spid = $model->where("id=".$pid['pid'])->find();
        $info['lname'] = $spid['name'];
        foreach($infos as $v){
            $tags .= $v['name']." ";
        }
        $info['tags'] = rtrim($tags);
        if($info['seo_title']){
            $this->seo['seo_title'] =$info['seo_title'];
        }else{
            $this->seo['seo_title'] =$this->replace_keyword($info,0,$this->siteseo[$moudel]['title']);
        }   
            
        if($info['seo_keys']){
            $this->seo['seo_keys'] =$info['seo_keys'];
        }else{
            $this->seo['seo_keys'] =$this->replace_keyword($info,1,$this->siteseo[$moudel]['keywords']);
        } 
            
        if($info['seo_desc']){
            $this->seo['seo_desc'] =$info['seo_desc'];
        }else{
            $this->seo['seo_desc'] =$this->replace_keyword($info,2,$this->siteseo[$moudel]['description']);
        } 	
        	
        //搜索SEO
        if(MODULE_NAME=="search" && $_GET['sortby']=="likes"){
            
            $this->seo['seo_title'] = "大家喜欢的宝贝"."-24小时最热"."-".$this->seo['seo_title'];
        }
		if(MODULE_NAME=="search" && $_GET['sortby']=="sort_order"){
            
            $this->seo['seo_title'] = "大家喜欢的宝贝"."-推荐"."-".$this->seo['seo_title'];
        }
        if(MODULE_NAME=="search" && $_GET['sortby']=="time"){
            
            $this->seo['seo_title'] = "大家喜欢的宝贝"."-最新"."-".$this->seo['seo_title'];
        }
        if(MODULE_NAME=="search" && $_GET['cid']=="no"){
            
            $this->seo['seo_title'] = "大家喜欢的宝贝"."-大杂烩"."-".$this->seo['seo_title'];
        }
        if(MODULE_NAME=="search" && $_GET['keywords']){
            
            $this->seo['seo_title'] = "-大家喜欢的宝贝"."-".$this->seo['seo_title'];
        }	
		$this->assign('seo', $this->seo);
    }
    function replace_keyword($info,$e,$str){
        $h = array(" - "," , "," ");
        $str = str_replace('{$site_name}',$this->setting['site_name'],$str);
        $str = $info['title'] ? str_replace('{$title}',$info['title'].$h[$e],$str):str_replace('{$title}',$info['title'],$str);
        $str = $info['name'] ? str_replace('{$name}',$info['name'].$h[$e],$str):str_replace('{$name}',$info['name'],$str);
        $str = str_replace('{$tags}',$info['tags'],$str);
        $str = $info['uname'] ? str_replace('{$uname}',$info['uname'].$h[$e],$str) : str_replace('{$uname}',$info['uname'],$str);
        $str = $info['pname'] ? str_replace('{$pname}',$info['pname'].$h[$e],$str) : str_replace('{$pname}',$info['pname'],$str);
        $str = $info['lname'] ? str_replace('{$lname}',$info['lname'].$h[$e],$str) : str_replace('{$lname}',$info['lname'],$str);
        $str = $info['album'] ? str_replace('{$album}',$info['album'].$h[$e],$str) : str_replace('{$album}',$info['album'],$str);
        return $str;
    }

    public function ucNavSeo($action,$userid){
        $arr = array(
            "index"=>"的专辑-{$this->setting['site_title']}",
            "me"=>"的动态-{$this->setting['site_title']}",
            "album"=>"的专辑-{$this->setting['site_title']}",
            "like"=>"喜欢的宝贝-{$this->setting['site_title']}",
            "share"=>"的分享-{$this->setting['site_title']}",
            "details"=>"的专辑-{$this->setting['site_title']}",
            "account_basic"=>"的基本资料-{$this->setting['site_title']}",
            "account_face"=>"的上传头像-{$this->setting['site_title']}",
            "account_sns"=>"的账号绑定-{$this->setting['site_title']}",
            "account_pwd"=>"的修改密码-{$this->setting['site_title']}",
            "account_exchange"=>"的礼品兑换-{$this->setting['site_title']}",
            "account_commission"=>"的佣金管理-{$this->setting['site_title']}",
            "account_invitation"=>"的邀请好友-{$this->setting['site_title']}",
            "account_message"=>"的短信息-{$this->setting['site_title']}",
            "account_get_cash"=>"的提现-{$this->setting['site_title']}",
            "keywords"=>"{$this->setting['site_keyword']}",
            "description"=>"{$this->setting['site_description']}",
        );
        if($userid){
            $seo = $this->seo;
            $seo['seo_title'] = getUserName($userid).$arr[$action];
            $seo['seo_keys'] = $arr["keywords"];
            $seo['seo_desc'] = $arr["description"];
            return $seo;
        }elseif($_COOKIE['user']['id']){
            $seo = $this->seo;
            $seo['seo_title'] = getUserName($_COOKIE['user']['id']).$arr[$action];
            $seo['seo_keys'] = $arr["keywords"];
            $seo['seo_desc'] = $arr["description"];
            return $seo;
        }else{
            return $this->seo;
        }
    }
    

    //网站开关状态
    function siteStatus($options){
        header("Content-type: text/html; charset=utf-8");
        exit($options);
    }
    /*
    function _empty(){
        header("HTTP/1.0 404 Not Found");
        $this->display('Public:404'); 
    }
    */
    //读取通用SEO
    function siteseo(){
        $list = $this->seo_mod->select();
        $array = array();
        foreach($list as $v){
            $array[$v['actionname']] = $v;
        }
        return $array;
    }
    //底部关于我们信息
    function about(){
        $s = $this->article_mod->field("id,title,url")->where('cate_id=1 and status=1')->order('ordid')->select();
        return $s;
    }
    //引入ucenter相关文件
    function require_uc(){
    	 include_once ROOT_PATH.'/uc_client/config.inc.php';
	     include_once ROOT_PATH.'/uc_client/uc_client/client.php';
    	
    }
	//查询母个商品的返现金额
	public function get_commission($title,$num_iid,$p='commission'){		
		$tb_top = $this->taobao_client();
		$req = $tb_top->load_api('TaobaokeItemsGetRequest');
		$req->setFields("num_iid,title,nick,pic_url,price,click_url,shop_click_url,commission");
		$req->setPid($this->setting['taobao_pid']);
		$req->setNick($this->setting['taobao_usernick']);
		$req->setKeyword($title);
		$req->setPageNo(1);
		$req->setPageSize(40);	
		$goods_list = get_object_vars_final($tb_top->execute($req));		
		if($goods_list['total_results']>0){
			$good_list_rel=$goods_list['taobaoke_items']['taobaoke_item'];	
		}
		else{
			if($p=='commission'){
				return '0';	
			}else{
				return;	
			}
			
		}	   
		if(!is_array($good_list_rel)){
			if($p=='commission'){
				return '0';	
			}
			else{
				return;	
			}
		    
		}
		$c=count($good_list_rel);
	    for($i=0;$i<$c;$i++){
	        if($good_list_rel[$i]['num_iid']==$num_iid && strip_tags($good_list_rel[$i]['title'])==strip_tags($title)){
		        $re=$good_list_rel[$i];			   
		    }
	    }
	    if($p=='commission') return $re['commission'];
	    if($p=='click_url') return $re['click_url'];
	}
}

?>