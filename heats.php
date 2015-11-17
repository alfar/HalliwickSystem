<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	function make_seed()
	{
	    list($usec, $sec) = explode(' ', microtime());
	    return (float) $sec + ((float) $usec * 100000);
	}
	srand(make_seed());

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

	if (isset($_GET['newtime']))
	{
		$swimmerId = (int)(0 . $_GET['newtime']);
		$db->updateCompetitionSwimmmerSemiTime($id, $swimmerId, $dist, $_GET['time']);
	}
	
	if (isset($_GET['itrack']))
	{
		$db->insertEmptyTrack($_GET['raceid'], $_GET['itrack']);
	}
	elseif (isset($_GET['rtrack']))
	{
		$db->removeEmptyTrack($_GET['raceid'], $_GET['rtrack']);
	}
	elseif (isset($_GET['resettracks']))
	{
		$db->resetTracks($_GET['resettracks']);
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
				
		if (isset($_GET['upgrade']))
		{
			$db->upgradeSemiFinal($id, $dist);

			$round = 8;
			$_SESSION["round" . $dist] = 8;
		}

		if (isset($_POST['dragSwimmer']))
		{
			$dragSwimmer = $_POST['dragSwimmer'];
			$dragFrom = $_POST['dragFrom'];
			$dragTo = $_POST['dragTo'];

			$db->moveSwimmerToHeat($dragSwimmer, $dragFrom, $dragTo);
		}
?>
<?php
	$activetab = 'heats';
	$subtitle = _('Heats');
	require('competitionhead.php');
?>
	<ul class="nav nav-tabs">
		<li<?= $dist == 25 ? ' class="active"' : '' ?>><a href="heats.php?id=<?= $id ?>&distance=25"><?= _('25m') ?></a></li>
		<li<?= $dist == 50 ? ' class="active"' : '' ?>><a href="heats.php?id=<?= $id ?>&distance=50"><?= _('50m') ?></a></li>
		<li<?= $dist == 100 ? ' class="active"' : '' ?>><a href="heats.php?id=<?= $id ?>&distance=100"><?= _('100m') ?></a></li>
	</ul>

	<div class="container" style="margin-top: 10px; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;">
		<div class="navbar navbar-default">
			<form name="roundpicker" class="navbar-form navbar-left" action="heats.php?id=<?= $id ?>&distance=<?= $dist ?>" method="post">
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
			<p class="navbar-text"><?= _('Antal svømmere') ?>: <?PHP
	$count = $db->countSwimmersForDistance($id, $dist);

	$heats = ceil($count / $trackCount);

	$actualHeats = $db->countFirstRoundHeatsForDistance($id, $dist);
	$semiEHeats = 1;
	$semiHeats = 1;
	switch($round)
	{
		case 0:
			$swimmers = $count;
			$result = $heats;
			$howmany = $trackCount;
			break;
		case 20:
			if (isset($_GET['howmany']))
			{
				$howmany = $_GET['howmany'];

				$db->updateHowMany($id, $dist, $round, $howmany);
			}
			else
			{
				$howmany = $aryCompo["howmanySemiExtra$dist"];
			}
			
			$res = $db->countSwimmers($id, $dist, $round, $howmany, isset($_GET['generate']));

			if ($res != null)
			{
				$swimmers = $res;
				$result = ceil($swimmers / $trackCount);
			}
			else
			{
				$swimmers = $howmany * $heats;
				$result = ceil($howmany * $heats / $trackCount);
			}
			break;
		case 4:
			if (isset($_GET['howmany']))
			{
				$howmany = $_GET['howmany'];

				$db->updateHowMany($id, $dist, $round, $howmany);
			}
			else
			{
				$howmany = $aryCompo["howmanySemi$dist"];
				
				if ($howmany == 0)
				{
					if ($actualHeats > 0)
					{
						$howmany = $howmany_semi[$trackCount][$actualHeats];
					}
					else
					{
						$howmany = 1;
					}
				}
			}

			$res = $db->countSwimmers($id, $dist, $round, $howmany, isset($_GET['generate']));

			if ($res != null)
			{
				$swimmers = $res;
				$result = ceil($swimmers / $trackCount);
			}
			else
			{
				$swimmers = $howmany * $heats;
				$result = ceil($howmany * $heats / $trackCount);
			}
			break;
		case 24:
			$heats = $db->countHeats($id, $dist, 20);

			if (isset($_GET['howmany']))
			{
				$howmany = $_GET['howmany'];

				$db->updateHowMany($id, $dist, $round, $howmany);
			}
			else
			{
				$howmany = $aryCompo["howmanyFinalExtra$dist"];
				
				if ($howmany == 0)
				{
					$semiEHeats = $heats;
					if ($semiEHeats > 0)
					{
						$howmany = $howmany_final[$trackCount][$semiEHeats];
					}
					else
					{
						$howmany = 1;
					}
				}
			}

			$res = $db->countSwimmers($id, $dist, $round, $howmany, isset($_GET['generate']));

			if ($res != null)
			{
				$swimmers = $res['result'];
				$result = ceil($swimmers / $trackCount);
			}
			else
			{
				$swimmers = $howmany * $heats;
				$result = ceil($howmany * $heats / $trackCount);
			}
			break;
		case 8:
			$heats = $db->countHeats($id, $dist, 4);
			
			if (isset($_GET['howmany']))
			{
				$howmany = $_GET['howmany'];

				$db->updateHowMany($id, $dist, $round, $howmany);
			}
			else
			{
				$howmany = $aryCompo["howmanyFinal$dist"];
				
				$semiHeats = $heats;
				if ($semiHeats > 0)
				{
					$howmany = $howmany_final[$trackCount][$heats];
				}
				else
				{
					$howmany = 1;
				}
			}

			$res = $db->countSwimmers($id, $dist, $round, $howmany, isset($_GET['generate']));

			if ($res != null)
			{
				$swimmers = $res['result'];
				$result = ceil($swimmers / $trackCount);
			}
			else
			{
				$swimmers = $howmany * $heats;
				$result = ceil($howmany * $heats / $trackCount);
			}
			break;
		case 5:
			if (isset($_GET['howmany']))
			{
				$howmany = $_GET['howmany'];

				$db->updateHowMany($id, $dist, $round, $howmany);
			}
			else
			{
				$howmany = $aryCompo["howmanyFinal$dist"];
				
				if ($howmany == 0)
				{
					if ($actualHeats > 0)
					{
						$howmany = $howmany_final[$trackCount][$actualHeats];
					}
					else
					{
						$howmany = 1;
					}
				}
			}

			$res = $db->countSwimmers($id, $dist, $round, $howmany, isset($_GET['generate']));

			if ($res != null)
			{
				$swimmers = $res['result'];
				$result = ceil($swimmers / $trackCount);
			}
			else
			{
				$swimmers = $howmany * $heats;
				$result = ceil($howmany * $heats / $trackCount);
			}
			break;
	}

	echo($swimmers);
?> - <?= _('Antal heats') ?>: <?PHP
	$origresult = $result;
	if (!isset($_GET['generate']))
	{
		$res = $db->countHeats($id, $dist, $round);
				
		if ($res > 0)
		{
			$result = $res;
		}

		if (isset($_GET['addheat']))
		{
			$result++;
			$db->newHeat($id, $dist, $round, '', $result);
		}
		
		if (isset($_GET['deleteheat']))
		{
			$result--;
			$db->deleteHeat($id, $dist, $round, $_GET['deleteheat']);
		}
		
		echo($result);
	}	
	else
	{
		echo($result);

		$heatswimmers = array();

		$heatswimcount = 0;
		$swimcount = 0;
		for ($hc = 0; $hc < $result; $hc++)
		{
			$swimcount += $swimmers;

			$heatswimcount = floor($swimcount / $result);
			$heatswimmers[$hc] = $heatswimcount;
			$swimcount -= $heatswimcount * $result;
		}

		$db->upgradeSwimmers($id, $dist, $round, $howmany);
		$rsClubs = $db->clubListForDistance($id, $dist, $round, $howmany);

		$perclub = array();
		$clubcount = 0;
		$clubids = array();
		$clubnames = array();
		$clubs = array();
		$usedswimmers = array();
		while ($aryClubs = $db->fetch_array($rsClubs))
		{
			$clubids[$clubcount] = $aryClubs['id'];
			$clubs[$clubcount] = 0;
			$usedswimmers[$clubcount] = "0";
			$clubnames[$clubcount++] = $aryClubs['name'];
			$perclub[$aryClubs['name']] = $aryClubs['cnt'];
		}

		$db->clearRaces($id, $dist, $round);

		$currentRaceId = $db->newHeat($id, $dist, $round, '', 1);

		$currentheat = 0;
		$heatswimcount = 1;
		for ($sc = 0; $sc < $swimmers; $sc++)
		{
			for ($cc = 0; $cc < $clubcount; $cc++)
			{
				$clubs[$cc] += $perclub[$clubnames[$cc]];
				if ($clubs[$cc] >= $swimmers)
				{
					if ($heatswimcount > $heatswimmers[$currentheat])
					{
						$heatswimcount -= $heatswimmers[$currentheat];
						$currentheat++;

						$currentRaceId = $db->newHeat($id, $dist, $round, '', $currentheat + 1);
					}

					$grabSwimmer = $db->getRandomSwimmer($id, $dist, $round, $howmany, $clubids[$cc], $usedswimmers[$cc]);

					$clubs[$cc] -= $swimmers;
					$db->addRaceSwimmer($currentRaceId, $grabSwimmer['id']);

					$usedswimmers[$cc] .= ', ' . $grabSwimmer['id'];
					$heatswimcount++;
				}
			}
		}
	}
?>
</p>
	<ul class="nav navbar-nav navbar-left">
		<li><a href="heats.php?id=<?= $id ?>&addheat=1" title="<?= _('Tilføj heat') ?>"><span class="glyphicon glyphicon-plus"></span></a></li>
	</ul>
	<ul class="nav navbar-nav navbar-right">
		<li><a target="_blank" href="printheats.php?id=<?= $id ?>"><span class="glyphicon glyphicon-print"></span> <?= _('Udskriv heats') ?></a></li>
	</ul>
</div>

<?PHP
	$rsHeats = $db->raceList($id, $dist, $round);

	$heatCount = 0;

	while($aryHeats = $db->fetch_array($rsHeats))
	{
		$heatCount++;
		$startTime = 0;

		if ($heatCount == 1)
		{
			if ($actualHeats == 0 && $round > 0)
			{
?>
	<div class="alert alert-danger><?= _('Kan ikke generere videregående heats før indledende heats er genereret.') ?></div>
<?PHP
			}
			else
			{
?>
	<a id="generatelink" href="heats.php?id=<?= $id ?>&generate=1&howmany=<?= $howmany ?>"><?= _('Gendan heats') ?></a><?PHP
				if ($round > 0)
				{
					echo(' ' . _('med') . ' <select onchange="document.getElementById(\'generatelink\').href = \'heats.php?id=' . $id .'&generate=1&howmany=\' + this.options[this.selectedIndex].value;"><option value="1"' . ($howmany == 1 ? ' selected="selected"' : '') . '>' . _('den bedste') . '</option><option value="2"' . ($howmany == 2 ? ' selected="selected"' : '') . '>' . _('de to bedste') . '</option><option value="3"' . ($howmany == 3 ? ' selected="selected"' : '') . '>' . _('de tre bedste') . '</option></select> ' . _('fra hvert heat') . '.');
				}
	
				if (($round == 4 || $round == 20) && $result == 1)
				{
					echo('<br /><a href="heats.php?id=' . $id . '&upgrade=1">' . _('Overfør heat til finale') . '</a>');
				}
			}
		}

		if ($heatCount % 2 == 1)
		{
?>
										<div class="row">
<?PHP
		}
?>
	<div class="col-lg-6">
	<table class="table table-striped table-bordered" onselectstart="event.returnValue = false; return false;" onmousedown="fnDragSwimmer(<?= $aryHeats['id'] ?>)" onmouseup="fnDropSwimmer(<?= $aryHeats['id'] ?>)">
<?PHP
			$raceId = $aryHeats['id'];

			$rsSwimmers = $db->raceSwimmersOrdered($raceId, $round);

      $track = 0;
      $tracks = '';
			$swimmerStarts = 0;
			$warning = false;
			
			while ($arySwimmers = $db->fetch_array($rsSwimmers))
			{
				$track++;

				while(isset($aryTracks[$track - 1]) && $aryTracks[$track - 1] == 0)
				{
					if ($track > $number_of_tracks)
					{
						break;
					}
					
					$tracks .= '<tr><td colspan="5">---</td></tr>';
					$track++;
				} 
				if ($arySwimmers['track'] == 0 || $arySwimmers['track'] < $track)
				{
					$db->setTrack($raceId, $arySwimmers['swimmerId'], $track);
				}
				else
				{
					while($track < $arySwimmers['track'])
					{
						$tracks .= '<tr><td colspan="4" class="emptytrack">' . _('(Tom)') . '</td><td><a href="?id=' . $id . '&raceid=' . $raceId . '&rtrack=' . $track . '" title="' . _('Fjern tom bane') . '"><span class="glyphicon glyphicon-chevron-right"></span></a></td></tr>';
						$track++;
					}
				}

				if ($track > $trackCount)
				{
				    $warning = true;
				    $warningtext = _('For mange baner i brug!');
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

				$problem = '';
				
				$lastSwimmerStarts = $swimmerStarts;
				if ($round == 0)
				{
					$swimmerTime = $arySwimmers['time'];
				}
				elseif ($round == 4 || $round == 20)
				{
					$tempTime = $arySwimmers['time'];
					$swimmerTime = $arySwimmers['semitime'];
					
					if ($swimmerTime > $tempTime * 1.20)
					{
						if ($arySwimmers['semitimechecked'] == 0)
						{
							$problem = '<a href="javascript:fnCorrectTime(' . $arySwimmers['swimmerId'] . ', \'' . $arySwimmers['swimmername'] . '\', ' . $arySwimmers['semitime'] . ');"><span class="glyphicon glyphicon-dashboard" title="' . _('Denne svømmer har svømmet væsentligt langsommere end forventet. Korriger evt. tilmeldt tid til semifinalen.') . '"></span></a> ';
						}
					}
					if ($arySwimmers['semitimechecked'] == 1)
					{
						$problem = '<a href="javascript:fnCorrectTime(' . $arySwimmers['swimmerId'] . ', \'' . $arySwimmers['swimmername'] . '\', ' . $arySwimmers['semitime'] . ');"><span class="glyphicon glyphicon-ok-circle" title="' . _('Denne svømmers tid er blevet korrigeret, klik for korrigere igen.') . '"></span></a> ';
					}
				}
				elseif ($round == 5 || $round == 8 || $round == 24)
				{
					$swimmerTime = $arySwimmers['finaltime'];
				}
				
				$swimmerStarts = round($startTime - $swimmerTime);

				$db->updateStartTime($raceId, $arySwimmers['swimmerId'], $swimmerStarts);
				if (($swimmerStarts - $lastSwimmerStarts) == 1)
				{
					$problem .= '<span class="glyphicon glyphicon-time" title="' . _('Denne svømmers starttal ligger for tæt på en andens.') . '"></span> ';
					$warning = true;
					$warningtext = _('En svømmers starttal ligger for tæt på en andens.');
				}
				if ($arySwimmers['edge'] == 1 && $track != 1 && $track != $number_of_tracks)
				{
			    $problem .= '<span class="glyphicon glyphicon-download-alt" title="' . _('Denne svømmer skal svømme ved kant') . '"></span> ';
			    $warning = true;
					$warningtext = _('En svømmer skal svømme ved kant');
				}

        $tracks .= '<tr><td class="swimmer_' . $arySwimmers['swimmerId'] . '">' . $problem . $arySwimmers['swimmername'] . '</td><td>' . $arySwimmers['clubname'] . '</td><td>' . $swimmerTime . '</td><td>' . $swimmerStarts . '</td><td><a href="?id=' . $id . '&raceid=' . $raceId . '&itrack=' . $track . '" title="' . _('Indsæt tom bane') . '"><span class="glyphicon glyphicon-chevron-left"></span></a></td></tr>';
			}

            if (isset($warning) && $warning)
            {
				echo('		<tr class="danger" title="' . $warningtext . '"><th>' . _('Heat nr.') . ' ' . $aryHeats['number'] . '</th><th>' . _('Klub') . '</th><th>' . _('Tid') . '</th><th>' . _('Starttal') . '</th><th><a href="?id=' . $id . '&resettracks=' . $raceId . '" title="' . _('Nulstil baner') . '"><span class="glyphicon glyphicon-eject"></span></a></th></tr>');
            }
            elseif ($tracks == '')
            {
				echo('		<tr><th>' . _('Heat nr.') . ' ' . $aryHeats['number'] . '</th><th colspan="4" align="right"><a href="heats.php?id=' . $id . '&deleteheat=' . $aryHeats['number'] . '" title="' . _('Slet dette heat') . '"><span class="glyphicon glyphicon-remove"></span></a></th></tr>');
	        }
	        else
            {
				echo('		<tr><th>' . _('Heat nr.') . ' ' . $aryHeats['number'] . '</th><th>' . _('Klub') . '</th><th>' . _('Tid') . '</th><th>' . _('Starttal') . '</th><th><a href="?id=' . $id . '&resettracks=' . $raceId . '" title="' . _('Nulstil baner') . '"><span class="glyphicon glyphicon-eject"></span></a></th></tr>');
            }
			echo($tracks);
?>
	</table>
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

	if ($heatCount == 0)
	{
		if ($actualHeats == 0 && ($round == 4 || $round == 20))
		{
?>
	<div class="alert alert-danger"><?= _('Kan ikke generere semifinaler før indledende heats er genereret.') ?></div>
<?PHP
		}
		elseif ($semiHeats == 0 && ($round == 8 || $round == 5))
		{
?>
	<div class="alert alert-danger"><?= _('Kan ikke generere finalen før semifinaleheats er genereret.') ?></div>
<?PHP
		}
		elseif ($semiEHeats == 0 && $round == 24)
		{
?>
	<div class="alert alert-danger"><?= _('Kan ikke generere ekstra-finalen før ekstra semifinaleheats er genereret.') ?></div>
<?PHP
		}
		else
		{
?>
<a id="generatelink" href="heats.php?id=<?= $id ?>&generate=1&howmany=1"><?= _('Dan heats') ?></a><?PHP
			if ($round > 0)
			{
				echo(' ' . _('med') . ' <select onchange="document.getElementById(\'generatelink\').href = \'heats.php?id=' . $id .'&generate=1&howmany=\' + this.options[this.selectedIndex].value;"><option value="1">' . _('den bedste') . '</option><option value="2">' . _('de to bedste') . '</option><option value="3">' . _('de tre bedste') . '</option></select> ' . _('fra hvert heat') . '.');
			}
		}
?>
<?PHP
	}
?>

	<table style="display: inline;" width="400" onselectstart='event.returnValue = false;' onmousedown="fnDragSwimmer(0)" cellpadding="2" cellspacing="0">
<?PHP
			$tracks = '';
			$raceId = $aryHeats['id'];

			$rsSwimmers = $db->noRaceSwimmers($id, $dist);

			$swimmerStarts = 0;
			while ($arySwimmers = $db->fetch_array($rsSwimmers))
			{
      	$tracks .= '<tr><td class="swimmer_' . $arySwimmers['swimmerId'] . '">' . $arySwimmers['swimmername'] . '</td><td>' . $arySwimmers['clubname'] . '</td><td>' . $arySwimmers['time'] . '</td></tr>';
			}
			
			echo($tracks);
?>
			</table>
	<form name="drag" action="heats.php?id=<?= $id ?>" method="post">
		<input type="hidden" name="dragSwimmer" />
		<input type="hidden" name="dragFrom" />
		<input type="hidden" name="dragTo" />
	</form>
	
</div>
<script language="javascript">
var dragSwimmer = 0;
var dragFrom = 0;

$(function() {
	document.body.onmouseup = fnCancelDrag;
});

function fnCorrectTime(id, name, time)
{
	var newtime = prompt("<?= _('Korriger tid for') ?> " + name, time);
	if (newtime == null)
	{	
	}
	else
	{
		location.href = "heats.php?id=<?= $id ?>&newtime=" + id + "&time=" + newtime;
	}
}

function fnDragSwimmer(heat)
{
	dragSwimmer = 0;
	dragFrom = 0;

	var cls = event.srcElement.className;

	if (cls != '')
	{
		var data = cls.split('_');

		if (data.length == 2)
		{
			if (data[0] == 'swimmer')
			{
				dragFrom = heat;
				dragSwimmer = data[1];
				document.body.style.cursor = 'move';
			}
		}
	}
}

function fnDropSwimmer(heat)
{
	if (dragSwimmer != 0 && dragFrom != heat)
	{
		document.forms['drag'].dragSwimmer.value = dragSwimmer;
		document.forms['drag'].dragFrom.value = dragFrom;
		document.forms['drag'].dragTo.value = heat;
		document.forms['drag'].submit();
	}
	event.cancelBubble = true;
}

function fnCancelDrag()
{
	document.body.style.cursor = '';
	dragSwimmer = 0;
	dragFrom = 0;
}
</script>
<?php
	require('competitionfoot.php');
?>
<?PHP
	}

	$db->close();
?>