<?php
error_reporting(E_ALL);

//micro framework
require_once 'Slim/Slim.php';
//micro orm
require_once 'idiorm.php';
//some classes
require_once 'user.php';

$app = new Slim(array());
ORM::configure('mysql:host=localhost;dbname=test');
ORM::configure('username', 'root');
ORM::configure('password', '');

//test client
require_once 'mockClient.php';

//install
//require_once 'install.php';

$app->post('/rfid/signup/', function() use($app) {
	$login = $app->request()->post('login');
	$pass  = $app->request()->post('pass');
	$user = User::get('project', $pass, 0);

	if($user == FALSE) {
		return responseStatus(403);
	}
	else if(User::doesUserExist($login) == FALSE) {
		$pass = getRandomString();
		User::create($login, $pass);
		return responseStatus(200, $pass);
	}

	return responseStatus(403);
});

$app->post('/rfid/auth/', function() use($app) {
	//TODO::filter unsafe symbols; Slim conditions seem not work with post-requests
	$login = $app->request()->post('login');
	$pass  = $app->request()->post('pass');
	$user = User::get($login, $pass, 1);

	if($user == FALSE) {
		return responseStatus(403);
	}

	HttpSession::set($user);
});

$app->post('/rfid/post/', function() use($app) {
	if(($user = HttpSession::get()) == FALSE) {
		return responseStatus(403, Status::sessionExpired);
	}

	//TODO::проверить на пустых данных
	$json = $app->request()->post('json');
	$checksum = $app->request()->post('checksum');

	if(sha1($json) != $checksum) {
		return responseStatus(403, Status::corruptedChecksum);
	}

	if(Report::get($checksum) != FALSE) {
		return responseStatus(403, Status::duplicatedMessage);
	}

	$json = json_decode($json, true);

	if(json_last_error() != JSON_ERROR_NONE) {
		return responseStatus(403, Status::corruptedFormat);
	}
   
	Report::create($user, $json, $checksum);
});

$app->run();
echo HttpSession::sessionTtl;
?>