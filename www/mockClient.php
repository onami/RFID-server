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
		<input name='login' type=text value='bgvlKW3ZAsLwzqLPkMXjG0oQJ6G4ax7eJxuZNbgN' />
		<input name='pass' type=text value='bb5e1379ccaa46ab1dce6917e0be341706ab776e' />
		<input type=submit />
	</form>
END;
	}

	static function ViewPost() {
		echo <<<END
	<form action='/rfid/post/' method='post'>
		<input name='json' type=text value='{"id":1,"time":"2012-04-19 05:54:51","location":"","deliveryStatus":0,"readingStatus":1,"tags":["01023412DC03011808308272","E2001985850C00542370212B","E2001985850C00710560DA72","E2001985850C013920304145","E2001985850C015327900395","E2001985850C01720900BE5C","E2001985850C02611270957D","E2001985850C02821860588C","E2009061530100600840C096"]}'>
		<input  type=submit>
		<input name='checksum' type=text value="edd8005c6abc8bce242261231bdd050f24b4b887">
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