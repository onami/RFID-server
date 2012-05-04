<?php
//PHP doesn't support nested classed, alas
class HttpSession {
	public static $sessionTtl = 900;

	public static function get() {
		global $app;

		$sessionId = $app->getCookie('session_id');

		$user = ORM::for_table('users')->where('session_id', $sessionId)->find_one();

		if($user == false || $user->status == UserStatus::inactive || time() - strtotime($user->last_auth) >= self::$sessionTtl) {
			return false;
		}

		return $user;
	}	

	public static function set($user) {
		$user->last_auth = date('Y-m-d h:i:s');
		$user->session_id = getRandomString();
		$user->save();
		setcookie('session_id', $user->session_id, time() + time()+60*60*24*30, '/');
	}
}
?>