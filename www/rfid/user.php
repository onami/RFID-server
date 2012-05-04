<?php
class UserStatus {
	const inactive = 0;
	const device = 1;
	const user = 2;
}

class User {
	public static function doesUserExist($login) {
		if(ORM::for_table('users')->where('login', $login)->find_one() == true) {
			return true;
		}
		return false;
	}

	public static function create($login, $pass, $status = 1) {
		//TODO::добавить соль
		$user = ORM::for_table('users')->create();
		$user->pass = sha1($pass);
		$user->login = $login;
		$user->status = $status;
		$user->save();
	}

	public static function get($login, $pass) {
		return ORM::for_table('users')
		->where('login', $login)
		->where('pass', sha1($pass))
		->find_one();
	}

	public static function redirect($user) {
		global $basedir;

		if($user->status == UserStatus::device) {
			return response(null);
		} else if($user->status == UserStatus::user) {
			header ("Location: {$basedir}/report/location/292f00aa9449566d1765691213406cc17c599589/", true, 303);
			exit();
		}
	}
}
?>