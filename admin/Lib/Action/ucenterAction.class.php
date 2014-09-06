<?php

class ucenterAction extends baseAction
{	
	function setucenter()
	{
	    $config = array(
        'dbhost' => "'".C('DB_HOST')."';",	
        'dbuser' => "'".C("DB_USER")."';",
        'dbpw' => "'".C("DB_PWD")."';",
        'dbname' => "'".C("DB_NAME")."';",
        'pconnect' => "0;",
        'tablepre' => "'`".C("DB_NAME")."`.".C("DB_PREFIX")."';",
        'dbcharset' => "'utf8';",
        'cookiedomain' => "'';",
        'cookiepath' => "'/';"
        );

		$ucenter_mod = M('ucenter');
		$list = $ucenter_mod->select();
        foreach($list as $k=>$v){
            $info[$v['name']]=$v['value'];
            $info['config'] .= "define('UC_".strtoupper($v['name'])."','".$v['value']."');\n";
        }
        $this->assign("set",$info);
        foreach($config as $k=>$v){
            $info['config'] .= "$".$k."=".$v."\n";
        }
        $new_config = "<?php ".$info['config']."?>";
        $uc_config_path = ROOT_PATH."/uc_client/config.inc.php";
        file_put_contents($uc_config_path,$new_config);
		$this->display();
	}
    
    function doEdit(){
        
        if($_POST['dosubmit']){
            $model = M("ucenter");
            foreach($_POST["uc_setings"] as $k=>$v){
                $map['name'] = $k;
                $info = $model->where($map)->find();
                if($info){
                    $r=$model->where($map)->setField("value",$v);
                }else{
                    $r=$model->add(array("name"=>"{$k}","value"=>"{$v}"));    
                }
            }
            $this->success('提交成功');
        }
        
    }
}
?>