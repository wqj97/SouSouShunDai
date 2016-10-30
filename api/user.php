<?
/**
 * User interfaces. 
 * @author Fang
 * @see model/user.php
 * @version 1.0.0, 2016.10.30
 */

session_start();
require_once '../model/base.php';
global $sql;
$type = $sql->query("select `type` from `user` where Id = $_SESSION[UID]");
$type = $type->fetch_row();
if ($type[0] != 1) die('<h1>非法操作!</h1>');
require_once '../model/user.php';

$u = new UserUtils();

switch ($_POST['action']){
	case 'newUser':
		$r = $u->newUser($_POST['name'],$_POST['sex']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'updateNameSex':
		$r = $u->updateNameSex($_POST['id'],$_POST['name'],$_POST['sex']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'updateLocation':
		$r = $u->updateLocation($_POST['id'],$_POST['location']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'updateTel':
		$r = $u->updateTel($_POST['id'],$_POST['tel']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'getUserInfo':
		$r = $u->getUserInfo($_POST['id']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'getNameTelSex':
		$r = $u->getNameTelSex($_POST['id']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'getPublisherByOrder':
		$r = $u->getPublisherByOrder($_POST['orderId']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
	case 'getCourierByOrder':
		$r = $u->getCourierByOrder($_POST['orderId']);
		echo json_encode($r,JSON_UNESCAPED_UNICODE);
		exit;
}