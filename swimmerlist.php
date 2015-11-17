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
		//mysql_query("select s.id as id, s.name as name, c.name as club, cs.distance as distance, cs.time as time from tblSwimmer s inner join tblClub c on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '$id' order by c.name, s.name");
		
		$aryData = array();
		$cnt = 0;
		while ($arySwimmer = $db->fetch_array($rsSwimmer))
		{
			$test = "" . $arySwimmer['swimmerId'];
			if ($swimmer != $test)
			{
				$swimmer = $test; // $arySwimmer['id'];
				if (is_array($aryRow))
				{
					$aryData[$cnt] = $aryRow;
					$cnt++;
				}
				$aryRow = array();
				//$aryRow['Nr'] = $arySwimmer['id'];
				$aryRow['Navn'] = $arySwimmer['name'];
				$aryRow['Klub'] = $arySwimmer['club'];
			}
			
			$aryRow[$arySwimmer['distance']] = $arySwimmer['time'];
		}
		if (is_array($aryRow))
		{
			$aryData[$cnt] = $aryRow;
		}
		
		$pdf =& new Cezpdf();
		$pdf->selectFont('./fonts/Helvetica');

		$pdf->ezSetY(830);
		$pdf->ezText("Deltagerliste " . $aryCompo['name'] . " - " . $aryCompo['date'], 18, array('justification' => 'center'));
			
		$pdf->ezSetY(781);
		$pdf->ezTable($aryData, array('Navn'=>'Navn', 'Klub'=>'Klub', '25'=>'25m', '50'=>'50m', '100'=>'100m'), '', array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481 ) ); // 'Nr'=>'Nr', 

		header("Content-type: application/pdf");
		header("Content-Length: $len");
		//header("Content-Disposition: attachment; filename=swimmers.pdf");
		$pdf->ezStream();
	}
	
	$db->close();
?>	
