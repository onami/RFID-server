<?php
//TODO::Изменить на полностью рандомную строку
function getRandomString() {
			return sha1(microtime(true).mt_rand(10000,90000));
	}

function responseStatus($status, $message = '') {
		global $app;

		$app->response()->status($status);
		echo $message;
	}

class Status {
	const nonActive = 0;
	const sessionExpired = 'sessionExpired';
	const corruptedChecksum = 'corruptedChecksum';
	const corruptedFormat = 'corruptedFormat';
	const duplicatedMessage = 'duplicatedMessage';	
}

class User {
	public static function doesUserExist($login) {
		if(ORM::for_table('users')->where('login', $login)->find_one() == TRUE) {
			return TRUE;
		}
		return FALSE;
	}

	public static function create($login, $pass, $status = 1) {
		//TODO::добавить соль
		$pass = sha1($pass);
		ORM::get_db()->exec("INSERT INTO `users` (`login`, `pass`, `status`) VALUES ('$login', '$pass', 1)");
	}

	public static function get($login, $pass, $status = 1) {
		return ORM::for_table('users')->where('login', $login)->where('pass', sha1($pass))->where('status', $status)->find_one();
	}
}

//PHP doesn't support nested classed, alas
class HttpSession {
	public static $sessionTtl = 900;

	static function get() {
		global $app;

		$sessionId = $app->getCookie('session_id');
		$user = ORM::for_table('users')->where('session_id', $sessionId)->find_one();

		if($user == FALSE || $user->status == Status::nonActive || time() - strtotime($user->last_auth) >= self::$sessionTtl) {
			return FALSE;
		}

		return $user;
	}	

	static function set($user) {
		global $app;
		$sessionId = getRandomString();
		$app->setcookie('session_id', $sessionId);
		ORM::get_db()->exec("UPDATE `users` SET last_auth = '".date('Y-m-d h:i:s')."', session_id = '$sessionId' WHERE id = {$user->id}");
	}
}

class Report {
	static function get($checksum) {
		return ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one();
	}

	static function create($user, $json, $checksum) {
		ORM::get_db()->exec("INSERT INTO `reading_sessions` (`user_id`, `checksum`, `time_stamp`, `coords`) VALUES ({$user->id}, '$checksum', '{$json['time_stamp']}', GeomFromText('POINT({$json['coords']})'))");

		$readingSessionId = ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one();
		foreach($json['data'] as $tag) {
			ORM::get_db()->exec("INSERT INTO `tubes` (`tag`, `session_id`, `status`) VALUES ('$tag', {$readingSessionId->session_id}, '1')");
		}
	}
}
?>