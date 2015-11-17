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

		$rsHeats = $db->raceList($id, $dist, $round);

		$pdf = new Cezpdf();
		$pdf->selectFont('./fonts/Helvetica');

		switch ($round)
		{
			case 0:
				$roundname = _('Indledende runder');
				break;
			case 4:
				$roundname = _('Semifinale');
				break;
			case 5:
			case 8:
				$roundname = _('Finale');
				break;
			case 20:
				$roundname = _('Semifinale ekstraløb');
				break;
			case 24:
				$roundname = _('Finale ekstraløb');
				break;
		}

		$heatno = 0;
		while ($aryHeats = $db->fetch_array($rsHeats))
		{
			$helps = array();
			$helpcounter = 0;
			
			if ($heatno % 3 == 0)
			{
				if ($heatno > 0)
				{
					$pdf->ezNewPage();
				}
				$pdf->ezSetY(830);
				$pdf->ezText($aryCompo['name'] . " - " . date('d-m-Y', strtotime($aryCompo['date'])), 18, array('justification' => 'center'));

				$pdf->ezText($roundname . ' ' . gettext_dist($dist), 16, array('justification' => 'center'));
			}

			$pdf->ezText('', 16, array('justification' => 'center'));
			$heatno++;
			$raceId = $aryHeats['id'];
			$rsSwimmers = $db->raceSwimmers($raceId);

			$aryData = array();
			$lane = 1;

			$startTime = 0;

			while ($arySwimmers = $db->fetch_array($rsSwimmers))
			{
				while(isset($aryTracks[$lane - 1]) && $aryTracks[$lane - 1] == 0)
				{
					if ($lane > $number_of_tracks)
					{
						break;
					}

					$lane++;
				} 

				if ($round == 0)
				{
					$swimmerTime = $arySwimmers['time'];
				}
				elseif ($round == 4 || $round == 20)
				{
					$swimmerTime = $arySwimmers['semitime'];
				}
				else
				{
					$swimmerTime = $arySwimmers['finaltime'];
				}

				if ($startTime == 0)
				{
					$startTime = $swimmerTime;
				}

				while($lane < $arySwimmers['track'])
				{
					$aryData[] = array('Bane' => $lane, 'Navn' => _('(Tom)'), 'Klub' => '', 'Tilmeldt tid' => '', 'Tilladt tid' => '', 'Starttal' => '');
					$lane++;
				}
				
				$help = '';
				if ($arySwimmers['help'] != "")
				{
					$helpcounter++;
					$help = " " . $helpcounter . ")";
					$helps[] = array('Nummer' => $helpcounter, 'Note' => $arySwimmers['help']);
				}

				$aryData[] = array('Bane' => $lane, 'Navn' => $arySwimmers['swimmername'] . $help, 'Klub' => $arySwimmers['clubname'], 'Tilmeldt tid' => $swimmerTime, 'Tilladt tid' => number_format($swimmerTime * 0.93,2), 'Starttal' => round($startTime - $swimmerTime));
				$lane++;
			}

			$pdf->ezTable($aryData, array('Bane'=>'', 'Navn'=>_('Navn'), 'Klub'=>_('Klub'), 'Tilmeldt tid'=>_('Tilmeldt'), 'Tilladt tid'=>_('Tilladt'), 'Starttal'=>_('Start')), _('Heat') . ' ' . $heatno, array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481, 'fontSize' => 14, 'titleFontSize' => 18, 'cols' => array('Bane' => array('width' => 30, 'justification' => 'center'), 'Tilmeldt tid' => array('justification' => 'right', 'width' => 68), 'Tilladt tid' => array('justification' => 'right', 'width' => 68), 'Starttal' => array('justification' => 'right', 'width' => 45)) ) );
			
			if ($helpcounter > 0)
			{
				$pdf->ezTable($helps, array('Nummer'=> '', 'Note'=>''), '', array('showHeadings' => 0, 'showLines' => 0, 'shaded' => 0, 'xPos' => 57, 'xOrientation' => 'right', 'width' => 481, 'fontSize' => 10, 'titleFontSize' => 12, 'cols' => array('Nummer' => array('width' => 30, 'justification' => 'center'))));
			}
		}

		header("Content-type: application/pdf");

		$pdf->ezStream();
	}

	$db->close();
?>