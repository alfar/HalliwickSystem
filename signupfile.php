<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();
	
	header('Content-disposition: attachment; filename=tilmelding.txt');
?>
SIGNUP/1.0
<?PHP
	$id = (int)(0 . $_GET['id']);

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
S|<?= $arySwimmer['swimmername'] ?>|<?= $arySwimmer['clubname'] ?>|<?= $arySwimmer['edge'] ?>|<?= $arySwimmer['pilot'] ?>|<?= $arySwimmer['diet'] ?>|
D|<?= $arySwimmer['distance'] ?>|<?= $arySwimmer['distance'] > 0 ? number_format($arySwimmer['time'], 2) : '' ?>|<?= $arySwimmer['devices'] ?>|
<?PHP
		}
		else
		{
?>
D|<?= $arySwimmer['distance'] ?>|<?= $arySwimmer['distance'] > 0 ? number_format($arySwimmer['time'], 2) : '' ?>|<?= $arySwimmer['devices'] ?>|
<?PHP
		}
		$row++;
	}
	
	$db->close();
?>