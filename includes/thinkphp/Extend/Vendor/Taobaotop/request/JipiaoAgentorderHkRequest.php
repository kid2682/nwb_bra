<?php
/**
 * TOP API: taobao.jipiao.agentorder.hk request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class JipiaoAgentorderHkRequest
{
	/** 
	 * hk（占座）时需要的信息，这是一个list，list中的每个元素的结构为:“乘机人姓名;pnr”(以分号进行分隔).
	 **/
	private $hkInfo;
	
	/** 
	 * 订单id
	 **/
	private $orderId;
	
	private $apiParas = array();
	
	public function setHkInfo($hkInfo)
	{
		$this->hkInfo = $hkInfo;
		$this->apiParas["hk_info"] = $hkInfo;
	}

	public function getHkInfo()
	{
		return $this->hkInfo;
	}

	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
		$this->apiParas["order_id"] = $orderId;
	}

	public function getOrderId()
	{
		return $this->orderId;
	}

	public function getApiMethodName()
	{
		return "taobao.jipiao.agentorder.hk";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->hkInfo,"hkInfo");
		RequestCheckUtil::checkMaxListSize($this->hkInfo,9,"hkInfo");
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
