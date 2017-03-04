<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/2/18
 * Time: 上午3:48
 */
$url= $_GET['url'];
$img = file_get_contents($url);
$fn = substr(strrchr($url, "/"), 1);
file_put_contents($fn,$img);
echo $fn;