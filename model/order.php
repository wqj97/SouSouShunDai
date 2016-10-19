<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/19
 * Time: 下午11:51
 */

namespace Wang;
require_once "base.php";

class order
{
    public function newOrder($Date,$price,$expire,$size,$remark){
//        新建订单
        global $sql;
        $action = $sql->prepare("update `user` set `a` = ?,`b` = ?");
//        举例
        $action->bind_param("ss",$date,$name);
        $action->execute();
//
//

        $out = ["result"=>"成功","reason"=>""];
        return $this->JSONout($out);
//        返回result 成功或失败,失败请返回原因.
    }

    public function getOrder($page){
//        获取所有订单----根据page分页
        global $sql;
    }

    public function getPhone($Id){
//        获取订单对应的手机号
    }


    public function getOrderById($Id){
//        获取单个订单----根据Id
        global $sql;
    }


    private function JSONout($str){
//        私有方法,接受数组变量,将它转化为JSON字符串返回
        return json_encode($str,JSON_UNESCAPED_UNICODE);
    }
}