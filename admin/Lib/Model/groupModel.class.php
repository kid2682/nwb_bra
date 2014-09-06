<?php
class groupModel extends Model
{	
	
	    // 自动填充设置
  protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function')
   );	
  public function check_title($title) {
    	
        $where = "title='$title'";      
        $id = $this->where($where)->getField('id');
        if ($id) {
        	//存在
            return false;
        } else {
        	//不存在
            return true;
        }
    }
}
?>