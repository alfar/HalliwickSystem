# phpMyAdmin SQL Dump
# version 2.5.4
# http://www.phpmyadmin.net
#
# Vært: localhost
# Genereringstidspunkt: 28/07 2005 kl. 09:18:15
# Server version: 4.0.18
# PHP version: 4.3.4
# 
# Database: : `hasiStavne`
# 

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblClub`
#

CREATE TABLE `tblClub` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=16 ;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblCompetition`
#

CREATE TABLE `tblCompetition` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `date` date NOT NULL default '0000-00-00',
  `leader` varchar(255) NOT NULL default '',
  `extra100` int(11) NOT NULL default '0',
  `howmanySemi25` int(11) NOT NULL default '0',
  `howmanySemi50` int(11) NOT NULL default '0',
  `howmanySemi100` int(11) NOT NULL default '0',
  `tracks` int(11) NOT NULL default '63',
  `howmanySemiExtra25` int(11) NOT NULL default '0',
  `howmanySemiExtra50` int(11) NOT NULL default '0',
  `howmanySemiExtra100` int(11) NOT NULL default '0',
  `howmanyFinal25` int(11) NOT NULL default '0',
  `howmanyFinal50` int(11) NOT NULL default '0',
  `howmanyFinal100` int(11) NOT NULL default '0',
  `howmanyFinalExtra25` int(11) NOT NULL default '0',
  `howmanyFinalExtra50` int(11) NOT NULL default '0',
  `howmanyFinalExtra100` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=15 ;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblCompetitionSwimmer`
#

CREATE TABLE `tblCompetitionSwimmer` (
  `competitionId` int(11) NOT NULL default '0',
  `swimmerId` int(11) NOT NULL default '0',
  `distance` int(11) NOT NULL default '0',
  `time` decimal(5,2) NOT NULL default '0.00',
  `semitime` decimal(5,2) default NULL,
  `semitimechecked` int(11) NOT NULL default '0',
  `finaltime` decimal(5,2) NOT NULL default '0.00',
  `help` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`competitionId`,`swimmerId`,`distance`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblPrize`
#

CREATE TABLE `tblPrize` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `restriction` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblRace`
#

CREATE TABLE `tblRace` (
  `id` int(11) NOT NULL auto_increment,
  `competitionId` int(11) NOT NULL default '0',
  `distance` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `number` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=694 ;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblRaceSwimmer`
#

CREATE TABLE `tblRaceSwimmer` (
  `raceId` int(11) NOT NULL default '0',
  `swimmerId` int(11) NOT NULL default '0',
  `result1` decimal(5,2) NOT NULL default '0.00',
  `position` int(11) NOT NULL default '0',
  `track` int(11) NOT NULL default '0',
  `result2` decimal(5,2) NOT NULL default '0.00',
  `startTime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`raceId`,`swimmerId`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblSwimmer`
#

CREATE TABLE `tblSwimmer` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `clubId` int(11) NOT NULL default '0',
  `edge` int(11) NOT NULL default '0',
  `pilot` int(11) NOT NULL default '0',
  `devices` varchar(255) NOT NULL default '',
  `diet` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `clubId` (`clubId`)
) TYPE=MyISAM AUTO_INCREMENT=83 ;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblTeam`
#

CREATE TABLE `tblTeam` (
  `id` int(11) NOT NULL auto_increment,
  `competitionId` int(11) NOT NULL default '0',
  `distance` int(11) NOT NULL default '0',
  `clubId` int(11) NOT NULL default '0',
  `time` float NOT NULL default '0',
  `result1` float NOT NULL default '0',
  `result2` float NOT NULL default '0',
  `place` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

# --------------------------------------------------------

#
# Struktur dump for tabellen `tblTeamSwimmer`
#

CREATE TABLE `tblTeamSwimmer` (
  `teamId` int(11) NOT NULL default '0',
  `swimmerId` int(11) NOT NULL default '0',
  `time` float(5,2) NOT NULL default '0.00',
  PRIMARY KEY  (`teamId`,`swimmerId`)
) TYPE=MyISAM;
    
# --------------------------------------------------------

INSERT INTO `tblPrize` VALUES (1, '25m vinder', 'cs.distance = 25 and position = 1 and type = 8');
INSERT INTO `tblPrize` VALUES (2, '50m vinder', 'cs.distance = 50 and position = 1 and type = 8');
INSERT INTO `tblPrize` VALUES (3, '100m vinder', 'cs.distance = 100 and position = 1 and type = 5');
INSERT INTO `tblPrize` VALUES (4, '25m god tid', 'cs.distance = 25 and type = 0 order by (result1 + result2) / 2 - time');
INSERT INTO `tblPrize` VALUES (5, '50m god tid', 'cs.distance = 50 and type = 0 order by (result1 + result2) / 2 - time');
INSERT INTO `tblPrize` VALUES (6, '100m god tid', 'cs.distance = 100 and type = 0 order by (result1 +result2) / 2 - time');
