<?php
error_reporting(E_ALL);

//micro frameworkw
require_once 'Slim/Slim.php';
//micro orm
require_once 'idiorm.php';
//some classes
require_once 'user.php';

$app = new Slim(array());
ORM::configure('mysql:host=localhost;dbname=rfid');
ORM::configure('username', 'root');
ORM::configure('password', '');

//test client 
require_once 'mockClient.php';

//install. db should be created in advance
//require_once 'install.php';

//TODO::добавить prepared_statements

$app->post('/rfid/signup/', function() use($app) {
	try	{
		//Устройство предъявляет ключ проекта
		$login = $app->request()->post('login');
		$pass  = $app->request()->post('pass');
		$user = User::get('project', $pass, 0);

		//Если ключ верен и пользователя с таким логином незарегистривано
		if($user == TRUE && User::doesUserExist($login) == FALSE) {
			$pass = getRandomString();
			User::create($login, $pass);
			return response(NULL, $pass);
		}
		else {
			return response(ResponseStatus::invalidCredentials);
		}

	} catch(Exception $e) {
			return response(ResponseStatus::internalServerError, $e->getMessage());
		}
});

$app->post('/rfid/auth/', function() use($app) {
	try {
		$login = $app->request()->post('login');
		$pass  = $app->request()->post('pass');
		$user = User::get($login, $pass, UserStatus::active);

		if($user == FALSE) {
			return response(ResponseStatus::invalidCredentials);
		}

		HttpSession::set($user);

		return response(NULL);

	} catch(Exception $e) {
			return response(ResponseStatus::internalServerError, $e->getMessage());
	}
});

$app->post('/rfid/post/', function() use($app) {
	try {
		if(($user = HttpSession::get()) == FALSE) {
			return response(ResponseStatus::sessionExpired);
		}

		//TODO::проверить на пустых данных
		//TODO::добавить JsonScheme
		$json = $app->request()->post('json');
		$checksum = $app->request()->post('checksum');

		if(strlen($json) == 0 || strlen($checksum) == 0) {
			return response(ResponseStatus::emptyRequest);
		}

		if(sha1($json) != $checksum) {
			return response(ResponseStatus::corruptedChecksum);
		}

		if(Report::get($checksum) != FALSE) {
			return response(ResponseStatus::duplicatedMessage);
		}

		$json = json_decode($json, true);

		if(json_last_error() != JSON_ERROR_NONE) {
			return response(ResponseStatus::corruptedFormat);
		}

		try {
			ORM::get_db()->beginTransaction();
			Report::create($user, $json, $checksum);
			ORM::get_db()->commit();
		}
		catch(Exception $e) {
			ORM::get_db()->rollBack();
			throw new Exception($e->getMessage());
		}

		return response(NULL);

	} catch(Exception $e) {
			return response(ResponseStatus::internalServerError, $e->getMessage());
	}
});

//TODO::Сделать проверку
$app->get('/rfid/report/id/:device/', function($device) use($app) {
//	try {
		if(($user = HttpSession::get()) == FALSE) {
			return response(ResponseStatus::sessionExpired);
		}

		//TODO::фильтр
		Report::getReportByDevice($device);
//	} catch(Exception $e) {
	//		return response(ResponseStatus::internalServerError, $e->getMessage());
//	}
});

$app->run();
?>