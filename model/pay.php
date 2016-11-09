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
        $input->SetTotal_fee($fee);
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
        echo ',function (res) {if (res.err_msg == "get_brand_wcpay_request:ok") {that.$router.push(\'/index\');}});';
        return $sign;
    }

    public function cancel($Id)
    {
        global $sql;
        $action = $sql->prepare("select `userId`,`payId`,`price` from `orders` where Id = ?");
        $action->bind_param("s", $Id);
        $action->bind_result($userId, $payId,$fee);
        $action->execute();
        $action->fetch();
        $action->free_result();
        if ($userId != $_SESSION["UID"]) {
            return $this->toJSON(["result" => "失败", "reason" => "授权失败"]);
        }
        $input = new WxPayRefund();
        $input->SetOut_trade_no($payId);
        $input->SetOut_refund_no($Id);
        $input->SetTotal_fee($fee);
        $input->SetRefund_fee($fee);
        $input->SetOp_user_id("1406450002");
        WxPayApi::refund($input);
        $sql->query("update `orders` set finish = 2 where Id = '$Id'");
    }

    static public function payToUser()
    {

        $nonceStr = WxPayApi::getNonceStr();


        $xml = '
        <xml>
        <mch_appid>' . appId . '</mch_appid>
        <mchid>1406450002</mchid>
        <nonce_str>3PG2J4ILTKCH16CQ2502SI8ZNMTM67VS</nonce_str>
        <partner_trade_no>100000982014120919616</partner_trade_no>
        <openid>ohO4Gt7wVPxIT1A9GjFaMYMiZY1s</openid>
        <check_name>OPTION_CHECK</check_name>
        <re_user_name>张三</re_user_name>
        <amount>100</amount>
        <desc>节日快乐!</desc>
        <spbill_create_ip>10.2.3.10</spbill_create_ip>
        <sign>C97BDBACF37622775366F38B629F45E3</sign>
        </xml>
        ';

    }

    private function makeSign($n)
    {

    }

    private function toJSON($str)
    {
        return json_encode($str, 256);
    }
}