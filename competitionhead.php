<?php
	require('head.php');
?>
			<div class="row">
			<div class="col-md-3">
				<ul class="nav nav-stacked nav-pills">
					<li<?= $activetab == 'competitiondata' ? ' class="active"' : '' ?>><a href="competitiondata.php?id=<?= $id ?>"><span class="glyphicon glyphicon-book"></span> <?= _('Stævnedata') ?></a></li>
					<li<?= $activetab == 'swimmers' ? ' class="active"' : '' ?>><a href="swimmers.php?id=<?= $id ?>"><span class="glyphicon glyphicon-user"></span> <?= _('Svømmere') ?></a></li>
					<li<?= $activetab == 'heats' ? ' class="active"' : '' ?>><a href="heats.php?id=<?= $id ?>"><span class="glyphicon glyphicon-list"></span> <?= _('Heats') ?></a></li>
					<li<?= $activetab == 'results' ? ' class="active"' : '' ?>><a href="results.php?id=<?= $id ?>"><span class="glyphicon glyphicon-time"></span> <?= _('Resultater') ?></a></li>
					<li<?= $activetab == 'teams' ? ' class="active"' : '' ?>><a href="teams.php?id=<?= $id ?>"><span class="glyphicon glyphicon-transfer"></span> <?= _('Stafet') ?></a></li>
					<li<?= $activetab == 'teamresults' ? ' class="active"' : '' ?>><a href="teamresults.php?id=<?= $id ?>"><span class="glyphicon glyphicon-ok-circle"></span> <?= _('Stafetresultater') ?></a></li>
					<li<?= $activetab == 'prizes' ? ' class="active"' : '' ?>><a href="prizes.php?id=<?= $id ?>"><span class="glyphicon glyphicon-glass"></span> <?= _('Pokaler') ?></a></li>
					<li<?= $activetab == 'diplomas' ? ' class="active"' : '' ?>><a href="diplomas.php?id=<?= $id ?>"><span class="glyphicon glyphicon-list-alt"></span> <?= _('Diplomer') ?></a></li>
					<li<?= $activetab == 'prints' ? ' class="active"' : '' ?>><a href="prints.php?id=<?= $id ?>"><span class="glyphicon glyphicon-print"></span> <?= _('Udskrifter') ?></a></li>
					<li class="separator"></li>
					<li><a href="index.php" target="_top"><span class="glyphicon glyphicon-home"></span> <?= _('Stævneliste') ?></a></li>
				</ul>
			</div>
			<div class="col-md-9">