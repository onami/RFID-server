<?php
Class MockClient {
	static function ViewSignup() {
		echo <<<END
	<form action='/rfid/signup/' method='post'>
		<input name='pass' type=text value='f0c951d22714b96f8b53e895df8fe0a8f8afc6f4' />
		<input name='login' type=text value='test' />
		<input type=submit />
	</form>
END;
	}

	static function ViewAuth() {
		echo <<<END
	<form action='/rfid/auth/' method='post'>
		<input name='pass' type=text value='000ed8fcfa3ded5aadaccf1106ca156602796bd8' />
		<input name='login' type=text value='test' />
		<input type=submit />
	</form>
END;
	}

	static function ViewPost() {
		echo <<<END
	<form action='/rfid/post/' method='post'>
		<input name='json' type=text value='{"coords":"50 40","time_stamp":"2012-11-10 09:08:07", "data": ["rfid1", "rfid2", "rfid3"]}'>
		<input  type=submit>
		<input name='checksum' type=text value='80ae91392e52ba3a29078f9d9b889a767b5dfc98'>
	</form>
END;
	}
}

$app->get('/rfid/signup/', function() {
	MockClient::ViewSignup();
});

$app->get('/rfid/auth/', function() {
	MockClient::ViewAuth();
});

$app->get('/rfid/post/', function() {
	MockClient::ViewPost();
});

?>