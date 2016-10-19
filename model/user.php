<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/19
 * Time: 下午11:57
 */

namespace Fang;
require_once "base.php";

class user
{
    public function getUser($Id){
//        获取单个用户信息,Id是订单Id
        global $sql;
        $out = ["result"=>"成功","reason"=>""];
        return [];
//        返回result 成功或失败,失败请返回原因.
    }


    public function updateUser($Id,$name,$position,$sexual){
//        修改用户信息,Id是用户编号
        global $sql;
        $action = $sql->prepare("update `user` set `a` = ?,`b` = ?");
//        举例
        $action->bind_param("ss",$Id,$name);
        $action->execute();
    }

    public function editPhone($Id,$phone){
//        修改手机号
        return [];
    }






    private function JSONout($str){
//        私有方法,接受数组变量,将它转化为JSON字符串返回
        return json_encode($str,JSON_UNESCAPED_UNICODE);
    }
}