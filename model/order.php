<?php
namespace Wang;
require_once "base.php";

class order
{
    //创建新订单
    public function newOrder($date, $price, $expire, $size, $remark, $receiveTime)
    {
        global $sql;
        $action = $sql->prepare("insert into orders(`userId`,`date`,`price`,`expire`,`size`,`remark`,`receiveTime`) VALUES (?,?,?,?,?,?,?)");
        $action->bind_param("sssssss", $_SESSION["UID"], $date, $price, $expire, $size, $remark, $receiveTime);
        $action->execute();
        if (!$action->error) {
            return $this->JSONout(array("result" => "成功"));
        } else {
            return $this->JSONout(array("result" => "失败", "reason" => $sql->error));
        }
    }

    //修改订单
    public function upOrder($Id, $date, $price, $expire, $size, $remark, $receiveTime)
    {
        global $sql;
        $action = $sql->prepare("update orders set `date`= ? ,`price`= ? ,`expire`= ? ,`size`= ? ,`remark`= ? ,`receiveTime`= ?  WHERE Id= ? ");
        $action->bind_param("sssssss", $date, $price, $expire, $size, $remark, $receiveTime, $Id);
        $action->execute();
        if (!$action->error) {
            return $this->JSONout(array("result" => "成功"));
        } else {
            return $this->JSONout(array("result" => "失败", "reason" => $sql->error));
        }
    }

    //获取所有订单----根据page分页
    public function getOrder($page)
    {
        global $sql;
        $start = ($page - 1) * 12;
        $order = [];
        $orderInfo = $sql->query("select `Id`,`size`,`price`,`userId` from `orders` ORDER BY `Id` DESC limit $start,12")->fetch_all(MYSQLI_ASSOC);
        foreach ($orderInfo as $key => $val) {
            $userInfo = $sql->query("select `position`,`sexual` from `user` where Id = '$val[userId]'")->fetch_array(1);
            array_push($order, ["Id" => $val["Id"], "size" => $val["size"], "price" => $val["price"], "position" => $userInfo["position"], "sexual" => $userInfo["sexual"]]);
        }
        return $this->JSONout($order);
    }

    //获取订单对应的手机号
    public function getPhone($Id)
    {
        global $sql;
        $action = $sql->prepare("select `toker` from `orders` where Id = ?");
        $action->bind_param("s",$Id);
        $action->bind_result($tokerId);
        $action->execute();
        $action->fetch();
        if($tokerId != $_SESSION["UID"]){
            exit("不是接单用户");
        }
        $action->free_result();

        $userId = $sql->query("select `userId` from `orders` WHERE Id='$Id'")->fetch_row()[0];
        $tel = $sql->query("select `phone` from `user` WHERE `Id`='$userId'")->fetch_row()[0];
        return $this->JSONout($tel);
    }

    //获取订单详情 根据订单Id
    public function getOrderById($Id)
    {
        global $sql;
        $result = $sql->query("select * from `orders` WHERE Id='$Id'");
        $order = $result->fetch_array(1);
        return $this->JSONout($order);
    }

//    接单
    public function take($Id){
        global $sql;

//        拉取订单信息
        $date = date("Y.m.d");
        $userInfo = $sql->query("select count(Id),`type`,`userId` from `orders` where `date` = '$date' and `toker` = $_SESSION[UID]")->fetch_row();
        if($userInfo[3] == $_SESSION["UID"]){
//            排出自己接自己的单
            return $this->JSONout(array("result" => "失败", "reason" => "不能接自己的单"));
        }

        $type = $userInfo[1];
        $count = $userInfo[0];
//        判断接单权限

        if($count >= userLevel[$type]){
            return $this->JSONout(array("result" => "失败", "reason" => "超过每日接单上限"));
        }else{
            $action = $sql->prepare("update `orders` set `toker` = $_SESSION[UID] where Id = ?");
            $action->bind_param("s",$Id);
            $action->execute();
            return $this->JSONout(array("result" => "成功"));
        }
    }

    //私有方法,接受数组变量,将它转化为JSON字符串返回
    private function JSONout($str)
    {
        return json_encode($str, JSON_UNESCAPED_UNICODE);
    }
}



