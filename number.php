<style>
    body{
        white-space: pre;
    }
</style>
<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/12/8
 * Time: 下午10:35
 */
require_once "model/base.php";
global $sql;
$phone = $sql->query("SELECT `phone` FROM `user` WHERE `phone` IS NOT NULL")->fetch_all(1);
foreach ($phone as $key => $val){
    echo $val["phone"].",";
    if($key % 10 == 0){
        echo "</br>";
    }
}