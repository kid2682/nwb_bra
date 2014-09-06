<?php
class user_userModel extends RelationModel{
    protected $tableName = 'user';
    protected $_link = array(
        'items' => array(
            'mapping_type'  => HAS_MANY ,
            'class_name'    => 'items',
            'foreign_key'   => 'id',
        ),
        
        'user_comments' => array(
            'mapping_type'  => HAS_MANY ,
            'class_name'    => 'user_comments',
            'foreign_key'   => 'id',
        ),	
        'user_info'=>array(
			'mapping_type'    =>HAS_ONE,
            'class_name'     =>'user_info',
			'foreign_key'=>'uid',
			'as_fields'=>'sex,qq,realname,alipay,jifenbao,brithday,address,blog,info,share_num,like_num,follow_num,fans_num,album_num,exchange_num,integral,money,constellation,job',		
         ),
    );
    public function get_user($id){
        $mod=D('user');
        return $mod->where('id='.$id)->find();
    }
}