<?php
/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'' : $slice;
}
//计算时间
function gettime($time){
    if($rtime = (time()-$time)/3600/24/365>1){
        $rtime = (time()-$time)/3600/24/365;
        return floor($rtime)."年前";
    }elseif($rtime = (time()-$time)/3600/24/30>1){
        $rtime = (time()-$time)/3600/24/30;
        return floor($rtime)."月前";
    }elseif($rtime = (time()-$time)/3600/24>1){
        $rtime = (time()-$time)/3600/24;
        return floor($rtime)."天前";
    }elseif($rtime = (time()-$time)/3600>1){
        $rtime = (time()-$time)/3600;
        return floor($rtime)."小时前";
    }elseif((time()-$time)/60>1){
        $rtime = (time()-$time)/60;
        return floor($rtime)."分钟前";
    }elseif((time()-$time)<60){
        return time()-$time."秒前";
    }
}
//获取用户名
function getUserName($uid){
    $map['id'] = $uid;
    $uc = M('user')->field('name')->where($map)->find();
    return $uc['name'];
}
/*
*   返回剪裁后的图片地址$id = 商品ID    $width = 剪裁后的图片宽度
*/
function isImages($id){
    $path1 = ROOT_PATH.'/data/items/'.$id.'/'.md5(64).'_64.jpg';
    $path2 = ROOT_PATH.'/data/items/'.$id.'/'.md5(210).'_210.jpg';
    $path3 = ROOT_PATH.'/data/items/'.$id.'/'.md5(450).'_450.jpg';
    return file_exists($path1)&&file_exists($path2)&&file_exists($path3) ? true : false;
}
//屏蔽蜘蛛访问
function banspider($ban_str){			
	if(preg_match("/($ban_str)/i", $_SERVER['HTTP_USER_AGENT']))
	{		    
	    exit; 
	}
}
//屏蔽ip
function banip($value1,$value2){
	$ban_range_low=ip2long($value1);
	$ban_range_up=ip2long($value2);
	$ip=ip2long($_SERVER["REMOTE_ADDR"]);			
	if ($ip>=$ban_range_low && $ip<=$ban_range_up)
	{
		echo "对不起,您的IP在被禁止的IP段之中，禁止访问！";
		exit();
	}
}
function getBanip(){
	if(file_exists('./data/banip_config_inc.php')){
		$banip=@file_get_contents('./data/banip_config_inc.php');
		$banip=unserialize($banip);
		return $banip;
	}
	else{
		return false;
	}
}
/*
*   返回剪裁后的图片地址$id = 商品ID    $width = 剪裁后的图片宽度
*/
function base64ImagesPath($id,$width){
    return base64_encode(SITE_ROOT.'/data/items/'.$id.'/'.md5($width).'_'.$width.'.jpg');
}
//剪裁图平
function calculation($iamges,$path,$width=array('64','210','450')){
    foreach($width as $vwidth){
        /**/
        if(!isDir($path)){
            return "建立目录不成功！";
        }
    	//获取图片资源的宽度、高度、类型
    	list($imagewidth, $imageheight, $imageType) = getimagesize($iamges);
        switch($imageType) {
    		case "image/gif":
    			$im=imagecreatefromgif($iamges); 
    			break;
    	    case "image/pjpeg":
    		case "image/jpeg":
    		case "image/jpg":
    			$im=imagecreatefromjpeg($iamges); 
    			break;
    	    case "image/png":
    		case "image/x-png":
    			$im=imagecreatefrompng($iamges); 
    			break;
      	}
        
        //计算比例
        $proportion = $vwidth/$imagewidth;
        //$im = imagecreatefromjpeg($iamges);
            
        //计算新图片大小   
        $new_img_width  = $proportion*$imagewidth; 
       	$new_img_height = $proportion*$imageheight; 
            
        if($vwidth < $imagewidth){
        	$newim = imagecreatetruecolor($new_img_width, $new_img_height);   
        	//复制资源
        	imagecopyresampled($newim, $im, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
    	}else{
    	    $newim = imagecreatetruecolor($imagewidth, $imageheight);   
        	//复制资源
        	imagecopyresampled($newim, $im, 0, 0, 0, 0, $imagewidth, $imageheight, $imagewidth, $imageheight);
    	}
        $saveimages = $path.md5($vwidth).'_'.$vwidth.".jpg";
        
    	//header('Content-Type: image/jpeg');
    	//imagejpeg($newim); 
    	imagejpeg($newim,$saveimages,75); 
    	imagedestroy($newim);  
    	imagedestroy($im);
     }
}
function isDir($path){
    if(is_dir($path)){
        return true;
    }else{
        if(mkdir($path,777))
            return true;
        else
            return false;
    }
}
/*url_parse*/
function url_parse($url){    	
    $rs = preg_match("/^(http:\/\/|https:\/\/)/", $url, $match);
	if (intval($rs)==0) {
		$url = "http://".$url;
	}		
	return $url;
}
function base64encode($data){
	return base64_encode($data);
}
/*关键词替换*/

 function ReplaceKeywords($content)
{
	if (empty($content) )
	{
		return($content);
	}
	//获取屏蔽词语
	if(file_exists('./data/word.txt')){
		$str=file_get_contents('./data/word.txt');
		$arrKeywords=explode(',', $str);
		$array_keywords=array();
		foreach ($arrKeywords as $key=>$value){
			$array_keywords[]=explode('|', $value);
		}			
		foreach($array_keywords as $arr)//遍历关键字
		{
			if (strpos($content, $arr[0]) > -1 )
			{
				$content = preg_replace("/" . $arr[0] . "/i", $arr[1], $content);
				$arrTemp[] = $arr;				
			}
		}
		return $content;
	}
	else{
		return $content;
	}
	
}


/*
    获取用户金钱
*/
/*
    写入cookie方法
*/
function writeCookie($uid,$uname){
    $last_time=time();
    $key=md5($uid.$uname.$last_time);				
    cookie('user[id]',$uid);
    cookie('user[name]',$uname);
    cookie('user[login_time]',$last_time);				
    cookie('user[key]',$key);
}
/*
*  获取用户头像
   m大 z中 s小
*/
function getUserFace($uid,$type='s'){
    $array = array("80"=>"m_","60"=>"z_","35"=>"s_");  
   
    if($type=='all'){
        foreach($array as $k=>$v){
            $facePath = ROOT_PATH."/data/user/{$uid}/{$v}{$uid}.jpg";
            if(file_exists($facePath)){
                $face[$k]=SITE_ROOT."data/user/{$uid}/{$array[$k]}{$uid}.jpg";
            }else{
                $face[$k] = SITE_ROOT."data/user/{$array[$k]}avatar.gif";
            }
        }
       
        return $face;
    }else{
       $defaultFace = ROOT_PATH."/data/user/{$type}_avatar.gif";
       $newFace = ROOT_PATH."/data/user/{$uid}/{$type}_{$uid}.jpg";
        if(file_exists($newFace))
            $face = SITE_ROOT."data/user/{$uid}/{$type}_{$uid}.jpg";
        else
            $face = SITE_ROOT."data/user/{$type}_avatar.gif";
        return $face;
            
    }
    
    
}
/*
*  获取管理员用户名
*/
function getAdminUserName(){
    if($_SESSION['admin_info']['user_name']){
        return $_SESSION ['admin_info']['user_name'];
    }else{
        $adminUser = M("admin")->where("id=1")->find();
        return $adminUser['user_name'];
    }
}



function uc($url,$vars='',$suffix=true,$redirect=false,$domain=false){
	$uid=empty($_REQUEST['uid'])?$_COOKIE['user']['id']:intval($_REQUEST['uid']);
	if($vars==''){
		$vars="&uid=".$uid;
	}
	elseif(is_array($vars)){
		$vars['uid']=$uid;
	}
	return u($url,$vars,$suffix,$redirect,$domain);
}
function uimg($img){
	if(empty($img)){
		return SITE_ROOT."data/user/avatar.gif";
	}
	return $img;
}
/*
 * 检查是否喜欢、分享,不存在则添加
 * */
function check_favorite($type,$id){
	$mod=D($type);
	if(!$mod->where("items_id=$id and uid=".$_COOKIE['user']['id'])->count()>0){
		$mod->add(array(
            'items_id'=>$id,
            'uid'=>$_COOKIE['user']['id'],
			'add_time'=>time()
		));
		return false;
	}
	return true;
}
/*
 * 获取喜欢记录
 * */
function get_favorite($type,$pagesize=8){
	import("ORG.Util.Page");

	if($type=='like_list'){
		$mod=D($type);
		$items_mod=D('items');
		
		$where='uid='.$_COOKIE['user']['id'];
		 
		$count = $mod->where($where)->count();
		$p = new Page($count,$pagesize);
		 
		$like_list=$mod->where($where)->limit($p->firstRow.','.$p->listRows)->select();

		foreach($like_list as $key=>$val){
			 
			$list[$key]=$items_mod->where('id='.$val['items_id'])->find();
		}
		return array('list'=>$list,'page'=>$p->show());
	}else if($type=='share_list'){
		$where='uid='.$_COOKIE['user']['id'];
		$mod=D('items');		
		$count = $mod->where($where)->count();
		
		$p = new Page($count,$pagesize);
        $list=$mod->where($where)->limit($p->firstRow.','.$p->listRows)->select();
        return array('list'=>$list,'page'=>$p->show());
	}
}
//检测cookie是否正常
function check_cookie(){
	if(isset($_COOKIE['user'])){
		$key=$_COOKIE['user']['key'];
		$now_key=$_COOKIE['user']['id'].$_COOKIE['user']['name'].$_COOKIE['user']['login_time'];
		if($key!=md5($now_key))
		return false;		
		return true;
			
				
	}else{
		return false;
	}
}
//转换时间
function gmtTime()
{	
	return date('Ymdhis');
}
//表单转义
function setFormString($_string) {
	if (!get_magic_quotes_gpc()) {
		if (is_array($_string)) {
			foreach ($_string as $_key=>$_value) {
				$_string[$_key] = setFormString($_value);	//不支持就用代替addslashes();
			}
		} else {
			return addslashes($_string); //mysql_real_escape_string($_string, $_link);
		}
	}
	return $_string;
}
//htmlspecialchars

function setHtmlspecialchars($_string){
	if (is_array($_string)) {
		foreach ($_string as $_key=>$_value) {
			$_string[$_key] = setHtmlspecialchars($_value);	//迭代调用
		}
	} else {
		return htmlspecialchars($_string); //mysql_real_escape_string($_string, $_link);不支持就用代替addslashes();
	}
	return $_string;	
}
//如果不是二维数组返回true
function IsTwoArray($array){
	  return count($array)==count($array, 1);
}
//把对象数组转换为关联数组的方法
function get_object_vars_final($obj){
	if(is_object($obj)){
		$obj=get_object_vars($obj);
	}
	if(is_array($obj)){
		foreach ($obj as $key=>$value){
			$obj[$key]=get_object_vars_final($value);
		}
	}
	return $obj;
}
/*
 * code=1表示是赋值操作
 * code=2表示取值操作
 * */
 function replace_url($url,$code= 1)
{	
	if($code == 1){		
		$url = str_replace ("img","<1<",$url);
		$url = str_replace ("image","<2<",$url);
		$url = str_replace ("taobaocdn.com","<3<",$url);
		$url = str_replace ("59miao.com","<4<",$url);
		$url = str_replace ("/bao/uploaded","<5<",$url);
		$url = str_replace ("210x1000","<6<",$url);	
		$url = str_replace ("http://","<7<",$url);
		$url = base64_encode($url);
		//$url=substr($url, 0,10).'/'.substr($url, 10,strlen($url)).'.jpg';
		$url=substr($url, 0,10).'/'.substr($url, 10,10).'/'.substr($url, 20,strlen($url)).'.jpg';
		//$url = $url .'.jpg';
		//if($weijingtai==1){$url="photo/".$url;}else{$url="photo.php?url=".$url;} 
	}
	if($code == 2){
		$url = str_replace (".jpg","",$url);
		$url = str_replace ("/","",$url);
		$url = base64_decode($url);
		$url = str_replace ("<1<","img",$url);
		$url = str_replace ("<2<","image",$url);
		$url = str_replace ("<3<","taobaocdn.com",$url);
		$url = str_replace ("<4<","59miao.com",$url);
		$url = str_replace ("<5<","/bao/uploaded",$url);
		$url = str_replace ("<6<","210x1000",$url);					
		$url = str_replace ("<7<","http://",$url);
		
	}
	return $url;
}
//数组中随机取出俩个数
function getRandArray($array){
		$i=rand(0, count($array)-1);
		if($i==count($array)-1){
			$j=0;
		}else{
			$j=$i+1;
		}		
		$new_ad_rel=array();
		
		$new_ad_rel[]=$array[$i];
		$new_ad_rel[]=$array[$j];
	return $new_ad_rel;
}
//根据url获取id的方法
function get_id($url) {
	$id = 0;
	$parse = parse_url($url);
	if (isset($parse['query'])) {
		parse_str($parse['query'], $params);
		if (isset($params['id'])) {
			$id = $params['id'];
		} elseif (isset($params['item_id'])) {
			$id = $params['item_id'];
		} elseif (isset($params['default_item_id'])) {
			$id = $params['default_item_id'];
		}elseif(isset($params['mallstItemId'])){
			$id = $params['mallstItemId'];
		}else if(isset($params['num_iid '])){
			$id = $params['num_iid'];
		}
	}
	return $id;
}
//获取淘宝的图片
function getTaoImg($img,$width,$height){
    $taoimg = $img.'_'.$width.'x'.$height.'.jpg';   
    return $taoimg;
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
?>