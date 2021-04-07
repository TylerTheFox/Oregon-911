<?PHP require("impl/call_impl.php"); ?>
<!DOCTYPE html>
<html ng-app="OR911_CALLS" ng-controller="callController">
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="Brandan Tyler Lasley" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
<?PHP echo(generateTwitterMetaData($callInfo)); ?>
        <title ng-if="callValid">Oregon 911 - Call {{ IncidentData.Header.callSummery }} (#{{ IncidentData.Header.callNum }})</title>
        <title ng-if="!callValid">Oregon 911 - Invalid Call</title>
    </head>
    <body>
        <div id="page">
            <div class="header">
                <p>Oregon 911 - Incident</p>
            </div>
            
            <div class="content">
                <div style="padding-top: 20px;">
                    <div id="page-wrapper">
                        <div ng-if="!callValid">
                            <h1> Call Log: Invalid Call!</h1>
                            
                            <details open>
                                <summary>CALL ERROR</summary>
                                <br>
                                <b> This call cannot be retrieved. </b>
                                <br>
                                <br>
                            </details>
                        </div>
                        <h1 ng-if="callValid"> Call Log: {{ IncidentData.Header.callSummery }}</h1>
                        
                        <details ng-if="callValid" open>
                            <summary>CALL {{ IncidentData.Header.callNum }}</summary>
                            <div id="callinfo">
                                <table>
                                    <tr>
                                        <th scope="row">Summary</th>
                                        <td>{{ IncidentData.Header.callSummery }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Number</th>
                                        <td>{{ IncidentData.Header.callNum }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Type</th>
                                        <td>{{ resolveTypeName(IncidentData.Header.callType) }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Date</th>
                                        <td>{{ IncidentData.Header.callTimestamp }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Address</th>
                                        <td>{{ IncidentData.Header.callAddress }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Agency</th>
                                        <td>{{ IncidentData.Header.respondingAgency }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Station</th>
                                        <td>{{ IncidentData.Header.respondingStation }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Units</th>
                                        <td><span ng-repeat="unit in IncidentData.Units" ng-class="ResolveUnitStatusClasses(unit)" title="{{ unit.stationAgency }}">{{ unit.unit }}{{$last ? '' : ', '}}</span></td>
                                    </tr>
                                    
                                    <tr ng-if="isCallADrill()">
                                        <th scope="row">Additional Info</th>
                                        <td class="DrillWarning">This address/call type is known to be a testing/drill address/call type.</td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td ng-class="CallStatusClass[IncidentData.Header.callActive]">{{ resolveStatusName(IncidentData.Header.callActive) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </details>

                        <details open>
                            <summary>Map</summary>
                            <div id="map" style="width:100%; height:300px;"></div>
                        </details>

                        <details ng-if="callValid" open>
                            <summary>Call Log</summary>

                            <table ng-if="IncidentData.Calllog !== null" style="width:100%;">
                                <tr>
                                    <th style="width:10%;">Date</th>
                                    <th style="width:10%;">Author</th>
                                    <th style="width:70%;">Entry</th>
                                </tr>
                                <tr ng-repeat="clog in IncidentData.Calllog">
                                    <th>{{ clog.timestamp }}</th>
                                    <th ng-if="clog.Twitter !== null"><a href="//www.twitter.com/{{ clog.Twitter }}">{{ clog.username }}</a></th>
                                    <th ng-if="clog.Twitter === null"><p>{{ clog.username }}</p></th>
                                    <th>{{ clog.entry }}</th>
                                </tr>
                            </table>  
                        </details>

                        <details ng-if="callValid" open>
                            <summary>Units</summary>
                                <table ng-if="IncidentData.Units !== null" style="width:100%;">
                                   <tr>
                                      <th>Unit</th>
                                      <th ng-if="!isMobile">Agency</th>
                                      <th>Station</th>
                                      <th ng-if="!isMobile">Dispatched</th>
                                      <th ng-if="!isMobile">Enroute</th>
                                      <th ng-if="!isMobile">Onscene</th>
                                      <th ng-if="!isMobile">Clear</th>
                                      <th ng-if="!isMobile">Distance (from station)</th>
                                      <th ng-if="isMobile">Status</th>
                                   </tr>
                                   <tr ng-repeat="unit in IncidentData.Units">
                                      <th>{{ unit.unit }}</th>
                                      <th ng-if="!isMobile">{{ unit.stationAgency }}</th>
                                      <th>{{ unit.stationAbbreviation }}</th>
                                      <th ng-if="!isMobile" ng-class="ResolveUnitDetailClasses(1, unit)">{{ unit.dispatched }}</th>
                                      <th ng-if="!isMobile" ng-class="ResolveUnitDetailClasses(2, unit)">{{ unit.enroute }}</th>
                                      <th ng-if="!isMobile" ng-class="ResolveUnitDetailClasses(3, unit)">{{ unit.onscene }}</th>
                                      <th ng-if="!isMobile" ng-class="ResolveUnitDetailClasses(4, unit)">{{ unit.clear }}</th>
                                      <th ng-if="!isMobile">{{ calculateDistance(unit.stationLat, unit.stationLng, IncidentData.Header.callLat, IncidentData.Header.callLng) }} m</th>
                                      <th ng-if="isMobile" ng-class="ResolveUnitDetailClasses(FindUnitHighestClearStatus(unit)+1, unit)">{{ GetUnitsHighestClearStatusTime(unit) }}</th>
                                   </tr>
                                </table>
                        </details>

                        <details ng-if="callValid" open>
                            <summary>Flags</summary>
                            
                            <table ng-if="IncidentData.Flags !== null" style="width:100%;">
                               <tr>
                                  <th>Unit</th>
                                  <th>Dispatched</th>
                                  <th>Clear</th>
                               </tr>
                               <tr ng-repeat="flag in IncidentData.Flags">
                                  <th>{{ flag.Flag }}</th>
                                  <th>{{ flag.dispatched }}</th>
                                  <th>{{ flag.clear }}</th>
                               </tr>
                            </table>
                        </details>

                        <details ng-if="callValid" open>
                            <summary>Call Change log</summary>
                            
                            <table ng-if="IncidentData.ChangeLog !== null" style="width:100%;">
                               <tr>
                                  <th>Date</th>
                                  <th>Change</th>
                               </tr>
                               <tr ng-repeat="change in IncidentData.ChangeLog">
                                  <th>{{ change.timestamp }}</th>
                                  <th ng-if="change.update === 1">{{ change.callSummery }}</th>
                                  <th ng-if="change.update === 2">{{ change.callAddress }}</th>
                                  <th ng-if="change.update === 3">Marker Moved ({{ change.callLat }},{{ change.callLng }})</th>
                               </tr>
                            </table>

                        </details>

                        <details  ng-if="callValid"open>
                            <summary>Last 10 Calls From This Address</summary>
                            <table ng-if="IncidentData.LastTen !== null" style="width:100%;">
                                <tr>
                                    <th>Date</th>
                                    <th ng-if="!isMobile">Type</th>
                                    <th>Summary</th>
                                    <th>URL</th>
                                </tr>
                               <tr ng-repeat="call in IncidentData.LastTen">
                                  <th>{{ call.callTimestamp }}</th>
                                  <th ng-if="!isMobile">{{ resolveTypeName(call.callType) }}</th>
                                  <th>{{ call.callSummery }}</th>
                                  <th><a href="./call.php?call={{ call.callNum }}&county={{ call.callCounty }}&type={{ call.callType }}">Goto Call</a></th>
                               </tr>
                            </table>
                        </details>
                        <?php
                            echo "<p>Copyright &copy; " . date("Y") . " Oregon 911. All Rights Reserved.</p>\n";
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <br>
        
        <!-- Initial Call Data Load (Preloaded by server) -->
        <?PHP
        if ($call_valid)
        {
            $callNum        = $callInfo->Header->callNum;
            $callCounty     = $callInfo->Header->callCounty;
            $callType       = $callInfo->Header->callType;
            echo("<p ng-if=\"false\">Please don't scrape me! <a href=\"api/1.0/get/call/?call=$callNum&county=$callCounty&type=$callType\">Use the API</a></p>\n");
        }
        else
        {
            echo("<p ng-if=\"false\">Please don't scrape me! This call doesn't even exist!!!!!!!</p>\n");
        }
        ?>
        <script type="text/javascript">
            var callJSONStr = <?PHP echo("'$callInfo_JSON';\n"); ?>
        </script>
        
        <script type="text/javascript"          src="js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript"          src="js/angular.min.js"></script>
        <script type="text/javascript"          src="js/angular-route.min.js"></script>
        <script type="text/javascript"          src="js/angular-css.min.js"></script>
        <script type="text/javascript"          src="js/re-tree.min.js"></script>
        <script type="text/javascript"          src="js/ua-device-detector.min.js"></script>
        <script type="text/javascript"          src="js/ng-device-detector.min.js"></script>
        <script type="text/javascript"          src="js/leaflet.js"></script>
        <script type="text/javascript"          src="js/Leaflet.label.js"></script>
        <script type='text/javascript'          src="js/call_map.js"></script>
        <script type='text/javascript'          src="js/call.js"></script>
    </body>
</html>
