<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/12/6
 * Time: 下午6:55
 */
//error_reporting(2048);
require_once "../model/base.php";
global $sql;
$action = $sql->prepare("SELECT `head`,`discountActived`,`discount` FROM user WHERE `phone` = ?");
$action->bind_param("s", $_GET["phone"]);
$action->bind_result($userHead, $discountActived,$discount);
$action->execute();
$action->fetch();
$action->free_result();
if(empty($userHead)){
    $userHead = "/logo.png";
}
if ($discountActived != 1) {
    $action = $sql->prepare("UPDATE `user` SET `discount` = 1 WHERE `phone` = ?");
    $action->bind_param("s", $_GET["phone"]);
    $action->execute();
    $action->free_result();
}
?>
<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        @font-face {
            src:url('Go_Boom!.ttf');
            font-family: "GoBoom";
        }
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "PingFangSC-Semibold", Tahoma, Arial, Roboto, ”Droid Sans”, ”Helvetica Neue”, ”Droid Sans Fallback”, ”Heiti SC”, sans-self;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction:column;
            background: #54c8ff;
            font-size: 62.5%;
        }
        .logo img{
            border-radius: 15px;
            display: block;
        }
        .main {
            position: relative;
            transition: all .5s ease;
            top: 0;
            background: #fff;
            /*box-shadow: 0 0 50px rgba(0, 0, 0, 0.1);*/
            width: 90%;
            border-radius: 20px;
        }

        .main-head {
            background: #ff5e6a;
            border-radius: 20px 20px 0 0;
            text-align: center;
            height: 5rem;
            line-height: 5rem;
            font-size: 1.4rem;
            color: white;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .main-user-head {
            height: auto;
            margin-bottom: -5px;

        }

        .main-user-head img {
            width: 100%;
        }
        .main-user-head input{
            font-family:"GoBoom";
            width:100%;
            height:75px;
            text-align: center;
            border:none;
            outline: none;
            padding:0;
            font-size:35px;
            letter-spacing: 10px;
        }
        .main-user-head input::-webkit-input-placeholder{
            color: #dbdbdb !important;
            letter-spacing: normal;
            font-size:24px;
        }
        .main-accept {
            height: 5rem;
            line-height: 5rem;
            font-size: 1.4rem;
            text-align: center;
            background: #f8f8f8;
            border-radius: 0 0 20px 20px;
            color: #ff5e6a;
        }

        .go {
            top: -100% !important;
            transition-delay: .5s;
        }

        .ready {
            top: 10% !important;
        }
    </style>
    <title>嗖嗖顺带邀您免单</title>
</head>
<body>
<?php
if (isset($_GET["phone"])) {
    echo '<div class="main"><div class="main-head">';
    if ($discount) {
        echo "您已领取过 <span style=\"color: #ffe90d;font-weight: bold;font-size: 2rem;\">3</span> 元免单";
    } elseif ($userHead == "/logo.png"){
        echo "这个手机号还没有注册";
    }else {
        echo "成功领取 <span style=\"color: #ffe90d;font-weight: bold;font-size: 2rem;\">3</span> 元免单";
    }
    echo '</div><div class="main-user-head"><img src="' . $userHead . '"></div><div class="main-accept">点击前往微信</div></div>';
}else{
    echo '<div class="main">
    <div class="main-head">输入手机号领取 <span style="color: #ffe90d;font-weight: bold;font-size: 2rem;">3</span> 元免单</div>
    <div class="main-user-head"><input type="text" placeholder="在这里输入手机号" id="phone"></div>
    <div class="main-accept">点击领取</div>
</div>';
}
?>
<div class="goByself" style="display: none">
    <div class="main-head">如果您看到这个劳驾您手动打开微信~</div>
    <div class="main-accept">嗖嗖顺带等你哦</div>
</div>
<script>
    window.onload = function () {
        document.querySelector(".main-accept").addEventListener("touchend", function () {
            go();
        })
    };
    function go() {
        var main = document.querySelector(".main");
        main.className = "main ready";
        main.addEventListener("transitionend", function () {
            main.className = "main go"
        });
        setTimeout(function () {
            if(document.querySelector("#phone")){
                location.href = "discount.php?phone=" + document.querySelector("#phone").value;
            }else{
                location.href = "weixin://index";
            }
            setTimeout(function () {
                document.body.removeChild(document.querySelector(".main"));
                document.querySelector(".goByself").style.display = "block"
            },1000)
        }, 1500)
    }
</script>
</body>
</html>