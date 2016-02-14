<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("database.php");
require_once("google.php");

if (isset($_GET['term'])) {

    // prevent direct access
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if (!$isAjax) {
        $user_error = 'Access denied - not an AJAX request...';
        trigger_error($user_error, E_USER_ERROR);
    }

    // get what user typed in autocomplete input
    $term = $db->sql_escape(trim($_GET['term']));

    // Create the SQL statement
    $sql = 'SELECT DISTINCT address from `oregon911_cad`.`pdx911_archive` WHERE address LIKE \'%' . $term . '%\'  AND county !=\'M\' order by TIMESTAMP DESC LIMIT 10';

    // Run the query 
    $result = $db->sql_query($sql);

    $a_json = array();

    $i = 0;
    while ($row = $result->fetch_assoc()) {
        $a_json_row["id"] = $i;
        $a_json_row["value"] = $row['address'];
        $a_json_row["label"] = $row['address'];
        array_push($a_json, $a_json_row);
        $i++;
    }

    // highlight search results
    $a_json = apply_highlight($a_json, $parts);

    $json = json_encode($a_json);
    print $json;
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Search</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <script type='text/javascript' src="https://maps.google.com/maps/api/js?sensor=false&extn=.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu" class="main-menu"></a>
                Oregon 911 - Search
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
                if (!$mobile) {
                    ?>
                    <div style="padding-top: 20px;">
                        <div <?php
                        echo ('id="wccca-page-wrapper"');
                        ?>>
                                <?PHP
                            } else {
                                echo('<div style="background-color: #FFF;">');
                            }
                            ?>
                        <!-- ================== This is where the main stuff happens ================= -->
                        <!-- Specifying an 'open' attribute will make all the content visible when the page loads -->

                        <?PHP
                        if ($_GET['type'] == "map") {
                            ?>
                            <h1> Search - Map </h1>
                            <p> Click anywhere on the map to start your query. </p>
                            <?PHP
                        } else {
                            ?>
                            <h1> Search - Advanced </h1>      
                        <?PHP }
                        ?>
                        <center>
                            <?PHP
                            if (!$mobile) {
                                echo($ad_336_280);
                            } else {
                                echo($ad_320_100);
                            }
                            ?>
                        </center>
                        <!-- End Oregon 911 Units -->
                        <?PHP
                        if ($_GET['type'] == "map") {
                            ?>

                            <details class="wccca-details" open='true'>
                                <summary>Search</summary>
                                <?PHP
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                    $lat = htmlspecialchars(strip_tags($db->sql_escape($_GET['lat'])));
                                    $lon = htmlspecialchars(strip_tags($db->sql_escape($_GET['lon'])));
                                    $radius = htmlspecialchars(strip_tags($db->sql_escape($_GET['radius'])));
                                }
                                $callSum = htmlspecialchars(strip_tags($db->sql_escape($_GET['calltype'])));
                                $days = htmlspecialchars(strip_tags($db->sql_escape($_GET['days'])));
                                $limit = htmlspecialchars(strip_tags($db->sql_escape($_GET['limit'])));
                                ?>
                                <div id="map_2385853" style="width:100%; height:500px;"></div>
                                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <input type="hidden" name="type" id="type" value="map" />
                                    <input type="hidden" name="lat" id="lat" value="" />
                                    <input type="hidden" name="lon" id="lon" value=""  />
                                    <input type="hidden" name="radius" id="radius" value="" />
                                    <input type="hidden" name="zoom" id="zoom" value="" />
                                    <label for="calltype">Call Type:</label>
                                    <select name="calltype">
                                        <option value="">ANY</option>
                                        <?PHP
                                        // Create the SQL statement
                                        $sql = "SELECT callSum FROM `oregon911_cad`.`pdx911_archive` WHERE callSum NOT LIKE '%*%' AND county !='M' group by callsum order by count(callsum) DESC;";

                                        // Run the query 
                                        $result = $db->sql_query($sql);

                                        while ($row = $result->fetch_assoc()) {
                                            if ($_GET['calltype'] == $row['callSum']) {
                                                ?> <option selected="true" value="<?PHP echo($row['callSum']); ?>"><?PHP echo($row['callSum']); ?></option> <?PHP
                                            } else {
                                                ?> <option value="<?PHP echo($row['callSum']); ?>"><?PHP echo($row['callSum']); ?></option> <?PHP
                                            }
                                        }
                                        ?>
                                    </select></label>
                                    <?PHP
                                    if (!empty($days)) {
                                        ?>  <label for="days">Days Ago: <input  name='days' min="1" max="360" type="number" value='<?PHP echo($days); ?>'/></label> <?PHP
                                    } else {
                                        ?>  <label for="days">Days Ago: <input  name='days' min="1" max="360" type="number" value='30'/></label> <?PHP
                                        }
                                        ?>
                                        <?PHP
                                        if (!empty($limit) OR ( $limit = 0)) {
                                            ?>  <label for="limit">Limit (0 = off): <input  name='limit' min="0" max="500" type="number" value='<?PHP echo($limit); ?>'/></label> <?PHP
                                        } else {
                                            ?>  <label for="limit">Limit (0 = off): <input  name='limit' min="0" max="500" type="number" value='100'/></label> <?PHP
                                        }
                                        ?>
                                    <input type="submit" value="Search!">
                                </form>
                                <?PHP
                                if ($radius > 4000) {
                                    ?><h2><b> Search area too large! You would not want to experience what would happen with >4000 search area.  </b></h2><?PHP
                                }
                                ?>
                                <b> Use Limit = 0 with caution. </b>
                            </details>
                            <?PHP
                        } else {
                            ?>
                            <details class="wccca-details" open='true'>
                                <summary>Search</summary>
                                <form method="GET">
                                    <input type="hidden" name="type" id="type" value="advanced" />
                                    <?PHP
                                    if (!$mobile) {
                                        ?>
                                        <label for="callnumber">Call #:</label>
                                        <input type="text" name="callnumber" id="callnumber" value="<?PHP echo(htmlspecialchars(strip_tags($_GET['callnumber']))); ?>">
                                        <?PHP
                                    }
                                    ?>
                                    <label for="county">County:</label>
                                    <select name="county">
                                        <option value="">ANY</option>
                                        <?PHP
                                        // Create the SQL statement
                                        $sql = 'SELECT DISTINCT county FROM `oregon911_cad`.`pdx911_archive` WHERE  county !=\'M\' ';

                                        // Run the query 
                                        $result = $db->sql_query($sql);

                                        while ($row = $result->fetch_assoc()) {
                                            if ($_GET['county'] == $row['county']) {
                                                ?> <option selected="true" value="<?PHP echo($row['county']); ?>"><?PHP echo($row['county']); ?></option> <?PHP
                                            } else {
                                                ?> <option value="<?PHP echo($row['county']); ?>"><?PHP echo($row['county']); ?></option> <?PHP
                                            }
                                        }
                                        ?>
                                    </select>  
                                    <?PHP
                                    if (!$mobile) {
                                        ?>
                                        <label for="county">Station:</label>
                                        <select name="station">
                                            <option value="">ANY</option>
                                            <?PHP
                                            // Create the SQL statement
                                            $sql = 'SELECT DISTINCT station FROM `oregon911_cad`.`pdx911_archive` WHERE county !=\'M\' ';

                                            // Run the query 
                                            $result = $db->sql_query($sql);

                                            while ($row = $result->fetch_assoc()) {
                                                if ($_GET['station'] == $row['station']) {
                                                    ?> <option selected="true" value="<?PHP echo($row['station']); ?>"><?PHP echo($row['station']); ?></option> <?PHP
                                                } else {
                                                    ?> <option value="<?PHP echo($row['station']); ?>"><?PHP echo($row['station']); ?></option> <?PHP
                                                }
                                            }
                                            ?>
                                        </select>
                                        <?PHP
                                    }
                                    ?>

                                    <label for="address">Address:</label>
                                    <input type="text" name="address" id="address" value="<?PHP echo(htmlspecialchars(strip_tags($_GET['address']))); ?>">
                                    <label for="calltype">Call Type:</label>
                                    <select name="calltype">
                                        <option value="">ANY</option>
                                        <?PHP
                                        // Create the SQL statement
                                        $sql = "SELECT callSum FROM `oregon911_cad`.`pdx911_archive` WHERE callSum NOT LIKE '%*%' AND county !='M'  group by callsum order by count(callsum) DESC;";

                                        // Run the query 
                                        $result = $db->sql_query($sql);

                                        while ($row = $result->fetch_assoc()) {
                                            if ($_GET['calltype'] == $row['callSum']) {
                                                ?> <option selected="true" value="<?PHP echo($row['callSum']); ?>"><?PHP echo($row['callSum']); ?></option> <?PHP
                                            } else {
                                                ?> <option value="<?PHP echo($row['callSum']); ?>"><?PHP echo($row['callSum']); ?></option> <?PHP
                                            }
                                        }
                                        ?>
                                    </select>
                                    <input type="submit" value="Submit">
                                </form>
                            </details>
                            <?PHP
                        }
                        if ($_GET['type'] == "map") {
                            ?>
                            <details class="wccca-details" open='true'>
                                <summary>Results</summary>
                                <?PHP
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                    $lat = htmlspecialchars(strip_tags($db->sql_escape($_GET['lat'])));
                                    $lon = htmlspecialchars(strip_tags($db->sql_escape($_GET['lon'])));
                                    $radius = htmlspecialchars(strip_tags($db->sql_escape($_GET['radius'])));
                                    $zoom = htmlspecialchars(strip_tags($db->sql_escape($_GET['zoom'])));
                                    if ((!empty($lat)) && (!empty($lon)) && (!empty($radius) && ($radius <= 4000))) {
                                        // Create the SQL statement
                                        if (empty($days)) {
                                            $days = 30;
                                        }
                                        if (!empty($limit) or ( !$limit == 0)) {
                                            $sql = "Select * FROM `oregon911_cad`.`pdx911_archive` WHERE callSum LIKE '%$callSum%' AND county !='M'  AND (`timestamp` > DATE_SUB(now(), INTERVAL $days DAY)) AND (geodistance($lat,$lon,lat,lon) < $radius) order by timestamp DESC LIMIT $limit";
                                        } else {
                                            $sql = "Select * FROM `oregon911_cad`.`pdx911_archive` WHERE callSum LIKE '%$callSum%' AND county !='M'  AND (`timestamp` > DATE_SUB(now(), INTERVAL $days DAY)) AND (geodistance($lat,$lon,lat,lon) < $radius) order by timestamp DESC";
                                        }
                                        // Run the query 
                                        $result = $db->sql_query($sql);
                                        $global_result = $result;
                                        echo '<table style="width:100%;">';
                                        echo '<tr><th>Date</th><th>Call #</th><th>Call Type</th><th>Station</th><th>Address</th><th>Units</th><th>URL</th></tr>';

                                        while ($row = $result->fetch_assoc()) {
                                            if (!$mobile) {
                                                echo '<tr><th>' . $row['timestamp'] . '</th><th><a href="./call?call=' . $row['GUID'] . '&amp;county=' . $row['county'] . '&type=' . $row['type'] . '">' . $row['GUID'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&amp;calltype=' . urlencode($row['callSum']) . '">' . $row['callSum'] . '</a></th><th><a href="./station?station=' . $row['station'] . '&amp;county=' . $row['county'] . '">' . $row['station'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&amp;address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th>' . $row['units'] . '</th><th><a href="./call?call=' . $row['GUID'] . '&amp;county=' . $row['county'] . '&type=' . $row['type'] . '"  target="_blank">Open Call</a></th></tr>';
                                            } else {
                                                echo '<tr><th><a href="./call?call=' . $row['GUID'] . '&amp;county=' . $row['county'] . '&type=' . $row['type'] . '"  target="_blank">' . $row['callSum'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&amp;address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th></tr>';
                                            }
                                        }
                                        echo '</table>';
                                    }
                                }
                                ?>
                            </details>
                            <b> Results are limited to the most recent <?PHP echo($limit); ?>.</b>
                            <?PHP
                        } else {
                            ?>
                            <details class="wccca-details" open='true'>
                                <summary>Results</summary>
                                <?PHP
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                    $callnumber = htmlspecialchars(strip_tags($db->sql_escape($_GET['callnumber'])));
                                    $county = htmlspecialchars(strip_tags($db->sql_escape($_GET['county'])));
                                    $calltype = htmlspecialchars(strip_tags($db->sql_escape($_GET['calltype'])));
                                    $address = htmlspecialchars(strip_tags($db->sql_escape($_GET['address'])));
                                    $station = htmlspecialchars(strip_tags($db->sql_escape($_GET['station'])));
                                    // Create the SQL statement
                                    $sql = "Select timestamp, GUID, county, type, callSum, station, address, units FROM `oregon911_cad`.`pdx911_archive` WHERE GUID LIKE '%" . $callnumber . "%' AND county !='M' AND county LIKE '%" . $county . "%' AND address LIKE '%" . $address . "%' and callSum LIKE '%" . $calltype . "%' AND station LIKE '%" . $station . "%' order by timestamp DESC LIMIT 20";

                                    // Run the query 
                                    $result = $db->sql_query($sql);

                                    echo '<table style="width:100%;">';
                                    if (!$mobile) {
                                        echo '<tr><th>Date</th><th>Call #</th><th>Call Type</th><th>Station</th><th>Address</th><th>Units</th><th>URL</th></tr>';
                                    } else {
                                        echo '<tr><th>Call Type</th><th>Address</th></tr>';
                                    }

                                    while ($row = $result->fetch_assoc()) {
                                        if (!$mobile) {
                                            echo '<tr><th>' . $row['timestamp'] . '</th><th><a href="./call?call=' . $row['GUID'] . '&amp;county=' . $row['county'] . '&type=' . $row['type'] . '">' . $row['GUID'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&amp;calltype=' . urlencode($row['callSum']) . '">' . $row['callSum'] . '</a></th><th><a href="./station?station=' . $row['station'] . '&amp;county=' . $row['county'] . '">' . $row['station'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&amp;address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th>' . $row['units'] . '</th><th><a href="./call?call=' . $row['GUID'] . '&amp;county=' . $row['county'] . '&type=' . $row['type'] . '"  target="_blank">Open Call</a></th></tr>';
                                        } else {
                                            echo '<tr><th><a href="./call?call=' . $row['GUID'] . '&amp;county=' . $row['county'] . '&type=' . $row['type'] . '"  target="_blank">' . $row['callSum'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&amp;address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th></tr>';
                                        }
                                    }
                                    echo '</table>';
                                }
                                ?>
                            </details>
                            <b> Results are limited to the most recent 20 </b>
                        <?PHP }
                        ?>
                        <!-- ====================================================================================== -->
                        <?php
                        $time = microtime();
                        $time = explode(' ', $time);
                        $time = $time[1] + $time[0];
                        $finish = $time;
                        $total_time = round(($finish - $start), 4);
                        echo '<p>Page generated in ' . $total_time . ' seconds.</p>';
                        ?>
                        <?PHP
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
        <?PHP
        if ($_GET['type'] == 'map') {
            ?>

            <script type='text/javascript'>
            var locations = {};
            var locs = {
    <?PHP
    $CallIDM = 1;

// Stations 
    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_stations` WHERE county !='M'  ORDER BY ID";
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

    if ((!empty($lat)) && (!empty($lon)) && (!empty($radius))) {
        if ($global_result) {
            mysqli_data_seek($global_result, 0);
            while ($rows = $global_result->fetch_assoc()) {
                echo(',' . $CallIDM . ': { info:\'<div class="incident">' .
                '<h1> Call: ' . $rows['callSum'] . ' </h1>' .
                '<h3> Address: ' . $rows['address'] . ' </h3>' .
                '<p>Station: ' . $rows['station'] . '</p>' .
                '<p> Time: ' . $rows['timestamp'] . ' </p>');
                echo('<a href="./call?call=' . $rows['GUID'] . '&county=' . $rows['county'] . '&type=' . $row['type'] . '" target="_blank">More Info</a></div>\'' .
                ', lat:' . $rows['lat'] . ', lng:' . $rows['lon'] . ', icon:\'' . $rows['icon'] . '\' }');
                $CallIDM++;
            }
        }
    }
    ?>
            };
            var map = new google.maps.Map(document.getElementById('map_2385853'), {
    <?PHP
    $lat = htmlspecialchars(strip_tags($db->sql_escape($_GET['lat'])));
    $lon = htmlspecialchars(strip_tags($db->sql_escape($_GET['lon'])));
    $radius = htmlspecialchars(strip_tags($db->sql_escape($_GET['radius'])));
    $zoom = htmlspecialchars(strip_tags($db->sql_escape($_GET['zoom'])));
    if ((!empty($zoom))) {
        echo("zoom: $zoom,");
    } else {
        echo("zoom: 10,");
    }
    ?>
            streetViewControl: true,
    <?PHP
    if ((!empty($lat)) && (!empty($lon))) {
        echo("center: new google.maps.LatLng($lat, $lon),");
    } else {
        echo("center: new google.maps.LatLng(45.432913, -122.636261),");
    }
    ?>
            mapTypeId: google.maps.MapTypeId.ROADMAP
            });
                    var doom = new google.maps.Circle({
                        center: new google.maps.LatLng(0, 0),
                        radius: 100,
                        strokeColor: "#C82620",
                        strokeOpacity: 0.8,
                        editable: true,
                        strokeWeight: 2,
                        fillColor: "#C82620",
                        fillOpacity: 0.4
                    });
    <?PHP
    if ((!empty($lat)) && (!empty($lon))) {
        echo("var circleofdoom = new google.maps.LatLng($lat, $lon);");
        ?>
                doom.center = circleofdoom;
                doom.radius = <?PHP echo($radius); ?>;
                doom.setMap(map);
                writeOut();
        <?PHP
    }
    ?>
            google.maps.event.addListener(doom, 'center_changed', function (e) {
                writeOut();
            });

            google.maps.event.addListener(doom, 'radius_changed', function (e) {
                writeOut();
            });

            google.maps.event.addListener(map, 'zoom_changed', function (e) {
                writeOut();
            });

            //add click event
            google.maps.event.addListener(map, 'click', function (e) {
                removeCircle();
                //draw marker with circle
                doom.center = e.latLng;
                doom.radius = 200;
                doom.setMap(map)
                writeOut();
            });

            function writeOut() {
                document.getElementById('lat').value = doom.getCenter().lat();
                document.getElementById('lon').value = doom.getCenter().lng();
                document.getElementById('radius').value = doom.getRadius();
                document.getElementById('zoom').value = map.getZoom();
            }

            //remove all markers from map
            function removeCircle() {
                doom.setMap(null);
            }

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
        <?PHP }
        ?>
    </body>
</html>