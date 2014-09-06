<?php
class apiMode extends Model{
	//获取商家分类信息	
	public function GetShopCats($miao_api){			
		$fields = 'cid,name,count';		
		$data = $miao_api->ListShopCatsGet($fields);
		$shop_cats = $data['shop_cats']['shop_cat'];		
		return $shop_cats;		
	}
}
?>