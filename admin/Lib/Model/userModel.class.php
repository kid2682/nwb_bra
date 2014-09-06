<?php
class userModel extends RelationModel{
	protected $_link = array(
        'items_comments' => array(
            'mapping_type'  => HAS_MANY ,
            'class_name'    => 'items_comments',
            'foreign_key'   => 'id',
		),
		'user_info'=>array(
			'mapping_type'    =>HAS_ONE,
            'class_name'     =>'user_info',
			'foreign_key'=>'uid',
			'as_fields'=>'sex,qq,brithday,jifenbao,address,blog,info,share_num,like_num,follow_num,fans_num,album_num,exchange_num,integral,money,realname,alipay',		
         ),

	
	);
	public function get_user($id){
		$mod=D('user');
		return $mod->where('id='.$id)->find();
	}
	function get_list($pagesize=20){
		import("ORG.Util.Page");

		$mod=D('user');
		$where=" 1=1 ";
		if(isset($_REQUEST['keyword'])){
			$keys = $_REQUEST['keyword'];
			$where.=" and name like '%$keys%'";
		}

		$count = $mod->count();
		$p = new Page($count,$pagesize);

		$list=$mod->where($where)->order("last_time desc")->limit($p->firstRow.','.$p->listRows)->select();
		return array('list'=>$list,'page'=>$p->show(),'count'=>$count);
	}
}