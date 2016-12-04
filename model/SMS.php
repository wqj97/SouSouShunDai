<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/20
 * Time: 上午12:11
 */
//短信模块
namespace Wan;
require_once "base.php";
class SMS
{
    public function sendSMS($phone){

//        创建一个SMS码返回给前端,前端把验证码发送回来验证

        global $sql;

//        判断一日是否尝试过5次和是否间隔60s;

        $date = date("Y.m.d");
        $action = $sql->prepare("select count(Id) from SMS where `phone` = ? and `date` = '$date'");
        $action->bind_param("s",$phone);
        $action->bind_result($times);
        $action->execute();
        $action->fetch();
        $action->free_result();
        $expire = $sql->query("select `expireTime` from `SMS` where `Id` = (SELECT max(Id) from SMS where `phone` = '$phone') and `date` = '$date'")->fetch_row()[0];
        $expire = (int)$expire - 540;
        if($times >=5 || $expire >= time()){
            return $this->toJSON(["result"=>"失败","reason"=>"一天不得超过五次请求,或未间隔60s"]);
        }

//        开始创建验证码

        $expireTime = time() + 600;
        $code = "".rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        $action = $sql->prepare("insert into SMS (`phone`,`date`,`expireTime`,`code`) VALUES (?,'$date','$expireTime','$code')");
        $action->bind_param("s",$phone);
        $action->execute();

//        获取用户信息

        $userInfo = $sql->query("select `name` from `user` where Id = '$_SESSION[UID]'")->fetch_row()[0];

//        发送信息
        if($userInfo == ""){
            $userInfo = "未填写";
        }
        $this->aliSend($phone,$userInfo,$code);

        return $this->toJSON(["result"=>"成功","reason"=>"$action->insert_id"]);
    }

    public function checkCode($Id,$code){

//        判断验证码是否正确

        global $sql;
        $action = $sql->prepare("select `code`,`expireTime`,`phone` from `SMS` where Id = ?");
        $action->bind_param("s",$Id);
        $action->bind_result($codeInsql,$expireTime,$phone);
        $action->execute();
        $action->fetch();
        $action->free_result();
        if($expireTime>=time() && $code == $codeInsql){
            $result = $this->updateTel($_SESSION["UID"],$phone);
            if($result["result"] == "成功"){
                return $this->toJSON(["result"=>"成功"]);
            }
        }else{
            return $this->toJSON(["result"=>"失败","reason"=>"验证码无效"]);

        }
    }
    private function updateTel($Id,$phone){
        global $sql;
        $action = $sql->prepare("update `user` set `phone` = ? where Id = ?");
        $action->bind_param("ss",$phone,$Id);
        $action->execute();
        $action->free_result();
        return ['result' => '成功'];
    }
    public function aliSend($phone,$name,$code){
        $name = str_replace("."," ",$name);
        require_once "aliSDK/TopSdk.php";
        $c = new \TopClient();
        $c ->appkey = "23488407" ;
        $c ->secretKey = "caedeb8a53a888551cc77af2da442dc7" ;
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "嗖嗖顺带" );
        $req ->setSmsParam("{name:'$name',number:'$code',expire:'10分钟'}" );
        $req ->setRecNum( $phone );
        $req ->setSmsTemplateCode( "SMS_25000030" );
        $resp = $c ->execute( $req );
        return $resp;
    }

    private function toJSON($array){
        return json_encode($array,256);
    }
}