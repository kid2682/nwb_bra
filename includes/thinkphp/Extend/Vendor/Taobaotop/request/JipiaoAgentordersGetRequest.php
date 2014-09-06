<?php
/**
 * TOP API: taobao.jipiao.agentorders.get request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class JipiaoAgentordersGetRequest
{
	/** 
	 * 1. 未付款的订单
2. 待处理的订单
3. 确认出票的订单
4. 已成功并且需要解冻的订单
	 **/
	private $status;
	
	private $apiParas = array();
	
	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getApiMethodName()
	{
		return "taobao.jipiao.agentorders.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
