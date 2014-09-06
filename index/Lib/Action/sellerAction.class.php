<?php
class sellerAction extends baseAction{
	public function index(){	
		if($this->setting['display_b2c_ad']==1){
			//动态广告系统
			$miao_api = $this->miao_client();   //获取59秒api设置信息
			$adv_data = $miao_api->AdsGet('', '468x60');
			$ad_rel=$adv_data['ads']['ad'];
			$ad_rel=getRandArray($ad_rel);	
	       		if(count($ad_rel)>0){
				$this->assign('ad_rel',$ad_rel);
			}
		}
		$seller_cate_mod=D('seller_cate');		
		if(S('seller_cate_rel')){
            $cate_rel = S('seller_cate_rel');
        }else{
			$cate_rel=$seller_cate_mod->where("status='1'")->order('sort asc,id asc')->select();         
            S('seller_cate_rel',$cate_rel,'3600');
		}
		
		$cate_rel=$seller_cate_mod->where("status='1'")->order('sort asc,id asc')->select();
        
		//print_r($cate_rel);
		$this->assign('seller_cate',$cate_rel);
		//推荐返利商家
		$seller_list_mod=D('seller_list');
		
		if(S('seller_rec_seller')){
            $rec_seller = S('seller_rec_seller');
        }else{
			$rec_seller=$seller_list_mod->field('id,click_url,name,net_logo,site_logo,cash_back_rate')->where("status='1' AND recommend='1'")->select();	        
	        /**/
			$seler_arr=array();
			foreach ($rec_seller as $value){
				$value['click_url']=base64_encode(urlencode($value['click_url']));
				$seler_arr[]=$value;
			}		
	  
			$rec_seller=$seler_arr;          
            S('seller_rec_seller',$rec_seller,'3600');
		}	
		
		$this->assign('rec_seller',$rec_seller);   //推荐商家
		$seller_list_cate_mod=D('seller_list_cate');
		//查询seller页面显示的商家
		if(S('seller_cate_list')){
            $cate_list = S('seller_cate_list');
        }else{
			$cate_seller_rel=$seller_cate_mod->where("status='1' AND seller_status='1'")->order('sort asc,id asc')->select();
			$cate_list=array();
			foreach ($cate_seller_rel as $value){
				$list_cate_id=$seller_list_cate_mod->field('list_id')->where("cate_id='{$value['id']}'")->select();
	            
				$str='';
				foreach ($list_cate_id as $v){
					$str.='\''.$v['list_id'].'\',';
				}
				$str=substr($str, 0,-1);			
				$list_rel=$seller_list_mod->field('id,click_url,name,net_logo,site_logo,cash_back_rate')->where("id IN ($str) AND status='1'")->order('sort asc,id asc')->limit('12')->select();
	            
				$seler_arr=array();
				foreach ($list_rel as $v1){
					$v1['click_url']=base64_encode(urlencode($v1['click_url']));
					$seler_arr[]=$v1;
				}	
	            $cate_list[]=array(
					'cate_name'=>$value[name],
					'cate_id'=>$value[id],
					'seller_list'=>$seler_arr
				);
			}	        
            S('seller_cate_list',$cate_list,'3600');
		}
			
		$this->assign('cate_list',$cate_list);   //推荐商家
		//获取seo信息
	
        $this->nav_seo('seller','nav',4);
		
		$this->display();
	}
	public function cate(){
		$seller_list_cate_mod=D('seller_list_cate');
		$cate_id=isset($_GET['id'])&&is_numeric($_GET['id'])?intval($_GET['id']):'';
		//获取分类
		$seller_cate_mod=D('seller_cate');
		$cate_rel=$seller_cate_mod->where("status='1'")->order('sort asc,id asc')->select();
		//print_r($cate_rel);
		$this->assign('seller_cate',$cate_rel);
		$list_cate_id=$seller_list_cate_mod->field('list_id')->where("cate_id='{$cate_id}'")->select();
		$str='';
		foreach ($list_cate_id as $v){
			$str.='\''.$v['list_id'].'\',';
		}
		$str=substr($str, 0,-1);
		$seller_list_mod=D('seller_list');
		//获取内容显示数据并分页
		//$list_rel=$seller_list_mod->where("id IN ($str) AND status='1'")->order('sort asc,id asc')->select();	
		
	
		import("ORG.Util.Page");
		$count = $seller_list_mod->where("id IN ($str) AND status='1'")->count();
		$p = new Page($count,10);
		$seller_list = $seller_list_mod->field('id,click_url,freeshipment,installment,has_invoice,description,name,net_logo,site_logo,cash_back_rate')->where("id IN ($str) AND status='1'")->limit($p->firstRow.','.$p->listRows)->order('sort asc,id asc')->select();
        
		$seler_arr=array();
		foreach ($seller_list as $value){
			$value['click_url']=base64_encode(urlencode($value['click_url']));
			$seler_arr[]=$value;
		}		
		$page = $p->show();
		$this->assign('page',$page);	
		$this->assign('seller_list',$seler_arr);		
		$this->assign('cate_id',$cate_id);
		//seo
		$this->nav_seo('seller','nav',4);
		$this->display();
	}
	
}
?>