<?PHP
	require('config.php');
	
	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();
	
	$db->setupSignup();
?>
<?php
	require('head.php');
?>
			<ul class="nav nav-tabs">
				<li><a href="index.php"><?= _('Stævneoversigt') ?></a></li>
				<li class="active"><a href="signups.php"><?= _('Tilmelding') ?></a></li>
			</ul>
				<table class="table table-striped table-bordered">
					<tr><th><?= _('Stævne') ?></th><th><?= _('Dato') ?></th></tr>
					<tr><td class="data0"><a href="newsignup.php"><?= _('Ny tilmelding') ?></a></td><td class="data0"></td></tr>
<?PHP
	$rsCompos = $db->signupList();
	
	$row = 1;
	while ($aryCompo = $db->fetch_array($rsCompos))
	{
?>
					<tr><td class="data<?= $row % 2 ?>"><a href="signupdata.php?id=<?= $aryCompo['id'] ?>"><?= $aryCompo['name'] ?></a></td><td class="data<?= $row % 2 ?>"><?= $aryCompo['date'] ?></td></tr>
<?PHP
		$row++;
	}
	
	$db->close();
?>
				</table>
<?php
	require('foot.php');
?>