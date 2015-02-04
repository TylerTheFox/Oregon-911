<?PHP
require_once("loggedin.php");
require_once("google.php");
?>
<!DOCTYPE html>
<html style="overflow:hidden;">
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Map</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body style="overflow:hidden;">
        <div id="page">
            <div class="header">
                <a href="#menu"></a>
                Oregon 911 - Map
            </div>
            <div class="content">
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
                <div id='map' style="height:100vh; width: 100%; overflow:hidden;"></div>
            </div>
            <?PHP include ("./inc/nav.php"); ?>
        </div>
        <script type="text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <?PHP
        if (!$mobile) {
            ?>
            <script type='text/javascript' src="https://maps.google.com/maps/api/js?libraries=adsense&amp;sensor=false&amp;extn=.js"></script>
            <?PHP
        } else {
            ?>
            <script type='text/javascript' src="https://maps.google.com/maps/api/js?sensor=false"></script>
            <?PHP
        }
        ?>
        <script async type='text/javascript'>
            var locations = {};
            var locs = {
<?PHP
$CallIDM = 1;

// Stations 
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_stations` WHERE county != 'M' ORDER BY ID";
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
        echo (",$CallIDM: { info:'" . "Fire Station: " . $rows['ABBV'] . " City: " . $rows['CITY'] . " Agency: " . $rows['DISTRICT'] . " Address: " . $rows['ADDRESS'] . "', lat:" . $rows['LAT'] . ", lng:" . $rows['LON'] . ", icon:'../images/MISC/firedept.png' }");
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

$sql = "SELECT * FROM `oregon911_cad`.`pdx911_calls` WHERE county != 'M' AND lon != '0' AND lat !='0'";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    echo(',' . $CallIDM . ': { info:\'<div class="incident">' .
    '<h1> Call: ' . $rows['callSum'] . ' </h1>' .
    '<h3> Address: ' . $rows['address'] . ' </h3>' .
    '<p>Station: ' . $rows['station'] . '</p>' .
    '<p> Time: ' . $rows['timestamp'] . ' </p>');
    echo('<a href="./units?call=' . $rows['GUID'] . '&county=' . $rows['county'] . '" target="_blank">More Info</a></div>\'' .
    ', lat:' . $rows['lat'] . ', lng:' . $rows['lon'] . ', icon:\'' . $rows['icon'] . '\' }');
    $CallIDM++;
}
?>
            };

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                streetViewControl: true,
                center: new google.maps.LatLng(45.432913, -122.636261),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

<?PHP
if (!$mobile) {
    ?>
                var adUnitDiv = document.createElement('div');
                var adUnitOptions = {
                    format: google.maps.adsense.AdFormat.VERTICAL_BANNER,
                    position: google.maps.ControlPosition.RIGHT_BOTTOM,
                    publisherId: 'ca-pub-4799522447106781',
                    map: map,
                    visible: true
                };
                var adUnit = new google.maps.adsense.AdUnit(adUnitDiv, adUnitOptions);
    <?PHP
}
?>

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
                delay: 30000,
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
            ajaxObj.get();

        </script>
        <?PHP echo($analytics); ?>
    </body>
</html>