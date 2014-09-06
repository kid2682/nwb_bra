<?php
class userViewModel extends ViewModel {
     public $viewFields = array(

	 'User'=>array('id','name','passwd','email','ip','add_time','status','last_time','last_ip'),

	 'UserInfo'=>array( '_on'=>'User.id=UserInfo.uid','sex','brithday','address','blog','info','share_num','like_num','follow_num','fans_num','album_num','exchange_num','integral','money','constellation','job','qq'),

	 );

	 public function updateUser($data){
		if(M("user")->save($data))
			return true;
	 }
	 public function updateInfo($data){
		if(M("userInfo")->save($data))
			return true;
	 }

}