<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

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
?>
<?php
	$activetab = 'diplomas';
	$subtitle = _('Diplomer');
	require('competitionhead.php');
?>
				<form action="printdiplomas.php?id=<?= $id ?>" method="post" target="_blank">
	<nav class="navbar navbar-default">
<?PHP
	if (count($diplomaTypes) > 1)
	{
?>
		<div class="navbar-form">
			<div class="form-group">
				<label for="diplomaType"><?= _('Diplomtype') ?>:</label>
										<select name="diplomaType">
<?PHP
		foreach($diplomaTypes as $diploma => $diplomaName)
		{
			echo('<option value="' . $diploma . '">' . $diplomaName . '</option>');
		}
?>
										</select>
			</div>
		</div>
<?PHP
	}
	else
	{
		foreach($diplomaTypes as $diploma => $diplomaName)
		{
			echo('<input type="hidden" name="diplomaType" value="' . $diploma . '" />');
		}
	}
?>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="javascript:void(0);" onclick="$(this).closest('form').submit();"><span class="glyphicon glyphicon-print"></span> <?= _('Udskriv diplomer') ?></a></li>
			</ul>
	</nav>

			<input name="id" type="hidden" value="<?= $id ?>" />
			<ul class="list-unstyled">
				<li><label for="all"><input type="checkbox" name="all" onclick="fnCheckAll('');" /> <?= _('Alle') ?></label></li>
				<ul class="list-unstyled" style="margin-left: 20px;">
<?PHP
	$rsSwimmers = $db->swimmerList($id); // mysql_query("select s.id as sid, c.id as cid, s.name as sname, c.name as cname, s.*, c.* from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblClub c on c.id = s.clubId where cs.competitionId = '$id' order by c.name, s.name");

	$currentclub = -1;
	$currentswimmer = -1;

	while ($swimmer = $db->fetch_array($rsSwimmers))
	{
		if ($currentclub != $swimmer['clubId'])
		{
			if ($currentclub != -1)
			{
				echo('</ul>');
			}
			$currentclub = $swimmer['clubId'];
			echo('<li><label for="club_' . $currentclub . '"><input type="checkbox" name="club_' . $currentclub . '" onclick="fnCheckAll(' . $currentclub . ');"  /> ' . $swimmer['clubname'] . '</li><ul class="list-unstyled" style="margin-left: 20px;">');
		}

		if ($currentswimmer != $swimmer['swimmerId'])
		{
			$currentswimmer = $swimmer['swimmerId'];
			echo('<li><label for="swimmer_' . $currentswimmer . '"><input type="checkbox" club="' . $currentclub . '" name="swimmer_' . $currentswimmer . '" /> ' . $swimmer['swimmername'] . '</li>');
		}
	}
?>
				</ul>
				<li><label for="teamall25"><input type="checkbox" name="teamall25" onclick="fnCheckAll('team25');" /> <?= _('25m Stafet') ?></label></li>
				<ul class="list-unstyled" style="margin-left: 20px;">
<?PHP
	$rsTeams = $db->teamList($id, 25);

	while ($team = $db->fetch_array($rsTeams))
	{
		echo('<li><label for="team_' . $team['id'] . '"><input type="checkbox" name="team_' . $team['id'] . '" club="team25" /> ' . $team['cname'] . '</li>');
	}
?>
			</ul>
			<li><label for="teamall50"><input type="checkbox" name="teamall50" onclick="fnCheckAll('team50');" /> <?= _('50m Stafet') ?></label></li>
			<ul class="list-unstyled" style="margin-left: 20px;">
<?PHP
	$rsTeams = $db->teamList($id, 50);

	while ($team = $db->fetch_array($rsTeams))
	{
		echo('<li><label for="team50_' . $team['id'] . '"><input type="checkbox" name="team_' . $team['id'] . '" club="team50" /> ' . $team['cname'] . '</label></li>');
	}
?>
		</ul>
				</form>
<script>
function fnCheckAll(clubId)
{
	var checks = document.getElementsByTagName('INPUT');

	for (cnt = 0; cnt < checks.length; cnt++)
	{
		if (clubId == '' || checks[cnt].getAttribute('club') == clubId)
		{
			checks[cnt].checked = event.srcElement.checked;
		}
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