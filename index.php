<?php

$games = array(
    "1942",
    "galaga",
    "mspacman",
    "pacman",
    "dkong",
    "dkong2"
);

$loaded_games = array();
foreach ($games as $game) {
    $gameLoc = gameLocation($game);
    if (file_exists($gameLoc)) {
        array_push($loaded_games, $game);
    }
}

$games_arr = array();
$games_arr["games"] = array();

foreach ($loaded_games as $game) {
    $score_data = null;
    $data = array(
        "scoreData" => getGameData($game),
        "game" => $game
    );
    array_push($games_arr["games"], $data);
}

http_response_code(200);
echo json_encode($games_arr);

function gameLocation($game) {
    $baseLocation = getenv('HISCORE_LOCATION', true) ? getenv('HISCORE_LOCATION') : "/home/pi/RetroPie/roms/mame-libretro/mame2003/hi/";
    return $baseLocation . $game . ".hi";
}
function getGameData($game) {
    $game_file = gameLocation($game);
    switch ($game) {
        case "1942":
            $data = nineteen42($game_file);
            return $data;
        case "mspacman":
            return array(
                "score" => pacman($game_file)
            );
        case "galaga":
            return galaga($game_file);
        case "pacman":
            return array(
                "score" => pacman($game_file)
            );
        case "dkong":
            return dkongData($game_file);
        case "dkong2":
            return dkongData($game_file);
        default:
            return debug($game_file);
    }
}

function debug($file) {
    $fp = fopen($file, "r") or die("cannot open file!");
    $bytes = array();
    while (!feof($fp)) {
        $buf = fread($fp, 1);
        array_push($bytes, bin2hex($buf));
    }
    fclose($fp);
    return array(
        "size" => count($bytes),
        "bytes" => $bytes
    );
}


function dkongData($file) {
    //http://forum.arcadecontrols.com/index.php/topic,83614.0.html?PHPSESSID=9tv47t95nisk3b90dn506d7nmn
    $fp = fopen($file, "r") or die("cannot open file!");
    $bytes = array();
    while (!feof($fp)) {
        $buf = fread($fp, 1);
        array_push($bytes, bin2hex($buf));
    }
    fclose($fp);
    $scores      = array();
    $placeholder = 1;
    $row         = 0;
    $rowLength   = 34;
    while ($row < 5) {
        $rowData  = array();
        $startPos = $rowLength * $row;
        $row++;
        $endPos = $row * $rowLength;
        for ($i = $startPos; $i < $endPos; $i++) {
            array_push($rowData, $bytes[$i]);
        }
        $score = "";
        for ($i = 7 + $startPos; $i < 13 + $startPos; $i++) {
            $score = $score . hexdec($bytes[$i]);
        }
        $initials = "";
        for ($i = 15 + $startPos; $i < 18 + $startPos; $i++) {
            $strtemp  = sprintf("%c", 48 + (hexdec($bytes[$i])));
            $strtemp = str_replace("@", " ", $strtemp);
            $initials = $initials . $strtemp;
        }
        $scoreData = array(
            "score" => intval($score),
            "scoreWithLeadingZeros" => $score,
            "initials" => $initials
        );
        array_push($scores, $scoreData);
    }
    return array(
        "scores" => $scores
    );
}

function nineteen42($file) {
    //1942 hiscore parse
    //1942 has a 26 line long Hex data file arranged into a fairly readable format
    //First byte [0] is the index of the score 00-25. 55 means it's the TOP score
    //[1-4] is the score value
    //[5-12(c)] is the name
    //13 is the level reached
    //14-15 seem to be 00 in all cases
    if (!$file) {
        echo "ERROR: No file specified";
        exit();
    }
    $fp = fopen($file, "r") or die("cannot open file!");
    $bytes      = array();
    $scoretable = array(
        "score" => array(),
        "initials" => array(),
        "top" => 0,
        "level" => array()
    );
    while (!feof($fp)) {
        $buf = fread($fp, 1);
        array_push($bytes, bin2hex($buf));
    }
    reset($bytes); //Start from the beginning
    $position = 0;
    for ($lineCounter = 0; $lineCounter < 26; $lineCounter++) {
        //Walk the bytes
        $index = hexdec($bytes[$position]);
        $score = sprintf("%s%s%s%s", $bytes[$position + 1], $bytes[$position + 2], $bytes[$position + 3], $bytes[$position + 4]);
        $level = $bytes[$position + 13];
        $temp  = array();
        for ($k = 5; $k < 13; $k++) {
            if (hexdec($bytes[$position + $k] != 30)) {
                $strtemp = sprintf("%c", (55 + hexdec($bytes[$position + $k])));
                array_push($temp, $strtemp);
            } else {
                array_push($temp, " ");
            }
        }
        $name                           = sprintf("%s%s%s%s%s%s%s%s", $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5], $temp[6], $temp[7]);
        $scoretable["score"][$index]    = ltrim($score, '0');
        $scoretable["initials"][$index] = $name;
        $scoretable["level"][$index]    = $level;
        $position                       = $position + 16;
    }
    return $scoretable;
}

function pacman($file) {
    /*
    This function should handle hiscores for any standard pacman hardware
    so I made it take a file parameter for the clones and sequels the default
    is puckman
    */
    if (!$file) {
        echo "ERROR: No file specified";
        exit();
    }
    $bytes = array();
    $fp = fopen($file, "r") or die("cannot open file!");
    while (!feof($fp)) {
        $buf = fread($fp, 1);
        array_push($bytes, bin2hex($buf));
    }
    fclose($fp);
    $bytes = array_reverse($bytes);
    return getScore($bytes, 40, 2);
}
function galaga($file) {
    /*
    Notes: galaga stores all the scores one after another 6 numbers max each
    24 is used for null so if your score was 30000 it would be stored
    00 00 00 00 03 24 there are only 5 stored scores and right after the scores
    are the initials stored A to Z starting at hex value 00 and ending at hex value 24
    the very last set of 6 values after the 5 initials sets is the top score by itself
    */
    if (!$file) {
        echo "ERROR: No file specified";
        exit();
    }
    $scoretable = array(
        "score" => array(),
        "initials" => array(),
        "top" => 0
    );
    $bytes      = array();
    $fp = fopen($file, "r") or die("cannot open file!");
    while (!feof($fp)) {
        $buf = fread($fp, 1);
        array_push($bytes, bin2hex($buf));
    }
    fclose($fp);
    reset($bytes);
    $nullValue = 24;
    $offset = 0;
    for ($i = 0; $i < 5; $i++) {
        /*
        parse and combine the 5 TOP SCORES
        */
        $temp = array();
        for ($k = 0; $k < 6; $k++) {
            array_push($temp, current($bytes));
            next($bytes);
        }
        $temp = array_reverse($temp);

        array_push($scoretable["score"], getScore($temp, $nullValue, $offset));
    }
    for ($i = 0; $i < 5; $i++) {
        /*
        Parse and combine the Initials data
        */
        $temp = array();
        for ($k = 0; $k < 3; $k++) {
            /*
            This is me being lazy what I **should** have done and will have to for other games
            is made a static array character map but there are only 2 extra characters so I
            took the easy way out this time
            */
            $strtemp = sprintf("%c", (55 + hexdec(current($bytes))));
            $strtemp = str_replace("a", ".", $strtemp);
            $strtemp = str_replace("[", " ", $strtemp);
            array_push($temp, $strtemp);
            next($bytes);
        }
        array_push($scoretable["initials"], sprintf("%s%s%s", $temp[0], $temp[1], $temp[2]));
    }
    $temp = array();
    for ($i = 0; $i < 6; $i++) {
        /*
        This is the last of the data in the file, the TOP SCORE
        */
        array_push($temp, current($bytes));
        next($bytes);
    }
    $temp = array_reverse($temp);
    $scoretable["top"] = getScore($temp, $nullValue, $offset);
    return $scoretable;
}

function getScore($bytes, $nullValue, $offset) {
    $placeholder = 100000;
    $score = 0;
    while ($placeholder > 1) {
        if ($bytes[$offset] != $nullValue) {
            $score = $score + $bytes[$offset] * $placeholder;
        }
        $placeholder = $placeholder / 10;
        $offset++;
    }
    return $score;
}
function addOrdinalNumberSuffix($num) {
    //This snippet was from https://www.if-not-true-then-false.com/2010/php-1st-2nd-3rd-4th-5th-6th-php-add-ordinal-number-suffix/
    //Thanks snippet JR person
    if (!in_array(($num % 100), array(
        11,
        12,
        13
    ))) {
        switch ($num % 10) {
            // Handle 1st, 2nd, 3rd
            case 1:
                return $num . 'st';
            case 2:
                return $num . 'nd';
            case 3:
                return $num . 'rd';
        }
    }
    return $num . 'th';
}
?>