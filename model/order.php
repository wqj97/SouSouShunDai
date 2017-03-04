<?php
namespace Wang;
require_once "base.php";

class order
{

    //创建新订单
    public function newOrder($date, $price, $_3, $_4, $_5, $type)
    {
        global $sql;
        require_once "pay.php";
        if ($type == 0) {
            $action = $sql->prepare("INSERT INTO orders(`userId`,`date`,`price`,`size`,`remark`,`expressSMS`,`type`) VALUES (?,?,?,?,?,?,?)");
            $action->bind_param("sssssss", $_SESSION["UID"], $date, $price, $_3, $_4, $_5, $type);
            $action->execute();
            $Id = $action->insert_id;
            if (!$action->error) {
                $No = \pay::getPay($price, $_SESSION["UID"]);
                $sql->query("update `orders` set `sign` = '$No' where Id = '$Id'");
                echo $sql->error;
            } else {
                return $this->JSONout(array("result" => "失败", "reason" => $sql->error));
            }
        } else {
            $action = $sql->prepare("INSERT INTO orders(`userId`,`date`,`price`,`message`,`autoPosition`,`type`,`keyWord`) VALUES (?,?,?,?,?,?,?)");
            $action->bind_param("sssssss", $_SESSION["UID"], $date, $price, $_3, $_4, $type, $_5);
            $action->execute();
            $action->free_result();
            $Id = $action->insert_id;
            if (!$action->error) {
                $No = \pay::getPay($price, $_SESSION["UID"]);
                $sql->query("update `orders` set `sign` = '$No' where Id = '$Id'");
                echo $sql->error;
            } else {
                return $this->JSONout(array("result" => "失败", "reason" => $sql->error));
            }
        }
    }

    //修改订单
    public function upOrder($Id, $date, $price, $size, $remark)
    {
        global $sql;
        $action = $sql->prepare("UPDATE orders SET `date`= ? ,`price`= ? ,`size`= ? ,`remark`= ?  WHERE Id= ? ");
        $action->bind_param("sssssss", $date, $price, $size, $remark, $Id);
        $action->execute();
        if (!$action->error) {
            return $this->JSONout(array("result" => "成功"));
        } else {
            return $this->JSONout(array("result" => "失败", "reason" => $sql->error));
        }
    }

    //获取所有订单----根据page分页
//    public function getOrder($page)
//    {
//        global $sql;
//        $start = ($page - 1) * 12;
//        $order = [];
//        $orderInfo = $sql->query("select `Id`,`size`,`price`,`userId`,`remark`,`expressSMS`,`addFee` from `orders` where `toker` is NULL and `payId` != 0 and `finish` = 0 ORDER BY `Id` DESC limit $start,12")->fetch_all(MYSQLI_ASSOC);
//        foreach ($orderInfo as $key => $val) {
//            $userInfo = $sql->query("select `position`,`sexual` from `user` where Id = '$val[userId]'")->fetch_array(1);
//            if ($val["addFee"] == 1) {
//                $prices = $sql->query("select `price` from `addFee` where orderId = '$val[Id]'")->fetch_all(1);
//                foreach ($prices as $PriceKey => $PriceVal) {
//                    $val["price"] += $PriceVal["price"];
//                }
//            }
//            $sms = $val["expressSMS"];
////            preg_match("/菜鸟驿站|小树林|奥克米|软件园/", $sms, $sms);
////            if (!isset($sms[0])) {
////                $sms[0] = $val["expressSMS"];
////                $len = strlen($sms[0]);
////                if ($len >= 5) {
////                    $len = 5;
////                }
////                $sms[0] = mb_substr($sms[0], 0, $len);
////            }
//            array_push($order, ["Id" => $val["Id"], "size" => $val["size"], "price" => (String)$val["price"], "position" => $userInfo["position"], "sexual" => $userInfo["sexual"], "remark" => $val["remark"]]);
//        }
//        $usedorder = [];
//        $orderInfo = $sql->query("select `Id`,`size`,`price`,`userId`,`remark`,`expressSMS`,`addFee` from `orders` where `finish` = 1 order by Id desc limit 6")->fetch_all(1);
//        foreach ($orderInfo as $key => $val) {
//            $userInfo = $sql->query("select `position`,`sexual` from `user` where Id = '$val[userId]'")->fetch_array(1);
//            if ($val["addFee"] == 1) {
//                $prices = $sql->query("select `price` from `addFee` where orderId = '$val[Id]'")->fetch_all(1);
//                foreach ($prices as $PriceKey => $PriceVal) {
//                    $val["price"] += $PriceVal["price"];
//                }
//            }
//            $sms = $val["expressSMS"];
////            preg_match("/菜鸟驿站|小树林|奥克米|软件园/", $sms, $sms);
////            if (!isset($sms[0])) {
////                $sms[0] = $val["expressSMS"];
////                $len = strlen($sms[0]);
////                if ($len >= 5) {
////                    $len = 5;
////                }
////                $sms[0] = mb_substr($sms[0], 0, $len);
////            }
//            array_push($usedorder, ["Id" => $val["Id"], "size" => $val["size"], "price" => (String)$val["price"], "position" => $userInfo["position"], "sexual" => $userInfo["sexual"], "remark" => $val["remark"]]);
//        }
//        return $this->JSONout([$order, $usedorder]);
//    }

    /**
     * @param int $page 页码
     * @return string JSON序列化的订单,12条
     */
    public function getSingleListOrder($page = 1)
    {
        global $sql;
        $start = ($page - 1) * 12;
        $orderInfo = $sql->query("select `Id`,`size`,`price`,`userId`,`remark`,`addFee`,`type`,`finish`,`date`,`toker`,`keyWord`,`autoPosition`,`message`,`expressSMS` from `orders` where payId != 0 and finish != 2 order by Id desc,finish limit $start,12")->fetch_all(1);
        $out = [];
        foreach ($orderInfo as $key => $val) {
            $userInfo = $sql->query("select `sexual`,`position`,`head`,`name` from `user` where Id = '$val[userId]'")->fetch_row();
            if ($val["addFee"] == 1) {
                $prices = $sql->query("select `price` from `addFee` where orderId = '$val[Id]'")->fetch_all(1);
                foreach ($prices as $PriceKey => $PriceVal) {
                    $val["price"] += $PriceVal["price"];
                }
            }
            if (isset($val["toker"])) {
                $val["toker"] = true;
            }
            $val["position"] = $userInfo[1];
            $val["sexual"] = $userInfo[0];
            $val['head'] = $userInfo[2];
            $val['nickname'] = $userInfo[3];
            preg_match(position, $val["expressSMS"], $position);
            $val["address"] = isset($position[0]) ? $position[0] : "代取悬赏";
            preg_match(expressLTD, $val["expressSMS"], $val["expressSMS"]);
            if (empty($val['expressSMS'])) {
                $val["expressSMS"] = "快递悬赏";
            } else {
                $val["expressSMS"] = $val["expressSMS"][0];
            }
            array_push($out, $val);
        }
        return $this->JSONout($out);
    }

    public function recoverOrder()
    {
        $out = 0;
        global $sql;
        $sql->query("DELETE FROM orders WHERE `payId` = 0");
        $out += $sql->affected_rows;
        $addfees = $sql->query("SELECT Id,`orderId` FROM addFee WHERE payId IS NULL ")->fetch_all(1);
        foreach ($addfees as $key => $val) {
            $sql->query("update `orders` set `consulting` = NULL where Id = '$val[orderId]'");
            $sql->query("delete from addFee where Id = '$val[Id]'");
            $out++;
        }
        return $out;
    }

    //获取订单对应的手机号
    public function getPhone($Id)
    {
        global $sql;
        $action = $sql->prepare("SELECT `toker` FROM `orders` WHERE Id = ?");
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
        $result = $sql->query("select `Id`,`expressSMS`,`price`,`size`,`remark`,`type`,`addFee`,`message`,`autoPosition`,`keyWord` from `orders` WHERE Id='$Id'");
        $order = $result->fetch_array(1);
        if ($order['addFee'] == 1) {
            $prices = $sql->query("select `price` from `addFee` where orderId = '$order[Id]'")->fetch_all(1);
            foreach ($prices as $PriceKey => $PriceVal) {
                $order["price"] += $PriceVal["price"];
            }
        }
        $sms = $order["expressSMS"];
        preg_match(expressLTD, $sms, $sms);
        if (!isset($sms[0])) {
            $sms[0] = $order["expressSMS"];
            $len = strlen($sms[0]);
            if ($len >= 5) {
                $len = 5;
            }
            $sms[0] = mb_substr($sms[0], 0, $len);
        }
        $order["expressSMS"] = $sms[0];
        return $this->JSONout($order);
    }

//    接单
    public function take($Id)
    {
        global $sql;

//        拉取订单信息
        $userInfo = $sql->query("select `userId`,`toker` from `orders` where Id = '$Id'")->fetch_row();
        if ($userInfo[1]) {
            return $this->JSONout(array("result" => "失败", "reason" => "订单已经被别人接啦"));
        }
        if ($userInfo[0] == $_SESSION["UID"]) {
//            排出自己接自己的单
            return $this->JSONout(array("result" => "失败", "reason" => "不能接自己的单"));
        }
        $type = $sql->query("select `type`,`position`,`phone` from `user` where Id = '$_SESSION[UID]'")->fetch_row();
        if (!isset($type[1]) || !isset($type[2])) {
            return $this->JSONout(array("result" => "失败", "reason" => "请完善个人信息"));
        }
//        判断接单权限
        $date = date("Y.m.d");
        $count = $sql->query("select count(Id) from `orders` where `toker` = '$_SESSION[UID]' and `date` = '$date'")->fetch_row()[0];
        if ($count >= userLevel[$type[0]]) {
            return $this->JSONout(array("result" => "失败", "reason" => "超过每日接单上限(今日红包雨每人仅限一单~)"));
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
        $mineSend = [];
        $mineToke = [];
        $resultMineSend = $sql->query("select * from orders where userId = '$_SESSION[UID]' and `payId` != 0 ORDER BY `finish`,Id desc,consulting")->fetch_all(1);
        foreach ($resultMineSend as $key => $val) {
            $userInfo = $sql->query("select `name`,`phone`,`position` from `user` where Id = '$val[toker]'")->fetch_array(1);
            $userInfo["price"] = $val["price"];
            if ($val["addFee"] = 1) {
                $prices = $sql->query("select `price` from `addFee` where orderId = '$val[Id]'")->fetch_all(1);
                foreach ($prices as $PriceKey => $PriceVal) {
                    $userInfo["price"] += $PriceVal["price"];
                }
            }
            $userInfo["Id"] = $val["Id"];
            $userInfo["consulting"] = $val["consulting"];
            $userInfo["expressSMS"] = $val["expressSMS"];
            $userInfo["size"] = $val["size"];
            $userInfo["remark"] = $val["remark"];
            $userInfo["finish"] = $val["finish"];
            $userInfo["date"] = $val["date"];
            $userInfo["toker"] = isset($val["toker"]);
            $userInfo["type"] = $val["type"];
            $userInfo["message"] = $val["message"];
            $userInfo["autoPosition"] = $val["autoPosition"];
            $userInfo["keyWord"] = $val["keyWord"];
            array_push($mineSend, $userInfo);
        }
        $resultMineToke = $sql->query("select * from orders where toker = '$_SESSION[UID]' ORDER BY `finish`,Id desc,consulting")->fetch_all(1);
        foreach ($resultMineToke as $key => $val) {
            $userInfo = $sql->query("select `name`,`phone`,`position` from `user` where Id = '$val[userId]'")->fetch_array(1);
            $userInfo["price"] = $val["price"];
            if ($val["addFee"] = 1) {
                $prices = $sql->query("select `price` from `addFee` where orderId = '$val[Id]'")->fetch_all(1);
                foreach ($prices as $PriceKey => $PriceVal) {
                    $userInfo["price"] += $PriceVal["price"];
                }
            }
            $userInfo["Id"] = $val["Id"];
            $userInfo["consulting"] = $val["consulting"];
            $userInfo["expressSMS"] = $val["expressSMS"];
            $userInfo["size"] = $val["size"];
            $userInfo["remark"] = $val["remark"];
            $userInfo["finish"] = $val["finish"];
            $userInfo["date"] = $val["date"];
            $userInfo["type"] = $val["type"];
            $userInfo["message"] = $val["message"];
            $userInfo["autoPosition"] = $val["autoPosition"];
            $userInfo["keyWord"] = $val["keyWord"];
            array_push($mineToke, $userInfo);
        }
        return $this->JSONout(["mineSend" => $mineSend, "mineToke" => $mineToke], 256);
    }

//   确认收货
    public function finish($Id)
    {
        require_once "lib/WxPay.Api.php";
        global $sql;
        $hasPay = $sql->query("select `hasPaid` from `orders` where Id = '$Id'")->fetch_row()[0];
        if ($hasPay) {
            return $this->JSONout(["result" => "失败,已经完成的订单不能再完成"]);
        }
        $userInfo = $sql->query("select `openId` from `user` where Id = (SELECT `userId` from `orders` where Id = '$Id')")->fetch_row()[0];
        if ($userInfo != $_COOKIE["openid"]) {
            exit("非法操作");
        }
        $orderInfo = $sql->query("select `payId`,`price`,`toker`,`addFee` from `orders` where Id = '$Id'")->fetch_row();
        $openId = $sql->query("select `openId` from `user` where Id = '$orderInfo[2]'")->fetch_row()[0];
        $input = new \WxPayToUser();
        $input->Setopenid($openId);
        $fee = $orderInfo[1] * tax * 100;
        if ($fee <= 100) {
            $fee = 100;
        }
        if ($orderInfo[3] == 1) {
            $fees = $sql->query("select price from `addFee` where orderId = '$Id' and `payId` is not NULL ")->fetch_all(1);
            foreach ($fees as $key => $val) {
                $fee += $val["price"] * 100 * tax;
            }
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

    /**
     * 催单
     * @param int $Id 订单Id
     * @return string JSON序列化的处理结果
     */
    public function reminder($Id)
    {
        global $sql;
//        判断订单状态
        $finish = $sql->query("select finish from `orders` where Id = '$Id'")->fetch_row()[0];
        if ($finish != 0) {
            return $this->JSONout(["result" => "失败", "reason" => "不能催已完成或者已取消的订单"]);
        }

//        发送模板消息
        $orderInfo = $sql->query("SELECT userId,price from `orders` where Id = '$Id'")->fetch_row();
        $openId = $sql->query("select `openId` from `user` where Id = '$orderInfo[0]'")->fetch_row()[0];
        $content = '{
           "touser":"' . $openId . '",
           "template_id":"Ez_1yfaoSRnbvTHRSm8UWKltAq0u4BwFJdPq3NdRWmM",
           "url":"http://dq.97qingnian.com/index.html#/state",            
           "data":{
                   "first": {
                       "value":"您收到一个确认收货催单提醒",
                       "color":"#333"
                   },
                   "keyword1":{
                       "value":"' . $Id . '",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"已接单",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"' . $orderInfo[1] . '",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"请您尽快确认收货\\n如果有任何疑问,直接回复我们会及时解答",
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
    }


    /**
     * 协商
     * @param int $Id 订单Id
     * @param int $action 操作: 0. 要求加价 , 1. 同意加价 , 2. 要求拒接 , 3. 同意拒接 , 4. 嗖嗖顺带介入, 5. 取消加价
     * @return string JSON序列化的处理结果
     */
    public function consult($Id, $action)
    {
        global $sql;
//        判断正确参数
        if ($action < 0 || $action > 5) {
            return $this->JSONout(["result" => "失败", "reason" => "错误的指令"]);
        }

//        判断订单状态
        $actions = $sql->prepare("SELECT `consulting` FROM `orders` WHERE Id = ?");
        $actions->bind_param("s", $Id);
        $actions->bind_result($consulting);
        $actions->execute();
        $actions->fetch();
        $actions->free_result();
//        进行不同操作
        switch ($action) {
            case "0" :
                $orderInfo = $sql->query("select `finish`,`toker`,`userId` from `orders` where Id = '$Id'")->fetch_row();
                if (empty($_SESSION["UID"]) || $orderInfo[1] != $_SESSION["UID"]) {
                    return $this->JSONout(["result" => "失败", "reason" => "授权失败"]);
                }
                if ($orderInfo[0] == 1) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单已完成"]);
                }
                if (empty($orderInfo[1])) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单还未接单"]);
                }
                if (!empty($consulting)) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单已经在协商,不能再重新进行协商"]);
                }
                $sql->query("update `orders` set `consulting` = 0 where Id = '$Id'");
                $this->consultingMessage($orderInfo[2], 0, $Id);
                return $this->JSONout(["result" => "成功"]);
                break;
            case "1";
                if ($consulting == 0) {
                    $sql->query("update `orders` set `consulting` = 1 where Id = '$Id'");
                } elseif (!isset($_POST["fee"])) {
                    return $this->JSONout(["result" => "失败", "reason" => "缺少post参数fee"]);
                }

                $sql->query("insert into addFee (userId,orderId) VALUES ('$_COOKIE[openid]','$Id')");
                require_once "pay.php";
                \pay::addFee($_POST["fee"]);
                break;
            case "2":
                $orderInfo = $sql->query("select `finish`,`toker`,`userId` from `orders` where Id = '$Id'")->fetch_row();
                if (empty($_SESSION["UID"]) || $orderInfo[1] != $_SESSION["UID"]) {
                    return $this->JSONout(["result" => "失败", "reason" => "授权失败"]);
                }
                if ($orderInfo[0] == 1) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单已完成"]);
                }
                if (empty($orderInfo[1])) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单还未接单"]);
                }
                if (!empty($consulting)) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单已经在协商,不能再重新进行协商"]);
                }
                $sql->query("update `orders` set `consulting` = 2 where Id = '$Id'");
                $this->consultingMessage($orderInfo[2], 2, $Id);
                return $this->JSONout(["result" => "成功"]);
                break;
            case "3":
                $orderInfo = $sql->query("select `toker` from `orders` where Id = '$Id'")->fetch_row();
                $sql->query("update `orders` set `toker` = null,`consulting`=null where Id = '$Id'");
                $this->consultingMessage($orderInfo[0], 3, $Id);
                return $this->JSONout(["result" => "成功"]);
                break;
            case "4":
                $orderInfo = $sql->query("select `finish`,`toker`,`userId` from `orders` where Id = '$Id'")->fetch_row();
                if (empty($_SESSION["UID"]) || $orderInfo[2] != $_SESSION["UID"]) {
                    return $this->JSONout(["result" => "失败", "reason" => "授权失败"]);
                }
                if ($orderInfo[0] == 1) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单已完成"]);
                }
                if (empty($orderInfo[1])) {
                    return $this->JSONout(["result" => "失败", "reason" => "订单还未接单"]);
                }
                $sql->query("update `orders` set `consulting` = 4 where Id = '$Id'");
                $this->consultingMessage($orderInfo[1], 4, $Id);
                $this->consultingMessage($orderInfo[2], 4, $Id);
                foreach ([4, 6, 7, 16, 20, 101, 105] as $key => $val) {
                    $this->consultingMessage($val, 5, $Id);
                }
                return $this->JSONout(["result" => "成功"]);
                break;
            case "5":
                if ($consulting != 1) {
                    return $this->JSONout(["result" => "失败", "reason" => "并没有在加价等待支付状态"]);
                }
                $sql->query("update `orders` set `consulting` = 0 where Id = '$Id'");
                $sql->query("delete from addFee where `orderId` = '$Id' and `payid` = 0");
                return $this->JSONout(["result" => "成功"]);

        }
    }

    /**
     * @param $userId int 接收人Id
     * @param $type int 操作类型
     * @param $Id int 订单编号
     * @return mixed 发送结果
     * 操作类型 0 , 2 , 4
     */
    public function consultingMessage($userId, $type, $Id)
    {
        global $sql;
        $openId = $sql->query("select `openId` from `user` where Id = '$userId'")->fetch_row()[0];
        switch ($type) {
            case 0:
                $message = "您的接单同学申请加价";
                break;
            case 2:
                $message = "您的接单同学申请拒接";
                break;
            case 3:
                $message = "同意拒接";
                break;
            case 4:
                $message = "现在已经进如平台介入,不要担心,嗖嗖顺代保证会给您一个满意的结果~请耐心等待";
                break;
            case 5:
                $message = date("H:i:s") . ",编号 : $Id 进入介入阶段,管理员尽快处理";
        }
        $content = '{
           "touser":"' . $openId . '",
           "template_id":"4N6uQcQXEGpzaHIQCEigbvoTdRx8Ksh8KY07gTVl4rI",
           "url":"http://dq.97qingnian.com/index.html#/state",            
           "data":{
                   "first": {
                       "value":"' . $message . '！",
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
                       "value":"如需帮助,可以直接回复",
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
    }

    /**
     * 微信接单提醒
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
     * @param object $data 订单obj
     * @param array $Ids 接受名单(openid)
     * @return void
     */

    public static function watcher($data, $Ids)
    {
        $time = $data["time_end"];
        $hour = substr($time, 8, 2);
        $minute = substr($time, 10, 2);
        $second = substr($time, 12, 2);
        foreach ($Ids as $key => $val) {
            $content = '{
                            "touser":"' . $val . '",
                            "template_id":"YNdcfpSLmlQ1N3R2AgPIDcmUjGC7LOi27g_lMR6ULUM",
                            "url":"http://dq.97qingnian.com/index.html#/index",            
                            "data":{
                                    "first": {
                                    "value":"有人发布了新的订单！",
                                    "color":"#333"},
                            "OrderSn":{
                                    "value":"' . $data["out_trade_no"] . '",
                                    "color":"#173177"},
                            "OrderStatus": {
                                    "value":"有人发布了新的订单",
                                    "color":"#173177"},
                            "remark":{
                                    "value":"订单价格: ' . ($data["total_fee"] / 100) . ' 元,\\n发布时间: ' . $hour . '时' . $minute . '分' . $second . '秒",
                                    "color":"#333"}}
                        }';
            require_once "wxControl.php";
            $ACT = \wxControl::getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ACT;
            $opts = array('http' =>
                array(

                    'method' => 'POST',

                    'header' => 'Content - type: application / x - www - form - urlencoded',

                    'content' => $content

                )
            );
            $context = stream_context_create($opts);
            echo file_get_contents($url, false, $context);
        }
    }

    //私有方法,接受数组变量,将它转化为JSON字符串返回
    private function JSONout($str)
    {
        return json_encode($str, JSON_UNESCAPED_UNICODE);
    }
}



