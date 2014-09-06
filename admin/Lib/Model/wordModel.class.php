<?php
class wordModel extends RelationModel
{
	protected $_link=array(
	   'word_cate'=>array(
	       'mapping_type'  => BELONGS_TO,
	       'class_name'    => 'word_cate',
           'foreign_key'   => 'cid',
		   'as_fields'=>'name',
           
	   ),
	);   
}