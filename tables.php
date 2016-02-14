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
        <title>Oregon 911 - Tables</title>

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
                Oregon 911 - Tables
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
                        <h1> Tables: </h1>
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
                        <?php if ($mode == 'calltypes') { ?>
                            <details open='true' id="custom-marker" class="wccca-details">
                                <summary>Call Types (Both Counties)</summary>
                                <?PHP
                                echo '<table style="width:100%;">';
                                echo '<tr><th>Type</th><th>Count</th><th>County</th></tr>';

                                $sql = "SELECT DISTINCT callSum, count(callSum) AS 'amount', county FROM `oregon911_cad`.`pdx911_archive` WHERE callSum NOT LIKE '%*%' AND (county != 'M' AND county != 'MULTCO') group by callSum order by 2 DESC";
                                $result = $db->sql_query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr><th>' . $row['callSum'] . '</th><th>' . $row['amount'] . '</th><th>' . $row['county'] . '</th>';
                                }
                                echo '</table>';
                                ?>
                            </details>
                        <?php } elseif ($mode == 'dispatchflags') { ?>
                            <details open='true' id="custom-marker" class="wccca-details">
                                <summary>Call Flags (Both Counties)</summary>
                                <?PHP
                                echo '<table style="width:100%;">';
                                echo '<tr><th>Flag</th><th>Count</th><th>County</th></tr>';

                                $sql = "Select * From `oregon911_cad`.`pdx911_callSum_flags`";
                                $result = $db->sql_query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr><th>' . $row['Flag'] . '</th><th>' . $row['Count'] . '</th><th>' . $row['County'] . '</th>';
                                }
                                echo '</table>';
                                ?>
                            </details>
                        <?php } elseif ($mode == 'avgtraveltable') { ?>
                            <details open='true' id="custom-marker" class="wccca-details">
                                <summary>Average Response Time(Both Counties)</summary>
                                <?PHP
                                echo '<table style="width:100%;">';
                                echo '<tr><th>Agency</th><th>County</th><th>Average Time In Minutes</th></tr>';

                                $sql = "SELECT `oregon911_cad`.`pdx911_units`.agency, `oregon911_cad`.`pdx911_units`.county, ROUND(AVG((IF(onscene >= enroute, TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 60, IF(TIME_TO_SEC(TIMEDIFF(onscene, enroute)) / 3600 > 20, ((UNIX_TIMESTAMP(DATE_ADD((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', onscene), '%Y-%d-%c %T')), INTERVAL 1 DAY)) - UNIX_TIMESTAMP((STR_TO_DATE(CONCAT(DATE(TIMESTAMP), ' ', enroute), '%Y-%d-%c %T')))) / 60), 0)))), 2) as AVG_R FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county WHERE `oregon911_cad`.`pdx911_units`.county != 'M' AND `oregon911_cad`.`pdx911_units`.county != 'MULTCO' AND `oregon911_cad`.`pdx911_units`.agency != '' AND onscene != '00:00:00' AND enroute != '00:00:00' group by `oregon911_cad`.`pdx911_units`.agency order by 3 ASC";
                                $result = $db->sql_query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr><th>' . $row['agency'] . '</th><th>' . $row['county'] . '</th><th>' . $row['AVG_R'] . '</th>';
                                }
                                echo '</table>';
                                ?>
                            </details>
                        <?PHP } ?>
                        <!-- ====================================================================================== -->
                        <?PHP
                        if (!$mobile) {
                            ?>
                        </div>

                    <?PHP } ?>
                </div>
            </div>

            <?PHP include ("./inc/nav.php");
            echo '<p>Copyright &copy; ' . date("Y") . ' Oregon 911. All Rights Reserved.</p>';
            ?>

        </div>
        <script type = "text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
<?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
    </body>
</html>