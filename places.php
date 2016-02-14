<?PHP
require_once ("phpbb.php");
require_once("google.php");

if (isset($_POST['maplat']) AND isset($_POST['maplng'])) {
    $sql = 'SELECT * FROM ' . USER_GROUP_TABLE . ' WHERE user_id = ' . (int) $user->data['user_id'];
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        $safename = $db->sql_escape(strip_tags($_POST["name"]));
        $safedistance = $db->sql_escape(strip_tags($_POST["distance"]));
        $safeemail = $db->sql_escape(strip_tags($_POST["Email"]));
        $safetext = $db->sql_escape(strip_tags($_POST["Txt"]));
        $safelat = $db->sql_escape(strip_tags($_POST["maplat"]));
        $satelng = $db->sql_escape(strip_tags($_POST["maplng"]));

        if (((bool) $safetext OR (bool) $safeemail) AND ( isset($safelat) AND isset($satelng)) AND ( $safelat != 0 AND $satelng != 0)) {
            $validphone = false;
            if ((bool) $safetext) {
                $user->get_profile_fields($user->data['user_id']);
                if ($user->profile_fields['pf_phone'] != NULL) {
                    if (isset($user->profile_fields['pf_carrier']) != NULL && $user->profile_fields['pf_carrier'] != 1) {
                        $validphone = true;
                    }
                }
            }

            if (($validphone == false) and ( (bool) $safetext == true)) {
                die('<p>Phone number is either missing from your profile or is invalid, please enter your phone number <a href="http://www.oregon911.net/discussion/ucp.php?i=173">Here</a> without hyphens in the following format 0000000000 and make sure you select a carrier!</p><a href="places">Click here to go back</a>');
            } else {
                if (strlen($safename) > 0 && strlen($safedistance) > 0) {
                    //Array with the data to insert
                    $sql_array = array(
                        'FK_user_id' => $user->data['user_id'],
                        'name' => $safename,
                        'distance' => $safedistance,
                        'lat' => $safelat,
                        'lon' => $satelng,
                        'Email' => (bool) $safeemail,
                        'Txt' => (bool) $safetext
                    );

                    // Create the SQL statement
                    $sql = 'INSERT IGNORE INTO `phpbb_places` ' . $db->sql_build_array('INSERT', $sql_array);

                    // Run the query 
                    $result = $db->sql_query($sql);
                    break;
                }
            }
        }
    }
}

if (ISSET($_GET['del'])) {
    $safename = $db->sql_escape(strip_tags($_GET["del"]));

    $sql_in = array($safename);
    $sql = 'DELETE FROM `phpbb_places` WHERE FK_user_id = ' . $user->data['user_id'] . ' AND ' . $db->sql_in_set('name', $sql_in);

    // Run the query 
    $result = $db->sql_query($sql);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../units.css">
        <meta charset="UTF-8" />
        <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
        <style type="text/css">
            html { height: 100% }
            body { height: 100%; margin: 0; padding: 0 }
            #map_canvas { height: 100% }
            .auto-style1 {
                margin-bottom: 337px;
            }
        </style>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title> Oregon 911 Places</title>
        <script
        src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
        <script>
            //create empty temp array
            var markersArray = [];
            var circlesArray = [];
            //map property
            var map;


            function initialize() {
                //define map
                var mapOptions = {
                    zoom: 9,
                    center: new google.maps.LatLng(45.432913, -122.636261),
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                //create new map
                map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

                //add click event
                google.maps.event.addListener(map, 'click', function (e) {
                    var distance = parseInt(document.getElementById('distance').value);
                    if (distance < 1) {
                        alert('Your distance is too small');
                    }

                    //clear map
                    removeMarkersAndCircles();
                    //draw marker with circle
                    placeMarker(e.latLng, map, distance);
                    //write actual position into inputs
                    writeLabels(e.latLng);
                });
            }

            //function to place marker into map
            function placeMarker(position, map, distance) {
                // Create marker 
                var marker = new google.maps.Marker({
                    map: map,
                    position: position,
                    title: 'Center'
                });

                //add marker into temp array
                markersArray.push(marker);

                //Add circle overlay and bind to marker
                var circle = new google.maps.Circle({
                    map: map,
                    radius: distance,
                    //editable: true,
                    fillColor: '#AA0000'
                });
                circle.bindTo('center', marker, 'position');

                circlesArray.push(circle);
            }


            //remove all markers from map
            function removeMarkersAndCircles() {
                if (markersArray) {
                    for (i = 0; i < markersArray.length; i++) {
                        markersArray[i].setMap(null);
                        circlesArray[i].setMap(null);
                    }
                    markersArray.length = 0;
                    circlesArray.length = 0;
                }
            }

            //write labels into inputs
            function writeLabels(position) {
                document.getElementById('maplat').value = position.lat();
                document.getElementById('maplng').value = position.lng();
            }

            //draw marker and circle by location
            function drawByLocation() {
                var distance = parseInt(document.getElementById('distance').value);
                if (distance < 1) {
                    alert('Your distance is too small');
                }

                //get values from inputs
                var lat = document.getElementById('mapsetlat').value;
                var lng = document.getElementById('mapsetlng').value;

                var position = new google.maps.LatLng(lat, lng);

                //create marker and circle
                removeMarkersAndCircles();
                placeMarker(position, map, distance);
                writeLabels(position);

            }

            //initialize map
            google.maps.event.addDomListener(window, 'load', initialize);
        </script>
    </head>

    <body>
        <div id="wccca-page-wrapper">
            <h1> Places/Alerts </h1>
            <p><a href="http://cad.oregon911.net">Back to call map</a></p>

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
            <details open='true' id="custom-marker" 'class="wccca-details"'>
                <summary>Map</summary>
                <div id="map-canvas" style="width:100%; height:300px;"></div>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table style="width:25%;">
                        <tr>
                            <td>Distance (in meters):</td>
                            <td><input type="text" name="distance" id="distance" value="100" /></td>
                        </tr>

                        <tr>
                            <td>Name:</td>
                            <td><input type="text" name="name" id="name" /></td>
                        </tr>

                        <tr>
                            <td>Alert By:</td>
                            <td>Email <input type="checkbox" name="Email"><br> Text Message <input type="checkbox" name="Txt"></td>
                        </tr>
                    </table>
                    <input type="hidden" name="maplat" id="maplat" value="" />
                    <input type="hidden" name="maplng" id="maplng" value=""  />
                    <input type="submit" value="Add To Places">
                </form>
            </details>

            <details open='true' id="custom-marker" 'class="wccca-details"'>
                <summary>Places</summary>
                <?PHP
                // Call Log Entries 
                echo '<table style="width:100%;">';
                echo '<tr><th style="width:47.5%;">Name</th><th style="width:47.5%;">Distance</th><th style="width:5%;">Delete</th></tr>';
                $sql = "SELECT `phpbb_places`.name, `phpbb_places`.distance FROM `phpbb_places` JOIN `phpbb_users` ON FK_user_id = user_id WHERE `phpbb_places`.FK_user_id  = '" . $user->data['user_id'] . "'";
                $result = $db->sql_query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo '<tr><th>' . htmlspecialchars($row['name']) . '</th><th>' . htmlspecialchars($row['distance']) . '</th><th><a href="places?del=' . htmlspecialchars($row['name']) . '"><img style="border:0;" src="http://cad.oregon911.net/images/WEB/del.png" alt="Delete" width="20" height="20"></a></th></tr>';
                }
                echo '</table>';
                ?>
            </details>

            <?php
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round(($finish - $start), 4);
            echo '<p>Page generated in ' . $total_time . ' seconds.</p>';
            echo '<p>Copyright &copy; ' . date("Y") . ' Oregon 911. All Rights Reserved.</p>';
            ?>

        </div>
        <?PHP echo($analytics); ?>
    </body>
</html>
