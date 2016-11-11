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
if(!isset($_SESSION["UID"])){
    exit(json_encode(["result"=>"失败","reason"=>"授权失败"],256));
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