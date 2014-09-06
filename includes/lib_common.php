<?php
//id=0表示是一级分类，$collect_site表示采集那个网站的数据     $recommend 为0表示选出所有不热门推荐的分类   为1表示选出所有分类
function get_items_cate_list($id=0,$level=0,$recommend=1,$collect_site='collect_taobao'){
	$items_cate_mod=D('items_cate');
	$list=array();
	$join="LEFT JOIN ".C('DB_PREFIX')."$collect_site as ct ON ct.cate_id=".C('DB_PREFIX')."items_cate.id";
	//获取一级分类
	if($recommend==1){	
		$res=$items_cate_mod->cache(true)->where("pid={$id} AND import_status=1 ")
			->join($join)
			->order("ordid ASC")->select();	
            
	}else{ //选出不热门推荐的分类
		$res=$items_cate_mod->cache(true)->where("pid={$id} AND import_status=1 AND status=1 ")
			->join($join)
			->order("ordid ASC")->select();
            
	}	
	foreach($res as $key=>$val){
		$val['level']=$level;
		$list[$val['id']]=$val;
		//二级分类
		if($recommend==1){
			$arr=$items_cate_mod->cache(true)
			->where('pid='.$val['id'])->join($join)
			->order("ordid ASC")->select();
		}else{ //选出不热门推荐的分类	
			$arr=$items_cate_mod->cache(true)
			->where("pid='{$val['id']}' AND status=1 ")->join($join)
			->order("ordid ASC")->select();	
		}
		//三级分类
		foreach($arr as $k2=>$v2){
			$v2['level']=$level+1;
			$v2['cls']="sub_".$val['id'];
			$list[$v2['id']]=$v2;
			//判断是否选出所有分类			
			$res3=$arr[$k2]['items']=$items_cate_mod->cache(true)
			->where('pid='.$v2['id'])
			->join($join)->order("ordid ASC")->select();		
			foreach($res3 as $k3=>$v3){
				$v3['level']=$level+2;
				$v3['cls']="sub_".$val['id']." sub_".$v2['id'];
				$list[$v3['id']]=$v3;
			}
		}
		$res[$key]['items']=$arr;
	}
	return array('list'=>$res,'sort_list'=>$list);
}