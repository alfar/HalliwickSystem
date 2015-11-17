<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();
?>
<?php
	$activetab = 'swimmers';
	$subtitle = _('Tilmelding svømmere');
	require('signuphead.php');
?>
<?PHP
	$id = (int)(0 . $_GET['id']);

	if (isset($_GET['clearSwimmer']))
	{
		$swimmerId = (int)(0 . $_GET['clearSwimmer']);
		$db->deleteSignupSwimmer($id, $swimmerId);
	}

	if (isset($_GET['swimmerId']))
	{
		$swimmerId = (int)(0 . $_GET['swimmerId']);
	}
	else
	{
		$swimmerId = 0;
	}

	$clubId = -1;	
	if ($aryCompo = $db->getSignup($id))
	{
		if ($swimmerId != 0)
		{
			$command = 'update';
			$submitname = _('Opdater');
			
			if ($arySwimmer = $db->getSwimmer($swimmerId))
			{
				$swimmername = $arySwimmer['name'];
				$clubId = $arySwimmer['clubId'];
				$edge = $arySwimmer['edge'];
				$pilot = $arySwimmer['pilot'];
				$diet = $arySwimmer['diet'];
			}

			$rsTimes = $db->signupSwimmerTimes($id, $swimmerId);
			
			$time25 = '';
			$time50 = '';
			$time100 = '';
			
			$team25 = 0;
			$team50 = 0;
			
			$help25 = '';
			$help50 = '';
			$help100 = '';
			
			$helpt25 = '';
			$helpt50 = '';
			
			while ($aryTime = $db->fetch_array($rsTimes))
			{
				if ($aryTime['distance'] == 25)
				{
					$time25 = $aryTime['time'];
					$help25 = $aryTime['help'];
				}
				if ($aryTime['distance'] == 50)
				{
					$time50 = $aryTime['time'];
					$help50 = $aryTime['help'];
				}
				if ($aryTime['distance'] == 100)
				{
					$time100 = $aryTime['time'];
					$help100 = $aryTime['help'];
				}
				if ($aryTime['distance'] == -25)
				{
					$team25 = 1;
					$helpt25 = $aryTime['help'];
				}
				if ($aryTime['distance'] == -50)
				{
					$team50 = 1;
					$helpt50 = $aryTime['help'];
				}				
			}
		}
		else
		{
			$command = 'create';
			$submitname = _('Tilføj');
			if (isset($_POST['command']))
			{
				if ($_POST['command'] == 'update')
				{
					if (isset($_POST['swimmername']))
					{
						if ($_POST['clubId'] == 0)
						{
							// Opret ny klub
							$clubId = $db->newClub($_POST['clubName']);
						}
						else
						{
							$clubId = $_POST['clubId'];
						}
		
						if (isset($_POST['edge']) && $_POST['edge'] == 'on')
						{
							$edge = 1;
						}
						else
						{
							$edge = 0;
						}
		
						if (isset($_POST['pilot']) && $_POST['pilot'] == 'on')
						{
							$pilot = 1;
						}
						else
						{
							$pilot = 0;
						}
	
						$db->updateSwimmer($_POST['updateswimmerId'], $_POST['swimmername'], $edge, $pilot, $_POST['diet'], $clubId);
		
						$db->clearSignupSwimmerDistances($id, $_POST['updateswimmerId']);
		
						if ($_POST['time25'] != '')
						{
							$db->addSignupSwimmerDistance($id, $_POST['updateswimmerId'], 25, $_POST['time25'], $_POST['help25']);
						}
						if ($_POST['time50'] != '')
						{
							$db->addSignupSwimmerDistance($id, $_POST['updateswimmerId'], 50, $_POST['time50'], $_POST['help50']);
						}
						if ($_POST['time100'] != '')
						{
							$db->addSignupSwimmerDistance($id, $_POST['updateswimmerId'], 100, $_POST['time100'], $_POST['help100']);
						}
						if (isset($_POST['team25']) && $_POST['team25'] != '')
						{
							$db->addSignupSwimmerDistance($id, $_POST['updateswimmerId'], -25, 0.00, $_POST['helpt25']);
						}
						if (isset($_POST['team50']) && $_POST['team50'] != '')
						{
							$db->addSignupSwimmerDistance($id, $_POST['updateswimmerId'], -50, 0.00, $_POST['helpt50']);
						}
					}
				}				
				elseif (isset($_POST['swimmerId']))
				{
					if ($_POST['swimmerId'] == 0)
					{
						if ($_POST['clubId'] == 0)
						{
							// Opret ny klub
							$clubId = $db->newClub($_POST['clubName']);
						}
						else
						{
							$clubId = $_POST['clubId'];
						}
		
						if (isset($_POST['edge']) && $_POST['edge'] == 'on')
						{
							$edge = 1;
						}
						else
						{
							$edge = 0;
						}
		
						if (isset($_POST['pilot']) && $_POST['pilot'] == 'on')
						{
							$pilot = 1;
						}
						else
						{
							$pilot = 0;
						}
						
						// Opret ny svømmer
						$swimmerId = $db->newSwimmer($_POST['swimmername'], $clubId, $edge, $pilot, $_POST['diet']);
					}
					else
					{
						$swimmerId = $_POST['swimmerId'];
					}
		
					if ($_POST['time25'] != '')
					{
						$db->addSignupSwimmerDistance($id, $swimmerId, 25, $_POST['time25'], $_POST['help25']);
					}
					if ($_POST['time50'] != '')
					{
						$db->addSignupSwimmerDistance($id, $swimmerId, 50, $_POST['time50'], $_POST['help50']);
					}
					if ($_POST['time100'] != '')
					{
						$db->addSignupSwimmerDistance($id, $swimmerId, 100, $_POST['time100'], $_POST['help100']);
					}
					if (isset($_POST['team25']) && $_POST['team25'] != '')
					{
						$db->addSignupSwimmerDistance($id, $swimmerId, -25, 0.00, $_POST['helpt25']);
					}
					if (isset($_POST['team50']) && $_POST['team50'] != '')
					{
						$db->addSignupSwimmerDistance($id, $swimmerId, -50, 0.00, $_POST['helpt50']);
					}
				}
			}
			$swimmername = '';					
			$edge = 0;
			$pilot = 0;
			$diet = '';
			$time25 = '';
			$time50 = '';
			$time100 = '';
			$team25 = 0;
			$team50 = 0;
			$help25 = '';
			$help50 = '';
			$help100 = '';
			$helpt25 = '';
			$helpt50 = '';
		}
?>
	<div class="container" style="margin-top: 10px;">

	<nav class="navbar navbar-default">
		<ul class="nav navbar-nav navbar-right">
			<li><a href="signupfile.php?id=<?= $id ?>" target="main"><span class="glyphicon glyphicon-export"></span> <?= _('Generér fil') ?></a></li>
			<li><a href="printsignup.php?id=<?= $id ?>" target="_blank"><span class="glyphicon glyphicon-print"></span> <?= _('Udskriv tilmeldinger') ?></a></li>
		</ul>
	</nav>
	
							<table class="table table-striped">
								<tr><th><?= _('Navn') ?></th><th><?= _('Klub') ?></th><th><?= _('Distance') ?></th><th><?= _('Tid') ?></th><th></th><th></th></tr>
<?PHP
	$rsSwimmers = $db->signupSwimmerList($id);

	$swimmerIds = '0';
	
	$lastSwimmer = 0;
	
	$row = 0;
	while ($arySwimmer = $db->fetch_array($rsSwimmers))
	{
		if ($arySwimmer['swimmerId'] != $lastSwimmer)
		{			
			$lastSwimmer = $arySwimmer['swimmerId'];
			$swimmerIds .= ',' . $arySwimmer['swimmerId'];
?>
				<tr class="data<?= $row % 2 ?>"><td><?= $arySwimmer['swimmername'] ?></td><td><?= $arySwimmer['clubname'] ?></td><td><?= $arySwimmer['distance'] > 0 ? $arySwimmer['distance'] : ((-$arySwimmer['distance']) . ' stafet') ?></td><td><?= $arySwimmer['distance'] > 0 ? number_format($arySwimmer['time'], 2) : '' ?></td><td align="right">
<?PHP
			if ($arySwimmer['edge'] > 0)
			{
				echo(' <span class="glyphicon glyphicon-download-alt" title="' . _('Ved kant') . '"></span>');
			}
			if ($arySwimmer['pilot'] > 0)
			{
				echo(' <span class="glyphicon glyphicon-plane" title="' . _('Pilot') . '"></span>');
			}
			if ($arySwimmer['diet'] != '')
			{
				echo(' <span class="glyphicon glyphicon-cutlery" title="' . $arySwimmer['diet'] . '"></span>');
			}
			if ($arySwimmer['devices'] != '')
			{
				echo(' <span class="glyphicon glyphicon-briefcase" title="' . $arySwimmer['devices'] . '"></span>');
			}
?></td><td align="right"><a href="signupswimmers.php?id=<?= $id ?>&swimmerId=<?= $arySwimmer['swimmerId'] ?>" title="<?= _('Rediger svømmer') ?>"><span class="glyphicon glyphicon-pencil"></span></a><a href="javascript:if (confirm('<?= _('Er du sikker på at du vil lade denne svømmer udgå?') ?>')) { location.href = 'signupswimmers.php?id=<?= $id ?>&clearSwimmer=<?= $arySwimmer['swimmerId'] ?>'; }" title="<?= _('Frameld svømmer') ?>"><span class="glyphicon glyphicon-remove"></span></a></td></tr>
<?PHP
		}
		else
		{
?>
				<tr class="data<?= $row % 2 ?>"><td></td><td></td><td><?= $arySwimmer['distance'] > 0 ? $arySwimmer['distance'] : ((-$arySwimmer['distance']) . ' stafet') ?></td><td><?= $arySwimmer['distance'] > 0 ? number_format($arySwimmer['time'], 2) : '' ?></td><td align="right">
<?PHP
			if ($arySwimmer['devices'] != '')
			{
				echo(' <span class="glyphicon glyphicon-briefcase" title="' . $arySwimmer['devices'] . '"></span>');
			}
?></td><td align="right"><img src="images/clear.gif" height="16" width="16" /></td></tr>
<?PHP
		}
		$row++;
	}
?>	
						</table>
						<form method="post" style="display: inline" name="swimmer" action="signupswimmers.php?id=<?= $id ?>">
							<input type="hidden" name="command" value="<?= $command ?>" />
							<input type="hidden" name="updateswimmerId" value="<?= $swimmerId ?>" />
							<table width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="50%" valign="top">
										<table width="100%" cellpadding="0" cellspacing="2">
											<tr id="swimmeridrow">
												<td><?= _('Vælg svømmer') ?>:</td>
												<td colspan="2">
													<select name="swimmerId" onchange="fnUpdateForm();" style="width: 250px;">
														<option value="0"><?= _('Opret ny') ?></option>
<?PHP
	$rsNewSwimmers = $db->allSwimmersExcept($swimmerIds);	

	while ($arySwimmer = $db->fetch_array($rsNewSwimmers))
	{
?>
														<option value="<?= $arySwimmer['swimmerId'] ?>"><?= $arySwimmer['clubname'] . ' - ' . $arySwimmer['swimmername'] ?></option>
<?PHP
}
?>
													</select>
												</td>
											</tr>
											<tr><td><?= _('Navn') ?>:</td><td colspan="2"><input type="text" name="swimmername" onkeyup="fnFindSwimmer()" value="<?= $swimmername ?>" style="width: 250px;" /></td></tr>
											<tr><td></td><td><?= _('Tid') ?></td><td><?= _('Hjælpemidler') ?></td></tr>
											<tr><td><?= _('25 meter') ?>:</td><td><input type="text" name="time25" value="<?= $time25 ?>" onchange="fnFixTime(this)" style="width: 50px;" /></td><td><input type="text" name="help25" value="<?= $help25 ?>" style="width: 200px;" /></td></tr>
											<tr><td><?= _('50 meter') ?>:</td><td><input type="text" name="time50" value="<?= $time50 ?>" onchange="fnFixTime(this)" style="width: 50px;" /></td><td><input type="text" name="help50" value="<?= $help50 ?>" style="width: 200px;" /></td></tr>
											<tr><td><?= _('100 meter') ?>:</td><td><input type="text" name="time100" value="<?= $time100 ?>" onchange="fnFixTime(this)" style="width: 50px;" /></td><td><input type="text" name="help100" value="<?= $help100 ?>" style="width: 200px;" /></td></tr>
											<tr><td><?= _('25 meter stafet') ?>:</td><td><input type="checkbox" name="team25"<?= $team25 == 1 ? ' checked="checked"' : '' ?> /></td><td><input type="text" name="helpt25" value="<?= $helpt25 ?>" style="width: 200px;" /></td></tr>
											<tr><td><?= _('50 meter stafet') ?>:</td><td><input type="checkbox" name="team50"<?= $team50 == 1 ? ' checked="checked"' : '' ?> /></td><td><input type="text" name="helpt50" value="<?= $helpt50 ?>" style="width: 200px;" /></td></tr>
										</table>
									</td>
									<td width="50%" valign="top">
										<table width="100%" cellpadding="0" cellspacing="2">
											<tr id="clubidrow">
												<td><?= _('Klub') ?>:</td>
												<td>
													<select name="clubId" onchange="fnUpdateForm();" style="width: 250px;">
<?PHP
	$rsClub = $db->clubList();
	
	while ($aryClub = $db->fetch_array($rsClub))
	{
?>
														<option value="<?= $aryClub['id'] ?>"<?PHP if ($aryClub['id'] == $clubId) { echo('selected="selected"'); } ?>><?= $aryClub['name'] ?></option>
<?PHP
	}
?>
														<option value="0"><?= _('Opret ny') ?></option>
													</select>
												</td>
											</tr>
											<tr id="clubnamerow"><td><?= _('Klubnavn') ?>:</td><td><input type="text" name="clubName" style="width: 250px;" /></td></tr>
											<tr id="edgerow"><td colspan="2"><input type="checkbox" name="edge" <?PHP if ($edge == 1) { echo('checked="checked" '); } ?>/> <?= _('Ved kant') ?></td></tr>
											<tr id="pilotrow"><td colspan="2"><input type="checkbox" name="pilot" <?PHP if ($pilot == 1) { echo('checked="checked" '); } ?> /> <?= _('Pilot') ?></td></tr>
											<tr id="dietrow"><td><?= _('Diæt') ?>:</td><td><input type="text" name="diet" value="<?= $diet ?>" style="width: 250px;" /></td></tr>					
										</table>
									</td>
								</tr>
								<tr><td colspan="2" align="right"><input type="submit" class="btn btn-primary" value="<?= $submitname ?>" /></td></tr>
							</table>
						</form>
<?PHP		
	}
	
	$db->close();
?>
<script language="javascript" src="timefunctions.js"></script>
<script language="javascript">
function fnFindSwimmer()
{
	var strSwimmer = document.forms['swimmer'].swimmername.value.toLowerCase();

	var selSwimmer = document.forms['swimmer'].swimmerId;

	if (event.keyCode == 9)
	{
		if (selSwimmer.selectedIndex > 0)
		{
			if (!event.shiftKey)
			{
				document.forms['swimmer'].time25.focus();
			}
		}
		return false;
	}
		
	var blnFound = false;
	if (strSwimmer != '')
	{
		for (ic = 1; ic < selSwimmer.options.length; ic++)
		{
			var strCurrentSwimmer = selSwimmer.options[ic].text.toLowerCase();
			
			var aryNames = strCurrentSwimmer.split(' - ');
			if (aryNames[1] && aryNames[1].indexOf(strSwimmer) == 0)
			{
				selSwimmer.selectedIndex = ic;
				blnFound = true;
				break;
			}
		}
	}
		
	if (!blnFound)
	{
		selSwimmer.selectedIndex = 0;
	}
	fnUpdateForm();
}

function fnFixTime(inp)
{
	time = fnGetTime(inp.value);
	
	if (time != null)
	{
		inp.value = time;
	}
	else
	{
		inp.value = '';
	}
}

function fnUpdateForm()
{
	var selSwimmer = document.forms['swimmer'].swimmerId;
	var hdnCommand = document.forms['swimmer'].command;
	
	if (selSwimmer.selectedIndex != 0 && hdnCommand.value != 'update')
	{
		document.getElementById('clubidrow').style.display = 'none';
		document.getElementById('edgerow').style.display = 'none';
		document.getElementById('pilotrow').style.display = 'none';
		document.getElementById('dietrow').style.display = 'none';
		document.getElementById('clubnamerow').style.display = 'none';
	}
	else
	{
		if (hdnCommand.value == 'update')
		{
			document.getElementById('swimmeridrow').style.display = 'none';
		}
		document.getElementById('clubidrow').style.display = 'block';
		document.getElementById('edgerow').style.display = 'block';
		document.getElementById('pilotrow').style.display = 'block';
		document.getElementById('dietrow').style.display = 'block';

		var selClub = document.forms['swimmer'].clubId;
		
		if (selClub.selectedIndex == selClub.options.length - 1)
		{
			document.getElementById('clubnamerow').style.display = 'block';
		}
		else
		{
			document.getElementById('clubnamerow').style.display = 'none';
		}
	}
}
</script>
<?php
	require('signupfoot.php');
?>