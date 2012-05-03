<?php
error_reporting(E_ALL);

//micro frameworkw
require_once 'Slim/Slim.php';
//micro orm
require_once 'idiorm.php';
//some classes
require_once 'user.php';

$app = new Slim(array());
$dbname = "rfid";
//Sjz1tib8uF7hEY5
//abitechk_rfid
ORM::configure('mysql:host=localhost;dbname='.$dbname);
ORM::configure('username', 'root');
ORM::configure('password', '');
ORM::get_db()->exec('set names utf8');

//test client 
require_once 'mockClient.php';

//install. db should be created in advance
require_once 'install.php';

//TODO::добавить prepared_statements
//TODO::ограничить localhost
$app->post('/rfid/signup/', function() use($app) {
	try	{
		if(checkAllowedIP() == false) {
			return response(ResponseStatus::bannedIP);
		}
		//Устройство предъявляет ключ проекта
		$login = $app->request()->post('login');
		$pass  = $app->request()->post('pass');
		$user = User::get('project', $pass, 0);

		//Если ключ верен и пользователя с таким логином незарегистривано
		if($user == true && User::doesUserExist($login) == false) {
			$pass = getRandomString();
			User::create($login, $pass);
			return response(null, $pass);
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

		if($user == false) {
			return response(ResponseStatus::invalidCredentials);
		}

		HttpSession::set($user);

		return response(null);

	} catch(Exception $e) {
			return response(ResponseStatus::internalServerError, $e->getMessage());
	}
});

$app->post('/rfid/post/', function() use($app) {
	try {
		if(($user = HttpSession::get()) == false) {
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

		if(Report::get($checksum) != false) {
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
		} catch(Exception $e) {
			ORM::get_db()->rollBack();
			throw new Exception($e->getMessage());
		}

		return response(null);

	} catch(Exception $e) {
			return response(ResponseStatus::internalServerError, $e->getMessage());
	}
});

//TODO::Сделать проверку
$app->get('/rfid/report/location/:location/', function($location) use($app) {
	try {
		if(($user = HttpSession::get()) == false) {

			return response(ResponseStatus::sessionExpired);
		}

		$location = ORM::for_table('locations')->where('key', $location)->find_one();

		if($location == false) {
			return ;
		}

		//TODO::фильтр
		Report::getReportByLocation($location);
	} catch(Exception $e) {
			return response(ResponseStatus::internalServerError, $e->getMessage());
	}
});

$app->run();
?>