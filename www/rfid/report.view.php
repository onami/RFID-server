<?php

	//TODO::сделать нормальные шаблоны
	$style = @"<style>
		tr.head { background: orange; }
		tr.report:nth-child(even) {background: #CCC}
		tr.report:nth-child(odd) {background: #FFF}
		td.score {background: orange;}
		tr { font-family: Calibri; }
	</style>";

class ReportView {
	static function renderReportsList() {
		global $style;
		$locations = array();
		
		echo $style;

		//Месторасположения
		echo '<table><thead><tr class="head"><td>Месторасположения</td></thead>';
		foreach(ORM::for_table('locations')->find_many() as $location)
		{
			$locations[$location->id] = $location->description;
			echo "<tr class='report'><td><a href='location/{$location->id}/'>{$location->description}</a></td></tr>";
		}
		echo '</table>';

		//Устройства
		$devices = ORM::for_table('devices')->find_many();
		echo '<table><thead><tr class="head"><td colspan=2>Считыватели</td></tr></thead>';
		foreach($devices as $device)
		{
			echo "<tr class='report'><td><a href='device/{$device->id}/'>{$device->description}</td><td>{$locations[$device->location_id]}</td></tr>";
		}
		echo '</table>';

		$roles = Report::getRolesDictionary();

		echo '<table><thead><tr class="head"><td>Персонал</td><td>Должность</td></thead>';
		foreach(ORM::for_table('users')->group_by('user_id')->find_many() as $user) {
			echo @"<tr class='report'><td><a href='user/{$user->user_id}/'>{$user->description}</td>
			<td>{$roles[$user->role_id]}</td></tr>";
		}
	}

	static function renderByUser($user) {
		$userDevices = ORM::for_table('users')->where('user_id', $user->user_id)->find_many();

		foreach($userDevices as $device) {
			self::renderByDevice(Report::getDeviceById($device->device_id));
			echo '<br/>';
		}
	}

	static function renderByDevice($device) {
		global $style;

		$location = Report::getLocationById($device->location_id);

		//Считаем число уникальных меток, связанных с данным $device
		$total = ORM::for_table('tags_list')
			->raw_query(@"
					SELECT count(*) as total
					FROM tags_list tl, reading_sessions r
					WHERE 
						tl.last_session_id = r.session_id and
						r.device_id = {$device->id}", array())
			->find_one();

		//Берём все сессии, связанные с данным расположением
		$results = ORM::for_table('reading_sessions')->raw_query(@"
			SELECT r.*
			FROM reading_sessions r
			WHERE r.device_id = {$device->id}
			ORDER BY r.time_marker
			", array())->find_many();

		echo <<<END
{$style}
END;

		echo @"<table border=1>
		<thead>
				<tr>
					<td colspan=2>{$device->description}</td>
				</tr>
				<tr>
					<td colspan=2>Считано уникальных меток: {$total->total}</td>
				</tr>

				<tr class='head'>
					<td>Меток за сеанс</td>
					<td>Время считывания</td>
				</tr>
			</thead>";


		foreach($results as $tag) {
			echo @"
				<tr class='report'>
					<td>{$tag->count}</td>
					<td>{$tag->time_marker}</td>
				</tr>";
		}

		echo '</table>';
	}

	static function renderByLocation($location) {
		global $style;

		//Считаем число уникальных меток, связанных с данным $location
		$total = ORM::for_table('tags_list')
			->raw_query(@"
					SELECT count(*) as total
					FROM tags_list tl, reading_sessions r
					WHERE 
						tl.last_session_id = r.session_id and
						r.location_id = {$location->id}", array())
			->find_one();

		//Берём все сессии, связанные с данным расположением
		$results = ORM::for_table('reading_sessions')->raw_query(@"
			SELECT r.*
			FROM reading_sessions r
			WHERE r.location_id = {$location->id}
			ORDER BY r.time_marker
			", array())->find_many();

		$devices = Report::getDevicesDictionary();

		echo <<<END
{$style}
END;

		echo @"<table>
		<thead>
				<tr>
					<td>Расположение:</td>
					<td>{$location->description}</td>
					<td>Всего числится меток:</td>
					<td>{$total->total}</td>
				</tr>
			</thead>
		<table>";

		echo @"<table>
		<thead>
				<tr class='head'>
					<td>Меток за сеанс</td>
					<td>Время считывания</td>
					<td>Считыватель</td>
				</tr>
			</thead>";


		foreach($results as $tag) {
			echo @"
				<tr class='report'>
					<td>{$tag->count}</td>
					<td>{$tag->time_marker}</td>
					<td>{$devices[$tag->device_id]}</td>
				</tr>";
		}

		echo '</table>';
	}
}
?>