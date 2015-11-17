<?PHP
	require('config.php');

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

	function sortbyposition($a, $b)
	{
		if ($a['Position'] == $b['Position'])
		{
			return 0;
		}

		$ap = $a['Position'];
		$bp = $b['Position'];

		if ($ap < 0)
		{
			$ap = 100 - $ap;
		}

		if ($bp < 0)
		{
			$bp = 100 - $bp;
		}

//		echo($ap . ', ' . $bp . '<br />');

		return ($ap < $bp) ? -1 : 1;
	}

	include('class.pdf.php');
	include('class.ezpdf.php');

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

		$rsHeats = $db->raceList($id, $dist, $round);

		$pdf = new Cezpdf();
		$pdf->selectFont('./fonts/Helvetica');

		switch ($round)
		{
			case 0:
				$roundname = _('Indledende runder');
				break;
			case 4:
				$roundname = _('Semifinaler');
				break;
			case 5:
			case 8:
				$roundname = _('Finale');
				break;
			case 20:
				$roundname = _('Semifinaler ekstraløb');
				break;
			case 24:
				$roundname = _('Finale ekstraløb');
				break;
		}

		$heatno = 0;
		while ($aryHeats = $db->fetch_array($rsHeats))
		{
			if ($heatno % 3 == 0)
			{
				if ($heatno > 0)
				{
					$pdf->ezNewPage();
				}
				$pdf->ezSetY(830);
				$pdf->ezText($aryCompo['name'] . " - " . date('d-m-Y', strtotime($aryCompo['date'])), 18, array('justification' => 'center'));

				$pdf->ezText(_('Resultater') . ' ' . $roundname . ' ' . gettext_dist($dist), 16, array('justification' => 'center'));
			}

			$pdf->ezText('', 16, array('justification' => 'center'));
			$heatno++;
			$raceId = $aryHeats['id'];
			$rsSwimmers = $db->raceSwimmers($raceId); // mysql_query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '$raceId' order by rs.startTime"); // position / abs(position) desc, abs(position) asc");

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
				
				while($lane < $arySwimmers['track'])
				{
//					$aryData[] = array('Bane' => $lane, 'Navn' => '(Tom)', 'Klub' => '', 'Tid' => '', 'Position' => '', 'Placering' => '');
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

				$aryData[] = array('Bane' => $lane, 'Navn' => $arySwimmers['swimmername'], 'Klub' => $arySwimmers['clubname'], 'Tid' => $arySwimmers['position'] == 999 ? 'Udg.' : ($arySwimmers['result1'] + $arySwimmers['result2'] > 0) ? number_format(($arySwimmers['result1'] + $arySwimmers['result2']) / 2, 2) : '', 'Starttal' => $arySwimmers['startTime'], 'Position' => $arySwimmers['position'], 'Placering' => $arySwimmers['position'] == 999 ? 'Udg.' : ($arySwimmers['position'] > 0 ? $arySwimmers['position'] : ($arySwimmers['position'] == 0 ? '' : ((-$arySwimmers['position']) . 'X'))));
				$lane++;
			}

			usort($aryData, 'sortbyposition');

			$pdf->ezTable($aryData, array('Bane'=>_('Bane'), 'Navn'=>_('Navn'), 'Klub'=>_('Klub'), 'Starttal'=>_('Start'), 'Tid'=>_('Tid'), 'Placering'=>_('Plac.')), _('Heat') . ' ' . $heatno, array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481, 'fontSize' => 16, 'titleFontSize' => 18, 'cols' => array('Bane' => array('width' => 54, 'justification' => 'center'), 'Tid' => array('justification' => 'right', 'width' => 60), 'Starttal' => array('justification' => 'right', 'width' => 50), 'Placering' => array('justification' => 'right', 'width' => 55)) ) );
		}

		header("Content-type: application/pdf");

		$pdf->ezStream();
	}
	
	$db->close();
?>