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

	static function RenderList($viewData) {
		global $style;

		echo $style;

		//Сотрудник
		echo @"
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


	static function toolPusherReport($viewData) {
		global $style;

		$device = $viewData['device'];
		$user = $viewData['user'];

		echo $style;

		echo @"<table border=1>
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
					<td>Меток за сеанс</td>
					<td>Время считывания</td>
					<td>Считыватель</td>
					<td>Место считывания</td>
				</tr>
			</thead>";


		foreach($viewData['sessions'] as $session) {
			echo @"
				<tr class='report'>
					<td>{$session->count}</td>
					<td>{$session->time_marker}</td>
					<td>{$device->description}
					<td>{$locations[$session->location_id]}
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
					<td>Дата</td>
					<td>Время</td>
					<td>Считано</td>
					<td>НКТ</td>
					<td>Буровой мастер</td>
				</tr>
			</thead>
		<table>";

		echo '</table>';
	}
}
?>