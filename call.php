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
        $initial_type = 'F';
    }

    $found = false;
    $active = false;


// Create the SQL statement
    $sql = "SELECT callSum, U.address, type, U.agency as raw_agency, U.station, units, (Select DISTRICT From `oregon911_cad`.`pdx911_stations` AS S WHERE U.STATION = S.ABBV and U.county = S.county) as agency, U.lat, U.lon, icon, timestamp, 1 AS CACT FROM `oregon911_cad`.`pdx911_calls` AS U WHERE U.GUID = '" . $GUID . "' AND U.county = '" . $county . "' AND U.county != 'M' AND U.type LIKE '" . $initial_type . "'  UNION ALL SELECT callSum, U.address, type, U.agency as raw_agency, U.station, units, (Select DISTRICT From `oregon911_cad`.`pdx911_stations` AS S WHERE U.STATION = S.ABBV and U.county = S.county AND S.county != 'M') as agency, U.lat, U.lon, icon, timestamp, 0 AS CACT FROM `oregon911_cad`.`pdx911_archive` AS U WHERE U.county != 'M' AND U.GUID = '" . $GUID . "' AND U.county = '" . $county . "'  AND U.type LIKE '" . $initial_type . "' ";

// Run the query 
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);

    $callSum = $row['callSum'];
    if (empty($callSum)) {
        $initial_type = '%';
        // Create the SQL statement
        $sql = "SELECT callSum, U.address, type, U.agency as raw_agency, U.station, units, (Select DISTRICT From `oregon911_cad`.`pdx911_stations` AS S WHERE U.STATION = S.ABBV and U.county = S.county) as agency, U.lat, U.lon, icon, timestamp, 1 AS CACT FROM `oregon911_cad`.`pdx911_calls` AS U WHERE U.GUID = '" . $GUID . "' AND U.county = '" . $county . "' AND U.county != 'M' AND U.type LIKE '" . $initial_type . "'  UNION ALL SELECT callSum, U.address, type, U.agency as raw_agency, U.station, units, (Select DISTRICT From `oregon911_cad`.`pdx911_stations` AS S WHERE U.STATION = S.ABBV and U.county = S.county AND S.county != 'M') as agency, U.lat, U.lon, icon, timestamp, 0 AS CACT FROM `oregon911_cad`.`pdx911_archive` AS U WHERE U.county != 'M' AND U.GUID = '" . $GUID . "' AND U.county = '" . $county . "'  AND U.type LIKE '" . $initial_type . "' ";

// Run the query 
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
    }

    $callSum = $row['callSum'];
    $address = $row['address'];
    $type = $row['type'];
    $station = $row['station'];
    $units = $row['units'];
    $agency = $row['agency'];
    $agencyRaw = $row['raw_agency'];
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
                    echo ('<a href="/search?county=' . $county . '&calltype=' . urlencode($callSum) . '">' . $callSum . '</a>');
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
                    echo ('<a href="/search?county=' . $county . '&address=' . urlencode($address) . '">' . $address . '</a>');
                    ?></td>
            </tr>
            <tr>
            <tr>
                <th scope="row">Agency</th>
                <td><?php
                    if ($type == "P") {
                        echo ("<a href=\"/agency?agency=" . rawurlencode($agencyRaw) . "&county=" . $county . "\">" . $agencyRaw . "</a>");
                    } else {
                        if (!empty($agency)) {
                            echo ("<a href=\"/agency?agency=" . rawurlencode($agency) . "&county=" . $county . "\">" . $agency . "</a>");
                        } else {
                            echo ("Unknown");
                        }
                    }
                    ?>
                </td>
            </tr>
            <th scope="row">Station</th>
            <td><?php
                echo ('<a href="/station?station=' . $station . '&county=' . $county . '">' . $station . '</a>');
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
        if ((strtolower($address) == strtolower("WCCCA 911")) OR ( strtolower($address) == strtolower("2200 Kaen Rd")) OR ( strtolower($address) == strtolower("17911 NW EVERGREEN PK")) OR ( strpos(strtolower($callSum), 'drill') !== false) OR ( strpos(strtolower($callSum), 'test') !== false)) {
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
            echo '<tr><th><a href="/unitinfo?unit=' . $row['unit'] . '&county=' . $row['county'] . '">' . $row['unit'] . '</a></th><th><a href="/agency?agency=' . rawurlencode($row['agency']) . '&county=' . $county . '">' . $row['agency'] . '</a></th><th><a href="/station?station=' . $row['station'] . '&county=' . $row['county'] . '">' . $row['station'] . '</a></th>';
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
            echo '<tr><th><a href="/unitinfo?unit=' . $row['unit'] . '&county=' . $row['county'] . '">' . $row['unit'] . '</a></th>';
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
    $H = 280;
    if (isset($_GET['H'])) {
        $H = $_GET['H'];
    }
    $W = 550;
    if (isset($_GET['W'])) {
        $W = $_GET['W'];
    }

    $TwitterCardIMG = "http://www.cad.oregon911.net/static/staticmap.php?center=$lat,$long&zoom=$zoom&size=$W\x$H&markers=$lat,$long,$icon";
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

        <link type="text/css" rel="stylesheet" href="/css/main.css" />
        <link type="text/css" rel="stylesheet" href="/src/css/jquery.mmenu.all.css" />
        <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-label/v0.2.1/leaflet.label.css' rel='stylesheet' />
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="/src/js/jquery.mmenu.min.all.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu" class="main-menu"></a>
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
                                <div id="map" style="width:100%; height:300px;"></div>
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
                                        echo '<tr><th>' . $row['timestamp'] . '</th><th>' . $row['GUID'] . '</th><th>' . $row['callSum'] . '</th><th><a href="/call?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">Call Log</a></th></tr>';
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
                                        echo '<tr><th>' . $row['timestamp'] . '</th><th>' . $row['callSum'] . '</th><th><a href="/call?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">Call Log</a></th></tr>';
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
                            
                            <details <?php echo ($theme); ?> open=true>
                                <summary>Other Incidents</summary>
                                <?PHP echo($matched_content_ad); ?>
                            </details>
                            <!-- ====================================================================================== -->
                            <?php
                            $time = microtime();
                            $time = explode(' ', $time);
                            $time = $time[1] + $time[0];
                            $finish = $time;
                            $total_time = round(($finish - $start), 4);
                            echo '<p>Page generated in ' . $total_time . ' seconds.</p>';
                            echo '<p>Copyright &copy; ' . date("Y") . ' Oregon 911. All Rights Reserved.</p>';
                            ?>
                            <?PHP
                        }
                        if (!$mobile) {
                            ?>
                        </div>

                    <?PHP } ?>
                </div>
            </div>

            <?PHP include ("inc/nav.php"); ?>

        </div>
        <script type = "text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
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
                $('#callinfo').load('/call?AJAX_REFRESH=callinfo&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#calllog-all').load('/call?AJAX_REFRESH=calllog-all&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#calllog').load('/call?AJAX_REFRESH=calllog&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#units').load('/call?AJAX_REFRESH=units&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#units-mobile').load('?AJAX_REFRESH=units-mobile&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#flags').load('/call?AJAX_REFRESH=flags&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
                $('#changelog').load('/call?AJAX_REFRESH=changelog&call=<?PHP echo($GUID); ?>&county=<?PHP echo($county); ?>&type=<?PHP echo($_GET['type']); ?>').fadeIn("slow");
            }
<?PHP if ($active) { ?>
                var refreshId = setInterval(auto_refresh, 20000);
<?PHP } ?>
        </script>
        <script type="text/javascript" src="/js/leaflet.js"></script>
        <script type="text/javascript" src="/js/Leaflet.label.js"></script>

        <script async type='text/javascript'>
            var myCalls = [];
            var fire = new L.LayerGroup();
            var EMS = new L.LayerGroup();
            var police = new L.LayerGroup();
            var accidents = new L.LayerGroup();
            var firestations = new L.LayerGroup();
            var hydrants = new L.LayerGroup();
            var hospitals = new L.LayerGroup();
            var TrfAccid = [" BLOCKING", "CRASH, UNK INJ", "TAI-MAJOR INCIDE", "TRF ACC, UNK INJ", "BLOCKING", "NOT BLOCKING", "TRF ACC, INJURY", "MVA-INJURY ACCID", "TRF ACC, NON-INJ", "TAI-TRAPPED VICT", "TAI-HIGH MECHANI", "TAI-PT NOT ALERT", "MVA-UNK INJURY"];
            var OR911TileErrorCount = 0;
            var failOver = false;

// create a map in the "map" div, set the view to a given place and zoom
// initialize the map on the "map" div with a given center and zoom
            var map = L.map('map', {
                center: [<?PHP echo ($lat); ?>, <?PHP echo ($long); ?>],
                zoom: 16,
                layers: [fire, EMS, police, accidents, firestations]
            });

            L.tileLayer("http://openfiremap.org/hytiles/{z}/{x}/{y}.png", {
                attribution: '&copy; <a href="http://openfiremap.org">OpenFireMap</a> contributors',
                minZoom: 0,
                maxZoom: 19
            }).addTo(hydrants);

            L.tileLayer('http://openfiremap.org/eytiles/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://openfiremap.org">OpenFireMap</a> contributors',
                minZoom: 0,
                maxZoom: 19
            }).addTo(hospitals);

            // Oregon 911 tile layer, HTTPS not supported.
            var OR911_OSM = L.tileLayer('http://www.server2.oregon911.net/osm/{z}/{x}/{y}.png', {
                attribution: '&copy; Brandan Lasley 2015 Map &copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
                minZoom: 0,
                maxZoom: 18
            }).addTo(map);

            OR911_OSM.on('tileerror', function (error, tile) {
                if (OR911TileErrorCount > 3) {
                    // OR911s tile server is offline or slow, switch to OSM.
                    OR911TileServerOffline();
                } else {
                    // Don't continue counting.
                    OR911TileErrorCount++;
                }
            });


            // https: also suppported.
            var HERE_hybridDay = L.tileLayer('http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/hybrid.day/{z}/{x}/{y}/256/png8?app_id=Yf8CiPvTiOOwEUHMvSz4&app_code=EAM6lcJb4d0C3sA93UbmEQ', {
                attribution: '&copy; Brandan Lasley 2015 Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>',
                subdomains: '1234',
                mapID: 'newest',
                app_id: 'Y8m9dK2brESDPGJPdrvs',
                app_code: 'dq2MYIvjAotR8tHvY8Q_Dg',
                base: 'aerial',
                minZoom: 0,
                maxZoom: 19
            });

            // https: also suppported.
            var HERE_normalNight = L.tileLayer('http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/normal.night/{z}/{x}/{y}/256/png8?app_id=Yf8CiPvTiOOwEUHMvSz4&app_code=EAM6lcJb4d0C3sA93UbmEQ', {
                attribution: '&copy; Brandan Lasley 2015 Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>',
                subdomains: '1234',
                mapID: 'newest',
                app_id: 'Y8m9dK2brESDPGJPdrvs',
                app_code: 'dq2MYIvjAotR8tHvY8Q_Dg',
                base: 'base',
                minZoom: 0,
                maxZoom: 19
            });

            // https: also suppported.
            var HERE_terrainDay = L.tileLayer('http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/terrain.day/{z}/{x}/{y}/256/png8?app_id=Yf8CiPvTiOOwEUHMvSz4&app_code=EAM6lcJb4d0C3sA93UbmEQ', {
                attribution: '&copy; Brandan Lasley 2015 Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>',
                subdomains: '1234',
                mapID: 'newest',
                app_id: 'Y8m9dK2brESDPGJPdrvs',
                app_code: 'dq2MYIvjAotR8tHvY8Q_Dg',
                base: 'aerial',
                minZoom: 0,
                maxZoom: 19
            });

            var baseLayers = {
                "Standard": OR911_OSM,
                "Night Map": HERE_normalNight,
                "Satellite": HERE_hybridDay,
                "Terrain": HERE_terrainDay
            };

            var overlays = {
                "Fire": fire,
                "EMS": EMS,
                "Police": police,
                "Accidents": accidents,
                "Fire Stations": firestations//,
                        //"Hydrants": hydrants,
                        //"Hospitals": hospitals
            };

            L.control.layers(baseLayers, overlays).addTo(map);

// deletes all markers on map;
            function clearMap() {
                for (var i = 0; i < myCalls.length; i++) {
                    map.removeLayer(myCalls[i].call);
                }
                return true;
            }

// display all markers id on map;
            function displayAll() {
                for (var i = 0; i < myCalls.length; i++) {
                    alert(myCalls[i].id);
                }
                return true;
            }

// Search though all markers.
            function searchMarkers(idx) {
                for (var i = 0; i < myCalls.length; i++) {
                    if (myCalls[i].id === idx) {
                        return true;
                    }
                }
                return false;
            }

// Positions the label correctly.. enough
            function getLabelOffset(labelname) {
                var offset = 30 + (35 % 35 + (3 * labelname.length));
                return -offset;
            }


// Changes call to another layer group.
            function changeLayer(myCall, type, isMVA) {
                if (isMVA) {
                    var layer = "accidents";
                    myCall.call.addTo(accidents);
                } else {
                    if (type === "F") {
                        var layer = "F";
                        myCall.call.addTo(fire);
                    } else if (type === "M") {
                        var layer = "M";
                        myCall.call.addTo(EMS);
                    } else if (type === "P") {
                        var layer = "P";
                        myCall.call.addTo(police);
                    } else {
                        var layer = "other";
                        myCall.call.addTo(map);
                    }
                }
                return layer;
            }

// Is this an accident or a normal call?
            function isAccident(labelname) {
                for (var i = TrfAccid.length; i--; ) {
                    if (TrfAccid[i] === labelname) {
                        return true;
                    }
                }
                return false;
            }

// Remove call from current layer.
            function cleanLayer(myCall) {
                if (myCall.layer === "F") {
                    fire.removeLayer(myCall.call);
                } else if (myCall.layer === "M") {
                    EMS.removeLayer(myCall.call);
                } else if (myCall.layer === "P") {
                    police.removeLayer(myCall.call);
                } else if (myCall.layer === "accidents") {
                    accidents.removeLayer(myCall.call);
                } else {
                    map.removeLayer(myCall.call);
                }
            }

// Add Marker to map and return the created marker.
            function addMarker(idx, html, lat, lng, type, iconW, iconH, iconURL, labelname, label) {
                if (!searchMarkers(idx)) {
                    //console.log("Adding call: " + idx);
                    var markerLocation = new L.LatLng(lat, lng);
                    var offset = getLabelOffset(labelname);
                    var Marker = L.Icon.extend({
                        options: {
                            iconUrl: iconURL,
                            iconSize: [iconW, iconH],
                            labelAnchor: new L.Point(offset, 35),
                            zoomAnimation: false,
                            clickable: true,
                            shadowSize: [iconW, iconH]
                        }
                    });
                    var marker = new Marker();

                    if (isAccident(labelname)) {
                        var layer = "accidents";
                        var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(accidents).showLabel();
                    } else {
                        if (type === "F") {
                            var layer = "F";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(fire).showLabel();
                        } else if (type === "M") {
                            var layer = "M";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(EMS).showLabel();
                        } else if (type === "P") {
                            var layer = "P";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(police).showLabel();
                        } else if (type === "FS") {
                            var layer = "FS";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindPopup(html).addTo(firestations).showLabel();
                        } else {
                            var layer = "other";
                            if (label) {
                                var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(map).showLabel();
                            } else {
                                var callMarker = L.marker(markerLocation, {icon: marker}).bindPopup(html).addTo(map);
                            }
                        }
                    }

                    var callObj = {};
                    callObj['id'] = idx;
                    callObj['call'] = callMarker;
                    callObj['layer'] = layer;

                    myCalls.push(callObj);
                    return true;
                }
                return updateMarker(idx, html, lat, lng, type, iconW, iconH, iconURL, labelname, label);
            }

// Updates Marker 
            function updateMarker(idx, html, lat, lng, type, iconW, iconH, iconURL, labelname, label) {
                if (!(idx.indexOf("stadic") > -1)) {
                    var markerLocation = new L.LatLng(lat, lng);
                    for (var i = 0; i < myCalls.length; i++) {
                        if (myCalls[i].id === idx) {
                            //console.log("Updating call: " + myCalls[i].id);
                            var markerLocation = new L.LatLng(lat, lng);

                            if ((type !== myCalls[i].layer) && (myCalls[i].layer !== 'accidents')) {
                                console.log("Cleaning " + myCalls[i].id + " " + myCalls[i].layer)
                                cleanLayer(myCalls[i]);
                                myCalls[i].layer = changeLayer(myCalls[i], type, isAccident(labelname));
                            }

                            var offset = getLabelOffset(labelname);
                            var Marker = L.icon({
                                iconUrl: iconURL,
                                iconSize: [iconW, iconH],
                                labelAnchor: new L.Point(offset, 35),
                                zoomAnimation: false,
                                clickable: true,
                                shadowSize: [iconW, iconH]
                            });
                            myCalls[i].call.unbindLabel();
                            myCalls[i].call.setLatLng(markerLocation);
                            myCalls[i].call.bindPopup(html);
                            myCalls[i].call.setIcon(Marker);
                            if (label) {
                                myCalls[i].call.bindLabel(labelname, {noHide: true}).showLabel();
                            }
                            return true;
                        }
                    }
                }
                return false;
            }

// remove marker from map and data structure. This doesn't work.
            function removeMarker(idx) {
                console.log("Removing Marker" + idx);
                for (var i = 0; i < myCalls.length; i++) {
                    if (myCalls[i].id === idx) {
                        myCalls[i].call.unbindLabel();
                        cleanLayer(myCalls[i]);
                        myCalls.splice(i, 1);
                        return true;
                    }
                }
                alert("Couldn't remove a marker, this should NOT happen... ever");
                return false;
            }

            var firstrun = true;

// JSON update
            var ajaxObj = {
                options: {
                    url: "./map.php?cad=true",
                    dataType: "json"
                },
                delay: 10000,
                errorCount: 0,
                errorThreshold: 15000,
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

            function setMarkers(locObj) {
                navigator.geolocation.getCurrentPosition(successHandler, errorHandler); // User Geolocation stuff
                var tmpMyCalls = [];
                $.each(locObj, function (key, loc) {
                    tmpMyCalls.push(key);
                    addMarker(key, loc.info, loc.lat, loc.lng, loc.type, loc.iconW, loc.iconH, loc.icon, loc.labelname, loc.label);
                });
                cleanMarkers(tmpMyCalls);
            }

// Remove markers no longer existing. 
            function cleanMarkers(tmpMyCalls) {
                for (var i = 0; i < myCalls.length; i++) {
                    if (tmpMyCalls.length > 0) {
                        var found = false;
                        for (var id = 0; id < tmpMyCalls.length; id++) {
                            if (myCalls[i].id === tmpMyCalls[id]) {
                                found = true;
                            }
                        }
                        if (found === false) {
                            if (!(myCalls[i].id.indexOf("donotremove") > -1)) {
                                removeMarker(myCalls[i].id);
                            }
                        }
                    } else {
                        //console.log("================= REMOVING ALL CALLS! =================" + myCalls[i].id);
                        clearMap();
                    }
                }
            }

            function OR911TileServerOffline() {
                if (!failOver) {
                    map.removeLayer(OR911_OSM);
                    // Fall back to OSM tile server
                    OR911_OSM = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
                        minZoom: 0,
                        maxZoom: 19
                    }).addTo(map);

                    console.log("Oregon 911 tile server not responding...");
                    failOver = true;
                }
            }
// Run
            if (firstrun) {
                navigator.geolocation.getCurrentPosition(successHandler, errorHandler); // User Geolocation stuff

<?PHP
echo ('addMarker("1", "' . htmlspecialchars($type . ' ' . $station . ' ' . $GUID . ' ' . str_replace("'", "\'", $callSum) . ' | ' . $address . '  | ' . $units) . '",' . $lat . ',' . $long . ', "X", 32, 37, "' . $icon . '", "' . htmlspecialchars($callSum) . '", true);');
?>

            }
// User Geolocation stuff
            function errorHandler(error) {
// sad face :(
            }
            function successHandler(location) {
                addMarker("donotremove1", "<div style='width: 200px;'><b>YOU</b> <p>Accuracy: &#177; " + location.coords.accuracy + " meters</p></div>", location.coords.latitude, location.coords.longitude, "other", 11, 11, "./images/MISC/pos.png", 0, false);
            }
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
