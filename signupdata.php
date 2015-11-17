<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();
?>
<?php
	$activetab = 'signupdata';
	$subtitle = _('Tilmelding');
	require('signuphead.php');
?>
<?PHP
	$id = (int)(0 . $_REQUEST['id']);
	if (isset($_POST['name']))
	{
		$date = preg_replace('/([0-9][0-9])-([0-9][0-9])-([0-9][0-9][0-9][0-9])/', '$3/$2/$1', $_POST['date']);

		$db->updateSignup($id, $_POST['name'], $date);
	}
		
	if ($aryCompo = $db->getSignup($id))
	{
?>
<form method="post" class="form-horizontal">
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Navn') ?>:</label>
		<div class="col-lg-10"><input type="text" name="name" class="form-control" value="<?= $aryCompo['name'] ?>" /></div>
	</div>
	<div class="form-group">
		<label for="date" class="col-lg-2 control-label"><?= _('Dato') ?>:</label>
		<div class="col-lg-10"><input type="text" name="date" class="form-control" value="<?= strftime('%d-%m-%Y', strtotime($aryCompo['date'])) ?>" /></div>
	</div>
	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<input type="submit" class="btn btn-primary" value="<?= _('Opdater') ?>" />
		</div>
	</div>
</form>
<?PHP		
	}
	
	$db->close();
?>
<?php
	require('signupfoot.php');
?>