<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("loggedin.php");
require_once("google.php");

if ($_GET['AJAX_REFRESH'] == "W") {
    $sql = "Select * from `oregon911_cad`.`pdx911_calls` WHERE county ='W' order by timestamp DESC";

// Run the query 
    $result = $db->sql_query($sql);
    echo '<table style="width:100%;">';
    if (!$_GET['mobile'] == "Y") {
        echo '<tr><th>Call #</th><th>Call Type</th><th>Station</th><th>Address</th><th>Units</th><th>URL</th></tr>';
    } else {
        echo '<tr><th style="word-break:break-all;">Call</th><th style="word-break:break-all;">Address</th><th style="word-break:break-all;">Units</th></tr>';
    }
    while ($row = $result->fetch_assoc()) {
        if (!$_GET['mobile'] == "Y") {
            echo '<tr><th><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">' . $row['GUID'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&calltype=' . urlencode($row['callSum']) . '">' . $row['callSum'] . '</a></th><th><a href="./station?station=' . $row['station'] . '&county=' . $row['county'] . '">' . $row['station'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th>';
        } else {
            echo '<tr><th style="word-break:break-all;"><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">' . $row['callSum'] . '</a></th><th style="word-break:break-all;"><a href="./search?county=' . $row['county'] . '&address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th style="word-break:break-all;">';
        }
//echo($row['units']);
        $sql = "Select * from `oregon911_cad`.`pdx911_units` WHERE GUID = '" . $row['GUID'] . "' AND county = '" . $row['county'] . "'";
        // Run the query
        $result2 = $db->sql_query($sql);
        $UOUTPUT = '';
        while ($unit = $result2->fetch_assoc()) {
            if ($unit['clear'] != '00:00:00') {
                $UOUTPUT .= '<span class="clear" title="Cleared @ ' . $unit['clear'] . '">' . $unit['unit'] . '</span>, ';
            } else if ($unit['onscene'] != '00:00:00') {
                $UOUTPUT .= '<span class="onscene" title="Onscene @ ' . $unit['onscene'] . '">' . $unit['unit'] . '</span>, ';
            } else if ($unit['enroute'] != '00:00:00') {
                $UOUTPUT .= '<span class="enroute" title="Enroute @ ' . $unit['enroute'] . '">' . $unit['unit'] . '</span>, ';
            } else if ($unit['dispatched'] != '00:00:00') {
                $UOUTPUT .= '<span class="dispatched" title="Dispatched @ ' . $unit['dispatched'] . '">' . $unit['unit'] . '</span>, ';
            }
        }
        echo (substr($UOUTPUT, 0, -2));
        echo("</th>");
        if (!$mobile) {
            echo('<th><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">Call Log </a></th></tr>');
        }
    }
    echo '</table>';
    exit;
} else if ($_GET['AJAX_REFRESH'] == "C") {
    $sql = "Select * from `oregon911_cad`.`pdx911_calls` WHERE county ='C' order by timestamp DESC";

// Run the query 
    $result = $db->sql_query($sql);
    echo '<table style="width:100%;">';
    if (!$_GET['mobile'] == "Y") {
        echo '<tr><th>Call #</th><th>Call Type</th><th>Station</th><th>Address</th><th>Units</th><th>URL</th></tr>';
    } else {
        echo '<tr><th style="word-break:break-all;">Call</th><th style="word-break:break-all;">Address</th><th style="word-break:break-all;">Units</th></tr>';
    }
    while ($row = $result->fetch_assoc()) {
        if (!$_GET['mobile'] == "Y") {
            echo '<tr><th><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">' . $row['GUID'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&calltype=' . urlencode($row['callSum']) . '">' . $row['callSum'] . '</a></th><th><a href="./station?station=' . $row['station'] . '&county=' . $row['county'] . '">' . $row['station'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th>';
        } else {
            echo '<tr><th style="word-break:break-all;"><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">' . $row['callSum'] . '</a></th><th><a href="./search?county=' . $row['county'] . '&address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th><th style="word-break:break-all;">';
        }
        //echo($row['units']);
         $sql = "Select * from `oregon911_cad`.`pdx911_units` WHERE GUID = '" . $row['GUID'] . "' AND county = '" . $row['county'] . "'";
          // Run the query
          $result2 = $db->sql_query($sql);
          $UOUTPUT = '';
          while ($unit = $result2->fetch_assoc()) {
          if ($unit['clear'] != '00:00:00') {
          $UOUTPUT .= '<span class="clear" title="Cleared @ ' . $unit['clear'] . '">' . $unit['unit'] . '</span>, ';
          } else if ($unit['onscene'] != '00:00:00') {
          $UOUTPUT .= '<span class="onscene" title="Onscene @ ' . $unit['onscene'] . '">' . $unit['unit'] . '</span>, ';
          } else if ($unit['enroute'] != '00:00:00') {
          $UOUTPUT .= '<span class="enroute" title="Enroute @ ' . $unit['enroute'] . '">' . $unit['unit'] . '</span>, ';
          } else if ($unit['dispatched'] != '00:00:00') {
          $UOUTPUT .= '<span class="dispatched" title="Dispatched @ ' . $unit['dispatched'] . '">' . $unit['unit'] . '</span>, ';
          }
          }
          echo (substr($UOUTPUT, 0, -2)); 
        echo("</th>");
        if (!$mobile) {
            echo('<th><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '&type=' . $row['type'] . '">Call Log </a></th></tr>');
        }
    }
    echo '</table>';
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Incidents List</title>

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
                Oregon 911 - Incident List
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
                        <h1> Call Log: <?php echo ($callSum); ?> </h1>
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
                        if (!$mobile) {
                            ?>
                            <div class="dragdrop">
                                <details class="wccca-details" open='true'>
                                    <summary>Active Calls - Washington County</summary>
                                    <div id="washington"> </div>
                                </details>
                            </div>

                            <div class="dragdrop">
                                <details class="ccom-details" open='true'>
                                    <summary>Active Calls - Clackamas County</summary>
                                    <div id="clackamas"> </div>
                                </details>
                            </div>
                            <?PHP
                        } else {
                            ?>
                            <div class="dragdrop">
                                <details class="wccca-details" open='true'>
                                    <summary>Active Calls - Washington County</summary>
                                    <div id="washington-mobile"> </div>
                                </details>
                            </div>

                            <div class="dragdrop">
                                <details class="ccom-details" open='true'>
                                    <summary>Active Calls - Clackamas County</summary>
                                    <div id="clackamas-mobile"> </div>
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
                        echo ('<br>');
                        echo '<p>Copyright &copy; ' . date("Y") . ' Brandan Lasley. All Rights Reserved.</p>';
                        //    echo '<p>The material on this site may not be reproduced, distributed, transmitted, cached or otherwise used, except with the prior written permission of Brandan Lasley.</p>';
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
        <script type = "text/javascript">
            $(function () {
                $('nav#menu').mmenu();
            });
        </script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
        <script type='text/javascript' src="//maps.google.com/maps/api/js?sensor=false&extn=.js"></script>
        <style type="text/css">
            html { height: 100% }
            body { height: 100%; margin: 0; padding: 0 }
            #map_canvas { height: 100% }
            .auto-style1 {
                margin-bottom: 337px;
            }
        </style>

        <!-- Ugh.... this the AJAX refresh stuff, this will probably get messy on other pages -->
        <script>
            $(document).ready(function () {
                auto_refresh();
            });
            function auto_refresh() {
                $('#washington').load('?AJAX_REFRESH=W').fadeIn("slow");
                $('#clackamas').load('?AJAX_REFRESH=C').fadeIn("slow");
                $('#washington-mobile').load('?AJAX_REFRESH=W&mobile=Y').fadeIn("slow");
                $('#clackamas-mobile').load('?AJAX_REFRESH=C&mobile=Y').fadeIn("slow");
            }
            var refreshId = setInterval(auto_refresh, 10000);
        </script>

        <script>
            jQuery.fn.swap = function (b) {
                // method from: https://blog.pengoworks.com/index.cfm/2008/9/24/A-quick-and-dirty-swap-method-for-jQuery
                b = jQuery(b)[0];
                var a = this[0];
                var t = a.parentNode.insertBefore(document.createTextNode(''), a);
                b.parentNode.insertBefore(a, b);
                t.parentNode.insertBefore(b, t);
                t.parentNode.removeChild(t);
                return this;
            };


            $(".dragdrop").draggable({revert: true, helper: "clone"});

            $(".dragdrop").droppable({
                accept: ".dragdrop",
                activeClass: "ui-state-hover",
                hoverClass: "ui-state-active",
                drop: function (event, ui) {

                    var draggable = ui.draggable, droppable = $(this),
                            dragPos = draggable.position(), dropPos = droppable.position();

                    draggable.css({
                        left: dropPos.left + 'px',
                        top: dropPos.top + 'px'
                    });

                    droppable.css({
                        left: dragPos.left + 'px',
                        top: dragPos.top + 'px'
                    });
                    draggable.swap(droppable);
                }
            });</script>
    </body>
</html>

