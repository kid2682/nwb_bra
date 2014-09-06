<?php
class Back {
	public $dbhost;
	public $dbuser;
	public $dbpwd;
	public $dbname;
	public $mylsqi;
	public function __construct($dbhost,$dbuser,$dbpwd,$dbname){
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpwd  = $dbpwd;
		$this->dbname = $dbname;
		$this->connect();
	}
	public function connect(){
		$this->mysqli = new mysqli($this->dbhost,$this->dbuser,$this->dbpwd,$this->dbname);
        $this->mysqli->query("SET NAMES 'UTF8'");
		return $this->mysqli;
	}
	public function query($sql){
		return $this->mysqli->query($sql);
	}
	//获取数据表明
	public function table(){
		$tables = $this->mysqli->query("show table status from {$this->dbname}");
		$table = array();
		while ($row = $tables->fetch_assoc()){
			$table[]=$row['Name'];
		}
		return $table;
	}
	//获取指定表内所有数据
	public function fields($table){
		$result = $this->mysqli->query("select * from {$table}");
		$data = array();
		while($row = $result->fetch_assoc()){
			$data[]=$row;
		}
		return $data;
	}
	//组装INSERT数据
	public function make_insert($table,$data) {
		foreach($data as $v){
			$fields_data .= "'".mysql_escape_string($v)."',";
		}
		$field = trim($fields_data,',');
		$sql = "INSERT INTO `".$table."` VALUES(".$field.")\n";
		return $sql;
	}
	//制造字段信息和删除表信息
	public function make_header($table){
		$sql = "DROP TABLE IF EXISTS " . $table . "\n";
		$result = $this->mysqli->query ( "show create table " . $table );
		$create = $result->fetch_assoc();
		$create_table = $create["Create Table"];
		$tmp = preg_replace ( "/\n/", "", $create_table );
		$sql .= $tmp . "\n";
		return $sql;
	}
    //恢复数据
    public function execute($filename){
        $sql = file($filename);
        foreach($sql as $v){  
            
            if(!$this->query($v))
                return false;
        }
        return true;
    }
    //回调数据方法
    public function executeCollBack($fileName){
        if(file_exists($fileName)){
            if($this->execute($fileName)){        
                preg_match("/_v(\d)/",$fileName,$vs);
                $v = "_v".++$vs[1];
                $nextFileName = preg_replace("/_v\d/",$v,$fileName);
                sleep ( 1 );
                $this->executeCollBack($nextFileName);
            }    
        }
        return true;
    }
    //读取备份文件目录
    function readDir($path='./backup'){
        $handle = opendir ($path);
       	while ( $file = readdir ( $handle ) ) {
      		if (preg_match( "/^[0-9]{8,8}([0-9a-z_]+)(\.sql)$/", $file ))
     			$dir .= "<option value='$file'>$file</option>";
       	}
       	closedir ( $handle );
        return $dir;
    }
    //将数据写入文件
    function writeFile($sql, $filename) {
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
	//创建目录
	public function createDir($dir){
		if(!is_dir($dir)){
			@mkdir ( $dir, 0777 );
		}
		if(is_dir($dir)){
			return true;
		}else{
			return false;
		}
	}
	public function __destruct(){
		$this->mysqli->close();
	}
}
?>
