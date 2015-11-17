<?PHP
	class MysqlDB
	{
		function MysqlDB($server, $username, $password, $db)
		{
			$this->server = $server;
			$this->username = $username;
			$this->password = $password;
			$this->db = $db;			
		}
		
		function connect()
		{
			$this->cnDB = mysql_connect($this->server, $this->username, $this->password);
			
			mysql_select_db($this->db);			
		}
		
		function fetch_array($recordset)
		{
			return mysql_fetch_array($recordset);
		}
		
		function close()
		{
			mysql_close($this->cnDB);
		}
		
		function competitionList()
		{
			return mysql_query('select * from tblCompetition order by date desc');
		}
		
		function newCompetition($name, $date, $leader, $extra100, $tracks)
		{
			mysql_query("insert into tblCompetition (name, date, leader, extra100, tracks) values ('" . mysql_escape_string($name) . "', '" . mysql_escape_string($date) . "', '" . mysql_escape_string($leader) . "', '" . mysql_escape_string($extra100) . "', '" . mysql_escape_string($tracks) . "')");
			
			return mysql_insert_id();
		}

		function getCompetition($id)
		{
			$rsCompo = mysql_query("select * from tblCompetition where id = '" . mysql_escape_string($id) . "'");
			return $this->fetch_array($rsCompo);
		}
		
		function updateCompetition($id, $name, $date, $leader, $extra100, $tracks)
		{
			mysql_query("update tblCompetition set name = '" . mysql_escape_string($name) . "', date = '" . mysql_escape_string($date) . "', leader = '" . mysql_escape_string($leader) . "', extra100 = '" . mysql_escape_string($extra100) . "', tracks = '" . mysql_escape_string($tracks) . "' where id = '" . mysql_escape_string($id) . "';");
		}
		
		function updateHowMany($competitionId, $distance, $round, $howmany)
		{
			switch($round)
			{
				case 4:
					mysql_query("update tblCompetition set `howmanySemi" . mysql_escape_string($distance) . "` = '" . mysql_escape_string($howmany) . "' where id = '" . mysql_escape_string($competitionId) . "'");
					break;
				case 5:
					mysql_query("update tblCompetition set `howmanyFinal" . mysql_escape_string($distance) . "` = '" . mysql_escape_string($howmany) . "' where id = '" . mysql_escape_string($competitionId) . "'");
					break;
				case 8:
					mysql_query("update tblCompetition set `howmanyFinal" . mysql_escape_string($distance) . "` = '" . mysql_escape_string($howmany) . "' where id = '" . mysql_escape_string($competitionId) . "'");
					break;
				case 20:
					mysql_query("update tblCompetition set `howmanySemiExtra" . mysql_escape_string($distance) . "` = '" . mysql_escape_string($howmany) . "' where id = '" . mysql_escape_string($competitionId) . "'");
					break;
				case 24:
					mysql_query("update tblCompetition set `howmanyFinalExtra" . mysql_escape_string($distance) . "` = '" . mysql_escape_string($howmany) . "' where id = '" . mysql_escape_string($competitionId) . "'");
					break;				
			}
		}
		
		function deleteCompetitionSwimmer($competitionId, $swimmerId)
		{
			mysql_query("delete from tblCompetitionSwimmer where swimmerId = '" . mysql_escape_string($swimmerId) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");

			$rsRaces = $this->competitionRaces($competitionId);

			$raceIds = array();
			
			while ($race = $this->fetch_array($rsRaces))
			{
				$raceIds[] = $race['id'];
			}
			
			mysql_query("delete from tblRaceSwimmer where swimmerId = '" . mysql_escape_string($swimmerId) . "' and raceId in (" . implode(',', $raceIds) . ")");
		}

		function competitionRaces($competitionId)
		{
			return mysql_query("select id from tblRace where competitionId = '" . mysql_escape_string($competitionId) . "'");
		}
		
		function competitionSwimmerTimes($competitionId, $swimmerId)
		{
			return mysql_query("select * from tblCompetitionSwimmer where competitionId = '" . mysql_escape_string($competitionId) . "' and swimmerId = '" . mysql_escape_string($swimmerId) . "'");
		}
		
		function upgradeSemiFinal($competitionId, $distance)
		{
			$rsFinals = mysql_query("select id from tblRace where distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "' and (type = 8 or type = 5)");
			
			while ($final = $this->fetch_array($rsFinals))
			{
				mysql_query("delete from tblRaceSwimmer where raceId = {$final['id']}");
				mysql_query("delete from tblRace where id = {$final['id']}");
			}

			$rsUpdateSwimmers = mysql_query("select swimmerId from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.type = 4 and distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");
			
			while ($us = $this->fetch_array($rsUpdateSwimmers))
			{
				mysql_query("update tblCompetitionSwimmer set finaltime = semitime where swimmerId = {$us['swimmerId']} and distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");
			}
			
			mysql_query("update tblRace set type = 8 where type = 4 and competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "'");
			mysql_query("update tblCompetition set howmanyFinal$distance = howmanySemi$distance where id = '" . mysql_escape_string($competitionId) . "';");
		}
		
		function clubList()
		{
			return mysql_query("select id, name from tblClub");
		}
		
		function clubListForDistance($competitionId, $distance, $round, $howmany)
		{
			switch ($round)
			{
				case 0:
					return mysql_query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' group by c.id, c.name");
					break;
				case 4:
					return mysql_query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 20:
					return mysql_query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . mysql_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 8:
					return mysql_query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 5:
					return mysql_query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' group by c.id, c.name");
					break;
				case 24:
					return mysql_query("select c.id, c.name, count(*) as cnt from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' group by c.id, c.name");
					break;
			}
		}

		function newClub($name)
		{
			mysql_query("insert into tblClub (name) values ('" . mysql_escape_string($name) . "')");
			
			return mysql_insert_id();
		}
		
		function swimmerList($competitionId)
		{
			return mysql_query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname, cs.distance, cs.time, s.edge, s.pilot, cs.help as devices, s.diet from tblSwimmer s inner join tblCompetitionSwimmer cs on s.id = cs.swimmerId inner join tblClub c on c.id = s.clubId where cs.competitionId = '" . mysql_escape_string($competitionId) . "' order by c.name, s.name, cs.distance / abs(cs.distance) desc, abs(cs.distance)");
		}
		
		function newSwimmer($name, $clubId, $edge, $pilot, $diet)
		{
			mysql_query("insert into tblSwimmer (name, clubId, edge, pilot, diet) values ('" . mysql_escape_string($name) . "', '" . mysql_escape_string($clubId) . "', '" . mysql_escape_string($edge) . "', '" . mysql_escape_string($pilot) . "', '" . mysql_escape_string($diet) . "')");
			
			return mysql_insert_id();
		}

		function getSwimmer($id)
		{
			$rsSwimmer = mysql_query("select * from tblSwimmer where id = '" . mysql_escape_string($id) . "'");
			return $this->fetch_array($rsSwimmer);
		}
		
		function updateSwimmer($id, $name, $edge, $pilot, $diet, $clubId)
		{
			mysql_query("update tblSwimmer set name = '" . mysql_escape_string($name) . "', edge = '" . mysql_escape_string($edge) . "', pilot = '" . mysql_escape_string($pilot) . "', diet = '" . mysql_escape_string($diet) . "', clubId = '" . mysql_escape_string($clubId) . "' where id = '" . mysql_escape_string($id) . "'");
		}
		
		function updateCompetitionSwimmmerSemiTime($competitionId, $swimmerId, $distance, $time)
		{
			mysql_query("update tblCompetitionSwimmer set semitime = '" . mysql_escape_string($time) . "', semitimechecked = 1 where swimmerId = '" . mysql_escape_string($swimmerId) . "' and competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "'");
		}
		
		function clearSwimmerDistances($competitionId, $swimmerId)
		{
			mysql_query("delete from tblCompetitionSwimmer where competitionId = '" . mysql_escape_string($competitionId) . "' and swimmerId = '" . mysql_escape_string($swimmerId) . "'");
		}
		
		function addSwimmerDistance($competitionId, $swimmerId, $distance, $time, $help)
		{
			mysql_query("insert into tblCompetitionSwimmer (competitionId, swimmerId, distance, time, help) values ('" . mysql_escape_string($competitionId) . "', '" . mysql_escape_string($swimmerId) . "', '" . mysql_escape_string($distance) . "', '" . mysql_escape_string($time) . "', '" . mysql_escape_string($help) . "')");
		}
		
		function allSwimmersExcept($swimmerIds)
		{
			if ($swimmerIds == '')
			{
				$rsNewSwimmers = mysql_query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname from tblSwimmer s inner join tblClub c on c.id = s.clubId order by c.name, s.name");
			}
			else
			{
				$rsNewSwimmers = mysql_query("select s.id as swimmerId, s.name as swimmername, c.id as clubId, c.name as clubname from tblSwimmer s inner join tblClub c on c.id = s.clubId where s.id not in (" . mysql_escape_string($swimmerIds) . ") order by c.name, s.name");
			}
			
			return $rsNewSwimmers;
		}
		
		function updateResult($raceId, $swimmerId, $time1, $time2, $position)
		{
			mysql_query("update tblRaceSwimmer set result1 = '" . mysql_escape_string($time1) . "', result2 = '" . mysql_escape_string($time2) . "', position = '" . mysql_escape_string($position) . "' where raceId = '" . mysql_escape_string($raceId) . "' and swimmerId = '" . mysql_escape_string($swimmerId) . "'");
		}
		
		function raceList($competitionId, $distance, $round)
		{
			return mysql_query("select * from tblRace where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' and type = '" . mysql_escape_string($round) . "' order by number");
		}
		
		function raceSwimmers($raceId)
		{
			return mysql_query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . mysql_escape_string($raceId) . "' order by startTime");
		}
		
		function raceSwimmersOrdered($raceId, $round)
		{
			if ($round == 0)
			{
				return mysql_query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . mysql_escape_string($raceId) . "' order by time desc");
			}
			elseif ($round == 4 || $round == 20)
			{
				return mysql_query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . mysql_escape_string($raceId) . "' order by semitime desc");
			}
			else
			{
				return mysql_query("select *, s.name as swimmername, cl.name as clubname from tblRace r inner join tblRaceSwimmer rs on r.id = rs.raceId inner join tblSwimmer s on s.id = rs.swimmerId inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id and cs.competitionId = r.competitionId and cs.distance = r.distance inner join tblClub cl on cl.id = s.clubId where r.id = '" . mysql_escape_string($raceId) . "' order by finaltime desc");
			}
		}
		
		function addRaceSwimmer($raceId, $swimmerId)
		{
			mysql_query("insert into tblRaceSwimmer (raceId, swimmerId) values ('" . mysql_escape_string($raceId) . "', '" . mysql_escape_string($swimmerId) . "')");
		}
		
		function updateStartTime($raceId, $swimmerId, $time)
		{
			mysql_query("update tblRaceSwimmer set startTime = $time where swimmerId = '" . mysql_escape_string($swimmerId) . "' and raceId = '" . mysql_escape_string($raceId) . "'");
		}
		
		function clearRaces($competitionId, $distance, $round)
		{
			$rsRaces = $this->raceList($competitionId, $distance, $round);
	
			$raceIds = "0";
	
			while ($aryRace = $this->fetch_array($rsRaces))
			{
				$raceIds .= ", " . $aryRace['id'];
			}
	
			mysql_query("delete from tblRaceSwimmer where raceId in ($raceIds)");
			mysql_query("delete from tblRace where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' and type = '" . mysql_escape_string($round) . "'");
		}
	
		function moveSwimmerToHeat($swimmerId, $from, $to)
		{
			mysql_query("update tblRaceSwimmer set raceId = '" . mysql_escape_string($to) . "' where swimmerId = '" . mysql_escape_string($swimmerId) . "' and raceId = '" . mysql_escape_string($from) . "'");
		}
		
		function upgradeSwimmers($competitionId, $distance, $round, $howmany)
		{
			switch ($round)
			{
				case 0:
					break;
				case 4:
					$rsUpdateSwimmers = mysql_query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "'"); 
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						mysql_query("update tblCompetitionSwimmer set semitime = '{$us['result']}', semitimechecked = 0 where swimmerId = '{$us['swimmerId']}' and distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");
					}
					break;
				case 20:
					$rsUpdateSwimmers = mysql_query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . mysql_escape_string($howmany) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "'");
	
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						mysql_query("update tblCompetitionSwimmer set semitime = '{$us['result']}', semitimechecked = 0 where swimmerId = '{$us['swimmerId']}' and competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "'");
					}
					break;
				case 8:
					$rsUpdateSwimmers = mysql_query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . mysql_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						mysql_query("update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");
					}
					break;
				case 5:
					$rsUpdateSwimmers = mysql_query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						mysql_query("update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");
					}
					break;
				case 24:
					$rsUpdateSwimmers = mysql_query("select swimmerId, ((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.distance = '" . mysql_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "'");
					
					while ($us = $this->fetch_array($rsUpdateSwimmers))
					{
						mysql_query("update tblCompetitionSwimmer set finaltime = '{$us['result']}' where swimmerId = '{$us['swimmerId']}' and distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "'");
					}
					break;
			}
		}
		
		function countSwimmersForDistance($competitionId, $distance)
		{
			$rsSwimmerCount = mysql_query("select count(*) as result from tblCompetitionSwimmer cs where cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "'");
			$c = $this->fetch_array($rsSwimmerCount, MYSQL_NUM);
			return $c[0];
		}
		
		function countFirstRoundHeatsForDistance($competitionId, $distance)
		{
			$rsFirstHeatCount = mysql_query("select count(*) as heats from tblRace where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' and type = '0'");

			$actualHeats = $this->fetch_array($rsFirstHeatCount, MYSQL_NUM);
			return $actualHeats[0];
		}
		
		function countSwimmers($competitionId, $distance, $round, $howmany, $generate)
		{
			if (!$generate)
			{
				$rsSwimmers = mysql_query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '" . mysql_escape_string($round) . "'");
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
					$rsSwimmers = mysql_query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "'");
					break;
				case 20:
					$rsSwimmers = mysql_query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . mysql_escape_string($howmany) . "'");
					break;
				case 5:
					$rsSwimmers = mysql_query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "'");
					break;
				case 8:
					$rsSwimmers = mysql_query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "'");
					break;
				case 24:
					$rsSwimmers = mysql_query("select count(*) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "'");
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
			$semiCount = mysql_query("select count(*) as result from tblRace where competitionId = '" . mysql_escape_string($competitionId) . "' and type = '" . mysql_escape_string($round) . "' and distance = '" . mysql_escape_string($distance) . "'");
			$count = $this->fetch_array($semiCount, MYSQL_NUM);
			return $count[0];
		}		

		function newHeat($competitionId, $distance, $round, $name, $number)
		{
			mysql_query("insert into tblRace (competitionId, distance, type, name, number) values ('" . mysql_escape_string($competitionId) . "', '" . mysql_escape_string($distance) . "', '" . mysql_escape_string($round) . "', '" . mysql_escape_string($name) . "', '" . mysql_escape_string($number) . "')");
			return mysql_insert_id();
		}
		
		function deleteHeat($competitionId, $distance, $round, $number)
		{
			mysql_query("delete from tblRace where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' and type = '" . mysql_escape_string($round) . "' and number = '" . mysql_escape_string($number) . "'");
			mysql_query("update tblRace set number = number - 1 where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' and type = '" . mysql_escape_string($round) . "' and number > '" . mysql_escape_string($number) . "'");
		}
		
		function getBestTime($competitionId, $swimmerId, $distance)
		{
			return mysql_query("select min((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where ((result1 + result2) / 2) > 0 and position < 999 and swimmerId = '" . mysql_escape_string($swimmerId) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "'");
		}
		
		function getRandomSwimmer($competitionId, $distance, $round, $howmany, $clubId, $usedswimmers)
		{
			switch ($round)
			{
				case 0:
					$rsRndSwimmer = mysql_query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and s.clubId = '" . mysql_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 4:
					$rsRndSwimmer = mysql_query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and s.clubId = '" . mysql_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 20:
					$rsRndSwimmer = mysql_query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position < 0 and rs.position >= '-" . mysql_escape_string($howmany) . "' and s.clubId = '" . mysql_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 8:
					$rsRndSwimmer = mysql_query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '4' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and s.clubId = '" . mysql_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 24:
					$rsRndSwimmer = mysql_query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '20' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and s.clubId = '" . mysql_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
				case 5:
					$rsRndSwimmer = mysql_query("select s.id from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblRaceSwimmer rs on rs.swimmerId = s.id inner join tblRace r on r.id = rs.raceId where r.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and cs.distance = '" . mysql_escape_string($distance) . "' and r.distance = '" . mysql_escape_string($distance) . "' and r.type = '0' and rs.position > 0 and rs.position <= '" . mysql_escape_string($howmany) . "' and s.clubId = '" . mysql_escape_string($clubId) . "' and s.id not in ($usedswimmers)");
					break;
			}
	
			$rndSwimmers = mysql_num_rows($rsRndSwimmer);
	
			$rand = rand(0, $rndSwimmers - 1);
	
			mysql_data_seek($rsRndSwimmer, $rand);
	
			return $this->fetch_array($rsRndSwimmer);
		}
		
		function teamSwimmers($teamId)
		{
			return mysql_query("select * from tblSwimmer s inner join tblTeamSwimmer ts on ts.swimmerId = s.id where teamId = '" . mysql_escape_string($teamId) . "'"); // mysql_query("select * from tblTeamSwimmer where teamId = '" . mysql_escape_string($teamId) . "'");
		}
		
		function newTeam($competitionId, $clubId, $distance)
		{
			mysql_query("insert into tblTeam (clubId, competitionId, distance) values ('" . mysql_escape_string($clubId) . "', '" . mysql_escape_string($competitionId) . "', '" . mysql_escape_string($distance) . "')");
			
			return mysql_insert_id();
		}
		
		function teamSwimmerList($competitionId, $distance, $swimmers)
		{
			return mysql_query("select * from tblCompetitionSwimmer where distance = '" . mysql_escape_string($distance) . "' and competitionId = '" . mysql_escape_string($competitionId) . "' and swimmerId in (" . mysql_escape_string($swimmers) . ")");
		}
		
		function updateTeamtime($teamId, $time)
		{
			mysql_query("update tblTeam set time = '" . mysql_escape_string($time) . "' where id = '" . mysql_escape_string($teamId) . "'");
		}
		
		function getSwimmerBestTime($competitionId, $swimmerId, $distance)
		{
			$rsResult = mysql_query("select min((result1 + result2) / 2 - startTime) as result from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where (result1 + result2 / 2) > 0 and swimmerId = '" . mysql_escape_string($swimmerId) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "'");
			
			return $this->fetch_array($rsResult);
		}
		
		function addTeamSwimmer($teamId, $swimmerId, $time)
		{
			mysql_query("insert into tblTeamSwimmer (teamId, swimmerId, time) values ('" . mysql_escape_string($teamId) . "', '" . mysql_escape_string($swimmerId) . "', '" . mysql_escape_string($time) . "')");
		}
		
		function updateTeamSwimmerTime($teamId, $swimmerId, $time)
		{
			mysql_query("update tblTeamSwimmer set time = '" . mysql_escape_string($time) . "' where teamId = '" . mysql_escape_string($teamId) . "' and swimmerId = '" . mysql_escape_string($swimmerId) . "'");
		}
		
		function deleteTeam($competitionId, $teamId)
		{
			mysql_query("delete from tblTeam where competitionId = '" . mysql_escape_string($competitionId) . "' and id = '" . mysql_escape_string($teamId) . "'");
			if (mysql_affected_rows() > 0)
			{
				mysql_query("delete from tblTeamSwimmers where teamId = '" . mysql_escape_string($teamId) . "'");
			}
		}
		
		function teamList($competitionId, $distance)
		{
			return mysql_query("select t.id as tid, c.*, t.*, c.name cname from tblTeam t inner join tblClub c on t.clubId = c.id where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' order by t.time desc");
		}
		
		function teamClubListExcept($competitionId, $clubs)
		{
			return mysql_query("select distinct c.id, c.name from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where cs.competitionId = '" . mysql_escape_string($competitionId) . "' and c.id not in (" . mysql_escape_string(implode(', ', $clubs)) . ") order by c.name");
		}
		
		function teamSwimmerListExcept($competitionId, $distance, $swimmers)
		{
			return mysql_query("select s.id as sid, s.name as sname, c.name as cname from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id where competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '-" . mysql_escape_string($distance) . "' and s.id not in (" . mysql_escape_string(implode(', ', $swimmers)) . ") order by c.name, s.name");
		}
		
		function updateTeamResult($teamId, $time1, $time2, $position)
		{
			mysql_query("update tblTeam set result1 = '" . mysql_escape_string($time1) . "', result2 = '" . mysql_escape_string($time2) . "', place = '" . mysql_escape_string($position) . "' where id = '" . mysql_escape_string($teamId) . "'");
		}
		
		function prizeList()
		{
			return mysql_query("select * from tblPrize");
		}
		
		function getPrize($competitionId, $restriction)
		{
			return mysql_query("select s.name as swimmername, c.name as clubname from tblClub c inner join tblSwimmer s on s.clubId = c.id inner join tblRaceSwimmer rs on s.id = rs.swimmerId inner join tblRace r on r.id = rs.raceId inner join tblCompetitionSwimmer cs on s.id = cs.swimmerId and cs.competitionId = '" . mysql_escape_string($competitionId) . "' and r.distance = cs.distance where r.competitionId = '" . mysql_escape_string($competitionId) . "' and $restriction limit 0, 1");
		}
		
		function diplomaSwimmers($competitionId, $swimmerList)
		{
			return mysql_query("select s.id as sid, c.id as cid, s.name as sname, c.name as cname, cs.distance, s.*, c.* from tblSwimmer s inner join tblCompetitionSwimmer cs on cs.swimmerId = s.id inner join tblClub c on c.id = s.clubId where cs.competitionId = '" . mysql_escape_string($competitionId) . "' and s.id in (" . mysql_escape_string(implode(',', $swimmerList)) . ") order by c.name, s.name, distance");
		}
		
		function diplomaSwimmerPosition($competitionId, $swimmerId, $distance)
		{
			return mysql_query("select r.type, rs.position from tblRaceSwimmer rs inner join tblRace r on r.id = rs.raceId where (result1 + result2 / 2) > 0 and position < 999 and swimmerId = '" . mysql_escape_string($swimmerId) . "' and r.competitionId = '" . mysql_escape_string($competitionId) . "' and distance = '" . mysql_escape_string($distance) . "' and (r.type = 8 or r.type = 24 or r.type = 5)");
		}
		
		function diplomaTeams($teamList)
		{
			return mysql_query("select t.id tid, t.*, c.* from tblTeam t inner join tblClub c on c.id = t.clubId where t.id in (" . implode(',', $teamList) . ")");
		}
		
		function diplomaTeamSwimmers($teamId)
		{
			return mysql_query("select * from tblTeamSwimmer ts inner join tblSwimmer s on s.id = ts.swimmerId where ts.teamId = {$teamId} order by name");
		}
	}
?>