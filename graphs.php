<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("database.php");
require_once("google.php");
$mode = $_GET['mode'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Graphs</title>

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
                <a href="#menu" class="main-menu"></a>
                Oregon 911 - Graphs
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
                        <h1> Graphs:  </h1>
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


                        <?php if ($mode == 'yearcalls') { ?>
                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        type: 'spline'
                                        },
                                                title: {
                                                text: 'WCCCA/CCOM Calls Per Day'
                                                },
                                                subtitle: {
    <?PHP
    $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
    if ($days > 0 && $days < 360) {
        echo ("text: 'Over a period of " . $days . " days'");
    } else {
        echo("text: 'Over a period of 1 year'");
    }
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
                                                text: 'Calls'
                                                },
                                                        min: 0
                                                },
                                                tooltip: {
                                                headerFormat: '<b>{series.name}</b><br>',
                                                        pointFormat: '{point.x:%e. %b}: {point.y} Calls'
                                                },
                                                series: [{
                                                name: 'WCCCA',
                                                        color: '#C80000',
                                                        // Define the data points. All series have a dummy year
                                                        // of 1970/71 in order to be compared on the same x axis. Note
                                                        // that in JavaScript, months start at 0 for January, 1 for February etc.
    <?PHP
    if ($days > 0 && $days < 360) {
        $sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, county, COUNT(*) AS total FROM `oregon911_cad`.`pdx911_archive` WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < " . $days . " AND county !='M' and county !='MULTCO' GROUP BY DATE_FORMAT(TIMESTAMP, '%d'), DATE_FORMAT(TIMESTAMP, '%m'), DATE_FORMAT(TIMESTAMP, '%Y'), county order by year, month, day, county DESC;";
    } else {
        $sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, county, COUNT(*) AS total FROM `oregon911_cad`.`pdx911_archive` WHERE TIMESTAMPDIFF(YEAR, timestamp, NOW()) < 1 AND county !='M' and county !='MULTCO' GROUP BY DATE_FORMAT(TIMESTAMP, '%d'), DATE_FORMAT(TIMESTAMP, '%m'), DATE_FORMAT(TIMESTAMP, '%Y'), county order by year, month, day, county DESC;";
    }
    $result = $db->sql_query($sql);

    $Woutput = '';
    $Coutput = '';
    while ($row = $result->fetch_assoc()) {
        if ($row['total'] < 0) {
            $AVG_R = 0;
        } else {
            $AVG_R = $row['total'];
        }
        if ($row['county'] == "W") {
            $Woutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
        } elseif ($row['county'] == "C") {
            $Coutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
        }
    }
    ?>
                                                data: [
    <?PHP echo rtrim($Woutput, ","); ?>
                                                ]
                                                }, {
                                                name: 'CCOM',
                                                        color: '#00C800',
                                                        data: [
    <?PHP echo rtrim($Coutput, ","); ?>
                                                        ]
                                                }]
                                        });
                                        });</script>
                            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            <?PHP
                            $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
                            if ($days > 0 && $days < 360) {
                                ?><a href="graphs?mode=yearcalls&days=360">Over the year.</a><?PHP
                            } else {
                                ?><a href="graphs?mode=yearcalls&days=30">Over 30 days.</a><?PHP
                            }
                            ?>
                            <script type="text/javascript" src="https:////code.highcharts.com/3.0.1/highcharts.js"></script>
                            <script src="../../js/modules/exporting.js"></script>
                        <?php } elseif ($mode == 'callvolume') { ?>
                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        zoomType: 'x'
                                        },
                                                title: {
                                                text: 'Call Volumes (Both Counties)'
                                                },
                                                subtitle: {
                                                text: document.ontouchstart === undefined ?
                                                        'Click and drag in the plot area to zoom in' :
                                                        'Pinch the chart to zoom in'
                                                },
                                                xAxis: {
                                                type: 'datetime',
                                                        minRange: 14 * 24 * 3600000 // fourteen days
                                                },
                                                yAxis: {
                                                title: {
                                                text: 'Calls'
                                                }
                                                },
                                                legend: {
                                                enabled: false
                                                },
                                                plotOptions: {
                                                area: {
                                                fillColor: {
                                                linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                                                        stops: [
                                                                [0, Highcharts.getOptions().colors[0]],
                                                                [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                        ]
                                                },
                                                        marker: {
                                                        radius: 2
                                                        },
                                                        lineWidth: 1,
                                                        states: {
                                                        hover: {
                                                        lineWidth: 1
                                                        }
                                                        },
                                                        threshold: null
                                                }
                                                },
                                                series: [{
                                                type: 'area',
                                                        name: 'Calls',
                                                data: [
    <?PHP
    $sql = "SELECT count(*) as Calls, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR FROM `oregon911_cad`.`pdx911_archive` group by DATE(TIMESTAMP) order by TIMESTAMP ASC;";
    $result = $db->sql_query($sql);

    $output = '';
    while ($row = $result->fetch_assoc()) {
        $output .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $row['Calls'] . "   ],";
    }
    echo rtrim($output, ",");
    ?>
                                                        ]
                                                }]
                                        });
                                        });</script>
                            <script type="text/javascript" src="https://code.highcharts.com/3.0.1/highcharts.js"></script>
                            <script src="../../js/modules/exporting.js"></script>

                            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        <?php } elseif ($mode == 'countyaveragetravel') { ?>
                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        type: 'spline'
                                        },
                                                title: {
                                                text: 'WCCCA/CCOM Travel Time'
                                                },
                                                subtitle: {
    <?PHP
    $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
    if ($days > 0 && $days < 360) {
        echo ("text: 'Over a period of " . $days . " days'");
    } else {
        echo("text: 'Over a period of 4 months'");
    }
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
                                                series: [{
                                                name: 'WCCCA',
                                                        color: '#C80000',
                                                        // Define the data points. All series have a dummy year
                                                        // of 1970/71 in order to be compared on the same x axis. Note
                                                        // that in JavaScript, months start at 0 for January, 1 for February etc.
    <?PHP
    if ($days > 0 && $days < 120) {
        $sql = "SELECT `oregon911_cad`.`pdx911_units`.county, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute))/3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))) , 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency != '' AND `oregon911_cad`.`pdx911_units`.onscene != '00:00:00' AND `oregon911_cad`.`pdx911_units`.enroute != '00:00:00' AND TIMESTAMPDIFF(DAY, timestamp, NOW()) < " . $days . " group by `oregon911_cad`.`pdx911_units`.county , DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC;";
    } else {
        $sql = "SELECT `oregon911_cad`.`pdx911_units`.county, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute))/3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))) , 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency != '' AND `oregon911_cad`.`pdx911_units`.onscene != '00:00:00' AND `oregon911_cad`.`pdx911_units`.enroute != '00:00:00' AND TIMESTAMPDIFF(DAY, timestamp, NOW()) < 120 group by `oregon911_cad`.`pdx911_units`.county , DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC;";
    }
    $result = $db->sql_query($sql);

    $Woutput = '';
    $Coutput = '';
    while ($row = $result->fetch_assoc()) {
        if ($row['AVG_R'] < 0) {
            $AVG_R = 0;
        } else {
            $AVG_R = $row['AVG_R'];
        }
        if ($row['county'] == "W") {
            $Woutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
        } elseif ($row['county'] == "C") {
            $Coutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
        }
    }
    ?>
                                                data: [
    <?PHP echo rtrim($Woutput, ","); ?>
                                                ]
                                                }, {
                                                name: 'CCOM',
                                                        color: '#00C800',
                                                        data: [
    <?PHP echo rtrim($Coutput, ","); ?>
                                                        ]
                                                }]
                                        });
                                        });</script>
                            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            <?PHP
                            $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
                            if ($days > 0 && $days < 360) {
                                ?><a href="/graphs?mode=countyaveragetravel&days=360">Over 4 months.</a><?PHP
                            } else {
                                ?><a href="/graphs?mode=countyaveragetravel&days=30">Over 30 days.</a><?PHP
                            }
                            ?>
                            <script type="text/javascript" src="https:////code.highcharts.com/3.0.1/highcharts.js"></script>
                            <script src="../../js/modules/exporting.js"></script>
                        <?php } elseif ($mode == 'averagetravel') { ?>
                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        type: 'spline'
                                        },
                                                title: {
                                                text: 'Agency Travel Time'
                                                },
                                                subtitle: {
    <?PHP
    $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
    if ($days > 0 && $days < 360) {
        echo("text: 'Over a period of " . $days . " days'");
    } else {
        echo("text: 'Over a period of 1 year'");
    }
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
    <?PHP
    $AgenciesList = $db->sql_query("Select DISTINCT agency From `oregon911_cad`.`pdx911_units` WHERE county != 'M' AND county != 'MULTCO' AND agency != ''");
    $output = '';
    while ($agency = $AgenciesList->fetch_assoc()) {
        $output .= ("{ name: '" . $agency['agency'] . "',data: [");
        if ($days > 0 && $days < 360) {
            $sql = "SELECT `oregon911_cad`.`pdx911_units`.agency, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute))/3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))) , 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency = '" . $agency['agency'] . "' AND `oregon911_cad`.`pdx911_units`.onscene != '00:00:00' AND `oregon911_cad`.`pdx911_units`.enroute != '00:00:00' AND TIMESTAMPDIFF(DAY, timestamp, NOW()) < " . $days . " group by `oregon911_cad`.`pdx911_units`.agency , CONCAT(YEAR(timestamp), '/', WEEK(timestamp)) order by 3 ASC;";
        } else {
            $sql = "SELECT `oregon911_cad`.`pdx911_units`.agency, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute))/3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))) , 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency = '" . $agency['agency'] . "' AND `oregon911_cad`.`pdx911_units`.onscene != '00:00:00' AND `oregon911_cad`.`pdx911_units`.enroute != '00:00:00' AND TIMESTAMPDIFF(DAY, timestamp, NOW()) < 360 group by `oregon911_cad`.`pdx911_units`.agency , CONCAT(YEAR(timestamp), '/', WEEK(timestamp)) order by 3 ASC;";
        }
        $internel_output = '';
        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            if ($row['AVG_R'] < 0) {
                $AVG_R = 0;
            } else {
                $AVG_R = $row['AVG_R'];
            }
            $internel_output .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
        }
        $output .= rtrim($internel_output, ",");
        $output .= ("]},");
    }
    ?>

                                        series: [ <?PHP echo(rtrim($output, ",")); ?> ]
                                        });
                                        });</script>
                            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            <?PHP
                            $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
                            if ($days > 0 && $days < 360) {
                                ?><a href="/graphs?mode=averagetravel&days=120">Over 4 months.</a><?PHP
                            } else {
                                ?><a href="/graphs?mode=averagetravel&days=30">Over 30 days.</a><?PHP
                            }
                            ?>
                            <script type="text/javascript" src="https:////code.highcharts.com/3.0.1/highcharts.js"></script>
                            <script src="../../js/modules/exporting.js"></script>
                            <?php
                        } elseif ($mode == 'calltypevolume') {
                            $calltype = htmlspecialchars(strip_tags($db->sql_escape($_GET['calltype'])));
                            ?>



                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        type: 'spline'
                                        },
                                                title: {
                                                text: '<?PHP echo(htmlspecialchars(strip_tags($_GET['calltype']))); ?>'
                                                },
                                                subtitle: {
    <?PHP
    $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
    if ($days > 0 && $days < 360) {
        echo ("text: 'Over a period of " . $days . " days'");
    } else {
        echo("text: 'Over a period of 7 days'");
    }
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
                                                text: 'Call Volume'
                                                },
                                                        min: 0
                                                },
                                                tooltip: {
                                                headerFormat: '<b>{series.name}</b><br>',
                                                        pointFormat: '{point.x:%e. %b}: {point.y} Calls'
                                                },
                                                series: [
    <?PHP
    if ($days > 0 && $days < 360) {
        $sql = "SELECT `oregon911_cad`.`pdx911_units`.county, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, count(`oregon911_cad`.`pdx911_archive`.callSum) as RESULT FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < '" . $days . "' AND callSum = '" . $calltype . "' group by `oregon911_cad`.`pdx911_units`.county , DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC";
    } else {
        $sql = "SELECT `oregon911_cad`.`pdx911_units`.county, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, count(`oregon911_cad`.`pdx911_archive`.callSum) as RESULT FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < '7' AND callSum = '" . $calltype . "' group by `oregon911_cad`.`pdx911_units`.county , DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC";
    }
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        if ($row['county'] == 'W') {
            $Woutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $row['RESULT'] . "   ],";
        } else {
            $Coutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $row['RESULT'] . "   ],";
        }
    }
    ?>
    <?PHP if (!empty($Woutput)) { ?>
                                                    {
                                                    name: 'WCCCA',
                                                            color: '#C80000',
                                                            // Define the data points. All series have a dummy year
                                                            // of 1970/71 in order to be compared on the same x axis. Note
                                                            // that in JavaScript, months start at 0 for January, 1 for February etc.
                                                            data: [
        <?PHP echo rtrim($Woutput, ","); ?>
                                                            ]
                                                    }
    <?PHP } ?>
    <?PHP if (!empty($Coutput)) { ?>
        <?PHP if (!empty($Woutput)) { ?>
                                                        ,
        <?PHP } ?>
                                                    {
                                                    name: 'CCOM',
                                                            color: '#00C800',
                                                            // Define the data points. All series have a dummy year
                                                            // of 1970/71 in order to be compared on the same x axis. Note
                                                            // that in JavaScript, months start at 0 for January, 1 for February etc.
                                                            data: [
        <?PHP echo rtrim($Coutput, ","); ?>
                                                            ]
                                                    }
    <?PHP } ?>
                                                ]
                                        });
                                        });</script>
                            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

                            <form action="#" method="GET">
                                <input type="hidden" name="mode" value="<?PHP echo($_GET['mode']); ?>">
                                <input type="hidden" name="days" value="<?PHP echo($_GET['days']); ?>">
                                <label for="calltype">Call Type:</label>
                                <select name="calltype">
                                    <?PHP
                                    // Create the SQL statement
                                    $sql = "SELECT callSum FROM `oregon911_cad`.`pdx911_archive` WHERE callSum NOT LIKE '%*%' group by callsum order by count(callsum) DESC;";

                                    // Run the query 
                                    $result = $db->sql_query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        if ($_GET['calltype'] == $row['callSum']) {
                                            ?> <option selected="true" value="<?PHP echo($row['callSum']); ?>"><?PHP echo($row['callSum']); ?></option> <?PHP
                                        } else {
                                            ?> <option value="<?PHP echo($row['callSum']); ?>"><?PHP echo($row['callSum']); ?></option> <?PHP
                                        }
                                    }
                                    ?>
                                </select>
                                <input type="submit" value="Submit">
                            </form>

                            <?PHP
                            $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
                            if ($days > 0 && $days < 360) {
                                if ($days == 30) {
                                    ?><a href="/graphs?mode=calltypevolume&days=7&calltype=<?PHP echo($_GET['calltype']); ?>">Over 7 days.</a><?PHP
                                } else {
                                    ?><a href="/graphs?mode=calltypevolume&days=30&calltype=<?PHP echo($_GET['calltype']); ?>">Over 30 days.</a><?PHP
                                }
                            }
                            ?>

                            <script type="text/javascript" src="https:////code.highcharts.com/3.0.1/highcharts.js"></script>
                            <script src="../../js/modules/exporting.js"></script>
                        <?php } elseif ($mode == 'averagetravel') { ?>
                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        type: 'spline'
                                        },
                                                title: {
                                                text: 'Agency Travel Time'
                                                },
                                                subtitle: {
    <?PHP
    $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
    if ($days > 0 && $days < 360) {
        echo("text: 'Over a period of " . $days . " days'");
    } else {
        echo("text: 'Over a period of 1 year'");
    }
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
    <?PHP
    $AgenciesList = $db->sql_query("Select DISTINCT agency From `oregon911_cad`.`pdx911_units` WHERE county != 'M' AND county != 'MULTCO' AND agency != ''");
    $output = '';
    while ($agency = $AgenciesList->fetch_assoc()) {
        $output .= ("{ name: '" . $agency['agency'] . "',data: [");
        if ($days > 0 && $days < 360) {
            $sql = "SELECT `oregon911_cad`.`pdx911_units`.agency, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute))/3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))) , 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency = '" . $agency['agency'] . "' AND `oregon911_cad`.`pdx911_units`.onscene != '00:00:00' AND `oregon911_cad`.`pdx911_units`.enroute != '00:00:00' AND TIMESTAMPDIFF(DAY, timestamp, NOW()) < " . $days . " group by `oregon911_cad`.`pdx911_units`.agency , CONCAT(YEAR(timestamp), '/', WEEK(timestamp)) order by 3 ASC;";
        } else {
            $sql = "SELECT `oregon911_cad`.`pdx911_units`.agency, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute))/3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))) , 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency = '" . $agency['agency'] . "' AND `oregon911_cad`.`pdx911_units`.onscene != '00:00:00' AND `oregon911_cad`.`pdx911_units`.enroute != '00:00:00' AND TIMESTAMPDIFF(DAY, timestamp, NOW()) < 360 group by `oregon911_cad`.`pdx911_units`.agency , CONCAT(YEAR(timestamp), '/', WEEK(timestamp)) order by 3 ASC;";
        }
        $internel_output = '';
        $result = $db->sql_query($sql);
        while ($row = $result->fetch_assoc()) {
            if ($row['AVG_R'] < 0) {
                $AVG_R = 0;
            } else {
                $AVG_R = $row['AVG_R'];
            }
            $internel_output .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $AVG_R . "   ],";
        }
        $output .= rtrim($internel_output, ",");
        $output .= ("]},");
    }
    ?>

                                        series: [ <?PHP echo(rtrim($output, ",")); ?> ]
                                        });
                                        });</script>
                        <?PHP } elseif ($mode == 'accidinjnoninj') {
                            ?>
                            <script type="text/javascript">
                                        $(function () {
                                        $('#container').highcharts({
                                        chart: {
                                        type: 'spline'
                                        },
                                                title: {
                                                text: 'Car Accidents Injury/Non Injury'
                                                },
                                                subtitle: {
    <?PHP
    $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));
    if ($days > 0 && $days < 360) {
        echo ("text: 'Over a period of " . $days . " days'");
    } else {
        echo("text: 'Over a period of 7 days'");
    }
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
                                                text: 'Call Volume'
                                                },
                                                        min: 0
                                                },
                                                tooltip: {
                                                headerFormat: '<b>{series.name}</b><br>',
                                                        pointFormat: '{point.x:%e. %b}: {point.y} Calls'
                                                },
                                                series: [
    <?PHP
    if ($days > 0 && $days < 360) {
        $sql = "SELECT `oregon911_cad`.`pdx911_units`.county, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, count(*) as RESULT FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < '" . $days . "' AND (callSum = 'TRF ACC, UNK INJ' OR callSum = 'TRF ACC, INJURY' OR callSum = 'TAU' OR callSum = 'MVA-INJURY ACCID' OR callSum = 'TRF ACC, NON-INJ' OR callSum = 'TAI-TRAPPED VICT' OR callSum = 'TAI-HIGH MECHANI' OR callSum = 'TAI-PT NOT ALERT' OR callSum = 'MVA-UNK INJURY') group by `oregon911_cad`.`pdx911_units`.county , DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC";
    } else {
        $sql = "SELECT `oregon911_cad`.`pdx911_units`.county, DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, count(*) as RESULT FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < '7' AND (callSum = 'TRF ACC, UNK INJ' OR callSum = 'TRF ACC, INJURY' OR callSum = 'TAU' OR  callSum = 'MVA-INJURY ACCID' OR callSum = 'TRF ACC, NON-INJ' OR callSum = 'TAI-TRAPPED VICT' OR callSum = 'TAI-HIGH MECHANI' OR callSum = 'TAI-PT NOT ALERT' OR callSum = 'MVA-UNK INJURY') group by `oregon911_cad`.`pdx911_units`.county , DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC";
    }
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        if ($row['county'] == 'W') {
            $Woutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $row['RESULT'] . "   ],";
        } else {
            $Coutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $row['RESULT'] . "   ],";
        }
    }
    if ($days > 0 && $days < 360) {
        $sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, count(*) as RESULT FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < '" . $days . "' AND (callSum = 'TRF ACC, UNK INJ' OR callSum = 'TRF ACC, INJURY' OR callSum = 'MVA-INJURY ACCID' OR callSum = 'TAU' OR callSum = 'TRF ACC, NON-INJ' OR callSum = 'TAI-TRAPPED VICT' OR callSum = 'TAI-HIGH MECHANI' OR callSum = 'TAI-PT NOT ALERT' OR callSum = 'MVA-UNK INJURY') group by DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC";
    } else {
        $sql = "SELECT DATE_FORMAT(TIMESTAMP, '%d') AS DAY, DATE_FORMAT(TIMESTAMP, '%m') AS MONTH, DATE_FORMAT(TIMESTAMP, '%Y') AS YEAR, count(*) as RESULT FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE TIMESTAMPDIFF(DAY, timestamp, NOW()) < '7' AND (callSum = 'TRF ACC, UNK INJ' OR callSum = 'TRF ACC, INJURY' OR callSum = 'MVA-INJURY ACCID' OR callSum = 'TAU' OR callSum = 'TRF ACC, NON-INJ' OR callSum = 'TAI-TRAPPED VICT' OR callSum = 'TAI-HIGH MECHANI' OR callSum = 'TAI-PT NOT ALERT' OR callSum = 'MVA-UNK INJURY') group by DATE_FORMAT(TIMESTAMP, '%d') , DATE_FORMAT(TIMESTAMP, '%m') , DATE_FORMAT(TIMESTAMP, '%Y') order by TIMESTAMP DESC";
    }
    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        $Toutput .= "[Date.UTC(" . $row['YEAR'] . ",  " . ($row['MONTH'] - 1) . ", " . $row['DAY'] . "), " . $row['RESULT'] . "   ],";
    }
    ?>
    <?PHP if (!empty($Woutput)) { ?>
                                                    {
                                                    name: 'WCCCA',
                                                            color: '#C80000',
                                                            // Define the data points. All series have a dummy year
                                                            // of 1970/71 in order to be compared on the same x axis. Note
                                                            // that in JavaScript, months start at 0 for January, 1 for February etc.
                                                            data: [
        <?PHP echo rtrim($Woutput, ","); ?>
                                                            ]
                                                    }
    <?PHP } ?>
    <?PHP if (!empty($Coutput)) { ?>
        <?PHP if (!empty($Woutput)) { ?>
                                                        ,
        <?PHP } ?>
                                                    {
                                                    name: 'CCOM',
                                                            color: '#00C800',
                                                            // Define the data points. All series have a dummy year
                                                            // of 1970/71 in order to be compared on the same x axis. Note
                                                            // that in JavaScript, months start at 0 for January, 1 for February etc.
                                                            data: [
        <?PHP echo rtrim($Coutput, ","); ?>
                                                            ]
                                                    }
    <?PHP } ?>, {
                                                name: 'Total',
                                                        // Define the data points. All series have a dummy year
                                                        // of 1970/71 in order to be compared on the same x axis. Note
                                                        // that in JavaScript, months start at 0 for January, 1 for February etc.
                                                        data: [
    <?PHP echo rtrim($Toutput, ","); ?>
                                                        ]
                                                }
                                                ]
                                        });
                                        });</script>
                            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

                            <?PHP
                            $days = $db->sql_escape(htmlspecialchars(strip_tags($_GET['days'])));

                            if ($days > 0 && $days < 360) {
                                if ($days == 30) {
                                    ?><a href="/graphs?mode=accidinjnoninj&days=7">Over 7 days.</a><?PHP
                                } else {
                                    ?><a href="/graphs?mode=accidinjnoninj&days=30">Over 30 days.</a><?PHP
                                }
                            } else {
                                ?><a href="/graphs?mode=accidinjnoninj&days=30">Over 30 days.</a><?PHP
                            }
                            ?>

                            <script type="text/javascript" src="https:////code.highcharts.com/3.0.1/highcharts.js"></script>
                            <script src="../../js/modules/exporting.js"></script>
                        <?php } else { ?>
                            <div class="container">
                                <p class="lead">404. Page Not Found :(</p>
                            </div><!-- /.container -->

                            <?php
                        }
?>

                        <!--====================================================================================== -->
                        <?PHP
                        if (!$mobile) {
                            ?>
                        </div>

                        <?PHP
                    }
                    ?>
                </div>
            </div>

            <?PHP include ("./inc/nav.php");
            ?>

        </div>
        <script type = "text/javascript">
                                        $(function () {
                                        $('nav#menu').mmenu();
                                        });</script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
    </body>
</html>
<?PHP

/**
 * mb_stripos all occurences
 * based on https://www.php.net/manual/en/function.strpos.php#87061
 *
 * Find all occurrences of a needle in a haystack
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
 */
function mb_stripos_all($haystack, $needle) {

    $s = 0;
    $i = 0;

    while (is_integer($i)) {

        $i = mb_stripos($haystack, $needle, $s);

        if (is_integer($i)) {
            $aStrPos[] = $i;
            $s = $i + mb_strlen($needle);
        }
    }

    if (isset($aStrPos)) {
        return $aStrPos;
    } else {
        return false;
    }
}

/**
 * Apply highlight to row label
 *
 * @param string $a_json json data
 * @param array $parts strings to search
 * @return array
 */
function apply_highlight($a_json, $parts) {

    $p = count($parts);
    $rows = count($a_json);

    for ($row = 0; $row < $rows; $row++) {

        $label = $a_json[$row]["label"];
        $a_label_match = array();

        for ($i = 0; $i < $p; $i++) {

            $part_len = mb_strlen($parts[$i]);
            $a_match_start = mb_stripos_all($label, $parts[$i]);

            foreach ($a_match_start as $part_pos) {

                $overlap = false;
                foreach ($a_label_match as $pos => $len) {
                    if ($part_pos - $pos >= 0 && $part_pos - $pos < $len) {
                        $overlap = true;
                        break;
                    }
                }
                if (!$overlap) {
                    $a_label_match[$part_pos] = $part_len;
                }
            }
        }

        if (count($a_label_match) > 0) {
            ksort($a_label_match);

            $label_highlight = '';
            $start = 0;
            $label_len = mb_strlen($label);

            foreach ($a_label_match as $pos => $len) {
                if ($pos - $start > 0) {
                    $no_highlight = mb_substr($label, $start, $pos - $start);
                    $label_highlight .= $no_highlight;
                }
                $highlight = '<span class="hl_results">' . mb_substr($label, $pos, $len) . '</span>';
                $label_highlight .= $highlight;
                $start = $pos + $len;
            }

            if ($label_len - $start > 0) {
                $no_highlight = mb_substr($label, $start);
                $label_highlight .= $no_highlight;
            }

            $a_json[$row]["label"] = $label_highlight;
        }
    }

    return $a_json;
}
?>
