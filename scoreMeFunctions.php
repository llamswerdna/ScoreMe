<?php
require('dbCred.php');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Custom comparison function based on 'battingOrder'
function compareByBattingOrder($player1, $player2) {
    $battingOrder1 = isset($player1['battingOrder']) ? $player1['battingOrder'] : PHP_INT_MAX;
    $battingOrder2 = isset($player2['battingOrder']) ? $player2['battingOrder'] : PHP_INT_MAX;

    if ($battingOrder1 == $battingOrder2) {
        return 0;
    }

    return ($battingOrder1 < $battingOrder2) ? -1 : 1;
}

// set gamePk
$gameId = $_GET['gameId']; 

// get GUMBO object
    $json = file_get_contents("https://statsapi.mlb.com/api/v1.1/game/${gameId}/feed/live");
    $GUMBO = json_decode($json, true);

// Set some universal variables
    $gameFinal = $GUMBO['gameData']['status']['statusCode'];
    $homeId = $GUMBO['gameData']['teams']['home']['id'];
    $homeLeague = $GUMBO['gameData']['teams']['home']['league']['id'];
    $homeDivision = $GUMBO['gameData']['teams']['home']['division']['id'];
    $awayId = $GUMBO['gameData']['teams']['away']['id'];
    $awayLeague = $GUMBO['gameData']['teams']['away']['league']['id'];
    $awayDivision = $GUMBO['gameData']['teams']['away']['division']['id'];
    $gameDate = $GUMBO['gameData']['datetime']['officialDate'];
    $season = substr($gameDate,0,4);
    $useMagic = false;
    $linescore = $GUMBO['liveData']['linescore']['teams'];
	$homeSportId = $GUMBO['gameData']['teams']['home']['sport']['id'];
	$awaySportId = $GUMBO['gameData']['teams']['away']['sport']['id'];
	
	$firstPitch = $GUMBO['gameData']['datetime']['time'] . " " . $GUMBO['gameData']['datetime']['ampm'] . "(scheduled)";
	
	foreach($GUMBO['liveData']['boxscore']['info'] as $info) {
		if ($info["label"] == "First pitch") {
			$firstPitch = $info["value"];
			break;
		};
	};
	
// Grab team colors if available
$sql_colors = "select home_team.color_1 as home_1,
		home_team.color_2 as home_2,
        away_team.color_1 as away_1,
        away_team.color_2 as away_2
from teams as home_team
	inner join teams as away_team
    	on home_team.team_id = " . $homeId . " and away_team.team_id = " . $awayId . ";";
		
$conn->query("SET SESSION group_concat_max_len=16384;");
$results_colors = $conn->query($sql_colors);

if ($results_colors->num_rows > 0) {
  // output data of each row
  while($row = $results_colors->fetch_assoc()) {
    $home_color_1 = $row["home_1"];
	$home_color_2 = $row["home_2"];
	$away_color_1 = $row["away_1"];
	$away_color_2 = $row["away_2"];
  }
} else {
	$home_color_1 = "FAEBD7";
	$home_color_2 = "000000";
	$away_color_1 = "FAEBD7";
	$away_color_2 = "000000";
};

$home_away_css = "
th.home_team, td.label.home_team { color:${home_color_2}; background-color:${home_color_1};}
th.away_team, td.label.away_team { color:${away_color_2}; background-color:${away_color_1};}
";

// Create GameDay link
switch($homeSportId) {
	case 1;
		$gameDayLink = "https://www.mlb.com/gameday/$gameId/";
		break;
	case 11;
	case 12;
	case 13;
	case 14;
	case 16;
		$gameDayLink = "https://www.milb.com/gameday/$gameId/";
		break;
	default;
		$gameDayLink = "";
		break;
};

// get rosters
    if ($homeSportId == 1) {
        $rosterType = 'depthChart';
    } else {
        $rosterType = 'active';
    };
    
    $json = file_get_contents("https://statsapi.mlb.com/api/v1/teams/${homeId}/roster?rosterType={$rosterType}&date=${gameDate}&hydrate=person");
    $homeRoster = json_decode($json, true);

    if ($awaySportId == 1) {
        $rosterType = 'depthChart';
    } else {
        $rosterType = 'active';
    };

    $json = file_get_contents("https://statsapi.mlb.com/api/v1/teams/${awayId}/roster?rosterType={$rosterType}&date=${gameDate}&hydrate=person");
    $awayRoster = json_decode($json, true);

// get standings
    $json = file_get_contents("https://statsapi.mlb.com/api/v1/standings?leagueId=${homeLeague}&season=${season}&standingsTypes=regularSeason&date=${gameDate}&fields=records,division,id,teamRecords,team,streak,streakCode,divisionRank,gamesBack,wildCardGamesBack,splitRecords,wins,losses,type,magicNumber,teamName&hydrate=team");
    $homeStandings = json_decode($json, true);

    if($homeLeague == $awayLeague) {
        $awayStandings = $homeStandings;
    } else {
        $json = file_get_contents("https://statsapi.mlb.com/api/v1/standings?leagueId=${awayLeague}&season=${season}&standingsTypes=regularSeason&date=${gameDate}&fields=records,division,id,teamRecords,team,streak,streakCode,divisionRank,gamesBack,wildCardGamesBack,splitRecords,wins,losses,type,magicNumber,teamName&hydrate=team");
        $awayStandings = json_decode($json, true);
    };

// Make standings Tables / get some team stats
    // HOME
        $homeStandingsTable = "";
        foreach($homeStandings['records'] as $record) {
            if ($record['division']['id'] == $homeDivision) {
                foreach ($record['teamRecords'] as $team) {
                    $homeStandingsTable .= "<tr>";

                    if($team['team']['id'] == $homeId) {
                        $td = '<td class="highlight">';
                        $tdMn = '<td class="highlight magicNum">';
                        $homeStreak = $team['streak']['streakCode'];
                    } else {
                        $td = '<td>';
                        $tdMn = '<td class="magicNum">';
                    };

                    $homeStandingsTable .=  $td . $team['team']['teamName'] . "</td>";
                    $homeStandingsTable .=  $td . $team['gamesBack'] . "</td>";
                    $homeStandingsTable .=  $td . $team['wildCardGamesBack'] . "</td>";

                    if(isset($team['magicNumber'] )) {
                        if($team['magicNumber'] <= 20 ) {
                            $useMagic = true;
                        };
                        $homeStandingsTable .=  $tdMn . $team['magicNumber'] . "</td>";
                    } else {
                        $homeStandingsTable .= $tdMn . " - </td>";
                    };

                    foreach($team['records']['splitRecords'] as $split) {
                        if ($team['team']['id'] == $homeId && $split['type'] == "lastTen") {
                            $homeLastTen = $split['wins'] . "-" . $split['losses'];
                        };
                    };

                    echo "</tr>";
                };
            };
        };
    
    // AWAY
    $awayStandingsTable = "";
    foreach($awayStandings['records'] as $record) {
        if ($record['division']['id'] == $awayDivision) {
            foreach ($record['teamRecords'] as $team) {
                $awayStandingsTable .= "<tr>";

                if($team['team']['id'] == $awayId) {
                    $td = '<td class="highlight">';
                    $tdMn = '<td class="highlight magicNum">';
                    $awayStreak = $team['streak']['streakCode'];
                } else {
                    $td = '<td>';
                    $tdMn = '<td class="magicNum">';
                };

                $awayStandingsTable .=  $td . $team['team']['teamName'] . "</td>";
                $awayStandingsTable .=  $td . $team['gamesBack'] . "</td>";
                $awayStandingsTable .=  $td . $team['wildCardGamesBack'] . "</td>";

                if(isset($team['magicNumber'] )) {
                    if($team['magicNumber'] <= 20 ) {
                        $useMagic = true;
                    };
                    $awayStandingsTable .=  $tdMn . $team['magicNumber'] . "</td>";
                } else {
                    $awayStandingsTable .= $tdMn . " - </td>";
                };

                foreach($team['records']['splitRecords'] as $split) {
                    if ($team['team']['id'] == $awayId && $split['type'] == "lastTen") {
                        $awayLastTen = $split['wins'] . "-" . $split['losses'];
                    };
                };

                echo "</tr>";
            };
        };
    };

// Make pitching tables
$awayPitchingTable = "";
$homePitchingTable = "";

    // HOME
        foreach($GUMBO['liveData']['boxscore']['teams']['home']['pitchers'] as $pitcher) {
            $playerKey = "ID" . $pitcher;
            $dispName = $GUMBO['gameData']['players'][$playerKey]['boxscoreName'];
			$bat_throw = $GUMBO['gameData']['players'][$playerKey]['batSide']['code'] . "/" . $GUMBO['gameData']['players'][$playerKey]['pitchHand']['code'];
            if (isset($GUMBO['liveData']['boxscore']['teams']['home']['players'][$playerKey]['jerseyNumber'])) {
                $jerseyNum = $GUMBO['liveData']['boxscore']['teams']['home']['players'][$playerKey]['jerseyNumber'];
            } elseif (isset($GUMBO['gameData']['players'][$playerKey]['primaryNumber'])) {
                $jerseyNum = $GUMBO['gameData']['players'][$playerKey]['primaryNumber'];
            } else {
                $jerseyNum = "";
            };
            $playerStat = $GUMBO['liveData']['boxscore']['teams']['home']['players'][$playerKey]['stats']['pitching'];
            if (isset($playerStat['note'])) {
                $note = $playerStat['note'];
            } else {
                $note = "";
            };

            $homePitchingTable .=
            "<tr>
                <td>
                    ${jerseyNum}
                </td>
                <td>
                    ${dispName}
                </td>
                <td>
                    {$note}
                </td>
				<td>
                    {$bat_throw}
                </td>
                <td>
                    {$playerStat['inningsPitched']}
                </td>
                <td>
                    {$playerStat['battersFaced']}
                </td>
                <td>
                    {$playerStat['hits']}
                </td>
                <td>
                    {$playerStat['runs']}
                </td>
                <td>
                    {$playerStat['earnedRuns']}
                </td>
                <td>
                    {$playerStat['baseOnBalls']}
                </td>
                <td>
                    {$playerStat['strikeOuts']}
                </td>
                <td>
                    {$playerStat['homeRuns']}
                </td>
                <td>
                    {$playerStat['wildPitches']}
                </td>
                <td>
                    {$playerStat['hitByPitch']}
                </td>
            </tr>";
        };
    
    // AWAY
    foreach($GUMBO['liveData']['boxscore']['teams']['away']['pitchers'] as $pitcher) {
        $playerKey = "ID" . $pitcher;
        $dispName = $GUMBO['gameData']['players'][$playerKey]['boxscoreName'];$bat_throw = $GUMBO['gameData']['players'][$playerKey]['batSide']['code'] . "/" . $GUMBO['gameData']['players'][$playerKey]['pitchHand']['code'];
            
        if (isset($GUMBO['liveData']['boxscore']['teams']['away']['players'][$playerKey]['jerseyNumber'])) {
            $jerseyNum = $GUMBO['liveData']['boxscore']['teams']['away']['players'][$playerKey]['jerseyNumber'];
        } elseif (isset($GUMBO['gameData']['players'][$playerKey]['primaryNumber'])) {
            $jerseyNum = $GUMBO['gameData']['players'][$playerKey]['primaryNumber'];
        } else {
            $jerseyNum = "";
        };
        $playerStat = $GUMBO['liveData']['boxscore']['teams']['away']['players'][$playerKey]['stats']['pitching'];
        if (isset($playerStat['note'])) {
            $note = $playerStat['note'];
        } else {
            $note = "";
        };

        $awayPitchingTable .=
        "<tr>
            <td>
                ${jerseyNum}
            </td>
            <td>
                ${dispName}
            </td>
            <td>
                {$note}
            </td>
			<td>
                {$bat_throw}
            </td>
            <td>
                {$playerStat['inningsPitched']}
            </td>
            <td>
                {$playerStat['battersFaced']}
            </td>
            <td>
                {$playerStat['hits']}
            </td>
            <td>
                {$playerStat['runs']}
            </td>
            <td>
                {$playerStat['earnedRuns']}
            </td>
            <td>
                {$playerStat['baseOnBalls']}
            </td>
            <td>
                {$playerStat['strikeOuts']}
            </td>
            <td>
                {$playerStat['homeRuns']}
            </td>
            <td>
                {$playerStat['wildPitches']}
            </td>
            <td>
                {$playerStat['hitByPitch']}
            </td>
        </tr>";
    };
	
	$Umpires = "";
	foreach($GUMBO['liveData']['boxscore']['officials'] as $ump) {
		switch ($ump['officialType']) {
			case "Home Plate":
				$umpPos = "HP";
				break;
			case "First Base":
				$umpPos = "1B";
				break;
			case "Second Base":
				$umpPos = "2B";
				break;
			case "Third Base":
				$umpPos = "3B";
				break;
			case "Left Field":
				$umpPos = "LF";
				break;
			case "Right Field":
				$umpPos = "RF";
				break;
			default:
				$umpPos = $ump['officialType'];
		};
		$Umpires .= "<li><strong>{$umpPos}:</strong> {$ump['official']['fullName']}</li>";
	};
?>