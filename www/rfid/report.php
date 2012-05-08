<?
class Report {
	static function getLocationById($locationId) {
		return $location = ORM::for_table('locations')->where('id', $locationId)->find_one();
	}

		static function getDeviceById($deviceId) {
		return $location = ORM::for_table('devices')->where('id', $deviceId)->find_one();
	}

	static function getReportByChecksum($checksum) {
		return ORM::for_table('reading_sessions')->where('checksum', $checksum)->find_one();
	}

	static function getUserById($userId) {
		return ORM::for_table('users')->where('user_id', $userId)->find_one();
	}

	//Словари

	//TODO::Обобщить
	static function getRolesDictionary() {
		$roles = array();
		foreach(ORM::for_table('roles')->find_many() as $role) {
			$roles[$role->id] = $role->description;
		}

		return $roles;
	}

	static function getDevicesDictionary() {
		$devices = array();

		foreach(ORM::for_table('devices')->find_many() as $device) {
			$devices[$device->id] = $device->description;
		}

		return $devices;
	}

	static function getLocationsDictionary() {
		$locations = array();

		foreach(ORM::for_table('locations')->find_many() as $location) {
			$locations[$location->id] = $location->description;
		}

		return $locations;
	}

	//Создание отчета
	static function create($device, $json, $checksum) {
		$session = ORM::for_table('reading_sessions')->create();
		$session->device_id = $device->id;
		$session->checksum = $checksum;
		$session->count = count($json['tags']);
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

		//Можно реализовать через SELECT last_insert_rowid()
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