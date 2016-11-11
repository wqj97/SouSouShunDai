<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/31
 * Time: 上午11:46
 */
session_start();
isset($_GET["action"]) ? $action = $_GET["action"] : $action = "zy";

switch ($action) {
    case "zy":
        $_SESSION["UID"] = 4;
        setcookie("openid", "o2NgYwVzsdtyXb4oONEla9PvzUNs", strtotime("+1 year"), "/");
        break;
    case "wqj":
        $_SESSION["UID"] = 5;
        setcookie("openid", "o2NgYwdiseIwOhdKTGSXFrZawK4I", strtotime("+1 year"), "/");
        break;
    case "del":
        session_destroy();
        setcookie("openid", "", -1, "/");
        break;
    case "pay":
        require_once "../model/pay.php";
        $pay = new pay();
        echo "<script> if (typeof WeixinJSBridge == \"undefined\") {
            if (document.addEventListener) {
                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
            } else if (document.attachEvent) {
                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
            }
        } else {
            onBridgeReady();
        }
        
        function onBridgeReady() {
          ";
        $pay->getPay(1);
        echo "}</script>";
        break;
    case "message":
        require_once "../model/order.php";
        $order = new \Wang\order();
        echo $order->sendMessage(19,20);
        break;
    case "act":
        require_once "../model/wxControl.php";
        echo wxControl::getAccessToken();
}