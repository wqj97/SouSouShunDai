<?php
/**
 * Created by PhpStorm.
 * User: wangchun
 * Date: 2016/10/30
 * Time: 10:37
 */
session_start();
require_once "../model/base.php";
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
        echo $order->newOrder($_POST["date"], $_POST["price"], $_POST["expire"], $_POST["size"], $_POST["remark"], $_POST["receiveTime"]);
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


}
