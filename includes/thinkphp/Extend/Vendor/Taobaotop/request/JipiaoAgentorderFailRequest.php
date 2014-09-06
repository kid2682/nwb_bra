<?php
/**
 * TOP API: taobao.jipiao.agentorder.fail request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class JipiaoAgentorderFailRequest
{
	/** 
	 * 备注
	 **/
	private $memo;
	
	/** 
	 * 订单id
	 **/
	private $orderId;
	
	/** 
	 * Reason字段为失败原因：分为
1．客户要求失败订单
2．此舱位已售完（经济舱或特舱）
3．剩余座位少于用户购买数量
4．特价管理里录入的特价票已经售完
5．假舱（请及时通过旺旺或者电话反馈给淘宝工作人员）
0．其它（请在备注中说明原因）
	 **/
	private $reason;
	
	private $apiParas = array();
	
	public function setMemo($memo)
	{
		$this->memo = $memo;
		$this->apiParas["memo"] = $memo;
	}

	public function getMemo()
	{
		return $this->memo;
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

	public function setReason($reason)
	{
		$this->reason = $reason;
		$this->apiParas["reason"] = $reason;
	}

	public function getReason()
	{
		return $this->reason;
	}

	public function getApiMethodName()
	{
		return "taobao.jipiao.agentorder.fail";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->memo,100,"memo");
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
		RequestCheckUtil::checkNotNull($this->reason,"reason");
		RequestCheckUtil::checkMaxValue($this->reason,5,"reason");
		RequestCheckUtil::checkMinValue($this->reason,0,"reason");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
