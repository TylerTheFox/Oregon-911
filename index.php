<?PHP
require_once("database.php");
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
                <a href="#menu" class="main-menu"></a>
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
            var firestations = new L.LayerGroup();
            var hydrants = new L.LayerGroup();
            var hospitals = new L.LayerGroup();
            var TrfAccid = [" BLOCKING", "CRASH, UNK INJ", "TAI-MAJOR INCIDE", "TRF ACC, UNK INJ", "BLOCKING", "NOT BLOCKING", "TRF ACC, INJURY", "MVA-INJURY ACCID", "TRF ACC, NON-INJ", "TAI-TRAPPED VICT", "TAI-HIGH MECHANI", "TAI-PT NOT ALERT", "MVA-UNK INJURY"];
            var updateAllowed = true; // Bug fix in Leaflet Labels
            var OR911TileErrorCount = 0;
            var failOver = false;

// create a map in the "map" div, set the view to a given place and zoom
// initialize the map on the "map" div with a given center and zoom
            var map = L.map('map', {
                center: [45.432913, -122.636261],
                zoom: 11,
                layers: [fire, EMS, police, accidents, firestations]
            });

            map.on('zoomstart', function () {
                updateAllowed = false;
            });

            map.on('zoomend', function () {
                updateAllowed = true;
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
                if (updateAllowed) {
                    var tmpMyCalls = [];
                    $.each(locObj, function (key, loc) {
                        tmpMyCalls.push(key);
                        addMarker(key, loc.info, loc.lat, loc.lng, loc.type, loc.iconW, loc.iconH, loc.icon, loc.labelname, loc.label);
                    });
                    cleanMarkers(tmpMyCalls);
                }
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

            function getZoom() {
                alert(map.getZoom());
            }

// Run
            if (firstrun) {
                getMarkerData();
                firstrun = false;
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
// User Geolocation stuff
            function errorHandler(error) {
// sad face :(
            }
            function successHandler(location) {
                addMarker("donotremove1", "<div style='width: 200px;'><b>YOU</b> <p>Accuracy: &#177; " + location.coords.accuracy + " meters</p></div>", location.coords.latitude, location.coords.longitude, "other", 11, 11, "./images/MISC/pos.png", 0, false);
            }
        </script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <?PHP echo($analytics); ?>
    </body>
</html>