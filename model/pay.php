<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/20
 * Time: 上午12:10
 */
//支付模块
//namespace Wan;
require_once "lib/WxPay.Api.php";
require_once "base.php";

class pay
{

    /**
     * 支付模块
     * @param int $fee 价格
     * @return string JavaScript,执行以付款
     */


    static public function getPay($fee)
    {
        require_once "lib/WxPay.JsApiPay.php";

        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $_COOKIE["openid"];
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("定金支付");
        $input->SetAttach("定金支付");
        $sign = WxPayConfig::MCHID . date("YmdHis");
        $input->SetOut_trade_no($sign);
        $input->SetTotal_fee($fee * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("定金");
        $input->SetNotify_url("http://dq.97qingnian.com/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);

        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        echo 'WeixinJSBridge.invoke(\'getBrandWCPayRequest\',';
        echo $jsApiParameters;
        echo ',function (res) {if (res.err_msg == "get_brand_wcpay_request:ok") {that.$router.push(\'/index\')}else{that.cancel()}});';
        return $sign;
    }

    /**
     * 加价模块
     * @param int $fee 价格
     * @return string JavaScript,执行以加价
     */


    static public function addFee($fee)
    {
        require_once "lib/WxPay.JsApiPay.php";

        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $_COOKIE["openid"];
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("加价支付");
        $input->SetAttach("加价支付");
        $sign = WxPayConfig::MCHID . date("YmdHis");
        $input->SetOut_trade_no($sign);
        $input->SetTotal_fee($fee * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("加价");
        $input->SetNotify_url("http://dq.97qingnian.com/addFee.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);

        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        echo 'WeixinJSBridge.invoke(\'getBrandWCPayRequest\',';
        echo $jsApiParameters;
        echo ',function (res) {if (res.err_msg == "get_brand_wcpay_request:ok") {location.reload();}else{cancel()}});';
        return $sign;
    }

    public function cancel($Id)
    {
        require_once "log.php";
        $logHandler = new CLogFileHandler("../logs/refund_" . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);
        global $sql;
        $action = $sql->prepare("select `userId`,`payId`,`price`,`addFee` from `orders` where Id = ?");
        $action->bind_param("s", $Id);
        $action->bind_result($userId, $payId, $fee, $addFee);
        $action->execute();
        $action->fetch();
        $action->free_result();
        if ($userId != $_SESSION["UID"]) {
            return $this->toJSON(["result" => "失败", "reason" => "授权失败"]);
        }

        $fee = $fee * 100;
        $input = new WxPayRefund();
        $input->SetOut_trade_no($payId);
        $input->SetOut_refund_no($Id);
        $input->SetTotal_fee($fee);
        $input->SetRefund_fee($fee);
        $input->SetOp_user_id("1406450002");
        WxPayApi::refund($input);
        $sql->query("update `orders` set finish = 2 where Id = '$Id'");
        if ($addFee == 1) {
            $addFees = $sql->query("select Id,payId,price from addFee where orderId = '$Id'")->fetch_all(1);
            foreach ($addFees as $key => $val) {
               $fee = $val["price"] * 100;
                $input = new WxPayRefund();
                $input->SetOut_trade_no($val["payId"]);
                $input->SetOut_refund_no($val["Id"]);
                $input->SetTotal_fee($fee);
                $input->SetRefund_fee($fee);
                $input->SetOp_user_id("1406450002");
                log::INFO("$key _ $val[payId] _ ". json_encode(WxPayApi::refund($input),256));
            }
            return $this->toJSON(["result" => "成功"]);
        }
        return $this->toJSON(["result" => "成功"]);
    }

    private function toJSON($str)
    {
        return json_encode($str, 256);
    }
}