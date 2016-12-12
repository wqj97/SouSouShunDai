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
if (!isset($_SESSION['UID'])) {
    exit (json_encode(["result" => "失败", "reason" => "授权失败"], 256));
}
global $sql;
require_once "../model/order.php";

$order = new \Wang\order();
switch ($_GET['action']) {
    case "new":
        isset($_POST["type"]) ? : $_POST["type"] = 0;
        if ($_POST["type"] == 0) {
            echo $order->newOrder($_POST["date"], $_POST["price"], $_POST["size"], $_POST["remark"], $_POST["SMS"], $_POST["type"]);
        } else {
            echo $order->newOrder($_POST["date"],$_POST["price"],$_POST["message"],$_POST["autoPosition"],$_POST["keyWord"],$_POST["type"]);
        }
        break;
//    case "getAll":
//        echo $order->getOrder($_POST["page"]);
//        break;
    case "get":
        echo $order->getOrderById($_POST["Id"]);
        break;
    case "edit":
        echo $order->upOrder($_POST["Id"], $_POST["date"], $_POST["price"], $_POST["size"], $_POST["remark"]);
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
    case "getSingleList":
        echo $order->getSingleListOrder($_POST["page"]);
        break;
    case "consult";
        echo $order->consult($_POST["Id"], $_POST["command"]);
        break;
    case "reminder":
        echo $order->reminder($_POST["Id"]);
        break;
    case "cancel":
        require_once "../model/base.php";
        require_once "../model/pay.php";
        $toker = $sql->query("select `toker` from `orders` where Id = '$_POST[Id]'")->fetch_row()[0];
        if ($toker) {
            echo json_encode(["result" => "失败", "reason" => "已经有人接单,不能取消或订单不存在"], 256);
            return;
        }
        $pay = new pay();
        echo $pay->cancel($_POST["Id"]);
        break;
    case "count":
        $count = $sql->query("select count(Id) from orders")->fetch_row()[0];
        $doing = $sql->query("select count(Id) from orders where finish = 0 and toker is not NULL ")->fetch_row()[0];
        echo json_encode(["result" => "成功", "count" => "$count", "doing" => $doing], 256);

}
