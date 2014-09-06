<?php
/**
 * TOP API: taobao.caipiao.present.stat.get request
 * 
 * @author auto create
 * @since 1.0, 2012-09-28 16:44:35
 */
class CaipiaoPresentStatGetRequest
{
	/** 
	 * 指定查询的天数，从当前日期（不包括当前日期）向前推算的天数，可为空。如果为空、0、负数或者大于90天，则设置为默认的90天。举例：当天是20120703， days=2， 则统计数据的日期为：20120702，20120701.
	 **/
	private $days;
	
	/** 
	 * 送彩方的淘宝数字ID，不可为空、0和负数。
	 **/
	private $userNumId;
	
	private $apiParas = array();
	
	public function setDays($days)
	{
		$this->days = $days;
		$this->apiParas["days"] = $days;
	}

	public function getDays()
	{
		return $this->days;
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
		return "taobao.caipiao.present.stat.get";
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
