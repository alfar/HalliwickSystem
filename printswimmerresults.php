<?PHP
	require('config.php');
	include('class.pdf.php');
	include('class.ezpdf.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();
	
	$id = (int)(0 . $_REQUEST['id']);

	$swimmer = 0;	
	if ($aryCompo = $db->getCompetition($id))
	{
		$rsSwimmer = $db->swimmerList($id);
		
		$aryData = array();
		$cnt = 0;
		while ($arySwimmer = $db->fetch_array($rsSwimmer))
		{
			$test = "" . $arySwimmer['swimmerId'];
			if ($swimmer != $test)
			{
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

				$res = "";
				$rsResult = $db->getBestTime($id, $swimmer, 25);

				if ($result = $db->fetch_array($rsResult))
				{
					if ($result['result'] > 0)
					{
						$min = floor($result['result'] / 60);

						$sek = floor($result['result']) % 60;

						$hun = floor($result['result'] * 100) % 100;

						$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
					}
				}
				$aryRow['25'] = $res;

				$res = "";
				$rsResult = $db->getBestTime($id, $swimmer, 50);

				if ($result = $db->fetch_array($rsResult))
				{
					if ($result['result'] > 0)
					{
						$min = floor($result['result'] / 60);

						$sek = floor($result['result']) % 60;

						$hun = floor($result['result'] * 100) % 100;
						
						$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
					}
				}
				$aryRow['50'] = $res;

				$res = "";
				$rsResult = $db->getBestTime($id, $swimmer, 100);

				if ($result = $db->fetch_array($rsResult))
				{
					if ($result['result'] > 0)
					{
						$min = floor($result['result'] / 60);

						$sek = floor($result['result']) % 60;

						$hun = floor($result['result'] * 100) % 100;

						$res = sprintf('%02d:%02d:%02d', $min, $sek, $hun);
					}
				}
				$aryRow['100'] = $res;
			}
		}
		if (is_array($aryRow))
		{
			$aryData[$cnt] = $aryRow;
		}
		
		$pdf = new Cezpdf();
		$pdf->selectFont('./fonts/Helvetica');

		$pdf->ezSetY(830);
		$pdf->ezText(_("Resultatliste") . " " . $aryCompo['name'] . " - " . date('d-m-Y', strtotime($aryCompo['date'])), 18, array('justification' => 'center'));
			
		$pdf->ezSetY(781);
		$pdf->ezTable($aryData, array('Navn'=>_('Navn'), 'Klub'=>_('Klub'), '25'=>_('25m'), '50'=>_('50m'), '100'=>_('100m')), '', array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481 ) ); // 'Nr'=>'Nr', 

		//header("Content-type: application/pdf");
		//header("Content-Length: $len");
		//header("Content-Disposition: attachment; filename=swimmers.pdf");
		$pdf->ezStream();
	}
	
	$db->close();
?>	
