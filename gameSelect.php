<html>
    <head>
        <title>ScoreMe &mdash; Scorecard Helper</title>
        <?php 
            // pull in CSS
            include('htmlHead.html');
        ?>
        </style>
        <style>
            <?php 
            // pull in CSS
            include('scoreMe.css');
            ?>
        </style>
    </head>
    <body>
        <header>
            <h1>ScoreMe &mdash; Select Game</h1>
        </header>
        <section>
            <article>
                <!-- <h2>Select a Game</h2>
                <form action="gameSelect.php" method="GET">
                    <select name="sport" id="sport" accesskey="target">
                        <option value='none' selected>Choose a Game</option> -->
                        <?php
                            $sportId = $_GET['sport']; 
                            if (isset($_GET['date'])) {
                                $dateParam = "&date=" . $_GET['date'];
                            } else {
                                $dateParam = "";
                            };
                            $json = file_get_contents("https://statsapi.mlb.com/api/v1/schedule?sportId={$sportId}{$dateParam}");
                            $schedule = json_decode($json, true);
                            foreach($schedule['dates'] as $date) {
                                
                                echo "<h2>Select a Game &mdash; {$date['date']}</h2>
                                <form action=\"scoreMe.php\" method=\"GET\">
                                    <select name=\"gameId\" id=\"gameId\" accesskey=\"target\">
                                        <option value=\"none\" selected>Choose a Game</option>
                                        ";

                                foreach($date['games'] as $game) {
                                    if($game['doubleHeader'] == "Y") {
                                        $dh_note = " (Game " . $game['gameNumber'] . ")";
                                    } else { 
                                        $dh_note = "";
                                    };

                                    echo "<option value=\"{$game['gamePk']}\">{$game['teams']['home']['team']['name']} vs. {$game['teams']['away']['team']['name']}{$dh_note} </option>
                                    ";
                                };
                            };
                        ?>
                    </select>
                    <button type="submit">Next &gt;</button>
                    </form>
            </article>
        </section>
    </body>
</html>