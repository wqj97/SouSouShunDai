<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 16/10/19
 * Time: 23:19:12
 */
//ini_set("display_errors","off");
$_host = "127.0.0.1";
$_sqlUser = "root";
$_sqlPwd = "wqj9705";
$_sqlDB = "Express";
$sql = new mysqli($_host,$_sqlUser,$_sqlPwd,$_sqlDB);
$sql->query("set names utf8");
header("Access-Control-Allow-Origin: *");
define("appId","wx573174f0dbdb80b2");
define("AppSecret","986759a218f79dbe22683dfedc94dfa2");
define("redirectURL","http%3a%2f%2fdq.97qingnian.com%2fapi%2fgetAccess.php");
define("userLevel",[3,10,9999]);