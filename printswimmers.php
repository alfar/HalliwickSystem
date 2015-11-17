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

	$id = (int)(0 . $_REQUEST['id']);

	$swimmer = 0;	
	if ($aryCompo = $db->getCompetition($id))
	{
		$rsSwimmers = $db->swimmerList($id);

		$note = -1;		
		$aryNotes = array();
		$aryData = array();
		$cnt = 0;
		while ($arySwimmer = $db->fetch_array($rsSwimmers))
		{
			$test = "" . $arySwimmer['swimmerId'];
			if ($swimmer != $test)
			{
				$hasnote = FALSE;
				$swimmer = $test; // $arySwimmer['id'];
				if (isset($aryRow) && is_array($aryRow))
				{
					$aryData[$cnt] = $aryRow;
					$cnt++;
				}
				$aryRow = array();
				//$aryRow['Nr'] = $arySwimmer['id'];
				$aryRow['Navn'] = $arySwimmer['swimmername'];
				$aryRow['Klub'] = $arySwimmer['clubname'];
				$aryRow['25'] = '';
				$aryRow['50'] = '';
				$aryRow['100'] = '';
				
				if ($arySwimmer['edge'] == 1)
				{
					$note++;
					$aryRow['note'] = ($note + 1) . ')';
					$hasnote = TRUE;
					$aryNotes[$note] = array('no'=> ($note +1) . ')', 'text'=>_('Ved kant'));
				}

				if ($arySwimmer['Pilot'] != '')
				{
					if ($hasnote === FALSE)
					{
						$note++;
						$aryRow['note'] = ($note + 1) . ')';
						$hasnote = TRUE;
						$aryNotes[$note] = array('no'=> ($note +1) . ')', 'text'=>_('Pilot'));
					}
					else
					{
						$aryNotes[$note]['text'] .= "\n" . _('Pilot');
					}
				}				

				if ($arySwimmer['diet'] != '')
				{
					if ($hasnote === FALSE)
					{
						$note++;
						$aryRow['note'] = ($note + 1) . ')';
						$hasnote = TRUE;
						$aryNotes[$note] = array('no'=> ($note +1) . ')', 'text'=>_('Diæt') . ': ' . $arySwimmer['diet']);
					}
					else
					{
						$aryNotes[$note]['text'] .= "\n" . _('Diæt') . ': ' . $arySwimmer['diet'];
					}
				}

			}
			
			if ($arySwimmer['devices'] != '')
			{
				if ($hasnote === FALSE)
				{
					$note++;
					$aryRow['note'] = ($note + 1) . ')';
					$hasnote = TRUE;
					$aryNotes[$note] = array('no'=> ($note +1) . ')', 'text'=>_('Hjælpemidler') . ' ' . $arySwimmer['distance'] . ': ' . $arySwimmer['devices']);
				}
				else
				{
					$aryNotes[$note]['text'] .= "\n" .  _('Hjælpemidler') . ' ' . gettext_dist($arySwimmer['distance']) . ': ' . $arySwimmer['devices'];
				}
			}
			
			$aryRow[$arySwimmer['distance']] = $arySwimmer['time'];
		}
		if (is_array($aryRow))
		{
			$aryData[$cnt] = $aryRow;
		}
		
		$pdf = new Cezpdf();
		$pdf->selectFont('./fonts/Helvetica');

		$pdf->ezSetY(830);
		$pdf->ezText(_("Deltagerliste") . " " . $aryCompo['name'] . " - " . date('d-m-Y', strtotime($aryCompo['date'])), 18, array('justification' => 'center'));
		$pdf->ezSetY(781);
		$pdf->ezTable($aryData, array('Navn'=>_('Navn'), 'Klub'=>_('Klub'), '25'=>_('25m'), '50'=>_('50m'), '100'=>_('100m'), 'note'=>_('Note')), '', array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481 ) ); // 'Nr'=>'Nr', 

		if ($note >= 0)
		{
			$pdf->ezTable($aryNotes, array('no'=> '', 'text'=>''), '', array('showHeadings' => 0, 'showLines' => 0, 'shaded' => 0, 'xPos' => 57, 'xOrientation' => 'right', 'width' => 481, 'fontSize' => 10, 'titleFontSize' => 12, 'cols' => array('no' => array('width' => 30, 'justification' => 'center'))));
		}
		
		$pdf->ezStream();
	}
	$db->close();
?>