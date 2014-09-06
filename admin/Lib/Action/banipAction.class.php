<?php
//系统设置控制器
class banipAction extends baseAction {
	
	public function index() {			
		$setting_mod = M('setting');
		$res = $setting_mod->where("name='ban_ip'")->select();			
		$banip=$res[0]['data'];			
		$this->assign('banip',$banip);		
		$this->display();		
	}
	public function update(){		
		if (isset($_POST['send'])) {				
				$ban_content = $_POST['ban_content'];			
				$_data = str_replace('|', '', trim($ban_content));
				$_data = array_unique(explode("\r\n", $_data));								
				$ban_data = '';
				foreach($_data as $key=>$val){
					if(!$val)
						unset($_data[$key]);
					else
						$_arr = explode("-", trim($val));						
						@!$_arr[1] && $_arr[1] = $_arr[0];
						$ban_data[] = $_arr;
				}	
				$ip_data=serialize($ban_data);			
				if(!@file_put_contents('./data/banip_config_inc.php', $ip_data)){
					$this->success('修改失败', U('banip/index'));
				}					
				$setting_mod = M('setting');
				$setting_mod->where("name='ban_ip'")->save(array('data'=>$ban_content));								
				$this->success('修改成功', U('banip/index'));
		}
	}
}
?>