<?
class Report {
	static function getLocationById($locationKey) {
		return $location = ORM::for_table('locations')->where('key', $locationKey)->find_one();
	}

	static function getReportByChecksum($checksum) {
		return ORM::for_table('reading_sessions')
			->where('checksum', $checksum)
			->find_one();
	}

	static function renderReportByLocation($location) {

		$total = ORM::for_table('tags_list')
			->raw_query(@"
					SELECT count(*) as total
					FROM tags_list tl, reading_sessions r
					WHERE 
						tl.last_session_id = r.session_id and
						r.location_id = {$location->id}", array())
			->find_one();

		//Берём все сессии, связанные с данным расположением
		//Подсчитываем число меток, связанных с этими сессиями
		//TODO::делать подсчёт сразу же после создания записи о сессии
		$results = ORM::for_table('tubes')->raw_query(@"
			SELECT r.*, count(*) as total
			FROM tubes t
			JOIN reading_sessions r
			ON
				t.session_id = r.session_id and
				r.location_id = {$location->id}
			GROUP BY t.session_id
			ORDER BY r.time_marker
			", array())->find_many();

		$devices = array();

		foreach(ORM::for_table('devices')->find_many() as $device) {
			$devices[$device->id] = $device->description;
		}


		echo <<<END
<style>
	tr.head { background: orange; }
	tr.report:nth-child(even) {background: #CCC}
	tr.report:nth-child(odd) {background: #FFF}
	td.score {background: orange;}
	tr { font-family: Calibri; }
</style>
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
					<td>{$tag->total}</td>
					<td>{$tag->time_marker}</td>
					<td>{$devices[$tag->device_id]}</td>
				</tr>";
		}

		echo '</table>';
	}

	static function create($device, $json, $checksum) {
		$session = ORM::for_table('reading_sessions')->create();
		$session->device_id = $device->id;
		$session->checksum = $checksum;
		$session->time_marker = $json['time'];

		$location = self::getLocationById($json['location']);

		if($location == false) {
			$session->location_id = $device->location_id;
		} else {
			$session->location_id = $location->id;
		}

		$session->reading_status = $json['readingStatus'];
		$session->session_mode = $json['sessionMode'];
		$session->save();

		$readingSessionId = ORM::for_table('reading_sessions')
							->where('checksum', $checksum)
							->find_one()
							->session_id;

		foreach($json['tags'] as $tag) {
			$record = ORM::for_table('tubes')->create();
			$record->tag = $tag;
			$record->session_id = $readingSessionId;
			$record->save();

			$tagInfo = ORM::for_table('tags_list')->where('tag', $tag)->find_one();

			if($tagInfo == false) {
				$tagInfo = ORM::for_table('tags_list')->create();
				$tagInfo->tag = $tag;
			}

			//Если запрос существует, надо учесть тот факт,
			//что отчеты могут придти в разном порядке
			//и более ранний по времени считывания окажется,
			//последним из присланных
			else {
				$prevSessionTimeMarker = ORM::for_table('reading_sessions')
					->where('session_id', $tagInfo->last_session_id)
					->find_one()
					->time_marker;

				if(strtotime($prevSessionTimeMarker) > strtotime($session->time_marker)) {
					continue;
				}
			}

			$tagInfo->last_session_id = $record->session_id;
			$tagInfo->save();
		}
	}
}
?>