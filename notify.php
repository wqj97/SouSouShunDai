<?php
ini_set('date.timezone','Asia/Shanghai');

//error_reporting(E_ERROR);

require_once "model/log.php";
require_once "model/lib/WxPay.Api.php";
require_once 'model/lib/WxPay.Notify.php';
require_once "model/base.php";

//log ini
$logHandler = new CLogFileHandler("logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
        if(!json_encode($data)){
            return true;
        }
        $this->update($data);

		$notfiyOutput = array();

		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
	private function update($data){
        global $sql;
        log::INFO("data : ". file_get_contents("php://input"));
        $sign = $data["out_trade_no"];
        $isset = $sql->query("select Id,userId from `orders` where `payId` = '$sign'")->fetch_row();
        if ($isset){
            return;
        }
        $sql->query("update `orders` set `payId` = '$sign' where `sign` = '$sign'");
        require_once "model/order.php";
        \Wang\order::watcher($data,alertUser);
    }
}

$notify = new PayNotifyCallBack();
$notify->Handle(false);
