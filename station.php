<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("loggedin.php");
require_once("google.php");

$county = strtoupper($db->sql_escape(strip_tags($_GET['county'])));
$station = strtoupper($db->sql_escape(strip_tags($_GET['station'])));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Station</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <style>
            html, body { height:100%; }
        </style>


    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu"></a>
                Oregon 911 - Station
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
                        <h1> Station: <?PHP echo($station); ?> </h1>
                        <center>
                            <?PHP
                            if (!$mobile) {
                                echo($ad_336_280);
                            } else {
                                echo($ad_320_100);
                            }
                            ?>
                        </center>
                        <!-- ================== This is where the main stuff happens ================= -->
                        <!-- Specifying an 'open' attribute will make all the content visible when the page loads -->

                        <p> Data is limited to 1 year, this page is still in active development and may contain bugs/inaccuracies! See one? Report it!</p>
                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Agency <?php
                                echo ($station);
                                ?></summary>

                            <table>                          
                                <tr>
                                    <th scope="row">Calls (to date)</th>
                                    <td><?php
                                        $sql = "Select count(DISTINCT(U.GUID)) as count from `oregon911_cad`.pdx911_units AS U LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.station = S.ABBV and U.county = S.county WHERE S.ABBV = '" . $station . "' and U.county = '" . $county . "'";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        echo ($rows['count']);
                                        ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Station Response Time</th>
                                    <?PHP
                                    $sql = "SELECT ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND S.ABBV = '" . $station . "' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "'";
                                    $result = $db->sql_query($sql);
                                    $rows = $db->sql_fetchrow($result);
                                    echo("<td>" . $rows['AVG_R'] . " Minutes</td>");
                                    ?>
                                </tr>
                                <tr>
                                    <th scope="row">Average Call Distance</th>
                                    <td><?php
                                        $sql = "Select ROUND(AVG(geodistance(S.LAT, S.LON, C.lat, C.lon) * 0.000621371), 2) as miles from `oregon911_cad`.pdx911_units as U, `oregon911_cad`.pdx911_archive as C, `oregon911_cad`.`pdx911_stations` as S WHERE S.ABBV = '" . $station . "' and C.county = '" . $county . "' AND U.GUID = C.GUID AND U.county = C.county AND S.abbv = U.station AND U.onscene != '00:00:00' AND U.enroute != '00:00:00';";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        echo($rows['miles'] . " Miles");
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
                                    <th scope="row">Agency</th>
                                    <td><?php
                                        $sql = "SELECT S.station, U.abbv, S.DISTRICT as agency FROM oregon911_cad.pdx911_unit_table As U LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.ABBV = S.ABBV and U.county = S.county WHERE S.ABBV = '" . $station . "' and U.county = '" . $county . "'";
                                        $result = $db->sql_query($sql);
                                        $rows = $db->sql_fetchrow($result);
                                        $agency = $rows['agency'];
                                        if ($rows['station'] == null) {
                                            echo ("Unknown");
                                        } else {
                                            echo ("<a href=\"http://cad.oregon911.net/agency?agency=" . rawurlencode($rows['agency']) . "&county=" . $county . "\">" . $rows['agency'] . "</a>");
                                        }
                                        ?></td>
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
                                    $sql = "Select S.ABBV as station, U.GUID from `oregon911_cad`.pdx911_calls AS PC JOIN `oregon911_cad`.pdx911_units AS U ON U.GUID = PC.GUID AND U.county = PC.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON U.station = S.ABBV and PC.county = S.county WHERE S.ABBV = '" . $station . "' and PC.county = '" . $county . "';";
                                    $result = $db->sql_query($sql);
                                    $rows = $db->sql_fetchrow($result);
                                    if ($rows['station'] == $station) {
                                        echo ("<td style=\"color:green;\">YES <a href=\"/call?call=" . $rows['GUID'] . "&county=" . $county . "\">(" . $rows['GUID'] . ")</a></p>");
                                    } else {
                                        echo ('<td style="color:red;">NO</p>');
                                    }
                                    ?></td>
                            </table>

                        </details>

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Station Image</summary>
                            <p> Not available at this time </p>
                        </details>   

                        <details <?PHP
                        if ($county == "W") {
                            echo ('class="wccca-details"');
                        } else {
                            echo ('class="ccom-details"');
                        }
                        ?> open=true>
                            <summary>Available Units</summary>
                            <?php
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Unit</th></tr>';
                            $sql = "select * from oregon911_cad.`pdx911_unit_table` AS S WHERE S.ABBV = '" . $station . "' AND S.County = '" . $county . "';";
                            $result = $db->sql_query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr><th><a href="http://cad.oregon911.net/unitinfo?unit=' . $row['UNIT'] . '&county=' . $county . '">' . $row['UNIT'] . '</a></th></tr>';
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
                            <summary>Heat Map</summary>
                            <div id="heatmap" style="width:100%; height:300px;"></div>
                        </details>   

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
                            <summary>Top 5 Call Types</summary>
                            <?php
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Call Type</th><th>Count</th></tr>';
                            $sql = "SELECT callsum, count(callsum) as count FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND S.ABBV = '" . $station . "' group by callsum order by 2 DESC limit 5";
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
                                <?PHP
                                if (!$mobile) {
                                    echo($ad_336_280);
                                } else {
                                    echo($ad_320_100);
                                }
                                ?>
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
                            $sql = "SELECT `oregon911_cad`.`pdx911_units`.GUID, callsum, count(unit) FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND '" . $station . "' = (select X.ABBV from `oregon911_cad`.`pdx911_units` AS P LEFT JOIN `oregon911_cad`.`pdx911_stations` AS X ON P.station = X.ABBV and P.county = X.county where `oregon911_cad`.`pdx911_units`.GUID = P.GUID and `oregon911_cad`.`pdx911_units`.county = P.county and X.ABBV = '" . $station . "' limit 1) group by GUID order by 3 DESC limit 3";
                            $result = $db->sql_query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr><th><a href=\"/call?call=" . $row['GUID'] . "&county=" . $county . "\">" . $row['GUID'] . "</a></th><th>" . $row['callsum'] . "</th><th>" . $row['count(unit)'] . "</th></tr>";
                            }
                            echo '</table>';
                            ?>
                        </details>   

                        <?PHP
                        if (!$mobile) {
                            ?>
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
                                $sql = "SELECT `oregon911_cad`.`pdx911_units`.GUID, callsum, timestamp FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND S.ABBV = '" . $station . "' order by timestamp DESC limit 3";
                                $result = $db->sql_query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr><th><a href=\"/call?call=" . $row['GUID'] . "&county=" . $county . "\">" . $row['GUID'] . "</a></th><th>" . $row['callsum'] . "</th><th>" . $row['timestamp'] . "</th></tr>";
                                }
                                echo '</table>';
                                ?>
                            </details>   
                            <?PHP
                        } else {
                            ?>
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
                                echo '<tr><th>Call Type</th><th>Date/Time</th></tr>';
                                $sql = "SELECT `oregon911_cad`.`pdx911_units`.GUID, callsum, timestamp FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND S.ABBV = '" . $station . "' order by timestamp DESC limit 3";
                                $result = $db->sql_query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr><th><a href=\"/call?call=" . $row['GUID'] . "&county=" . $county . "\">" . $row['callsum'] . "</a></th><th>" . $row['timestamp'] . "</th></tr>";
                                }
                                echo '</table>';
                                ?>
                            </details>   
                            <?PHP
                        }
                        ?>

                        <!-- ====================================================================================== -->
                        <?php
                        $time = microtime();
                        $time = explode(' ', $time);
                        $time = $time[1] + $time[0];
                        $finish = $time;
                        $total_time = round(($finish - $start), 4);
                        echo '<p>Page generated in ' . $total_time . ' seconds.</p>';
                        echo '<p>Copyright &copy; ' . date("Y") . ' Brandan Lasley. All Rights Reserved.</p>';
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
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
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
$sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND S.ABBV = '" . $station . "' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND TIMESTAMPDIFF(YEAR, timestamp, NOW()) < 1 GROUP BY DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%y') , `oregon911_cad`.`pdx911_units`.county order by year , month , day , `oregon911_cad`.`pdx911_units`.county DESC;";
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
                            name: '<?PHP echo($station); ?>',
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
        <script>
<?php
$sql = "SELECT `oregon911_cad`.`pdx911_archive`.LAT, `oregon911_cad`.`pdx911_archive`.LON FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $county . "' AND S.ABBV = '" . $station . "' AND TIMESTAMPDIFF(YEAR, timestamp, NOW()) < 1";
$result = $db->sql_query($sql);
$GeoOutput = '';

while ($row = $result->fetch_assoc()) {
    $GeoOutput .= "[" . $row['LAT'] . "," . $row['LON'] . "],";
}

echo "var latLongHeat = [" . rtrim($GeoOutput, ',') . "];";
?>


            function initialize() {
            $("#map-canvas").height(screen.height / 2);
                    $("#map-canvas").width(screen.width / 2);
                    // TODO: center should be dynamically calculated via agency data, zoom too

                    var mapOptions = {
                    zoom: 10,
                            center: new google.maps.LatLng(45.4461, - 122.6392),
                            mapTypeId: google.maps.MapTypeId.MAP
                    };
                    map = new google.maps.Map(document.getElementById('heatmap'),
                            mapOptions);
                    var Data = [];
                    var mapData = latLongHeat; // JSON.parse is extra work

                    for (var i = 0; i < mapData.length; i++) {
            var t = new google.maps.LatLng(mapData[i][0], mapData[i][1]);
                    Data[i] = t;
            }

            var pointArray = new google.maps.MVCArray(Data);
                    heatmap = new google.maps.visualization.HeatmapLayer({
                    data: pointArray
                    });
                    heatmap.setMap(map);
            }

            function toggleHeatmap() {
            heatmap.setMap(heatmap.getMap() ? null : map);
            }

            function changeGradient() {
            var gradient = [
                    'rgba(0, 255, 255, 0)',
                    'rgba(0, 255, 255, 1)',
                    'rgba(0, 191, 255, 1)',
                    'rgba(0, 127, 255, 1)',
                    'rgba(0, 63, 255, 1)',
                    'rgba(0, 0, 255, 1)',
                    'rgba(0, 0, 223, 1)',
                    'rgba(0, 0, 191, 1)',
                    'rgba(0, 0, 159, 1)',
                    'rgba(0, 0, 127, 1)',
                    'rgba(63, 0, 91, 1)',
                    'rgba(127, 0, 63, 1)',
                    'rgba(191, 0, 31, 1)',
                    'rgba(255, 0, 0, 1)'
            ]
                    heatmap.set('gradient', heatmap.get('gradient') ? null : gradient);
            }

            function changeRadius() {
            heatmap.set('radius', heatmap.get('radius') ? null : 20);
            }

            function changeOpacity() {
            heatmap.set('opacity', heatmap.get('opacity') ? null : 0.2);
            }

            google.maps.event.addDomListener(window, 'load', initialize);</script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
    </body>
</html>