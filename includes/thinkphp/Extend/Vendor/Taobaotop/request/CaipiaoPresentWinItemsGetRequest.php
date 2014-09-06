<?php
/**
 * TOP API: taobao.caipiao.present.win.items.get request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class CaipiaoPresentWinItemsGetRequest
{
	/** 
	 * 查询个数，最大值为50.如果为空、0和负数，则取默认值50
	 **/
	private $num;
	
	/** 
	 * 淘宝数字ID，不可为空，0和负数。
	 **/
	private $userNumId;
	
	private $apiParas = array();
	
	public function setNum($num)
	{
		$this->num = $num;
		$this->apiParas["num"] = $num;
	}

	public function getNum()
	{
		return $this->num;
	}

	public function setUserNumId($userNumId)
	{
		$this->userNumId = $userNumId;
		$this->apiParas["user_num_id"] = $userNumId;
	}

	public function getUserNumId()
	{
		return $this->userNumId;
	}

	public function getApiMethodName()
	{
		return "taobao.caipiao.present.win.items.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->userNumId,"userNumId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
