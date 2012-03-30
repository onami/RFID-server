<?php

require 'Slim/Slim.php';
require_once 'idiorm.php';

//TODO::потом сбросить срок кукисов на дефолт: 20 минут
$app = new Slim(array(
	'cookies.lifetime' => time() + 24*60*60));

ORM::configure('mysql:host=localhost;dbname=rfid');
ORM::configure('username', 'root');
ORM::configure('password', '');
$db = ORM::get_db();
ORM::set_db($db);

function getSha1()
	{
		return sha1(microtime(true).mt_rand(10000,90000));
	}

$app->get('/rfid/signup/:projectPass/:login', function($projectPass, $login) use($app){
	// f0c951d22714b96f8b53e895df8fe0a8f8afc6f4
	if(ORM::for_table('users')->where('pass', $projectPass)->where('login', 'project')->find_one() == FALSE ||
		ORM::for_table('users')->where('login', $login)->find_one() == TRUE) {
		echo 'Error 403';
		return $app->response()->status(403);
		}

	//TODO::Изменить на полностью рандомную строку
	$pass = getSha1();
	//TODO::Хранить зашифрованные пароли
	ORM::get_db()->exec("INSERT INTO `users` (`login`, `pass`, `status`) VALUES ('$login', '$pass', 1)");

	echo 'OK';
//TODO::доделать регэкспы
//TODO::сделать экранирование входных параметров по всех точках входа
	})->conditions(array('projectPass' => '[a-zA-Z0-9]+', 'login' =>  '[a-zA-Z0-9]+'));

/* Метод auth*/

$app->get('/rfid/auth/:login/:pass', function($login, $pass) use($app) {
	//TODO:Проверять зашифрованные строки
	$user = ORM::for_table('users')->where('login', $login)->where('pass', $pass)->where_not_equal('status', 0)->find_one();

	if($user == FALSE) {
		echo 'Error 403';
		return $app->response()->status(403);
		}

	$sessionKey = getSha1();
	$session = ORM::for_table('connection_sessions')->where('user_id', $user->id)->find_one();

	if($session == TRUE)
		{
			//TODO::сделать удаление устаревших сессиий
			ORM::get_db()->exec("UPDATE `connection_sessions` SET time_stamp = '".date('Y-m-d h:i:s')."', session_key = '$sessionKey' WHERE user_id = ".$user->id);
		}

	else {
		ORM::get_db()->exec("INSERT INTO `connection_sessions` (`user_id`, `session_key`, `time_stamp`) VALUES ($user->id, '$sessionKey', '".date('Y-m-d h:i:s')."')");
		}

	//TODO::устанавливать cookie только через HTTPS
	$session = $app->setcookie('session_id', $sessionKey);

	echo 'OK';
	});

/* Отправка rfid-меток */

$app->map('/rfid/post', function() use($app) {
//TODO::убрать формы и метод get.
	echo <<<END
	<form action='/rfid/post' method='post'><input name='dump' type=text> <input  type=submit>
	<input name='checksum' type=text value='80ae91392e52ba3a29078f9d9b889a767b5dfc98'>
	</form>
END;

	$session = ORM::for_table('connection_sessions')->where('session_key', $app->getcookie('session_id'))->find_one();
	
	if($session == FALSE) {
		echo 'Error 403';
		return $app->response()->status(403);
		}	
//TODO::обрабатывать как ошибку
	if(time() - strtotime($session->time_stamp) >= 1000) {
			echo 'Session has been expired<br/>';
		}

	$user = ORM::for_table('users')->where('id', $session->user_id)->find_one();
	$dump = trim($app->request()->post('dump'));


	$checksum = $app->request()->post('checksum');
	if(sha1($dump) != $checksum) {
		echo 'corrupted_data<br/>';
		return;
		}
//TODO::переделать проверку $dump, т.к. данные посылаются ВСЕГДА
	if($dump == NULL) return;

	$dump = json_decode($dump, true);
	
	if(json_last_error() != JSON_ERROR_NONE) {
		echo 'corrupted_json<br/>';
		return;
	}

	if(ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one() == TRUE)
	{
		echo 'duplicate';
		return;

	}

//TODO::А что если будут пересылаться те же самые данные
	ORM::get_db()->exec("INSERT INTO `reading_sessions` (`user_id`, `checksum`, `time_stamp`, `coords`) VALUES ({$session->user_id}, '$checksum', '{$dump['time_stamp']}', GeomFromText('POINT({$dump['coords']})'))");

	$readingSessionId = ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one();

//TODO::сделать в таблице композитный ключ.
	foreach($dump['data'] as $tag) {
		ORM::get_db()->exec("INSERT INTO `tubes` (`tag`, `session_id`, `status`) VALUES ('$tag', {$readingSessionId->session_id}, '1')");
	}
 	
 	print_r($dump);

//TODO::убрать GET после тестирования
	})->via('GET','POST');

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This is responsible for executing
 * the Slim application using the settings and routes defined above.
 */
$app->run();