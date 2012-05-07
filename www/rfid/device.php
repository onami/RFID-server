<?php
class Device {
	public static function get($deviceKey) {
		return ORM::for_table('devices')
			->where('key', $deviceKey)
			->find_one();
	}
}
?>