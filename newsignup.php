<?PHP
	require('config.php');

	if (isset($_POST['name']))
	{
		$db = new $dbclass($dbserver, $username, $password, $dbname);

		$db->connect();
		
		$date = preg_replace('/([0-9][0-9])-([0-9][0-9])-([0-9][0-9][0-9][0-9])/', '$3/$2/$1', $_POST['date']);
		
		$id = $db->newSignup($_POST['name'], $date);
				
		$db->close();
				
		header("Location: signupdata.php?id=$id");
	}
?>
<?php
	$subtitle = _('Ny tilmelding');
	require('head.php');
?>
<form method="post" class="form-horizontal">
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Navn') ?>:</label>
		<div class="col-lg-10"><input type="text" name="name" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="date" class="col-lg-2 control-label"><?= _('Dato') ?>:</label>
		<div class="col-lg-10"><input type="text" name="date" class="form-control" /></div>
	</div>
	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<input type="submit" class="btn btn-primary" value="<?= _('Opret') ?>" /> <a href="signups.php" class="btn btn-cancel"><?= _('Fortryd') ?></a>
		</div>
	</div>
</form>
<?php
	require('foot.php');
?>