<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/30
 * Time: 下午11:55
 */
session_start();
require_once "../model/base.php";
global $sql;
$type = $sql->query("select `type` from `user` where Id = $_SESSION[UID]");
$type = $type->fetch_row();
if ($type[0] != 0) {
    exit("<h1 style='color: red'>非法操作!</h1>");
}
require_once "../model/SMS.php";
$sms = new \Wan\SMS();

switch ($_GET["action"]){
    case "get":
        $phone = $_POST["phone"];
        echo $sms->sendSMS($phone);
        break;
    case "check":
        $Id = $_POST["Id"];
        $code = $_POST["code"];
        echo $sms->checkCode($Id,$code);
}