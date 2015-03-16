<?PHP
ob_start();
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("loggedin.php");
require_once("google.php");
require_once("../key.php");

if ((isset($_POST['entry'])) OR ( isset($_POST['priorityreq']))) {
    $safeGUID = htmlspecialchars($db->sql_escape(strip_tags($_POST["GUID"])));
    $safecounty = htmlspecialchars($db->sql_escape(strip_tags($_POST["county"])));
    $initial_type = $db->sql_escape(htmlspecialchars(strip_tags($_GET['type'])));
    $sql = 'SELECT * FROM ' . USER_GROUP_TABLE . ' WHERE user_id = ' . (int) $user->data['user_id'];
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        // Create the SQL statement
        $sql = 'SELECT * FROM `phpbb_calllog_priority` WHERE FK_GUID = \'' . $safeGUID . '\' AND FK_county = \'' . $safecounty . '\' AND FK_county !=\'M\' AND TIMESTAMPDIFF(MINUTE, timestamp, NOW()) < 30';

        // Run the query 
        $prirowresult = $db->sql_query($sql);

        // $row should hold the data you selected
        $prirow = $db->sql_fetchrow($prirowresult);
        if ($row['group_id'] == 8) {
            if ($_POST['priorityreq'] == '1' AND ( isset($_POST['priorityreq']))) {
                if (empty($prirow['FK_user_id'])) {
                    $sql_arr = array(
                        'FK_user_id' => $user->data['user_id'],
                        'FK_GUID' => $safeGUID,
                        'FK_county' => $safecounty
                    );
                    $sql = 'REPLACE INTO `phpbb_calllog_priority` ' . $db->sql_build_array('INSERT', $sql_arr);
                    $db->sql_query($sql);
                    header("location: http://cad.oregon911.net/units?call=" . $safeGUID . "&county=" . $safecounty . "&type=" . $initial_type);
                }
            } elseif ($_POST['priorityreq'] == '0' AND ( isset($_POST['priorityreq']))) {
                $sql = 'DELETE FROM `phpbb_calllog_priority` WHERE FK_user_id = \'' . $user->data['user_id'] . '\' AND FK_GUID = \'' . $safeGUID . '\' AND FK_county =\'' . $safecounty . '\'';
                $db->sql_query($sql);
                header("location: http://cad.oregon911.net/units?call=" . $safeGUID . "&county=" . $safecounty . "&type=" . $initial_type);
            } else {
                if ($user->data['user_id'] == $prirow['FK_user_id']) {
                    $safeentry = $db->sql_escape(strip_tags($_POST["entry"]));
                    if (strlen($safeentry) > 4) {
                        //Array with the data to insert
                        $sql_array = array(
                            'GUID' => $safeGUID,
                            'county' => $safecounty
                        );

                        // Create the SQL statement
                        $sql = 'SELECT GUID, county, lat, lon FROM `oregon911_cad`.`pdx911_calls` WHERE county != \'M\' AND ' . $db->sql_build_array('SELECT', $sql_array) . ' UNION ALL SELECT GUID, county, lat, lon FROM `oregon911_cad`.`pdx911_archive` WHERE county != \'M\' AND ' . $db->sql_build_array('SELECT', $sql_array);

                        // Run the query 
                        $result = $db->sql_query($sql);

                        // $row should hold the data you selected
                        $callrow = $db->sql_fetchrow($result);

                        // Be sure to free the result after a SELECT query                        
                        $db->sql_freeresult($result);

                        // Show we got the result we were looking for
                        if ($callrow['GUID'] = $safeGUID && $callrow['county'] = $safecounty) {
                            $sql_arr = array(
                                'FK_user_id' => $user->data['user_id'],
                                'FK_GUID' => $safeGUID,
                                'FK_county' => $safecounty,
                                'entry' => $safeentry
                            );
                            $sql = 'INSERT INTO `phpbb_calllog_entries` ' . $db->sql_build_array('INSERT', $sql_arr);
                            $db->sql_query($sql);


                            if (strlen($safeentry) > 110) {
                                $safeentry = substr($safeentry, 0, 110) . '...';
                            }
                            file_get_contents("http://www.api.oregon911.net/api/1.0/?method=cadentriestwitter&message=" . (urlencode($safeentry . " http://cad.oregon911.net/units?call=" . $safeGUID . "&county=" . $safecounty . "&type=" . $initial_type)) . "&lat=" . $callrow['lat'] . "&lon=" . $callrow['lon'] . "&type=JSON&key=$key");
                        }
                        header("location: http://cad.oregon911.net/units?call=" . $safeGUID . "&county=" . $safecounty . "&type=" . $initial_type);
                        break;
                    }
                }
            }
        }
    }
}

// Initial Call Details
if ((!empty($_GET['call'])) && (!empty($_GET['county']))) {
// Declare Variables
    $GUID = $db->sql_escape(htmlspecialchars(strip_tags($_GET['call'])));
    $county = $db->sql_escape(htmlspecialchars(strip_tags($_GET['county'])));
    $initial_type = $db->sql_escape(htmlspecialchars(strip_tags($_GET['type'])));

    if (strtoupper($initial_type) == 'P') {

        $initial_type = 'P';
    } else if (strtoupper($initial_type) == 'M') {

        $initial_type = 'M';
    } else if (strtoupper($initial_type) == 'F') {
        $initial_type = 'F';
    } else {

        $initial_type = '%';
    }

    $found = false;
    $active = false;

// Create the SQL statement
    $sql = "SELECT callSum, U.address, type, U.station, units, (Select DISTRICT From `oregon911_cad`.`pdx911_stations` AS S WHERE U.STATION = S.ABBV and U.county = S.county) as agency, U.lat, U.lon, icon, timestamp, 1 AS CACT FROM `oregon911_cad`.`pdx911_calls` AS U WHERE U.GUID = '" . $GUID . "' AND U.county = '" . $county . "' AND U.county != 'M' AND U.type LIKE '" . $initial_type . "'  UNION ALL SELECT callSum, U.address, type, U.station, units, (Select DISTRICT From `oregon911_cad`.`pdx911_stations` AS S WHERE U.STATION = S.ABBV and U.county = S.county AND S.county != 'M') as agency, U.lat, U.lon, icon, timestamp, 0 AS CACT FROM `oregon911_cad`.`pdx911_archive` AS U WHERE U.county != 'M' AND U.GUID = '" . $GUID . "' AND U.county = '" . $county . "'  AND U.type LIKE '" . $initial_type . "' ";

// Run the query 
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);

    $callSum = $row['callSum'];
    $address = $row['address'];
    $type = $row['type'];
    $station = $row['station'];
    $units = $row['units'];
    $agency = $row['agency'];
    $lat = $row['lat'];
    $long = $row['lon'];
    $icon = $row['icon'];
    $ts = $row['timestamp'];
    if (!empty($callSum)) {
        $found = true;
    }

    if ($row['CACT'] == 1) {
        $active = true;
    }
}

if ($found) {
// Theme / Text Output
    $type_output = "Fire";
    if ($type == "P") {
        $type_output = "Police";
        if ($county == "W") {
            $theme = ('class="wccca-details"');
        } else {
            $theme = ('class="ccom-details"');
        }
    } else if ($type == "F") {
        $type_output = "Fire";
        if ($county == "W") {
            $theme = ('class="wccca-details"');
        } else {
            $theme = ('class="ccom-details"');
        }
    } else if ($type == "M") {
        $type_output = "Medical";
        if ($county == "W") {
            $theme = ('class="wccca-details"');
        } else {
            $theme = ('class="ccom-details"');
        }
    } else {
        $type_output = "Fire";
        if ($county == "W") {
            $theme = ('class="wccca-details"');
        } else {
            $theme = ('class="ccom-details"');
        }
    }

// AJAX Requests
    if ($_GET['AJAX_REFRESH'] == "callinfo") {
        ?>
        <table>
            <tr>
                <th scope="row">Call Type</th>
                <td><?php
                    echo ('<a href="./search?county=' . $county . '&calltype=' . urlencode($callSum) . '">' . $callSum . '</a>');
                    ?></td>
            </tr>
            <tr>
                <th scope="row">Call Number</th>
                <td><?php
                    echo ($GUID);
                    ?></td>
            </tr>
            <tr>
                <th scope="row">Type</th>
                <td><?php
                    echo ($type_output);
                    ?></td>
            </tr>
            <tr>
                <th scope="row">Call Date</th>
                <td><?php
                    echo ($ts);
                    ?></td>
            </tr>
            <tr>
                <th scope="row">Address</th>
                <td><?php
                    echo ('<a href="./search?county=' . $county . '&address=' . urlencode($address) . '">' . $address . '</a>');
                    ?></td>
            </tr>
            <tr>
            <tr>
                <th scope="row">Agency</th>
                <td><?php
                    if (!empty($agency)) {
                        echo ("<a href=\"./agency?agency=" . rawurlencode($agency) . "&county=" . $county . "\">" . $agency . "</a>");
                    } else {
                        echo ("Unknown");
                    }
                    ?></td>
            </tr>
            <th scope="row">Station</th>
            <td><?php
                echo ('<a href="./station?station=' . $station . '&county=' . $county . '">' . $station . '</a>');
                ?></td>
        </tr>
        <tr>
            <th scope="row">Units</th>
            <td><?php
                if (!$initial_type == 'P') {
                    $initial_type = "%";
                }
                $sql = "Select * from `oregon911_cad`.`pdx911_units` WHERE county != 'M' AND GUID = '" . $GUID . "' AND county = '" . $county . "' AND type LIKE '" . $initial_type . "' ";
                // Run the query
                $result2 = $db->sql_query($sql);
                $UOUTPUT = '';
                while ($unit = $result2->fetch_assoc()) {
                    if ($unit['clear'] != '00:00:00') {
                        $UOUTPUT .= '<span class="clear" title="Cleared @ ' . $unit['clear'] . '">' . $unit['unit'] . '</span>, ';
                    } else if ($unit['onscene'] != '00:00:00') {
                        $UOUTPUT .= '<span class="onscene" title="Onscene @ ' . $unit['onscene'] . '">' . $unit['unit'] . '</span>, ';
                    } else if ($unit['enroute'] != '00:00:00') {
                        $UOUTPUT .= '<span class="enroute" title="Enroute @ ' . $unit['enroute'] . '">' . $unit['unit'] . '</span>, ';
                    } else if ($unit['dispatched'] != '00:00:00') {
                        $UOUTPUT .= '<span class="dispatched" title="Dispatched @ ' . $unit['dispatched'] . '">' . $unit['unit'] . '</span>, ';
                    }
                }
                if ($UOUTPUT) {
                    echo (substr($UOUTPUT, 0, -2));
                } else {
                    echo($units);
                }
                ?></td>
        </tr>
        <?PHP
        if ((strtolower($address) == strtolower("WCCCA 911")) OR ( strtolower($address) == strtolower("17911 NW EVERGREEN PK")) OR ( strpos(strtolower($callSum), 'drill') !== false) OR ( strpos(strtolower($callSum), 'test') !== false)) {
            ?>
            <tr>
                <th scope="row">Additional Info</th>
                <td style="color:red; background-color:yellow;">This address/call type is known to be a testing/drill address/call type.</td>
            </tr>
            <?PHP
        }
        ?>
        <tr>
            <th scope="row">Status</th>
            <?PHP
            If ($active) {
                echo ('<td style="color:green;">ACTIVE</p>');
            } else {
                echo ('<td style="color:red;">CLEARED</p>');
            }
            ?>
            </td>
        </tr>
        </table>
        <?PHP
        exit;
        // WARINING!!!!! TYPE SYSTEM DOES NOT WORK FOR ALL OF THESE YET! MAINLY CALL LOGS
    } elseif ($_GET['AJAX_REFRESH'] == "calllog") {
// Call Log Entries 
        echo '<table style="width:100%;">';
        echo '<tr><th style="width:10%;">Date</th><th style="width:10%;">Author</th><th style="width:70%;">Entry</th></tr>';
        $sql = "SELECT user_id, timestamp, username, entry FROM `phpbb_calllog_entries` JOIN `phpbb_users` ON FK_user_id = user_id WHERE FK_GUID = '$GUID' AND FK_county = '$county' order by timestamp DESC LIMIT 15";
        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            $sql = "SELECT pf_twitter FROM oregon911_net_1.phpbb_profile_fields_data WHERE pf_twitter != '' AND user_id = '" . $row['user_id'] . "'";
            $result2 = $db->sql_query($sql);
            $twitter = $db->sql_fetchrow($result2);
            echo '<tr><th>' . $row['timestamp'] . '</th><th>';
            if (!empty($twitter['pf_twitter'])) {
                echo '<a href="//www.twitter.com/' . str_replace("@", "", $twitter['pf_twitter']) . '">' . $row['username'] . '</a>';
            } else {
                echo '<a href="//www.oregon911.net/discussion/memberlist.php?mode=viewprofile&u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
            }
            echo '</th><th>' . $row['entry'] . '</th></tr>';
        }
        echo '</table>';
        exit;
    } elseif ($_GET['AJAX_REFRESH'] == "calllog-all") {
// Call Log Entries 
        echo '<table style="width:100%;">';
        echo '<tr><th style="width:10%;">Date</th><th style="width:10%;">Author</th><th style="width:70%;">Entry</th></tr>';
        $sql = "SELECT user_id, timestamp, username, entry FROM `phpbb_calllog_entries` JOIN `phpbb_users` ON FK_user_id = user_id WHERE FK_GUID = '$GUID' AND FK_county = '$county' order by timestamp DESC";
        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            echo '<tr><th>' . $row['timestamp'] . '</th><th><a href="//www.oregon911.net/discussion/memberlist.php?mode=viewprofile&u=' . $row['user_id'] . '">' . $row['username'] . '</a></th><th>' . $row['entry'] . '</th></tr>';
        }
        echo '</table>';
        exit;
    } elseif ($_GET['AJAX_REFRESH'] == "units") {
        if (!$initial_type == 'P') {
            $initial_type = "%";
        }
// Call ID Table
        echo '<table style="width:100%;">';
        echo '<tr><th>Unit</th><th>Agency</th><th>Station</th><th>Dispatched</th><th>Enroute<th>Onscene</th><th>Clear</th><th>Distance (from station)</th></tr>';
        $sql = "SELECT *, S.DISTRICT as agency, S.ABBV as station FROM `oregon911_cad`.`pdx911_units` aS U LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.STATION = ABBV and U.county = S.county WHERE U.type LIKE '" . $initial_type . "' AND U.GUID = '$GUID' and U.county = '$county' AND U.unit NOT IN (Select flag From `oregon911_cad`.`pdx911_callSum_flags`)";
        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            echo '<tr><th><a href="./unitinfo?unit=' . $row['unit'] . '&county=' . $row['county'] . '">' . $row['unit'] . '</a></th><th><a href="./agency?agency=' . rawurlencode($row['agency']) . '&county=' . $county . '">' . $row['agency'] . '</a></th><th><a href="./station?station=' . $row['station'] . '&county=' . $row['county'] . '">' . $row['station'] . '</a></th>';
            if ($row['clear'] != '00:00:00') {
                echo( '<th>' . $row['dispatched'] . '</th>' . '<th>' . $row['enroute'] . '<th>' . $row['onscene'] . '</th>' . '<th style="background-color:#787878; color:white">' . $row['clear'] . '</th>');
            } else if ($row['onscene'] != '00:00:00') {
                echo( '<th>' . $row['dispatched'] . '</th>' . '<th>' . $row['enroute'] . '<th style="background-color:green; color:white">' . $row['onscene'] . '</th>' . '<th>' . $row['clear'] . '</th>');
            } else if ($row['enroute'] != '00:00:00') {
                echo( '<th>' . $row['dispatched'] . '</th>' . '<th style="background-color:#FFCC33; color:black">' . $row['enroute'] . '<th>' . $row['onscene'] . '</th>' . '<th>' . $row['clear'] . '</th>');
            } else if ($row['dispatched'] != '00:00:00') {
                echo( '<th style="background-color:#C82620; color:white">' . $row['dispatched'] . '</th>' . '<th>' . $row['enroute'] . '<th>' . $row['onscene'] . '</th>' . '<th>' . $row['clear'] . '</th>');
            }
            echo( '<th>~' . getDistance($row['unit'], $row['county'], $row['station'], $lat, $long, $db) . ' mi</th></tr>');
        }
        echo '</table>';
        exit;
    } elseif ($_GET['AJAX_REFRESH'] == "units-mobile") {
// Call ID Table
        if (!$initial_type == 'P') {
            $initial_type = "%";
        }
        echo '<table style="width:100%;">';
        echo '<tr><th>Unit</th><th>Status</th></tr>';
        $sql = "SELECT *, S.DISTRICT as agency, S.ABBV as station FROM `oregon911_cad`.`pdx911_units` aS U LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.STATION = ABBV and U.county = S.county WHERE U.type LIKE '" . $initial_type . "' AND U.GUID = '$GUID' and U.county = '$county' AND U.unit NOT IN (Select flag From `oregon911_cad`.`pdx911_callSum_flags`)";
        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            echo '<tr><th><a href="./unitinfo?unit=' . $row['unit'] . '&county=' . $row['county'] . '">' . $row['unit'] . '</a></th>';
            if ($row['clear'] != '00:00:00') {
                echo( '<th style="background-color:#787878; color:white">CLEAR</th>');
            } else if ($row['onscene'] != '00:00:00') {
                echo( '<th style="background-color:green; color:white">ON SCENE</th>');
            } else if ($row['enroute'] != '00:00:00') {
                echo( '<th style="background-color:#FFCC33; color:black">EN ROUTE</th>');
            } else if ($row['dispatched'] != '00:00:00') {
                echo( '<th style="background-color:#C82620; color:white">DISPATCHED</th>');
            }
        }
        echo '</table>';
        exit;
    } elseif ($_GET['AJAX_REFRESH'] == "flags") {
        if (!$initial_type != 'P') {
// Call ID Table
            echo '<table style="width:100%;">';
            echo '<tr><th>Unit</th><th>Dispatched</th><th>Clear</th></tr>';
            $sql = "SELECT * FROM `oregon911_cad`.`pdx911_units` WHERE GUID='$GUID' and county='$county' AND unit IN (Select flag From `oregon911_cad`.`pdx911_callSum_flags`)";
            $result = $db->sql_query($sql);
            while ($row = $result->fetch_assoc()) {
                echo '<tr><th>' . $row['unit'] . '</th><th>' . $row['dispatched'] . '</th><th>' . $row['clear'] . '</th></tr>';
            }
            echo '</table>';
        }
        exit;
    } elseif ($_GET['AJAX_REFRESH'] == "changelog") {
        echo '<table style="width:100%;">';
        echo '<tr><th>Timestamp</th><th>Change</th></tr>';
        $sql = "SELECT * FROM oregon911_cad.pdx911_records WHERE GUID='$GUID' and county='$county' order by timestamp DESC";

        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            switch ($row['update']) {
                case 1:
                    echo '<tr><th>' . $row['timestamp'] . '</th><th>' . $row['callSum'] . '</th></tr>';
                    break;
                case 2:
                    echo '<tr><th>' . $row['timestamp'] . '</th><th>' . $row['address'] . '</th></tr>';
                    break;
                case 3:

                    echo '<tr><th>' . $row['timestamp'] . '</th><th>Marker moved</th></tr>';
                    break;
            }
        }
        echo '</table>';
        exit;
    }

// Twitter Stuff
    $zoom = 14;
    if (isset($_GET['zoom'])) {
        $zoom = $_GET['zoom'];
    }
    $H = 300;
    if (isset($_GET['H'])) {
        $H = $_GET['H'];
    }
    $W = 530;
    if (isset($_GET['W'])) {
        $W = $_GET['W'];
    }
    $TwitterCardIMG = "https://maps.google.com/maps/api/staticmap?center=$lat,$long&markers=icon:http://cad.oregon911.net/$icon|$lat,$long&zoom=$zoom&size=" . $W . "x" . $H . "&sensor=false";
    if ($_GET['img'] == "Y") {
        $im = file_get_contents($TwitterCardIMG);
        header('content-type: image/gif');
        echo $im;
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Incident</title>


        <meta name="viewport" content="width=device-width, initial-scale=1">
       <meta name="twitter:card" content="summary_large_image">
        <?php
        if (($county == 'W') && ($type == 'P')) {
            ?><meta name="twitter:site" content="@Washco_Police"><?php
        } else if ($county == 'W') {
            ?><meta name="twitter:site" content="@Washco_FireMed"><?php
        } else {
            ?><meta name="twitter:site" content="@Clackco_FireMed"><?php
        }
        ?>

        <meta name="twitter:creator" content="@Oregon911">
        <meta name="twitter:title" content="<?php
        echo ($callSum);
        ?>">
        <meta name="twitter:description" content="ID: <?php
        echo ($GUID);
        ?> Address: <?php
              echo ($address);
              ?> Station: <?php
              echo ($station);
              ?> Units: <?php
              echo ($units);
              ?>">
        <meta name="twitter:image:src" content="<?php
        echo ("http://cad.oregon911.net/call?call=$GUID&county=$county&img=Y");
        ?>">

        <style type="text/css">
            html { height: 100% }
            body { height: 100%; margin: 0; padding: 0 }
            #map_canvas { height: 100% }
            .auto-style1 {
                margin-bottom: 337px;
            }
            iframe[id^='twitter-widget-']{ 
                width:100% !important;
                height:350px !important;
            }
        </style>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu"></a>
                Oregon 911 - Incident
            </div>
            <?PHP
            // Emergency Alert Code   
            // Create the SQL statement
            $sql = "SELECT Message FROM oregon911_net_1.phpbb_website_alerts WHERE starts < NOW() AND expires > NOW();";
            // Run the query 
            $result = $db->sql_query($sql);

            // $row should hold the data you selected
            $row = $db->sql_fetchrow($result);

            if ($row) {
                ?>
                <div id="alert">
                    <a class="alert" href="#alert"><?PHP echo($row['Message']); ?></a>
                </div> 
                <br>
                <br>
                <?PHP
            }
            ?>
            <div class="content">
                <?PHP
                if (!$found) {
                    if (!$mobile) {
                        ?>
                        <div style="padding-top: 50px;">
                            <div <?php
                            if ($county == "W") {
                                echo ('id="wccca-page-wrapper"');
                            } else {
                                if ($type == 'P') {
                                    echo ('id="com-page-wrapper"');
                                }
                            }
                            ?>>
                                <?PHP }
                                ?>
                            <div style="background-color: #FFF;">
                                <details <?php echo ($theme);
                                ?> open=true>
                                    <summary>Fatal Error</summary>
                                    <b> Call Not Found </b>
                                </details>
                            </div>
                            <?PHP
                            if (!$mobile) {
                                ?>
                            </div>
                        </div>
                        <?PHP
                    }
                } else {
                    if (!$mobile) {
                        ?>
                        <div style="padding-top: 20px;">
                            <div <?php
                            if ($county == "W") {
                                echo ('id="wccca-page-wrapper"');
                            } else {
                                echo ('id="ccom-page-wrapper"');
                            }
                            ?>>
                                    <?PHP
                                } else {
                                    echo('<div style="background-color: #FFF;">');
                                }
                                ?>
                            <h1> Call Log: <?php echo ($callSum); ?> </h1>

                            <center>
                                <?PHP
                                if (!$mobile) {
                                    echo($ad_336_280);
                                } else {
                                    echo($ad_320_100);
                                }
                                ?>
                            </center>
                            <!-- ================== This is where the main stuff happens ================= -->
                            <!-- Specifying an 'open' attribute will make all the content visible when the page loads -->
                            <details <?php echo ($theme); ?> open=true>
                                <summary>CALL <?php
                                    echo ($GUID);
                                    ?></summary>
                                <div id="callinfo"> </div>
                            </details>

                            <details <?php echo ($theme); ?> open=true>
                                <summary>Map</summary>
                                <div id="map_2385853" style="width:100%; height:300px;"></div>
                            </details>

                            <details <?php echo ($theme); ?> open=true>
                                <summary>Call Log</summary>

                                <?PHP
// Create the SQL statement
                                $sql = 'SELECT * FROM `phpbb_calllog_priority` WHERE FK_county != \'M\' AND FK_GUID = \'' . $GUID . '\' AND FK_county = \'' . $county . '\' AND TIMESTAMPDIFF(MINUTE, timestamp, NOW()) < 30';

// Run the query 
                                $prirowresult = $db->sql_query($sql);

// $row should hold the data you selected
                                $prirow = $db->sql_fetchrow($prirowresult);

                                $sql = 'SELECT * FROM ' . USER_GROUP_TABLE . ' WHERE user_id = ' . (int) $user->data['user_id'];
                                $result = $db->sql_query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    if ($row['group_id'] == 8) {
                                        if ((!isset($prirow['FK_user_id']))) {
                                            ?>
                                            <form method="post" action="<?PHP echo (GetURL()) ?>">
                                                <input type="hidden" name="priorityreq" value="1">
                                                <input type="hidden" name="GUID" value="<?PHP echo ($GUID); ?>" />
                                                <input type="hidden" name="county" value="<?PHP echo ($county); ?>" />
                                                <input type="hidden" name="type" value="<?PHP echo (htmlspecialchars(strip_tags($_GET['type']))); ?>" />
                                                <input type="submit" value="Assign Me To Call">
                                            </form>
                                            <?php
                                        } else {
                                            if ($prirow['FK_user_id'] == $user->data['user_id']) {
                                                ?>
                                                <form method="post" action="<?php echo (GetURL()) ?>">
                                                    <input type="hidden" name="GUID" value="<?php
                                                    echo ($GUID);
                                                    ?>" />
                                                    <input type="hidden" name="county" value="<?php
                                                    echo ($county);
                                                    ?>" />
                                                    <input type="hidden" name="type" value="<?PHP echo (htmlspecialchars(strip_tags($_GET['type']))); ?>" />
                                                    <table style="width:100%;">
                                                        <tr>
                                                            <th style="width:98%;"><input style="width:98%;" type="text" name="entry"></th>
                                                            <th><input type="submit" value="Submit"></th>
                                                        </tr>
                                                    </table>
                                                </form>
                                                <form method="post" action="<?PHP echo (GetURL()) ?>">
                                                    <input type="hidden" name="priorityreq" value="0">
                                                    <input type="hidden" name="GUID" value="<?php
                                                    echo ($GUID);
                                                    ?>" />
                                                    <input type="hidden" name="county" value="<?php
                                                    echo ($county);
                                                    ?>" />
                                                    <input type="hidden" name="type" value="<?PHP echo (htmlspecialchars(strip_tags($_GET['type']))); ?>" />
                                                    <input type="submit" value="Release Me From Call">
                                                </form>
                                                <?php
                                            } else {
                                                echo ("<b> Another user is assigned to this call, assignments expire after 30 minutes.</b>");
                                            }
                                        }
                                        break;
                                    }
                                }
                                ?>
                                <?PHP
                                // Create the SQL statement
                                $sql = "SELECT count(entry) as C FROM `phpbb_calllog_entries` JOIN `phpbb_users` ON FK_user_id = user_id WHERE FK_GUID = '$GUID' AND FK_county = '$county' order by timestamp DESC";

                                // Run the query 
                                $result = $db->sql_query($sql);

                                // $row should hold the data you selected
                                $count = $db->sql_fetchrow($result);

                                if ($_GET['request'] == "showall") {
                                    ?> <div id="calllog-all"> </div>
                                    <a href="<?PHP echo(str_replace("&request=showall", "", GetURL())); ?>">Show Less</a> <?PHP
                                } else {
                                    ?> <div id="calllog"> </div>
                                    <?PHP
                                    if ($count['C'] >= 15) {
                                        ?>
                                        <a href="<?PHP echo(GetURL() . '&request=showall'); ?>">Show All</a>
                                        <?PHP
                                    }
                                }
                                ?>
                            </details>

                            <?PHP if (!$mobile) { ?>
                                <details <?php echo ($theme); ?> open=true>
                                    <summary>Units</summary>
                                    <div id="units"> </div> 
                                </details>
                                <?PHP
                            } else {
                                ?>
                                <details <?php echo ($theme); ?> open=true>
                                    <summary>Units</summary>
                                    <div id="units-mobile"> </div> 
                                </details>
                                <?PHP
                            }
                            ?>

                            <details <?php echo ($theme); ?> open=true>
                                <summary>Flags</summary>
                                <div id="flags"> </div>
                            </details>

                            <details <?php echo ($theme); ?> open=true>
                                <summary>Call Change log</summary>
                                <div id="changelog"> </div>
                            </details>

                            <details <?php echo ($theme); ?> open=true>
                                <summary>Ad</summary>
                                <center>
                                    <?PHP
                                    if (!$mobile) {
                                        echo($ad_336_280);
                                    } else {
                                        echo($ad_320_100);
                                    }
                                    ?>
                                </center>
                            </details>

                            <?PHP if (!$mobile) { ?>
                                <details <?php echo ($theme); ?> open=true>
                                    <summary>Last 10 Calls From This Address</summary>
                                    <?php
                                    echo '<table style="width:100%;">';
                                    echo '<tr><th style="width: 20%;">Date</th><th style="width: 40%;">GUID</th style="width: 20%;"><th>Call</th><th style="width: 10%;">URL</th></tr>';
                                    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_archive` WHERE county != 'M' AND address='$address' and county='$county' and GUID != '$GUID' order by timestamp DESC Limit 10";
                                    $result = $db->sql_query($sql);
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<tr><th>' . $row['timestamp'] . '</th><th>' . $row['GUID'] . '</th><th>' . $row['callSum'] . '</th><th><a href="./call?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">Call Log</a></th></tr>';
                                    }
                                    echo '</table>';
                                    ?>
                                </details>
                                <?PHP
                            } else {
                                ?>
                                <details <?php echo ($theme); ?> open=true>
                                    <summary>Last 10 Calls From This Address</summary>
                                    <?php
                                    echo '<table style="width:100%;">';
                                    echo '<tr><th>Date</th><th>Call</th><th>URL</th></tr>';
                                    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_archive` WHERE county != 'M' AND address='$address' and county='$county' and GUID != '$GUID' order by timestamp DESC Limit 10";
                                    $result = $db->sql_query($sql);
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<tr><th>' . $row['timestamp'] . '</th><th>' . $row['callSum'] . '</th><th><a href="./call?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">Call Log</a></th></tr>';
                                    }
                                    echo '</table>';
                                    ?>
                                </details>
                                <?PHP
                            }
                            ?>

                            <details <?php echo ($theme); ?> open=true>
                                <summary>Social Media</summary>
                                <div style="width: 98%; margin-left: auto; margin-right: auto;">
                                    <details <?php
                                    if ($county == "W") {
                                        echo ('class="wccca-details"');
                                    } else {
                                        echo ('class="ccom-details"');
                                    }
                                    ?> style="margin-top: 10px;" open='true'>
                                        <summary>Twitter</summary>
                                        <a class="twitter-timeline" href="//twitter.com/Oregon911" data-widget-id="<?php
                                           if ($county == "W") {
                                               echo ("480616657857941506");
                                           } elseif ($county == "C") {
                                               echo ("480616506204504065");
                                           } else {
                                               echo ("480609539121627136");
                                           }
                                           ?>">Tweets by @Oregon911</a>
                                    </details>
                                </div>
                            </details>
                            <!-- ====================================================================================== -->
                            <?php
                            $time = microtime();
                            $time = explode(' ', $time);
                            $time = $time[1] + $time[0];
                            $finish = $time;
                            $total_time = round(($finish - $start), 4);
                            echo '<p>Page generated in ' . $total_time . ' seconds.</p>';
                            echo '<p>Copyright &copy; ' . date("Y") . ' Brandan Lasley. All Rights Reserved.</p>';
                            ?>
                            <?PHP
                        }
                        if (!$mobile) {
                            ?>
                        </div>

                    <?PHP } ?>
                </div>
            </div>

            <?PHP include ("./inc/nav.php"); ?>

        </div>
        <script type = "text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
        <script type='text/javascript' src="//maps.google.com/maps/api/js?sensor=false&extn=.js"></script>
        <script async>!function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                if (!d.getElementById(id)) {
                    js = d.createElement(s);
                    js.id = id;
                    js.src = p + "://platform.twitter.com/widgets.js";
                    fjs.parentNode.insertBefore(js, fjs);
                }
            }(document, "script", "twitter-wjs");
        </script>
        <script>
            $(document).ready(function () {
                auto_refresh();
            });
            function auto_refresh() {
                $('#callinfo').load('?AJAX_REFRESH=callinfo&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#calllog-all').load('?AJAX_REFRESH=calllog-all&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#calllog').load('?AJAX_REFRESH=calllog&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#units').load('?AJAX_REFRESH=units&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#units-mobile').load('?AJAX_REFRESH=units-mobile&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#flags').load('?AJAX_REFRESH=flags&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#changelog').load('?AJAX_REFRESH=changelog&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
            }
<?PHP if ($active) { ?>
                var refreshId = setInterval(auto_refresh, 20000);
<?PHP } ?>
        </script>
        <script async type='text/javascript'>
            var locations = {};
            var locs = {
<?PHP
$CallIDM = 1;

// Calls
echo ("$CallIDM: { info:'" . $type . " " . $station . " " . $GUID . " " . str_replace("'", "\'", $callSum) . " | " . $address . "  |" . $units . "', lat:" . $lat . ", lng:" . $long . ", icon:'" . $icon . "' }");
$CallIDM++;

// Stations 
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_stations` ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    if ($rows['ABBV'] != "UNK") {
        $station = $rows['ABBV'];
    } else {
        $station = $rows['STATION'];
    }

    if ($CallIDM == 1) {
        echo ("$CallIDM: { info:'" . "Fire Station: " . $station . " City: " . $rows['CITY'] . " Agency: " . $rows['DISTRICT'] . " Address: " . $rows['ADDRESS'] . "', lat:" . $rows['LAT'] . ", lng:" . $rows['LON'] . ", icon:'../images/MISC/firedept.png' }");
    } else {
        echo (",$CallIDM: { info:'" . "Fire Station: " . $station . " City: " . $rows['CITY'] . " Agency: " . $rows['DISTRICT'] . " Address: " . $rows['ADDRESS'] . "', lat:" . $rows['LAT'] . ", lng:" . $rows['LON'] . ", icon:'../images/MISC/firedept.png' }");
    }
    $CallIDM++;
}

// Hospitals 
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_hospitals` ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    echo (",$CallIDM: { info:'" . "Hospital: " . str_replace("'", "\'", $rows['NAME']) . " City: " . str_replace("'", "\'", $rows['CITY']) . " Address: " . str_replace("'", "\'", $rows['ADDRESS']) . "', lat:" . $rows['LAT'] . ", lng:" . $rows['LON'] . ", icon:'../images/MISC/hospital.png' }");
    $CallIDM++;
}

// Airports 
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_airports` ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    echo (",$CallIDM: { info:'" . "Airport: " . str_replace("'", "\'", $rows['NAME']) . " Address: " . str_replace("'", "\'", $rows['ADDRESS']) . "', lat:" . $rows['LAT'] . ", lng:" . $rows['LON'] . ", icon:'../images/MISC/airport.png' }");
    $CallIDM++;
}

// Lifeflight 
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_lifeflight` ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    echo (",$CallIDM: { info:'" . "Name: " . $rows['NAME'] . " Type: " . $rows['UTYPE'] . " Address: " . $rows['ADDRESS'] . "', lng:" . $rows['LON'] . ", icon:'../images/MISC/lifeflight.png' }");
    $CallIDM++;
}
?>
            };

            var map = new google.maps.Map(document.getElementById('map_2385853'), {
                zoom: 15,
                streetViewControl: true,
                center: new google.maps.LatLng(<?PHP
echo ($lat);
?>, <?PHP
echo ($long);
?>),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });


            var infowindow = new google.maps.InfoWindow();

            var auto_remove = true;

            function setMarkers(locObj) {
                if (auto_remove) {
                    $.each(locations, function (key) {
                        if (!locObj[key]) {
                            if (locations[key].marker) {
                                locations[key].marker.setMap(null);
                            }
                            delete locations[key];
                        }
                    });
                }

                $.each(locObj, function (key, loc) {
                    if (!locations[key] && loc.lat !== undefined && loc.lng !== undefined) {

                        loc.marker = new google.maps.Marker({
                            icon: loc.icon,
                            position: new google.maps.LatLng(loc.lat, loc.lng),
                            map: map
                        });

                        google.maps.event.addListener(loc.marker, 'click', (function (key) {
                            return function () {
                                if (locations[key]) {
                                    infowindow.setContent(locations[key].info);
                                    infowindow.open(map, locations[key].marker);
                                }
                            }
                        })(key));

                        locations[key] = loc;
                    }
                    else if (locations[key] && loc.remove) {
                        if (locations[key].marker) {
                            locations[key].marker.setMap(null);
                        }
                        delete locations[key];
                    }
                    else if (locations[key]) {
                        $.extend(locations[key], loc);
                        if (loc.lat !== undefined && loc.lng !== undefined) {
                            locations[key].marker.setPosition(
                                    new google.maps.LatLng(loc.lat, loc.lng)
                                    );
                            locations[key].marker.setIcon(loc.icon);
                        }
                    }
                });
            }

            var ajaxObj = {
                options: {
                    url: "./map",
                    dataType: "json"
                },
                delay: 5000,
                errorCount: 0,
                errorThreshold: 10000,
                ticker: null,
                get: function () {
                    if (ajaxObj.errorCount < ajaxObj.errorThreshold) {
                        ajaxObj.ticker = setTimeout(getMarkerData, ajaxObj.delay);
                    }
                },
                fail: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                    ajaxObj.errorCount++;
                }
            };

            function getMarkerData() {
                $.ajax(ajaxObj.options)
                        .done(setMarkers)
                        .fail(ajaxObj.fail)
                        .always(ajaxObj.get);
            }

            setMarkers(locs);
            //ajaxObj.get();

        </script>
    </body>
</html>
<?PHP

// Functions
function getDef($unit, $county, $db) {
    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_unit_info` WHERE county='$county'";
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        if ((strtolower($unit)) == (strtolower($row['UNIT_ID']))) {
            Return $row['DESCRIPTION'];
        }
    }
    return NULL;
}

function getDistance($unit, $county, $station, $latTo, $lonTo, $db) {
    $latFrom;
    $lonFrom;

    if ((strpos($unit, 'LF') !== false) OR ( strpos($unit, 'LIFE') !== false)) {
        $sql = "SELECT LAT,LON FROM `oregon911_cad`.`pdx911_lifeflight` WHERE ABBV='$station'";
    } else {
        if ((is_numeric($station)) AND ( !empty($station))) {
            $sql = "SELECT LAT,LON,STATION,ABBV FROM `oregon911_cad`.` pdx911_stations` WHERE STATION='$station' and COUNTY='$county'";
        } elseif (!empty($station)) {
            $sql = "SELECT LAT,LON,STATION,ABBV FROM `oregon911_cad`.`pdx911_stations` WHERE ABBV='$station' and COUNTY='$county'";
        } else {
            return 'unk';
        }
    }

    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        $latFrom = $row['LAT'];
        $lonFrom = $row['LON'];
    }

    if ((empty($latFrom)) OR ( empty($lonFrom))) {
        return 'unk';
    }

    $pi80 = M_PI / 180;
    $latFrom *= $pi80;
    $lonFrom *= $pi80;
    $latTo *= $pi80;
    $lonTo *= $pi80;

    $r = 6372.797; // mean radius of Earth in km
    $dlat = $latTo - $latFrom;
    $dlon = $lonTo - $lonFrom;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($latFrom) * cos($latTo) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;

    //Return KM to miles.
    return round($km * 0.621371, 2);
}

function getUse($unit, $county, $db) {
    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_unit_info` WHERE county='$county'";
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        if ((strtolower($unit)) == (strtolower($row['UNIT_ID']))) {
            Return $row['NOTES'];
        }
    }
    return NULL;
}

function GetURL() {
    $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $params = $_SERVER['QUERY_STRING'];

    $currentUrl = '//' . $host . '/call' . '?' . $params;

    return $currentUrl;
}
?>
