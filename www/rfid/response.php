<?php
class Response {
	const internalServerError	= 1;
	const invalidDeviceKey		= 2;
	const corruptedChecksum		= 3;
	const corruptedFormat		= 4;
	const duplicatedMessage		= 5;	
	const emptyRequest			= 6;

	//В случае ошибки $error != NULL
	static function Set($error, $result = NULL) {
		echo json_encode(array('result' => $result, 'error' => $error));
	}
}
?>