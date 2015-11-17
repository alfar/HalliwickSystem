<?PHP
	ini_set('display_errors', 1);

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

	function gettext_position($p) {
		if ($p == 1)
		{									
			return _('1. plads');
		}
		elseif ($p == 2)
		{
			return _('2. plads');
		}
		elseif ($p == 3)
		{
			return _('3. plads');
		}
		else
		{
			return '';
		}		
	}	

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	session_start();

	$id = (int)(0 . $_GET['id']);

	if ($aryCompo = $db->getCompetition($id))
	{
		$componame = $aryCompo['name'];
		$compodate = $aryCompo['date'];

		$swimmerList = array();
		$count = 0;

		$swimmerList[$count++] = 0;
		
		$teamList[] = 0;

		foreach ($_POST as $key => $value)
		{
			if (strstr($key, 'swimmer_'))
			{
				$parts = explode('_', $key);
				$sid = $parts[1];

				$swimmerList[$count++] = $sid;
			}
			if (strstr($key, 'team_'))
			{
				$parts = explode('_', $key);
				$tid = $parts[1];

				$teamList[] = $tid;
			}
			if (strstr($key, 'team50_'))
			{
				$parts = explode('_', $key);
				$tid = $parts[1];

				$teamList[] = $tid;
			}
		}
		
		$diplomaType = $_POST['diplomaType'];

		//echo("select * from tblSwimmer where id in (" . implode(',', $swimmerList) . ")");
		$rsSwimmers = $db->diplomaSwimmers($id, $swimmerList);

		$pdf = new Cezpdf();
		$pdf->selectFont('./fonts/Times-Roman');

		if ($diplomaType == 'HASI')
		{
			$logo = $pdf->openObject();
			$pdf->ezSetY(640);
			$pdf->ezSetMargins(0, 0, 200, 200);
			$pdf->ezImage('diploma/logo.jpg', 0, 195, 'none', 'left');
			$pdf->closeObject();
	
			$border = $pdf->openObject();
			$pdf->ezSetY(803.4);
			$pdf->ezSetMargins(0, 0, 23.8, 0);
			$pdf->ezImage('diploma/border.jpg', 0, 547.4, 'none', 'left');
			$pdf->closeObject();
	
			$pdf->ezSetMargins(0, 0, 30, 30);
	
			$firstPage = true;
	
			$swimmername = '';
			$clubname = '';
			$date = date('d-m-Y', strtotime($aryCompo['date']));
	
			$currentswimmer = -1;
			while ($swimmer = $db->fetch_array($rsSwimmers))
			{
				if ($swimmer['sid'] != $currentswimmer)
				{
					if ($swimmername != '')
					{
						if (!$firstPage)
						{
							$pdf->ezNewPage();
						}
	
						$firstPage = false;
	
						$pdf->addObject($border);
	
						$pdf->addObject($logo);
	
						$text = $pdf->openObject();
	
						$pdf->ezSetY(820);
	
						$pdf->ezText(_('Diplom'), 80, array('justification' => 'center'));
	
						$pdf->ezText('', 280, array('justification' => 'center'));
	
						$pdf->ezText($swimmername, 40, array('justification' => 'center'));
			//			$pdf->ezText($clubname, 16, array('justification' => 'center'));
	
						$pdf->ezText('', 20, array('justification' => 'center'));
	
						$pdf->ezText(_('har deltaget i'), 20, array('justification' => 'center'));
	
						$pdf->ezText('', 20, array('justification' => 'center'));
	
						$pdf->ezText($aryCompo['name'] . ' - ' . $date, 30, array('justification' => 'center'));
	
						$pdf->ezText('', 20, array('justification' => 'center'));
	
						$pdf->ezText(_('med følgende resultat:'), 20, array('justification' => 'center'));
	
						$pdf->ezText('', 20, array('justification' => 'center'));
	
						foreach($results as $r)
						{
							$pdf->ezText($r['Resultat'], 20, array('justification' => 'center'));
						}
	
						$pdf->ezSetMargins(0, 0, 28.3, 75);
	
						$pdf->ezSetY(80);
	
						$pdf->setLineStyle(1);
	
						$pdf->line(300, 80, 530, 80);
	
						$pdf->ezText(_('Stævneleder') . ' ' . $aryCompo['leader'], 15, array('justification' => 'right'));
	
						$pdf->closeObject();
	
						$pdf->addObject($text);
					}
	
					$swimmername = $swimmer['sname'];
					$clubname = $swimmer['cname'];
					$results = array();
					$count = 0;
					$currentswimmer = $swimmer['sid'];
				}
	
				if ($swimmer['distance'] == 25)
				{
					if ($result = $db->getSwimmerBestTime($id, $currentswimmer, 25))
					{
						if ($result['result'] > 0)
						{
							$min = floor($result['result'] / 60);
	
							$sek = floor($result['result']) % 60;
	
							$hun = floor($result['result'] * 100) % 100;
		
							$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
	
							$rsResult = $db->diplomaSwimmerPosition($id, $currentswimmer, 25);
	
							$p = '';
	
							if ($place = $db->fetch_array($rsResult))
							{
								$p = gettext_position($place['position']);
									
								if ($p != '')
								{
									if ($place['type'] == 8)
									{
										$p .= ' ' . _('finale');
									}
									else
									{
										$p .= ' ' . _('finale ekstra');
									}
								}
							}
							if ($p != '')
							{
								$p .= ', ';
							}
	
							$results[$count++] = array('Resultat' => _('25m') . ': ' . $p . $res);
						}
					}
				}
				elseif ($swimmer['distance'] == 50)
				{
					if ($result = $db->getSwimmerBestTime($id, $currentswimmer, 50))
					{
						if ($result['result'] > 0)
						{
							$min = floor($result['result'] / 60);
	
							$sek = floor($result['result']) % 60;
	
							$hun = floor($result['result'] * 100) % 100;
		
							$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
	
							$rsResult = $db->diplomaSwimmerPosition($id, $currentswimmer, 50);
	
							$p = '';
	
							if ($place = $db->fetch_array($rsResult))
							{
								$p = gettext_position($place['position']);
									
								if ($p != '')
								{
									if ($place['type'] == 8)
									{
										$p .= ' ' . _('finale');
									}
									else
									{
										$p .= ' ' . _('finale ekstra');
									}
								}
							}
							if ($p != '')
							{
								$p .= ', ';
							}
	
							$results[$count++] = array('Resultat' => _('50m') . ': ' . $p . $res);
						}
					}
				}
				elseif ($swimmer['distance'] == 100)
				{
					if ($result = $db->getSwimmerBestTime($id, $currentswimmer, 100))
					{
						if ($result['result'] > 0)
						{
							$min = floor($result['result'] / 60);
	
							$sek = floor($result['result']) % 60;
	
							$hun = floor($result['result'] * 100) % 100;
		
							$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
	
							$rsResult = $db->diplomaSwimmerPosition($id, $currentswimmer, 100);
	
							$p = '';
	
							if ($place = $db->fetch_array($rsResult))
							{
								$p = gettext_position($place['position']);
									
								if ($p != '')
								{
									if ($place['type'] == 8)
									{
										$p .= ' ' . _('finale');
									}
									else
									{
										$p .= ' ' . _('finale ekstra');
									}
								}
							}
							if ($p != '')
							{
								$p .= ', ';
							}
	
							$results[$count++] = array('Resultat' => _('100m') . ': ' . $p . $res);
						}
					}
	
				}
			}
	
			if ($swimmername != '')
			{
				$pdf->ezSetMargins(0, 0, 75, 75);
	
				if (!$firstPage)
				{
					$pdf->ezNewPage();
				}
	
				$firstPage = false;
	
				$pdf->addObject($border);
	
				$pdf->addObject($logo);
	
				$text = $pdf->openObject();
	
				$pdf->ezSetY(820);
	
				$pdf->ezText(_('Diplom'), 80, array('justification' => 'center'));
	
				$pdf->ezText('', 280, array('justification' => 'center'));
	
				$pdf->ezText($swimmername, 40, array('justification' => 'center'));
	//			$pdf->ezText($clubname, 16, array('justification' => 'center'));
	
				$pdf->ezText('', 20, array('justification' => 'center'));
	
				$pdf->ezText(_('har deltaget i'), 20, array('justification' => 'center'));
	
				$pdf->ezText('', 20, array('justification' => 'center'));
	
				$pdf->ezText($aryCompo['name'] . ' - ' . $date, 30, array('justification' => 'center'));
	
				$pdf->ezText('', 20, array('justification' => 'center'));
	
				$pdf->ezText(_('med følgende resultat:'), 20, array('justification' => 'center'));
	
				$pdf->ezText('', 20, array('justification' => 'center'));
	
				foreach($results as $r)
				{
					$pdf->ezText($r['Resultat'], 20, array('justification' => 'center'));
				}
	
				$pdf->ezSetY(80);
	
				$pdf->setLineStyle(1);
	
				$pdf->line(300, 80, 530, 80);
				$pdf->ezText(_('Stævneleder') . ' ' . $aryCompo['leader'], 15, array('justification' => 'right'));
	
				$pdf->closeObject();
	
				$pdf->addObject($text);
			}
	
			$rsTeams = $db->diplomaTeams($teamList);
	
			while ($team = $db->fetch_array($rsTeams))
			{
				for ($page = 0; $page < 4; $page++)
				{
					$pdf->ezSetMargins(0, 0, 75, 75);
	
					if (!$firstPage)
					{
						$pdf->ezNewPage();
					}
	
					$firstPage = false;
	
					$pdf->addObject($border);
	
					$pdf->addObject($logo);
	
					$text = $pdf->openObject();
	
					$pdf->ezSetY(820);
	
					$pdf->ezText(_('Diplom'), 80, array('justification' => 'center'));
	
					$pdf->ezText('', 260, array('justification' => 'center'));
	
					if ($team['place'] <= 3)
					{
						$pdf->ezText(gettext_position($team['place']), 45, array('justification' => 'center'));
						$pdf->ezText('', 20, array('justification' => 'center'));
					}
	
					$rsSwimmers = $db->diplomaTeamSwimmers($team['tid']);
	
					while($swimmer = $db->fetch_array($rsSwimmers))
					{
						$pdf->ezText($swimmer['name'], 30, array('justification' => 'center'));
					}
	
					$pdf->ezText('', 20, array('justification' => 'center'));
	
					$pdf->ezText(_("har deltaget i") . " " . gettext_dist($team['distance']) . " " . _("stafet ved stævnet"), 20, array('justification' => 'center'));
	
					$pdf->ezText('', 20, array('justification' => 'center'));
	
					$pdf->ezText($aryCompo['name'] . ' - ' . $date, 30, array('justification' => 'center'));
	
					$pdf->ezText('', 20, array('justification' => 'center'));
	
					$pdf->ezSetY(80);
	
					$pdf->setLineStyle(1);
	
					$pdf->line(300, 80, 530, 80);
					$pdf->ezText(_('Stævneleder') . ' ' . $aryCompo['leader'], 15, array('justification' => 'right'));
	
					$pdf->closeObject();
	
					$pdf->addObject($text);
				}
			}
		}
		elseif ($diplomaType == 'Sælungerne')
		{
			$pdf->ezSetMargins(0, 0, 30, 30);
	
			$firstPage = true;
	
			$swimmername = '';
			$clubname = '';
			$date = date('d-m-Y', strtotime($aryCompo['date']));
	
			$currentswimmer = -1;
			while ($swimmer = $db->fetch_array($rsSwimmers))
			{
				if ($swimmer['sid'] != $currentswimmer)
				{
					if ($swimmername != '')
					{
						if (!$firstPage)
						{
							$pdf->ezNewPage();
						}
	
						$firstPage = false;
	
						$text = $pdf->openObject();
	
						$pdf->ezSetY(420);

						$pdf->ezText($swimmername, 40, array('justification' => 'center'));

						$pdf->ezSetY(470);

						$pdf->ezSetMargins(0, 0, 200, 30);

						$pdf->ezText($date, 12, array('justification' => 'left'));

						$pdf->ezSetY(230);

						$pdf->ezSetMargins(0, 0, 30, 30);
		
						foreach($results as $r)
						{
							$pdf->ezText($r['Resultat'], 12, array('justification' => 'center'));
							$pdf->ezText('', 18, array('justification' => 'center'));
						}

						$pdf->closeObject();
	
						$pdf->addObject($text);
					}
	
					$swimmername = $swimmer['sname'];
					$clubname = $swimmer['cname'];
					$results = array();
					$count = 0;
					$currentswimmer = $swimmer['sid'];
				}
				if ($swimmer['distance'] == 25)
				{
					if ($result = $db->getSwimmerBestTime($id, $currentswimmer, 25))
					{
						if ($result['result'] > 0)
						{
							$min = floor($result['result'] / 60);
	
							$sek = floor($result['result']) % 60;
	
							$hun = floor($result['result'] * 100) % 100;
	
							$res = '';
							$secs = '';
	
							if ($min > 0)
							{
								$res .= $min . ' min.';
							}
	
							if ($sek > 0)
							{
								if ($res != '')
								{
									$res .= ' ';
								}
								$secs .= $sek;
							}
	
							if ($hun > 0)
							{
								if ($secs == '')
								{
									$secs = '0';
								}
	
								$secs .= ',' . $hun;
							}
	
							if ($secs != '')
							{
								$res .= $secs . ' sek.';
							}
	
							$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
	
							$rsResult = $db->diplomaSwimmerPosition($id, $currentswimmer, 25);
	
							$p = '';
	
							if ($place = $db->fetch_array($rsResult))
							{
								$p = $place['position'];
	
								if ($p > 0 && $p <= 3)
								{
									$p .= '. plads ';
	
									if ($place['type'] == 8)
									{
										$p .= 'finale, ';
									}
									else
									{
										$p .= 'finale ekstra, ';
									}
								}
								else
								{
									$p = '';
								}
							}
	
							$results[$count++] = array('Resultat' => '25m: ' . $p . $res);
						}
					}
				}
				elseif ($swimmer['distance'] == 50)
				{
					if ($result = $db->getSwimmerBestTime($id, $currentswimmer, 50))
					{
						if ($result['result'] > 0)
						{
							$min = floor($result['result'] / 60);
	
							$sek = floor($result['result']) % 60;
	
							$hun = floor($result['result'] * 100) % 100;
	
							$res = '';
							$secs = '';
	
							if ($min > 0)
							{
								$res .= $min . ' min.';
							}
	
							if ($sek > 0)
							{
								if ($res != '')
								{
									$res .= ' ';
								}
								$secs .= $sek;
							}
	
							if ($hun > 0)
							{
								if ($secs == '')
								{
									$secs = '0';
								}
	
								$secs .= '.' . $hun;
							}
	
							if ($secs == '')
							{
								$res .= $secs;
							}
	
							$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
	
							$rsResult = $db->diplomaSwimmerPosition($id, $currentswimmer, 50);
	
							$p = '';
	
							if ($place = $db->fetch_array($rsResult))
							{
								$p = $place['position'];
	
								if ($p > 0 && $p <= 3)
								{
									$p .= '. plads ';
	
									if ($place['type'] == 8)
									{
										$p .= 'finale, ';
									}
									else
									{
										$p .= 'finale ekstra, ';
									}
								}
								else
								{
									$p = '';
								}
							}
	
							$results[$count++] = array('Resultat' => '50m: ' . $p . $res);
						}
					}
				}
				elseif ($swimmer['distance'] == 100)
				{
					if ($result = $db->getSwimmerBestTime($id, $currentswimmer, 100))
					{
						if ($result['result'] > 0)
						{
							$min = floor($result['result'] / 60);
	
							$sek = floor($result['result']) % 60;
	
							$hun = floor($result['result'] * 100) % 100;
	
							$res = '';
							$secs = '';
	
							if ($min > 0)
							{
								$res .= $min . ' min.';
							}
	
							if ($sek > 0)
							{
								if ($res != '')
								{
									$res .= ' ';
								}
								$secs .= $sek;
							}
	
							if ($hun > 0)
							{
								if ($secs == '')
								{
									$secs = '0';
								}
	
								$secs .= '.' . $hun;
							}
	
							if ($secs == '')
							{
								$res .= $secs;
							}
	
							$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
	
							$rsResult = $db->diplomaSwimmerPosition($id, $currentswimmer, 100);
	
							$p = '';
	
							if ($place = $db->fetch_array($rsResult))
							{
								$p = $place['position'];
	
								if ($p > 0 && $p <= 3)
								{
									$p .= '. plads finale, ';
								}
								else
								{
									$p = '';
								}
							}
	
							$results[$count++] = array('Resultat' => '100m: ' . $p . $res);
						}
					}
				}
			}

			if ($swimmername != '')
			{
				if (!$firstPage)
				{
					$pdf->ezNewPage();
				}

				$firstPage = false;

				$text = $pdf->openObject();

				$pdf->ezSetY(420);

				$pdf->ezText($swimmername, 40, array('justification' => 'center'));

				$pdf->ezSetY(470);

				$pdf->ezSetMargins(0, 0, 200, 30);

				$pdf->ezText($date, 12, array('justification' => 'left'));

				$pdf->ezSetY(230);

				$pdf->ezSetMargins(0, 0, 30, 30);

				foreach($results as $r)
				{
					$pdf->ezText($r['Resultat'], 12, array('justification' => 'center'));
					$pdf->ezText('', 18, array('justification' => 'center'));
				}

				$pdf->closeObject();

				$pdf->addObject($text);
			}			

			$rsTeams = $db->diplomaTeams($teamList);
	
			while ($team = $db->fetch_array($rsTeams))
			{
				for ($page = 0; $page < 4; $page++)
				{
					$pdf->ezSetMargins(0, 0, 75, 75);
	
					if (!$firstPage)
					{
						$pdf->ezNewPage();
					}
	
					$firstPage = false;

					$text = $pdf->openObject();

					$pdf->ezSetY(470);

					$pdf->ezSetMargins(0, 0, 200, 30);

					$pdf->ezText($date, 12, array('justification' => 'left'));

					$pdf->ezSetMargins(0, 0, 75, 75);

					$pdf->ezSetY(420);

					$pdf->ezText($team['place'] . '. plads', 40, array('justification' => 'center'));
					$pdf->ezText('', 20, array('justification' => 'center'));

					$pdf->ezSetY(250);

					$pdf->ezSetMargins(0, 0, 30, 30);

					$rsSwimmers = $db->diplomaTeamSwimmers($team['tid']);
	
					while($swimmer = $db->fetch_array($rsSwimmers))
					{
						$pdf->ezText($swimmer['name'], 20, array('justification' => 'center'));
					}

					$pdf->ezText("i " . $team['distance'] . "m stafet", 14, array('justification' => 'center'));

					$pdf->closeObject();
	
					$pdf->addObject($text);
				}
			}
		}
		//header("Content-type: application/pdf");
		$pdfout = $pdf->ezOutput();

		$name = time();

		$fp = fopen('dump/' . $name . '.pdf', 'wb');
		fwrite($fp, $pdfout);
		fclose($fp);
		
		header("Location: dump/$name.pdf");
	}
	else
	{
		echo('ged');
	}
	
	$db->close();
?>