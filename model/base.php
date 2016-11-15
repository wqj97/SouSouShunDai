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
//header("Access-Control-Allow-Origin: *");
define("appId","wx573174f0dbdb80b2");
define("AppSecret","986759a218f79dbe22683dfedc94dfa2");
define("redirectURL","http%3a%2f%2fdq.97qingnian.com%2fapi%2fgetAccess.php");
define("userLevel",[3,10,9999]);
define("tax",0.9);
define("alertUser",["o2NgYwdiseIwOhdKTGSXFrZawK4I","o2NgYwZNncz3EtcAxEKvChgVt6GU","o2NgYwVzsdtyXb4oONEla9PvzUNs","o2NgYwQqKEx19Mca9T_dt14YYtqI","o2NgYwbgl8vX8Jw_k7XGOIdtlj8M","o2NgYwV2OmouJ3_vOLh0ZX-oqB3Y","o2NgYwZzMC2Em6QciaxQexe2bn0Y","o2NgYwbt5mX3KqwKtWYn3t9_EWZw","o2NgYwdiksILdv24EGvVBNqxNwhE","o2NgYwSziaufBv8PkGHlR0J2XrxU"]);
//define("alertUser",[]);