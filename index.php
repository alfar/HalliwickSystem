<?PHP
	require('config.php');
	
	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();
?>
<?php
	require('head.php');
?>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#"><?= _('Stævneoversigt') ?></a></li>
				<li><a href="signups.php"><?= _('Tilmelding') ?></a></li>
			</ul>
			<table class="table table-striped table-bordered">
				<tr><th><?= _('Stævne') ?></th><th><?= _('Dato') ?></th></tr>
				<tr><td><a href="newcompetition.php"><?= _('Nyt stævne') ?></a></td><td></td></tr>
<?PHP
	$rsCompos = $db->competitionList();
	
	$row = 1;
	while ($aryCompo = $db->fetch_array($rsCompos))
	{
?>
				<tr><td><a href="competitiondata.php?id=<?= $aryCompo['id'] ?>"><?= $aryCompo['name'] ?></a></td><td><?= $aryCompo['date'] ?></td></tr>
<?PHP
		$row++;
	}
	
	$db->close();
?>
			</table>
<?php
	require('foot.php');
?>