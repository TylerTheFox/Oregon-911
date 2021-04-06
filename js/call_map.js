var myCalls = [];
var fire = new L.LayerGroup();
var EMS = new L.LayerGroup();
var police = new L.LayerGroup();
var accidents = new L.LayerGroup();
var firestations = new L.LayerGroup();
var TrfAccid = [" BLOCKING", "CRASH, UNK INJ", "TAI-MAJOR INCIDE", "TRF ACC, UNK INJ", "BLOCKING", "NOT BLOCKING", "TRF ACC, INJURY", "MVA-INJURY ACCID", "TRF ACC, NON-INJ", "TAI-TRAPPED VICT", "TAI-HIGH MECHANI", "TAI-PT NOT ALERT", "MVA-UNK INJURY"];
//var OR911TileErrorCount = 0;
//var failOver = false;

// create a map in the "map" div, set the view to a given place and zoom
// initialize the map on the "map" div with a given center and zoom
var map = L.map('map', 
{
    center: [0.0, 0.0],
    zoom: 16,
    layers: [fire, EMS, police, accidents, firestations]
});

// Oregon 911 tile layer, HTTPS not supported.
/*var OR911_OSM = L.tileLayer('http://www.server2.oregon911.net/osm/{z}/{x}/{y}.png', 
{
    attribution: '&copy; Brandan Lasley 2015 Map &copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
    minZoom: 0,
    maxZoom: 18
}).addTo(map);*/

/*OR911_OSM.on('tileerror', function (error, tile) 
{
    if (OR911TileErrorCount > 3) {
        // OR911s tile server is offline or slow, switch to OSM.
        OR911TileServerOffline();
    } else {
        // Don't continue counting.
        OR911TileErrorCount++;
    }
});*/

// Oregon 9-1-1's Tile Servers No Longer Exist
OR911_OSM = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', 
{
    attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
    minZoom: 0,
    maxZoom: 19
}).addTo(map);

var baseLayers = 
{
    "Standard": OR911_OSM
};

var overlays = 
{
    "Fire": fire,
    "EMS": EMS,
    "Police": police,
    "Accidents": accidents,
    "Fire Stations": firestations
};

L.control.layers(baseLayers, overlays).addTo(map);

// deletes all markers on map;
function clearMap() 
{
    for (var i = 0; i < myCalls.length; i++) 
    {
        map.removeLayer(myCalls[i].call);
    }
    return true;
}

// display all markers id on map;
function displayAll() 
{
    for (var i = 0; i < myCalls.length; i++)
    {
        alert(myCalls[i].id);
    }
    return true;
}

// Search though all markers.
function searchMarkers(idx) 
{
    for (var i = 0; i < myCalls.length; i++) 
    {
        if (myCalls[i].id === idx) 
        {
            return true;
        }
    }
    return false;
}

// Positions the label correctly.. enough
function getLabelOffset(labelname) 
{
    var offset = 30 + (35 % 35 + (3 * labelname.length));
    return -offset;
}


// Changes call to another layer group.
function changeLayer(myCall, type, isMVA) 
{
    if (isMVA) 
    {
        var layer = "accidents";
        myCall.call.addTo(accidents);
    } 
    else 
    {
        if (type === "F") 
        {
            var layer = "F";
            myCall.call.addTo(fire);
        } 
        else if (type === "M") 
        {
            var layer = "M";
            myCall.call.addTo(EMS);
        } 
        else if (type === "P") 
        {
            var layer = "P";
            myCall.call.addTo(police);
        } 
        else 
        {
            var layer = "other";
            myCall.call.addTo(map);
        }
    }
    return layer;
}

// Is this an accident or a normal call?
function isAccident(labelname) 
{
    for (var i = TrfAccid.length; i--; ) 
    {
        if (TrfAccid[i] === labelname) 
        {
            return true;
        }
    }
    return false;
}

// Remove call from current layer.
function cleanLayer(myCall) 
{
    if (myCall.layer === "F") 
    {
        fire.removeLayer(myCall.call);
    } 
    else if (myCall.layer === "M") 
    {
        EMS.removeLayer(myCall.call);
    } 
    else if (myCall.layer === "P") 
    {
        police.removeLayer(myCall.call);
    } 
    else if (myCall.layer === "accidents") 
    {
        accidents.removeLayer(myCall.call);
    } 
    else 
    {
        map.removeLayer(myCall.call);
    }
}

// Add Marker to map and return the created marker.
function addMarker(idx, html, lat, lng, type, iconW, iconH, iconURL, labelname, label) 
{
    if (!searchMarkers(idx)) 
    {
        //console.log("Adding call: " + idx);
        var markerLocation = new L.LatLng(lat, lng);
        var offset = getLabelOffset(labelname);
        
        var Marker = L.Icon.extend(
        {
            options: 
            {
                iconUrl: iconURL,
                iconSize: [iconW, iconH],
                labelAnchor: new L.Point(offset, 35),
                zoomAnimation: false,
                clickable: true,
                shadowSize: [iconW, iconH]
            }
        });
        
        var marker = new Marker();

        if (isAccident(labelname)) 
        {
            var layer = "accidents";
            var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(accidents).showLabel();
        } 
        else 
        {
            if (type === "F") 
            {
                var layer = "F";
                var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(fire).showLabel();
            } 
            else if (type === "M") 
            {
                var layer = "M";
                var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(EMS).showLabel();
            } 
            else if (type === "P") 
            {
                var layer = "P";
                var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(police).showLabel();
            } 
            else if (type === "FS") 
            {
                var layer = "FS";
                var callMarker = L.marker(markerLocation, {icon: marker}).bindPopup(html).addTo(firestations).showLabel();
            }
            else 
            {
                var layer = "other";
                
                if (label) 
                {
                    var callMarker = L.marker(markerLocation, {icon: marker}).bindLabel(labelname, {noHide: true}).bindPopup(html).addTo(map).showLabel();
                } 
                else 
                {
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
function updateMarker(idx, html, lat, lng, type, iconW, iconH, iconURL, labelname, label) 
{
    if (!(idx.indexOf("stadic") > -1)) 
    {
        
        var markerLocation = new L.LatLng(lat, lng);
        for (var i = 0; i < myCalls.length; i++) 
        {
            if (myCalls[i].id === idx) 
            {
                
                var markerLocation = new L.LatLng(lat, lng);

                if ((type !== myCalls[i].layer) && (myCalls[i].layer !== 'accidents')) 
                {
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
                if (label) 
                {
                    myCalls[i].call.bindLabel(labelname, {noHide: true}).showLabel();
                }
                return true;
            }
        }
    }
    return false;
}

// remove marker from map and data structure. This doesn't work.
function removeMarker(idx) 
{
    console.log("Removing Marker" + idx);
    for (var i = 0; i < myCalls.length; i++) 
    {
        if (myCalls[i].id === idx) 
        {
            myCalls[i].call.unbindLabel();
            cleanLayer(myCalls[i]);
            myCalls.splice(i, 1);
            return true;
        }
    }
    alert("Couldn't remove a marker, this should NOT happen... ever");
    return false;
}

function setMarkers(locObj) 
{
    navigator.geolocation.getCurrentPosition(successHandler, errorHandler); // User Geolocation stuff
    var tmpMyCalls = [];
    $.each(locObj, function (key, loc) 
    {
        tmpMyCalls.push(key);
        addMarker(key, loc.info, loc.lat, loc.lng, loc.type, loc.iconW, loc.iconH, loc.icon, loc.labelname, loc.label);
    });
    cleanMarkers(tmpMyCalls);
}

// Remove markers no longer existing. 
function cleanMarkers(tmpMyCalls) 
{
    for (var i = 0; i < myCalls.length; i++) 
    {
        if (tmpMyCalls.length > 0) 
        {
            var found = false;
            for (var id = 0; id < tmpMyCalls.length; id++) 
            {
                if (myCalls[i].id === tmpMyCalls[id]) 
                {
                    found = true;
                }
            }
            if (found === false) 
            {
                if (!(myCalls[i].id.indexOf("donotremove") > -1)) 
                {
                    removeMarker(myCalls[i].id);
                }
            }
        } else 
        {
            //console.log("================= REMOVING ALL CALLS! =================" + myCalls[i].id);
            clearMap();
        }
    }
}

function OR911TileServerOffline() 
{
    /*if (!failOver) {
        map.removeLayer(OR911_OSM);
        // Fall back to OSM tile server
        OR911_OSM = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
            minZoom: 0,
            maxZoom: 19
        }).addTo(map);

        console.log("Oregon 911 tile server not responding...");
        failOver = true;
    }*/
}

// Run
navigator.geolocation.getCurrentPosition(successHandler, errorHandler); // User Geolocation stuff

// User Geolocation stuff
function errorHandler(error) 
{
    // sad face :(
}

function successHandler(location) 
{
    addMarker("donotremove1", "<div style='width: 200px;'><b>YOU</b> <p>Accuracy: &#177; " + location.coords.accuracy + " meters</p></div>", location.coords.latitude, location.coords.longitude, "other", 11, 11, "./images/MISC/pos.png", 0, false);
}

