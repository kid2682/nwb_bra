<?php
class publicAction extends baseAction{
	//验证码
    function verify(){
        import("ORG.Util.Image");
        Image::buildImageVerify();
    }
    function photo(){
	    $url=replace_url($_REQUEST['url'],2);

	    header('HTTP/1.1 301 Moved Permanently');
 	//echo file_get_contents($url);
 		header("Location: ".$url);
	 	//echo $this->curl($url);	 	
    }
	private function curl($url){
	    $header = array();
	    $header[] = "Accept: */*";
	    $header[] = "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
	    $header[] = "Accept-Charset:GB2312,utf-8;q=0.7,*;q=0.7";
	    $header[] = "Connection: Keep-Alive";
	    $header[] = "Cache-Control: no-cache";
	    $header[] = "Cache-Control: max-age=0";
	    $header[] = "Referer: $url";
	    
	    $ch = curl_init();
	    curl_setopt($ch,CURLOPT_TIMEOUT,0);
	    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch,CURLOPT_VERBOSE,0);
	    //curl_setopt($ch,   CURLOPT_PROXY,   "59.57.15.71:80");
	    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch,CURLOPT_REFERER,$url);
	    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);	    
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return $output;
	}
}
?>