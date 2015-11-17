<?PHP
	require('config.php');
	include('class.pdf.php');
	include('class.ezpdf.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	session_start();

	$id = (int)(0 . $_GET['id']);
	$query = (int)(0 . $_GET['query']);

	if ($aryCompo = $db->getCompetition($id))
	{
		$rsQuery = $db->getPrint($query);
		if ($queryHead = $db->fetch_array($rsQuery))
		{
			$pdf = new Cezpdf();
			$pdf->selectFont('./fonts/Helvetica');
	
			$pdf->ezText($aryCompo['name'] . " - " . date('d-m-Y', strtotime($aryCompo['date'])), 18, array('justification' => 'center'));
	
			$pdf->ezText('', 10);
	
			$rsQuery = $db->printQuery($id, $query);
			
			while ($row = $db->fetch_array($rsQuery))
			{
				$table[] = $row;
			}

			$pdf->ezTable($table, null, $queryHead['name'], array('xPos' => 57, 'xOrientation' => 'right', 'width' => 481, 'fontSize' => 14, 'titleFontSize' => 18));

			$pdf->ezStream();
		}
	}

	$db->close();
?>