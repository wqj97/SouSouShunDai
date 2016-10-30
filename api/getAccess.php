<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/10
 * Time: 上午10:57
 */
session_start();
require_once "../model/base.php";
global $sql;
if (isset($_GET["code"])) {
    $code = $_GET["code"];
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . appId . "&secret=" . AppSecret . "&code=$code&grant_type=authorization_code";
    $access = json_decode(file_get_contents($url), true);
    if (isset($access["errcode"])) {
        header("location:../webAccess.php");
    }
    $userInfo = $sql->query("select Id,openId,RefreshToken from `user` where `openId` = '$access[openid]'")->fetch_array();
    if ($userInfo) {
        $expire = $sql->query("select ACTexpires from `user` where Id = $userInfo[Id]")->fetch_row()[0];
        if ($expire <= time()) {
            $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=" . appId . "&grant_type=refresh_token&refresh_token=" . $userInfo[2];
            $access = json_encode(file_get_contents($url), true);
            $time = time() + $access["expires_in"];
            $sql->query("update `user` set `AccessToken` = '$access[access_token]',ACTexpires = '$time',`RefreshToken`='$access[refresh_token]' WHERE Id = '$userInfo[0]'");
        }
    } else {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access[access_token]&openid=$access[openid]&lang=zh_CN";
        $userInfo = json_decode(file_get_contents($url), true);
        $time = time() + $access["expires_in"];
        $sql->query("insert into `user` (`name`,`openId`,`AccessToken`,`ACTexpires`,`RefreshToken`,`head`) VALUE ('$userInfo[nickname]','$userInfo[openid]','$access[access_token]','$time','$access[refresh_token]','$userInfo[headimgurl]')");
        $userInfo["Id"] = $sql->query("select max(Id) from `user`")->fetch_row()[0];
//        echo $sql->error;
    }
    $_SESSION['UID'] = $userInfo["Id"];
    $Cookies_expires = strtotime("+1 year");
    setcookie("openid", $userInfo["openid"], $Cookies_expires, "/");
    header("location:../index.html");

}