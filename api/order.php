<?php
/**
 * Created by PhpStorm.
 * User: wangchun
 * Date: 2016/10/30
 * Time: 10:37
 */
session_start();
require_once "../model/base.php";
//error_reporting(E_ERROR);

global $sql;
$type = $sql->query("select `type` from `user` where Id = $_SESSION[UID]");
$type = $type->fetch_row();
if ($type[0] != 0) {
    exit("<h1 style='color: red'>非法操作!</h1>");
}
require_once "../model/order.php";

$order = new \Wang\order();
switch ($_GET['action']) {
    case "new":
        isset($_POST["SMS"]) ? $_POST["SMS"] : $_POST["SMS"] = "未填写快递信";
        echo $order->newOrder($_POST["date"], $_POST["price"], $_POST["expire"], $_POST["size"], $_POST["remark"], $_POST["receiveTime"],$_POST["SMS"]);
        break;
    case "getAll":
        echo $order->getOrder($_POST["page"]);
        break;
    case "get":
        echo $order->getOrderById($_POST["Id"]);
        break;
    case "edit":
        echo $order->upOrder($_POST["Id"], $_POST["date"], $_POST["price"], $_POST["expire"], $_POST["size"], $_POST["remark"], $_POST["receiveTime"]);
        break;
    case "phone":
        echo $order->getPhone($_POST["Id"]);
        break;
    case "take":
        echo $order->take($_POST["Id"]);
        break;
    case "getMine":
        echo $order->getMine();
        break;
    case "finish":
        echo $order->finish($_POST["Id"]);
        break;
    case "cancel":
        require_once "../model/base.php";
        require_once "../model/pay.php";
        $toker = $sql->query("select `toker` from `orders` where Id = '$_POST[Id]'")->fetch_row()[0];
        if($toker){
            echo json_encode(["result"=>"失败","reason"=>"已经有人接单,不能取消或订单不存在"],256);
            return;
        }
        $pay = new pay();
        echo $pay->cancel($_POST["Id"]);
        break;
    case "count":
        $count =  $sql->query("select count(Id) from orders")->fetch_row()[0];
        echo json_encode(["result"=>"成功","count"=>"$count"],256);

}
