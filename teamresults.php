<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	function sortbyposition($a, $b)
	{
		if ($a['position'] == $b['position'])
		{
			return 0;
		}

		return ($a['position'] < $b['position']) ? 1 : -1;
	}

	function sortbyavg($a, $b)
	{
		$avga = floatval($a['time1']) + floatval($a['time2']) / 2.0;
		$avgb = floatval($b['time1']) + floatval($b['time2']) / 2.0;

		if ($avga == $avgb)
		{
			return 0;
		}

		return ($avga < $avgb) ? -1 : 1;
	}

	session_start();

	$id = (int)(0 . $_GET['id']);

	if (isset($_GET['distance']))
	{
		$dist = (int)(0 . $_GET['distance']);
	}
	else
	{
		$dist = 0;
	}
	
	if ($dist == 0 && isset($_SESSION['distance']))
	{
		$dist = $_SESSION['distance'];
	}
	
	if ($dist == 0)
	{
		$dist = 25;
	}
	
	$_SESSION['distance'] = $dist;

	if ($aryCompo = $db->getCompetition($id))
	{
		if (isset($_POST['result']))
		{
			$aryResults = array();
			$aryTakenPositions = array();
			

			foreach($_POST as $field => $time)
			{
				$f = split("_", $field);
				if ($f[0] == 'place')
				{
					if ($time == '')
					{
						$time = '0';
					}
	
					if ($time1 == '')
					{
						$time1 = $time2;
					}
	
					if ($time2 == '')
					{
						$time2 = $time1;
					}
	
					if ($time2 == '')
					{
						$time1 = '0.0';
						$time2 = '0.0';
					}
	
					if (intval($time) > 0)
					{
						$aryTakenPositions[] = $time;
					}
					$aryResults[] = array('swimmer' => $f[1], 'time1' => $time1, 'time2' => $time2, 'position' => $time);
				}
				elseif (count($f) > 1 && $f[2] == '1')
				{
					$time1 = $time;
				}
				else
				{
					$time2 = $time;
				}
			}

			usort($aryResults, 'sortbyavg');

			$posn = 1;
	
			for ($cnt = 0; $cnt < count($aryResults); $cnt++)
			{
				if ($aryResults[$cnt]['position'] == '0')
				{
					while (in_array($posn, $aryTakenPositions))
					{
						$posn++;
					}
	
					$aryResults[$cnt]['position'] = $posn;
					$posn++;
				}
			}
			
			foreach ($aryResults as $res)
			{
				$db->updateTeamResult($res['swimmer'], $res['time1'], $res['time2'], $res['position']);
			}
		}
?>
<?php
	$activetab = 'teamresults';
	$subtitle = _('Stafetresultater');
	require('competitionhead.php');
?>
	<ul class="nav nav-tabs">
		<li<?= $dist == 25 ? ' class="active"' : '' ?>><a href="teamresults.php?id=<?= $id ?>&distance=25"><?= _('25m') ?></a></li>
		<li<?= $dist == 50 ? ' class="active"' : '' ?>><a href="teamresults.php?id=<?= $id ?>&distance=50"><?= _('50m') ?></a></li>
	</ul>

	<div class="container" style="margin-top: 10px;">
	<nav class="navbar navbar-default">
		<ul class="nav navbar-nav navbar-right">
			<li><a href="printteamresults.php?id=<?= $id ?>" target="_blank"><span class="glyphicon glyphicon-print"></span> <?= _('Udskriv resultater') ?></a></li>
		</ul>
	</nav>	
					<form method="post">
						<table class="table table-striped table-bordered">						
							<tr><th><?= _('Hold') ?></th><th><?= _('Tid 1') ?></th><th><?= _('Tid 2') ?></th><th><?= _('Plac.') ?></th><th></th></tr>
<?PHP
	$rsTeams = $db->teamList($id, $dist);
	
	while ($team = $db->fetch_array($rsTeams))
	{

		$average = ($team['result1'] + $team['result2']) / 2;
		$marker = '';

		if ($average == 0.0)
		{
			$average = '';
		}
		else
		{
			$average = number_format($average, 2);
		}

?>
							<tr>
								<td><?= $team['name'] ?></td>
								<td><input type="text" name="time_<?= $team['tid'] ?>_1" value="<?= $team['result1'] > 0.00 ? $team['result1'] : '' ?>" style="width: 40px;" onchange="fnFixTime(this);" /></td>
								<td><input type="text" name="time_<?= $team['tid'] ?>_2" value="<?= $team['result2'] > 0.00 ? $team['result2'] : '' ?>" style="width: 40px;" onchange="fnFixTime(this);" /></td>
								<td><input type="text" name="place_<?= $team['tid'] ?>" value="<?= $team['place'] != 0 ? $team['place'] : '' ?>" style="width: 40px;" /></td>
								<td><span id="result_<?= $team['tid'] ?>" style="width: 40px;"><?= $average ?></span></td>
							</tr>
<?PHP		
	}
?>
							<tr><td colspan="5" align="right"><input type="hidden" name="result" value="1" /><input type="submit" class="btn btn-primary" value="<?= _('Gem') ?>" /></td></tr>
						</table>
		</div>
<script>
	function fnFixTime(inp)
	{
		aryMatch = inp.value.match(/^(([0-9]+)[:m])?([0-9]*)([,.s]([0-9]+))?$/);

		if (aryMatch)
		{
			var mins = 0;
			var secs = 0;
			var hundreds = 0;

			if (aryMatch[2] != '')
			{
				mins = parseInt(aryMatch[2]);

				if (isNaN(mins)) mins = 0;
			}

			if (aryMatch[3] != '')
			{
				secs = parseInt(aryMatch[3]);

				if (isNaN(secs)) secs = 0;
			}

			if (aryMatch[5] != '')
			{
				hundreds = parseInt(aryMatch[5]);

				if (isNaN(hundreds)) hundreds = 0;
			}

			if (mins + secs + hundreds > 0)
			{
				inp.value = parseFloat((mins * 60 + secs) + '.' + hundreds).toFixed(2);
			}
			else
			{
				inp.value = '';
			}

			var swimmerId = inp.name.split("_")[1];

			var val1 = parseFloat(document.getElementById('time_' + swimmerId + '_1').value);
			var val2 = parseFloat(document.getElementById('time_' + swimmerId + '_2').value);

			if (isNaN(val1))
			{
				val1 = val2;
			}

			if (isNaN(val2))
			{
				val2 = val1;
			}

			if (isNaN(val1))
			{
				val1 = 0.00;
				val2 = 0.00;
			}

			var avg = (val1 + val2) / 2;

			document.getElementById('result_' + swimmerId).innerHTML = avg.toFixed(2);
		}
		else
		{
			inp.value = '';
		}
	}
</script>
<?php
	require('competitionfoot.php');
?>
<?PHP
	}

	$db->close();
?>