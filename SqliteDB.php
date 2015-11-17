<?PHP
	class SqliteDB
	{
		function SqliteDB($server, $username, $password, $db)
		{
			$this->server = $server;
			$this->username = $username;
			$this->password = $password;
			$this->db = $db;
		}
		
		function connect()
		{
			$ready = file_exists($this->db);
			$this->cnDB = sqlite_open($this->db); //new SQLiteDatabase($this->db);
			if (!$ready)
			{
				sqlite_exec($this->cnDB, file_get_contents('db/sqlitedb.sql'));
			}
			
			sqlite_exec($this->cnDB, 'PRAGMA short_column_names = on');
		}
		
		function fetch_array($recordset, $type = SQLITE_ASSOC)
		{
			return sqlite_fetch_array($recordset, $type);
		}
		
		function close()
		{
			sqlite_close($this->cnDB);
		}
		
		function competitionList()
		{
			return sqlite_unbuffered_query($this->cnDB, 'select * from tblCompetition order by date desc');
		}
		
		function newCompetition($name, $date, $leader, $extra100, $tracks)
		{
			sqlite_exec($this->cnDB, "insert into tblCompetition (name, date, leader, extra100, tracks) values ('" . sqlite_escape_string($name) . "', '" . sqlite_escape_string($date) . "', '" . sqlite_escape_string($leader) . "', '" . sqlite_escape_string($extra100) . "', '" . sqlite_escape_string($tracks) . "')");
			
			return sqlite_last_insert_rowid($this->cnDB);
		}

		function getCompetition($id)
		{
			$rsCompo = sqlite_unbuffered_query($this->cnDB, "select * from tblCompetition where id = '" . sqlite_escape_string($id) . "'");
			return $this->fetch_array($rsCompo);
		}
		
		function updateCompetition($id, $name, $date, $leader, $extra100, $tracks)
		{
			sqlite_exec($this->cnDB, "update tblCompetition set name = '" . sqlite_escape_string($name) . "', date = '" . sqlite_escape_string($date) . "', leader = '" . sqlite_escape_string($leader) . "', extra100 = '" . sqlite_escape_string($extra100) . "', tracks = '" . sqlite_escape_string($tracks) . "' where id = '" . sqlite_escape_string($id) . "';");
		}
		
		function updateHowMany($competitionId, $distance, $round, $howmany)
		{
			switch($round)
			{
				case 4:
					sqlite_exec($this->cnDB, "update tblCompetition set howmanySemi" . sqlite_escape_string($distance) . " = '" . sqlite_escape_string($howmany) . "' where id = '" . sqlite_escape_string($competitionId) . "'");
					break;
				case 5:
					sqlite_exec($this->cnDB, "update tblCompetition set howmanyFinal" . sqlite_escape_string($distance) . " = '" . sqlite_escape_string($howmany) . "' where id = '" . sqlite_escape_string($competitionId) . "'");
					break;
				case 8:
					sqlite_exec($this->cnDB, "update tblCompetition set howmanyFinal" . sqlite_escape_string($distance) . " = '" . sqlite_escape_string($howmany) . "' where id = '" . sqlite_escape_string($competitionId) . "'");
					break;
				case 20:
					sqlite_exec($this->cnDB, "update tblCompetition set howmanySemiExtra" . sqlite_escape_string($distance) . " = '" . sqlite_escape_string($howmany) . "' where id = '" . sqlite_escape_string($competitionId) . "'");
					break;
				case 24:
					sqlite_exec($this->cnDB, "update tblCompetition set howmanyFinalExtra" . sqlite_escape_string($distance) . " = '" . sqlite_escape_string($howmany) . "' where id = '" . sqlite_escape_string($competitionId) . "'");
					break;				
			}
		}
		
		function deleteCompetitionSwimmer($competitionId, $swimmerId)
		{
			sqlite_exec($this->cnDB, "delete from tblCompetitionSwimmer where swimmerId = '" . sqlite_escape_string($swimmerId) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");

			$rsRaces = $this->competitionRaces($competitionId);

			$raceIds = array();
			
			while ($race = $this->fetch_array($rsRaces))
			{
				$raceIds[] = $race['id'];
			}
			
			sqlite_exec($this->cnDB, "delete from tblRaceSwimmer where swimmerId = '" . sqlite_escape_string($swimmerId) . "' and raceId in (" . implode(',', $raceIds) . ")");
		}

		function competitionRaces($competitionId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select id from tblRace where competitionId = '" . sqlite_escape_string($competitionId) . "'");
		}
		
		function competitionSwimmerTimes($competitionId, $swimmerId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblCompetitionSwimmer where competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "'");
		}
		
		function upgradeSemiFinal($competitionId, $distance)
		{
			$rsFinals = sqlite_unbuffered_query($this->cnDB, "select id from tblRace where distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "' and (type = 8 or type = 5)");
			
			while ($final = $this->fetch_array($rsFinals))
			{
				sqlite_exec($this->cnDB, "delete from tblRaceSwimmer where raceId = {$final['id']}");
				sqlite_exec($this->cnDB, "delete from tblRace where id = {$final['id']}");
			}

			$rsUpdateSwimmers = sqlite_unbuffered_query($this->cnDB, "select swimmerId from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.type = 4 and distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");
			
			while ($us = $this->fetch_array($rsUpdateSwimmers))
			{
				sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set finaltime = semitime where swimmerId = {$us['swimmerId']} and distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");
			}
			
			sqlite_exec($this->cnDB, "update tblRace set type = 8 where type = 4 and competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			sqlite_exec($this->cnDB, "update tblCompetition set howmanyFinal$distance = howmanySemi$distance where id = '" . sqlite_escape_string($competitionId) . "';");
		}
		
		function clubList()
		{
			return sqlite_unbuffered_query($this->cnDB, "select id, name from tblClub");
		}
		
		function clubListForDistance($competitionId, $distance, $round, $howmany)
		{
			switch ($round)
			{
				case 0:
					return sqlite_unbuffered_query($this->cnDB, "select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' group by c.id, c.name");
					break;
				case 4:
					return sqlite_unbuffered_query($this->cnDB, "select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 20:
					return sqlite_unbuffered_query($this->cnDB, "select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . sqlite_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 8:
					return sqlite_unbuffered_query($this->cnDB, "select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 5:
					return sqlite_unbuffered_query($this->cnDB, "select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 24:
					return sqlite_unbuffered_query($this->cnDB, "select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' group by c.id, c.name");
					break;
			}
		}

		function newClub($name)
		{
			sqlite_exec($this->cnDB, "insert into tblClub (name) values ('" . sqlite_escape_string($name) . "')");
			
			return sqlite_last_insert_rowid($this->cnDB);
		}
		
		function swimmerList($competitionId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname, cs.distance, cs.time, s.edge, s.pilot, cs.help as devices, s.diet, cs.semitime, cs.semitimechecked, cs.finaltime from tblSwimmer s inner join tblCompetitionSwimmer cs on s.id = cs.swimmerId inner join tblClub c on c.id = s.clubId where cs.competitionId = '" . sqlite_escape_string($competitionId) . "' order by c.name, s.name, cs.distance / abs(cs.distance) desc, abs(cs.distance)");
		}
		
		function newSwimmer($name, $clubId, $edge, $pilot, $diet)
		{
			sqlite_exec($this->cnDB, "insert into tblSwimmer (name, clubId, edge, pilot, diet) values ('" . sqlite_escape_string($name) . "', '" . sqlite_escape_string($clubId) . "', '" . sqlite_escape_string($edge) . "', '" . sqlite_escape_string($pilot) . "', '" . sqlite_escape_string($diet) . "')");
			
			return sqlite_last_insert_rowid($this->cnDB);
		}

		function getSwimmer($id)
		{
			$rsSwimmer = sqlite_unbuffered_query($this->cnDB, "select * from tblSwimmer where id = '" . sqlite_escape_string($id) . "'");
			return $this->fetch_array($rsSwimmer);
		}
		
		function updateSwimmer($id, $name, $edge, $pilot, $diet, $clubId)
		{
			sqlite_exec($this->cnDB, "update tblSwimmer set name = '" . sqlite_escape_string($name) . "', edge = '" . sqlite_escape_string($edge) . "', pilot = '" . sqlite_escape_string($pilot) . "', diet = '" . sqlite_escape_string($diet) . "', clubId = '" . sqlite_escape_string($clubId) . "' where id = '" . sqlite_escape_string($id) . "'");
		}
		
		function updateCompetitionSwimmmerSemiTime($competitionId, $swimmerId, $distance, $time)
		{
			sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set semitime = '" . sqlite_escape_string($time) . "', semitimechecked = 1 where swimmerId = '" . sqlite_escape_string($swimmerId) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
		}
		
		function clearSwimmerDistances($competitionId, $swimmerId)
		{
			sqlite_exec($this->cnDB, "delete from tblCompetitionSwimmer where competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and semitime is null and finaltime = 0.0");
		}
		
		function addSwimmerDistance($competitionId, $swimmerId, $distance, $time, $help)
		{
			$rsDistance = sqlite_unbuffered_query($this->cnDB, "select * from tblCompetitionSwimmer cs where competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			
			if ($this->fetch_array($rsDistance))
			{
				sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set time = '" . sqlite_escape_string($time) . "', help = '" . sqlite_escape_string($help) . "' where competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			}
			else
			{		
				sqlite_exec($this->cnDB, "insert into tblCompetitionSwimmer (competitionId, swimmerId, distance, time, help) values ('" . sqlite_escape_string($competitionId) . "', '" . sqlite_escape_string($swimmerId) . "', '" . sqlite_escape_string($distance) . "', '" . sqlite_escape_string($time) . "', '" . sqlite_escape_string($help) . "')");
			}
		}

		function clearSwimmerDistance($competitionId, $swimmerId, $distance)
		{
			sqlite_exec($this->cnDB, "delete from tblCompetitionSwimmer where competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
		}
		
		function hasSwimmerDistance($competitionId, $swimmerId, $distance)
		{
				$rsHasDistance = sqlite_unbuffered_query($this->cnDB, "select count(*) as hasDistance from tblCompetitionSwimmer where competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
				$hasDistance = $this->fetch_array($rsHasDistance);
				
				return $hasDistance['hasDistance'] > 0;
		}
		
		function allSwimmersExcept($swimmerIds)
		{
			if ($swimmerIds == '')
			{
				$rsNewSwimmers = sqlite_unbuffered_query($this->cnDB, "select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname from tblSwimmer s inner join tblClub c on c.id = s.clubId order by c.name, s.name");
			}
			else
			{
				$rsNewSwimmers = sqlite_unbuffered_query($this->cnDB, "select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname from tblSwimmer s inner join tblClub c on c.id = s.clubId where s.id not in (" . sqlite_escape_string($swimmerIds) . ") order by c.name, s.name");
			}
			
			return $rsNewSwimmers;
		}
		
		function updateResult($raceId, $swimmerId, $time1, $time2, $position)
		{
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set result1 = '" . sqlite_escape_string($time1) . "', result2 = '" . sqlite_escape_string($time2) . "', position = '" . sqlite_escape_string($position) . "' where raceId = '" . sqlite_escape_string($raceId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "'");
		}
		
		function raceList($competitionId, $distance, $round)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblRace where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' and type = '" . sqlite_escape_string($round) . "' order by number");
		}
		
		function raceSwimmers($raceId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . sqlite_escape_string($raceId) . "' order by startTime, track");
		}
		
		function raceSwimmersOrdered($raceId, $round)
		{
			if ($round == 0)
			{
				return sqlite_unbuffered_query($this->cnDB, "select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . sqlite_escape_string($raceId) . "' order by time desc");
			}
			elseif ($round == 4 || $round == 20)
			{
				return sqlite_unbuffered_query($this->cnDB, "select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . sqlite_escape_string($raceId) . "' order by semitime desc");
			}
			else
			{
				return sqlite_unbuffered_query($this->cnDB, "select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . sqlite_escape_string($raceId) . "' order by finaltime desc");
			}
		}
		
		function noRaceSwimmers($competition, $distance)
		{
			return sqlite_unbuffered_query($this->cnDB, "select *, s.name as swimmername, c.name as clubname from tblCompetitionSwimmer cs inner join tblSwimmer s on s.id = cs.swimmerId inner join tblClub c on s.clubId = c.id where cs.swimmerId not in (select rs.swimmerId from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionid = $competition and r.distance = $distance) and cs.competitionId = $competition and cs.distance = $distance order by s.name");
		}
		
		function addRaceSwimmer($raceId, $swimmerId)
		{
			sqlite_exec($this->cnDB, "insert into tblRaceSwimmer (raceId, swimmerId) values ('" . sqlite_escape_string($raceId) . "', '" . sqlite_escape_string($swimmerId) . "')");
		}
		
		function updateStartTime($raceId, $swimmerId, $time)
		{
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set startTime = $time where swimmerId = '" . sqlite_escape_string($swimmerId) . "' and raceId = '" . sqlite_escape_string($raceId) . "'");
		}
		
		function clearRaces($competitionId, $distance, $round)
		{
			$rsRaces = $this->raceList($competitionId, $distance, $round);
	
			$raceIds = "0";
	
			while ($aryRace = $this->fetch_array($rsRaces))
			{
				$raceIds .= ", " . $aryRace['id'];
			}
	
			sqlite_exec($this->cnDB, "delete from tblRaceSwimmer where raceId in ($raceIds)");
			sqlite_exec($this->cnDB, "delete from tblRace where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' and type = '" . sqlite_escape_string($round) . "'");
		}
		
		function resetTracks($raceId)
		{
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set track = 0 where raceId = $raceId");
		}
		
		function removeEmptyTrack($raceId, $track)
		{
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set track = track - 1 where raceId = $raceId and track >= $track");
		}
		
		function insertEmptyTrack($raceId, $track)
		{
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set track = track + 1 where raceId = $raceId and track >= $track");
		}
			
		function moveSwimmerToHeat($swimmerId, $from, $to)
		{
			if ($from == 0)
			{
				sqlite_exec($this->cnDB, "insert into tblRaceSwimmer (raceId, swimmerId) values (" . sqlite_escape_string($to) . ", " . sqlite_escape_string($swimmerId) . ")");
			}
			else
			{
				sqlite_exec($this->cnDB, "update tblRaceSwimmer set track = 0 where raceId = $from");
				sqlite_exec($this->cnDB, "update tblRaceSwimmer set raceId = '" . sqlite_escape_string($to) . "' where swimmerId = '" . sqlite_escape_string($swimmerId) . "' and raceId = '" . sqlite_escape_string($from) . "'");
			}
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set track = 0 where raceId = $to");
		}
		
		function upgradeSwimmers($competitionId, $distance, $round, $howmany)
		{
			switch ($round)
			{
				case 0:
					break;
				case 4:
					$rsUpdateSwimmers = sqlite_unbuffered_query($this->cnDB, "select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "'"); 
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set semitime = '{$us['result']}', semitimechecked = 0 where swimmerId = '{$us['swimmerId']}' and distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");
					}
					break;
				case 20:
					$rsUpdateSwimmers = sqlite_unbuffered_query($this->cnDB, "select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . sqlite_escape_string($howmany) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "'");
	
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set semitime = '{$us['result']}', semitimechecked = 0 where swimmerId = '{$us['swimmerId']}' and competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
					}
					break;
				case 8:
					$rsUpdateSwimmers = sqlite_unbuffered_query($this->cnDB, "select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");
					}
					break;
				case 5:
					$rsUpdateSwimmers = sqlite_unbuffered_query($this->cnDB, "select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");
					}
					break;
				case 24:
					$rsUpdateSwimmers = sqlite_unbuffered_query($this->cnDB, "select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						sqlite_exec($this->cnDB, "update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "'");
					}
					break;
			}
		}
		
		function countSwimmersForDistance($competitionId, $distance)
		{
			$rsSwimmerCount = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblCompetitionSwimmer cs where cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "'");
			$c = $this->fetch_array($rsSwimmerCount, SQLITE_NUM);
			return $c[0];
		}
		
		function countFirstRoundHeatsForDistance($competitionId, $distance)
		{
			$rsFirstHeatCount = sqlite_unbuffered_query($this->cnDB, "select count(*) as heats from tblRace where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' and type = '0'");

			$actualHeats = $this->fetch_array($rsFirstHeatCount, SQLITE_NUM);
			return $actualHeats[0];
		}
		
		function countSwimmers($competitionId, $distance, $round, $howmany, $generate)
		{
			if (!$generate)
			{
				$rsSwimmers = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '" . sqlite_escape_string($round) . "'");
				if ($res = $this->fetch_array($rsSwimmers))
				{
					$count = $res['result'];
					
					if ($count > 0)
					{
						return $count;
					}
				}
			}

			switch($round)
			{
				case 4:
					$rsSwimmers = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "'");
					break;
				case 20:
					$rsSwimmers = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . sqlite_escape_string($howmany) . "'");
					break;
				case 5:
					$rsSwimmers = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "'");
					break;
				case 8:
					$rsSwimmers = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "'");
					break;
				case 24:
					$rsSwimmers = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "'");
					break;
			}
			
			if ($res = $this->fetch_array($rsSwimmers))
			{
				return $res['result'];
			}
			
			return null;
		}

		function countHeats($competitionId, $distance, $round)
		{
			$semiCount = sqlite_unbuffered_query($this->cnDB, "select count(*) as result from tblRace where competitionId = '" . sqlite_escape_string($competitionId) . "' and type = '" . sqlite_escape_string($round) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			$count = $this->fetch_array($semiCount, SQLITE_NUM);
			return $count[0];
		}		

		function newHeat($competitionId, $distance, $round, $name, $number)
		{
			sqlite_exec($this->cnDB, "insert into tblRace (competitionId, distance, type, name, number) values ('" . sqlite_escape_string($competitionId) . "', '" . sqlite_escape_string($distance) . "', '" . sqlite_escape_string($round) . "', '" . sqlite_escape_string($name) . "', '" . sqlite_escape_string($number) . "')");
			return sqlite_last_insert_rowid($this->cnDB);
		}
		
		function deleteHeat($competitionId, $distance, $round, $number)
		{
			sqlite_exec($this->cnDB, "delete from tblRace where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' and type = '" . sqlite_escape_string($round) . "' and number = '" . sqlite_escape_string($number) . "'");
			sqlite_exec($this->cnDB, "update tblRace set number = number - 1 where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' and type = '" . sqlite_escape_string($round) . "' and number > '" . sqlite_escape_string($number) . "'");
		}
		
		function getBestTime($competitionId, $swimmerId, $distance)
		{
			return sqlite_unbuffered_query($this->cnDB, "select min((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where ((result1 + result2) / 2) > 0 and position < 999 and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
		}
		
		function getRandomSwimmer($competitionId, $distance, $round, $howmany, $clubId, $usedswimmers)
		{
			switch ($round)
			{
				case 0:
					$rsRndSwimmer = sqlite_query($this->cnDB, "select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and s.clubId = '" . sqlite_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 4:
					$rsRndSwimmer = sqlite_query($this->cnDB, "select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and s.clubId = '" . sqlite_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 20:
					$rsRndSwimmer = sqlite_query($this->cnDB, "select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . sqlite_escape_string($howmany) . "' and s.clubId = '" . sqlite_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 8:
					$rsRndSwimmer = sqlite_query($this->cnDB, "select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and s.clubId = '" . sqlite_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 24:
					$rsRndSwimmer = sqlite_query($this->cnDB, "select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and s.clubId = '" . sqlite_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 5:
					$rsRndSwimmer = sqlite_query($this->cnDB, "select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and cs.distance = '" . sqlite_escape_string($distance) . "' and r.distance = '" . sqlite_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . sqlite_escape_string($howmany) . "' and s.clubId = '" . sqlite_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
			}
	
			$rndSwimmers = sqlite_num_rows($rsRndSwimmer);
	
			$rand = rand(0, $rndSwimmers - 1);
	
			sqlite_seek($rsRndSwimmer, $rand);
	
			return $this->fetch_array($rsRndSwimmer);
		}
		
		function teamSwimmers($teamId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblSwimmer s inner join tblTeamSwimmer ts on ts.swimmerId = s.id where teamId = '" . sqlite_escape_string($teamId) . "'"); // sqlite_unbuffered_query($this->cnDB, "select * from tblTeamSwimmer where teamId = '" . sqlite_escape_string($teamId) . "'");
		}
		
		function newTeam($competitionId, $clubId, $distance)
		{
			sqlite_exec($this->cnDB, "insert into tblTeam (clubId, competitionId, distance) values ('" . sqlite_escape_string($clubId) . "', '" . sqlite_escape_string($competitionId) . "', '" . sqlite_escape_string($distance) . "')");
			
			return sqlite_last_insert_rowid($this->cnDB);
		}
		
		function teamSwimmerList($competitionId, $distance, $swimmers)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblCompetitionSwimmer where distance = '" . sqlite_escape_string($distance) . "' and competitionId = '" . sqlite_escape_string($competitionId) . "' and swimmerId in (" . sqlite_escape_string($swimmers) . ")");
		}
		
		function updateTeamtime($teamId, $time)
		{
			sqlite_exec($this->cnDB, "update tblTeam set time = '" . sqlite_escape_string($time) . "' where id = '" . sqlite_escape_string($teamId) . "'");
		}
		
		function getSwimmerBestTime($competitionId, $swimmerId, $distance)
		{
			$rsResult = sqlite_unbuffered_query($this->cnDB, "select min((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where (result1 + result2 / 2) > 0 and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			
			return $this->fetch_array($rsResult);
		}
		
		function addTeamSwimmer($teamId, $swimmerId, $time)
		{
			sqlite_exec($this->cnDB, "insert into tblTeamSwimmer (teamId, swimmerId, time) values ('" . sqlite_escape_string($teamId) . "', '" . sqlite_escape_string($swimmerId) . "', '" . sqlite_escape_string($time) . "')");
		}
		
		function updateTeamSwimmerTime($teamId, $swimmerId, $time)
		{
			sqlite_exec($this->cnDB, "update tblTeamSwimmer set time = '" . sqlite_escape_string($time) . "' where teamId = '" . sqlite_escape_string($teamId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "'");
		}
		
		function deleteTeam($competitionId, $teamId)
		{
			if (sqlite_exec($this->cnDB, "delete from tblTeam where competitionId = '" . sqlite_escape_string($competitionId) . "' and id = '" . sqlite_escape_string($teamId) . "'"))
			{
				sqlite_exec($this->cnDB, "delete from tblTeamSwimmer where teamId = '" . sqlite_escape_string($teamId) . "'");
			}
		}
		
		function teamList($competitionId, $distance)
		{
			return sqlite_unbuffered_query($this->cnDB, "select t.id as tid, c.*, t.*, c.name cname from tblTeam t inner join tblClub c on t.clubId = c.id where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' order by t.time desc");
		}
		
		function teamClubListExcept($competitionId, $clubs)
		{
			return sqlite_unbuffered_query($this->cnDB, "select distinct c.id, c.name from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and c.id not in (" . sqlite_escape_string(implode(', ', $clubs)) . ") order by c.name");
		}
		
		function teamSwimmerListExcept($competitionId, $distance, $swimmers)
		{
			return sqlite_unbuffered_query($this->cnDB, "select s.id as sid, s.name as sname, c.name as cname from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '-" . sqlite_escape_string($distance) . "' and s.id not in (" . sqlite_escape_string(implode(', ', $swimmers)) . ") order by c.name, s.name");
		}
		
		function updateTeamResult($teamId, $time1, $time2, $position)
		{
			sqlite_exec($this->cnDB, "update tblTeam set result1 = '" . sqlite_escape_string($time1) . "', result2 = '" . sqlite_escape_string($time2) . "', place = '" . sqlite_escape_string($position) . "' where id = '" . sqlite_escape_string($teamId) . "'");
		}
		
		function prizeList()
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblPrize");
		}
		
		function getPrizeDef($id)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblPrize where id = $id");
		}
		
		function addPrize($name, $query)
		{
			sqlite_query($this->cnDB, "insert into tblPrize (name, restriction) values ('" . sqlite_escape_string($name) . "', '" . sqlite_escape_string($query) . "');");
		}
		
		function updatePrize($id, $name, $query)
		{
			sqlite_query($this->cnDB, "update tblPrize set name = '" . sqlite_escape_string($name) . "', restriction = '" . sqlite_escape_string($query) . "' where id = $id;");
		}
		
		function deletePrize($id)
		{
			sqlite_query($this->cnDB, "delete from tblPrize where id = $id;");
		}
		
		function getPrize($competitionId, $restriction)
		{
			return sqlite_unbuffered_query($this->cnDB, "select s.name as swimmername, c.name as clubname from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblRaceSwimmer rs on s.id = rs.swimmerId inner join tblRace r on r.id = rs.raceId inner join tblCompetitionSwimmer cs on s.id = cs.swimmerId and cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and r.distance = cs.distance where r.competitionId = '" . sqlite_escape_string($competitionId) . "' and $restriction limit 0, 1");
		}
		
		function diplomaSwimmers($competitionId, $swimmerList)
		{
			return sqlite_unbuffered_query($this->cnDB, "select s.id as sid, c.id as cid, s.name as sname, c.name as cname, cs.distance, s.*, c.* from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblClub c on c.id = s.clubId where cs.competitionId = '" . sqlite_escape_string($competitionId) . "' and s.id in (" . sqlite_escape_string(implode(',', $swimmerList)) . ") order by c.name, s.name, distance");
		}
		
		function diplomaSwimmerPosition($competitionId, $swimmerId, $distance)
		{
			return sqlite_unbuffered_query($this->cnDB, "select r.type, rs.position from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where (result1 + result2 / 2) > 0 and position < 999 and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and r.competitionId = '" . sqlite_escape_string($competitionId) . "' and distance = '" . sqlite_escape_string($distance) . "' and (r.type = 8 or r.type = 24 or r.type = 5)");
		}
		
		function diplomaTeams($teamList)
		{
			return sqlite_unbuffered_query($this->cnDB, "select t.id tid, t.*, c.* from tblTeam t inner join tblClub c on c.id = t.clubId where t.id in (" . implode(',', $teamList) . ")");
		}
		
		function diplomaTeamSwimmers($teamId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblTeamSwimmer ts inner join tblSwimmer s on s.id = ts.swimmerId where ts.teamId = {$teamId} order by name");
		}
		
		function setupPrint()
		{
			$rs = sqlite_unbuffered_query($this->cnDB, "select count(*) as c from sqlite_master where type = 'table' and name = 'tblPrints'");
			
			if ($row = sqlite_fetch_array($rs))
			{
				if ($row['c'] == 0)
				{
					sqlite_query($this->cnDB, "CREATE TABLE tblPrints (id INTEGER NOT NULL, name TEXT NOT NULL DEFAULT '', query TEXT NOT NULL DEFAULT '', PRIMARY KEY (id));");
					sqlite_query($this->cnDB, 'insert into tblPrints (name, query) values (\'Dagens tider\', \'select s.name as Navn, c.name as Klub, rs1.distance as Distance, (rs1.result1 + rs1.result2) / 2 - rs1.startTime as [1.], (rs2.result1 + rs2.result2) / 2 - rs2.startTime as [2.], (rs3.result1 + rs3.result2) / 2 - rs3.startTime as [3.] from tblSwimmer s inner join tblClub c on c.id = s.clubId inner join (tblRaceSwimmer rs1 inner join tblRace r1 on r1.id = rs1.raceId and r1.type = 0 and r1.competitionId = $compo$) rs1 on rs1.swimmerId = s.id and rs1.result1 > 0 left join (tblRaceSwimmer rs2 inner join tblRace r2 on r2.id = rs2.raceId and (r2.type = 4 or r2.type = 20) and r2.competitionId = $compo$) rs2 on rs2.swimmerId = s.id and rs2.result1 > 0 and rs2.distance = rs1.distance left join (tblRaceSwimmer rs3 inner join tblRace r3 on r3.id = rs3.raceId and r3.type in (8, 24, 5) and r3.competitionId = $compo$) rs3 on rs3.swimmerId = s.id and rs3.result1 > 0 and rs3.distance = rs1.distance order by c.name, s.name\')');
				}
			}
		}

		function getPrint($id)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblPrints where id = $id;");
		}
		
		function addPrint($name, $query)
		{
			sqlite_query($this->cnDB, "insert into tblPrints (name, query) values ('" . sqlite_escape_string($name) . "', '" . sqlite_escape_string($query) . "');");
		}
		
		function updatePrint($id, $name, $query)
		{
			sqlite_query($this->cnDB, "update tblPrints set name = '" . sqlite_escape_string($name) . "', query = '" . sqlite_escape_string($query) . "' where id = $id;");
		}
		
		function deletePrint($id)
		{
			sqlite_query($this->cnDB, "delete from tblPrints where id = $id;");
		}
		
		function printList()
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblPrints order by name;");
		}
		
		function printQuery($id, $query)
		{
			$rsQuery = $this->getPrint($query);
			
			if ($query = sqlite_fetch_array($rsQuery))
			{
				return sqlite_unbuffered_query($this->cnDB, str_replace('$compo$', $id, $query['query']));
			}
		}
		
		function setTrack($raceId, $swimmerId, $track)
		{
			sqlite_exec($this->cnDB, "update tblRaceSwimmer set track = $track where raceId = $raceId and swimmerId = $swimmerId");
		}
	
		function setupSignup()
		{
			$rs = sqlite_unbuffered_query($this->cnDB, "select count(*) as c from sqlite_master where type = 'table' and name = 'tblSignUp'");
			
			if ($row = sqlite_fetch_array($rs))
			{
				if ($row['c'] == 0)
				{
					sqlite_query($this->cnDB, "CREATE TABLE tblSignUp (id INTEGER NOT NULL, name TEXT NOT NULL DEFAULT '', date TEXT NOT NULL DEFAULT '0000-00-00', PRIMARY KEY (id));");
					sqlite_query($this->cnDB, "CREATE TABLE tblSignUpSwimmer (signUpId INTEGER NOT NULL DEFAULT '0', swimmerId INTEGER NOT NULL DEFAULT '0', distance INTEGER NOT NULL DEFAULT '0', time NUMERIC(5,2) NOT NULL DEFAULT '0.00', help TEXT NOT NULL DEFAULT '', PRIMARY KEY (signUpId, swimmerId, distance));");
				}
			}
		}
				
		function signupList()
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblSignUp order by date desc");
		}
		
		function newSignup($name, $date)
		{
			sqlite_exec($this->cnDB, "insert into tblSignUp (name, date) values ('" . sqlite_escape_string($name) . "', '" . sqlite_escape_string($date) . "')");
			return sqlite_last_insert_rowid($this->cnDB);
		}
		
		function getSignup($id)
		{
			$rsCompo = sqlite_unbuffered_query($this->cnDB, "select * from tblSignUp where id = '$id';");
			return $this->fetch_array($rsCompo);
		}
		
		function addSignupSwimmerDistance($signupId, $swimmerId, $distance, $time, $help)
		{
			$rsDistance = sqlite_unbuffered_query($this->cnDB, "select * from tblSignUpSwimmer cs where signUpId = '" . sqlite_escape_string($signupId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			
			if ($this->fetch_array($rsDistance))
			{
				sqlite_exec($this->cnDB, "update tblSignUpSwimmer set time = '" . sqlite_escape_string($time) . "', help = '" . sqlite_escape_string($help) . "' where signUpId = '" . sqlite_escape_string($signupId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "' and distance = '" . sqlite_escape_string($distance) . "'");
			}
			else
			{		
				sqlite_exec($this->cnDB, "insert into tblSignUpSwimmer (signUpId, swimmerId, distance, time, help) values ('" . sqlite_escape_string($signupId) . "', '" . sqlite_escape_string($swimmerId) . "', '" . sqlite_escape_string($distance) . "', '" . sqlite_escape_string($time) . "', '" . sqlite_escape_string($help) . "')");
			}
		}
				
		function signupSwimmerList($signupId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname, cs.distance, cs.time, s.edge, s.pilot, cs.help as devices, s.diet from tblSwimmer s inner join tblSignUpSwimmer cs on s.id = cs.swimmerId inner join tblClub c on c.id = s.clubId where cs.signUpId = '" . sqlite_escape_string($signupId) . "' order by c.name, s.name, cs.distance / abs(cs.distance) desc, abs(cs.distance)");
		}
		
		function signupSwimmerTimes($signupId, $swimmerId)
		{
			return sqlite_unbuffered_query($this->cnDB, "select * from tblSignUpSwimmer where signUpId = '" . sqlite_escape_string($signupId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "'");
		}

		function clearSignupSwimmerDistances($signupId, $swimmerId)
		{
			sqlite_exec($this->cnDB, "delete from tblSignUpSwimmer where signUpId = '" . sqlite_escape_string($signupId) . "' and swimmerId = '" . sqlite_escape_string($swimmerId) . "'");
		}
		
		function findOrCreateClub($clubName)
		{
			$rsClub = sqlite_unbuffered_query($this->cnDB, "select id from tblClub where name = '" . sqlite_escape_string($clubName) . "'");
			
			if ($clubId = $this->fetch_array($rsClub))
			{
				return $clubId['id'];
			}
			else
			{
				return newClub($clubName);
			}
		}
		
		function findOrCreateSwimmer($name, $clubName, $edge, $pilot, $diet)
		{
			$clubId = $this->findOrCreateClub($clubName);
			$rsSwimmer = sqlite_unbuffered_query($this->cnDB, "select id from tblSwimmer where clubId = '" . sqlite_escape_string($clubId) . "' and name = '" . sqlite_escape_string($name) . "'");
			
			if ($swimmerId = $this->fetch_array($rsSwimmer))
			{
				return $swimmerId['id'];
			}
			else
			{
				return newSwimmer($name, $clubId, $edge, $pilot, $diet);
			}
		}
	}
?>