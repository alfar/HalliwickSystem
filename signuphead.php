<?php
	require('head.php');
?>
			<div class="row">
			<div class="col-md-3">
				<ul class="nav nav-stacked nav-pills">
					<li<?= $activetab == 'signupdata' ? ' class="active"' : '' ?>><a href="signupdata.php?id=<?= $id ?>"><span class="glyphicon glyphicon-book"></span> <?= _('Stamdata') ?></a></li>
					<li<?= $activetab == 'swimmers' ? ' class="active"' : '' ?>><a href="signupswimmers.php?id=<?= $id ?>"><span class="glyphicon glyphicon-user"></span> <?= _('Svømmere') ?></a></li>
					<li class="separator"></li>
					<li><a href="signups.php" target="_top"><span class="glyphicon glyphicon-home"></span> <?= _('Tilmeldinger') ?></a></li>
				</ul>
			</div>
			<div class="col-md-9">