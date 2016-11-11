<?php
namespace Wang;
require_once "base.php";

class order
{
    //创建新订单
    public function newOrder($date, $price, $expire, $size, $remark, $receiveTime, $SMS)
    {
        global $sql;
        require_once "pay.php";
        $sql->query("select `position`,`phone` from `user` where Id = '$_SESSION[UID]'");
        $action = $sql->prepare("insert into orders(`userId`,`date`,`price`,`expire`,`size`,`remark`,`receiveTime`,`expressSMS`) VALUES (?,?,?,?,?,?,?,?)");
        $action->bind_param("ssssssss", $_SESSION["UID"], $date, $price, $expire, $size, $remark, $receiveTime, $SMS);
        $action->execute();
        $Id = $action->insert_id;
        if (!$action->error) {
            $No = \pay::getPay($price);
            $sql->query("update `orders` set `sign` = '$No' where Id = '$Id'");
            echo $sql->error;
            $this->watcher(alertUser, $Id);
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
        $orderInfo = $sql->query("select `Id`,`size`,`price`,`userId` from `orders` where `toker` is NULL and `payId` != 0 ORDER BY `Id` DESC limit $start,12")->fetch_all(MYSQLI_ASSOC);
        foreach ($orderInfo as $key => $val) {
            $userInfo = $sql->query("select `position`,`sexual` from `user` where Id = '$val[userId]'")->fetch_array(1);
            array_push($order, ["Id" => $val["Id"], "size" => $val["size"], "price" => $val["price"], "position" => $userInfo["position"], "sexual" => $userInfo["sexual"]]);
        }
        return $this->JSONout($order);
    }

    private function recoverOrder()
    {
        global $sql;
        $sql->query("delete from orders where `payId` = 0");
    }

    //获取订单对应的手机号
    public function getPhone($Id)
    {
        global $sql;
        $action = $sql->prepare("select `toker` from `orders` where Id = ?");
        $action->bind_param("s", $Id);
        $action->bind_result($tokerId);
        $action->execute();
        $action->fetch();
        if ($tokerId != $_SESSION["UID"]) {
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
    public function take($Id)
    {
        global $sql;

//        拉取订单信息
        $userInfo = $sql->query("select COUNT(Id),`userId`,`toker` from `orders` where Id = '$Id'")->fetch_row();
        if ($userInfo[2]) {
            return $this->JSONout(array("result" => "失败", "reason" => "订单已经被别人接啦"));
        }
        if ($userInfo[1] == $_SESSION["UID"]) {
//            排出自己接自己的单
            return $this->JSONout(array("result" => "失败", "reason" => "不能接自己的单"));
        }
        $type = $sql->query("select `type` from `user` where Id = '$_SESSION[UID]'")->fetch_row()[0];
//        判断接单权限

        if ($userInfo[0] >= userLevel[$type]) {
            return $this->JSONout(array("result" => "失败", "reason" => "超过每日接单上限"));
        } else {
            $action = $sql->prepare("update `orders` set `toker` = $_SESSION[UID] where Id = ?");
            $action->bind_param("s", $Id);
            $action->execute();
            $this->sendMessage($Id, $userInfo[1]);
            return $this->JSONout(array("result" => "成功"));
        }
    }

//  获取自己的订单
    public function getMine()
    {
        global $sql;
        $resultMineSend = $sql->query("select * from orders where userId = '$_SESSION[UID]' ORDER BY `finish`")->fetch_all(1);
        $resultMineToke = $sql->query("select * from orders where toker = '$_SESSION[UID]'ORDER BY `finish`")->fetch_all(1);
        return $this->JSONout(["我发布的" => $resultMineSend, "我接的" => $resultMineToke]);
    }

//   确认收货
    public function finish($Id)
    {
        require_once "lib/WxPay.Api.php";
        $this->recoverOrder();
        global $sql;
        $hasPay = $sql->query("select `hasPaid` from `orders` where Id = '$Id'")->fetch_row()[0];
        if ($hasPay) {
            return $this->JSONout(["result" => "失败,已经完成的订单不能再完成"]);
        }
        $userInfo = $sql->query("select `openId` from `user` where Id = (SELECT `userId` from `orders` where Id = '$Id')")->fetch_row()[0];
        if ($userInfo != $_COOKIE["openid"]) {
            exit("非法操作");
        }
        $orderInfo = $sql->query("select `payId`,`price`,`toker` from `orders` where Id = '$Id'")->fetch_row();
        $openId = $sql->query("select `openId` from `user` where Id = '$orderInfo[2]'")->fetch_row()[0];
        $input = new \WxPayToUser();
        $input->Setopenid($openId);
        $fee = $orderInfo[1] * tax * 100;
        if ($fee <= 100) {
            $fee = 100;
        }
        $input->Setamount($fee);
        $input->Setpartner_trade_no($orderInfo[0]);
        $result = \WxPayApi::payToUser($input);
        require_once "log.php";
        $logHandler = new \CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
        $log = \Log::Init($logHandler, 15);
        \log::INFO("企业支付 : result : " . json_encode($result, 256));
        if ($result["result_code"] == "SUCCESS") {
            $sql->query("update `orders` set `finish` = 1,`hasPaid` = 1 where Id = '$Id'");
        } else {
            return $this->JSONout(array("result" => "失败", "reason" => $result["err_code_des"]));
        }
        $this->sendFinishMessage($Id, $openId);
        return $this->JSONout(array("result" => "成功"));

    }

//    微信提醒
    /**
     * @param int $Id 订单Id
     * @param int $userId 接受消息的用户Id
     * @return bool 处理结果
     */
    public function sendMessage($Id, $userId)
    {
        global $sql;
        $openId = $sql->query("select `openId` from `user` where Id = '$userId'")->fetch_row()[0];
        $content = '{
           "touser":"' . $openId . '",
           "template_id":"UtIG56r5h_Fv394C6C1mMuc2RWwSs-n0j2yTnH1OJ_c",
           "url":"http://dq.97qingnian.com/index.html#/state",            
           "data":{
                   "first": {
                       "value":"您的订单已有人接单！",
                       "color":"#333"
                   },
                   "keyword1":{
                       "value":"' . $Id . '",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"' . date("H点i分s秒") . '",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"如果有任何疑问,直接回复我们会及时解答",
                       "color":"#333"
                   }
           }
       }';
        require_once "wxControl.php";
        $ACT = \wxControl::getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ACT;
        $opts = array('http' =>
            array(

                'method' => 'POST',

                'header' => 'Content-type: application/x-www-form-urlencoded',

                'content' => $content

            )
        );
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
//        return true;

    }

    /**
     * @param int $Id 订单Id
     * @param int $userId 用户Id
     * @return void
     */
    private function sendFinishMessage($Id, $userId)
    {
        $content = '{
           "touser":"' . $userId . '",
           "template_id":"YNdcfpSLmlQ1N3R2AgPIDcmUjGC7LOi27g_lMR6ULUM",
           "url":"http://dq.97qingnian.com/index.html#/state",            
           "data":{
                   "first": {
                       "value":"您的代取已经确认收货！",
                       "color":"#333"
                   },
                   "OrderSn":{
                       "value":"' . $Id . '",
                       "color":"#173177"
                   },
                   "OrderStatus": {
                       "value":"已经确认收货,请查收您的钱包",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"如果有任何疑问,直接回复我们会及时解答",
                       "color":"#333"
                   }
           }
       }';
        require_once "wxControl.php";
        $ACT = \wxControl::getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ACT;
        $opts = array('http' =>
            array(

                'method' => 'POST',

                'header' => 'Content-type: application/x-www-form-urlencoded',

                'content' => $content

            )
        );
        $context = stream_context_create($opts);
        file_get_contents($url, false, $context);
//        return true;
    }

    /**
     * @param array $Ids 接受名单(openid)
     * @param int $Id 订单Id
     * @return void
     */

    public function watcher($Ids, $Id)
    {
        foreach ($Ids as $key => $val) {
            $content = '{
           "touser":"' . $val . '",
           "template_id":"YNdcfpSLmlQ1N3R2AgPIDcmUjGC7LOi27g_lMR6ULUM",
           "url":"http://dq.97qingnian.com/index.html#/index",            
           "data":{
                   "first": {
                       "value":"有人发布了新的订单！",
                       "color":"#333"
                   },
                   "OrderSn":{
                       "value":"' . $Id . '",
                       "color":"#173177"
                   },
                   "OrderStatus": {
                       "value":"有人发布了新的订单",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"通知开发人员:'.count($Ids).'人",
                       "color":"#333"
                   }
           }
       }';
            require_once "wxControl.php";
            $ACT = \wxControl::getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ACT;
            $opts = array('http' =>
                array(

                    'method' => 'POST',

                    'header' => 'Content-type: application/x-www-form-urlencoded',

                    'content' => $content

                )
            );
            $context = stream_context_create($opts);
            file_get_contents($url, false, $context);
        }

    }

    //私有方法,接受数组变量,将它转化为JSON字符串返回
    private function JSONout($str)
    {
        return json_encode($str, JSON_UNESCAPED_UNICODE);
    }
}



