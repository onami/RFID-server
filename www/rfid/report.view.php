<?php

	//TODO::сделать нормальные шаблоны
	$style = @"<style>
		tr.head { background: orange; }
		tr.report:nth-child(even) {background: #CCC}
		tr.report:nth-child(odd) {background: #FFF}
		td.score {background: orange;}
		tr { font-family: Calibri; }
		td { vertical-align: top;}
	</style>";

class ReportView {

	static function RenderList($viewData) {
		global $style;

		echo $style;

		//Сотрудник
		echo @"
		<table>
			<thead>
				<tr class='head'>
					<td>Подвески</td>
				</tr>
			</thead>
			<tr><td><a href='bundle/'>Посмотреть отчет</a></td></tr>
		</table>	
		<table>
			<thead>
				<tr class='head'>
					<td>Сотрудник</td>
					<td>Должность</td>
				</tr>
			</thead>";

		foreach($viewData['users'] as $user) {
			echo @"<tr class='report'><td><a href='user/{$user->id}/'>{$user->description}</td>
			<td>{$roles[$user->role_id]}</td></tr>";
		}
		echo "</table>";

		echo @"
		<table>
			<thead>
				<tr class='head'>
					<td>Расположение</td>
				</tr>
			</thead>";

		foreach($viewData['locations'] as $location) {
			echo @"<tr class='report'><td><a href='location/{$location->id}/'>{$location->description}</td>
			<td>{$roles[$user->role_id]}</td></tr>";
		}
		echo "</table>";
	}

	static function warehouseKeeperReport($viewData) {
		global $style;

		$device = $viewData['device'];

		echo $style;

		echo @"<table border=1>
		<thead>
				<tr class='head'>
					<td>Сотрудник</td>
					<td colspan=2>{$viewData['user']->description}</td>
				</tr>
				<tr>
					<td colspan=3>Расположение: {$viewData['location']->description}</td>
				</tr>
					<tr>
						<td colspan=3>{$device->description}</td>
					</tr>
				<tr>
					<td colspan=3>Считано уникальных меток: {$viewData['total']}</td>
				</tr>

				<tr class='head'>
					<td>Меток за сеанс</td>
					<td>Время считывания</td>
				</tr>
			</thead>";


		foreach($viewData['sessions'] as $session) {
			echo @"
				<tr class='report'>
					<td>{$session->count}</td>
					<td>{$session->time_marker}</td>
				</tr>";
		}

		echo '</table>';
	}


	static function Bundles($viewData) {
		global $style;

		$sessionMode[0] = "Чтение";
		$sessionMode[1] = "Запись";	
				
		echo $style;

		echo @"<table>
		<thead>
				<tr class='head'>
					<td>Устройство</td>
					<td>ID метки</td>
					<td>Номер скважины</td>
					<td>Длина подвески</td>
					<td>Дата формирования подвески</td>
					<td>Время считывания</td>	
					<td>Режим считывания</td>							
				</tr>
			</thead>";

		foreach($viewData['bundles'] as $bundle) {
			echo @"
				<tr class='report'>
					<td>{$viewData['devices'][$bundle->device_id]->description}</td>
					<td>{$bundle->tag}</td>
					<td>{$bundle->district_id}</td>
					<td>{$bundle->bundle_length}</td>
					<td>{$bundle->bundle_time}</td>
					<td>{$bundle->session_time}</td>
					<td>{$sessionMode[$bundle->session_mode]}</td>
				</tr>";
		}

		echo '</table>';
	}


	static function oilrigManagerReport($viewData) {
		global $style;

		$user = $viewData['user'];

		echo $style;

		echo @"<table>
		<thead>
				<tr class='head'>
					<td>Сотрудник</td>
					<td>{$viewData['user']->description}</td>
				</tr>
			</thead>";

		echo '</table>';
		echo '<hr/>';
	}

	static function toolPusherReport($viewData) {
		global $style;

		$device = $viewData['device'];
		$user = $viewData['user'];

		echo $style;

		echo @"<table>
		<thead>
				<tr class='head'>
					<td>Сотрудник</td>
					<td colspan=3>{$viewData['user']->description}</td>
				</tr>
				<tr>
					<td colspan=4>{$device->description}</td>
				</tr>
				<tr>
					<td colspan=4>Считано уникальных меток: {$viewData['total']}</td>
				</tr>
				<tr class='head'>
					<td colspan=2>Время</td>
					<td>Место</td>
					<td>Меток</td>
				</tr>
			</thead>";

		foreach($viewData['sessions'] as $session) {
			$date = new DateTime($session->time_marker);
			echo @"
				<tr class='report'>
					<td>{$date->format('d.m.Y')}</td>
					<td>{$date->format('H:i:s')}</td>
					<td>{$viewData['locations'][$session->location_id]->description}</td>
					<td>{$session->count}</td>
				</tr>";
		}

		echo '</table>';
	}


	static function locationReport($viewData) {
		global $style;

		echo $style;

		echo @"<table>
		<thead>
				<tr class='head'>
					<td>Расположение</td>
					<td colspan=4>{$viewData['location']->description}</td>
				</tr>
				<tr class='head'>
					<td>Дата</td>
					<td>Время</td>
					<td>Считано</td>
					<td>НКТ</td>
					<td>Исполнитель</td>
				</tr>
			</thead>";

		foreach($viewData['sessions'] as $session) {
			$date = new DateTime($session->time_marker);

			echo @"
				<tr>
					<td>{$date->format('d.m.Y')}</td>
					<td>{$date->format('H:i:s')}</td>
					<td><center><b><font size=5>{$session->count}</font></b></center></td>
					<td>";
					echo "<table>";
					foreach($viewData['tags'][$session->session_id] as $tag) {
						echo "<tr class='report'><td>{$tag->tag}</td></tr>";
					}
					echo "</table>";

					echo @"</td>
					<td>{$session->description}</td>
				</tr>";
		}	

		echo '</table>';
	}
}
?>