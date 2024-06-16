<?php
require('scoreMeFunctions.php');
?>
<html>
    <head>
        <title>ScoreMe &mdash; Scorecard Helper</title>
        <?php 
            // pull in Viewport
            include('htmlHead.html');
        ?>
        <style>

            
            <?php 
            // pull in CSS
            include('scoreMe.css');

            // Decide to display the magic number columns
            if ($useMagic !== true) {
                echo ".magicNum {display:none;}";
            };
			echo $home_away_css;
            ?>
        </style>
    </head>
    <body>
	<?php 
            // pull in Google Analytics code
            include('GA-4.html');
        ?>
        <header>
            <h1>ScoreMe &mdash; Scorecard Helper</h1>
            <a href="startHere.php">Start Over</a>
        </header>
        <section id="mainWrap">
            <?php 
                
                    echo "<div id=\"gameOver\"><strong>Game Status:</strong> {$GUMBO['gameData']['status']['detailedState']}
                    ";
					
					if ($gameDayLink != "") {
						echo 
						"<a class=\"gameDayLink\" target=\"_blank\" href=\"$gameDayLink\">Official GameDay Site</a>";
					};
					echo "</div>";
             ?>
                <?php
                    
                    // Home Team 
                    echo  '<figure class="tableContainer" id="homeSideDeets"><table>';
                    echo "
                        <caption>Home / Location Details</caption>
                        <tr>
                            <td class=\"label home_team\">
                                Home Team
                            </td>
                            <td class=\"value\">
                                {$GUMBO['gameData']['teams']['home']['name']}
                            </td>
                        </tr>
                        ";
                    
                        // End Record
                        $endRecord = $GUMBO['gameData']['teams']['home']['record']['leagueRecord']['wins'] .
                                        "-" .
                                        $GUMBO['gameData']['teams']['home']['record']['leagueRecord']['losses'];

                        if ($gameFinal == "F") {
                            echo "
                            <tr>
                                <td class=\"label home_team\">
                                    Record (End)
                                </td>
                                <td class=\"value\">
                                    {$endRecord}
                                </td>
                            </tr>
                            ";
                        };

                        // Streak (get from standings)
                        echo "
                            <tr>
                                <td class=\"label home_team\">
                                    Streak
                                </td>
                                <td class=\"value\">
                                    {$homeStreak}
                                </td>
                            </tr>
                            ";

                        // L10 (get from standings)
                        echo "
                            <tr>
                                <td class=\"label home_team\">
                                    Last 10
                                </td>
                                <td class=\"value\">
                                    {$homeLastTen}
                                </td>
                            </tr>
                            ";

                        // Venue
                            echo "
                            <tr>
                                <td class=\"label home_team\">
                                    Venue
                                </td>
                                <td class=\"value\">
                                    {$GUMBO['gameData']['venue']['name']}
                                </td>
                            </tr>
                            ";
							
						// Umpires
						echo "
						<tr>
                                <td class=\"label home_team\">
                                    Umpires
                                </td>
                                <td class=\"value\">
								<ul class=\"umps\">
									{$Umpires}
								</ul>
                                </td>
                            </tr>
						";

                        // Attendance
                            if(isset($GUMBO['gameData']['gameInfo']['attendance'])) {
                                $attendance = number_format($GUMBO['gameData']['gameInfo']['attendance']);
                            } else {
                                $attendance = "TBD";
                            };
                            echo "
                            <tr>
                                <td class=\"label home_team\">
                                    Attendance
                                </td>
                                <td class=\"value\">
                                    {$attendance}
                                </td>
                            </tr>
                            ";

                        // Temp
                            if(isset($GUMBO['gameData']['weather']['temp'])) {
                                $tempF = $GUMBO['gameData']['weather']['temp'];
								$tempC = round((($tempF-32)*5)/9);
								$temp = $tempF . "&deg; F / ". $tempC . "&deg; C";
                            } else {
                                $temp = "TBD";
                            };
                            echo "
                            <tr>
                                <td class=\"label home_team\">
                                    Temp
                                </td>
                                <td class=\"value\">
                                    {$temp}
                                </td>
                            </tr>
                            ";

                        // Series Game Number


                        echo "</table></figure>";

                        // Lineup

                            // Sort the $data['players'] array by 'battingOrder'
                            usort($GUMBO['liveData']['boxscore']['teams']['home']['players'], 'compareByBattingOrder');

                            echo '<figure class="tableContainer homeSide lineup"><table>
                            <caption>Home Lineup</caption>
                                <tr>
                                    <th class="home_team">Order</th>
                                    <th class="home_team">#</th>
                                    <th class="home_team">Name</th>
                                    <th class="home_team">Position</th>
									<th class="home_team">B/T</th>';
                                
                            if ($gameFinal == "F") {
                                echo "<th class=\"home_team\">AB</th>
                                    <th class=\"home_team\">R</th>
                                    <th class=\"home_team\">H</th>
                                    <th class=\"home_team\">RBI</th>
                                    <th class=\"home_team\">BB</th>
                                    <th class=\"home_team\">K</th>";
                            };
                                
                            echo '</tr>';
                            
                            foreach ($GUMBO['liveData']['boxscore']['teams']['home']['players'] as $key => $player) {
                                
                                if (isset($player['battingOrder'])) {

                                    $playerKey = "ID" . $player['person']['id'];
									$batThrow = $GUMBO['gameData']['players'][$playerKey]['batSide']['code'] . "/" . $GUMBO['gameData']['players'][$playerKey]['pitchHand']['code'];
                                    $dispName = $GUMBO['gameData']['players'][$playerKey]['boxscoreName'];
                                    
                                    $batOrd = $player['battingOrder'];
                                    $batSlot = floor($batOrd / 100);
                                    $slotOrd = $batOrd % 100;
                                    if ($slotOrd == 0) {
                                        $batOrdDisplay = $batSlot;
                                    } else {
                                        $batOrdDisplay = "&#x2515;" . str_repeat("&#x2500;",$slotOrd) . $batSlot;
                                    }

                                    echo 
                                        "<tr>
                                            <td>
                                                {$batOrdDisplay}
                                            </td>
                                            <td>
                                                {$player['jerseyNumber']}
                                            </td>
                                            <td>
                                                {$dispName}
                                            </td>
                                            <td>
                                                {$player['position']['abbreviation']}
                                            </td>
											<td>
                                                {$batThrow}
                                            </td>";
                                    
                                    if ($gameFinal == "F") {
                                        echo "<td>
                                                {$player['stats']['batting']['atBats']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['runs']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['hits']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['rbi']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['baseOnBalls']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['strikeOuts']}
                                            </td>";
                                    };

                                    echo "</tr>";
                                };
                            };
                                
                            echo "</table></figure>";

                        // Opposing Pitchers
                            echo "<figure class=\"tableContainer homeSide pitchers\"><table>
                            <caption>Visiting Pitchers</caption>
                                <tr>
                                    <th class=\"away_team\">#</th>
                                    <th class=\"away_team\">Name</th>
									<th class=\"away_team\">Dec.</th>
                                    <th class=\"away_team\">B/T</th>
                                    <th class=\"away_team\">IP</th>
                                    <th class=\"away_team\">TBF</th>
                                    <th class=\"away_team\">H</th>
                                    <th class=\"away_team\">R</th>
                                    <th class=\"away_team\">ER</th>
                                    <th class=\"away_team\">BB</th>
                                    <th class=\"away_team\">K</th>
                                    <th class=\"away_team\">HR</th>
                                    <th class=\"away_team\">WP</th>
                                    <th class=\"away_team\">HB</th>
                                </tr>
                                    {$awayPitchingTable}
                                    </table></figure>";


                        // Bench
                            echo '<figure class="tableContainer homeSide bench"><table>
                            <caption>Home Bench</caption>
                                <tr>
                                    <th class="home_team">#</th>
                                    <th class="home_team">Name</th>
                                    <th class="home_team">Position</th>
									<th class="home_team">B/T</th>
                                </tr>
                                    
                                    ';
                        
                            foreach ($GUMBO['liveData']['boxscore']['teams']['home']['players'] as $key => $player) {
                                
                                if (isset($player['battingOrder'])) {
                                    $batOrd = $player['battingOrder'];
                                    $slotOrd = $batOrd % 100;

                                    if ($slotOrd == 0) {
                                        $isBench = false;
                                    } else {
                                        $isBench = true;
                                    };

                                } else {
                                    if ($player['position']['abbreviation'] == "P") {
                                        $isBench = false;
                                    } else {
                                        $isBench = true;
                                    };
                                };

                                if ($isBench == true) {

                                    $playerKey = "ID" . $player['person']['id'];
									$batThrow = $GUMBO['gameData']['players'][$playerKey]['batSide']['code'] . "/" . $GUMBO['gameData']['players'][$playerKey]['pitchHand']['code'];
                                    $dispName = $GUMBO['gameData']['players'][$playerKey]['boxscoreName'];
                                    $defaultPos = $GUMBO['gameData']['players'][$playerKey]['primaryPosition']['abbreviation'];
                                        
                                        echo 
                                            "<tr>
                                                <td>
                                                    {$player['jerseyNumber']}
                                                </td>
                                                <td>
                                                    {$dispName}
                                                </td>
                                                <td>
                                                    {$defaultPos}
                                                </td>
												<td>
                                                    {$batThrow}
                                                </td>
                                            </tr>";

                                };
                            };
                            
                        echo "</table></figure>";

                        // Bullpen
                            echo '<figure class="tableContainer homeSide bullpen"><table>
                            <caption>Visitor Bullpen</caption>
                                <tr>
                                    <th class="away_team">#</th>
                                    <th class="away_team">Name</th>
                                    <th class="away_team">Throws</th>
                                </tr>';

                                foreach ($awayRoster['roster'] as $player) {
                                    if ($player['position']['abbreviation'] == "P" && $player['status']['code'] == "A") {
                                        $playerId = 'ID' . $player['person']['id'];
                                        if (isset($GUMBO['liveData']['boxscore']['teams']['away']['players'][$playerId]['jerseyNumber'])) {
                                            $jerseyNum = $GUMBO['liveData']['boxscore']['teams']['away']['players'][$playerId]['jerseyNumber'];
                                        } elseif (isset($GUMBO['gameData']['players'][$playerId]['primaryNumber'])) {
                                            $jerseyNum = $GUMBO['gameData']['players'][$playerId]['primaryNumber'];
                                        } else {
                                            $jerseyNum = "";
                                        };
                                        
                                        echo "
                                        <tr>
                                            <td>
                                                {$jerseyNum}
                                            </td>
                                            <td>
                                            {$player['person']['boxscoreName']}
                                            </td>
                                            <td>
                                            {$player['person']['pitchHand']['code']}
                                            </td>
                                        </tr>
                                        ";
                                    };
                                };

                            echo "</table></figure>";

                        // Standings
                        echo "<figure class=\"tableContainer standings homeSide\"><table>
                                <caption>Home Team Standings</caption>
                                <tr>
                                    <th class=\"home_team\">Team</th>
                                    <th class=\"home_team\">GB</th>
                                    <th class=\"home_team\">WCGB</th>
                                    <th class=\"magicNum home_team\">Magic Number</th>
                                </tr>
                                {$homeStandingsTable}
                            </table></figure>";

                    // Final Stats
                        // Runs
                        // Hits
                        // Errors
                        // LOB

                    if ($gameFinal == "F") {
                       echo
                            "<figure class=\"tableContainer\" id=\"scoreTotals\"><table>
                                <tr>
                                    <th colspan=\"3\" class=\"away_team\">VISITOR</th>
                                    <th class=\"home_team\" colspan=\"3\">HOME</th>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">{$linescore['away']['runs']}</td>
                                    <td colspan=\"2\">Runs</td>
                                    <td colspan=\"2\">{$linescore['home']['runs']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">{$linescore['away']['hits']}</td>
                                    <td colspan=\"2\">Hits</td>
                                    <td colspan=\"2\">{$linescore['home']['hits']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">{$linescore['away']['errors']}</td>
                                    <td colspan=\"2\">Errors</td>
                                    <td colspan=\"2\">{$linescore['home']['errors']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">{$linescore['away']['leftOnBase']}</td>
                                    <td colspan=\"2\">LOB</td>
                                    <td colspan=\"2\">{$linescore['home']['leftOnBase']}</td>
                                </tr>
                            </table></figure>";
                    };
                ?>
                <?php
                        
                    // Away Team
                    echo  '<figure class="tableContainer" id="awaySideDeets"><table>';
                    echo "
                        <caption>Visitor / Game Details</caption>
                        <tr>
                            <td class=\"label away_team\">
                                Visiting Team
                            </td>
                            <td class=\"value\">
                                {$GUMBO['gameData']['teams']['away']['name']}
                            </td>
                        </tr>
                        ";
                    
                        
                        // End Record
                        $endRecordAway = $GUMBO['gameData']['teams']['away']['record']['leagueRecord']['wins'] .
                                        "-" .
                                        $GUMBO['gameData']['teams']['away']['record']['leagueRecord']['losses'];

                        if ($gameFinal == "F") {
                            echo "
                            <tr>
                                <td class=\"label away_team\">
                                    Record (End)
                                </td>
                                <td class=\"value\">
                                    {$endRecordAway}
                                </td>
                            </tr>
                            ";
                        };

                        // Streak (get from standings)
                        echo "
                            <tr>
                                <td class=\"label away_team\">
                                    Streak
                                </td>
                                <td class=\"value\">
                                    {$awayStreak}
                                </td>
                            </tr>
                            ";

                        // L10 (get from standings)
                        echo "
                            <tr>
                                <td class=\"label away_team\">
                                    Last 10
                                </td>
                                <td class=\"value\">
                                    {$awayLastTen}
                                </td>
                            </tr>
                            ";

                        // Date
                            $displayDate = date_format(date_create_from_format("Y-m-d",$gameDate),"n/j/Y");
                            echo "
                            <tr>
                                <td class=\"label away_team\">
                                    Date
                                </td>
                                <td class=\"value\">
                                    {$displayDate}
                                </td>
                            </tr>
                            ";

                        // First Pitch
                            echo "
                                <tr>
                                    <td class=\"label away_team\">
                                        First Pitch
                                    </td>
                                    <td class=\"value\">
                                        {$firstPitch}
                                    </td>
                                </tr>
                                ";

                        // End

                        // Game Time
                            if (isset($GUMBO['gameData']['gameInfo']['gameDurationMinutes'])) {
                                $minutes = $GUMBO['gameData']['gameInfo']['gameDurationMinutes'];
                            } else {
                                $minutes = 0;
                            };

                            // Convert minutes to hours and minutes
                            $hours = floor($minutes / 60); // Get the whole number of hours
                            $remainingMinutes = $minutes % 60; // Get the remaining minutes

                            // Format the result as "hh:mm"
                            $gameDuration = sprintf('%d:%02d', $hours, $remainingMinutes);

                            echo "
                                <tr>
                                    <td class=\"label away_team\">
                                        Duration
                                    </td>
                                    <td class=\"value\">
                                        {$gameDuration}
                                    </td>
                                </tr>
                                ";

                            echo "</table></figure>";

                        // Lineup

                            // Sort the $data['players'] array by 'battingOrder'
                            usort($GUMBO['liveData']['boxscore']['teams']['away']['players'], 'compareByBattingOrder');

                            echo '<figure class="tableContainer awaySide lineup"><table>
                            <caption>Visitor Lineup</caption>
                                <tr>
                                    <th class="away_team">Order</th>
                                    <th class="away_team">#</th>
                                    <th class="away_team">Name</th>
                                    <th class="away_team">Position</th>
									<th class="away_team">B/T</th>';
                                
                            if ($gameFinal == "F") {
                                echo "<th class=\"away_team\">AB</th>
                                    <th class=\"away_team\">R</th>
                                    <th class=\"away_team\">H</th>
                                    <th class=\"away_team\">RBI</th>
                                    <th class=\"away_team\">BB</th>
                                    <th class=\"away_team\">K</th>";
                            };
                                
                            echo '</tr>';
                            
                            foreach ($GUMBO['liveData']['boxscore']['teams']['away']['players'] as $key => $player) {
                                
                                if (isset($player['battingOrder'])) {

                                    $playerKey = "ID" . $player['person']['id'];
									$batThrow = $GUMBO['gameData']['players'][$playerKey]['batSide']['code'] . "/" . $GUMBO['gameData']['players'][$playerKey]['pitchHand']['code'];
                                    $dispName = $GUMBO['gameData']['players'][$playerKey]['boxscoreName'];
                                    
                                    $batOrd = $player['battingOrder'];
                                    $batSlot = floor($batOrd / 100);
                                    $slotOrd = $batOrd % 100;
                                    if ($slotOrd == 0) {
                                        $batOrdDisplay = $batSlot;
                                    } else {
                                        $batOrdDisplay = "&#x2515;" . str_repeat("&#x2500;",$slotOrd) . $batSlot;
                                    }

                                    echo 
                                        "<tr>
                                            <td>
                                                {$batOrdDisplay}
                                            </td>
                                            <td>
                                                {$player['jerseyNumber']}
                                            </td>
                                            <td>
                                                {$dispName}
                                            </td>
                                            <td>
                                                {$player['position']['abbreviation']}
                                            </td>
											<td>
                                                {$batThrow}
                                            </td>";
                                    
                                    if ($gameFinal == "F") {
                                        echo "<td>
                                                {$player['stats']['batting']['atBats']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['runs']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['hits']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['rbi']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['baseOnBalls']}
                                            </td>
                                            <td>
                                                {$player['stats']['batting']['strikeOuts']}
                                            </td>";
                                    };

                                    echo "</tr>";
                                };
                            };
                                
                            echo "</table></figure>";

                        // Opposing Pitchers
                        echo "<figure class=\"tableContainer awaySide pitchers\"><table>
                        <caption>Home Pitchers</caption>
                            <tr>
                                <th class=\"home_team\">#</th>
                                <th class=\"home_team\">Name</th>
								<th class=\"home_team\">Dec.</th>
                                <th class=\"home_team\">B/T</th>
                                <th class=\"home_team\">IP</th>
                                <th class=\"home_team\">TBF</th>
                                <th class=\"home_team\">H</th>
                                <th class=\"home_team\">R</th>
                                <th class=\"home_team\">ER</th>
                                <th class=\"home_team\">BB</th>
                                <th class=\"home_team\">K</th>
                                <th class=\"home_team\">HR</th>
                                <th class=\"home_team\">WP</th>
                                <th class=\"home_team\">HB</th>
                            </tr>
                                {$homePitchingTable}
                                </table></figure>";

                        // Bench
                            echo '<figure class="tableContainer awaySide bench"><table>
                            <caption>Visitor Bench</caption>
                                <tr>
                                    <th class="away_team">#</th>
                                    <th class="away_team">Name</th>
                                    <th class="away_team">Position</th>
									<th class="away_team">B/T</th>
                                </tr>
                                    
                                    ';
                        
                            foreach ($GUMBO['liveData']['boxscore']['teams']['away']['players'] as $key => $player) {
                                
                                if (isset($player['battingOrder'])) {
                                    $batOrd = $player['battingOrder'];
                                    $slotOrd = $batOrd % 100;

                                    if ($slotOrd == 0) {
                                        $isBench = false;
                                    } else {
                                        $isBench = true;
                                    };

                                } else {
                                    if ($player['position']['abbreviation'] == "P") {
                                        $isBench = false;
                                    } else {
                                        $isBench = true;
                                    };
                                };

                                if ($isBench == true) {

                                    $playerKey = "ID" . $player['person']['id'];
									$batThrow = $GUMBO['gameData']['players'][$playerKey]['batSide']['code'] . "/" . $GUMBO['gameData']['players'][$playerKey]['pitchHand']['code'];
                                    $dispName = $GUMBO['gameData']['players'][$playerKey]['boxscoreName'];
                                    $defaultPos = $GUMBO['gameData']['players'][$playerKey]['primaryPosition']['abbreviation'];
                                        
                                        echo 
                                            "<tr>
                                                <td>
                                                    {$player['jerseyNumber']}
                                                </td>
                                                <td>
                                                    {$dispName}
                                                </td>
                                                <td>
                                                    {$defaultPos}
                                                </td>
												<td>
                                                    {$batThrow}
                                                </td>
                                            </tr>";

                                };
                            };
                            
                            echo "</table></figure>";

                            
                        // Bullpen
                            echo '<figure class="tableContainer bullpen awaySide"><table>
                            <caption>Home Bullpen</caption>
                                <tr>
                                    <th class="home_team">#</th>
                                    <th class="home_team">Name</th>
                                    <th class="home_team">Throws</th>
                                </tr>';

                                foreach ($homeRoster['roster'] as $player) {
                                    if ($player['position']['abbreviation'] == "P" && $player['status']['code'] == "A") {
                                        $playerId = 'ID' . $player['person']['id'];
                                        if (isset($GUMBO['liveData']['boxscore']['teams']['home']['players'][$playerId]['jerseyNumber'])) {
                                            $jerseyNum = $GUMBO['liveData']['boxscore']['teams']['home']['players'][$playerId]['jerseyNumber'];
                                        } elseif (isset($GUMBO['gameData']['players'][$playerId]['primaryNumber'])) {
                                            $jerseyNum = $GUMBO['gameData']['players'][$playerId]['primaryNumber'];
                                        } else {
                                            $jerseyNum = "";
                                        };
                                        
                                        echo "
                                        <tr>
                                            <td>
                                                {$jerseyNum}
                                            </td>
                                            <td>
                                            {$player['person']['boxscoreName']}
                                            </td>
                                            <td>
                                            {$player['person']['pitchHand']['code']}
                                            </td>
                                        </tr>
                                        ";
                                    };
                                };


                                echo "</table></figure>";

                        // Standings
                        echo "<figure class=\"tableContainer standings awaySide\"><table>
                                <caption>Visitor Standings</caption>
                                <tr>
                                    <th class=\"away_team\">Team</th>
                                    <th class=\"away_team\">GB</th>
                                    <th class=\"away_team\">WCGB</th>
                                    <th class=\"magicNum away_team\">Magic Number</th>
                                </tr>
                                {$awayStandingsTable}
                            </table></figure>";

                        // Weather

                    // End Stats
                    if ($gameFinal == "F") {
                        echo
                             "<figure class=\"tableContainer\" id=\"eventTotals\"><table>
                                 <tr>
                                     <th class=\"away_team\" colspan=\"3\">VISITOR</th>
                                     <th class=\"home_team\" colspan=\"3\">HOME</th>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['batting']['doubles']}</td>
                                     <td colspan=\"2\">2B</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['batting']['doubles']}</td>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['batting']['triples']}</td>
                                     <td colspan=\"2\">3B</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['batting']['triples']}</td>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['batting']['homeRuns']}</td>
                                     <td colspan=\"2\">HR</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['batting']['homeRuns']}</td>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['batting']['stolenBases']}</td>
                                     <td colspan=\"2\">SB</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['batting']['stolenBases']}</td>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['batting']['caughtStealing']}</td>
                                     <td colspan=\"2\">CS</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['batting']['caughtStealing']}</td>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['batting']['groundIntoDoublePlay']}</td>
                                     <td colspan=\"2\">DP</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['batting']['groundIntoDoublePlay']}</td>
                                 </tr>
                                 <tr>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['away']['teamStats']['fielding']['passedBall']}</td>
                                     <td colspan=\"2\">PB</td>
                                     <td colspan=\"2\">{$GUMBO['liveData']['boxscore']['teams']['home']['teamStats']['fielding']['passedBall']}</td>
                                 </tr>
                             </table></figure>";
                     };

                ?>
        </section>
    </body>
</html>