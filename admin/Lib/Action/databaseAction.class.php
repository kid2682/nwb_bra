<?php
class databaseAction extends baseAction{
	function execute(){
		$tables = $this->getAllTable();
        $this->assign('tables',$tables);
		$this->assign('db_name',C('DB_NAME'));

		$this->display();
	}
    private function getAllTable()
	{
		$tables_all = Db::getInstance()->getTables(); 
		$tables = array();
		foreach($tables_all as $table)
		{
			if(preg_match("/".C('DB_PREFIX')."/",$table))
				array_push($tables,$table);
		}
		return $tables;
	}
    function doExecute(){
        
		$sql  = trim($_REQUEST['sql']);
		$result = array('status'=>1,'info'=>0,'html'=>'');
		$db = Db::getInstance();
        if(is_string($sql))
		{
			$sql = str_replace("\r", '', $sql);
            $sql = explode(";\n",trim($sql));
        }
		
		$queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|TRUNCATE|REVOKE|LOCK|UNLOCK';
		
		$start_time = microtime(true);
		$count = 0;
		
        foreach($sql as $query)
		{
			$query = trim($query);
            if(!empty($query))
			{
				if (preg_match('/^\s*"?('.$queryIps.')\s+/i', $query))
				{
					$data = $db->execute($query);
					$type = 'execute';
				}
				else
				{
					$data = $db->query($query);
					$type = 'query';
				}
				
				if(false !== $data)
				{
					if($type == 'query' && !empty($data))
					{
						$count = count($data);
						$fields = array_keys($data[0]);
						$val_list = array();
						foreach($data as $key => $val)
						{
							$val  = array_values($val);
							$val_list[] = $val;
						}
						
						$this->assign('fields',$fields);
						$this->assign('val_list',$val_list);
						$result['html'] = $this->fetch('Database:table');
					}
				}
				
                if($db->getError() != "")
				{
					$result['status'] = 0;
					$this->assign('msg',$db->getError());
					$result['html'] = $this->fetch('Database:error');
                }
            }
        }
		
		$run_time = number_format((microtime(true) - $start_time),6);
		if($result['status'] == 1)
			$result['info'] = sprintf($count,$count,$run_time);	
		
		die(json_encode($result));
 
    }
	
	
	public function index()
	{
		$db_back_dir = ROOT_PATH."data/db_backup/";
		$sql_list = $this->dirFileInfo($db_back_dir);
		$this->assign("sql_list",$sql_list);
		$this->assign("name",gmtTime());
		$this->display();
	}
	
	public function dump()
	{
		$time = gmtTime();
		$name = empty($_REQUEST['sql_file_name']) ? gmtTime() : $_REQUEST['sql_file_name'];
		$tables = $this->getAllTable();	
		$_SESSION['dump_table_data'] = array(
			'file_dir'=>$name,
			'tables'=>$tables,
			'perfix'=>C('DB_PREFIX'),
			'time'=>$time,
		);
		
		$this->redirect('database/dumptable');
	}

	public function dumptable()
	{
		$table_data = $_SESSION['dump_table_data'];
		if(empty($table_data))
			$this->redirect('database/index');
		else
			$_SESSION['dump_table_data'] = $table_data;
			
		@set_time_limit(3600);
		
		if(function_exists('ini_set'))
		{
			ini_set('max_execution_time',3600);
			ini_set("memory_limit","256M");
		}

		$begin = isset($_REQUEST['begin']) ? intval($_REQUEST['begin']) : 0;
		$index = isset($_REQUEST['index']) ? intval($_REQUEST['index']) : 0;
		
		$back_dir = ROOT_PATH."data/db_backup/".$table_data['file_dir'].'/';

		if($index >= count($table_data['tables']))
		{
			$this->assign("tables",false);
			$this->display();
			ob_start();
			ob_end_flush(); 
			ob_implicit_flush(1);
			
			unset($_SESSION['dump_table_data']);
			echoFlush('<script type="text/javascript">showmessage(\''.L('DUMP_SUCCESS').'\',3);</script>');
			exit;
		}

		$table = $table_data['tables'][$index];
		$table_vars = array(
			'count'=>count($table_data['tables']),
			"index"=>$index + 1,
			"name"=>$table);

		$this->assign("tables",$table_vars);
		$this->display();
		
		ob_start();
		ob_end_flush(); 
		ob_implicit_flush(1);

		if($index == 0)
		{
			mk_dir($back_dir);
            $table_data = '$table_data = '.var_export($table_data, true).";";
			$db_table_file = $back_dir."tables.php";
			@file_put_contents($db_table_file,"<?php\n$table_data\n?>");
		}

		$tbname = 	str_replace(C('DB_PREFIX'),'%DB_PREFIX%',$table);
		$modelname = str_replace(C('DB_PREFIX'),'',$table);
		$table_dir = $back_dir.$modelname.'/';
		mk_dir($table_dir);
		$modelname = parse_name($modelname,1);
		$model=D($modelname);

		$data_num = $model->count();
		$dumpsql_vol = '';
		
		if($begin == 0)
		{
			 $sql_file_path = $table_dir."table.sql";
			 $dumpsql_vol .= "DROP TABLE IF EXISTS `$tbname`;\r\n";  //用于表结构导出处理的Sql语句
		 	 $tmp_arr = M()->query("SHOW CREATE TABLE `$table`");
		     $tmp_sql = $tmp_arr[0]['Create Table'].";\r\n";
		     $tmp_sql  = str_replace(C('DB_PREFIX'),'%DB_PREFIX%',$tmp_sql);
			 $dumpsql_vol .= $tmp_sql;   //表结构语句处理结束
			 if(@file_put_contents($sql_file_path,$dumpsql_vol) === false)
			 {
				echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DUMP_TIPS2'),$table,U('DataBase/dumptable',array('index'=>$index,'begin'=>$begin))).'\',-1);</script>');
				exit;
			 }
			 else
			 {
				echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DUMP_TIPS3'),$table).'\',1);</script>');
			 }
		}

		if($data_num > $begin)
		{
			$sql_file_path = $table_dir.$begin.".sql";
			$dumpsql_vol = '';
			$limit = $data_num - $begin;
            if($limit > 5000)
                $limit = 5000;

			echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DUMP_TIPS4'),$table,$begin,$begin + $limit).'\',1);</script>');

			$data_list=$model->limit($begin.','.$limit)->findAll();
			foreach($data_list as $data_row)
			{
				 $dumpsql_row = "INSERT INTO `{$tbname}` VALUES (";   //用于每行数据插入的SQL脚本语句
				 foreach($data_row as $col_value)
				 {
				   $dumpsql_row .="'".mysql_real_escape_string($col_value)."',";
				 }
				 $dumpsql_row=substr($dumpsql_row,0,-1);  //删除最后一个逗号
				 $dumpsql_row .= ");\r\n";
				 $dumpsql_vol.= $dumpsql_row;
			}

			if(@file_put_contents($sql_file_path,$dumpsql_vol) === false)
			{
				echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DUMP_TIPS5'),$table,$begin,$begin + $limit,U('DataBase/dumptable',array('index'=>$index,'begin'=>$begin))).'\',-1);</script>');
				exit;
			}
			else
			{
				if($limit < 5000)
				{
					echoFlush('<script type="text/javascript">showmessage(\''.U('DataBase/dumptable',array('index'=>$index + 1,'begin'=>0)).'\',2);</script>');
    				exit;
				}
				else
				{
					echoFlush('<script type="text/javascript">showmessage(\''.U('DataBase/dumptable',array('index'=>$index,'begin'=>$begin + $limit)).'\',2);</script>');
					exit;
				}
			}
		}
		else
		{
			echoFlush('<script type="text/javascript">showmessage(\''.U('DataBase/dumptable',array('index'=>$index + 1,'begin'=>0)).'\',2);</script>');
    		exit;
		}
	}

	public function delete()
	{
		$dir = $_REQUEST['dir'];
		if(empty($dir))
			exit;
		$_SESSION['delete_table_dir'] = $dir;
		$this->redirect('DataBase/deletetable');
	}

	public function deletetable()
	{
		$name = $_SESSION['delete_table_dir'];
		if(empty($name))
			$this->redirect('DataBase/index');
		else
			$_SESSION['delete_table_dir'] = $name;
			
		@set_time_limit(3600);
		if(function_exists('ini_set'))
		{
			ini_set('max_execution_time',3600);
			ini_set("memory_limit","256M");
		}

		$this->display();
		ob_start();
		ob_end_flush(); 
		ob_implicit_flush(1);
		
		echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DELETE_TIPS1'),$name).'\',1);</script>');
		
		$dir = FANWE_ROOT."public/db_backup/".$name.'/';
		$dirhandle=opendir($dir);
		while(($file = readdir($dirhandle)) !== false)
		{
			if(($file!=".") && ($file!=".."))
			{
				if(is_dir($dir.$file))
				{
					echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DELETE_TIPS2'),$file).'\',1);</script>');
                    usleep(10);
					$this->clearSqlDir($dir.$file.'/',$file);
					@rmdir($dir.$file.'/');
				}
				else
				{
					@unlink($dir.$file);
				}
			}
		}

		@closedir($dirhandle);
		usleep(10);
		@rmdir($dir);
		echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DELETE_TIPS4'),$name).'\',3);</script>');
		exit;
	}

	public function restore1()
	{
		$dir = $_REQUEST['dir'];
		if(empty($dir))
			exit;
		$_SESSION['restore_table_dir'] = $dir;
		$this->redirect('DataBase/restoretable');
	}

	public function restoretable()
	{
		$restore_table_dir = $_SESSION['restore_table_dir'];
		$back_dir = FANWE_ROOT."public/db_backup/".$restore_table_dir.'/';
		if(!@include($back_dir."tables.php"))
			$this->redirect('DataBase/index');
		else
			$_SESSION['restore_table_dir'] = $restore_table_dir;
			
		@set_time_limit(3600);
		if(function_exists('ini_set'))
		{
			ini_set('max_execution_time',3600);
			ini_set("memory_limit","256M");
		}

		$begin = isset($_REQUEST['begin']) ? intval($_REQUEST['begin']) : 0;
		$index = isset($_REQUEST['index']) ? intval($_REQUEST['index']) : 0;
		
		$this->assign("restore_tips",sprintf(L('RESTORE_TIPS0'),U('DataBase/restoretable',array('index'=>$index,'begin'=>0))));

		if($index >= count($table_data['tables']))
		{
			$this->assign("tables",false);
			$this->display();
			ob_start();
			ob_end_flush(); 
			ob_implicit_flush(1);

			echoFlush('<script type="text/javascript">showmessage(\''.L('RESTORE_SUCCESS').'\',3);</script>');
			exit;
		}

		$table = $table_data['tables'][$index];
		$table = str_replace($table_data['perfix'],'',$table);
		$table_dir = $back_dir.$table.'/';

		$table_vars = array(
			'count'=>count($table_data['tables']),
			"index"=>$index + 1,
			"name"=>$table);

		$this->assign("tables",$table_vars);
		$this->display();
		ob_start();
		ob_end_flush(); 
		ob_implicit_flush(1);
		
		if(!file_exists($table_dir.'table.sql'))
		{
			echoFlush('<script type="text/javascript">showmessage(\''.U('DataBase/restoretable',array('index'=>$index + 1,'begin'=>0)).'\',2);</script>');
    		exit;
		}

		$db = Db::getInstance();

		if($begin == 0)
		{
			 $sql = @file_get_contents($table_dir.'table.sql');
			 $sql = str_replace("\r", '', $sql);
			 $segmentSql = explode(";\n", $sql);

			 foreach($segmentSql as $itemSql)
			 {
				 $itemSql = trim($itemSql);
				 if(empty($itemSql))
				 	continue;

				 $itemSql = str_replace("%DB_PREFIX%",C('DB_PREFIX'),$itemSql);
				 $db->query($itemSql);
				 if($db->getError() != "")
				 {
					 echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('RESTORE_TIPS2'),$table,U('DataBase/restoretable',array('index'=>$index,'begin'=>0))).'\',-1);</script>');
					 exit;
				 }
			 }

			 echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('RESTORE_TIPS3'),$table).'\',1);</script>');
		}

		if(file_exists($table_dir.$begin.'.sql'))
		{
			$limit = 5000;
			echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('RESTORE_TIPS4'),$table,$begin,($begin + $limit)).'\',1);</script>');

			$sql = @file_get_contents($table_dir.$begin.'.sql');
			$sql = str_replace("\r", '', $sql);
			$segmentSql = explode(";\n", $sql);
			$sql_index = 0;
			foreach($segmentSql as $itemSql)
			{
				$sql_index++;
				
				if(!empty($itemSql))
				{
					$itemSql = str_replace("%DB_PREFIX%",C('DB_PREFIX'),$itemSql);
					$db->query($itemSql);
					if($db->getError() != "")
					{
						echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('RESTORE_TIPS5'),$table,$sql_index,U('DataBase/restoretable',array('index'=>$index,'begin'=>0))).'\',-1);</script>');
					 	exit;
					}
				}
			}
			
			echoFlush('<script type="text/javascript">showmessage(\''.U('DataBase/restoretable',array('index'=>$index,'begin'=>$begin + $limit)).'\',2);</script>');
			exit;
		}
		else
		{
			echoFlush('<script type="text/javascript">showmessage(\''.U('DataBase/restoretable',array('index'=>$index + 1,'begin'=>0)).'\',2);</script>');
    		exit;
		}
	}
	/*
	private function getAllTable()
	{
		$tables_all = Db::getInstance()->getTables(); 
		$tables = array();
		foreach($tables_all as $table)
		{
			if(preg_match("/".C('DB_PREFIX')."/",$table))
				array_push($tables,$table);
		}
		return $tables;
	}
	*/
	private function dirFileInfo($dir)
	{
		if(!is_dir($dir))
			return false;
		
		$dirhandle=opendir($dir);
		$list=array();
		while(($file = readdir($dirhandle)) !== false)
		{
			if(($file!=".") && ($file!="..") && is_dir($dir.$file) && file_exists($dir.$file.'/tables.php'))
			{
				include $dir.$file.'/tables.php';
				$list[]=array(
					'filename'=>$table_data['file_dir'],
					'filetime'=>$table_data['time'],
					'filedate'=>toDate($table_data['time'])
				);
			}
		}
		@closedir($dirhandle);
		usort($list,fileSort);
		return $list;
   }
   
	private function clearSqlDir($dir,$name)
	{
			$dirhandle=opendir($dir);
			while(($file = readdir($dirhandle)) !== false)
			{
				if(($file!=".") && ($file!=".."))
				{
					echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DELETE_TIPS3'),$name,$file).'\',1);</script>');
					usleep(10);
					@unlink($dir.$file);
				}
			}
			@closedir($dirhandle);
	}	

	function fileSort($a, $b)
	{
		if ($a['filetime'] == $a['filetime'])
	        return 0;
	
	    return ($a['filetime'] < $a['filetime']) ? 1 : -1;
	}
    /*
    *   数据库备份
    */
    function backup(){
        $obj  = $this->getConfig();
        $database = $obj->table();
       	foreach($database as $v){
       	    $options .="<option value='{$v}'>{$v}</option>";
       	}
        $this->assign("options",$options);
 
        $this->display();
    }
    function doBackup(){
        set_time_limit(0);
        $obj  = $this->getConfig();
        if (!$table = $obj->table()) {     
            $this->error("读数据库结构错误");
            exit; 
	    }
        if ($_POST ['bfzl'] == "quanbubiao") { 
    
            if (!$_POST ['fenjuan']) {

			    $sql = "";
                foreach($table as $tabName){
                    
                    $sql .= $obj->make_header ( $tabName );
                    $fields = $obj->fields($tabName);
                    foreach($fields as $data){
                        $sql .= $obj->make_insert($tabName,$data);
                    }
                   
                }
    			
			    $filename = date ( "Ymd", time () ) . "_all.sql";
				if ($this->write_file ( $sql, $filename ))
					$this->success("全部数据表数据备份完成,生成备份文件'./backup/$filename'");
				else
                    $this->error("备份全部数据表失败");
				exit;
			}else { 
    			if (!$_POST ['filesize'] ) {
                    $this->error("请填写备份文件分卷大小");
    				exit;
    			}
                
    			$sql = "";
    			$p = 1;
    			$filename = date ( "Ymd", time () ) . "_all";
                
                foreach($table as $tabName){
                    $sql .= $obj->make_header ( $tabName );
                    $fields = $obj->fields ( $tabName );  
                    foreach($fields as $data){
                        $sql .= $obj->make_insert ( $tabName, $data );
                        if (strlen ( $sql ) >= $_POST ['filesize'] * 1000) {
    						$filename .= ("_v" . $p . ".sql");
                            if($this->write_file ( $sql, $filename )){
    						    echo "全部数据表-卷-{$p}-数据备份完成,生成备份文件/backup/{$filename}</br>";
    						}else{
    						    echo "全部数据表-卷-{$p}-数据备份失败</br>";
    						}
                            $p ++;
    						$filename = date ( "Ymd", time () ) . "_all";
    						$sql = "";
                        }
                        
  					}
                    
                }                    

                if ($sql != "") {
    				$filename .= ("_v" . $p . ".sql");
                    if($this->write_file ( $sql, $filename )){
    				    $this->success("全部数据表-卷-" . $p . "-数据备份完成,生成备份文件'./backup/$filename'");
    			    }
                }
            } 
	   }elseif ($_POST ['bfzl'] == "danbiao") { 
	       
            if(!$_POST['tablename']){
                $this->error("单表数据备份,必须选择表名");
            }
            if (!$_POST ['fenjuan']) {

			    $sql = "";
                
                $sql .= $obj->make_header ( $_POST['tablename'] );
                $fields = $obj->fields($_POST['tablename']);
                
                foreach($fields as $data){
                    $sql .= $obj->make_insert($_POST['tablename'],$data);
                }
                        
			    $filename = date ( "Ymd", time () ) . "_" . $_POST ['tablename'] . ".sql";
				if ($this->write_file ( $sql, $filename ))
					$this->success("{$_POST['tablename']}表数据备份完成,生成备份文件'./backup/$filename'");
				else
                    $this->error("备份{$_POST['tablename']}表失败");
				exit;
			}else { 
    			if (!$_POST ['filesize'] ) {
                    $this->error("请填写备份文件分卷大小");
    				exit;
    			}
                
    			$sql = "";
    			$p = 1;
    			$filename = date ( "Ymd", time () ) . "_" . $_POST ['tablename'];
                
 
                $sql .= $obj->make_header ( $_POST['tablename'] );
                $fields = $obj->fields ( $_POST['tablename'] );  
                foreach($fields as $data){
                    $sql .= $obj->make_insert ( $_POST['tablename'], $data );
                    if (strlen ( $sql ) >= $_POST ['filesize'] * 1000) {
    			        $filename .= ("_v" . $p . ".sql");
                        if($this->write_file ( $sql, $filename )){
    					    echo "全部数据表-卷-{$p}-数据备份完成,生成备份文件/backup/{$filename}</br>";
    					}else{
    					    echo "全部数据表-卷-{$p}-数据备份失败</br>";
    					}
                        $p ++;
    					$filename = date ( "Ymd", time () ) . "_" . $_POST ['tablename'];
    					$sql = "";
                    }
                        
	            }

                if ($sql != "") {
    				$filename .= ("_v" . $p . ".sql");
                    if($this->write_file ( $sql, $filename )){
    				    $this->success("全部数据表-卷-" . $p . "-数据备份完成,生成备份文件'./backup/$filename'");
    			    }
                }
            } 
	   }		
    }
		
    function write_file($sql, $filename) {

        $obj = $this->getConfig();
        $dir = "./backup";
        $obj->createDir($dir);
    	$re = true;
    	if (! @$fp = fopen ( "./backup/" . $filename, "w+" )) {
    		$re = false;
    		echo "failed to open target file";
    	}
    	if (! @fwrite ( $fp, $sql )) {
    		$re = false;
    		echo "failed to write file";
    	}
    	if (! @fclose ( $fp )) {
    		$re = false;
    		echo "failed to close target file";
    	}
    	return $re;
    }
    function down_file($sql, $filename) {
    	ob_end_clean ();
    	header ( "Content-Encoding: none" );
    	header ( "Content-Type: " . (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' ) ? 'application/octetstream' : 'application/octet-stream') );
    	header ( "Content-Disposition: " . (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' ) ? 'inline; ' : 'attachment; ') . "filename=" . $filename );
    	header ( "Content-Length: " . strlen ( $sql ) );
    	header ( "Pragma: no-cache" );
    	header ( "Expires: 0" );
    	echo $sql;
    	$e = ob_get_contents ();
    	ob_end_clean ();
    }

    /*
    *   数据库还原
    */
    function restore(){
        $obj  = $this->getConfig();
        $dir = $obj->readDir();
        $this->assign('dir',$dir);
        $this->display();
    }
    function doRestore(){
        set_time_limit(0);
        $obj  = $this->getConfig();
        //从服务器恢复
        $path = ".".DIRECTORY_SEPARATOR."backup".DIRECTORY_SEPARATOR;
        if ($_POST ['restorefrom'] == "server") {
            if (! $_POST ['serverfile']) {
        	   $this->error("您选择从服务器文件恢复备份，但没有指定备份文件");
        	   exit;
    	    }
      		if (!preg_match ( "/_v[0-9]+/", $_POST ['serverfile'] )) {	
        		$filename = $path . $_POST ['serverfile'];
        
       			if ($obj->execute($filename))
        			$this->success("备份文件" . $_POST ['serverfile'] . "成功导入数据库");
        		else
        			$this->error("备份文件" . $_POST ['serverfile'] . "导入失败");
       			exit;
      		} else {

                $filename = $path . $_POST ['serverfile'];
                if ($obj->executeCollBack($filename)){
     			    $this->success("全部导入成功");
          		}else {
            		$this->error("导入失败");
           			exit;
          		}
         
      		}
        }
        
    }
    /*
    *   导入back类
    */
    function getConfig(){
        $host = C('DB_HOST');
        $dbuser = C('DB_USER');
        $dbpwd = C('DB_PWD');
        $dbname = C('DB_NAME');
        import("ORG.Util.Back"); 
        $obj = new Back($host,$dbuser,$dbpwd,$dbname);
        return $obj;
    }
    
  
}