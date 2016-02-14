<?php
require_once("database.php");
require_once("google.php");
$SQL_TABLE = "SELECT callSum, COUNT(*) as 'Calls' FROM oregon911_cad.pdx911_archive WHERE (timestamp BETWEEN '$year-07-4 00:01:00' AND '$year-07-5 02:00:00') AND callSum IN ('MISC FIRE','RESIDENTIAL FIRE','COMMERCIAL FIRE', 'CAR FIRE', 'ELECTRICAL FIRE', 'UNKNOWN TYP FIRE', 'BRUSH FIRE', 'CHIMNEY FIRE', 'BARKDUST FIRE', 'GRASS FIRE', 'TREE FIRE', 'BARN FIRE', 'POLE FIRE', 'TRUCK FIRE', 'TRUCK FIRE,LARGE', 'FIREWORKS', 'DUMPSTER FIRE', 'BOAT FIRE', 'MOTOR ASST, FIRE') GROUP BY callSum ORDER BY 2 DESC;";
$SQL_TOTAL = "SELECT COUNT(*) as 'Total' FROM oregon911_cad.pdx911_archive WHERE (timestamp BETWEEN '$year-07-4 00:01:00' AND '$year-07-5 02:00:00') AND callSum IN ('MISC FIRE','RESIDENTIAL FIRE','COMMERCIAL FIRE', 'CAR FIRE', 'ELECTRICAL FIRE', 'UNKNOWN TYP FIRE', 'BRUSH FIRE', 'CHIMNEY FIRE', 'BARKDUST FIRE', 'GRASS FIRE', 'TREE FIRE', 'BARN FIRE', 'POLE FIRE', 'TRUCK FIRE', 'TRUCK FIRE,LARGE', 'FIREWORKS', 'DUMPSTER FIRE', 'BOAT FIRE', 'MOTOR ASST, FIRE');";

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - July Fourth</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <style>
            html, body { height:100%; }
            .meter {
                height: 20px;  /* Can be anything */
                position: relative;
                margin: 60px 0 20px 0; /* Just for demo spacing */
                background: #555;
                -moz-border-radius: 25px;
                -webkit-border-radius: 25px;
                border-radius: 25px;
                padding: 10px;
                -webkit-box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
                -moz-box-shadow   : inset 0 -1px 1px rgba(255,255,255,0.3);
                box-shadow        : inset 0 -1px 1px rgba(255,255,255,0.3);
            }
            .meter > span {
                display: block;
                height: 100%;
                -webkit-border-top-right-radius: 8px;
                -webkit-border-bottom-right-radius: 8px;
                -moz-border-radius-topright: 8px;
                -moz-border-radius-bottomright: 8px;
                border-top-right-radius: 8px;
                border-bottom-right-radius: 8px;
                -webkit-border-top-left-radius: 20px;
                -webkit-border-bottom-left-radius: 20px;
                -moz-border-radius-topleft: 20px;
                -moz-border-radius-bottomleft: 20px;
                border-top-left-radius: 20px;
                border-bottom-left-radius: 20px;
                background-color: rgb(43,194,83);
                background-image: -webkit-gradient(
                    linear,
                    left bottom,
                    left top,
                    color-stop(0, rgb(43,194,83)),
                    color-stop(1, rgb(84,240,84))
                    );
                background-image: -moz-linear-gradient(
                    center bottom,
                    rgb(43,194,83) 37%,
                    rgb(84,240,84) 69%
                    );
                -webkit-box-shadow:
                    inset 0 2px 9px  rgba(255,255,255,0.3),
                    inset 0 -2px 6px rgba(0,0,0,0.4);
                -moz-box-shadow:
                    inset 0 2px 9px  rgba(255,255,255,0.3),
                    inset 0 -2px 6px rgba(0,0,0,0.4);
                box-shadow:
                    inset 0 2px 9px  rgba(255,255,255,0.3),
                    inset 0 -2px 6px rgba(0,0,0,0.4);
                position: relative;
                overflow: hidden;
            }
            .meter > span:after, .animate > span > span {
                content: "";
                position: absolute;
                top: 0; left: 0; bottom: 0; right: 0;
                background-image:
                    -webkit-gradient(linear, 0 0, 100% 100%,
                    color-stop(.25, rgba(255, 255, 255, .2)),
                    color-stop(.25, transparent), color-stop(.5, transparent),
                    color-stop(.5, rgba(255, 255, 255, .2)),
                    color-stop(.75, rgba(255, 255, 255, .2)),
                    color-stop(.75, transparent), to(transparent)
                    );
                background-image:
                    -moz-linear-gradient(
                    -45deg,
                    rgba(255, 255, 255, .2) 25%,
                    transparent 25%,
                    transparent 50%,
                    rgba(255, 255, 255, .2) 50%,
                    rgba(255, 255, 255, .2) 75%,
                    transparent 75%,
                    transparent
                    );
                z-index: 1;
                -webkit-background-size: 50px 50px;
                -moz-background-size: 50px 50px;
                background-size: 50px 50px;
                -webkit-animation: move 2s linear infinite;
                -moz-animation: move 2s linear infinite;
                -webkit-border-top-right-radius: 8px;
                -webkit-border-bottom-right-radius: 8px;
                -moz-border-radius-topright: 8px;
                -moz-border-radius-bottomright: 8px;
                border-top-right-radius: 8px;
                border-bottom-right-radius: 8px;
                -webkit-border-top-left-radius: 20px;
                -webkit-border-bottom-left-radius: 20px;
                -moz-border-radius-topleft: 20px;
                -moz-border-radius-bottomleft: 20px;
                border-top-left-radius: 20px;
                border-bottom-left-radius: 20px;
                overflow: hidden;
            }

            .animate > span:after {
                display: none;
            }

            @-webkit-keyframes move {
                0% {
                    background-position: 0 0;
                }
                100% {
                    background-position: 50px 50px;
                }
            }

            @-moz-keyframes move {
                0% {
                    background-position: 0 0;
                }
                100% {
                    background-position: 50px 50px;
                }
            }


            .orange > span {
                background-color: #f1a165;
                background-image: -moz-linear-gradient(top, #f1a165, #f36d0a);
                background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f1a165),color-stop(1, #f36d0a));
                background-image: -webkit-linear-gradient(#f1a165, #f36d0a);
            }

            .red > span {
                background-color: #f0a3a3;
                background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
                background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, #f42323));
                background-image: -webkit-linear-gradient(#f0a3a3, #f42323);
            }

            .nostripes > span > span, .nostripes > span:after {
                -webkit-animation: none;
                -moz-animation: none;
                background-image: none;
            }
        </style>

    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu" class="main-menu"></a>
                Oregon 911 - July Fourth
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
                        <h1> July Fourth: <?php echo ($callSum); ?> </h1>
                        <p> Note: This page does not update automatically, and this is for Washington county only. </p>
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

                        <details open='true' id="custom-marker" class="wccca-details">
                            <summary>Graph By Hour</summary>
                            <div id="hourly" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        </details>

                        <!--<details open='true' id="custom-marker" class="wccca-details">
                            <summary>Graph By Event</summary>
                            <div id="evently" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        </details>-->
                        <br>
                        <details open='true' id="custom-marker" class="wccca-details">
                            <summary>2015 Table</summary>
                            <?PHP
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Event</th><th>Total</th></tr>';

                            $result = $db->sql_query(getTableSQL(2015));
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr><th>' . $row['callSum'] . '</th><th>' . $row['Calls'] . '</th>';
                            }
                            // Run the query 
                            $result = $db->sql_query(getTotalSQL(2015));
                            $row = $db->sql_fetchrow($result);
                            echo '<tr><th>Total</th><th>' . $row['Total'] . '</th>';
                            echo '</table>';
                            ?>
                        </details>
                        <br>
                        <details open='true' id="custom-marker" class="wccca-details">
                            <summary>2014 Table</summary>
                            <?PHP
                            echo '<table style="width:100%;">';
                            echo '<tr><th>Event</th><th>Total</th></tr>';

                            $result = $db->sql_query(getTableSQL(2014));
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr><th>' . $row['callSum'] . '</th><th>' . $row['Calls'] . '</th>';
                            }
                            // Run the query 
                            $result = $db->sql_query(getTotalSQL(2014));
                            $row = $db->sql_fetchrow($result);
                            echo '<tr><th>Total</th><th>' . $row['Total'] . '</th>';
                            echo '</table>';
                            ?>
                        </details>

                        <?PHP
                        $begin = strtotime('2015-07-4 00:01:00');
                        $now = time();
                        $end = strtotime('2015-07-5 02:00:00');
                        $percent = ($now - $begin) / ($end - $begin) * 100;
                        if ($percent > 100) {
                            $percent = 100;
                        }
                        ?>
                        <div class="meter red">
                            <span style="width: <?PHP echo ($percent); ?>%"></span>
                        </div>

                        <!-- ====================================================================================== -->
                        <?PHP
                        if (!$mobile) {
                            ?>
                        </div>

                    <?PHP } ?>
                </div>
            </div>

            <?PHP include ("./inc/nav.php"); ?>

        </div>
        <script type = "text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });</script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>

        <script type="text/javascript">
            $(function () {
                $('#hourly').highcharts({
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: 'July 4th Hourly Calls'
                    },
                    subtitle: {
                        text: '2014-2015'
                    },
                    xAxis: {
                        type: "datetime",
                        dateTimeLabelFormats: {
                            day: '%H'
                        },
                        tickInterval: 3600 * 1000,
                        labels: {
                            format: '{value:%H}'
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
                        pointFormat: 'Hour {point.x:%H}, {point.y}  Calls'
                    },
                    series: [{
                            name: '2015',
                            color: '#0000FF',
<?PHP
$sql = "SELECT HOUR(timestamp) as hour, MINUTE(timestamp) as minute, second(timestamp) as second, 'W' as county, count(*) as total FROM oregon911_cad.pdx911_archive WHERE oregon911_cad.pdx911_archive.county != 'C' AND (timestamp BETWEEN '2015-07-4 00:01:00' AND '2015-07-5 02:00:00') AND callSum IN ('MISC FIRE','RESIDENTIAL FIRE','COMMERCIAL FIRE', 'CAR FIRE', 'ELECTRICAL FIRE', 'UNKNOWN TYP FIRE', 'BRUSH FIRE', 'CHIMNEY FIRE', 'BARKDUST FIRE', 'GRASS FIRE', 'TREE FIRE', 'BARN FIRE', 'POLE FIRE', 'TRUCK FIRE', 'TRUCK FIRE,LARGE', 'FIREWORKS', 'DUMPSTER FIRE', 'BOAT FIRE', 'MOTOR ASST, FIRE') GROUP BY HOUR(timestamp) UNION ALL SELECT HOUR(timestamp) as hour, MINUTE(timestamp) as minute, SECOND(timestamp) as second, 'C' as county, count(*) as total FROM oregon911_cad.pdx911_archive WHERE oregon911_cad.pdx911_archive.county != 'C' AND (timestamp BETWEEN '2014-07-4 00:01:00' AND '2014-07-5 02:00:00') AND callSum IN ('MISC FIRE','RESIDENTIAL FIRE','COMMERCIAL FIRE', 'CAR FIRE', 'ELECTRICAL FIRE', 'UNKNOWN TYP FIRE', 'BRUSH FIRE', 'CHIMNEY FIRE', 'BARKDUST FIRE', 'GRASS FIRE', 'TREE FIRE', 'BARN FIRE', 'POLE FIRE', 'TRUCK FIRE', 'TRUCK FIRE,LARGE', 'FIREWORKS', 'DUMPSTER FIRE', 'BOAT FIRE', 'MOTOR ASST, FIRE') GROUP BY HOUR(timestamp);";
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
        $Woutput .= "[Date.UTC(2015,7,4," . $row['hour']. "," . $row['minute']. "," . $row['second']. "), $AVG_R ],";
    } elseif ($row['county'] == "C") {
        $Coutput .= "[Date.UTC(2015,7,4," . $row['hour']. "," . $row['minute']. "," . $row['second']. "), $AVG_R ],";
    }
}
?>
                            data: [<?PHP echo rtrim($Woutput, ","); ?>]
                        }, {
                            name: '2014',
                            color: '#C80000',
                            data: [<?PHP echo rtrim($Coutput, ","); ?>]
                        }]
                });
            });</script>
        <script type="text/javascript" src="https:////code.highcharts.com/3.0.1/highcharts.js"></script>
        <script src="../../js/modules/exporting.js"></script>
    </body>
</html>

<?PHP

function getTableSQL($year) {
    return "SELECT callSum, COUNT(*) as 'Calls' FROM oregon911_cad.pdx911_archive WHERE oregon911_cad.pdx911_archive.county != 'C' AND (timestamp BETWEEN '$year-07-4 00:01:00' AND '$year-07-5 02:00:00') AND callSum IN ('MISC FIRE','RESIDENTIAL FIRE','COMMERCIAL FIRE', 'CAR FIRE', 'ELECTRICAL FIRE', 'UNKNOWN TYP FIRE', 'BRUSH FIRE', 'CHIMNEY FIRE', 'BARKDUST FIRE', 'GRASS FIRE', 'TREE FIRE', 'BARN FIRE', 'POLE FIRE', 'TRUCK FIRE', 'TRUCK FIRE,LARGE', 'FIREWORKS', 'DUMPSTER FIRE', 'BOAT FIRE', 'MOTOR ASST, FIRE') GROUP BY callSum ORDER BY 2 DESC;";
}

function getTotalSQL($year) {
    return "SELECT COUNT(*) as 'Total' FROM oregon911_cad.pdx911_archive WHERE oregon911_cad.pdx911_archive.county != 'C' AND (timestamp BETWEEN '$year-07-4 00:01:00' AND '$year-07-5 02:00:00') AND callSum IN ('MISC FIRE','RESIDENTIAL FIRE','COMMERCIAL FIRE', 'CAR FIRE', 'ELECTRICAL FIRE', 'UNKNOWN TYP FIRE', 'BRUSH FIRE', 'CHIMNEY FIRE', 'BARKDUST FIRE', 'GRASS FIRE', 'TREE FIRE', 'BARN FIRE', 'POLE FIRE', 'TRUCK FIRE', 'TRUCK FIRE,LARGE', 'FIREWORKS', 'DUMPSTER FIRE', 'BOAT FIRE', 'MOTOR ASST, FIRE');";
}
?>