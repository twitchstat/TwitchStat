<?php

function getTwitchDataJSON($numRes)
{
    $numOfResults = $numRes;
    $reqURL = 'http://api.twitch.tv/kraken/games/top?limit=' . $numOfResults . '&on_site=1';
    $twitchData = file_get_contents($reqURL);
    $jsonData = json_decode($twitchData);
    return $jsonData;
}


?>
