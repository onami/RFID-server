<?
class UserRole {
	const warehouseKeeper = 1; //Кладовщик
	const toolPusher = 2; //Буровой мастер
	const oilrigManager = 3; //Руководитель буровых мастеров
}

class LocationType {
	const warehouse = 1;
	const well = 2; //скважина
}


class Report {
	static function getRecord($tableName, $row, $value) {
		return $location = ORM::for_table($tableName)->where($row, $value)->find_one();		
	}

	static function getLocationById($id) {
		return self::getRecord('locations', 'id', $id);
	}

	static function getLocationByKey($key) {
		return self::getRecord('locations', 'key', $key);
	}

	static function getDeviceById($id) {
		return self::getRecord('devices', 'id', $id);
	}

	static function getDeviceByKey($key) {
		return self::getRecord('devices', 'key', $key);
	}

	static function getReportByChecksum($checksum) {
		return self::getRecord('reading_sessions', 'checksum', $checksum);
	}

	static function getBundleByChecksum($checksum) {
		return self::getRecord('tubes_bundles', 'checksum', $checksum);
	}

	static function getUserById($id) {
		return self::getRecord('users', 'id', $id);
	}

	//Словари
	static function getDictionary($tableName) {
		$objects = array();

		foreach(ORM::for_table($tableName)->find_many() as $object) {
			$objects[$object->id] = $object;
		}

		return $objects;
	}

	//Создание отчета
	static function create($device, $json, $checksum) {
		$session = ORM::for_table('reading_sessions')->create();
		$session->device_id = $device->id;
		$session->checksum = $checksum;
		$session->count = count($json['tags']);
		$session->time_marker = $json['time'];

		$location = self::getLocationByKey($json['location']);

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
							->id;

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
					->where('id', $tagInfo->last_session_id)
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

	static function createBundle($device, $json, $checksum) {
		$session = ORM::for_table('tubes_bundles')->create();
		$session->tag 			= $json['tag'];
		$session->checksum		= $checksum;
		$session->device_id		= $device->id;
		$session->session_time	= $json['time'];
		$session->session_mode 	= $json['sessionMode'];
		$session->district_id	= $json['bundle']['districtId'];
		$session->bundle_length	= $json['bundle']['bundleLength'];
		$session->bundle_time 	= date('Y-m-d H:i:s', $json['bundle']['time']);

		$session->save();
	}
}
?>