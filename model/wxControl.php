<?php

/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/11/10
 * Time: 上午10:23
 */
require_once "base.php";

class wxControl
{
    static public function getAccessToken(){

        $filename = dirname(__FILE__)."/system.json";
        $file = fopen($filename,"r+");
        $fileContent = json_decode(fread($file,filesize($filename)));
        if($fileContent["expires"] < time()){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".appId."&secret=".AppSecret;
            $webGet = json_decode(file_get_contents($url),true);
            $webGet["expires"] = $webGet["expires_in"] + time();
            fwrite($file,json_encode($webGet,256));
            return $webGet["access_token"];
        }else{
            return $fileContent["access_token"];
        }

    }

}