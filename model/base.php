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
$_sqlDB = "zhaoxiangguan";
$sql = new mysqli($_host,$_sqlUser,$_sqlPwd,$_sqlDB);
$sql->query("set names utf8");
define("appId","");
define("AppSecret","");
define("redirectURL","http%3a%2f%2fdq.97qingnian.com%2fapi%2fgetAccess.php");