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
        <script type="text/javascript" src="./js/leaflet.js"></script>
        <script type="text/javascript" src="./js/Leaflet.label.js"></script>
        <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-label/v0.2.1/leaflet.label.css' rel='stylesheet' />

    </head>
    <body>
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



                <div id='map' style="height:96vh; width: 100%; overflow:hidden;"></div>



            </div>
            <?PHP include ("./inc/nav.php"); ?>
        </div>
        <script type="text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
        <script type="text/javascript">
            var myCalls = [];
            var fire = new L.LayerGroup();
            var EMS = new L.LayerGroup();
            var police = new L.LayerGroup();
            var accidents = new L.LayerGroup();
            var TrfAccid = ["TRF ACC, UNK INJ", "BLOCKING", "NOT BLOCKING", "TRF ACC, INJURY", "MVA-INJURY ACCID", "TRF ACC, NON-INJ", "TAI-TRAPPED VICT", "TAI-HIGH MECHANI", "TAI-PT NOT ALERT", "MVA-UNK INJURY"];

// create a map in the "map" div, set the view to a given place and zoom
// initialize the map on the "map" div with a given center and zoom
            var map = L.map('map', {
                center: [45.432913, -122.636261],
                zoom: 11,
                layers: [fire, EMS, police, accidents]
            });

            // add an OpenStreetMap tile layer
            var OSM = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; Brandan Lasley 2015 &copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);


            // https: also suppported.
            var HERE_hybridDay = L.tileLayer('http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/hybrid.day/{z}/{x}/{y}/256/png8?app_id={app_id}&app_code={app_code}', {
                attribution: 'Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>',
                subdomains: '1234',
                mapID: 'newest',
                app_id: 'Y8m9dK2brESDPGJPdrvs',
                app_code: 'dq2MYIvjAotR8tHvY8Q_Dg',
                base: 'aerial',
                minZoom: 0,
                maxZoom: 20
            });

            // https: also suppported.
            var HERE_normalNight = L.tileLayer('http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/normal.night/{z}/{x}/{y}/256/png8?app_id={app_id}&app_code={app_code}', {
                attribution: 'Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>',
                subdomains: '1234',
                mapID: 'newest',
                app_id: 'Y8m9dK2brESDPGJPdrvs',
                app_code: 'dq2MYIvjAotR8tHvY8Q_Dg',
                base: 'base',
                minZoom: 0,
                maxZoom: 20
            });

            // https: also suppported.
            var HERE_terrainDay = L.tileLayer('http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/terrain.day/{z}/{x}/{y}/256/png8?app_id={app_id}&app_code={app_code}', {
                attribution: 'Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>',
                subdomains: '1234',
                mapID: 'newest',
                app_id: 'Y8m9dK2brESDPGJPdrvs',
                app_code: 'dq2MYIvjAotR8tHvY8Q_Dg',
                base: 'aerial',
                minZoom: 0,
                maxZoom: 20
            });

            var baseLayers = {
                "Standard": OSM,
                "Night Map": HERE_normalNight,
                "Satellite": HERE_hybridDay,
                "Terrain": HERE_terrainDay
            };

            var overlays = {
                "Fire": fire,
                "EMS": EMS,
                "Police": police,
                "Accidents": accidents
            };

            L.control.layers(baseLayers, overlays).addTo(map);

// deletes all markers on map;
            function clearMap() {
                for (var i = 0; i < myCalls.length; i++) {
                    if (!(myCalls[i].id.indexOf("stadic") > -1)) {
                        myCalls[i].call.unbindLabel();
                        if (myCalls[i].layer === "fire") {
                            fire.removeLayer(myCalls[i].call);
                        } else if (myCalls[i].layer === "EMS") {
                            EMS.removeLayer(myCalls[i].call);
                        } else if (myCalls[i].layer === "police") {
                            police.removeLayer(myCalls[i].call);
                        } else if (myCalls[i].layer === "accidents") {
                            accidents.removeLayer(myCalls[i].call);
                        } else {
                            map.removeLayer(myCalls[i].call);
                        }
                    }
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

// Add Marker to map and return the created marker.
            function addMarker(idx, html, lat, lng, type, iconW, iconH, iconURL, labelname, label) {
                if (!searchMarkers(idx)) {
                    //console.log("Adding call: " + idx);
                    var markerLocation = new L.LatLng(lat, lng);
                    var offset = 0;
// This works, but is not perfect!                    
// offset = -(((80 - labelname.length)) + (labelname.length / 2) );

                    if (labelname.length <= 5) {
                        offset = -(40 - labelname.length / 2);
                    } else if ((labelname.length > 5) && (labelname.length < 10)) {
                        offset = -(55 - labelname.length / 2);
                    } else if ((labelname.length > 10) && (labelname.length < 16)) {
                        offset = -(77 - labelname.length / 2);
                    } else if ((labelname.length > 16) && (labelname.length < 21)) {
                        offset = -(82 - labelname.length / 2);
                    } else {
                        offset = -(85 - labelname.length / 2);
                    }

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

                    var isAccident = false;
                    for (var i = TrfAccid.length; i--; ) {
                        if (TrfAccid[i] === labelname) {
                            isAccident = true;
                        }
                    }

                    if (isAccident) {
                        var layer = "accidents";
                        var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(accidents).showLabel();
                    } else {
                        if (type === "F") {
                            var layer = "fire";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(fire).showLabel();
                        } else if (type === "M") {
                            var layer = "EMS";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(EMS).showLabel();
                        } else if (type === "P") {
                            var layer = "police";
                            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(police).showLabel();
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
                            var offset = 0;

                            if (!type === myCalls[i].layer) {
                                cleanLayer(myCalls[i]);
                                if (isAccident) {
                                    var layer = "accidents";
                                    myCalls[i].addTo(accidents).showLabel();
                                } else {
                                    if (type === "F") {
                                        var layer = "fire";
                                        myCalls[i].addTo(fire).showLabel();
                                    } else if (type === "M") {
                                        var layer = "EMS";
                                        myCalls[i].addTo(EMS).showLabel();
                                    } else if (type === "P") {
                                        var layer = "police";
                                        myCalls[i].addTo(police).showLabel();
                                    } else {
                                        var layer = "other";
                                        if (label) {
                                            myCalls[i].addTo(map).showLabel();
                                        } else {
                                            myCalls[i].addTo(map);
                                        }
                                    }
                                }
                                myCalls[i].layer = layer;
                            }

                            if (labelname.length <= 5) {
                                offset = -(40 - labelname.length / 2);
                            } else if ((labelname.length > 5) && (labelname.length < 10)) {
                                offset = -(55 - labelname.length / 2);
                            } else if ((labelname.length > 10) && (labelname.length < 16)) {
                                offset = -(77 - labelname.length / 2);
                            } else if ((labelname.length > 16) && (labelname.length < 21)) {
                                offset = -(82 - labelname.length / 2);
                            } else {
                                offset = -(85 - labelname.length / 2);
                            }
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
<?PHP
if ($_GET['label'] != "n") {
    ?>
                                    myCalls[i].call.bindLabel(labelname, {noHide: true}).showLabel();
                                    ;
    <?PHP
}
?>
                            }
                            return true;
                        }
                    }
                }
                return false;
            }

// remove marker from map and data structure. This doesn't work.
            /* function removeMarker(idx) {
             for (var i = 0; i < myCalls.length; i++) {
             if (myCalls[i].id === idx) {
             myCalls[i].call.unbindLabel();
             map.removeLayer(myCalls[i].call);
             if (idx > 0) {
             myCalls.splice(idx, 1);
             } else {
             myCalls = [];
             }
             return true;
             }
             }
             return false; // always returns false because its broken.
             }*/

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
                            //console.log("Removing call: " + myCalls[i].id);
                            myCalls[i].call.unbindLabel();

                            cleanLayer(myCalls[i]);
                            myCalls.splice(i, 1);
                        }
                    } else {
                        //console.log("================= REMOVING ALL CALLS! =================" + myCalls[i].id);
                        clearMap();
                        myCalls = [];
                    }
                }
            }

            function cleanLayer(myCall) {
                if (myCall.layer === "fire") {
                    fire.removeLayer(myCall.call);
                } else if (myCall.layer === "EMS") {
                    EMS.removeLayer(myCall.call);
                } else if (myCall.layer === "police") {
                    police.removeLayer(myCall.call);
                } else if (myCall.layer === "accidents") {
                    accidents.removeLayer(myCall.call);
                } else {
                    map.removeLayer(myCall.call);
                }
            }

            // Run
            if (firstrun) {
                getMarkerData();
                firstrun = false;
            }
            ajaxObj.get();
        </script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <?PHP echo($analytics); ?>
    </body>
</html>