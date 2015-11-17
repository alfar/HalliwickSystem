<?PHP
	require('config.php');

	$db = new $dbclass($dbserver, $username, $password, $dbname);

	$db->connect();

	session_start();

	if (isset($_POST['name']))
	{
		if (isset($_GET['query']))
		{
			$db->updatePrize($_GET['query'], $_POST['name'], $_POST['query']);
		}
		else
		{
			$db->addPrize($_POST['name'], $_POST['query']);
		}
	}

	$edit_name = '';
	$edit_query = '';
	$edit_submit = _('Opret');
	$edit_cancel = false;

	if (isset($_GET['query']))
	{
		$rsPrint = $db->getPrizeDef($_GET['query']);
		
		if ($print = $db->fetch_array($rsPrint))
		{
			$edit_name = $print['name'];
			$edit_query = $print['restriction'];
			$edit_submit = _('Gem');
			$edit_cancel = true;
		}
	}

	if (isset($_GET['delquery']))
	{
		$db->deletePrize($_GET['delquery']);
	}


	$id = (int)(0 . $_GET['id']);
?>
<?php
	$activetab = 'prizes';
	$subtitle = _('Pokaler');
	require('competitionhead.php');
?>
							<table class="table table-striped">
								<tr><th><?= _('Pokal') ?></th><th><?= _('Vinder') ?></th><th></th></tr>
<?PHP
	$rsPrize = $db->prizeList();
	
	while ($prize = $db->fetch_array($rsPrize))
	{
		$winnerRest = $prize['restriction'];
		
		$rsWinner = $db->getPrize($id, $winnerRest);
		
		if ($winner = $db->fetch_array($rsWinner))
		{		
			echo('<tr><td>' . $prize['name'] . '</td><td>' . $winner['clubname'] . ' - ' . $winner['swimmername'] . '</td><td align="right"><a href="prizes.php?id=' . $id . '&query=' . $prize['id'] . '"><span class="glyphicon glyphicon-pencil"></span></a> <a href="prizes.php?id=' . $id . '&delquery=' . $prize['id'] . '"><span class="glyphicon glyphicon-remove"></span></a></td></tr>');
		}
		else
		{
			echo('<tr><td>' . $prize['name'] . '</td><td></td><td align="right"><a href="prizes.php?id=' . $id . '&query=' . $prize['id'] . '"><span class="glyphicon glyphicon-pencil"></span></a> <a href="prizes.php?id=' . $id . '&delquery=' . $prize['id'] . '"><span class="glyphicon glyphicon-remove"></span></a></td></tr>');
		}
	}
	
	$db->close();
?>
						</table>
<form method="post" class="form-horizontal">
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Navn') ?>:</label>
		<div class="col-lg-10"><input type="text" name="name" value="<?= $aryCompo['name'] ?>" class="form-control" /></div>
	</div>
	<div class="form-group">
		<label for="name" class="col-lg-2 control-label"><?= _('Forespørgsel') ?>:</label>
		<div class="col-lg-10"><textarea name="query" rows="10" cols="80" class="form-control"><?PHP echo($edit_query); ?></textarea></div>
	</div>
	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<?PHP echo($edit_cancel ? '<input type="button" class="btn" onclick="location.href=\'prizes.php?id=' . $id . '\';" value="' . _('Fortryd') . '" /> ' : ''); ?><input type="submit" class="btn btn-primary" value="<?PHP echo($edit_submit); ?>" />
		</div>
	</div>
</form>
<?php
	require('competitionfoot.php');
?>
