<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("database.php");
require_once("google.php");

$county = strtoupper($db->sql_escape(strip_tags($_GET['county'])));
$agency = strtoupper($db->sql_escape(strip_tags($_GET['agency'])));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Agency</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu" class="main-menu"></a>
                Oregon 911 - Agency
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
                <br>
                <br>
                <div id="alert">
                    <a class="alert" href="#alert"><?PHP echo($row['Message']); ?></a>
                </div>
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
                        <h1> Agency: <?PHP echo($agency); ?> </h1>

                        <b> Data is limited to 1 year, this page is still in active development and may contain bugs/inaccuracies! See one? Report it!</b>
                        <!-- ================== This is where the main stuff happens ================= -->
                        <!-- Specifying an 'open' attribute will make all the content visible when the page loads -->

                        <center>
                            <?PHP
                            if (!$mobile) {
                                echo($ad_336_280);
                            } else {
                                echo($ad_320_100);
                            }
                            ?>
                        </center>

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Agency <?php
                                echo ($agency);
                                ?></summary>

                            <table>                          
                                <tr>
                                    <th scope="row">Calls (to date)</th>
                                    <td><?php
                                        $sql = "Select count(DISTINCT(U.GUID)) as count from `oregon911_cad`.pdx911_units AS U LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.station = S.ABBV and U.county = S.county WHERE S.DISTRICT = '" . $agency . "' and U.county = '" . $county . "'";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        echo ($rows['count']);
                                        ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Agency Response Time</th>
                                    <?PHP
                                    $sql = "SELECT ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND S.DISTRICT = '" . $agency . "' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "'";
                                    $result = $db->sql_query($sql);
                                    $rows = $db->sql_fetchrow($result);
                                    echo("<td>" . $rows['AVG_R'] . " Minutes</td>");
                                    ?>
                                </tr>
                                <!--<tr>
                                    <th scope="row">Average Call Distance</th>
                                    <td><?php
                                       /* $sql = "Select ROUND(AVG(geodistance(S.LAT, S.LON, C.lat, C.lon) * 0.000621371), 2) as miles from `oregon911_cad`.pdx911_units as U, `oregon911_cad`.pdx911_archive as C, `oregon911_cad`.`pdx911_stations` as S WHERE S.DISTRICT = '" . $agency . "' and C.county = '" . $county . "' AND U.GUID = C.GUID AND U.county = C.county AND S.abbv = U.station AND U.onscene != '00:00:00' AND U.enroute != '00:00:00';";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        echo($rows['miles'] . " Miles");*/
                                        ?></td>
                                </tr>-->
                                <tr>
                                    <th scope="row">Number of stations</th>
                                    <td><?php
                                        $sql = "SELECT count(STATION) as count FROM oregon911_cad.pdx911_stations WHERE DISTRICT = '" . $agency . "' AND county = '" . $county . "'";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        echo($rows['count']);
                                        ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Number of Vehicles</th>
                                    <td><?php
                                        $sql = "SELECT count(unit) count FROM oregon911_cad.pdx911_unit_table WHERE agency = '" . $agency . "' AND county = '" . $county . "'";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        echo($rows['count']);
                                        ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Avg county Response Time</th>
                                    <?PHP
                                    $sql = "SELECT ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "'";
                                    $result = $db->sql_query($sql);
                                    $rows = $db->sql_fetchrow($result);
                                    echo("<td>" . $rows['AVG_R'] . " Minutes</td>");
                                    ?>
                                </tr>
                                <tr>
                                    <th scope="row">County</th>
                                    <td><?php
                                        if ($county == "W") {
                                            echo("Washington");
                                        } elseif ($county == "C") {
                                            echo("Clackamas");
                                        } else {
                                            echo("Unknown");
                                        }
                                        ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Currently On A Call</th>
                                    <?php
                                    $sql = "Select S.DISTRICT as agency, U.GUID from `oregon911_cad`.pdx911_calls AS PC JOIN `oregon911_cad`.pdx911_units AS U ON U.GUID = PC.GUID AND U.county = PC.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.station = S.ABBV and PC.county = S.county WHERE S.DISTRICT = '" . $agency . "' and PC.county = '" . $county . "';";
                                    $result = $db->sql_query($sql);
                                    $rows = $db->sql_fetchrow($result);
                                    if ($rows['agency'] == $agency) {
                                        echo ("<td style=\"color:green;\">YES <a href=\"/units?call=" . $rows['GUID'] . "&county=" . $county . "\">(" . $rows['GUID'] . ")</a></p>");
                                    } else {
                                        echo ('<td style="color:red;">NO</p>');
                                    }
                                    ?></td>
                            </table>

                        </details>

                        <!--  <details <?PHP
                        /*if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }*/
                        ?> open=true>
                          <summary>Heat Map</summary>
                            <div id="heatmap" style="width:100%; height:300px;"></div>
                        </details>   -->

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Average Response Time</summary>
                            <div id="yearavgunit" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        </details>
                        
                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Stations</summary>
                            <?php
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Station #:</th><th>Abbreviation</th><th>Address</th><th>City</th></tr>';
                            $sql = "SELECT * FROM oregon911_cad.pdx911_stations WHERE DISTRICT = '$agency' order by CITY asc;";
                            $result = $db->sql_query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr><th><a href="/station?station=' . $row['ABBV'] . '&county=' . $row['COUNTY'] . '">' . $row['STATION'] . '</a></th><th>' . $row['ABBV'] . '</th><th>' . $row['ADDRESS'] . '</th><th>' . $row['CITY'] . '</th></tr>';
                            }
                            echo '</table>';
                            ?>
                        </details>  

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Top 5 Call Types</summary>
                            <?php
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Call Type</th><th>Count</th></tr>';
                            $sql = "SELECT callsum, count(callsum) as count FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND S.DISTRICT = '" . $agency . "' group by callsum order by 2 DESC limit 5";
                            $result = $db->sql_query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr><th>' . $row['callsum'] . '</th><th>' . $row['count'] . '</th></tr>';
                            }
                            echo '</table>';
                            ?>
                        </details>  

                        <details <?php
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open='true'>
                            <summary>Ad</summary>
                            <center>
                                <?PHP echo($ad_336_280); ?>
                            </center>
                        </details>

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Top 3 Largest Calls</summary>
                            <?php
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Call Number</th><th>Call Type</th><th>Units</th></tr>';
                            $sql = "SELECT `oregon911_cad`.`pdx911_units`.GUID, callsum, count(unit) FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND '" . $agency . "' = (select X.DISTRICT from `oregon911_cad`.`pdx911_units` AS P LEFT JOIN `oregon911_cad`.`pdx911_stations` AS X ON P.station = X.ABBV and P.county = X.county where `oregon911_cad`.`pdx911_units`.GUID = P.GUID and `oregon911_cad`.`pdx911_units`.county = P.county and X.DISTRICT = '" . $agency . "' limit 1) group by GUID order by 3 DESC limit 3";
                            $result = $db->sql_query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr><th><a href=\"/units?call=" . $row['GUID'] . "&county=" . $county . "\">" . $row['GUID'] . "</a></th><th>" . $row['callsum'] . "</th><th>" . $row['count(unit)'] . "</th></tr>";
                            }
                            echo '</table>';
                            ?>
                        </details>   

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Last 3 Calls</summary>
                            <?php
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Call Number</th><th>Call Type</th><th>Date/Time</th></tr>';
                            $sql = "SELECT `oregon911_cad`.`pdx911_units`.GUID, callsum, timestamp FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND S.DISTRICT = '" . $agency . "' order by timestamp DESC limit 3";
                            $result = $db->sql_query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr><th><a href=\"/units?call=" . $row['GUID'] . "&county=" . $county . "\">" . $row['GUID'] . "</a></th><th>" . $row['callsum'] . "</th><th>" . $row['timestamp'] . "</th></tr>";
                            }
                            echo '</table>';
                            ?>
                        </details>   

                        <!-- ====================================================================================== -->
                        <?PHP
                        if (!$mobile) {
                            ?>
                        </div>

                    <?PHP }
                     echo '<p>Copyright &copy; ' . date("Y") . ' Oregon 911. All Rights Reserved.</p>';?>
                </div>
            </div>

            <?PHP include ("./inc/nav.php"); ?>

        </div>
        <script type = "text/javascript">
                    $(function () {
                    $('nav#menu').mmenu();
                    });</script>
        <script src="/js/highcharts.js"></script>
        <script src="/js/modules/exporting.js"></script>
        <script type="text/javascript">
                    $(function () {
                    $('#yearavgunit').highcharts({
                    chart: {
                    type: 'spline'
                    },
                            title: {
                            text: 'Response Time'
                            },
                            subtitle: {
<?PHP
echo("text: 'Over a period of 1 year'");
?>
                            },
                            xAxis: {
                            type: 'datetime',
                                    dateTimeLabelFormats: { // don't display the dummy year
                                    month: '%e. %b',
                                            year: '%b'
                                    },
                                    title: {
                                    text: 'Date'
                                    }
                            },
                            yAxis: {
                            title: {
                            text: 'Minutes'
                            },
                                    min: 0
                            },
                            tooltip: {
                            headerFormat: '<b>{series.name}</b><br>',
                                    pointFormat: '{point.x:%e. %b}: {point.y} Minutes'
                            },
                            series: [
<?PHP
$sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND S.DISTRICT = '" . $agency . "' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND TIMESTAMPDIFF(YEAR, timestamp, NOW()) < 1 GROUP BY DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%y') , `oregon911_cad`.`pdx911_units`.county order by year , month , day , `oregon911_cad`.`pdx911_units`.county DESC;";
$result = $db->sql_query($sql);

while ($row = $result->fetch_assoc()) {
    if ($row['AVG_R'] < 0) {
        $AVG_R = 0;
    } else {
        $AVG_R = $row['AVG_R'];
    }
    $Aoutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
}

$sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND TIMESTAMPDIFF(YEAR, timestamp, NOW()) < 1 GROUP BY DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%y') , `oregon911_cad`.`pdx911_units`.county order by year , month , day , `oregon911_cad`.`pdx911_units`.county DESC;";
$result = $db->sql_query($sql);

while ($row = $result->fetch_assoc()) {
    if ($row['AVG_R'] < 0) {
        $AVG_R = 0;
    } else {
        $AVG_R = $row['AVG_R'];
    }
    $Coutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
}
?>
                            {
                            name: '<?PHP echo($agency); ?>',
                                    data: [
<?PHP echo rtrim($Aoutput, ","); ?>
                                    ]
                            }, {
                            name: '<?PHP echo("County"); ?>',
<?PHP
if ($county == "C") {
    echo ("color: '#00C800',");
} else {
    echo("color: '#C80000',");
}
?>
                            data: [
<?PHP echo rtrim($Coutput, ","); ?>
                            ]
                            }]
                    });
                    });</script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
    </body>
</html>