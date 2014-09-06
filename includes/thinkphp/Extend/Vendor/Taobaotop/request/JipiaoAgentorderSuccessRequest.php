<?php
/**
 * TOP API: taobao.jipiao.agentorder.success request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class JipiaoAgentorderSuccessRequest
{
	/** 
	 * 订单id
	 **/
	private $orderId;
	
	/** 
	 * 成功出票需要的信息。每个元素的结构为：旧乘机人姓名;新乘机人姓名;pnr;票号(以分号进行分隔)。有时用户输入的乘机人姓名输入错误或者有生僻字，代理商必须输入新的名字以保证验真的通过。
	 **/
	private $successInfo;
	
	private $apiParas = array();
	
	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
		$this->apiParas["order_id"] = $orderId;
	}

	public function getOrderId()
	{
		return $this->orderId;
	}

	public function setSuccessInfo($successInfo)
	{
		$this->successInfo = $successInfo;
		$this->apiParas["success_info"] = $successInfo;
	}

	public function getSuccessInfo()
	{
		return $this->successInfo;
	}

	public function getApiMethodName()
	{
		return "taobao.jipiao.agentorder.success";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
		RequestCheckUtil::checkNotNull($this->successInfo,"successInfo");
		RequestCheckUtil::checkMaxListSize($this->successInfo,9,"successInfo");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
