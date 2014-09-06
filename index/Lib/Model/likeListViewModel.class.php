<?php
class likeListViewModel extends ViewModel{
	public $viewFields = array(

		'LikeList'=>array('items_id','uid','add_time'),

		'Items'=>array('title','img','url', '_on'=>'LikeList.items_id=Items.id'),

	);
}
