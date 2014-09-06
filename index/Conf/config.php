<?php
$config = require("config.inc.php");
$array = array(	
		'URL_MODEL' => 0,		
		'LOAD_EXT_CONFIG'=>'theme,html_suffix',
        //缓存配置
        'DATA_CACHE_TYPE' => 'file', // 数据缓存方式 文件
        'DATA_CACHE_TIME' => 0, // 数据缓存时间
        'DATA_CACHE_SUBDIR' => true,
        'DATA_PATH_LEVEL' => 2,
       // 'SHOW_PAGE_TRACE' => true, // 显示页面Trace信息

		//缓存时间调用
        'INDEX_GROUP_CATES' =>864000,//首页分组列表
        'TOP_ACTIVES'       =>60,    //首页热门活动列表缓存
        'LATELY_LIKE'       =>300,   //首页大家刚刚喜欢了
        'REC_SELLER'        =>864000,//首页返利商家
		

		'URL_REWIRTE_MODE_VAL'=>'1',  //U方法中是否使用自定义的U方法的函数
		'URL_PATHINFO_DEPR'=>'-',  //参数之间的分割符号
		//启用路由功能
		'URL_ROUTER_ON'=>true,
		//路由定义
		'URL_ROUTE_RULES'=>array(
            'item/:id\d'=>'item/index',
			//搜索页面
			'album/details'=>'album/details',
			'album/:cid\d'=>'album/index',
			'album'=>'album/index',
			//促销活动
			'promo/:cid\d/:pid\d'=>'promo/cate',
			'promo/:cid\d'=>'promo/cate',	
			'promo'=>'promo/index',
			'seller/:id\d'=>'seller/cate',
			'seller'=>'seller/index',	
			'cate/:cid\d/:sp\d/:p\d'=>'cate/index',		
            'cate/:cid\d'=>'cate/index',
			'article/:id\d'=>'article/index',
			'articlelist/:cid\d'=>'articlelist/index',
			'tag/:id\d'=>'cate/tag',
			
			
		),		

);
return array_merge($config, $array);