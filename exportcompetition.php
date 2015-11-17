<?php
	require('config.php');
	$db = new $dbclass($dbserver, $username, $password, $dbname);
	$db->connect();

//	header('Content-disposition: attachment; filename=tilmelding.txt');

	$id = (int)(0 . $_GET['id']);

	$lastSwimmer = 0;
	
	if ($aryCompo = $db->getCompetition($id))
	{
		echo("COMPO/1.0\n");
		echo('c|' . $aryCompo['tracks'] . '|' . $aryCompo['name'] . '|' . strftime('%d-%m-%Y', strtotime($aryCompo['date'])) . '|' . $aryCompo['leader'] . '|' . $aryCompo['extra100'] . "\n");

		$rsSwimmers = $db->swimmerList($id);
		while ($arySwimmer = $db->fetch_array($rsSwimmers))
		{
			if ($arySwimmer['swimmerId'] != $lastSwimmer)
			{			
				$lastSwimmer = $arySwimmer['swimmerId'];
?>
S|<?= $arySwimmer['swimmername'] ?>|<?= $arySwimmer['clubname'] ?>|<?= $arySwimmer['edge'] ?>|<?= $arySwimmer['pilot'] ?>|<?= $arySwimmer['diet'] ?>|<?= $arySwimmer['semitime'] ?>|<?= $arySwimmer['semitimechecked'] ?>|<?= $arySwimmer['finalTime'] ?>|
D|<?= $arySwimmer['distance'] ?>|<?= $arySwimmer['distance'] > 0 ? number_format($arySwimmer['time'], 2) : '' ?>|<?= $arySwimmer['devices'] ?>|
<?php
				}
			else
			{
?>
D|<?= $arySwimmer['distance'] ?>|<?= $arySwimmer['distance'] > 0 ? number_format($arySwimmer['time'], 2) : '' ?>|<?= $arySwimmer['devices'] ?>|
<?PHP
			}
		}		
	}	

	$db->close();
?>