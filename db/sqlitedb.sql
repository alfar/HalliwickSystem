CREATE TABLE tblClub (
  id INTEGER,
  name TEXT DEFAULT '',
  PRIMARY KEY  (id)
);

CREATE TABLE tblCompetition (
  id INTEGER NOT NULL,
  name TEXT NOT NULL DEFAULT '',
  date TEXT NOT NULL DEFAULT '0000-00-00',
  leader TEXT NOT NULL DEFAULT '',
  extra100 INTEGER NOT NULL DEFAULT '0',
  howmanySemi25 INTEGER NOT NULL DEFAULT '0',
  howmanySemi50 INTEGER NOT NULL DEFAULT '0',
  howmanySemi100 INTEGER NOT NULL DEFAULT '0',
  tracks INTEGER NOT NULL DEFAULT '63',
  howmanySemiExtra25 INTEGER NOT NULL DEFAULT '0',
  howmanySemiExtra50 INTEGER NOT NULL DEFAULT '0',
  howmanySemiExtra100 INTEGER NOT NULL DEFAULT '0',
  howmanyFinal25 INTEGER NOT NULL DEFAULT '0',
  howmanyFinal50 INTEGER NOT NULL DEFAULT '0',
  howmanyFinal100 INTEGER NOT NULL DEFAULT '0',
  howmanyFinalExtra25 INTEGER NOT NULL DEFAULT '0',
  howmanyFinalExtra50 INTEGER NOT NULL DEFAULT '0',
  howmanyFinalExtra100 INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY  (id)
);

CREATE TABLE tblCompetitionSwimmer (
  competitionId INTEGER NOT NULL DEFAULT '0',
  swimmerId INTEGER NOT NULL DEFAULT '0',
  distance INTEGER NOT NULL DEFAULT '0',
  time NUMERIC(5,2) NOT NULL DEFAULT '0.00',
  semitime NUMERIC(5,2) DEFAULT NULL,
  semitimechecked INTEGER NOT NULL DEFAULT '0',
  finaltime NUMERIC(5,2) NOT NULL DEFAULT '0.00',
  help TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (competitionId,swimmerId,distance)
);

CREATE TABLE tblPrize (
  id INTEGER NOT NULL,
  name TEXT NOT NULL DEFAULT '',
  restriction TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (id)
);

CREATE TABLE tblRace (
  id INTEGER NOT NULL,
  competitionId INTEGER NOT NULL DEFAULT '0',
  distance INTEGER NOT NULL DEFAULT '0',
  type INTEGER NOT NULL DEFAULT '0',
  name TEXT NOT NULL DEFAULT '',
  number INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY  (id)
);

CREATE TABLE tblRaceSwimmer (
  raceId INTEGER NOT NULL DEFAULT '0',
  swimmerId INTEGER NOT NULL DEFAULT '0',
  result1 NUMERIC(5,2) NOT NULL DEFAULT '0.00',
  position INTEGER NOT NULL DEFAULT '0',
  track INTEGER NOT NULL DEFAULT '0',
  result2 NUMERIC(5,2) NOT NULL DEFAULT '0.00',
  startTime INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY  (raceId,swimmerId)
);

CREATE TABLE tblSwimmer (
  id INTEGER NOT NULL,
  name TEXT NOT NULL DEFAULT '',
  clubId INTEGER NOT NULL DEFAULT '0',
  edge INTEGER NOT NULL DEFAULT '0',
  pilot INTEGER NOT NULL DEFAULT '0',
  devices TEXT NOT NULL DEFAULT '',
  diet TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (id)
);

CREATE TABLE tblTeam (
  id INTEGER NOT NULL,
  competitionId INTEGER NOT NULL DEFAULT '0',
  distance INTEGER NOT NULL DEFAULT '0',
  clubId INTEGER NOT NULL DEFAULT '0',
  time float NOT NULL DEFAULT '0',
  result1 float NOT NULL DEFAULT '0',
  result2 float NOT NULL DEFAULT '0',
  place INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY  (id)
);

CREATE TABLE tblTeamSwimmer (
  teamId INTEGER NOT NULL DEFAULT '0',
  swimmerId INTEGER NOT NULL DEFAULT '0',
  time float(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY  (teamId,swimmerId)
);

CREATE TABLE tblPrints (
	id INTEGER NOT NULL,
	name TEXT NOT NULL DEFAULT '', 
	query TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (id)
);
    
INSERT INTO tblPrize VALUES (1, '25m vinder', 'cs.distance = 25 and position = 1 and type = 8');
INSERT INTO tblPrize VALUES (2, '50m vinder', 'cs.distance = 50 and position = 1 and type = 8');
INSERT INTO tblPrize VALUES (3, '100m vinder', 'cs.distance = 100 and position = 1 and type = 5');
INSERT INTO tblPrize VALUES (4, '25m god tid', 'cs.distance = 25 and type = 0 order by abs((result1 + result2) / 2 - startTime - time * 0.93)');
INSERT INTO tblPrize VALUES (5, '50m god tid', 'cs.distance = 50 and type = 0 order by abs((result1 + result2) / 2 - startTime - time * 0.93)');
INSERT INTO tblPrize VALUES (6, '100m god tid', 'cs.distance = 100 and type = 0 order by abs((result1 + result2) / 2 - startTime - time * 0.93)');

