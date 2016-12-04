<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/11/15
 * Time: 下午10:12
 */
require_once "../model/log.php";
error_reporting(2048);
$type = $_GET["type"];
switch ($type){

    case "xsh":
        $logHandler = new CLogFileHandler("../logs/xxh_qrCode.log");
        $log = Log::Init($logHandler, 15);
        log::INFO("学生会扫码");
        break;
    default:
        break;
}
echo '<script>location.href = "http://weixin.qq.com/r/DjoEHMLEjZ-yrYZ-928c"</script>';
