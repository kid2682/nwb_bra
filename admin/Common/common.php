<?php
//删除商品图片和目录可以是数组或者文件
function delDirFile($path,$arr){
    if(is_array($arr)){
        foreach($arr as $v){
            $delPath = $path.'/'.$v;
            $allFile = scandir($delPath);
            foreach($allFile as $val){
                if($val != '.' || $val != '..'){
                    $delfile = $delPath.'/'.$val;
                    unlink($delfile);
                }   
            }
            rmdir($delPath);
        }   
    }else{
        $delfile = $path.'/'.$arr;
        unlink($delfile);
    }
}
//清除api缓存
function delCache($dir){	//删除目录
	    $handle=@opendir($dir);
	    while ($file = @readdir($handle)) {
	        $bdir=$dir.'/'.$file;
	        if (filetype($bdir)=='dir') {
	            if($file!='.' && $file!='..')
	            delCache($bdir);
	        } else {
	            @unlink($bdir);
	        }
	    }
	    closedir($handle);
	    @rmdir($dir);		
		return true;
}
//清除所有缓存新方法
function deleteCacheData($dir){
		$fileArr	=	file_list($dir);		
	 	foreach($fileArr as $file)
	 	{
	 		if(strstr($file,"Logs")==false)
	 		{	 			
	 			@unlink($file);	 			
	 		}
	 	}
	 
	 	$fileList	=	array();
	}
function file_list($path)
{
 	global $fileList;
 	if ($handle = opendir($path)) 
 	{
 		while (false !== ($file = readdir($handle))) 
 		{
 			if ($file != "." && $file != "..") 
 			{
 				if (is_dir($path."/".$file)) 
 				{ 					
 						
 					file_list($path."/".$file);
 				} 
 				else 
 				{
 						//echo $path."/".$file."<br>";
 					$fileList[]	=	$path."/".$file;
 				}
 			}
 		}
 	}
 	return $fileList;
}


function url_parse($url){    	
    $rs = preg_match("/^(http:\/\/|https:\/\/)/", $url, $match);
	if (intval($rs)==0) {
		$url = "http://".$url;
	}		
	return $url;
}
function uimg($img){
	if(empty($img)){
		return SITE_ROOT."data/user/avatar.gif";
	}
	return $img;
}
//转换时间
function gmtTime()
{	
	return date('YmdHis');
}
//如果不是二维数组返回true
function IsTwoArray($array){
	  return count($array)==count($array, 1);
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
/**
 * $username 用户名
 
 * 
 * */

//返现函数
function fanli12($username,$commission,$cashback_rate,$integralback_rate){
	
}

/*获取返现积分，和返现金 * $commission 佣金
 * $cashback_rate 返现率
 * $integralback_rate 反积分比例
 * */
function cashback($commission,$cashback_rate,$integralback_rate){  //输入会员等级，订单总佣金，输出该会员应该得到的返现，等级不输入，按最低返现计算
   	$cashback=array();
	$cashback['cacheback']=round($commission*$cashback_rate/100,2);
	$cashback['integralback']=round($commission*$integralback_rate/100,2);
	return $cashback;
}
//集分宝返现
/*
 * $commission       佣金
 * $fanxian_bili     1块钱相当于多少集分宝
 * $cashback_rate    返现比例
 *  
 * $integralback_rate  返积分比例
 * */

function cashback_jifenbao($commission,$fanxian_bili,$cashback_rate,$integralback_rate){  //输入会员等级，订单总佣金，输出该会员应该得到的返现，等级不输入，按最低返现计算
   	$cashback=array();
	$cashback['cacheback']=round($commission*$cashback_rate*$fanxian_bili/100,2);
	$cashback['integralback']=round($commission*$integralback_rate/100,2);
	return $cashback;
}

//表单过滤函数
function setFormString($_string) {
		if (!get_magic_quotes_gpc()) {
			if (is_array($_string)) {
				foreach ($_string as $_key=>$_value) {
					$_string[$_key] = setFormString($_value);	//迭代调用
				}
			} else {
				return addslashes($_string); //mysql_real_escape_string($_string, $_link);不支持就用代替addslashes();
			}
		}
		return $_string;
}	
//对象表单选项转换
function setObjFormItem($_data, $_key, $_value) {
	$_items = array();
	if (is_array($_data)) {
		foreach ($_data as $_v) {
			$_items[$_v->$_key] = $_v->$_value;
		}
	}
	return $_items;
}
//数组表单转换
function setArrayFormItem($_data, $_key, $_value) {
		$_items = array();
		if (is_array($_data)) {
			foreach ($_data as $_v) {
				$_items[$_v[$_key]] = $_v[$_value];
			}
		}
	return $_items;
}





//返利函数
function fanli($username, $fxje, $tgje, $order_code, $merchant_id) {
	if (JIFENOPEN == 1 && JIFENBL > 0) {
		$jifen = round($fxje * JIFENBL);
		if ($jifen > 0) {
			$msg_tabao_jifen = $jifen . "积分！";
		}
	} else {
		$jifen = 0;
	}

	$field_arr = array (
		'money' => $fxje,
		'jifen' => $jifen,
		'dengji' => 1
	);
	update_sql('user', $field_arr, "ddusername='$username'", 1); //增加会员金额,积分和等级

	//用户消息
	$title = '您获得了新的商城交易返现！';
	$trade_id = $order_code;
	$msg_tabao = "您获得了新的交易返现，" . $merchant_id . "商城订单号" . $order_code . "返现金额" . $fxje . '！' . $msg_tabao_jifen;
	$filed_arr = array (
		'title' => $title,
		'content' => $msg_tabao,
		'addtime' => date('Y-m-d H:i:s'
	), 'see' => 0, 'ddusername' => $ddusername, 'senduser' => '网站客服');
	insert_one_sql("msg", $filed_arr);

	//用户明细
	$shijian = "商城交易返现";
	$memo = $merchant_id . "交易号$order_code";
	$filed_arr = array (
		'ddusername' => $ddusername,
		'shijian' => $shijian,
		'addtime' => date('Y-m-d H:i:s'
	), 'je' => $fxje, 'jifen' => $jifen, 'memo' => $memo);
	insert_one_sql("mingxi", $filed_arr);

	//求推荐人
	$tjrid = sel_sql("user", "tjr", "ddusername='$ddusername'");
	if ($tjrid > 0) {
		$tjrname = sel_sql("user", "ddusername", "Id='$tjrid'");
		//增加推荐人佣金
		$field_arr = array (
			'money' => $tgje
		);
		update_sql("user", $field_arr, "Id='$tjrid'", 1);

		//用户消息
		$title = '您获得了新的推广佣金！';
		$msg_taobaotuiguang = "您获得了新的推广佣金" . $tgje;
		$filed_arr = array (
			'title' => $title,
			'content' => $msg_taobaotuiguang,
			'addtime' => date('Y-m-d H:i:s'
		), 'see' => 0, 'ddusername' => $tjrname, 'senduser' => '网站客服');
		insert_one_sql("msg", $filed_arr);

		//用户明细
		$shijian = "推广佣金";
		$memo = "交易人$ddusername";
		$filed_arr = array (
			'ddusername' => $tjrname,
			'shijian' => $shijian,
			'addtime' => date('Y-m-d H:i:s'
		), 'je' => $tgje, 'memo' => $memo);
		insert_one_sql("mingxi", $filed_arr);
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
*  获取用户头像
   m大 z中 s小
*/
function getUserFace($uid,$type='s'){
    $array = array("80"=>"m_","60"=>"z_","35"=>"s_");  
   
    if($type=='all'){
        foreach($array as $k=>$v){
            $facePath = "./data/user/{$uid}/{$v}{$uid}.jpg";
            if(file_exists($facePath)){
                $face[$k]="./data/user/{$uid}/{$array[$k]}{$uid}.jpg";
            }else{
                $face[$k] = "./data/user/{$array[$k]}avatar.gif";
            }
        }
       
        return $face;
    }else{
       $defaultFace = "./data/user/{$type}_avatar.gif";
       $newFace = "./data/user/{$uid}/{$type}_{$uid}.jpg";
        if(file_exists($newFace))
            $face = "./data/user/{$uid}/{$type}_{$uid}.jpg";
        else
            $face = "./data/user/{$type}_avatar.gif";
        return $face;
            
    }
    
    
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
		curl_close($ch);
		return $reponse;
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
//传入日期的格式为
//$Date_1="2009-8-09";

//echo $Date_1+1;

//$Date_2="2009-06-08";
function get_diff_date($start_date,$end_date){
	$Date_List_a1=explode("-",$start_date);
	$Date_List_a2=explode("-",$end_date);	
	$d1=mktime(0,0,0,$Date_List_a1[1],$Date_List_a1[2],$Date_List_a1[0]);	
	$d2=mktime(0,0,0,$Date_List_a2[1],$Date_List_a2[2],$Date_List_a2[0]);	
	$tmp='';
	if($d1>$d2){
		$tmp=$d2;
		$d2=$d1;
		$d1=$tmp;
	}	
	$Days=round(($d2-$d1)/3600/24);
	return $Days;

}
?>