<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/10
 * Time: 上午10:55
 */
session_start();
require_once "model/base.php";
if(!isset($_COOKIE["openid"])){
    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".appId."&redirect_uri=".redirectURL."&response_type=code&scope=snsapi_userinfo#wechat_redirect";
    header("location:$url");
}else{
    global $sql;
    $UID = $sql->query("select Id from `user` where `openid` = '$_COOKIE[openid]'")->fetch_row()[0];
    $_SESSION['UID'] = $UID;
    if (!isset($_GET["to"])){
        $_GET["to"] = "index";
    }
    switch ($_GET["to"]){
        case "index":
            header("location:index.html#/index");
            break;
        case "order":
            header("location:order.html");
            break;
        case "back":
            echo("<script>history.go(-1)</script>");
            break;
    }
}