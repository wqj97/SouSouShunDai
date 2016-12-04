<?php
ini_set('date.timezone', 'Asia/Shanghai');

//error_reporting(E_ERROR);

require_once "model/log.php";
require_once "model/lib/WxPay.Api.php";
require_once 'model/lib/WxPay.Notify.php';
require_once "model/base.php";

//log ini
$logHandler = new CLogFileHandler("logs/addFee_" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
        ) {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $this->update($data);

        $notfiyOutput = array();

        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }

    private function update($data)
    {
        global $sql;
//        log::INFO("addFee : " . json_encode($data));
        log::INFO(file_get_contents("php://input"));
        $sign = $data["out_trade_no"];
        $fee = $data["cash_fee"] / 100;
        $openId = $data["openid"];
        $isset = $sql->query("select Id from `addFee` where `payId` = '$sign'")->fetch_row();
        if ($isset) {
            return;
        }
        $maxId = $sql->query("select max(Id) from `addFee` where userId = '$openId'")->fetch_row()[0];
        $sql->query("update `orders` set addFee = 1,`consulting` = null  where Id = (select `orderId` from `addFee` where Id = '$maxId')");
        $sql->query("update `addFee` set `payId` = '$sign',`price` = '$fee' where `Id` = '$maxId'");

    }
}

$notify = new PayNotifyCallBack();
$notify->Handle(false);
