<?PHP
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once("database.php");
require_once("google.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="author" content="http://brandanlasley.com" />
        <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />
        <title>Oregon 911 - Social</title>

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
                Oregon 911 - Social
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
                        <h1> Social Media: </h1>
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

                        <details class="wccca-details" open='true'>
                            <summary>Washington County</summary>

                            <table>
                                <col width=40%>
                                <tr><th>Account</th><th>Description</th><tr>
                                <tr><td><a href="https://twitter.com/WashCo_FireMed" target="_blank">WashCo_FireMed</a></td><td>Posts every call Fire/Medical in Washington County as it happens.</td><tr>
                                <tr><td><a href="https://twitter.com/WashCo_Police" target="_blank">WashCo_Poilce</a></td><td>Posts every traffic accident, hazards and firework complaint for Washington County.</td><tr>
                                <tr><td><a href="https://twitter.com/WashCoScanner" target="_blank">WashCoScanner</a><b><font color="red">*</font></b></td><td>Scanner information for Washington County</td><tr>
                                <tr><td><a href="https://twitter.com/WCCCA" target="_blank">WCCCA 911</a><b><font color="red">*</font></b></td><td>Washington County 911</td><tr>
                            </table>

                        </details>



                        <details class="ccom-details" open='true'>
                            <summary>Clackamas County</summary>

                            <table>
                                <col width=40%>
                                <tr><th>Account</th><th>Description</th><tr>
                                <tr><td><a href="https://twitter.com/ClackCo_FireMed" target="_blank">ClackCo_FireMed</a></td><td>Posts every call Fire/Medical in Clackamas County as it happens.</td><tr>
                                <tr><td><a href="https://twitter.com/ClackCoScanner" target="_blank">ClackCoScanner</a><b><font color="red">*</font></b></td><td>Scanner information for Clackamas County</td><tr>
                            </table>

                        </details>

                        <details class="boec-details" open='true'>
                            <summary>Multnomah County</summary>

                            <table>
                                <col width=40%>
                                <tr><th>Account</th><th>Information</th><tr>
                                <tr><td><a href="https://twitter.com/PDXFireLog" target="_blank">PDXFireLog</a><b><font color="red">*</font></b></td><td>Most recent, closed, non-confidential, fire/medical calls for Multnomah County.</td><tr>
                                <tr><td><a href="https://twitter.com/PDXPoliceLog" target="_blank">PDXPoliceLog</a><b><font color="red">*</font></b></td><td>Most recent, closed, non-confidential, police calls for Multnomah County.</td><tr>
                                <tr><td><a href="https://twitter.com/PDXScanner911" target="_blank">PDXScanner911</a><b><font color="red">*</font></b></td><td>Posts scanner information heard in Multnomah County.</td><tr>
                            </table>

                        </details>


                        <br>

                        <details class="duel-details" open='true'>
                            <summary>Duel-County</summary>

                            <table>
                                <col width=40%>
                                <tr><th>Account</th><th>Description</th><tr>
                                <tr><td><a href="https://twitter.com/CADAlerts" target="_blank">CADAlerts</a></td><td>Automatic emergency alerts based on a computer algorithm.</td><tr>
                                <tr><td><a href="https://twitter.com/CADEntries" target="_blank">CADEntries</a></td><td>Posts every entry added to the call log page.</td><tr>
                                <tr><td><a href="https://twitter.com/PDXLifeFlight" target="_blank">PDXLifeFlight</a></td><td>Automatic Lifeflight activations along with updates on the status.</td><tr>                                
                                <tr><td><a href="https://twitter.com/PDXAccidents" target="_blank">PDXAccidents</a></td><td>Posts every traffic accident in Clackamas/Washington County.</td><tr>
                            </table>

                        </details>

                        <details class="tri-details" open='true'>
                            <summary>Tri-County</summary>

                            <table>
                                <col width=40%>
                                <tr><th>Account</th><th>Description</th><tr>
                                <tr><td><a href="https://twitter.com/PDXAlerts" target="_blank">PDXAlerts</a><b><font color="red">*</font></b></td><td>Posts alerts/information for Washington, Clackamas and Multnomah county. </td><tr>
                            </table>

                        </details>

                        <b> <font color="red">*</font> These accounts are not owned / operated by Oregon 911. </b>
                        <br>
                        <br>
                        <?php
                        echo '<p>Copyright &copy; ' . date("Y") . ' Oregon 911. All Rights Reserved.</p>';
                        ?>

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
            });
        </script>
        <?PHP echo($analytics); ?>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
    </body>
</html>