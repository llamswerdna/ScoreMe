<html>
    <head>
        <title>ScoreMe &mdash; Select League</title>
        <?php 
            // pull in viewport
            include('htmlHead.html');
        ?>
        <style>

            <?php 
            // pull in CSS
            include('scoreMe.css');
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
        </header>
        <section>
            <article>
                <h2>Select a League / Level</h2>
                <form action="gameSelect.php" method="GET">
                    <select name="sport" id="sport" accesskey="target">
                        <option value='none' selected>Choose a League / Sport</option>
                        <?php
                            $json = file_get_contents('https://statsapi.mlb.com/api/v1/sports');
                            $sports = json_decode($json, true);
                            foreach($sports['sports'] as $sport) {
                                echo "<option value=\"{$sport['id']}\">{$sport['name']}</option>";
                            };
                        ?>
                    </select>
                    <input type="date" name="date">
                    <button type="submit">Next &gt;</button>
                    </form>
            </article>
        </section>
    </body>
</html>