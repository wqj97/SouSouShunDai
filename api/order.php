<?php
/**
 * Created by PhpStorm.
 * User: wangchun
 * Date: 2016/10/30
 */
session_start();
require_once "../model/base.php";
global $sql;
$type = $sql->query("select `type` from `user` where Id = $_SESSION[UID]");
$type = $type->fetch_row();
if ($type[0] != 1) {
    exit("<h1 style='color: red'>非法操作!</h1>");
}
require_once "../model/order.php";

$order = new order();
switch ($_GET['action']){
    case "new":
            echo json_encode($order->newOrder($_POST["date"],$_POST["price"],$_POST["expire"],$_POST["size"],$_POST["remark"],$_POST["receiveTime"],JSON_UNESCAPED_UNICODE));
        break;
    case "getAll":
            echo json_encode($order->getOrder($_POST["page"]),JSON_UNESCAPED_UNICODE);
        break;
    case "get":
            echo json_encode($order->getOrderById($_POST["Id"]),JSON_UNESCAPED_UNICODE);
        break;
    case "edit":
            echo json_encode($order->upOrder($_POST["Id"],$_POST["date"],$_POST["price"],$_POST["expire"],$_POST["size"],$_POST["remark"],$_POST["receiveTime"],JSON_UNESCAPED_UNICODE));
        break;
    case "phone":
            echo json_encode($order->getPhone($_POST["Id"],JSON_UNESCAPED_UNICODE));
        break;




}
