<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/31
 * Time: 上午11:46
 */
session_start();
$_SESSION["UID"] = 4;
setcookie("openid","o2NgYwVzsdtyXb4oONEla9PvzUNs",strtotime("+1 year"),"/");