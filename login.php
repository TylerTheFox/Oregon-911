<?php
require_once("google.php");
define('IN_PHPBB', true);
define('OR911_INFO', true);
$phpbb_root_path = '/home/oregon911/oregon911.net/discussion/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once($phpbb_root_path . 'common.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);

if (isset($_POST['username']) && isset($_POST['password'])) {
    $result = $auth->login(htmlspecialchars(strip_tags($db->sql_escape($_POST['username']))), htmlspecialchars(strip_tags($db->sql_escape($_POST['password']))), htmlspecialchars(strip_tags($db->sql_escape($_POST['autologin']))));

    if ($result['status'] == LOGIN_SUCCESS) {
        if (!isset($_POST['redirect'])) {
            header("location: ./");
            exit;
        } else {
            $info = parse_url($_POST['redirect']);
            $host = $info['host'];
            $host_names = explode(".", $host);
            $bottom_host_name = $host_names[count($host_names) - 2] . "." . $host_names[count($host_names) - 1];
            if (strtoupper($bottom_host_name) == 'OREGON911.NET') {
                header("location: " . $_POST['redirect']);
                exit;
            } else {
                header("location: ./");
                exit;
            }
        }
    } else {
        header("location: login?error=Invalid Username/Password!&redirect=" . $_POST['redirect']);
    }
}

if ($user->data['username'] == 'Anonymous' OR $user->data['is_bot']) {
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
                    <a href="#menu"></a>
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
                            <h1> Login: <?php echo ($callSum); ?> </h1>
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
                            <br>
                            <form method="post" action="login">
                                <label for="username">Username: </label> <input type="text" name="username" id="username" size="40"  autocomplete = "off"/><br /><br />
                                <label for="password">Password: </label><input type="password" name="password" id="password" size="40" autocomplete = "off"/><br /><br />
                                <label for="autologin">Remember Me?: </label><input type="checkbox" name="autologin" id="autologin" checked='true'  /><br /><br />
                                <?PHP
                                if (isset($_GET['error'])) {
                                    ?>
                                    <b><font color=red><?PHP echo(htmlspecialchars(strip_tags($_GET['error']))); ?></font></b> 
                                    <br>
                                    <?PHP
                                }
                                ?>
                                <input type="submit" value="Log In" name="login" />
                                <input type="hidden" name="redirect" value="<?PHP
                                if (isset($_GET['redirect'])) {
                                    echo (htmlspecialchars(strip_tags($_GET['redirect'])));
                                }
                                ?>" />
                            </form>
                            <a title="Register" href="https://www.oregon911.net/discussion/ucp.php?mode=register">Register</a>

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
    <?PHP
} else {
    if (!isset($_GET['redirect'])) {
        header("location: ./");
        exit;
    } else {
        $info = parse_url(htmlspecialchars(strip_tags($_GET['redirect'])));
        $host = $info['host'];
        $host_names = explode(".", $host);
        $bottom_host_name = $host_names[count($host_names) - 2] . "." . $host_names[count($host_names) - 1];
        if (strtoupper($bottom_host_name) == 'OREGON911.NET') {
            header("location: " . $_GET['redirect']);
            exit;
        } else {
            header("location: ./");
            exit;
        }
    }
}
?>