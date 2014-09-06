<?php

class settingAction extends baseAction
{
	function index()
	{   
		$this->assign('set',$this->setting);
		$this->display($_REQUEST['type']);
	}
	function edit()
	{
		$setting_mod = M('setting');
		foreach($this->_stripcslashes($_POST['site']) as $key=>$val ){
			$setting_mod->where("name='".$key."'")->save(array('data'=>$val));
		}
		if ($_FILES['img']['name']!='') {
			$upload_list = $this->_upload('setting');
			$data['data'] = $upload_list;
			$setting_mod->where("name='site_logo'")->save($data);			
		}		
		$this->success('修改成功',U('setting/index'));
	}   
    function msg(){
        $result = M('UserSetmsg')->select();
        foreach($result as $k=>$v){
            $arr[$v['key']] = $v['val'];
        }
        $this->assign('arr',$arr);
        $this->display();
    }
    function doMsgEdit(){
        unset($_POST['Submit']);
        $model = M("user_setmsg");
        foreach($_POST as $k=>$v){
            $map['key'] = $k;
            $info = $model->where($map)->find();
            if($info){
                $r=$model->where($map)->setField("val",$v);
            }else{
                $r=$model->add(array("id"=>null,"key"=>"{$k}","val"=>"{$v}"));    
            }
        }
        $this->success('提交成功');
    }   
}
?>