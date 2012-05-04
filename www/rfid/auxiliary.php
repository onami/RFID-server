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
	const inactiveAccount		= 9;
}
?>