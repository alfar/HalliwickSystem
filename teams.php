<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	session_start();

	function recalcTeamTime($teamId)
	{
		global $db;
		$rsSwimmers = $db->teamSwimmers($teamId);
					
		$total = 0.0;
		
		while ($swimmer = $db->fetch_array($rsSwimmers))
		{
			$total += $swimmer['time'];
		}
		
		$db->updateTeamTime($teamId, $total);
	}


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
		
		if (isset($_POST['newteam']))
		{
			if ($_POST['newteam'] == 1)
			{
				$clubId = $_POST['clubId'];
				
				$time1 = 0;
				$time2 = 0;
				$time3 = 0;
				$time4 = 0;
				
				$teamId = $db->newTeam($id, $clubId, $dist);
				
				$swimmers = $_POST['swimmer1'] . ', ' . $_POST['swimmer2'] . ', ' . $_POST['swimmer3'] . ', ' . $_POST['swimmer4'];
				$rsSwimmers = $db->teamSwimmerList($id, $dist, $swimmers);
				
				while ($swimmer = $db->fetch_array($rsSwimmers))
				{
					$currentswimmer = $swimmer['swimmerId'];
					$res = $db->getSwimmerBestTime($id, $currentswimmer, $dist);
					
					if ($currentswimmer == $_POST['swimmer1'] && $time1 == 0 && $res['result'] !== null)
					{
						$time1 = $res['result'];
					}
					elseif ($currentswimmer == $_POST['swimmer2'] && $time2 == 0 && $res['result'] !== null)
					{
						$time2 = $res['result'];
					}
					elseif ($currentswimmer == $_POST['swimmer3'] && $time3 == 0 && $res['result'] !== null)
					{
						$time3 = $res['result'];
					}
					elseif ($currentswimmer == $_POST['swimmer4'] && $time4 == 0 && $res['result'] !== null)
					{
						$time4 = $res['result'];
					}
				}				

				$db->addTeamSwimmer($teamId, $_POST['swimmer1'], $time1);
				$db->addTeamSwimmer($teamId, $_POST['swimmer2'], $time2);
				$db->addTeamSwimmer($teamId, $_POST['swimmer3'], $time3);
				$db->addTeamSwimmer($teamId, $_POST['swimmer4'], $time4);
				
				recalcTeamTime($teamId);
			}
		}
				
		if (isset($_GET['newtimeteam']) && isset($_GET['newtimeswimmer']))
		{
			$db->updateTeamSwimmerTime($_GET['newtimeteam'], $_GET['newtimeswimmer'], $_GET['time']);
			recalcTeamTime($_GET['newtimeteam']);
		}
				
		if (isset($_GET['delete']))
		{
			$deleteId = $_GET['delete'];
			$db->deleteTeam($id, $deleteId);
		}
?>
<?php
	$activetab = 'teams';
	$subtitle = _('Stafet');
	require('competitionhead.php');
?>
	<ul class="nav nav-tabs">
		<li<?= $dist == 25 ? ' class="active"' : '' ?>><a href="teams.php?id=<?= $id ?>&distance=25"><?= _('25m') ?></a></li>
		<li<?= $dist == 50 ? ' class="active"' : '' ?>><a href="teams.php?id=<?= $id ?>&distance=50"><?= _('50m') ?></a></li>
	</ul>

	<div class="container" style="margin-top: 10px;">

	<nav class="navbar navbar-default">
		<ul class="nav navbar-nav navbar-right">
			<li><a href="printteams.php?id=<?= $id ?>" target="_blank"><span class="glyphicon glyphicon-print"></span> <?= _('Udskriv heat') ?></a></li>
		</ul>
	</nav>


							<table class="table table-striped">
								<tr><th colspan="3"><?= _('Hold') ?></th><th align="right"><?= _('Tilmeldt tid') ?></th><th align="right"><?= _('Starttal') ?></th><th></th></tr>
<?PHP
		$rsTeams = $db->teamList($id, $dist);
		
		$clubstaken = array();
		$swimmerstaken = array();
		$cnt = 0;
		$scnt = 0;
		
		$clubstaken[$cnt++] = 0;
		$swimmerstaken[$scnt++] = 0;
		
		$startnumber = 0;
		
		while ($team = $db->fetch_array($rsTeams))
		{
			if ($startnumber == 0)
			{
				$startnumber = $team['time'];
			}
			
			echo('<tr><td colspan="3">' . $team['name'] . '</td><td align="right">' . number_format($team['time'], 2) . '</td><td align="right">' . floor($startnumber - $team['time']) . '</td><td align="right"><a href="teams.php?id=' . $id . '&delete=' . $team['tid'] . '"><span class="glyphicon glyphicon-remove"></span></a></td></tr>');
			
			$teamId = $team['tid'];
			$rsSwimmers = $db->teamSwimmers($teamId);
			
			while ($swimmer = $db->fetch_array($rsSwimmers))
			{
				$currentswimmer = $swimmer['swimmerId'];
				
				$rsRegularTime = $db->teamSwimmerList($id, $dist, $currentswimmer);
								
				$icon = 'time';
				$message = _('Klik for at korrigere tiden til stafetten');

				$best['result'] = 0;
				if ($reg = $db->fetch_array($rsRegularTime))
				{
					$best = $db->getSwimmerBestTime($id, $currentswimmer, $dist);

					if (!($best))
					{
						$best['result'] = 0;
					}
					elseif ($best['result'] != 0 && $swimmer['time'] != $best['result'])
					{
						$icon = 'ok-circle';
						$message = _('Denne svømmers tid er korrigeret. Klik for at korrigere tiden til stafetten');
					}
					elseif ($swimmer['time'] >= ($reg['time'] * 1.2))
					{
						$icon = 'record-circle';
						$message = _('Denne svømmer er 20%% eller mere langsommere end sin tilmeldte tid. Klik for at korrigere tiden til stafetten');
					}
				}
				
				echo('<tr><td width="16"></td><td width="16"><a href="javascript:fnCorrectTime(' . $teamId . ', ' . $currentswimmer . ', \'' . $swimmer['name'] . '\', ' . number_format($swimmer['time'], 2) . ');" title="' . $message .'"><span class="glyphicon glyphicon-' . $icon . '"></span></a></td><td>' . $swimmer['name'] . '</td><td width="80" align="right">' . ($best['result'] != 0 && $best['result'] != $swimmer['time'] ? '(' . number_format($best['result'], 2) . ') ' : '') . number_format($swimmer['time'], 2) . '</td><td width="80"></td><td width="18"></td></tr>');
				$swimmerstaken[$scnt++] = $swimmer['id'];
			}
			
			$clubstaken[$cnt++] = $team['clubId'];
		}
?>
						</table>

						<form method="post" class="form-horizontal">
							<legend><?= _('Nyt hold') ?></legend>
								<div class="form-group"><label for="clubId" class="col-lg-2 control-label"><?= _('For klub') ?>:</label><div class="col-lg-10"><select name="clubId" class="form-control">
<?PHP
		$rsClubs = $db->teamClubListExcept($id, $clubstaken);
		
		while ($club = $db->fetch_array($rsClubs))
		{
			echo('<option value="' . $club['id'] . '">' . $club['name'] . '</option>');
		}
?>					
						</select></div></div>
						
						<div class="form-group"><label for="swimmer1" class="col-lg-2 control-label"><?= _('Svømmer 1') ?>:</label><div class="col-lg-10"><select name="swimmer1" class="form-control">
<?PHP
		$rsSwimmers = $db->teamSwimmerListExcept($id, $dist, $swimmerstaken);
		
		while ($swimmer = $db->fetch_array($rsSwimmers))
		{
			echo('<option value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
		}
?>
						</select></div></div>

						<div class="form-group"><label for="swimmer2" class="col-lg-2 control-label"><?= _('Svømmer 2') ?>:</label><div class="col-lg-10"><select name="swimmer2" class="form-control">
<?PHP
		$rsSwimmers = $db->teamSwimmerListExcept($id, $dist, $swimmerstaken);
		
		$cnt = 0;
		while ($swimmer = $db->fetch_array($rsSwimmers))
		{
			$cnt++;
			
			if ($cnt == 2)
			{
				echo('<option selected="selected" value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
			}
			else
			{
				echo('<option value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
			}
		}
?>
						</select></div></div>

						<div class="form-group"><label for="swimmer3" class="col-lg-2 control-label"><?= _('Svømmer 3') ?>:</label><div class="col-lg-10"><select name="swimmer3" class="form-control">
<?PHP
		$rsSwimmers = $db->teamSwimmerListExcept($id, $dist, $swimmerstaken);
		
		$cnt = 0;
		while ($swimmer = $db->fetch_array($rsSwimmers))
		{
			$cnt++;
			
			if ($cnt == 3)
			{
				echo('<option selected="selected" value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
			}
			else
			{
				echo('<option value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
			}
		}
?>
						</select></div></div>

						<div class="form-group"><label for="swimmer4" class="col-lg-2 control-label"><?= _('Svømmer 4') ?>:</label><div class="col-lg-10"><select name="swimmer4" class="form-control">
<?PHP
		$rsSwimmers = $db->teamSwimmerListExcept($id, $dist, $swimmerstaken);
		
		$cnt = 0;
		while ($swimmer = $db->fetch_array($rsSwimmers))
		{
			$cnt++;
			
			if ($cnt == 4)
			{
				echo('<option selected="selected" value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
			}
			else
			{
				echo('<option value="' . $swimmer['sid'] . '">' . $swimmer['cname'] . ' - ' . $swimmer['sname'] . '</option>');
			}
		}
?>
						</select></div></div>
						<div class="form-actions">
							<input type="hidden" name="newteam" value="1" /><input type="submit" class="btn btn-primary" value="<?= _('Opret') ?>" />
						</div>
						</form>
	</div>
<script language="javascript" src="timefunctions.js"></script>
<script language="javascript">
function fnCorrectTime(teamId, swimmerId, name, time)
{
	var newtime = fnGetTime(prompt("<?= _('Korriger tid for') ?> " + name, time));
	if (newtime == null)
	{	
	}
	else
	{
		location.href = "teams.php?id=<?= $id ?>&newtimeteam=" + teamId + "&newtimeswimmer=" + swimmerId + "&time=" + newtime;
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