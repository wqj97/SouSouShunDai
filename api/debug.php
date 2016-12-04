<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/31
 * Time: 上午11:46
 */
session_start();
isset($_GET["action"]) ? $action = $_GET["action"] : $action = "";

switch ($action) {
    case "zy":
        $_SESSION["UID"] = 4;
        setcookie("openid", "o2NgYwVzsdtyXb4oONEla9PvzUNs", strtotime("+7 day"), "/");
        break;
    case "wqj":
        $_SESSION["UID"] = 20;
        setcookie("openid", "o2NgYwdiseIwOhdKTGSXFrZawK4I", strtotime("+7 day"), "/");
        header("location:/index.html#/index");
        break;
    case "del":
        session_destroy();
        setcookie("openid", "", -1, "/");
        break;
//    case "act":
//        require_once "../model/wxControl.php";
//        echo wxControl::getAccessToken();
//    case "sms":
//        require_once "../model/SMS.php";
//        $sms = new \Wan\SMS();
//        echo $sms->aliSend("13347320707","Conquer.XW","10分钟");
//        break;
    case "recover":
        require_once "../model/order.php";
        $recover = new \Wang\order();
        echo "删除无效订单数 : ".$recover->recoverOrder()." 个";
        break;
    case "doing":
        require_once "../model/base.php";
        global $sql;
        $consulting = ["无协商","要求加价","要求拒接","嗖嗖介入"];
        $result = $sql->query("select `userId`,`toker`,`price`,`consulting`,`remark`,`size`,`Id`,`date` from `orders` where finish = 0 and toker is not NULL order by Id DESC ")->fetch_all();
        echo "<meta charset='utf-8'>";
        echo "<style>td{text-align: center;margin: 2px;}tr{margin: 3px 0;border-bottom: 1px solid #333;}</style>";
        echo "<h1 style='text-align: center;'>正在进行的订单</h1>";
        echo "<table>";
        echo "<tr>
<th>编号</th>
<th>发单</th>
<th>接单</th>
<th>价格</th>
<th>大小</th>
<th>日期</th>
<th>发单手机号</th>
<th>接单手机号</th>
<th>发单收货地址</th>
<th>协商状态</th>
<th>登录他</th>
</tr>";
        foreach ($result as $key => $val){
            $sender = $sql->query("select `name`,`phone`,`position` from `user` where Id = '$val[0]'")->fetch_row();
            $toker = $sql->query("select `name`,`phone`,`position` from `user` where Id = '$val[1]'")->fetch_row();
            $consult = "";
            if (empty($val[3])){
                $val[3] = "NULL";
            }
            switch ($val[3]){
                case "NULL":
                    $consult = $consulting[0];
                    break;
                case "0":
                    $consult = $consulting[1];
                    break;
                case "2":
                    $consult = $consulting[2];
                    break;
                case "4":
                    $consult = $consulting[3];
                    break;
            }
            echo "<tr>
<td>$val[6]</td>
<td>$sender[0]</td>
<td>$toker[0]</td>
<td>$val[2]</td>
<td>$val[5]</td>
<td>$val[7]</td>
<td>$sender[1]</td>
<td>$toker[1]</td>
<td>$sender[2]</td>
<td>$consult</td>
<td><a href='debug.php?action=test&Id=$val[0]'>登录</a></td>
</tr>";
        }
        echo "</table>";
        break;
    case "consult":
        require_once "../model/order.php";
        $order = new \Wang\order();
        $_POST["fee"] = 1;
        echo "<script>document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false); function onBridgeReady() {";
        echo $order->consult(314,1)."}</script>";
        break;
    case "test" :
        require_once "../model/base.php";
        global $sql;
        $openid = $sql->query("select `openId` from `user` where Id = '$_GET[Id]'")->fetch_row()[0];
        $_SESSION["UID"] = $_GET["Id"];
        setcookie("openid", "$openid", strtotime("+1 year"), "/");
        header("location:/index.html#/index");
        break;
}