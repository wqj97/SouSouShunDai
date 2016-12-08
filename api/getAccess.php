<style>
    html{
        white-space: pre-wrap;
    }
</style>
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
    $userInfo = $sql->query("select Id,openId from `user` where `openId` = '$access[openid]'")->fetch_array(1);
    if ($userInfo) {
        $expire = $sql->query("select ACTexpires from `user` where Id = $userInfo[Id]")->fetch_row()[0];
        if ($expire <= time()) {
            $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".appId."&grant_type=refresh_token&refresh_token=".$access["refresh_token"];
            $access = json_decode(file_get_contents($url), true);
            $time = time() + $access["expires_in"];
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access[access_token]&openid=$access[openid]&lang=zh_CN";
            $user = json_decode(file_get_contents($url), true);
            $sql->query("update `user` set `AccessToken` = '$access[access_token]',ACTexpires = '$time',`name`='$user[nickname]',`head` = '$user[headimgurl]' WHERE Id = '$userInfo[Id]'");
        }
    } else {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access[access_token]&openid=$access[openid]&lang=zh_CN";
        $userInfo = json_decode(file_get_contents($url), true);
        $time = time() + $access["expires_in"];
        $sql->query("insert into `user` (`name`,`openId`,`AccessToken`,`ACTexpires`,`head`) VALUE ('$userInfo[nickname]','$userInfo[openid]','$access[access_token]','$time','$userInfo[headimgurl]')");
        $userInfo["Id"] = $sql->insert_id;
    }
    $_SESSION['UID'] = $userInfo["Id"];
    $Cookies_expires = strtotime("+7 day");
    setcookie("openid", isset($userInfo["openId"]) ? $userInfo["openId"] : $userInfo["openid"], $Cookies_expires, "/");
    header("location:../index.html#/profile/phone");
}