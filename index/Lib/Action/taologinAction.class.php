<?php

class taologinAction extends baseAction {
	function index(){		 
		 $code = $_REQUEST['code'];   //通过访问https://oauth.taobao.com/authorize获取code
		 $grant_type = 'authorization_code';
		 $redirect_uri = $this->site_root . "index.php?m=taologin&state=1";  //此处回调url要和后台设置的回调url相同
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
		 
		 $token = json_decode(curl($url,$postfields));
		 $access_token = $token->access_token;
		 $openid = $token->taobao_user_id;
		 $name=$token->taobao_user_nick;
		 $encode_token=json_encode($token);
		 
		 $user_openid=$this->user_openid_mod->where("openid={$openid} AND type='tao'")->find();
		 $is_new=false;

		if($user_openid){
			$user_rel=$this->user_mod->where('id='.$user_openid['uid'])->find();
			if(count($user_rel)>0){
				//第二次登录
				//$_COOKIE['user']['id']=$user_openid['uid'];
				
				$last_time=time();
				$key=md5($user_rel['id'].$user_rel['name'].$last_time);				
				cookie('user[id]',$user_rel['id'],3600*24*7);
				cookie('user[name]',$user_rel['name'],3600*24*7);
				cookie('user[login_time]',$last_time,3600*24*7);				
				cookie('user[key]',$key,3600*24*7);	
				$data=array('last_time'=>time(),'last_ip'=>$_SERVER['REMOTE_ADDR']);
				$this->user_mod->where('id='.$user_rel['id'])->save($data);					
				//存在这条数据的话，判断状态			
				if($user_rel['status']==0){					
					header('Location:'.U('uc/sign'));exit;
				}				
				
			}else{
				$this->user_openid_mod->where("openid={$openid} AND type='tao'")->delete();
				$is_new=true;
			}
		}else{
			$is_new=true;
		}
		if($is_new){			
			$tao_info=array('info'=>$encode_token);
			$data=array(
					'name'=>$name,
					//'img'=>$res->figureurl_2,
					'last_time'=>time(),
					'last_ip'=>$_SERVER['REMOTE_ADDR'],
					'add_time'=>time(),
					'status'=>0,
					'ip'=>$_SERVER['REMOTE_ADDR'],
			);		
			$last_uid=$this->user_mod->add($data);	
			$last_time=time();
			$key=md5($last_uid.$data['name'].$last_time);				
			cookie('user[id]',$last_uid,3600*24*7);
			cookie('user[name]',$data['name'],3600*24*7);
			cookie('user[login_time]',$last_time,3600*24*7);				
			cookie('user[key]',$key,3600*24*7);				
			$data=array(
					'type'=>'tao',
					'uid'=>$last_uid,
					'uname'=>$data['name'],
					'openid'=>$openid,
					'info'=>$tao_info,
			);			
			//增加加user_info表
			$user_info_data=array(
					'uid'=>$last_uid,
					'info'=>'这个人很懒，什么都没有留下'
			);
			$this->user_info->add($user_info_data);
			$this->user_openid_mod->add($data);
			header('Location:'.U('uc/sign'));exit;
		}
		//增加登录次数
        $this->user_mod->where('id='.$last_uid)->setInc('login_count',1);
		$_SESSION['login_type']='tao';
		header('Location:'.U('index/index'));exit;
		exit;
	}
}

?>