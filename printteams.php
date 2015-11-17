<?PHP
	require('config.php');
	include('class.pdf.php');
	include('class.ezpdf.php');

	function gettext_dist($dist)
	{
		if ($dist == 25) {
			return _('25m');
		} elseif ($dist == 50) {
			return _('50m');			
		} else {
			return _('100m');
		}
	}

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

	if ($dist == 0)
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

	if ($aryCompo = $db->getCompetition($id))
	{
//		$rsHeats = mysql_query("select * from tblRace where competitionId = '$id' and distance = '$dist' and type = '$round' order by number");
		
		$pdf = new Cezpdf();
		$pdf->selectFont('./fonts/Helvetica');

		$roundname = _('Stafet');
		
		$pdf->ezSetY(830);
		$pdf->ezText($aryCompo['name'] . " - " . date('d-m-Y', strtotime($aryCompo['date'])), 18, array('justification' => 'center'));

		$pdf->ezText($roundname . ' ' . gettext_dist($dist), 16, array('justification' => 'center'));		

		$pdf->ezText('', 16, array('justification' => 'center'));

		$rsSwimmers = $db->teamList($id, $dist);

		$aryData = array();
		$lane = 1;
		
		$startTime = 0;
		
		while ($arySwimmers = $db->fetch_array($rsSwimmers))
		{
			if ($startTime == 0)
			{
				$startTime = $arySwimmers['time'];
			}
			
			$aryData[] = array('Bane' => $lane, 'Klub' => $arySwimmers['cname'], 'Starttal' => round($startTime - $arySwimmers['time']));

			$teamSwimmers = $db->teamSwimmers($arySwimmers['id']);
			
			while ($swimmer = $db->fetch_array($teamSwimmers))
			{
				$aryData[] = array('Bane' => '', 'Klub' => '- ' . $swimmer['name'], 'Starttal' => '');
			}
			$lane++;
		}

		$pdf->ezTable($aryData, array('Bane'=>_('Bane'), 'Klub'=>_('Hold'), 'Starttal'=>_('Starttal')), '', array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481, 'fontSize' => 16, 'titleFontSize' => 18, 'cols' => array('Bane' => array('width' => 54, 'justification' => 'center'), 'Starttal' => array('justification' => 'right', 'width' => 68)) ) );
		
		header("Content-type: application/pdf");

		$pdf->ezStream();
	}
	
	$db->close();
?>