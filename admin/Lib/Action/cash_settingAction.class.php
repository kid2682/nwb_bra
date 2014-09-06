<?php
class cash_settingAction extends baseAction
{
	//显示采集数据的网址
	public function index()
	{
		$setting_mod = M('setting');
		if (isset($_POST['dosubmit'])) {
			$setting_mod = M('setting');
			foreach($this->_stripcslashes($_POST['site']) as $key=>$val ){
				$setting_mod->where("name='".$key."'")->save(array('data'=>$val));
			}
			$this->success('修改成功', U('cash_setting/index'));
		}
		$res = $setting_mod->where("name='is_cashback' OR name='cashback_rate' OR name='lowest_get_cash' OR name='lowest_get_jifen_cash' OR name='integralback_rate' OR name='cashback_type' OR name='tb_fanxian_name' OR name='tb_fanxian_unit' OR name='tb_fanxian_bili'")->select();
		foreach($res as $val )
		{
			$setting[$val['name']] = $val['data'];
		}
		$this->assign('set',$setting);
		$this->display();
	}
	
}
?>