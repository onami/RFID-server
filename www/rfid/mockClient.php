<?php
Class MockClient {
	static function ViewPost() {

		echo <<<END
	<script src="http://o/rfid/sha1.js" type="text/javascript" encoding="UTF-8"></script>	
	<form action='/rfid/post/bgvlKW3ZAsLwzqLPkMXjG0oQJ6G4ax7eJxuZNbgN/' method='post' name='form1'>
		<textarea rows=25 cols=100 name='json'>{"time":"2012-05-02 04:34:43","location":"","readingStatus":0,"sessionMode":0,"tags":["0000","01023412DC03011808308272","0DB090097207019412109D36","12345678","3005FB63AC1F3841EC880467","8710439039640FFF1111111111111122222211111111","E2001982821300440820C438","E2001985850C002317705D27","E2001985850C0046144080A2","E2001985850C00542370212B","E2001985850C00710560DA72","E2001985850C00920430E451","E2001985850C01280960B58E","E2001985850C01371390866D","E2001985850C013920304145","E2001985850C015327900395","E2001985850C01720900BE5C","E2001985850C018011909CF1","E2001985850C01921320900A","E2001985850C0192144082EA","E2001985850C019814707E9D","E2001985850C02611270957D","E2002993931602600880BEE5","E2003412DC03011945197612","E2003412DC03011945207326","E2003412DC03011945215718","E2003412DC03011945233127","E2003412DC03011945281543","E2003412DC03011945290523","E2003412DC03011945290528","E2003412DC03011945290802","E2003412DC03011945292331","E20044686802010712109AEA","E2008320200100610970B0BA","E20083202001006321903048","E2008320200100710810C0EE","E2008320200101131070A938","E20083202001011821303516","E2008320200101550740C9DF","E20083202001016122702A20","E2008320200101660740CA0B","E2009061530100600840C096","E2009061530101020220F374","E2009061530101210220F3C0","E2009061530101681240986E"]}</textarea>
		<br/><input onclick='form1.checksum.value = SHA1(form1.json.value)' type=submit>
		<input name='checksum' size=44 type=text value="">
	</form>
END;
	}
}

$app->get('/post/', 'MockClient::ViewPost');
?>