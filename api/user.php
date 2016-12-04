<?
/**
 * @author Fang
 * @see model/user.php
 * @version 1.0.0, 2016.10.30
 */

session_start();
require_once '../model/base.php';
global $sql;
require_once '../model/user.php';
if(empty($_SESSION["UID"])){
    exit (json_encode(["result"=>"失败","reason"=>"授权失败"],256));
}
$u = new UserUtils();

switch ($_GET['action']){

	case 'edit':
		$u->updateNameSexPos($_POST['Id'],$_POST['name'],$_POST['sexual'],$_POST['position']);
        echo json_encode(["result"=>"成功"],256);
        break;

	case 'getUser':
		$r = $u->getUserInfo($_POST['Id']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
        break;
    case 'getMine':
        $r = $u->getMine();
        echo json_encode($r,256);

}