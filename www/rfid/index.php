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

require_once 'device.php';
require_once 'report.php';
require_once 'report.view.php';
require_once 'response.php';
require_once 'mockClient.php';
//install.db should be
//created in advance
require_once 'install.php';

$app->post('/post/:deviceId/', 'post');
$app->get('/report/location/:location/', 'reportOnLocation');
$app->get('/report/device/:device/', 'reportOnDevice');
$app->get('/report/user/:user/', 'reportOnUser');
$app->get('/report/', 'ReportView::renderReportsList');

function post($deviceId) {
	try {
		global $app;

		$device = Device::get($deviceId);

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
		} catch(Exception $e) {
			ORM::get_db()->rollBack();
			throw new Exception($e->getMessage());
		}

		Response::Set(null);

	} catch(Exception $e) {
			return Response::Set(Response::internalServerError, $e->getMessage());
	}
}

/* Reports */

function reportOnUser($user) {
	try {
		
		$user = Report::getUserById($user);

		if($user == false) {
			return;
		}

		ReportView::renderByUser($user);

	} catch(Exception $e) {
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

		ReportView::renderByLocation($location);

	} catch(Exception $e) {
			Response::Set(Response::internalServerError, $e->getMessage());
			return;
	}
}

function reportOnDevice($device) {
	try {
		
		$device = Report::getDeviceById($device);

		if($device == false) {
			return;
		}

		ReportView::renderByDevice($device);

	} catch(Exception $e) {
			Response::Set(Response::internalServerError, $e->getMessage());
			return;
	}
}

$app->run();
?>