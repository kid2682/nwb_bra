<?php
class taoOauthAction extends baseAction {
	function index(){		 
		 $code = $_REQUEST['code'];   //通过访问https://oauth.taobao.com/authorize获取code
		 $grant_type = 'authorization_code';
		 $redirect_uri = $this->site_root . "admin.php?m=taoOauth&state=1";  //此处回调url要和后台设置的回调url相同
		 $client_id = $this->setting['taobao_appkey'];//自己的APPKEY
		 $client_secret = $this->setting['taobao_appsecret'];//自己的appsecret
		 
		 //请求参数
		 $postfields= array('grant_type'     => $grant_type,
		                     'client_id'     => $client_id,
		                     'client_secret' => $client_secret,
		                     'code'          => $code,
		                     'redirect_uri'  => $redirect_uri
		 );		 
		 $url = 'https://oauth.taobao.com/token';
		 
		 $token = get_object_vars_final(json_decode(curl($url,$postfields)));
		 //print_r($token);
		 //print_r($token);
		 //查看授权是否成功
		if(isset($token['error'])){
			$this->assign('error',$token['error']);
			$this->assign('error_description',$token['error_description']);			
		}else{			
			 $access_token = $token['access_token'];			
			 $this->assign('seeeion_key',$access_token);
		}
		 //自动刷新令牌refresh_token
		 $postfields2= array('grant_type'     => 'refresh_token',
		                     'client_id'     => $client_id,
		                     'client_secret' => $client_secret,		                  
		                     'refresh_token'  =>$token['refresh_token']
		 );		 
		 $url = 'https://oauth.taobao.com/token';
		 $token = get_object_vars_final(json_decode(curl($url,$postfields2)));		 
		 if(isset($token['error'])){		 			
			echo '<div style="font-size:14px; margin-top:20px; color:red; text-align:center;">自动刷新淘宝授权失败，session 有效期为一天'.'---'.$token['error_description'].'</div>';	
		 }
		$this->display();
	}
	function refresh_token(){
		 $client_id = $this->setting['taobao_appkey'];//自己的APPKEY
		 $client_secret = $this->setting['taobao_appsecret'];//自己的appsecret
		 $refresh_token=$this->setting['tao_session'];//refresh_token
		 $grant_type='refresh_token';
		//请求参数
		 $postfields= array('grant_type'     => $grant_type,
		                     'client_id'     => $client_id,
		                     'client_secret' => $client_secret,		                  
		                     'refresh_token'  =>$refresh_token
		 );		 
		 $url = 'https://oauth.taobao.com/token';
		 
		 $token = get_object_vars_final(json_decode(curl($url,$postfields)));
		 print_r($token);	
		 
		if(!is_array($token)){			
			$this->error('对不起，授权失败,授权不可用',U('items_collect/author_tao'));
		}
		if(isset($token['error'])){
			if($token['error_description']=='refresh times limit exceed'){
				$this->error('对不起，授权失败,自动刷新淘宝授权可用',U('items_collect/author_tao'));
				//jump(-1,'自动刷新淘宝授权可用');
			}
			else{
				$this->error('对不起，检测失败，请从新获取淘宝授权后再检测',U('items_collect/author_tao'));				
			}
		}
		if(urldecode($token['taobao_user_nick'])==$this->setting['taobao_nick']){
			$this->success('恭喜您，授权成功',U('items_collect/author_tao'));
		}
		else{
			$this->error('对不起，授权失败,请核对后台淘宝账号是否正确',U('items_collect/author_tao'));			
		}
		exit;
	}
}
?>