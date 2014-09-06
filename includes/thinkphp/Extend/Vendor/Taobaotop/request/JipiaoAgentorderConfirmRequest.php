<?php
/**
 * TOP API: taobao.jipiao.agentorder.confirm request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class JipiaoAgentorderConfirmRequest
{
	/** 
	 * 最迟出票时间
	 **/
	private $latestTicketTime;
	
	/** 
	 * 订单id
	 **/
	private $orderId;
	
	private $apiParas = array();
	
	public function setLatestTicketTime($latestTicketTime)
	{
		$this->latestTicketTime = $latestTicketTime;
		$this->apiParas["latest_ticket_time"] = $latestTicketTime;
	}

	public function getLatestTicketTime()
	{
		return $this->latestTicketTime;
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
		return "taobao.jipiao.agentorder.confirm";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->latestTicketTime,"latestTicketTime");
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
