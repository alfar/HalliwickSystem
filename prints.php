<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	$db->setupPrint();

	session_start();

	if (isset($_POST['name']))
	{
		if (isset($_GET['query']))
		{
			$db->updatePrint($_GET['query'], $_POST['name'], $_POST['query']);
		}
		else
		{
			$db->addPrint($_POST['name'], $_POST['query']);
		}
	}

	$edit_name = '';
	$edit_query = '';
	$edit_submit = _('Opret');
	$edit_cancel = false;

	if (isset($_GET['query']))
	{
		$rsPrint = $db->getPrint($_GET['query']);
		
		if ($print = $db->fetch_array($rsPrint))
		{
			$edit_name = $print['name'];
			$edit_query = $print['query'];
			$edit_submit = _('Gem');
			$edit_cancel = true;
		}
	}

	if (isset($_GET['delquery']))
	{
		$db->deletePrint($_GET['delquery']);
	}

	$id = (int)(0 . $_GET['id']);
?>
<?php
	$activetab = 'prints';
	$subtitle = _('Udskrifter');
	require('competitionhead.php');
?>
							<table class="table table-striped">
								<tr><th><?= _('Udskrift') ?></th><th></th></tr>
<?PHP
	$rsPrints = $db->printList();
	
	while ($print = $db->fetch_array($rsPrints))
	{
			echo('<tr><td><a href="printprint.php?id=' . $id . '&query=' . $print['id'] . '" target="_blank">' . $print['name'] . '</a></td><td align="right"><a href="prints.php?id=' . $id . '&query=' . $print['id'] . '"><span class="glyphicon glyphicon-pencil"></span></a> <a href="prints.php?id=' . $id . '&delquery=' . $print['id'] . '"><span class="glyphicon glyphicon-remove"></span></a></td></tr>');
	}
	
	$db->close();
?>
							</table>
<form method="post" class="form-horizontal">
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Navn') ?>:</label>
		<div class="col-lg-10"><input type="text" name="name" value="<?= $edit_name ?>" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Forespørgsel') ?>:</label>
		<div class="col-lg-10"><textarea name="query" rows="10" cols="80" class="form-control"><?PHP echo($edit_query); ?></textarea></div>
	</div>
	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<?PHP echo($edit_cancel ? '<input type="button" class="btn" onclick="location.href=\'prints.php?id=' . $id . '\';" value="' . _('Fortryd') . '" /> ' : ''); ?><input type="submit" class="btn btn-primary" value="<?PHP echo($edit_submit); ?>" />
		</div>
	</div>
</form>
<?php
	require('competitionfoot.php');
?>
