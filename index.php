<?php
//eventually, all of this PHP should be kicked out to a separate file.  remember MVC structure.  V is this page, C is the logic here, and M is the database shit.
    date_default_timezone_set('America/New_York');

    include 'functions/getTwitchDataJSON.php';

    $numOfResults = 4; //low number for testing my database stuff, set to 9999 once that's all working
    $jsonData = getTwitchDataJSON($numOfResults);


// gatherAndStoreTwitchStatistics($jsonData)
// TODO: replace all sql shit with SAFE queries to prevent sql injection
//////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (count((array)$jsonData)) //make sure the object was populated
    {
        //open up a database connection
        require('dbcreds.php'); //import the database credentials
        $conn = mysqli_connect($db_hostname, $db_username, $db_password, $default_db);
        if(!$conn)
        {
            echo "Couldn't connect to the database.";
        }

        foreach($jsonData->top as $gameParent)
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
            if($result = $conn->query($query))
            {
                $query = "SELECT * FROM " . $title . " ORDER BY id DESC LIMIT 1;"; //get last row in the table
                if($lastRow = $conn->query($query))
                {
                    //add first row to the table if there arent any rows yet
                    $query = "insert into " . $title . " (viewers, timestamp) values (" . $viewers . ", '" . $timestamp . "');";
                    $conn->query($query);
                }

                $result->free();
            }
            else {
                $query = "CREATE TABLE " . $title . " (id int(8) not null primary key auto_increment, viewers int(8) not null, timestamp varchar(255) not null);";
                $conn->query($query);
            }

        }

        $conn->close();
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>TwitchStat</title>
    </head>
    <body>

    </body>
</html>
