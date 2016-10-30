<?php
namespace Wang;
require_once "base.php";

class order
{
    //创建新订单
    public function newOrder($date,$price,$expire,$size,$remark,$receiveTime){
        global $sql;
        $action = $sql->prepare("insert into orders(`date`,`price`,`expire`,`size`,`remark`,`receiveTime`) VALUES (?,?,?,?,?,?)");
        $action->bind_param("ssssss",$date,$price,$expire,$size,$remark,$receiveTime);
        $action->execute();
        if(!$action->error){
            return $this->JSONout(array("result"=>"成功"));
        }
       else{
           return $this->JSONout(array("result"=>"失败","reason"=>$sql->error));
       }
    }
    //修改订单
    public function upOrder($Id,$date,$price,$expire,$size,$remark,$receiveTime){
        global $sql;
        $action=$sql->prepare("update orders set `date`='$date',`price`='$price',`expire`='$expire',`size`='$size',`remark`='$remark',`receiveTime`='$receiveTime' WHERE Id='$Id'");
        $action->bind_param("ssssss",$date,$price,$expire,$size,$remark,$receiveTime);
        $action->execute();
        if(!$action->error){
            return $this->JSONout(array("result"=>"成功"));
        }
        else{
            return $this->JSONout(array("result"=>"失败","reason"=>$sql->error));
        }
    }

    //获取所有订单----根据page分页
    public function getOrder($page){
        global $sql;
        $start = ($page -1) * 12;
        $order = [];
        $orderInfo = $sql->query("select `Id`,`size`,`price`,`userId` from `orders` ORDER BY `Id` DESC limit $start,12")->fetch_all(1);
        foreach ($orderInfo as $key=>$val){
            $userInfo = $sql->query("select `position`,`sexual` from `user` where Id = '$orderInfo[userId]'")->fetch_array(1);
            array_push($order,["Id"=>$val["Id"],"size"=>$val["size"],"price"=>$val["price"],"position"=>$userInfo["position"],"sexual"=>["sexual"]]);
        }
        return $this->JSONout($order);
    }

    //获取订单对应的手机号
    public function getPhone($Id){
        global $sql;
        $userId=$sql->query("select `userId` from `orders` WHERE Id='$Id'");
        $tel=$sql->query("select `phone` from `user` WHERE `Id`='$userId'")->fetch_row();
        return $this->JSONout($tel);
    }

    //获取订单详情 根据订单Id
    public function getOrderById($Id){
        global $sql;
        $result=$sql->query("select * from `orders` WHERE Id='$Id'");
        $order=$result->fetch_array(1);
        return $this->JSONout($order);
    }

    //私有方法,接受数组变量,将它转化为JSON字符串返回
    private function JSONout($str){
        return json_encode($str,JSON_UNESCAPED_UNICODE);
    }
}



