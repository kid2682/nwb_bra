<?php
class items_collectAction extends baseAction
{
	//显示采集数据的网址
	public function index()
	{
		
		$items_site_mod = D('items_site');
		import("ORG.Util.Page");
		$count = $items_site_mod->count();
		$p = new Page($count,20);
		$sites_list = $items_site_mod->limit($p->firstRow.','.$p->listRows)->select();
		$key = 1;
		foreach($sites_list as $k=>$val){
			$sites_list[$k]['key'] = ++$p->firstRow;
		}
		$page = $p->show();
		$this->assign('page', $page);
		$this->assign('sites_list', $sites_list);
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=items_collect&a=add\', title:\'添加来源\', width:\'500\', height:\'250\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', '添加来源');
		$this->assign('big_menu',$big_menu);
		//查看是否授权		
		$result=$this->getSign();
		$this->assign('shouquan',$result);
		$this->display();
	}
	public function add()
	{
		if (isset($_POST['dosubmit'])) {
			$data['name'] = isset($_POST['name']) && trim($_POST['name']) ? trim($_POST['name']) : $this->error('请填写来源名称');
			$data['alias'] = isset($_POST['alias']) && trim($_POST['alias']) ? trim($_POST['alias']) : $this->error('请填写唯一标识');
			$data['site_domain'] = isset($_POST['site_domain']) && trim($_POST['site_domain']) ? trim($_POST['site_domain']) : $this->error('请填写网站域名');
			$data['collect_url'] = isset($_POST['collect_url']) && trim($_POST['collect_url']) ? trim($_POST['collect_url']) : '';
			$data['type'] = isset($_POST['type']) && intval($_POST['type']) ? 1 : 0;
			if ($_FILES['site_logo']['name']!='') {
				$upload_list = $this->_upload($_FILES['site_logo']);
				$data['site_logo'] = $upload_list['0']['savename'];
			} else {
				$this->error('请上传网站LOGO');
			}
			$items_site_mod = D('items_site');
			$result = $items_site_mod->add($data);
			if($result){
				$this->success(L('operation_success'), '', '', 'add');
			}else{
				$this->error(L('operation_failure'));
			}
		}
		$this->display();
	}

	public function edit()
	{
		$items_site_mod = D('items_site');
		if (isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) && intval($_POST['id']) ? intval($_POST['id']) : $this->error('参数错误');
			$data['name'] = isset($_POST['name']) && trim($_POST['name']) ? trim($_POST['name']) : $this->error('请填写来源名称');
			$data['alias'] = isset($_POST['alias']) && trim($_POST['alias']) ? trim($_POST['alias']) : $this->error('请填写唯一标识');
			$data['site_domain'] = isset($_POST['site_domain']) && trim($_POST['site_domain']) ? trim($_POST['site_domain']) : $this->error('请填写网站域名');
			$data['collect_url'] = isset($_POST['collect_url']) && trim($_POST['collect_url']) ? trim($_POST['collect_url']) : '';
			$data['type'] = isset($_POST['type']) && intval($_POST['type']) ? 1 : 0;
			if ($_FILES['site_logo']['name']!='') {
				$upload_list = $this->_upload($_FILES['site_logo']);
				$data['site_logo'] = $upload_list['0']['savename'];
			}
			$result = $items_site_mod->where('id='.$id)->save($data);
			if(false !== $result){
				$this->success(L('operation_success'), '', '', 'edit');
			}else{
				$this->error(L('operation_failure'));
			}
		}
		$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error('参数错误');
		$site_info = $items_site_mod->where('id='.$id)->find();
		$this->assign('site_info', $site_info);
		$this->display();
	}

	public function delete()
	{
		if((!isset($_GET['id']) || empty($_GET['id'])) && (!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要删除的商品！');
		}
		$items_site_mod = D('items_site');
		if( isset($_POST['id'])&&is_array($_POST['id']) ){
			$ids = implode(',',$_POST['id']);
			$items_site_mod->delete($ids);
		}else{
			$id = intval($_GET['id']);
			$items_site_mod->where('id='.$id)->delete();
		}
		$this->success(L('operation_success'));
	}

	public function _upload($file)
	{
		import("ORG.Net.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200;
		$upload->allowExts = explode(',', 'jpg,gif,png,jpeg');
		$upload->savePath = './data/author/';
		$upload->thumb = true;
		$upload->imageClassPath = 'ORG.Util.Image';
		$upload->thumbPrefix = '32_,120_';
		$upload->thumbMaxWidth = '32,120';
		$upload->thumbMaxHeight = '32,120';
		$upload->saveRule = uniqid;
		$upload->thumbRemoveOrigin = true;

		if (!$upload->uploadOne($file)) {
			//捕获上传异常
			$this->error($upload->getErrorMsg());
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
		}
		return $uploadList;
	}	
	//设置淘宝api
	public function taobaoapi()
	{
		$setting_mod = M('setting');
		if (isset($_POST['dosubmit'])) {
			$taobao['taobao_usernick'] = isset($_POST['usernick']) && trim($_POST['usernick']) ? trim($_POST['usernick']) : $this->error('请填写帐号');
			$taobao['taobao_pid'] = isset($_POST['pid']) && trim($_POST['pid']) ? trim($_POST['pid']) : $this->error('请填写pid');
			$taobao['taobao_appkey'] = isset($_POST['appkey']) && trim($_POST['appkey']) ? trim($_POST['appkey']) : $this->error('请填写appkey');
			$taobao['taobao_appsecret'] = isset($_POST['appsecret']) && trim($_POST['appsecret']) ? trim($_POST['appsecret']) : $this->error('请填写appsecret');
			$taobao['commission_rate_min'] = isset($_POST['commission_rate_min']) && trim($_POST['commission_rate_min']) ? trim($_POST['commission_rate_min']) : $this->error('请填写佣金比例');
			$taobao['commission_rate_max'] = isset($_POST['commission_rate_max']) && trim($_POST['commission_rate_max']) ? trim($_POST['commission_rate_max']) : $this->error('请填写佣金比例');
			$taobao['levelstart'] = isset($_POST['levelstart']) && trim($_POST['levelstart']) ? trim($_POST['levelstart']) : $this->error('请选择店铺等级');
			$taobao['levelend'] = isset($_POST['levelend']) && trim($_POST['levelend']) ? trim($_POST['levelend']) : $this->error('请选择店铺等级');
			$taobao['tao_collect_set'] = isset($_POST['tao_collect_set']) && trim($_POST['tao_collect_set']) ? trim($_POST['tao_collect_set']) : '0';
			$taobao['taobao_search_pid'] = isset($_POST['taobao_search_pid']) && trim($_POST['taobao_search_pid']) ? trim($_POST['taobao_search_pid']) : '0';
			foreach( $taobao as $key=>$val ){
				$setting_mod->where("name='".$key."'")->save(array('data'=>$val));
			}
			$this->success('修改成功', U('items_collect/taobaoapi'));
		}
		$res = $setting_mod->where("name='taobao_usernick' OR name='taobao_pid' OR name='taobao_appkey' OR name='taobao_appsecret' OR name='commission_rate_min' OR name='commission_rate_max' OR name='levelstart' OR name='levelend' OR name='tao_collect_set' OR name='taobao_search_pid'")->select();
		foreach( $res as $val )
		{
			$taobaoset[$val['name']] = $val['data'];
		}
		$this->assign('taobao',$taobaoset);
		$this->display();
	}
	//微购api
	public function wegoapi()
	{
		$setting_mod = M('wegoapi');
		if (isset($_POST['dosubmit'])) {
			$taobao['username'] = isset($_POST['username']) && trim($_POST['username']) ? trim($_POST['username']) : '';
			$taobao['password'] = isset($_POST['password']) && trim($_POST['password']) ? trim($_POST['password']):'';
			$taobao['weburl'] = isset($_POST['weburl']) && trim($_POST['weburl']) ? trim($_POST['weburl']) : '';
			$taobao['token'] = isset($_POST['token']) && trim($_POST['token']) ? trim($_POST['token']) : '';
			$taobao['commission_rate_min'] = isset($_POST['commission_rate_min']) && trim($_POST['commission_rate_min']) ? trim($_POST['commission_rate_min']) :'';
			$taobao['commission_rate_max'] = isset($_POST['commission_rate_max']) && trim($_POST['commission_rate_max']) ? trim($_POST['commission_rate_max']) :'';
			
			$taobao['commission_rate_max'] = isset($_POST['commission_rate_max']) && trim($_POST['commission_rate_max']) ? trim($_POST['commission_rate_max']) :'';
			$taobao['price_min'] = isset($_POST['price_min']) && trim($_POST['price_min']) ? trim($_POST['price_min']) :'';
			$taobao['price_max'] = isset($_POST['price_max']) && trim($_POST['price_max']) ? trim($_POST['price_max']) :'';
			
			$taobao['levelstart'] = isset($_POST['levelstart']) && trim($_POST['levelstart']) ? trim($_POST['levelstart']) : '';
			$taobao['levelend'] = isset($_POST['levelend']) && trim($_POST['levelend']) ? trim($_POST['levelend']) :'';
			$taobao['tao_collect_set'] = isset($_POST['tao_collect_set']) && trim($_POST['tao_collect_set']) ? trim($_POST['tao_collect_set']) : '0';
			
			$taobao['sign'] = isset($_POST['sign']) && trim($_POST['sign']) ? trim($_POST['sign']) : '0';
			
			$taobao['order'] = isset($_POST['order']) && trim($_POST['order']) ? trim($_POST['order']) : '0';
			foreach( $taobao as $key=>$val ){
				$setting_mod->where("name='".$key."'")->save(array('data'=>$val));
			}
			$this->success('修改成功', U('items_collect/wegoapi'));
			
		}
		$res = $setting_mod->where("name='username' OR name='password' OR name='weburl' OR name='token' OR name='commission_rate_min' OR name='commission_rate_max' OR name='levelstart' OR name='levelend' OR name='tao_collect_set' OR name='order' OR name='price_min' OR name='price_max' OR name='sign'")->select();
		
		foreach( $res as $val )
		{
			$wegoapi[$val['name']] = $val['data'];
		}
		//print_r($taobaoset);
		$this->assign('wegoapi',$wegoapi);
		$this->display();
	}
	
	
	//获取淘宝授权
	public function author_tao(){
		$setting_mod = M('setting');
		if (isset($_POST['dosubmit'])){
			$taobao['tao_session'] = isset($_POST['tao_session']) && trim($_POST['tao_session']) ? trim($_POST['tao_session']) : $this->error('请填写帐号');
			foreach( $taobao as $key=>$val ){
				$setting_mod->where("name='".$key."'")->save(array('data'=>$val));
			}
			$this->success('修改成功', U('items_collect/author_tao'));
		}		
		$res = $setting_mod->where("name='tao_session' OR name='taobao_appkey' OR name='site_domain'")->select();
		//print_r($res);		
		foreach($res as $val)
		{
			$taobaoset[$val['name']] = $val['data'];
		}		
		$this->assign('taobao',$taobaoset);
		$this->display();
	}	
	//设置59秒api
	public function miaoapi()
	{
		$setting_mod = M('setting');
		if (isset($_POST['dosubmit'])) {	
			$miao['miao_appkey'] = isset($_POST['miao_appkey']) && trim($_POST['miao_appkey']) ? trim($_POST['miao_appkey']) : $this->error('请填写appkey');
			$miao['miao_appsecret'] = isset($_POST['miao_appsecret']) && trim($_POST['miao_appsecret']) ? trim($_POST['miao_appsecret']) : $this->error('请填写appsecret');
			foreach( $miao as $key=>$val ){
				$setting_mod->where("name='$key'")->save(array('data'=>$val));
			}			
			$this->success('修改成功', U('items_collect/miaoapi'));
		}
		$res = $setting_mod->where("name='miao_appkey' OR name='miao_appsecret'")->select();
		foreach( $res as $val )
		{
			$miaoset[$val['name']] = $val['data'];
		}
		$this->assign('miao',$miaoset);
		$this->display();
	}	
	//采集淘宝数据
	public function taobao_collect()
	{
		if (isset($_POST['dosubmit'])) {
			$cate_id = isset($_POST['cate_id']) && intval($_POST['cate_id']) ? intval($_POST['cate_id']) : $this->error('请选择分类');
			$keywords = isset($_POST['keywords']) && trim($_POST['keywords']) ? trim($_POST['keywords']) : $this->error('请填写关键词');
			$pages = isset($_POST['pages']) && intval($_POST['pages']) ? intval($_POST['pages']) : 1;
			$this->redirect('items_collect/taobao_collect_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'pages'=>$pages));
		}
		//获取分类
		$cate_id = isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');
		$cate_name = isset($_GET['cate_name']) && trim($_GET['cate_name']) ? trim($_GET['cate_name']) : '';
		$this->assign('cate_id', $cate_id);
		$this->assign('cate_name', $cate_name);
		$this->display();
	}	
	//采集59miao数据	
	public function miao_collect()
	{
		if (isset($_POST['dosubmit'])) {
			$cate_id = isset($_POST['cate_id']) && intval($_POST['cate_id']) ? intval($_POST['cate_id']) : $this->error('请选择分类');
			$keywords = isset($_POST['keywords']) && trim($_POST['keywords']) ? trim($_POST['keywords']) : $this->error('请填写关键词');
			$pages = isset($_POST['pages']) && intval($_POST['pages']) ? intval($_POST['pages']) : 1;
			$this->redirect('items_collect/miao_collect_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'pages'=>$pages));
		}
		$item_cate_mod=$this->items_cate_mod;
		//获取分类
		$cate_id = isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');
		
		$cate_rel=$item_cate_mod->where("id='{$cate_id}'")->find();
		$cate_keywords = isset($_GET['cate_name']) && trim($_GET['cate_name']) ? trim($_GET['cate_name']) : '';
		$this->assign('cate_id', $cate_id);
		$this->assign('cate_name', $cate_rel['name']);
		$this->assign('cate_keywords', $cate_keywords);		
		$this->display();
	}
    //采集微购数据
	public function wego_collect()
	{
		$this->isSign();		
		if (isset($_POST['dosubmit'])) {
			$cate_id = isset($_POST['cate_id']) && intval($_POST['cate_id']) ? intval($_POST['cate_id']) : $this->error('请选择分类');
			$keywords = isset($_POST['keywords']) && trim($_POST['keywords']) ? trim($_POST['keywords']) : $this->error('请填写关键词');
			$pages = isset($_POST['pages']) && intval($_POST['pages']) ? intval($_POST['pages']) : 1;
			$this->redirect('items_collect/wego_collect_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'pages'=>$pages));
		}
		$item_cate_mod=$this->items_cate_mod;
		//获取分类
		$cate_id = isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');
		
		$cate_rel=$item_cate_mod->where("id='{$cate_id}'")->find();
		$cate_keywords = isset($_GET['cate_name']) && trim($_GET['cate_name']) ? trim($_GET['cate_name']) : '';
		$this->assign('cate_id', $cate_id);
		$this->assign('cate_name', $cate_rel['name']);
		$this->assign('cate_keywords', $cate_keywords);		
		$this->display();
	}
	
	public function taobao_batch_collect_jump(){
		$tags_cate_mod=D('items_tags_cate');
		$tags_mod=D('items_tags');
        $items_cate_mod = D('items_cate');
        $items_site_mod = D('items_site');
        $collect_taobao_mod = D('collect_taobao');
        
		$cate = isset($_REQUEST['cate'])?explode(',',$_REQUEST['cate']): $this->error('请选择分类');
		$index=isset($_REQUEST['cate_index'])?intval('cate_index'):0;
		//$pages =5;
		$cate_id=$cate[$index];
		$tags_cate=$tags_cate_mod->where('cate_id='.$cate_id)->select();
		$keywords=$items_cate_mod->where('id='.$cate_id)->find();
		$tb_top = $this->taobao_client();
		$req = $tb_top->load_api('TaobaokeItemsGetRequest');
		$req->setFields("num_iid,title,nick,pic_url,price,click_url,shop_click_url,seller_credit_score,item_location,volume");
		$req->setPid($this->setting['taobao_pid']);
		$req->setNick($this->setting['taobao_usernick']);
		$req->setKeyword('男装');
		$req->setPageNo(1);
		$req->setPageSize(40);
		$resp = $tb_top->execute($req);
		$goods_list = (array)$resp->taobaoke_items;
		$sid = $items_site_mod->where("alias='taobao'")->getField('id');
		$items_nums = 0;
		foreach ($goods_list['taobaoke_item'] as $item) {
			$item = (array)$item;
			$item['item_key'] = 'taobao_'.$item['num_iid'];
			$item['sid'] = $sid;
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}
		if ($key>=count($cate)) {
			//记录采集时间
			$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			$this->collect_success('采集完成', '', 'collect');
		} else {
			$this->collect_success('第 <em class="blue">'.$index.'</em> 个分类，开始采集下一页',
			U('items_collect/taobao_batch_collect_jump', array('cate'=>implode(',',$cate),'cate_index'=>$index+1)));
		}
	}
	//采集淘宝数据跳转页面
	public function taobao_collect_jump()
	{
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');
		$keywords = isset($_GET['keywords']) && trim($_GET['keywords']) ? trim($_GET['keywords']) : $this->error('请填写关键词');
		$pages = isset($_GET['pages']) && intval($_GET['pages']) ? intval($_GET['pages']) : 1;

		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_taobao_mod = D('collect_taobao');
		
		if($this->setting['tao_collect_set']==1){
			$resp=$this->get_t_item_list($keywords, $p);  //淘宝商品api采集
		}else{
			$resp=$this->get_t_c_item_list($keywords, $p);  //淘宝促销api采集
		}		
		$goods_list = (array)$resp->taobaoke_items;
		$sid = $items_site_mod->where("alias='taobao'")->getField('id');

		$items_nums = 0;
		foreach ($goods_list['taobaoke_item'] as $item) {
			$item = (array)$item;
			$item['item_key'] = 'taobao_'.$item['num_iid'];
			$item['sid'] = $sid;
			if($item['coupon_price']){
				$item['price']=$item['coupon_price'];
			}
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}

		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			$this->collect_success('采集完成', '', 'collect');
		} else {
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/taobao_collect_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'pages'=>$pages,'p'=>$p+1)));
		}


	}
	//采集微购数据跳转页面
	public function wego_collect_jump()
	{
		$this->isSign();
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');
		$keywords = isset($_GET['keywords']) && trim($_GET['keywords']) ? trim($_GET['keywords']) : $this->error('请填写关键词');
		$pages = isset($_GET['pages']) && intval($_GET['pages']) ? intval($_GET['pages']) : 1;

		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_taobao_mod = D('collect_taobao');	
		
		$resp=$this->wegoApiSearch($keywords, $p);	
				
		$goods_list =get_object_vars_final($resp);
		
		$sid = $items_site_mod->where("alias='taobao'")->getField('id');		
		$items_nums = 0;
		foreach ($goods_list['list'] as $item) {			
			$item['item_key'] = 'taobao_'.$item['goods_id'];
			$item['sid'] = $sid;
			$item['title']=ReplaceKeywords(strip_tags($item['goods_title']));
			$item['pic_url']=$item['goods_img'];
			$item['price']=$item['goods_price'];
		 	$item['nick']=$item['shop_name'];
		 	$item['volume']=10;	
			
			if($item['coupon_price']){
				$item['price']=$item['coupon_price'];
			}
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}

		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			$this->collect_success('采集完成', '', 'collect');
		} else {
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/wego_collect_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'pages'=>$pages,'p'=>$p+1)));
		}


	}
	//采集59秒数据跳转页面
	public function miao_collect_jump()
	{
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');
		$keywords = isset($_GET['keywords']) && trim($_GET['keywords']) ? trim($_GET['keywords']) : $this->error('请填写关键词');
		$pages = isset($_GET['pages']) && intval($_GET['pages']) ? intval($_GET['pages']) : 1;

		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_miao_mod = D('collect_miao');
		$miao_api = $this->miao_client();   //获取59秒api设置信息
		
		$data=$miao_api->ListItemsSearch('',$keywords, '', '0', $p, 20);
		$goods_list= $data['items_search']['items']['item'];
		$sid = $items_site_mod->where("alias='miao'")->getField('id');
		$items_nums = 0;
		foreach ($goods_list as $item) {
			$item = (array)$item;
			$item['item_key'] = 'miao_'.$item['iid'];   //用于判断此商品是否存在
			$item['sid'] = $sid;
			$this->miao_collect_insert($item, $cate_id);   //数据入库
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}

		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_miao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_miao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_miao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			$this->collect_success('采集完成', '', 'collect');
		} else {
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/miao_collect_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'pages'=>$pages,'p'=>$p+1)));
		}


	}
	//采集成功跳转
	public function collect_success($message, $jump_url, $dialog='')	{
		$this->assign('message', $message);
		if(!empty($jump_url)) $this->assign('jump_url', $jump_url);
		if(!empty($dialog)) $this->assign('dialog', $dialog);
		$this->display(APP_PATH.'Tpl/'.C('DEFAULT_THEME').'/items_collect/collect_success.html');
		exit;
	}	
	//淘宝数据采集入库
	private function tao_collect_insert($item, $cate_id)
	{
		
		//print_r($item);
		$items_mod = D('items');
		$items_tags_mod = D('items_tags');
		$items_tags_item_mod = D('items_tags_item');		
		//需要判断商品是否已经存在
		//$isset = $items_mod->where("item_key='".$item['item_key']."'")->getField('id');
		$isset_item_id = $items_mod->where("item_key='".$item['item_key']."'")->getField('id');
		if ($isset_item_id) {			
			$update_date=array(
				    'title' => ReplaceKeywords(strip_tags($item['title'])),
				    'price' => $item['price'],		   
				    'likes' => $item['volume']+rand(1, 10),	
					'seller_name'=>$item['nick'], 		
					'url'=>$item['click_url'],
					'cash_back_rate'=>round($item['commission'],2).'元',
				    'haves' => $item['volume']	
				);
			$items_mod->where("id='{$isset_item_id}'")->save($update_date);
			return;
		}
		$add_time = time();
		$item_id = $items_mod->add(array(
		    'title' => ReplaceKeywords(strip_tags($item['title'])),
		    'cid' => $cate_id,
		    'sid' => $item['sid'],
		    'item_key' => $item['item_key'],
		    'img' => $item['pic_url'].'_210x1000.jpg',
		    'simg' => $item['pic_url'].'_64x64.jpg',
		    'bimg' => $item['pic_url'],
		    'price' => str_replace('元', '', $item['price']),
		    'url' => $item['click_url'],
		    'likes' => $item['volume'],
			'seller_name'=>$item['nick'],
			'cash_back_rate'=>round($item['commission'],2).'元',
		    'haves' => $item['volume'],
		    'add_time' => $add_time,
			'browse_num'=>0,
		
		));	
		//处理标签
		$tags = $items_tags_mod->get_tags_by_title(strip_tags($item['title']));
		if ($tags) {
			$tags = array_unique($tags);
			foreach ($tags as $tag) {
				$isset_id = $items_tags_mod->where("name='".$tag."'")->getField('id');
				if ($isset_id) {
					$items_tags_mod->where('id='.$isset_id)->setInc('item_nums');
					$items_tags_item_mod->add(array(
			            'item_id' => $item_id,
			            'tag_id' => $isset_id
					));
				} else {
					$tag_id = $items_tags_mod->add(array('name'=>$tag));
					$items_tags_item_mod->add(array(
			            'item_id' => $item_id,
			            'tag_id' => $tag_id
					));
				}
			}
		}
	}	
	//59秒数据采集入库
	private function miao_collect_insert($item, $cate_id)
	{
		$items_mod = D('items');
		$items_tags_mod = D('items_tags');
		$items_tags_item_mod = D('items_tags_item');
		//需要判断商品是否已经存在
		$isset = $items_mod->where("item_key='".$item['item_key']."'")->getField('id');
		if ($isset) {
			return;
		}
		$add_time = time();
		
		if (strpos($item['pic_url'], 'taobao') !== false){             
			$item['img'] =  $item['pic_url'].'_210x1000.jpg';
			$item['simg'] = $item['pic_url'].'_64x64.jpg';	
			//$item['bimg'] = $item['pic_url'].'_460x460.jpg';
			$item['bimg'] = $item['pic_url'];
        }else{        	
			$item['img'] = str_replace('.jpg', '_210x1000.jpg', $item['pic_url']);
			$item['simg'] = str_replace('.jpg', '_60x60.jpg', $item['pic_url']);
			$item['bimg'] = $item['pic_url'];
			//$item['bimg'] = str_replace('.jpg', '_460x460.jpg', $item['pic_url']);
        }
		if($item['popular']==0){
			$item['popular']=1;
		}
		
		$item_id = $items_mod->add(array(
		    'title' => ReplaceKeywords(strip_tags($item['title'])),
		    'cid' => $cate_id,
		    'sid' => $item['sid'],
		    'item_key' => $item['item_key'],
		    'img' => $item['img'],
		    'simg' => $item['simg'],
		    'bimg' => $item['bimg'],
		    'price' => $item['price'],
		    'url' => $item['click_url'],
		    'likes' => $item['popular'],
			'seller_name'=>$item['seller_name'],
			'cash_back_rate'=>$item['cashback_scope'],
		    'haves' => 1,
		    'add_time' => $add_time,		
		));
		//处理标签
		$tags = $items_tags_mod->get_tags_by_title(strip_tags($item['title']));
		if ($tags) {
			$tags = array_unique($tags);
			foreach ($tags as $tag) {
				$isset_id = $items_tags_mod->where("name='".$tag."'")->getField('id');
				if ($isset_id) {
					$items_tags_mod->where('id='.$isset_id)->setInc('item_nums');  //如果存在此标签的商品  让item_nums+1
					$items_tags_item_mod->add(array(
			            'item_id' => $item_id,
			            'tag_id' => $isset_id
					));
				} else {
					$tag_id = $items_tags_mod->add(array('name'=>$tag));
					$items_tags_item_mod->add(array(
			            'item_id' => $item_id,
			            'tag_id' => $tag_id
					));
				}
			}
		}
	}
	public function collect()
	{
		if(isset($_REQUEST['dosubmit'])){
			$cate=implode(',',$_REQUEST['cate']);
				
			header("location:".U('items_collect/taobao_batch_collect_jump?act=batch&cate='.$cate));
			exit;
		}
		$code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : $this->error('参数错误');
		$items_cate_mod = D('items_cate');
		switch ($code) {	
			case 'taobao':
				$res=get_items_cate_list();
				$this->assign('items_cate_list', $res['sort_list']);
				break;		
			case 'miao':
				$res=get_items_cate_list('0','0','1','collect_miao');
				$this->assign('items_cate_list', $res['sort_list']);
				break;
			case 'wego':
				$res=get_items_cate_list();
				$this->assign('items_cate_list', $res['sort_list']);
				break;
		}
		$this->assign('code',$code);
		$this->display();
	}
	//分类采集
	public function cate_collect()
	{
		if(isset($_REQUEST['dosubmit'])){
			$cate=implode(',',$_REQUEST['cate']);
				
			header("location:".U('items_collect/taobao_batch_collect_jump?act=batch&cate='.$cate));
			exit;
		}
		$code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : $this->error('参数错误');
		$items_cate_mod = D('items_cate');
		switch ($code) {	
			case 'taobao':
					$items_cate_mod = D('items_cate');
					$taocate=$this->get_taocats();					
					$this->assign('cate_list', $taocate);
				break;		
			case 'miao':
					$items_cate_mod = D('items_cate');
					$miaocate=$this->get_miaocats();					
					$this->assign('cate_list', $miaocate);
				break;			
		}
		$this->assign('code',$code);
		$this->display();
	}	
	/*一键采集开始*/
	//一键采集
	public function collect_all()
	{
		if (isset($_POST['dosubmit'])) {
			$num = isset($_POST['num']) && intval($_POST['num']) ? intval($_POST['num']) : $this->error('请填写每个分类采集的商品数量 ');				
			//echo $instr;
			$this->redirect('items_collect/collect_cate_jump', array('num'=>$num));
		}
		$this->display();
	}
	//采集所有分类跳转页面
	public function collect_cate_jump(){
			$cate_index_id=isset($_GET['cate_index_id'])?intval($_GET['cate_index_id']):'0';			
			//如果不存在cate_id
			$num=isset($_GET['num'])?intval($_GET['num']):'40';
			//查询出分类
		 	if(S('admin_category')){
	            $admin_category = S('admin_category');
	        }else{
	        	$item_cate_mod=$this->items_cate_mod;
				$rel1=$item_cate_mod->where("pid=0")->select();
				$instr='';
				foreach ($rel1 as $value){
					$instr.=$value['id'].',';
				}
				$instr=substr($instr, 0,-1);	
				if(empty($this->setting['collect_cate'])){
					$admin_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0")->order('ordid asc,id asc')->select();	
				}else{
					$admin_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0 AND id in({$this->setting['collect_cate']})")->order('ordid asc,id asc')->select();
				}           
	            S('admin_category',$admin_category,'3600');
			}
			if($admin_category[$cate_index_id]['id']){
				$this->redirect('items_collect/collect_item_jump', array('cate_id'=>$admin_category[$cate_index_id]['id'],'keywords'=>$admin_category[$cate_index_id]['keywords'],'name'=>$admin_category[$cate_index_id]['name'],'num'=>$num,'cate_index_id'=>$cate_index_id));
			}
			else{
				$this->collect_success('恭喜您数据采集完成', '', 'collect');
			}
	}
	//采集数据跳转页面
	public function collect_item_jump()
	{
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : '';
		$keywords = isset($_GET['keywords']) && trim($_GET['keywords']) ? trim($_GET['keywords']) : '';
		$name = isset($_GET['name']) && trim($_GET['name']) ? trim($_GET['name']) : '';
		$num=isset($_GET['num'])?intval($_GET['num']):'40';
		$cate_index_id=isset($_GET['cate_index_id'])?intval($_GET['cate_index_id']):'0';
		$pages = ceil($num/20); //每次页数  用采集的数量计算得到的

		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_miao_mod = D('collect_miao');
		$miao_api = $this->miao_client();   //获取59秒api设置信息
		
		$data=$miao_api->ListItemsSearch('',$keywords, '', '0', $p, 20);
		$goods_list= $data['items_search']['items']['item'];
		$sid = $items_site_mod->where("alias='miao'")->getField('id');
		$items_nums = 0;
		foreach ($goods_list as $item) {
			$item = (array)$item;
			$item['item_key'] = 'miao_'.$item['iid'];   //用于判断此商品是否存在
			$item['sid'] = $sid;
			$this->miao_collect_insert($item, $cate_id);   //数据入库
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}

		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_miao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_miao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_miao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			//$this->collect_success('采集完成', '', 'collect');
			$this->collect_success($name.'分类采集完成,开始采集下一个分类', U('items_collect/collect_cate_jump', array('num'=>$num,'cate_index_id'=>$cate_index_id+1)));
		} else {
			$this->collect_success($name.'分类下的第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/collect_item_jump', array('cate_id'=>$cate_id,'keywords'=>$keywords,'name'=>$name,'num'=>$num,'cate_index_id'=>$cate_index_id,'pages'=>$pages,'p'=>$p+1)));
		}

	}
	/*一键采集B2C完成*/
	
	/*一键采集淘宝开始*/
	//一键采集淘宝结束
	public function collect_all_tao()
	{
		if (isset($_POST['dosubmit'])) {
			$num = isset($_POST['num']) && intval($_POST['num']) ? intval($_POST['num']) : $this->error('请填写每个分类采集的商品数量 ');				
			//echo $instr;
			$this->redirect('items_collect/collect_cate_jump_tao', array('num'=>$num));
		}
		$this->display();
	}
	//一键采集微购开始
	public function collect_all_wego()
	{	
		$this->isSign();
		if (isset($_POST['dosubmit'])) {
			$num = isset($_POST['num']) && intval($_POST['num']) ? intval($_POST['num']) : $this->error('请填写每个分类采集的商品数量 ');				
			//echo $instr;
			$this->redirect('items_collect/collect_cate_jump_wego', array('num'=>$num));
		}
		$this->display();
	}	
	//采集所有分类跳转页面
	public function collect_cate_jump_wego(){
			$this->isSign();
			$cate_index_id=isset($_GET['cate_index_id'])?intval($_GET['cate_index_id']):'0';			
			//如果不存在cate_id
			$num=isset($_GET['num'])?intval($_GET['num']):'40';			
			//查询出分类
		 	if(S('admin_category')){
	            $admin_category = S('admin_category');
	        }else{
	        	$item_cate_mod=$this->items_cate_mod;
				$rel1=$item_cate_mod->where("pid=0")->select();
				$instr='';
				foreach ($rel1 as $value){
					$instr.=$value['id'].',';
				}
				$instr=substr($instr, 0,-1);
				if(empty($this->setting['collect_cate'])){
					$admin_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0")->order('ordid asc,id asc')->select();	
				}else{
					$admin_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0 AND id in({$this->setting['collect_cate']})")->order('ordid asc,id asc')->select();
				}				
	            S('admin_category',$admin_category,'3600');
			}			
			if($admin_category[$cate_index_id]['id']){
				$this->redirect('items_collect/collect_item_jump_wego', array('cate_id'=>$admin_category[$cate_index_id]['id'],'keywords'=>$admin_category[$cate_index_id]['keywords'],'name'=>$admin_category[$cate_index_id]['name'],'num'=>$num,'cate_index_id'=>$cate_index_id));
			}
			else{
				$this->collect_success('恭喜您数据采集完成', '', 'collect_tao');
			}
	}
	
	
	//采集所有分类跳转页面
	public function collect_cate_jump_tao(){
			$cate_index_id=isset($_GET['cate_index_id'])?intval($_GET['cate_index_id']):'0';			
			//如果不存在cate_id
			$num=isset($_GET['num'])?intval($_GET['num']):'40';			
			//查询出分类
		 	if(S('admin_category')){
	            $admin_category = S('admin_category');
	        }else{
	        	$item_cate_mod=$this->items_cate_mod;
				$rel1=$item_cate_mod->where("pid=0")->select();
				$instr='';
				foreach ($rel1 as $value){
					$instr.=$value['id'].',';
				}
				$instr=substr($instr, 0,-1);
				if(empty($this->setting['collect_cate'])){
					$admin_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0")->order('ordid asc,id asc')->select();	
				}else{
					$admin_category=$item_cate_mod->where("pid not in({$instr}) AND pid!=0 AND id in({$this->setting['collect_cate']})")->order('ordid asc,id asc')->select();
				}				
	            S('admin_category',$admin_category,'3600');
			}			
			if($admin_category[$cate_index_id]['id']){
				$this->redirect('items_collect/collect_item_jump_tao', array('cate_id'=>$admin_category[$cate_index_id]['id'],'keywords'=>$admin_category[$cate_index_id]['keywords'],'name'=>$admin_category[$cate_index_id]['name'],'num'=>$num,'cate_index_id'=>$cate_index_id));
			}
			else{
				$this->collect_success('恭喜您数据采集完成', '', 'collect_tao');
			}
	}	
	//采集微购数据跳转页面
	public function collect_item_jump_wego()
	{
		$this->isSign();
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : '';
		$keywords = isset($_GET['keywords']) && trim($_GET['keywords']) ? trim($_GET['keywords']) : '';
		$name = isset($_GET['name']) && trim($_GET['name']) ? trim($_GET['name']) : '';
		$num=isset($_GET['num'])?intval($_GET['num']):'40';
		$cate_index_id=isset($_GET['cate_index_id'])?intval($_GET['cate_index_id']):'0';
		$pages = ceil($num/20); //每次页数  用采集的数量计算得到的
		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_taobao_mod = D('collect_taobao');	

		$resp=$this->wegoApiSearch($keywords, $p);
		$goods_list =get_object_vars_final($resp);
		
		$sid = $items_site_mod->where("alias='taobao'")->getField('id');		
		$items_nums = 0;
		foreach ($goods_list['list'] as $item) {			
			$item['item_key'] = 'taobao_'.$item['goods_id'];
			$item['sid'] = $sid;
			$item['title']=ReplaceKeywords(strip_tags($item['goods_title']));
			$item['pic_url']=$item['goods_img'];
			$item['price']=$item['goods_price'];
		 	$item['nick']=$item['shop_name'];
		 	$item['volume']=10;	
			
			if($item['coupon_price']){
				$item['price']=$item['coupon_price'];
			}
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}
			
		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			//$this->collect_success('采集完成', '', 'collect');
			$this->collect_success($name.'分类采集完成,开始采集下一个分类', U('items_collect/collect_cate_jump_wego', array('num'=>$num,'cate_index_id'=>$cate_index_id+1)));
		} else {
			$this->collect_success($name.'分类下的第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/collect_item_jump_wego', array('cate_id'=>$cate_id,'keywords'=>$keywords,'name'=>$name,'num'=>$num,'cate_index_id'=>$cate_index_id,'pages'=>$pages,'p'=>$p+1)));
		}
		
	}	
	
	//采集淘宝数据跳转页面
	public function collect_item_jump_tao()
	{
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : '';
		$keywords = isset($_GET['keywords']) && trim($_GET['keywords']) ? trim($_GET['keywords']) : '';
		$name = isset($_GET['name']) && trim($_GET['name']) ? trim($_GET['name']) : '';
		$num=isset($_GET['num'])?intval($_GET['num']):'40';
		$cate_index_id=isset($_GET['cate_index_id'])?intval($_GET['cate_index_id']):'0';
		$pages = ceil($num/20); //每次页数  用采集的数量计算得到的
		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_taobao_mod = D('collect_taobao');
		if($this->setting['tao_collect_set']==1){
			$resp=$this->get_t_item_list($keywords, $p);  //淘宝商品api采集
		}else{
			$resp=$this->get_t_c_item_list($keywords, $p);  //淘宝促销api采集
		}		
		$goods_list = (array)$resp->taobaoke_items;
		//print_r($goods_list);exit;

		$sid = $items_site_mod->where("alias='taobao'")->getField('id');

		$items_nums = 0;
		foreach ($goods_list['taobaoke_item'] as $item) {
			$item = (array)$item;
			$item['item_key'] = 'taobao_'.$item['num_iid'];
			$item['sid'] = $sid;
			if($item['coupon_price']){
				$item['price']=$item['coupon_price'];
			}
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}		
		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			//$this->collect_success('采集完成', '', 'collect');
			$this->collect_success($name.'分类采集完成,开始采集下一个分类', U('items_collect/collect_cate_jump_tao', array('num'=>$num,'cate_index_id'=>$cate_index_id+1)));
		} else {
			$this->collect_success($name.'分类下的第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/collect_item_jump_tao', array('cate_id'=>$cate_id,'keywords'=>$keywords,'name'=>$name,'num'=>$num,'cate_index_id'=>$cate_index_id,'pages'=>$pages,'p'=>$p+1)));
		}
		
	}
	/*一键采集完成*/
	
	//获取淘宝分类
    private function get_taocats($cid = 0) {        
        $tb_top = $this->taobao_client();
        $req = $tb_top->load_api('ItemcatsGetRequest');
        $req->setFields("cid,parent_cid,name,is_parent");
        $req->setParentCid($cid);
        $resp = $tb_top->execute($req);
        $res_cats = (array) $resp->item_cats;
        $item_cate = array();
        foreach ($res_cats['item_cat'] as $val) {
            $val = (array) $val;
            $item_cate[] = $val;
        }       
        return $item_cate;
    }
	//获取59分类
    private function get_miaocats($cid = 0) {        
        $miao_api = $this->miao_client();   //获取59秒api设置信息
        $fileds="cid,name,count,status,sort_order";
		$data=$miao_api->ListItemCatsGet();		
		$item_cate=$data['itemcats']['itemcat'];		
		return $item_cate;      
		
    }
    //搜索淘宝商品
    public function search_tao(){    	
    	$_GET=setFormString($_GET); 
    	$p=isset($_GET['p'])?$_GET['p']:1; 
    	$page_size=40;   	
    	$cid=$_GET['cid'];
    	$start_price=$_GET['start_price'];    	
    	$end_price=$_GET['end_price'];    	
    	$start_commissionRate=$_GET['start_commissionRate'];    	
    	$end_commissionRate=$_GET['end_commissionRate'];    	
    	$start_commissionNum=$_GET['start_commissionNum'];
    	$end_commissionNum=$_GET['end_commissionNum'];    	
    	$start_totalnum=$_GET['start_totalnum'];    	
    	$end_totalnum=$_GET['end_totalnum'];
    	$levelstart=$_GET['levelstart'];    	
    	$levelend=$_GET['levelend'];  
    	$keyword=$_GET['keyword'];    	
    	$tb_top = $this->taobao_client();    	
    	if($this->setting['tao_collect_set']==1){
    		$req = $tb_top->load_api('TaobaokeItemsGetRequest');
    		$req->setFields('num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,volume');
			$req->setPid($this->setting['taobao_pid']);
			$req->setNick($this->setting['taobao_usernick']);	
			$req->setKeyword($keyword);
			$req->setCid($cid);
			$req->setStartPrice($start_price);
			$req->setEndPrice($end_price);
			$req->setStartCredit($levelstart);
			$req->setEndCredit($levelend);
			$req->setStartCommissionRate($start_commissionRate);
			$req->setEndCommissionRate($end_commissionRate);
			$req->setStartCommissionNum($start_commissionNum);
			$req->setEndCommissionNum($end_commissionNum);
			$req->setStartTotalnum($start_totalnum);
			$req->setEndTotalnum($end_totalnum);
			$req->setPageNo($p);
			$req->setPageSize($page_size);
			$rel = $tb_top->execute($req);	
    	}else{
    		//$req = $tb_top->load_api('TaobaokeItemsCouponGetRequest');    	
			$req = $tb_top->load_api('TaobaokeItemsCouponGetRequest');
			$req->setFields("num_iid,title,nick,pic_url,price,coupon_price,click_url,shop_click_url,seller_credit_score,item_location,volume,commission");
			$req->setPid($this->setting['taobao_pid']);
			$req->setNick($this->setting['taobao_usernick']);
			$req->setKeyword($keyword);
			$req->setCid($cid);			
			$req->setStartCredit($levelstart);
			$req->setEndCredit($levelend);			
			if(!empty($start_commissionRate)){
				$req->setStartCommissionRate($start_commissionRate);
				$req->setEndCommissionRate($end_commissionRate);
			}
			if(!empty($start_commissionNum)){
				$req->setStartCommissionNum($start_commissionNum);
				$req->setEndCommissionNum($end_commissionNum);	
			}			
			$req->setPageNo($p);
			$req->setPageSize($page_size);			
			$rel = $tb_top->execute($req);	
			//print_r($rel);		
    	}
		$taobaoke_item_list=get_object_vars_final($rel);		
		$taobaoke_item_list=$taobaoke_item_list['taobaoke_items']['taobaoke_item'];
        $total_results=$rel->total_results;
		//print_r($taobaoke_item_list);
		import("ORG.Util.Page");
		$count = $total_results;
		
		if($count>400){
			$count=400;
		}
		
		$p = new Page($count,$page_size);
		$page = $p->show();
		$this->assign('page',$page);	
	
    	$taobaoke_item_list_s = array();
        foreach ($taobaoke_item_list as $val) {           
            $taobaoke_item_list_s[$val['num_iid']] = $val;
        }
		//每次保存
		count($taobaoke_item_list_s)>0 && F('taobaoke_item_list_s', $taobaoke_item_list_s);
        $this->assign('list', $taobaoke_item_list);         
        $this->display();
    }
    //搜索59miao商品
    public function search_miao(){
    	$_GET=setFormString($_GET); 
    	
  		$cid= isset($_GET['cid']) && intval($_GET['cid']) ? intval($_GET['cid']) : '';  		
		$keyword = isset($_GET['keyword']) && trim($_GET['keyword']) ? trim($_GET['keyword']) : '';
		$pages = isset($_GET['pages']) && intval($_GET['pages']) ? intval($_GET['pages']) : 1;
		$p=isset($_GET['p'])?$_GET['p']:1; 
		$start_price=$_GET['start_price'];		
		$end_price=$_GET['end_price'];
		$seller_name=trim($_GET['seller_name']);
				
		//获取此商品对应的商家sid
		$seller_list=D('seller_list');
		$seller_rel=$seller_list->field('sid')->where("name = '{$seller_name}'")->find();
		
				
		$seller_id=count($seller_rel)>0?$seller_rel['sid']:0;
		
		
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_miao_mod = D('collect_miao');
		$miao_api = $this->miao_client();   //获取59秒api设置信息
		
		$data=$miao_api->ListItemsSearch('',$keyword, $cid, $seller_id, $p, 20,$start_price,$end_price);
		//ListItemsSearch('',$keywords, '', '0', $p, 20);
		
		//ListItemsSearch('',$keyword, $cid, $seller_id, $p, 20,$star_price,$end_price);		
		$goods_list= $data['items_search']['items']['item'];
    	$goods_list_s = array();
        foreach ($goods_list as $val) {           
            $goods_list_s[$val['iid']] = $val;
        }		
		//每次保存
		count($goods_list_s)>0 && F('goods_list_s', $goods_list_s);
        $this->assign('list', $goods_list);      
        $this->display();
    	
    }
    //发布淘宝商品
    public function publishtao(){
    	$ids=$_GET['ids'];
    	if(isset($_POST['dosubmit'])){    		 	
	    	//从缓存中获取本页商品数据
	    	$items_cate_mod = D('items_cate');
	    	$ids=$_POST['ids'];
	   		$ids_arr = explode(',', $ids);
	   		$cate_id=$_POST['cid'];
	        $taobaoke_item_list_s = F('taobaoke_item_list_s');
	        $items_site_mod = D('items_site');
	      	$sid = $items_site_mod->where("alias='taobao'")->getField('id');
	      	$items_nums = 0;
	        foreach ($taobaoke_item_list_s as $key => $val) {
	           if (in_array($key, $ids_arr)) {	
		           $val['item_key'] = 'taobao_'.$val['num_iid'];
				   $val['sid'] = $sid;      
			       if($val['coupon_price']){
						$val['price']=$val['coupon_price'];
				   }     	
		            //入库             
	               $this->tao_collect_insert($val, $cate_id);
	               $items_nums++;
	           }
	        }  
    		if ($items_nums>0) {
				$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
			}
	        $this->collect_success('采集完成', '', 'collect');      
    	}else{
    		$items_cate_mod = D('items_cate');
			$cate_list=$items_cate_mod->get_top2_list();
			$this->assign('cate_list', $cate_list);
			$this->assign('ids',$ids);
    	}
        $this->display();    
    }
    //发布59秒商品
    public function publishmiao(){
    	$ids=$_GET['ids'];
    	if(isset($_POST['dosubmit'])){    		 	
	    	//从缓存中获取本页商品数据
	    	$items_cate_mod = D('items_cate');
	    	$ids=$_POST['ids'];
	   		$ids_arr = explode(',', $ids);
	   		$cate_id=$_POST['cid'];
	        $goods_list_s = F('goods_list_s');    
	        $items_site_mod = D('items_site');
	      	$sid = $items_site_mod->where("alias='miao'")->getField('id');
	      	$items_nums = 0;
	        foreach ($goods_list_s as $key => $val) {
	           if (in_array($key, $ids_arr)) {	
	           	$val['item_key'] = 'miao_'.$val['iid'];
				$val['sid'] = $sid;
				$this->miao_collect_insert($val, $cate_id);   //数据入库
				$items_nums++;
	           }
	        }  
	    	if ($items_nums>0) {
				$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
			}	        
	        $this->collect_success('采集完成', '', 'collect');      
    	}else{
    		$items_cate_mod = D('items_cate');
			$cate_list=$items_cate_mod->get_top2_list();
			$this->assign('cate_list', $cate_list);
			$this->assign('ids',$ids);
    	}
        $this->display();
    }  

    //
    //淘宝数据分类采集
	public function catemiao_collect()
	{
		if (isset($_POST['dosubmit'])) {
			
			$cate_id = isset($_POST['cid']) && intval($_POST['cid']) ? intval($_POST['cid']) : $this->error('请选择分类');			
			$pages = isset($_POST['pages']) && intval($_POST['pages']) ? intval($_POST['pages']) : 1;
			
			$miaocid=$_POST['miaocid'];			
			$start_price=$_POST['start_price'];		
			$end_price=$_POST['end_price'];	
			$sid=$_POST['sid'];			
			$keyword=$_POST['keyword'];
			
			
			$jump_array=array(
				'cate_id'=>$cate_id,				
				'miaocid'=>$miaocid,
				'start_price'=>$start_price,
				'end_price'=>$end_price,
				'sid'=>$sid,				
				'keyword'=>$keyword,			
				'pages'=>$pages
			);
			$this->redirect('items_collect/catemiao_collect_jump',$jump_array);
		}
		
		$_GET=setFormString($_GET); 
		//59秒分类id		
		$miaocid=$_GET['miaocid'];			
		$start_price=$_GET['start_price'];		
		$end_price=$_GET['end_price'];	
		$seller_name=trim($_GET['seller_name']);	
		$keyword=$_GET['keyword'];	
		//获取商家id
		$seller_list=D('seller_list');
		$seller_rel=$seller_list->field('sid')->where("name ='$seller_name'")->find();				
		$sid=count($seller_rel)>0?$seller_rel['sid']:0;
		
		$this->assign('miaocid',$miaocid);
		$this->assign('start_price',$start_price);
		$this->assign('end_price',$end_price);		
		$this->assign('sid',$sid);
		$this->assign('keyword',$keyword);
		//获取分类
		$items_cate_mod = D('items_cate');
		$cate_list=$items_cate_mod->get_top2_list();
		$this->assign('cate_list', $cate_list);
		
		$this->display();
	}	
    
	//采集淘宝数据跳转页面
	public function catemiao_collect_jump()
	{
		
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');		
		$pages = isset($_GET['pages']) && intval($_GET['pages']) ? intval($_GET['pages']) : 1;
		$miaocid=$_GET['miaocid'];
		$start_price=$_GET['start_price'];		
		$end_price=$_GET['end_price'];		
		$sid=$_GET['sid'];
		$keyword=$_GET['keyword'];
		
		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');

		$collect_miao_mod = D('collect_miao');
		$miao_api = $this->miao_client();   //获取59秒api设置信息
		
		$data=$miao_api->ListItemsSearch('',$keyword, $miaocid, $sid, $p, 20,$start_price,$end_price);		
		
		$goods_list= $data['items_search']['items']['item'];
		$sid = $items_site_mod->where("alias='miao'")->getField('id');
		$items_nums = 0;
		foreach ($goods_list as $item) {
			$item = (array)$item;
			$item['item_key'] = 'miao_'.$item['iid'];   //用于判断此商品是否存在
			$item['sid'] = $sid;
			$this->miao_collect_insert($item, $cate_id);   //数据入库
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}

		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_miao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_miao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_miao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			$this->collect_success('采集完成', '', 'collect');
		} else {			
			$jump_array=array(
				'cate_id'=>$cate_id,				
				'miaocid'=>$miaocid,
				'start_price'=>$start_price,
				'end_price'=>$end_price,
				'sid'=>$sid,
				'keyword'=>$keyword,			
				'pages'=>$pages,
				'p'=>$p+1
			);	
			
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页', U('items_collect/catemiao_collect_jump',$jump_array));
		}
	}
    
    
    
    //采集淘宝数据分类采集
	public function catetaobao_collect()
	{
		if (isset($_POST['dosubmit'])) {
			
			$cate_id = isset($_POST['cid']) && intval($_POST['cid']) ? intval($_POST['cid']) : $this->error('请选择分类');			
			$pages = isset($_POST['pages']) && intval($_POST['pages']) ? intval($_POST['pages']) : 1;
			
			$cate_id=$_POST['cid'];
			$taocid=$_POST['taocid'];
			$start_price=$_POST['start_price'];		
			$end_price=$_POST['end_price'];		
			$start_commissionRate=$_POST['start_commissionRate'];
			$end_commissionRate=$_POST['end_commissionRate'];
			$start_commissionNum=$_POST['start_commissionNum'];				
			$end_commissionNum=$_POST['end_commissionNum'];		
			$start_totalnum=$_POST['start_totalnum'];		
			$end_totalnum=$_POST['end_totalnum'];		
			$levelstart=$_POST['levelstart'];		
			$levelend=$_POST['levelend'];		
			$keyword=$_POST['keyword'];
			$jump_array=array(
				'cate_id'=>$cate_id,				
				'taocid'=>$taocid,
				'start_price'=>$start_price,
				'end_price'=>$end_price,
				'start_commissionRate'=>$start_commissionRate,
				'end_commissionRate'=>$end_commissionRate,
				'start_commissionNum'=>$start_commissionNum,
				'end_commissionNum'=>$end_commissionNum,
				'start_totalnum'=>$start_totalnum,
				'end_totalnum'=>$end_totalnum,			
				'levelstart'=>$levelstart,
				'levelend'=>$levelend,	
				'keyword'=>$keyword,			
				'pages'=>$pages
			);
			$this->redirect('items_collect/catetaobao_collect_jump',$jump_array);
		}
		
		$_GET=setFormString($_GET); 
		//淘宝分类id
		$taocid=$_GET['taocid'];
		$start_price=$_GET['start_price'];		
		$end_price=$_GET['end_price'];		
		$start_commissionRate=$_GET['start_commissionRate'];
		$end_commissionRate=$_GET['end_commissionRate'];
		$start_commissionNum=$_GET['start_commissionNum'];				
		$end_commissionNum=$_GET['end_commissionNum'];		
		$start_totalnum=$_GET['start_totalnum'];		
		$end_totalnum=$_GET['end_totalnum'];		
		$levelstart=$_GET['levelstart'];		
		$levelend=$_GET['levelend'];		
		$keyword=$_GET['keyword'];		
		
		$this->assign('taocid',$taocid);
		$this->assign('start_price',$start_price);
		$this->assign('end_price',$end_price);
		$this->assign('start_commissionRate',$start_commissionRate);
		$this->assign('end_commissionRate',$end_commissionRate);
		$this->assign('start_commissionNum',$start_commissionNum);
		$this->assign('end_commissionNum',$end_commissionNum);
		$this->assign('start_totalnum',$start_totalnum);
		$this->assign('end_totalnum',$end_totalnum);
		$this->assign('levelstart',$levelstart);
		$this->assign('levelend',$levelend);
		$this->assign('keyword',$keyword);
		//获取分类
		$items_cate_mod = D('items_cate');
		$cate_list=$items_cate_mod->get_top2_list();
		$this->assign('cate_list', $cate_list);
		
		$this->display();
	}	
	
	//采集淘宝数据跳转页面
	public function catetaobao_collect_jump()
	{
		$cate_id= isset($_GET['cate_id']) && intval($_GET['cate_id']) ? intval($_GET['cate_id']) : $this->error('请选择分类');		
		$pages = isset($_GET['pages']) && intval($_GET['pages']) ? intval($_GET['pages']) : 1;
		$taocid=$_GET['taocid'];
		$start_price=$_GET['start_price'];		
		$end_price=$_GET['end_price'];		
		$start_commissionRate=$_GET['start_commissionRate'];
		$end_commissionRate=$_GET['end_commissionRate'];
		$start_commissionNum=$_GET['start_commissionNum'];				
		$end_commissionNum=$_GET['end_commissionNum'];		
		$start_totalnum=$_GET['start_totalnum'];		
		$end_totalnum=$_GET['end_totalnum'];		
		$levelstart=$_GET['levelstart'];		
		$levelend=$_GET['levelend'];		
		$keyword=$_GET['keyword'];
		
		$p = isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;//当前页
		$items_cate_mod = D('items_cate');
		$items_site_mod = D('items_site');
		$collect_taobao_mod = D('collect_taobao');
		$tb_top = $this->taobao_client();
		$req = $tb_top->load_api('TaobaokeItemsGetRequest');
		$req->setFields("num_iid,title,nick,pic_url,price,click_url,shop_click_url,seller_credit_score,item_location,volume,commission");		
		$req->setPid($this->setting['taobao_pid']);
		$req->setNick($this->setting['taobao_usernick']);
		
		$req->setKeyword($keyword);
		$req->setCid($taocid);
		$req->setStartPrice($start_price);
		$req->setEndPrice($end_price);
		$req->setStartCredit($levelstart);
		$req->setEndCredit($levelend);
		$req->setStartCommissionRate($start_commissionRate);
		$req->setEndCommissionRate($end_commissionRate);
		$req->setStartCommissionNum($start_commissionNum);
		$req->setEndCommissionNum($end_commissionNum);
		$req->setStartTotalnum($start_totalnum);
		$req->setEndTotalnum($end_totalnum);
		$req->setPageNo($p);	
		$req->setPageSize(40);
		$resp = $tb_top->execute($req);
		$goods_list = (array)$resp->taobaoke_items;
		$sid = $items_site_mod->where("alias='taobao'")->getField('id');
		
		$items_nums = 0;
		foreach ($goods_list['taobaoke_item'] as $item) {
			$item = (array)$item;
			$item['item_key'] = 'taobao_'.$item['num_iid'];
			$item['sid'] = $sid;
			$this->tao_collect_insert($item, $cate_id);
			$items_nums++;
		}
		//更新分类表商品数
		if ($items_nums>0) {
			$items_cate_mod->where('id='.$cate_id)->setInc('item_nums', $items_nums);
		}

		if ($p>=$pages) {
			//记录采集时间
			$islog = $collect_taobao_mod->where('cate_id='.$cate_id)->count();
			if ($islog) {
				$collect_taobao_mod->save(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			} else {
				$collect_taobao_mod->add(array('cate_id'=>$cate_id, 'collect_time'=>time()));
			}
			$this->collect_success('采集完成', '', 'collect');
		} else {
			
			$jump_array=array(
				'cate_id'=>$cate_id,				
				'taocid'=>$taocid,
				'start_price'=>$start_price,
				'end_price'=>$end_price,
				'start_commissionRate'=>$start_commissionRate,
				'end_commissionRate'=>$end_commissionRate,
				'start_commissionNum'=>$start_commissionNum,
				'end_commissionNum'=>$end_commissionNum,
				'start_totalnum'=>$start_totalnum,
				'end_totalnum'=>$end_totalnum,			
				'levelstart'=>$levelstart,
				'levelend'=>$levelend,	
				'keyword'=>$keyword,			
				'pages'=>$pages,
				'p'=>$p+1
			);			
			$this->collect_success('第 <em class="blue">'.$p.'</em> 页采集完成，开始采集下一页，共 <em class="blue">'.$pages.'</em> 页',
			 U('items_collect/catetaobao_collect_jump',$jump_array));
		}
	}
	//采集评论
	function collect_comments(){
		if(isset($_GET['cid'])&&isset($_GET['priority'])){
			//获取马甲
			$user_mod=D('user');
			$majia=$user_mod->field('id,name')->where('is_majia=1 AND status=1')->select();
					
			$cate_id=intval($_GET['cid']);
			$priority=$_GET['priority'];			
			$pagesize=$_GET['pagesize'];    
			$cmt_num=$_GET['cmt_num'];			
			 //把采集信息写入缓存
	        F('cmt_taobao_setting', array(
	            'cate_id' => $cate_id,	          
	            'order' => $priority.' DESC',
	            'users' => $majia,	            
	            'pagesize' => $pagesize,	          
	            'cmt_num' => $cmt_num,	           
	        ));	     
	        $this->redirect('items_collect/collect_comments_jump');
		}
		//获取分类
		$items_cate_mod = D('items_cate');
		$cate_list=$items_cate_mod->get_top2_list();
		$this->assign('cate_list', $cate_list);
		//获取马甲
		$user_mod=D('user');
		$majia=$user_mod->field('id,name')->where('is_majia=1 AND status=1')->select();	
		$majia_str='';
		
		foreach ($majia as $key=>$value){
			$majia_str.=$value['name']."\r\n";
		}		
		$this->assign('majia',$majia_str);		
		$this->display();	
	}
	//采集评论弹窗，跳转采集	
	function collect_comments_jump(){		
		$setting = F('cmt_taobao_setting');		
		$p=isset($_GET['p'])?$_GET['p']:1;		
		$start = ($p - 1) * $setting['pagesize'];
		$item_mod = D('items');
        $item_list = $item_mod->field('id,item_key')->where("sid=1 AND cid={$setting['cate_id']}")->order($setting['order'])->limit($start.','.$setting['pagesize'])->select();		
        if(count($item_list)>0){
        	foreach ($item_list as $val) {        		
	            $iid = array_pop(explode('_', $val['item_key']));	
	            $this->collect_one_goods($iid, $val['id'], $setting['cmt_num'], $setting['users']);          
	        }	      
	        $this->collect_success('正在采集请稍后..',U('items_collect/collect_comments_jump',array('p'=>$p+1)));
        }else{
        	$this->collect_success('采集完成', '', 'collect');     
        }        

	}	
 	/**
     * 采集指定商品的评论
     */
    private function collect_one_goods($iid, $item_id, $cmt_num, $users) {    
    	import("ORG.Net.Http");
        $seller = $this->get_seller_id($iid);      
        if (!$seller['id']) return false;
        $item_mod = D('items');
        $item_comment_mod = D('user_comments');
        if ($seller['type'] == 'tmall') {
            $rate_tmall_api = 'http://rate.tmall.com/list_detail_rate.htm?itemId='.$iid.'&spuId=&sellerId='.$seller_id.'&order=0&forShop=1&append=0&currentPage=1';
            $source = Http::fsockopenDownload($rate_tmall_api);
            $source = rtrim(ltrim(trim($source), '('), ')');
            $source = iconv('GBK', 'UTF-8//IGNORE', $source);
            $source = str_replace('"rateDetail":', '', $source);
            $rate_resp = json_decode($source, true);
            $rate_list = $rate_resp['rateList'];
            $is_cmt_taobao = $item_mod->where(array('id'=>$item_id))->getField('is_collect_comments');
            for ($i = 0; $i < $cmt_num; $i++) {
                $user_rand = array_rand($users);
                $time = strtotime($rate_list[$i]['rateDate']);             
          		if ($is_cmt_taobao==1) {
                    return false;
                }else{      
                	if(trim($rate_list[$i]['content'])!=''){                		
	                	$item_comment_rel=$item_comment_mod->add(array(
		                    'pid' => $item_id,
		                    'uid' => $users[$user_rand]['id'],
		                    'uname' => $users[$user_rand]['name'],
		                    'info' => ReplaceKeywords($rate_list[$i]['content']),
	                		'type'=>'item,index',
		                    'add_time' => $time,
		                ));
		                if($item_comment_rel){
		                	$item_mod->where("id='{$item_id}'")->setInc('comments',1); 		                	
		                }
                	}                	
	                
                }
            }
            //更新item表的 is_collect_comments为1同时采集评论                	
            $item_mod->where(array('id'=>$item_id))->save(array('is_collect_comments'=>1));
        } else {
            $rate_taobao_api = 'http://rate.taobao.com/feedRateList.htm?userNumId='.$seller['id'].'&auctionNumId='.$iid.'&currentPageNum=1';
            $source = Http::fsockopenDownload($rate_taobao_api);
            $source = rtrim(ltrim(trim($source), '('), ')');
            $source = iconv('GBK', 'UTF-8//IGNORE', $source);
            $rate_resp = json_decode($source, true);
            $rate_list = $rate_resp['comments'];
            
       
            $is_cmt_taobao = $item_mod->where(array('id'=>$item_id))->getField('is_collect_comments');
            for ($i = 0; $i < $cmt_num; $i++) {
                $user_rand = array_rand($users);
                $date = explode('.', $rate_list[$i]['date']);
                $time = mktime(0,0,0,$date[1],$date[2],$date[0]);            
                
          		if ($is_cmt_taobao==1) {
                    return false;
                }else{   
                	if(trim($rate_list[$i]['content'])!=''){                         	
	                	$item_comment_rel=$item_comment_mod->add(array(
		                    'pid' => $item_id,
		                    'uid' => $users[$user_rand]['id'],
		                    'uname' => $users[$user_rand]['name'],
		                    'info' => ReplaceKeywords($rate_list[$i]['content']),
	                		'type'=>'item,index',
		                    'add_time' => $time,
		                ));	 
	                    if($item_comment_rel){
		                	$item_mod->where("id='{$item_id}'")->setInc('comments',1);
		                }   
                	}       
                }
            }
            //更新item表的 is_collect_comments为1同时采集评论                	
            $item_mod->where(array('id'=>$item_id))->save(array('is_collect_comments'=>1));    
            echo '正在采集，请稍后...';
        }
    }

    /**
     * 根据商品id获取商品卖家ID
     */
    private function get_seller_id($iid){
    	import("ORG.Net.Http");
        $result = array('type'=>'taobao', 'id'=>0);
        $page_content = Http::fsockopenDownload('http://item.taobao.com/item.htm?id='.$iid);
        if (!$page_content) {
            //$page_content = Http::fsockopenDownload('http://detail.tmall.com/item.htm?id='.$iid);
            $page_content = file_get_contents('http://detail.tmall.com/item.htm?id='.$iid);
            $result['type'] = 'tmall';
        }
        preg_match('|; userid=(\d+);">|', $page_content, $out);
        $result['id'] = $out[1];
        return $result;
    }
    public function set_collect_cate(){    	
    	$setting_mod = M('setting');
		if (isset($_POST['dosubmit'])) {	
			$miao['collect_cate'] = isset($_POST['collect_cate']) && trim($_POST['collect_cate']) ? trim($_POST['collect_cate']) : '';

			$miao['collect_cate']=str_replace('，', ',', $miao['collect_cate']);
			
			foreach( $miao as $key=>$val ){
				$setting_mod->where("name='$key'")->save(array('data'=>$val));
			}	
			//清除缓存	
			if(is_dir("./admin/Runtime")){
	          deleteCacheData("./admin/Runtime"); 
	        }		
			$this->success('修改成功', U('items_collect/set_collect_cate'));
		}
		$res = $setting_mod->where("name='collect_cate'")->select();
		foreach( $res as $val )
		{
			$cateset[$val['name']] = $val['data'];
		}		
		$this->assign('cateset',$cateset);
		$this->display();
    }
    //TaobaokeItemsGetRequest 获取数据
    private function get_t_item_list($keywords,$p){
    	$tb_top = $this->taobao_client();
  		$req = $tb_top->load_api('TaobaokeItemsGetRequest');
		$req->setFields("num_iid,title,nick,pic_url,price,click_url,shop_click_url,seller_credit_score,item_location,volume,commission");		
		$req->setPid($this->setting['taobao_pid']);
		$req->setNick($this->setting['taobao_usernick']);
		$req->setStartCredit($this->setting['levelstart']);
		$req->setEndCredit($this->setting['levelend']);
		$req->setStartCommissionRate($this->setting['commission_rate_min']);
		$req->setEndCommissionRate($this->setting['commission_rate_max']);
		$req->setKeyword($keywords);		
		$req->setPageNo($p);
		$req->setPageSize(40);
		$resp = $tb_top->execute($req);
		return $resp;
		
    }
    //TaobaokeItemsCouponGetRequest 获取数据
	private function get_t_c_item_list($keywords,$p){
		$tb_top = $this->taobao_client();
		$req = $tb_top->load_api('TaobaokeItemsCouponGetRequest');
		$req->setFields("num_iid,title,nick,pic_url,price,coupon_price,click_url,shop_click_url,seller_credit_score,item_location,volume,commission");
		$req->setPid($this->setting['taobao_pid']);
		$req->setNick($this->setting['taobao_usernick']);
		$req->setKeyword($keywords);
		$req->setStartCredit($this->setting['levelstart']);
		$req->setEndCredit($this->setting['levelend']);
		$req->setStartCommissionRate($this->setting['commission_rate_min']);
		$req->setEndCommissionRate($this->setting['commission_rate_max']);
		$req->setPageNo($p);
		$req->setPageSize(40);
		$resp = $tb_top->execute($req);
		return $resp;
	}
	private function isSign(){
		if(S('sign_result')){	          
	            $result = S('sign_result');
	    }else{
        	$result=$this->getSign();		
            S('sign_result',$result,'3600');
		}
		if($result!=1){
			exit('您还未授权无法采集淘宝数据');
		}
	}
}
?>