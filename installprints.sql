Sv�mmetider historik

select s.name as Navn, c.name as Klub, cp.name as Konkurrence, cp.date as Dato, r1.distance as Distance, round(((rs1.result1 + rs1.result2) / 2 - rs1.startTime) * 100) as Tid1
, round(((rs2.result1 + rs2.result2) / 2 - rs2.startTime) * 100) as Tid2
, round(((rs3.result1 + rs3.result2) / 2 - rs3.startTime) * 100) as Tid3
 from tblSwimmer s
inner join tblClub c on c.id = s.clubId
inner join tblRaceSwimmer rs1 on rs1.swimmerId = s.id
inner join tblRace r1 on r1.id = rs1.raceId and r1.type = 0
inner join tblCompetition cp on cp.id = r1.competitionId

left join (tblRaceSwimmer rs2 
inner join tblRace r2 on r2.id = rs2.raceId and (r2.type = 4 or r2.type = 20)) rs2
on rs2.swimmerId = s.id and cp.id = rs2.competitionId and r1.distance = rs2.distance

left join (tblRaceSwimmer rs3 
inner join tblRace r3 on r3.id = rs3.raceId and (r3.type = 5 or r3.type = 24 or r3.type = 8)) rs3
on rs3.swimmerId = s.id and cp.id = rs3.competitionId and r1.distance = rs3.distance

order by c.name, s.name, r1.distance, cp.date



Dagens tider

select s.name as Name, c.name as Club, rs1.distance as Distance, (rs1.result1 + rs1.result2) / 2 - rs1.startTime as [1st], (rs2.result1 + rs2.result2) / 2 - rs2.startTime as [2nd], (rs3.result1 + rs3.result2) / 2 - rs3.startTime as [3rd] from tblSwimmer s inner join tblClub c on c.id = s.clubId inner join (tblRaceSwimmer rs1 inner join tblRace r1 on r1.id = rs1.raceId and r1.type = 0 and r1.competitionId = $compo$) rs1 on rs1.swimmerId = s.id and rs1.result1 > 0 left join (tblRaceSwimmer rs2 inner join tblRace r2 on r2.id = rs2.raceId and (r2.type = 4 or r2.type = 20) and r2.competitionId = $compo$) rs2 on rs2.swimmerId = s.id and rs2.result1 > 0 and rs2.distance = rs1.distance left join (tblRaceSwimmer rs3 inner join tblRace r3 on r3.id = rs3.raceId and r3.type in (8, 24, 5) and r3.competitionId = $compo$) rs3 on rs3.swimmerId = s.id and rs3.result1 > 0 and rs3.distance = rs1.distance order by c.name, s.name, rs1.distance

Dagens placeringer

select s.name as Name, c.name as Club, rs1.distance as Distance, rs1.position as [1st], rs1.number as [H#1], rs2.position as [2nd], rs2.number as [H#2], rs3.position as [3rd], rs3.number as [H#3] from tblSwimmer s inner join tblClub c on c.id = s.clubId inner join (tblRaceSwimmer rs1 inner join tblRace r1 on r1.id = rs1.raceId and r1.type = 0 and r1.competitionId = $compo$) rs1 on rs1.swimmerId = s.id and rs1.result1 > 0 left join (tblRaceSwimmer rs2 inner join tblRace r2 on r2.id = rs2.raceId and (r2.type = 4 or r2.type = 20) and r2.competitionId = $compo$) rs2 on rs2.swimmerId = s.id and rs2.result1 > 0 and rs2.distance = rs1.distance left join (tblRaceSwimmer rs3 inner join tblRace r3 on r3.id = rs3.raceId and r3.type in (8, 24, 5) and r3.competitionId = $compo$) rs3 on rs3.swimmerId = s.id and rs3.result1 > 0 and rs3.distance = rs1.distance order by c.name, s.name, rs1.distance

Dagens bedste tider

select s.name as Name, c.name as Club, (rs25.result1 + rs25.result2) / 2 - rs25.startTime as [25m], (rs50.result1 + rs50.result2) / 2 - rs50.startTime as [50m], (rs100.result1 + rs100.result2) / 2 - rs100.startTime as [100m]
	from tblSwimmer s
		inner join tblClub c on c.id = s.clubId
		left join (tblRaceSwimmer rs25 inner join tblRace r25 on rs25.raceId = r25.id and r25.competitionId = $compo$ and r25.distance = 25) rs25 on s.id = rs25.swimmerId and rs25.result1 > 0
		left join (tblRaceSwimmer rs50 inner join tblRace r50 on rs50.raceId = r50.id and r50.competitionId = $compo$ and r50.distance = 50) rs50 on s.id = rs50.swimmerId and rs50.result1 > 0
		left join (tblRaceSwimmer rs100 inner join tblRace r100 on rs100.raceId = r100.id and r100.competitionId = $compo$ and r100.distance = 100) rs100 on s.id = rs100.swimmerId and rs100.result1 > 0
	where s.id in (select swimmerId from tblCompetitionSwimmer where competitionId = $compo$)
	group by s.name
	order by c.name, s.name

Bedste tidtager

	select track as Lane, avg(abs(result1 - result2)) as Difference from tblRaceSwimmer rs inner join tblRace r on rs.raceId = r.id where r.competitionId = $compo$ and track > 0 and result1 > 0 group by track order by avg(abs(result1 - result2))
