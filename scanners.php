<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("loggedin.php");
require_once("google.php");

if (!empty($_GET['AJAX_REFRESH'])) {
    if (($_GET['AJAX_REFRESH'] == "dp") && (!empty($_GET['date']))) {
        $date = htmlspecialchars($db->sql_escape(strip_tags($_GET["date"])));
        $date = date("y/m/d", strtotime($date));
        $sql = "SELECT ID, TIME(START) as START, TIME(END) AS END FROM `oregon911_cad`.`radio_archive` WHERE START BETWEEN '$date 00:00:00' AND '$date 23:59:59' order by START DESC";
        $result = $db->sql_query($sql);
        ?>
        <p>Time: 
            <select name="timepicker">
                <?PHP
                while ($row = $result->fetch_assoc()) {
                    echo('<option value="' . $row['ID'] . '">' . $row['START'] . ' - ' . $row['END'] . '</option>');
                }
                ?>
            </select></p>
        <?PHP
        exit;
    } else if (($_GET['AJAX_REFRESH'] == "tp") && (!empty($_GET['audiofile']))) {
        $audiofile = htmlspecialchars($db->sql_escape(strip_tags($_GET["audiofile"])));
        // Create the SQL statement
        $sql = 'SELECT file_short FROM `oregon911_cad`.`radio_archive` WHERE ID = ' . $audiofile;
        // Run the query 
        $result = $db->sql_query($sql);
        // $row should hold the data you selected
        $file = $db->sql_fetchrow($result);
        ?>
        <audio id="player2" src="http://radio.oregon911.net/archive/radio1<?PHP echo($file['file_short']) ?>" type="audio/mp3" controls="controls"></audio>	
        <br>
        <a href="http://radio.oregon911.net/archive/radio1<?PHP echo($file['file_short']) ?>">Download</a>
        <?PHP
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Scanners</title>

        <link type="text/css" rel="stylesheet" href="css/main.css" />
        <link type="text/css" rel="stylesheet" href="./src/css/jquery.mmenu.all.css" />

        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

        <script src="/player/jquery.js"></script>	
        <script src="/player/mediaelement-and-player.min.js"></script>
        <link rel="stylesheet" href="/player/mediaelementplayer.min.css" />

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="./src/js/jquery.mmenu.min.all.js"></script>
        <script type="text/javascript" src="https://hosted.muses.org/mrp.js"></script>

        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
        <style>
            html, body { height:100%; }
        </style>

    </head>
    <body>
        <div id="page">
            <div class="header">
                <a href="#menu"></a>
                Oregon 911 - Scanners
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
                            <?PHP
                            if ($_GET['recordings'] == "Y") {
                                echo("<h1> Current Records/Recordings  </h1>");
                            } else {
                                echo("<h1> Scanners: </h1>");
                            }
                            ?>
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
                        <?PHP
                        if ($_GET['recordings'] == "Y") {
                            ?>
                            <div class="dragdrop">
                                <details class="wccca-details" open='true'>
                                    <summary>Radio 1 (Washington County Law Enforcement)</summary>
                                    <h2> Time/Date Search: </h2>
                                    <p>Date: <input type="text" id="datepicker" onchange="timebox(this)"></p>
                                    <div id="timepicker" onchange="audioplayer(this)"></div>
                                    <br>
                                    <br>
                                    <div id="audoplayer"></div>
                                    <br>
                                    <br>
                                    <b> Records are destroyed after 30 days, audio silence is trimmed.  </b>
                                </details>
                            </div>
                            <?PHP
                        } else {
                            ?>
                            <div class="dragdrop">
                                <details class="wccca-details" open='true'>
                                    <summary>Washington County Law Enforcement</summary>
                                    <br>
                                    <center>
                                        <script type="text/javascript">
                                            MRP.insert({
                                                'url': 'http://server2.oregon911.net/radio/radio1',
                                                'lang': 'en',
                                                'codec': 'mp3',
                                                'volume': 100,
                                                'autoplay': false,
                                                'buffering': 5,
                                                'title': 'Oregon 911 Radio',
                                                'welcome': 'Law Enforcement',
                                                'bgcolor': '#FFFFFF',
                                                'skin': 'arvyskin',
                                                'width': 560,
                                                'height': 30
                                            });
                                        </script>
                                    </center>
                                    <!-- set up player container with background color -->
                                    <p> Serving Beaverton, Hillsboro, Sheriff's Office, and South Cities </p>
                                    <a href="http://server2.oregon911.net/radio/radio1" target="_blank">Play (Mobile)</a>
                                    <br>
                                </details>
                            </div>

                            <div class="dragdrop">
                                <details class="wccca-details" open='true'>
                                    <summary>Washington County Fire/Med</summary>
                                    <p> Radio Reference </p>
                                    <a href="https://www.broadcastify.com/listen/feed/1102/web/?rl=rr" target="_blank">Play</a>
                                </details>
                            </div>

                            <div class="dragdrop"> 
                                <details class="ccom-details" open='true'>
                                    <summary>Clackamas County Law Enforcement</summary>
                                    <p> Radio Reference </p>
                                    <a href="https://www.broadcastify.com/listen/feed/3544/web" target="_blank">Play</a>
                                </details>
                            </div> 

                            <div class="dragdrop"> 
                                <details class="ccom-details" open='true'>
                                    <summary>Clackamas County Fire/Med</summary>
                                    <p> Radio Reference </p>
                                    <a href="https://www.broadcastify.com/listen/feed/3545/web" target="_blank">Play</a>
                                </details>
                            </div> 
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
            echo '<p>Copyright &copy; 2015 Oregon 911. All Rights Reserved.</p>';
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
            });
        </script>
        <script>
            $('audio,video').mediaelementplayer();
        </script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
        <script>
            $(function () {
                $("#datepicker").datepicker({minDate: -30, maxDate: "+0D"});
            });

            function timebox() {
                $('#timepicker').load('?AJAX_REFRESH=dp&date=' + document.getElementById('datepicker').value).fadeIn("slow");
            }

            function audioplayer() {
                var audiofile = $("#timepicker option:selected").val();
                $('#audoplayer').load('?AJAX_REFRESH=tp&audiofile=' + audiofile).fadeIn("slow");
            }
        </script>
    </body>
</html>