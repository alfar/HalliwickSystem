<?PHP
	require('config.php');

	if (isset($_POST['name']))
	{
		$db = new $dbclass($dbserver, $username, $password, $dbname);

		$db->connect();
		
		$date = preg_replace('/([0-9][0-9])-([0-9][0-9])-([0-9][0-9][0-9][0-9])/', '$3/$2/$1', $_POST['date']);
		
		$tracks = 0;
		foreach ($_POST as $k => $val)
		{
			if (strstr($k, 'track_'))
			{
				$tracks += pow(2, (int)(substr($k, 6, 1)));
			}
		}

		$id = $db->newCompetition($_POST['name'], $date, $_POST['leader'], (isset($_POST['extra100']) && $_POST['extra100'] == '1' ? '1' : '0'), $tracks);
				
		$db->close();
				
		header("Location: competitiondata.php?id=$id");
	}
?>
<?php
	$subtitle = _('Nyt stævne');
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
		<label for="leader" class="col-lg-2 control-label"><?= _('Stævneleder') ?>:</label>
		<div class="col-lg-10"><input type="text" name="leader" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="lanes" class="col-lg-2 control-label"><?= _('Brug banerne') ?>:</label>
		<div class="col-lg-10">
<?PHP
	for ($cnt = 0; $cnt < $number_of_tracks; $cnt++)
	{
		$bit = pow(2, $cnt);
		echo('<label for="track_' . $cnt . '" class="checkbox-inline"><input type="checkbox" name="track_' . $cnt . '" value="1" checked="checked" />' . ($cnt + 1) . '.</label> ');
	}
?>
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-2"></div>
		<label for="extra100" class="checkbox col-lg-10"><input type="checkbox" value="1" name="extra100" /> <?= _('100 meter ekstra') ?></label>
	</div>

	<div class="form-actions">
		<input type="submit" class="btn btn-primary" value="<?= _('Opret') ?>" /> <a href="index.php" class="btn btn-cancel"><?= _('Fortryd') ?></a>
	</div>
</form>
<?php
	require('foot.php');
?>