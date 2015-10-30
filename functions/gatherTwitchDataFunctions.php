<?php

date_default_timezone_set('America/New_York');

function getAndProcessData()
{
    $numOfResults = 6; //low number for testing my database stuff, set to 9999 once that's all working
    $jsonData = getTwitchDataJSON($numOfResults);

    if (count((array)$jsonData)) //make sure the object was populated
    {
        //open up a database connection
        require('dbcreds.php'); //import the database credentials
        $conn = mysqli_connect($db_hostname, $db_username, $db_password, $default_db);

        if($conn)
        {
            processTwitchDataResponseJSON($jsonData, $conn);
            $conn->close();
        }
    }
}

function getTwitchDataJSON($numRes)
{
    $numOfResults = $numRes;
    $reqURL = 'http://api.twitch.tv/kraken/games/top?limit=' . $numOfResults . '&on_site=1';
    $twitchData = file_get_contents($reqURL);
    $jsonData = json_decode($twitchData);
    return $jsonData;
}

function processTwitchDataResponseJSON($responseJSON, $dbConnection)
{
    foreach($responseJSON->top as $gameParent)
    {
        $title = $gameParent->game->name;
        $viewers = $gameParent->viewers;
        $timestamp = date('Y/m/d H:i:s');

        //make title safe and reformat it to be a table name
        $title = preg_replace('/[^a-zA-Z0-9\s]/', '', $title); //remove non-alphanumeric characters
        $title = str_replace(' ', '_', $title);
        $title = strtolower($title);

        //query for a table with a tablename that matches $title
        $query = "SELECT * FROM " . $title . ";";
        if($result = $dbConnection->query($query))
        {
            $query = "SELECT * FROM " . $title . " ORDER BY id DESC LIMIT 1;"; //get last row in the table
            if($lastRow = $dbConnection->query($query))
            {
                //add first row to the table if there arent any rows yet
                $query = "insert into " . $title . " (viewers, timestamp) values (" . $viewers . ", '" . $timestamp . "');";
                $dbConnection->query($query);
            }

            $result->free();
        }
        else {
            $query = "CREATE TABLE " . $title . " (id int(8) not null primary key auto_increment, viewers int(8) not null, timestamp varchar(255) not null);";
            $dbConnection->query($query);
        }
        echo $gameParent->game->name . '<br />';
    }
}

?>
