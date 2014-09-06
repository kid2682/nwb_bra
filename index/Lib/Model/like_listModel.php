<?php
class like_listModel extends RelationModel{
	protected $_link=array(
        'items'=>array(
	       'mapping_type'  => HAS_ONE,  
	       'class_name'=>'items',
	       'foreign_key'   => 'items_id',
	   ),
	);
}