<?php
class messageAction extends baseAction
{	//显示列表
	public function index()
	{
		$model = D('UserMsg');	
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';		
		//搜索
		if ($keyword!='') {
			$where['title'] = array('like','%'.$keyword.'%');
			$where['to_user'] = $keyword;
            $where['from_user'] = $keyword;
            $where['_logic'] = 'or';
            $this->assign('keyword', $keyword);
		}		
     
		import("ORG.Util.Page");
		$count = $model->where($where)->count();
		$p = new Page($count,20);
		$message_list = $model->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('list',$message_list);
		$this->display();
	}
    //添加/发送
    public function add(){
        $this->display("sendmsg");
    }
	//回复
	public function sendmsg()  
	{
		$model = D('UserMsg');	
		if(isset($_GET['id']) ){
			$ex_goods_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
        }		

		$info = $model->where('id='.$ex_goods_id)->find();
        $this->assign('type','edit');    
        $this->assign('show_header', false);  	
		$this->assign('info',$info);
		$this->display();
	}
	//执行发送
	public function doSendMsg()
	{	
	   
		$model = D('UserMsg');
		if(false === $data = $model->create()){
			$this->error($model->error());
		}
        
        $model->id = null;	
        $model->date = time();	
		$id=intval($_POST['id']);
        $result = $model->add();

		if(false !== $result){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}	
	//删除数据
	public function delete()
	{	
	    $model = D('UserMsg');
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要删除的数据');
		}
        $id = implode(",",$_POST['id']);
		$map['id'] = array('in',$id);
        $model->where($map)->delete();
        $this->success(L('operation_success'));
	}
}
?>