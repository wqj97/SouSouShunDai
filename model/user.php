<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2016/10/19
 * Time: 下午11:57
 */

namespace Fang;
require_once 'base.php';

class user
{
	/*public function __construct($arg){

	}*/
	
	public function newUser($name,$sex){
		global $sql;
		// Id,name,phone,pwd,openId,AccessToken,RefreshToken,ACTexpires,position,sexual,type
		$statement = $sql->prepare('INSERT INTO `user` VALUES(0,?,NULL,NULL,NULL,NULL,NULL,NULL,NULL,?,NULL)');
		$statement->bind_param($name,$sex);
		$statement->execute();

		return $sql->insert_id;	// new user's ID (generated by AUTO_INCREAMENT)
	}

	public function getUserByOrderID($orderID){
		global $sql;
		// ...
		$out = array('result' => 'success', 'reason' => '');
		return $out;
	}

	public function updateNameSex($id,$name,$sex){
		global $sql;
		$statement = $sql->prepare('UPDATE `user` SET `name` = ?,`sexual` = ? WHERE `id` = ?');
		$statement->bind_param($name,$sex,$id);
		$statement->execute();
	}

	public function updateLocation($id,$location){
		global $sql;
		$statement = $sql->prepare('UPDATE `user` SET `position` = ? WHERE `id` = ?');
		$statement->bind_param($location,$id);
		$statement->execute();
	}

	public function updateTel($id,$tel){
		global $sql;
		// Check phone number ...
		if (preg_match('/^1[3|4|5|8][0-9]\d{8}$/', $tel)){
			$statement = $sql->prepare('UPDATE `user` SET `phone` = ? WHERE `id` = ?');
			$statement->bind_param($tel,$id);
			$statement->execute();
			return true;
		} else {
			return false;
		}
	}


	private function JSONout($str){
		// 私有方法,接受数组变量,将它转化为JSON字符串返回
		return json_encode($str,JSON_UNESCAPED_UNICODE);
	}
}