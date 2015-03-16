<?php

define('IN_PHPBB', true);
define('OR911_INFO', true);
$phpbb_root_path = '/home/oregon911/oregon911.net/discussion/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once($phpbb_root_path . 'common.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);

if ($user->data['username'] == 'Anonymous' or $user->data['is_bot']) {
    header("location: http://cad.oregon911.net/login?redirect=" . urlencode(GetURL()));
}

function GetURL() {
    $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $params = $_SERVER['QUERY_STRING'];

    $currentUrl = $protocol . '://' . $host . $script . '?' . $params;

    return $currentUrl;
}

?>