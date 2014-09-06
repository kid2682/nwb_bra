<?php

class cash_back_logAction extends baseAction
{
	function index()
	{
		$log_mod = D('cash_back_log');
		$where = '1=1';
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		if ($keyword!='') {
			$where .= " AND uname LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}		
		import("ORG.Util.Page");
		$count = $log_mod->where($where)->count();
		$p = new Page($count,20);
		$log_list = $log_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('log_list',$log_list);
		$this->display();	
	}


}
?>