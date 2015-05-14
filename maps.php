<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("loggedin.php");
require_once("google.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Maps</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <script type='text/javascript' src="https://maps.google.com/maps/api/js?sensor=false&extn=.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body style="overflow: hidden;">
        <div id="page">
            <div class="header">
                <a href="#menu"></a>
                Oregon 911 - Maps
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

            <?php if ($_GET['mode'] == '24hr') { ?>
                <div id="map_2385853" style="width:100%; height:100vh;"></div>
                <script type='text/javascript'>
                    var locations = {};
                    var locs = {
    <?PHP
    $CallIDM = 1;

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

    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_archive` WHERE TIMESTAMPDIFF(HOUR, timestamp, NOW()) < 24 and county !='M' ";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
        echo(',' . $CallIDM . ': { info:\'<div class="incident">' .
        '<h1> Call: ' . $rows['callSum'] . ' </h1>' .
        '<h3> Address: ' . $rows['address'] . ' </h3>' .
        '<p>Station: ' . $rows['station'] . '</p>' .
        '<p> Time: ' . $rows['timestamp'] . ' </p>');
        echo('<a href="http://cad.oregon911.net/units?call=' . $rows['GUID'] . '&county=' . $rows['county'] . '&type=' . $rows['type']  . '" target="_blank">More Info</a></div>\'' .
        ', lat:' . $rows['lat'] . ', lng:' . $rows['lon'] . ', icon:\'' . $rows['icon'] . '\' }');
        $CallIDM++;
    }
    ?>
                    };

                    var map = new google.maps.Map(document.getElementById('map_2385853'), {
                        zoom: 10,
                        streetViewControl: true,
                        center: new google.maps.LatLng(45.3655611484158, -122.836181358989),
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
                            url: "http://cad.oregon911.net/map",
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
                <?PHP include ("./inc/nav.php"); ?>
            </div>
        <?php } else { ?>
            <div class="container">
                <p class="lead">404. Page Not Found :(</p>
            </div><!-- /.container -->
        <?php } ?>

        <script type = "text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
        <?PHP echo($analytics); ?>
    </body>
</html>