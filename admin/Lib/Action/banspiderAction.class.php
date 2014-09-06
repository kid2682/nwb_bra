<?php
//系统设置控制器
class banspiderAction extends baseAction {
	
	public function index() {	
		$banarray=array('baiduspider'=>'百度','googlebot'=>'谷歌','sogou'=>'搜狗','sosospider'=>'腾讯SOSO','slurp'=>'雅虎','youdaobot'=>'有道','bingbot'=>'Bing','msnbot'=>'MSN','ia_archiver'=>'Alexa');
		$setting_mod = M('setting');
		$res = $setting_mod->where("name='ban_sipder'")->select();			
		$banspider=explode('|',$res[0]['data']);			
		$this->assign('banspider',$banspider);
		$this->assign('banarray',$banarray);		
		$this->display();
		
	}
	public function update(){		
		if (isset($_POST['dosubmit'])) {				
				$ban_sipder = $_POST['ban_spider'];			
				$_data = $ban_sipder;						
				$ban_data = '';
				foreach($_data as $key=>$val){
					if(!$val)
						unset($_data[$key]);
					else
						$ban_data .= trim($val).'|';
				}
				$ban_data=substr($ban_data, 0,-1);			
				if(!$ban_data){
					$ban_data='';
				}
				$setting_mod = M('setting');
				$setting_mod->where("name='ban_sipder'")->save(array('data'=>$ban_data));										
				$this->success('修改成功', U('banspider/index'));
		}
	}	
}
?>