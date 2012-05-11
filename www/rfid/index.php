<?php
error_reporting(E_ALL);

//micro framework
require_once 'Slim/Slim.php';
//micro orm
//Uses prepared statements throughout
//to protect against SQL injection attacks.
require_once 'idiorm.php';

$app = new Slim(array());
$dbname = "rfid";
$basedir = "/rfid";

ORM::configure('mysql:host=localhost;dbname='.$dbname);
ORM::configure('username', 'root');
ORM::configure('password', '');
ORM::get_db()->exec('set names utf8');

/*******************************************************/

require_once 'report.php';
require_once 'report.view.php';
require_once 'response.php';
require_once 'mockClient.php';
//install.db should be
//created in advance
require_once 'install.php';

$app->post('/post/:deviceKey/', 'post');
$app->get('/report/user/:user/', 'reportOnUser');
$app->get('/report/', 'reportsList');
$app->get('/report/location/:location/', 'reportOnLocation');

function post($deviceKey) {
	try {
		global $app;

		$device = Report::getDeviceByKey($deviceKey);

		if($device == false) {
			Response::Set(Response::invalidDeviceKey);
			return;
		}

		//TODO::добавить JSON Scheme
		$json = $app->request()->post('json');
		$checksum = $app->request()->post('checksum');

		if(strlen($json) == 0 || strlen($checksum) == 0) {
			return Response::Set(Response::emptyRequest);
		}

		if(sha1($json) != $checksum) {
			return Response::Set(Response::corruptedChecksum);
		}

		if(Report::getReportByChecksum($checksum) != false) {
			return Response::Set(Response::duplicatedMessage);
		}

		$json = json_decode($json, true);

		//Не работает на PHP 5.2
		if(json_last_error() != JSON_ERROR_NONE) {
			return Response::Set(Response::corruptedFormat);
		}

		try {
			ORM::get_db()->beginTransaction();
			Report::create($device, $json, $checksum);
			ORM::get_db()->commit();
		}

		catch(Exception $e) {
			ORM::get_db()->rollBack();
			throw new Exception($e->getMessage());
		}

		Response::Set(null);

	}

	catch(Exception $e) {
		return Response::Set(Response::internalServerError, $e->getMessage());
	}
}

/* Reports */

function reportsList() {
		$viewData = array();
		$viewData['roles'] = Report::getDictionary('roles');
		$viewData['users'] = Report::getDictionary('users');
		$viewData['locations'] = Report::getDictionary('locations');

		ReportView::RenderList($viewData);
}

function reportOnUser($user) {
	try {		
		$user = Report::getUserById($user);

		if($user == false) {
			return;
		}

		if($user->role_id == UserRole::warehouseKeeper || $user->role_id == UserRole::toolPusher) {
			$viewData = array();
			$relDevice = Report::getRecord('users_rel', 'user_id', $user->id)->device_id;
			$viewData['device'] = Report::getDeviceById($relDevice);

			$viewData['user'] = $user;
			$viewData['location'] = Report::getLocationById($user->location_id);
			$viewData['locations'] = Report::getDictionary('locations');

			//Считаем число уникальных меток, связанных с данным $device
			$viewData['total'] = ORM::for_table('tags_list')
				->raw_query(@"
						SELECT count(*) as total
						FROM tags_list tl, reading_sessions r
						WHERE 
							tl.last_session_id = r.id and
							r.device_id = {$viewData['device']->id}", array())
				->find_one()->total;
			//Берём все сессии, связанные с данным расположением
			$viewData['sessions'] = ORM::for_table('reading_sessions')
				->raw_query(@"
					SELECT r.*
					FROM reading_sessions r
					WHERE r.device_id = {$viewData['device']->id}
					ORDER BY r.location_id, r.device_id, r.time_marker
					", array())->find_many();
		}

		if($user->role_id == UserRole::warehouseKeeper) {
			ReportView::warehouseKeeperReport($viewData);
		}

		else if($user->role_id == UserRole::toolPusher) {
			ReportView::toolPusherReport($viewData);
		}

		else if($user->role_id == UserRole::oilrigManager) {
			$viewData = array();
			$viewData['user'] = $user;
			ReportView::oilrigManagerReport($viewData);

			foreach(ORM::for_table('users_rel')->where('user_id', $user->id)->find_many() as $manager) {
				foreach(ORM::for_table('users_rel')->where('user_id', $manager->rel_user_id)->find_many() as $pusher) {
				   reportOnUser($pusher->user_id);
				   echo "<hr/>";
				//	$viewData['users'][$pusher->device_id] = Report::getDeviceById($pusher->device_id);
				}
			}


		}
	}

	catch(Exception $e) {
		Response::Set(Response::internalServerError, $e->getMessage());
		return;
	}
}

function reportOnLocation($location) {
	try {
		$location = Report::getLocationById($location);

		if($location == false) {
			return;
		}

		$viewData = array();
		$viewData['location'] = $location;

		$viewData['total'] = ORM::for_table('tags_list')
			->raw_query(@"
			SELECT count(*) as total
			FROM tags_list tl, reading_sessions r
			WHERE
				tl.last_session_id = r.id and
				r.location_id = {$location->id}", array())
			->find_one();

		//Берём все сессии, связанные с данным расположением
		$viewData['sessions'] = ORM::for_table('reading_sessions')
			->raw_query(@"
			SELECT r.id as session_id, r.time_marker, r.count, u.description
			FROM reading_sessions r
			LEFT OUTER JOIN users_rel u_rel
				LEFT OUTER JOIN users u
				ON u.id = u_rel.user_id
			ON r.device_id = u_rel.device_id
			WHERE  r.location_id = {$location->id}
			ORDER BY r.time_marker, u.description
				", array())->find_many();

		foreach($viewData['sessions'] as $session) {
			$viewData['tags'][$session->session_id] = ORM::for_table('tubes')
				->raw_query(@"
					SELECT t.* FROM `tubes` t, `reading_sessions` r
					WHERE r.location_id = {$location->id} and t.session_id = {$session->session_id} ", array())
				->find_many();
		}

		ReportView::locationReport($viewData);
	}
	catch(Exception $e) {
		Response::Set(Response::internalServerError, $e->getMessage());
		return;
	}

}

$app->run();
?>