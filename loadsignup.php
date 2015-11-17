<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	$id = (int)(0 . $_GET['id']);
	
	$errormessage = '';
	if (isset($_FILES['signupfile']))
	{
		$signupfile = file($_FILES['signupfile']['tmp_name']);
		
		$state = 0;
		
		$swimmerId = 0;
		
		foreach ($signupfile as $line)
		{
			if ($state == 0)
			{
				if (preg_match('/SIGNUP\/(\d+.\d)/', $line, $header))
				{
					$version = $header[1];
					$state = 1;
				}
				else
				{
					$errormessage = 'Ugyldig tilmeldingsfil';
					break;
				}
			}
			elseif ($state == 1)
			{
				if (preg_match('/S\|(.*)\|(.*)\|([01])\|([01])\|(.*)\|/', $line, $match))
				{
					$swimmerId = $db->findOrCreateSwimmer($match[1], $match[2], $match[3], $match[4], $match[5]);
					$db->clearSwimmerDistances($id, $swimmerId);
				}
				elseif (preg_match('/D\|(25|50|100|-25|-50)\|(.*)\|(.*)\|/', $line, $match))
				{
					$db->addSwimmerDistance($id, $swimmerId, $match[1], $match[2], $match[3]);
				}
			}
		}
		
		if ($errormessage == '')
		{
			header('Location: swimmers.php?id=' . $id);
		}
		
		unlink($_FILES['signupfile']['tmp_name']);
	}
?>
<?php
	$subtitle = _('Indlæs tilmelding');
	require('head.php');
?>
				<form method="post" enctype="multipart/form-data" class="form-horizontal">
					<div class="form-group"><label for="signupfile" class="col-lg-2 control-label"><?= _('Tilmeldingsfil') ?></label><div class="col-lg-10"><input type="file" name="signupfile" class="form-control" /></div>
						<?php if ($errormessage != '') { echo('<div class="alert-danger">' . $errormessage . '</div>'); } ?>
					<div class="form-actions">
						<input type="submit" class="btn btn-primary" value="<?= _('Opret') ?>" /> <a href="swimmers.php?id=<?= $id ?>" class="btn btn-cancel"><?= _('Fortryd') ?></a>
					</div>
				</form>
<?PHP	
	$db->close();
?>
<?php
	require('foot.php');
?>