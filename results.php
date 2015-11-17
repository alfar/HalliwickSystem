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

	if (!isset($_SESSION["round" . $dist]))
	{
		$_SESSION["round" . $dist] = 0;
	}

	if (isset($_POST["round"]))
	{
		$_SESSION["round" . $dist] = (int)(0 . $_POST['round']);
	}

	$round = $_SESSION["round" . $dist];

	if (isset($_GET['clearSwimmer']))
	{
		$swimmerId = (int)(0 . $_GET['clearSwimmer']);
		$db->deleteCompetitionSwimmer($id, $swimmerId);
	}

	if (isset($_GET["heat"]))
	{
		$raceId = $_GET['heat'];

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
			elseif ($f[2] == '1')
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

		usort($aryResults, 'sortbyposition');

		$disqn = -1;

		foreach ($aryResults as $res)
		{
			if ($res['position'] < 0)
			{
				$res['position'] = $disqn;
				$disqn--;
			}
			$db->updateResult($raceId, $res['swimmer'], $res['time1'], $res['time2'], $res['position']);
		}
	}

	if ($aryCompo = $db->getCompetition($id))
	{
		$extra100 = $aryCompo['extra100'];
		$trackBits = $aryCompo['tracks'];
		$aryTracks = array();
		$trackCount = 0;
		$cnt = 0;
		while ($trackBits > 0)
		{
			$trackCount += $trackBits & 1;
			$aryTracks[$cnt++] = $trackBits & 1;
			$trackBits = $trackBits >> 1;
		}
		
		while ($cnt < $number_of_tracks)
		{
			$aryTracks[$cnt++] = 0;
		}
?>
<?php
	$activetab = 'results';
	$subtitle = _('Resultater');
	require('competitionhead.php');
?>

	<ul class="nav nav-tabs">
		<li<?= $dist == 25 ? ' class="active"' : '' ?>><a href="results.php?id=<?= $id ?>&distance=25"><?= _('25m') ?></a></li>
		<li<?= $dist == 50 ? ' class="active"' : '' ?>><a href="results.php?id=<?= $id ?>&distance=50"><?= _('50m') ?></a></li>
		<li<?= $dist == 100 ? ' class="active"' : '' ?>><a href="results.php?id=<?= $id ?>&distance=100"><?= _('100m') ?></a></li>
	</ul>

	<div class="container" style="margin-top: 10px;">
		<div class="navbar navbar-default">
			<form name="roundpicker" class="navbar-form navbar-left" action="results.php?id=<?= $id ?>&distance=<?= $dist ?>" method="post">
				<div class="form-group">
					<label for="round"><?= _('Runde') ?>:</label>
					<select name="round" onchange="document.forms['roundpicker'].submit()" style="width: 200px;" class="form-control">
						<option value="0" <?PHP if ($round == 0) echo('selected="selected"'); ?>><?= _('Indledende') ?></option>
<?PHP
	if ($dist < 100 || $extra100 > 0)
	{
?>
						<option value="4" <?PHP if ($round == 4) echo('selected="selected"'); ?>><?= _('Semifinale') ?></option>
						<option value="8" <?PHP if ($round == 8) echo('selected="selected"'); ?>><?= _('Finale') ?></option>
<?PHP
	}
	else
	{
?>
						<option value="5" <?PHP if ($round == 5) echo('selected="selected"'); ?>><?= _('Finale') ?></option>
<?PHP
	}
	if ($dist < 100 || $extra100 > 0)
	{
?>
						<option value="20" <?PHP if ($round == 20) echo('selected="selected"'); ?>><?= _('Semifinale Ekstraløb') ?></option>
						<option value="24" <?PHP if ($round == 24) echo('selected="selected"'); ?>><?= _('Finale Ekstraløb') ?></option>
<?PHP
	}
?>
					</select>
				</div>
			</form>
				<ul class="nav navbar-nav navbar-right">
					<li><a target="_blank" href="printresults.php?id=<?= $id ?>"><span class="glyphicon glyphicon-print"></span> <?= _('Udskriv resultater') ?></a></li>
				</ul>
	</div>
<?PHP
	$rsHeats = $db->raceList($id, $dist, $round);

	$heatCount = 0;

	while($aryHeats = $db->fetch_array($rsHeats))
	{
		$heatCount++;
		$startTime = 0;

		if ($heatCount % 2 == 1)
		{
?>
		<div class="row">
<?PHP
		}

		$raceId = $aryHeats['id'];
?>
		<div class="col-lg-6">
		<a name="heat<?PHP echo($raceId); ?>"></a>
		<form action="results.php?id=<?PHP echo($id); ?>&heat=<?PHP echo($raceId); ?>#heat<?PHP echo($raceId); ?>" method="post" style="display: inline;">
			<table class="table table-striped table-bordered table-condensed">
				<tr><th><?= _('Heat nr.') ?> <?= $aryHeats['number'] ?></th><th><?= _('Klub') ?></th><th><?= _('Till.') ?></th><th><?= _('Tid 1') ?></th><th><?= _('Tid 2') ?></th><th><?= _('Pl.') ?></th><th></th><th></th></tr>
<?PHP
			$rsSwimmers = $db->raceSwimmers($raceId);

			$track = 0;
			while ($arySwimmers = $db->fetch_array($rsSwimmers))
			{
				$track++;
				while(isset($aryTracks[$track - 1]) && $aryTracks[$track - 1] == 0)
				{
					if ($track > $number_of_tracks)
					{
						break;
					}

					echo('<tr><td colspan="8">---</td></tr>');
					$track++;
				}
				
				while($track < $arySwimmers['track'])
				{
					echo('<tr><td colspan="8" class="emptytrack">' . _('(Tom)') . '</td></tr>');
					$track++;
				}
				
				if ($startTime == 0)
				{
					if ($round == 0)
					{
						$startTime = $arySwimmers['time'];
					}
					elseif ($round == 4 || $round == 20)
					{
						$startTime = $arySwimmers['semitime'];
					}
					else
					{
						$startTime = $arySwimmers['finaltime'];
					}
				}

				if ($round == 0)
				{
					$swimmerTime = $arySwimmers['time'];
				}
				elseif ($round == 4 || $round == 20)
				{
					$swimmerTime = $arySwimmers['semitime'];
				}
				elseif ($round == 5 || $round == 8 || $round == 24)
				{
					$swimmerTime = $arySwimmers['finaltime'];
				}
				$started = round($startTime - $swimmerTime);
				$allowedtime = $swimmerTime * 0.93 + $started;
				$average = ($arySwimmers['result1'] + $arySwimmers['result2']) / 2;
				$marker = '';

				if ($average == 0.0)
				{
					$average = '';
				}
				else
				{
					if (floor($average * 100) < floor($allowedtime * 100))
					{
						$marker = '<span style="color: #ff0000; font-weight: bold;">X</span>';
					}
					$average = number_format($average, 2);
				}

				if ($arySwimmers['position'] == 999)
				{
					$marker = '<span style="color: #ff0000; font-weight: bold;">%</span>';
				}
?>
				<tr>
					<td><?= $arySwimmers['swimmername'] ?></td>
					<td><?= $arySwimmers['clubname'] ?></td>
					<td id="allowed_<?= $arySwimmers['swimmerId'] ?>"><?= number_format($allowedtime,2) ?></td>
					<td><input type="text" id="time_<?= $arySwimmers['swimmerId'] ?>_1" name="time_<?= $arySwimmers['swimmerId'] ?>_1" value="<?= $arySwimmers['result1'] > 0.00 ? $arySwimmers['result1'] : '' ?>" style="width: 40px;" onchange="fnFixTime(this);" /></td>
					<td><input type="text" id="time_<?= $arySwimmers['swimmerId'] ?>_2" name="time_<?= $arySwimmers['swimmerId'] ?>_2" value="<?= $arySwimmers['result2'] > 0.00 ? $arySwimmers['result2'] : '' ?>" style="width: 40px;" onchange="fnFixTime(this);" /></td>
					<td><input type="text" id="place_<?= $arySwimmers['swimmerId'] ?>" name="place_<?= $arySwimmers['swimmerId'] ?>" value="<?= $arySwimmers['position'] != 0 ? $arySwimmers['position'] : '' ?>" style="width: 27px;" /></td>
					<td><span id="result_<?= $arySwimmers['swimmerId'] ?>" style="width: 40px;"><?= $average . $marker ?></span></td>
					<td><a href="javascript:fnCancel(<?= $arySwimmers['swimmerId'] ?>)" title="<?= _('Svømmede ikke') ?>"><span class="glyphicon glyphicon-remove"></span></a></td>
				</tr>
<?PHP
			}
?>
				<tr><td colspan="8" align="right"><input type="submit" class="btn btn-primary" value="<?= _('Gem') ?>" /></td></tr>
			</table>
		</form>
	</div>
<?PHP
		if ($heatCount % 2 == 0)
		{
?>
	</div>
<?PHP
		}
	}

	if ($heatCount % 2 == 1)
	{
?>
	</div>
<?PHP
	}
?>
</div>
<script language="javascript" src="timefunctions.js"></script>
<script language="javascript">
	function fnFixTime(inp)
	{
		time = fnGetTime(inp.value)
			
		if (time != null)
		{
			inp.value = time;

			var swimmerId = inp.id.split("_")[1];
		
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
		
			var allowed = parseFloat(document.getElementById('allowed_' + swimmerId).innerText);
		
			var marker = '';
			if (avg < allowed)
			{
				marker = '<span style="color: #ff0000; font-weight: bold">X</span>';
				document.getElementById('place_' + swimmerId).value = (avg - allowed).toFixed(2);
			}
			else
			{
				document.getElementById('place_' + swimmerId).value = "";
			}
			document.getElementById('result_' + swimmerId).innerHTML = avg.toFixed(2) + marker;
		}
		else
		{
			inp.value = '';
		}
	}
	
	function fnCancel(swimmerId)
	{
		if (confirm('<?= _('Er du sikker på, denne svømmer ikke svømmede?') ?>'))
		{
			document.getElementById('time_' + swimmerId + '_1').value = '';
			document.getElementById('time_' + swimmerId + '_2').value = '';
			document.getElementById('place_' + swimmerId).value = '999';
			document.getElementById('result_' + swimmerId).innerHTML = '<span style="color: #ff0000; font-weight: bold">%</span>';
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