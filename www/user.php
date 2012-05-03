<?php
//Проверка на разрешенный IP
function checkAllowedIP() {
	$whiteList[0]="127.0.0.1";
	if (in_array($_SERVER['REMOTE_ADDR'],$whiteList)) {
		return true;
	return false;
	}
}

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
	const bannedIP				= 8;
}

class UserStatus {
	const inactive = 0;
	const active = 1;
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

		if($user == false || $user->status == UserStatus::inactive || time() - strtotime($user->last_auth) >= self::$sessionTtl) {
			return false;
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

	static function getReportByLocation($location) {

		$total = ORM::for_table('tags_list')->raw_query(@"
					SELECT count(*) as total
					FROM tags_list tl, reading_sessions r
					WHERE tl.last_session_id = r.session_id and r.location_id = {$location->id}", array())->find_one();

		//Берём все сессии, связанные с данным расположением
		//Подсчитываем число меток, связанных с этими сессиями
		//TODO::делать подсчёт сразу же после создания записи о сессии
		$results = ORM::for_table('tubes')->raw_query(@"
			SELECT r.*, count(*) as total
			FROM tubes t
			JOIN reading_sessions r
			ON t.session_id = r.session_id and r.location_id = {$location->id}
			GROUP BY t.session_id
			ORDER BY r.time_marker
			", array())->find_many();

		$devices = array();
		foreach(ORM::for_table('users')->find_many() as $device)
		{
			$devices[$device->id] = $device->description;
		}


//Убрать в модель

		echo <<<END
<style>
	tr.head { background: orange; }
	tr.report:nth-child(even) {background: #CCC}
	tr.report:nth-child(odd) {background: #FFF}
	td.score {background: orange;}
	tr { font-family: Calibri; }
</style>
END;

		echo @"<table>
		<thead>
				<tr>
					<td>Расположение:</td>
					<td>{$location->description}</td>
					<td>Всего числится меток:</td>
					<td>{$total->total}</td>
				</tr>
			</thead>
		<table>";

		echo @"<table>
		<thead>
				<tr class='head'>
					<td>Меток за сеанс</td>
					<td>Время считывания</td>
					<td>Считыватель</td>
				</tr>
			</thead>";


		foreach($results as $tag) {
			echo @"
				<tr class='report'>
					<td>{$tag->total}</td>
					<td>{$tag->time_marker}</td>
					<td>{$devices[$tag->device_id]}</td>
				</tr>";
		}

		echo '</table>';
	}

	static function create($user, $json, $checksum) {
		$session = ORM::for_table('reading_sessions')->create();
		$session->device_id = $user->id;
		$session->checksum = $checksum;
		$session->time_marker = $json['time'];
		$session->location_id = $json['location'];
		$session->reading_status = $json['readingStatus'];
		$session->session_mode = $json['sessionMode'];
		$session->save();

		$readingSessionId = ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one()->session_id;

		foreach($json['tags'] as $tag) {
			$record = ORM::for_table('tubes')->create();
			$record->tag = $tag;
			$record->session_id = $readingSessionId;
			$record->save();

			$tagInfo = ORM::for_table('tags_list')->where('tag', $tag)->find_one();

			if($tagInfo == false) {
				$tagInfo = ORM::for_table('tags_list')->create();
				$tagInfo->tag = $tag;
			}

			//Если запрос существует, надо учесть тот факт,
			//что отчеты могут придти в разном порядке
			//и более ранний по времени считывания окажется, последним из присланных

			else {
				$prevSessionTimeMarker = ORM::for_table('reading_sessions')->where('session_id', $tagInfo->last_session_id)->find_one()->time_marker;
				if(strtotime($prevSessionTimeMarker) > strtotime($session->time_marker)) {
					continue;
				}
			}

			$tagInfo->last_session_id = $record->session_id;
			$tagInfo->save();
		}
	}
}
?>