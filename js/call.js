// Angular JS stuff
var app = angular.module('OR911_CALLS', ['ngRoute', 'angularCSS']);

app.controller('callController', function ($scope, $css, $http)
{
    
    $scope.InitalLoadComplete = false;
    
    $scope.MainTheme = 
    [  
        { name: 'MAIN',             url: 'css/main.css' },
        { name: 'CALL',             url: 'css/call.css' },    
        { name: 'UNITS',            url: 'css/units.css' },    
        { name: 'LEAFLET',          url: 'css/leaflet.css' },
        { name: 'LEAFLET LABEL',    url: 'css/leaflet.label.css' }
    ];
    
    $scope.CallThemes = 
    [  
        { name: 'WCCCA FIRE',       url: 'css/call/wccca_fire.css' },  
        { name: 'WCCCA MEDICAL',    url: 'css/call/wccca_medical.css' },  
        { name: 'WCCCA POLICE',     url: 'css/call/wccca_police.css' },  
        
        { name: 'CCOM FIRE',        url: 'css/call/ccom_fire.css' },  
        { name: 'CCOM MEDICAL',     url: 'css/call/ccom_medical.css' },  
        { name: 'CCOM POLICE',      url: 'css/call/ccom_police.css' }
    ];
    
    $scope.CallStatusClass          = [ "callCleared", "callActive" ];
    $scope.UnitTextStatusClass      = [ "dispatched", "enroute", "onscene", "clear" ];
    $scope.UnitDetailStatusClass    = [ "noneDetail", "dispatchedDetail", "enrouteDetail", "onsceneDetail", "clearDetail" ];
    
    $scope.DrillLocations           = [ "WCCCA 911", "2200 KAEN RD", "17911 NW EVERGREEN PK", "DRILL", "TEST" ];
        
    $scope.parseJSON = function(json)
    {
        $scope.IncidentData = angular.fromJson(json);
        $scope.InitializeCall();
    }
    
    $scope.InitializeCall = function()
    {
        // Center Map
        map.panTo(new L.LatLng($scope.IncidentData.Header.callLat, $scope.IncidentData.Header.callLng));
        
        // Generates string for map marker
        $scope.MarkerStr =  $scope.IncidentData.Header.callType             + " " + 
                            $scope.IncidentData.Header.respondingStation    + " " + 
                            $scope.IncidentData.Header.callNum              + " " +
                            $scope.IncidentData.Header.callSummery          + " " +
                            $scope.IncidentData.Header.callAddress          + " " +
                            $scope.IncidentData.Header.respondingUnits;
        
        // Remove the first slash for relative paths
        $scope.IncidentData.Header.callIcon = $scope.IncidentData.Header.callIcon.substring(1);

        // Adds or updates the marker to the map
        if (!$scope.InitalLoadComplete)
        {
            addMarker(  "1", 
                        $scope.MarkerStr, 
                        $scope.IncidentData.Header.callLat, 
                        $scope.IncidentData.Header.callLng, 
                        $scope.IncidentData.Header.callType, 
                        32, 
                        37, 
                        $scope.IncidentData.Header.callIcon, 
                        $scope.IncidentData.Header.callSummery, 
                        true
                    );
            $scope.InitalLoadComplete = true;
        }
        else  
        {
            updateMarker(   "1", 
                            $scope.MarkerStr, 
                            $scope.IncidentData.Header.callLat, 
                            $scope.IncidentData.Header.callLng, 
                            $scope.IncidentData.Header.callType, 
                            32, 
                            37, 
                            $scope.IncidentData.Header.callIcon,
                            $scope.IncidentData.Header.callSummery, 
                            true
                        );
        } 
    }
    
    $scope.isCallADrill = function(type)
    {
        var callAddressUpper = $scope.IncidentData.Header.callAddress.toUpperCase();
        
        var arrayLength = $scope.DrillLocations.length;
        for (var i = 0; i < arrayLength; i++) 
        {
            if ($scope.DrillLocations[i] === callAddressUpper)
            {
                return true;
            }
        }
        return false;
    }
    
    $scope.resolveTypeName = function(type)
    {
        var ret = "Unknown";
        
        if (type === "F")
        {
            ret = "FIRE";
        } 
        else if (type === "M")
        {
            ret = "MEDICAL";
        } 
        else if (type === "P")
        {
            ret = "POLICE";
        }
        return ret;
    };
    
    $scope.resolveStatusName = function(type)
    {
        var ret = "CLEARED";
        
        if (type === 1)
        {
            ret = "ACTIVE";
        }
        return ret;
    };
    
    $scope.ResolveTheme  = function()
    {
        var themeName = "WCCCA FIRE";
        
        if ($scope.IncidentData.Header.callType === "F")
        {
            if ($scope.IncidentData.Header.callCounty === "W")
            {
                themeName = "WCCCA FIRE";
            } 
            else if ($scope.IncidentData.Header.callCounty === "C")
            {
                themeName = "CCOM MEDICAL";
            }
            else
            {
                console.log("Error Unknown County!");
            }
        }
        else if ($scope.IncidentData.Header.callType === "M")
        {
            if ($scope.IncidentData.Header.callCounty === "W")
            {
                themeName = "WCCCA MEDICAL";
            } 
            else if ($scope.IncidentData.Header.callCounty === "C")
            {
                themeName = "CCOM MEDICAL";
            }
            else
            {
                console.log("Error Unknown County!");
            }
        }
        else if ($scope.IncidentData.Header.callType === "P")
        {
            if ($scope.IncidentData.Header.callCounty === "W")
            {
                themeName = "WCCCA POLICE";
            } 
            else if ($scope.IncidentData.Header.callCounty === "C")
            {
                themeName = "CCOM POLICE";
            }
            else
            {
                console.log("Error Unknown County!");
            }
        }
        else
        {
            console.log("Error Unknown Call Type!");
        }
        
        // Remove all stylesheets
        $css.removeAll();
        
        // Build CSS data
        angular.forEach($scope.MainTheme, function(mainTheme, MainThemeKey) 
        {
            $css.add(mainTheme.url);
        });
        
        // Resolve Theme
        angular.forEach($scope.CallThemes, function(callTheme, CallThemeKey) 
        {
            if (callTheme.name === themeName)
            {
                $css.add(callTheme.url);
            }
        });
    }
    
    $scope.ResolveUnitDetailClasses  = function(type, unit)
    {
        var ret             = $scope.UnitDetailStatusClass[0];
        var highestClear    = 0;
        
        if (type > 4 || type < 0)
        {
            console.log("Error unknown unit detail type");
        }

        if (unit.clear !== null)
        {
            highestClear = 4;
        } 
        else if (unit.onscene !== null)
        {
            highestClear = 3;
        }
        else if (unit.enroute !== null)
        {
            highestClear = 2;
        }
        else if (unit.dispatched !== null)
        {
            highestClear = 1;
        }
        
        if (highestClear === type)
        {
            ret = $scope.UnitDetailStatusClass[highestClear];
        }

        return ret;
    }
    
    $scope.ResolveUnitStatusClasses  = function(unit)
    {
        var highestClear = 0;
        
        if (unit.clear !== null)
        {
            highestClear = 3;
        } 
        else if (unit.onscene !== null)
        {
            highestClear = 2;
        }
        else if (unit.enroute !== null)
        {
            highestClear = 1;
        }
        else if (unit.dispatched !== null)
        {
            highestClear = 0;
        }
        
        return $scope.UnitTextStatusClass[highestClear];
    }
    
    
    $scope.calculateDistance = function(LatFrom, LngFrom, LatTo, LngTo)
    {
        if  (
                LatFrom === 0       || LngFrom === 0    || LatTo === 0      || LngTo === 0 ||
                LatFrom === null    || LngFrom === null || LatTo === null   || LngTo === null
            )
        {
            return 0;
        }
        
        var pi80        = Math.PI / 180.0;
        
        LatFrom         *= pi80;
        LngFrom         *= pi80;
        LatTo           *= pi80;
        LngTo           *= pi80;
        
        var r           = 6372.797; // mean radius of Earth in km
        var dlat        = LatTo - LatFrom;
        var dlon        = LngTo - LngFrom;
        var a           = Math.sin(dlat / 2.0) * Math.sin(dlat / 2.0) + Math.cos(LatFrom) * Math.cos(LatTo) * Math.sin(dlon / 2.0) * Math.sin(dlon / 2.0);
        var c           = 2.0 * Math.atan2(Math.sqrt(a), Math.sqrt(1.0 - a));
        var km          = r * c;
        
        return (km * 0.621371).toFixed(2);
    }
    
    $scope.reloadData = function()
    {
        var api_url = "./api/1.0/get/call/index.php?call=" + $scope.IncidentData.Header.callNum + "&county=" + $scope.IncidentData.Header.callCounty + "&type=" + $scope.IncidentData.Header.callType;
        
        $http({
              method: 'GET',
              url: api_url
           }).then(function (response){
                $scope.IncidentData = response.data;
                $scope.InitializeCall();
                
           },function (error){
                console.log("Error while refreshing data!");
           });
    }
    
    $scope.parseJSON(callJSONStr);
    $scope.ResolveTheme();
    
    setInterval(function () 
    {
        $scope.reloadData();
    }, 30000);
    
});