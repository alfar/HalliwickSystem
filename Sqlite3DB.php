<?PHP
	class Sqlite3DB
	{
		function Sqlite3DB($server, $username, $password, $db)
		{
			$this->server = $server;
			$this->username = $username;
			$this->password = $password;
			$this->db = $db;
		}
		
		function connect()
		{
			$ready = file_exists($this->db);
			$this->cnDB = new SQLite3($this->db);
			if (!$ready)
			{
				$this->cnDB->exec(file_get_contents('db/sqlitedb.sql'));
			}
			
			$this->cnDB->exec('PRAGMA short_column_names = on');
		}
		
		function fetch_array($recordset, $type = SQLITE3_ASSOC)
		{
			return $recordset->fetchArray($type);
		}
		
		function close()
		{
			$this->cnDB->close();
		}
		
		function competitionList()
		{
			return $this->cnDB->query('select * from tblCompetition order by date desc');
		}
		
		function newCompetition($name, $date, $leader, $extra100, $tracks)
		{
			$this->cnDB->exec("insert into tblCompetition (name, date, leader, extra100, tracks) values ('" . $this->cnDB->escapeString($name) . "', '" . $this->cnDB->escapeString($date) . "', '" . $this->cnDB->escapeString($leader) . "', '" . $this->cnDB->escapeString($extra100) . "', '" . $this->cnDB->escapeString($tracks) . "')");
			
			return $this->cnDB->lastInsertRowID();
		}

		function getCompetition($id)
		{
			$rsCompo = $this->cnDB->query("select * from tblCompetition where id = '" . $this->cnDB->escapeString($id) . "'");
			return $this->fetch_array($rsCompo);
		}
		
		function updateCompetition($id, $name, $date, $leader, $extra100, $tracks)
		{
			$this->cnDB->exec("update tblCompetition set name = '" . $this->cnDB->escapeString($name) . "', date = '" . $this->cnDB->escapeString($date) . "', leader = '" . $this->cnDB->escapeString($leader) . "', extra100 = '" . $this->cnDB->escapeString($extra100) . "', tracks = '" . $this->cnDB->escapeString($tracks) . "' where id = '" . $this->cnDB->escapeString($id) . "';");
		}
		
		function updateHowMany($competitionId, $distance, $round, $howmany)
		{
			switch($round)
			{
				case 4:
					$this->cnDB->exec("update tblCompetition set howmanySemi" . $this->cnDB->escapeString($distance) . " = '" . $this->cnDB->escapeString($howmany) . "' where id = '" . $this->cnDB->escapeString($competitionId) . "'");
					break;
				case 5:
					$this->cnDB->exec("update tblCompetition set howmanyFinal" . $this->cnDB->escapeString($distance) . " = '" . $this->cnDB->escapeString($howmany) . "' where id = '" . $this->cnDB->escapeString($competitionId) . "'");
					break;
				case 8:
					$this->cnDB->exec("update tblCompetition set howmanyFinal" . $this->cnDB->escapeString($distance) . " = '" . $this->cnDB->escapeString($howmany) . "' where id = '" . $this->cnDB->escapeString($competitionId) . "'");
					break;
				case 20:
					$this->cnDB->exec("update tblCompetition set howmanySemiExtra" . $this->cnDB->escapeString($distance) . " = '" . $this->cnDB->escapeString($howmany) . "' where id = '" . $this->cnDB->escapeString($competitionId) . "'");
					break;
				case 24:
					$this->cnDB->exec("update tblCompetition set howmanyFinalExtra" . $this->cnDB->escapeString($distance) . " = '" . $this->cnDB->escapeString($howmany) . "' where id = '" . $this->cnDB->escapeString($competitionId) . "'");
					break;				
			}
		}
		
		function deleteCompetitionSwimmer($competitionId, $swimmerId)
		{
			$this->cnDB->exec("delete from tblCompetitionSwimmer where swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");

			$rsRaces = $this->competitionRaces($competitionId);

			$raceIds = array();
			
			while ($race = $this->fetch_array($rsRaces))
			{
				$raceIds[] = $race['id'];
			}
			
			$this->cnDB->exec("delete from tblRaceSwimmer where swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and raceId in (" . implode(',', $raceIds) . ")");
		}

		function competitionRaces($competitionId)
		{
			return $this->cnDB->query("select id from tblRace where competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
		}
		
		function competitionSwimmerTimes($competitionId, $swimmerId)
		{
			return $this->cnDB->query("select * from tblCompetitionSwimmer where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "'");
		}
		
		function upgradeSemiFinal($competitionId, $distance)
		{
			$rsFinals = $this->cnDB->query("select id from tblRace where distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and (type = 8 or type = 5)");
			
			while ($final = $this->fetch_array($rsFinals))
			{
				$this->cnDB->exec("delete from tblRaceSwimmer where raceId = {$final['id']}");
				$this->cnDB->exec("delete from tblRace where id = {$final['id']}");
			}

			$rsUpdateSwimmers = $this->cnDB->query("select swimmerId from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.type = 4 and distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
			
			while ($us = $this->fetch_array($rsUpdateSwimmers))
			{
				$this->cnDB->exec("update tblCompetitionSwimmer set finaltime = semitime where swimmerId = {$us['swimmerId']} and distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
			}
			
			$this->cnDB->exec("update tblRace set type = 8 where type = 4 and competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			$this->cnDB->exec("update tblCompetition set howmanyFinal$distance = howmanySemi$distance where id = '" . $this->cnDB->escapeString($competitionId) . "';");
		}
		
		function clubList()
		{
			return $this->cnDB->query("select id, name from tblClub");
		}
		
		function clubListForDistance($competitionId, $distance, $round, $howmany)
		{
			switch ($round)
			{
				case 0:
					return $this->cnDB->query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' group by c.id, c.name");
					break;
				case 4:
					return $this->cnDB->query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' group by c.id, c.name");
					break;
				case 20:
					return $this->cnDB->query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . $this->cnDB->escapeString($howmany) . "' group by c.id, c.name");
					break;
				case 8:
					return $this->cnDB->query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' group by c.id, c.name");
					break;
				case 5:
					return $this->cnDB->query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' group by c.id, c.name");
					break;
				case 24:
					return $this->cnDB->query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' group by c.id, c.name");
					break;
			}
		}

		function newClub($name)
		{
			$this->cnDB->exec("insert into tblClub (name) values ('" . $this->cnDB->escapeString($name) . "')");
			
			return $this->cnDB->lastInsertRowID();
		}
		
		function swimmerList($competitionId)
		{
			return $this->cnDB->query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname, cs.distance, cs.time, s.edge, s.pilot, cs.help as devices, s.diet, cs.semitime, cs.semitimechecked, cs.finaltime from tblSwimmer s inner join tblCompetitionSwimmer cs on s.id = cs.swimmerId inner join tblClub c on c.id = s.clubId where cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' order by c.name, s.name, cs.distance / abs(cs.distance) desc, abs(cs.distance)");
		}
		
		function newSwimmer($name, $clubId, $edge, $pilot, $diet)
		{
			$this->cnDB->exec("insert into tblSwimmer (name, clubId, edge, pilot, diet) values ('" . $this->cnDB->escapeString($name) . "', '" . $this->cnDB->escapeString($clubId) . "', '" . $this->cnDB->escapeString($edge) . "', '" . $this->cnDB->escapeString($pilot) . "', '" . $this->cnDB->escapeString($diet) . "')");
			
			return $this->cnDB->lastInsertRowID();
		}

		function getSwimmer($id)
		{
			$rsSwimmer = $this->cnDB->query("select * from tblSwimmer where id = '" . $this->cnDB->escapeString($id) . "'");
			return $this->fetch_array($rsSwimmer);
		}
		
		function updateSwimmer($id, $name, $edge, $pilot, $diet, $clubId)
		{
			$this->cnDB->exec("update tblSwimmer set name = '" . $this->cnDB->escapeString($name) . "', edge = '" . $this->cnDB->escapeString($edge) . "', pilot = '" . $this->cnDB->escapeString($pilot) . "', diet = '" . $this->cnDB->escapeString($diet) . "', clubId = '" . $this->cnDB->escapeString($clubId) . "' where id = '" . $this->cnDB->escapeString($id) . "'");
		}
		
		function updateCompetitionSwimmmerSemiTime($competitionId, $swimmerId, $distance, $time)
		{
			$this->cnDB->exec("update tblCompetitionSwimmer set semitime = '" . $this->cnDB->escapeString($time) . "', semitimechecked = 1 where swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
		}
		
		function clearSwimmerDistances($competitionId, $swimmerId)
		{
			$this->cnDB->exec("delete from tblCompetitionSwimmer where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and semitime is null and finaltime = 0.0");
		}
		
		function addSwimmerDistance($competitionId, $swimmerId, $distance, $time, $help)
		{
			$rsDistance = $this->cnDB->query("select * from tblCompetitionSwimmer cs where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			
			if ($this->fetch_array($rsDistance))
			{
				$this->cnDB->exec("update tblCompetitionSwimmer set time = '" . $this->cnDB->escapeString($time) . "', help = '" . $this->cnDB->escapeString($help) . "' where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			}
			else
			{		
				$this->cnDB->exec("insert into tblCompetitionSwimmer (competitionId, swimmerId, distance, time, help) values ('" . $this->cnDB->escapeString($competitionId) . "', '" . $this->cnDB->escapeString($swimmerId) . "', '" . $this->cnDB->escapeString($distance) . "', '" . $this->cnDB->escapeString($time) . "', '" . $this->cnDB->escapeString($help) . "')");
			}
		}

		function clearSwimmerDistance($competitionId, $swimmerId, $distance)
		{
			$this->cnDB->exec("delete from tblCompetitionSwimmer where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
		}
		
		function hasSwimmerDistance($competitionId, $swimmerId, $distance)
		{
				$rsHasDistance = $this->cnDB->query("select count(*) as hasDistance from tblCompetitionSwimmer where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
				$hasDistance = $this->fetch_array($rsHasDistance);
				
				return $hasDistance['hasDistance'] > 0;
		}
		
		function allSwimmersExcept($swimmerIds)
		{
			if ($swimmerIds == '')
			{
				$rsNewSwimmers = $this->cnDB->query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname from tblSwimmer s inner join tblClub c on c.id = s.clubId order by c.name, s.name");
			}
			else
			{
				$rsNewSwimmers = $this->cnDB->query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname from tblSwimmer s inner join tblClub c on c.id = s.clubId where s.id not in (" . $this->cnDB->escapeString($swimmerIds) . ") order by c.name, s.name");
			}
			
			return $rsNewSwimmers;
		}
		
		function updateResult($raceId, $swimmerId, $time1, $time2, $position)
		{
			$this->cnDB->exec("update tblRaceSwimmer set result1 = '" . $this->cnDB->escapeString($time1) . "', result2 = '" . $this->cnDB->escapeString($time2) . "', position = '" . $this->cnDB->escapeString($position) . "' where raceId = '" . $this->cnDB->escapeString($raceId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "'");
		}
		
		function raceList($competitionId, $distance, $round)
		{
			return $this->cnDB->query("select * from tblRace where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' and type = '" . $this->cnDB->escapeString($round) . "' order by number");
		}
		
		function raceSwimmers($raceId)
		{
			return $this->cnDB->query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . $this->cnDB->escapeString($raceId) . "' order by startTime, track");
		}
		
		function raceSwimmersOrdered($raceId, $round)
		{
			if ($round == 0)
			{
				return $this->cnDB->query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . $this->cnDB->escapeString($raceId) . "' order by time desc");
			}
			elseif ($round == 4 || $round == 20)
			{
				return $this->cnDB->query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . $this->cnDB->escapeString($raceId) . "' order by semitime desc");
			}
			else
			{
				return $this->cnDB->query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . $this->cnDB->escapeString($raceId) . "' order by finaltime desc");
			}
		}
		
		function noRaceSwimmers($competition, $distance)
		{
			return $this->cnDB->query("select *, s.name as swimmername, c.name as clubname from tblCompetitionSwimmer cs inner join tblSwimmer s on s.id = cs.swimmerId inner join tblClub c on s.clubId = c.id where cs.swimmerId not in (select rs.swimmerId from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionid = $competition and r.distance = $distance) and cs.competitionId = $competition and cs.distance = $distance order by s.name");
		}
		
		function addRaceSwimmer($raceId, $swimmerId)
		{
			$this->cnDB->exec("insert into tblRaceSwimmer (raceId, swimmerId) values ('" . $this->cnDB->escapeString($raceId) . "', '" . $this->cnDB->escapeString($swimmerId) . "')");
		}
		
		function updateStartTime($raceId, $swimmerId, $time)
		{
			$this->cnDB->exec("update tblRaceSwimmer set startTime = $time where swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and raceId = '" . $this->cnDB->escapeString($raceId) . "'");
		}
		
		function clearRaces($competitionId, $distance, $round)
		{
			$rsRaces = $this->raceList($competitionId, $distance, $round);
	
			$raceIds = "0";
	
			while ($aryRace = $this->fetch_array($rsRaces))
			{
				$raceIds .= ", " . $aryRace['id'];
			}
	
			$this->cnDB->exec("delete from tblRaceSwimmer where raceId in ($raceIds)");
			$this->cnDB->exec("delete from tblRace where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' and type = '" . $this->cnDB->escapeString($round) . "'");
		}
		
		function resetTracks($raceId)
		{
			$this->cnDB->exec("update tblRaceSwimmer set track = 0 where raceId = $raceId");
		}
		
		function removeEmptyTrack($raceId, $track)
		{
			$this->cnDB->exec("update tblRaceSwimmer set track = track - 1 where raceId = $raceId and track >= $track");
		}
		
		function insertEmptyTrack($raceId, $track)
		{
			$this->cnDB->exec("update tblRaceSwimmer set track = track + 1 where raceId = $raceId and track >= $track");
		}
			
		function moveSwimmerToHeat($swimmerId, $from, $to)
		{
			if ($from == 0)
			{
				$this->cnDB->exec("insert into tblRaceSwimmer (raceId, swimmerId) values (" . $this->cnDB->escapeString($to) . ", " . $this->cnDB->escapeString($swimmerId) . ")");
			}
			else
			{
				$this->cnDB->exec("update tblRaceSwimmer set track = 0 where raceId = $from");
				$this->cnDB->exec("update tblRaceSwimmer set raceId = '" . $this->cnDB->escapeString($to) . "' where swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and raceId = '" . $this->cnDB->escapeString($from) . "'");
			}
			$this->cnDB->exec("update tblRaceSwimmer set track = 0 where raceId = $to");
		}
		
		function upgradeSwimmers($competitionId, $distance, $round, $howmany)
		{
			switch ($round)
			{
				case 0:
					break;
				case 4:
					$rsUpdateSwimmers = $this->cnDB->query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "'"); 
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						$this->cnDB->exec("update tblCompetitionSwimmer set semitime = '{$us['result']}', semitimechecked = 0 where swimmerId = '{$us['swimmerId']}' and distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					}
					break;
				case 20:
					$rsUpdateSwimmers = $this->cnDB->query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . $this->cnDB->escapeString($howmany) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
	
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						$this->cnDB->exec("update tblCompetitionSwimmer set semitime = '{$us['result']}', semitimechecked = 0 where swimmerId = '{$us['swimmerId']}' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
					}
					break;
				case 8:
					$rsUpdateSwimmers = $this->cnDB->query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						$this->cnDB->exec("update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					}
					break;
				case 5:
					$rsUpdateSwimmers = $this->cnDB->query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						$this->cnDB->exec("update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					}
					break;
				case 24:
					$rsUpdateSwimmers = $this->cnDB->query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						$this->cnDB->exec("update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "'");
					}
					break;
			}
		}
		
		function countSwimmersForDistance($competitionId, $distance)
		{
			$rsSwimmerCount = $this->cnDB->query("select count(*) as result from tblCompetitionSwimmer cs where cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "'");
			$c = $this->fetch_array($rsSwimmerCount, SQLITE3_NUM);
			return $c[0];
		}
		
		function countFirstRoundHeatsForDistance($competitionId, $distance)
		{
			$rsFirstHeatCount = $this->cnDB->query("select count(*) as heats from tblRace where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' and type = '0'");

			$actualHeats = $this->fetch_array($rsFirstHeatCount, SQLITE3_NUM);
			return $actualHeats[0];
		}
		
		function countSwimmers($competitionId, $distance, $round, $howmany, $generate)
		{
			if (!$generate)
			{
				$rsSwimmers = $this->cnDB->query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '" . $this->cnDB->escapeString($round) . "'");
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
					$rsSwimmers = $this->cnDB->query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "'");
					break;
				case 20:
					$rsSwimmers = $this->cnDB->query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . $this->cnDB->escapeString($howmany) . "'");
					break;
				case 5:
					$rsSwimmers = $this->cnDB->query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "'");
					break;
				case 8:
					$rsSwimmers = $this->cnDB->query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "'");
					break;
				case 24:
					$rsSwimmers = $this->cnDB->query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "'");
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
			$semiCount = $this->cnDB->query("select count(*) as result from tblRace where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and type = '" . $this->cnDB->escapeString($round) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			$count = $this->fetch_array($semiCount, SQLITE3_NUM);
			return $count[0];
		}		

		function newHeat($competitionId, $distance, $round, $name, $number)
		{
			$this->cnDB->exec("insert into tblRace (competitionId, distance, type, name, number) values ('" . $this->cnDB->escapeString($competitionId) . "', '" . $this->cnDB->escapeString($distance) . "', '" . $this->cnDB->escapeString($round) . "', '" . $this->cnDB->escapeString($name) . "', '" . $this->cnDB->escapeString($number) . "')");
			return $this->cnDB->lastInsertRowID();
		}
		
		function deleteHeat($competitionId, $distance, $round, $number)
		{
			$this->cnDB->exec("delete from tblRace where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' and type = '" . $this->cnDB->escapeString($round) . "' and number = '" . $this->cnDB->escapeString($number) . "'");
			$this->cnDB->exec("update tblRace set number = number - 1 where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' and type = '" . $this->cnDB->escapeString($round) . "' and number > '" . $this->cnDB->escapeString($number) . "'");
		}
		
		function getBestTime($competitionId, $swimmerId, $distance)
		{
			return $this->cnDB->query("select min((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where ((result1 + result2) / 2) > 0 and position < 999 and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
		}
		
		function getRandomSwimmer($competitionId, $distance, $round, $howmany, $clubId, $usedswimmers)
		{
			switch ($round)
			{
				case 0:
					$rsRndSwimmer = $this->cnDB->query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and s.clubId = '" . $this->cnDB->escapeString($clubId) . "' and s.id not in ($usedswimmers) order by random() limit 1");
					break;
				case 4:
					$rsRndSwimmer = $this->cnDB->query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and s.clubId = '" . $this->cnDB->escapeString($clubId) . "' and s.id not in ($usedswimmers) order by random() limit 1");
					break;
				case 20:
					$rsRndSwimmer = $this->cnDB->query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . $this->cnDB->escapeString($howmany) . "' and s.clubId = '" . $this->cnDB->escapeString($clubId) . "' and s.id not in ($usedswimmers) order by random() limit 1");
					break;
				case 8:
					$rsRndSwimmer = $this->cnDB->query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and s.clubId = '" . $this->cnDB->escapeString($clubId) . "' and s.id not in ($usedswimmers) order by random() limit 1");
					break;
				case 24:
					$rsRndSwimmer = $this->cnDB->query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and s.clubId = '" . $this->cnDB->escapeString($clubId) . "' and s.id not in ($usedswimmers) order by random() limit 1");
					break;
				case 5:
					$rsRndSwimmer = $this->cnDB->query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and cs.distance = '" . $this->cnDB->escapeString($distance) . "' and r.distance = '" . $this->cnDB->escapeString($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . $this->cnDB->escapeString($howmany) . "' and s.clubId = '" . $this->cnDB->escapeString($clubId) . "' and s.id not in ($usedswimmers) order by random() limit 1");
					break;
			}
			return $this->fetch_array($rsRndSwimmer);
		}
		
		function teamSwimmers($teamId)
		{
			return $this->cnDB->query("select * from tblSwimmer s inner join tblTeamSwimmer ts on ts.swimmerId = s.id where teamId = '" . $this->cnDB->escapeString($teamId) . "'"); // $this->cnDB->query("select * from tblTeamSwimmer where teamId = '" . $this->cnDB->escapeString($teamId) . "'");
		}
		
		function newTeam($competitionId, $clubId, $distance)
		{
			$this->cnDB->exec("insert into tblTeam (clubId, competitionId, distance) values ('" . $this->cnDB->escapeString($clubId) . "', '" . $this->cnDB->escapeString($competitionId) . "', '" . $this->cnDB->escapeString($distance) . "')");
			
			return $this->cnDB->lastInsertRowID();
		}
		
		function teamSwimmerList($competitionId, $distance, $swimmers)
		{
			return $this->cnDB->query("select * from tblCompetitionSwimmer where distance = '" . $this->cnDB->escapeString($distance) . "' and competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and swimmerId in (" . $this->cnDB->escapeString($swimmers) . ")");
		}
		
		function updateTeamtime($teamId, $time)
		{
			$this->cnDB->exec("update tblTeam set time = '" . $this->cnDB->escapeString($time) . "' where id = '" . $this->cnDB->escapeString($teamId) . "'");
		}
		
		function getSwimmerBestTime($competitionId, $swimmerId, $distance)
		{
			$rsResult = $this->cnDB->query("select min((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where (result1 + result2 / 2) > 0 and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			
			return $this->fetch_array($rsResult);
		}
		
		function addTeamSwimmer($teamId, $swimmerId, $time)
		{
			$this->cnDB->exec("insert into tblTeamSwimmer (teamId, swimmerId, time) values ('" . $this->cnDB->escapeString($teamId) . "', '" . $this->cnDB->escapeString($swimmerId) . "', '" . $this->cnDB->escapeString($time) . "')");
		}
		
		function updateTeamSwimmerTime($teamId, $swimmerId, $time)
		{
			$this->cnDB->exec("update tblTeamSwimmer set time = '" . $this->cnDB->escapeString($time) . "' where teamId = '" . $this->cnDB->escapeString($teamId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "'");
		}
		
		function deleteTeam($competitionId, $teamId)
		{
			if ($this->cnDB->exec("delete from tblTeam where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and id = '" . $this->cnDB->escapeString($teamId) . "'"))
			{
				$this->cnDB->exec("delete from tblTeamSwimmer where teamId = '" . $this->cnDB->escapeString($teamId) . "'");
			}
		}
		
		function teamList($competitionId, $distance)
		{
			return $this->cnDB->query("select t.id as tid, c.*, t.*, c.name cname from tblTeam t inner join tblClub c on t.clubId = c.id where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' order by t.time desc");
		}
		
		function teamClubListExcept($competitionId, $clubs)
		{
			return $this->cnDB->query("select distinct c.id, c.name from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and c.id not in (" . $this->cnDB->escapeString(implode(', ', $clubs)) . ") order by c.name");
		}
		
		function teamSwimmerListExcept($competitionId, $distance, $swimmers)
		{
			return $this->cnDB->query("select s.id as sid, s.name as sname, c.name as cname from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '-" . $this->cnDB->escapeString($distance) . "' and s.id not in (" . $this->cnDB->escapeString(implode(', ', $swimmers)) . ") order by c.name, s.name");
		}
		
		function updateTeamResult($teamId, $time1, $time2, $position)
		{
			$this->cnDB->exec("update tblTeam set result1 = '" . $this->cnDB->escapeString($time1) . "', result2 = '" . $this->cnDB->escapeString($time2) . "', place = '" . $this->cnDB->escapeString($position) . "' where id = '" . $this->cnDB->escapeString($teamId) . "'");
		}
		
		function prizeList()
		{
			return $this->cnDB->query("select * from tblPrize");
		}
		
		function getPrizeDef($id)
		{
			return $this->cnDB->query("select * from tblPrize where id = $id");
		}
		
		function addPrize($name, $query)
		{
			$this->cnDB->query("insert into tblPrize (name, restriction) values ('" . $this->cnDB->escapeString($name) . "', '" . $this->cnDB->escapeString($query) . "');");
		}
		
		function updatePrize($id, $name, $query)
		{
			$this->cnDB->query("update tblPrize set name = '" . $this->cnDB->escapeString($name) . "', restriction = '" . $this->cnDB->escapeString($query) . "' where id = $id;");
		}
		
		function deletePrize($id)
		{
			$this->cnDB->query("delete from tblPrize where id = $id;");
		}
		
		function getPrize($competitionId, $restriction)
		{
			return $this->cnDB->query("select s.name as swimmername, c.name as clubname from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblRaceSwimmer rs on s.id = rs.swimmerId inner join tblRace r on r.id = rs.raceId inner join tblCompetitionSwimmer cs on s.id = cs.swimmerId and cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and r.distance = cs.distance where r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and $restriction limit 0, 1");
		}
		
		function diplomaSwimmers($competitionId, $swimmerList)
		{
			return $this->cnDB->query("select s.id as sid, c.id as cid, s.name as sname, c.name as cname, cs.distance, s.*, c.* from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblClub c on c.id = s.clubId where cs.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and s.id in (" . $this->cnDB->escapeString(implode(',', $swimmerList)) . ") order by c.name, s.name, distance");
		}
		
		function diplomaSwimmerPosition($competitionId, $swimmerId, $distance)
		{
			return $this->cnDB->query("select r.type, rs.position from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where (result1 + result2 / 2) > 0 and position < 999 and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and r.competitionId = '" . $this->cnDB->escapeString($competitionId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "' and (r.type = 8 or r.type = 24 or r.type = 5)");
		}
		
		function diplomaTeams($teamList)
		{
			return $this->cnDB->query("select t.id tid, t.*, c.* from tblTeam t inner join tblClub c on c.id = t.clubId where t.id in (" . implode(',', $teamList) . ")");
		}
		
		function diplomaTeamSwimmers($teamId)
		{
			return $this->cnDB->query("select * from tblTeamSwimmer ts inner join tblSwimmer s on s.id = ts.swimmerId where ts.teamId = {$teamId} order by name");
		}
		
		function setupPrint()
		{
			$rs = $this->cnDB->query("select count(*) as c from sqlite_master where type = 'table' and name = 'tblPrints'");
			
			if ($row = $this->fetch_array($rs))
			{
				if ($row['c'] == 0)
				{
					$this->cnDB->exec("CREATE TABLE tblPrints (id INTEGER NOT NULL, name TEXT NOT NULL DEFAULT '', query TEXT NOT NULL DEFAULT '', PRIMARY KEY (id));");
					$this->cnDB->exec('insert into tblPrints (name, query) values (\'Dagens tider\', \'select s.name as Navn, c.name as Klub, rs1.distance as Distance, (rs1.result1 + rs1.result2) / 2 - rs1.startTime as [1.], (rs2.result1 + rs2.result2) / 2 - rs2.startTime as [2.], (rs3.result1 + rs3.result2) / 2 - rs3.startTime as [3.] from tblSwimmer s inner join tblClub c on c.id = s.clubId inner join (tblRaceSwimmer rs1 inner join tblRace r1 on r1.id = rs1.raceId and r1.type = 0 and r1.competitionId = $compo$) rs1 on rs1.swimmerId = s.id and rs1.result1 > 0 left join (tblRaceSwimmer rs2 inner join tblRace r2 on r2.id = rs2.raceId and (r2.type = 4 or r2.type = 20) and r2.competitionId = $compo$) rs2 on rs2.swimmerId = s.id and rs2.result1 > 0 and rs2.distance = rs1.distance left join (tblRaceSwimmer rs3 inner join tblRace r3 on r3.id = rs3.raceId and r3.type in (8, 24, 5) and r3.competitionId = $compo$) rs3 on rs3.swimmerId = s.id and rs3.result1 > 0 and rs3.distance = rs1.distance order by c.name, s.name\')');
				}
			}
		}

		function getPrint($id)
		{
			return $this->cnDB->query("select * from tblPrints where id = $id;");
		}
		
		function addPrint($name, $query)
		{
			$this->cnDB->query("insert into tblPrints (name, query) values ('" . $this->cnDB->escapeString($name) . "', '" . $this->cnDB->escapeString($query) . "');");
		}
		
		function updatePrint($id, $name, $query)
		{
			$this->cnDB->query("update tblPrints set name = '" . $this->cnDB->escapeString($name) . "', query = '" . $this->cnDB->escapeString($query) . "' where id = $id;");
		}
		
		function deletePrint($id)
		{
			$this->cnDB->query("delete from tblPrints where id = $id;");
		}
		
		function printList()
		{
			return $this->cnDB->query("select * from tblPrints order by name;");
		}
		
		function printQuery($id, $query)
		{
			$rsQuery = $this->getPrint($query);
			
			if ($query = $this->fetch_array($rsQuery))
			{
				return $this->cnDB->query(str_replace('$compo$', $id, $query['query']));
			}
		}
		
		function setTrack($raceId, $swimmerId, $track)
		{
			$this->cnDB->exec("update tblRaceSwimmer set track = $track where raceId = $raceId and swimmerId = $swimmerId");
		}
	
		function setupSignup()
		{
			$rs = $this->cnDB->query("select count(*) as c from $this->cnDB->master where type = 'table' and name = 'tblSignUp'");
			
			if ($row = $this->fetch_array($rs))
			{
				if ($row['c'] == 0)
				{
					$this->cnDB->exec("CREATE TABLE tblSignUp (id INTEGER NOT NULL, name TEXT NOT NULL DEFAULT '', date TEXT NOT NULL DEFAULT '0000-00-00', PRIMARY KEY (id));");
					$this->cnDB->exec("CREATE TABLE tblSignUpSwimmer (signUpId INTEGER NOT NULL DEFAULT '0', swimmerId INTEGER NOT NULL DEFAULT '0', distance INTEGER NOT NULL DEFAULT '0', time NUMERIC(5,2) NOT NULL DEFAULT '0.00', help TEXT NOT NULL DEFAULT '', PRIMARY KEY (signUpId, swimmerId, distance));");
				}
			}
		}
				
		function signupList()
		{
			return $this->cnDB->query("select * from tblSignUp order by date desc");
		}
		
		function newSignup($name, $date)
		{
			$this->cnDB->exec("insert into tblSignUp (name, date) values ('" . $this->cnDB->escapeString($name) . "', '" . $this->cnDB->escapeString($date) . "')");
			return $this->cnDB->lastInsertRowID();
		}
		
		function getSignup($id)
		{
			$rsCompo = $this->cnDB->query("select * from tblSignUp where id = '$id';");
			return $this->fetch_array($rsCompo);
		}
		
		function addSignupSwimmerDistance($signupId, $swimmerId, $distance, $time, $help)
		{
			$rsDistance = $this->cnDB->query("select * from tblSignUpSwimmer cs where signUpId = '" . $this->cnDB->escapeString($signupId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			
			if ($this->fetch_array($rsDistance))
			{
				$this->cnDB->exec("update tblSignUpSwimmer set time = '" . $this->cnDB->escapeString($time) . "', help = '" . $this->cnDB->escapeString($help) . "' where signUpId = '" . $this->cnDB->escapeString($signupId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "' and distance = '" . $this->cnDB->escapeString($distance) . "'");
			}
			else
			{		
				$this->cnDB->exec("insert into tblSignUpSwimmer (signUpId, swimmerId, distance, time, help) values ('" . $this->cnDB->escapeString($signupId) . "', '" . $this->cnDB->escapeString($swimmerId) . "', '" . $this->cnDB->escapeString($distance) . "', '" . $this->cnDB->escapeString($time) . "', '" . $this->cnDB->escapeString($help) . "')");
			}
		}
				
		function signupSwimmerList($signupId)
		{
			return $this->cnDB->query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname, cs.distance, cs.time, s.edge, s.pilot, cs.help as devices, s.diet from tblSwimmer s inner join tblSignUpSwimmer cs on s.id = cs.swimmerId inner join tblClub c on c.id = s.clubId where cs.signUpId = '" . $this->cnDB->escapeString($signupId) . "' order by c.name, s.name, cs.distance / abs(cs.distance) desc, abs(cs.distance)");
		}
		
		function signupSwimmerTimes($signupId, $swimmerId)
		{
			return $this->cnDB->query("select * from tblSignUpSwimmer where signUpId = '" . $this->cnDB->escapeString($signupId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "'");
		}

		function clearSignupSwimmerDistances($signupId, $swimmerId)
		{
			$this->cnDB->exec("delete from tblSignUpSwimmer where signUpId = '" . $this->cnDB->escapeString($signupId) . "' and swimmerId = '" . $this->cnDB->escapeString($swimmerId) . "'");
		}
		
		function findOrCreateClub($clubName)
		{
			$rsClub = $this->cnDB->query("select id from tblClub where name = '" . $this->cnDB->escapeString($clubName) . "'");
			
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
			$rsSwimmer = $this->cnDB->query("select id from tblSwimmer where clubId = '" . $this->cnDB->escapeString($clubId) . "' and name = '" . $this->cnDB->escapeString($name) . "'");
			
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