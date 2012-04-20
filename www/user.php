<?php
//TODO::Изменить на полностью рандомную строку
function getRandomString() {
			return sha1(microtime(true).mt_rand(10000,90000));
}

//В случае ошибки $error != NULL
function response($error, $result = NULL) {		
		echo json_encode(array('result' => $result, 'error' => $error));
}

class ResponseStatus {
	const internalServerError	= 1;
	const sessionExpired		= 2;
	const corruptedChecksum		= 3;
	const corruptedFormat		= 4;
	const duplicatedMessage		= 5;	
	const invalidCredentials	= 6;
	const emptyRequest			= 7;
}

class UserStatus {
	const inactive = 0;
	const active = 1;
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
		$user = ORM::for_table('users')->create();
		$user->pass = sha1($pass);
		$user->login = $login;
		$user->status = $status;
		$user->save();
	}

	public static function get($login, $pass, $status = 1) {
		return ORM::for_table('users')->where('login', $login)->where('pass', sha1($pass))->where('status', $status)->find_one();
	}
}

//PHP doesn't support nested classed, alas
class HttpSession {
	public static $sessionTtl = 900;

	public static function get() {
		global $app;

		$sessionId = $app->getCookie('session_id');

		$user = ORM::for_table('users')->where('session_id', $sessionId)->find_one();

		if($user == FALSE || $user->status == UserStatus::inactive || time() - strtotime($user->last_auth) >= self::$sessionTtl) {
			return FALSE;
		}

		return $user;
	}	

	public static function set($user) {
		global $app;

		$user->last_auth = date('Y-m-d h:i:s');
		$user->session_id = getRandomString();
		$user->save();
		$app->setcookie('session_id', $user->session_id, 0);
	}
}

class Report {
	static function get($checksum) {
		return ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one();
	}

	static function getReportByDevice($login) {

		$user = ORM::for_table('users')->where('login', $login)->find_one();
		if($user == null) return FALSE;


		$sessions = ORM::for_table('reading_sessions')->where('user_id', $user->id)->find_many();
		
		echo '<style>body { font-family:Calibri;}</style>';
		echo '<table><tr valign=top>';
		foreach($sessions as $session) {
			echo '<td><table border=1>';
			echo '<tr><td><h2>'.$session->time_marker.'</h2></td>';
			$tags = ORM::for_table('tubes')->where('session_id', $session->session_id)->find_many();
			foreach($tags as $tag) {
				echo '<tr><td>'.$tag->tag.'</td></tr>';			
			}
			echo '</table></td>';
		}
		echo '</tr></table>';
	}

	static function create($user, $json, $checksum) {
		$session = ORM::for_table('reading_sessions')->create();
		$session->user_id = $user->id;
		$session->checksum = $checksum;
		$session->time_marker = $json['time'];
		$session->location_id = $json['location'];
		$session->status = $json['readingStatus'];
		$session->mode = $json['readingMode'];
		$session->save();

		$readingSessionId = ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one()->session_id;

		foreach($json['tags'] as $tag) {
			$record = ORM::for_table('tubes')->create();
			$record->tag = $tag;
			$record->session_id = $readingSessionId;
			$record->save();

			// $record = ORM::for_table('tags_list')->create();
			// $record->tag = $tag;
			// $record->last_mode = $session->mode;
			// $record->save();

			ORM::get_db()->exec("INSERT INTO `tags_list` (tag, last_mode) VALUES ('{$tag}', {$session->mode}) ON DUPLICATE KEY UPDATE last_mode = {$session->mode};");
		}
	}
}
?>