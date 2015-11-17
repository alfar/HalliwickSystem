<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	$id = (int)(0 . $_REQUEST['id']);

	if (isset($_POST['name']))
	{
		$date = preg_replace('/([0-9][0-9])-([0-9][0-9])-([0-9][0-9][0-9][0-9])/', '$3/$2/$1', $_POST['date']);

		$tracks = 0;
		foreach ($_POST as $k => $val)
		{
			if (strstr($k, 'track_'))
			{
				$tracks += pow(2, (int)(substr($k, 6, 1)));
			}
		}
		$db->updateCompetition($id, $_POST['name'], $date, $_POST['leader'], isset($_POST['extra100']) && $_POST['extra100'] == '1' ? '1' : '0', $tracks);
	}
		
	if ($aryCompo = $db->getCompetition($id))
	{
		$tracks = $aryCompo['tracks'];
?>
<?php
	$activetab = 'competitiondata';
	$subtitle = _('Stævnedata');
	require('competitionhead.php');
?>
<form method="post" class="form-horizontal">
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Navn') ?>:</label>
		<div class="col-lg-10"><input type="text" name="name" value="<?= $aryCompo['name'] ?>" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="date" class="col-lg-2 control-label"><?= _('Dato') ?>:</label>
		<div class="col-lg-10"><input type="text" name="date" value="<?= strftime('%d-%m-%Y', strtotime($aryCompo['date'])) ?>" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="leader" class="col-lg-2 control-label"><?= _('Stævneleder') ?>:</label>
		<div class="col-lg-10"><input type="text" name="leader" value="<?= $aryCompo['leader'] ?>" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="lanes" class="col-lg-2 control-label"><?= _('Brug banerne') ?>:</label>
		<div class="col-lg-10">
<?PHP
	for ($cnt = 0; $cnt < $number_of_tracks; $cnt++)
	{
		$bit = pow(2, $cnt);
		echo('<label for="track_' . $cnt . '" class="checkbox-inline"><input type="checkbox" name="track_' . $cnt . '" value="1"' . (($tracks & $bit) == $bit ? ' checked="checked"' : '') . ' />' . ($cnt + 1) . '.</label> ');
	}
?>
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<label for="extra100" class="checkbox"><input type="checkbox" value="1" name="extra100"<?= $aryCompo['extra100'] > 0 ? ' checked="checked"' : '' ?> /> <?= _('100 meter ekstra') ?></label>
		</div>
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
	require('competitionfoot.php');
?>
