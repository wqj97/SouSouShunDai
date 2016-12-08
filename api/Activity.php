<style>
    body{
        white-space: pre;
    }
</style>
<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/12/6
 * Time: 下午2:37
 */
require_once "../model/base.php";
require_once "../model/aliSDK/TopSdk.php";
//error_reporting(2048);
if (isset($_GET["token"])){
    if($_GET["token"] != "Wanqj97..."){
        exit("没有执行权限");
    }
}else{
    exit("没有执行权限");
}
global $sql;
//第一步,取出所有填写了短信的手机号和用户Id,且还未发过促销短信

$users = $sql->query("select `Id`,`phone` from user where `phone` is not null and `discountSend` is null limit 15")->fetch_all(1);


//第二部,准备手机号列表和数据库更新

$phone  = [];
foreach ($users as $key => $val){
    array_push($phone,$val["phone"]);
}

//第三部,生成短网址

$url = "http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long=";
$dUrl = [];
foreach ($users as $key => $val){
    $dwzUrl = $url."http://dq.97qingnian.com/api/discount.php?Id=".$val["Id"];
    $dwz = file_get_contents($dwzUrl);
    $tmp = json_decode($dwz,true)[0];
//    如果短网址获取失败,重试5次,再失败就令值为false
    if(!$dwz){
        for($i=0;$i == 5 || $dwz; $i++){
            $dwz = file_get_contents($dwzUrl);
            $tmp = json_decode($dwz,true)[0];
            if ($i == 5){
                $tmp['url_short'] = false;
            }
        }
    }
    array_push($dUrl,$tmp['url_short']);
}
//var_dump($dUrl);
//第三部,剔除失败对象,生成发送对象

$send = [];
foreach ($phone as $key => $val){
    if($dUrl[$key]){
        $valuabelPhone = ["phone"=>$val,"dUrl"=>$dUrl["$key"]];
        array_push($send,$valuabelPhone);
    }
}

//第四部,调用阿里sdk发送短信




//第五步,输出成功列表

echo "<table><tr><th>手机号</th><th>短网址</th></tr>";
foreach ($send as $key => $val){
    echo "<tr><td>$val[phone]</td><td>$val[dUrl]</td></tr>";
}
echo "</table>";