<?php
define('IN_PHPBB', true);
define('OR911_INFO', true);
$phpbb_root_path = '/home/oregon911/oregon911.net/discussion/';
$phpEx           = substr(strrchr(__FILE__, '.'), 1);
require_once($phpbb_root_path . 'common.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->session_kill();
$user->session_begin();
if (!isset($_GET['redirect'])) {
	header("location: ./");
	exit;
} else {
	$info             = parse_url($_GET['redirect']);
	$host             = $info['host'];
	$host_names       = explode(".", $host);
	$bottom_host_name = $host_names[count($host_names) - 2] . "." . $host_names[count($host_names) - 1];
	if (strtoupper($bottom_host_name) == 'OREGON911.NET') {
		header("location: " . $_GET['redirect']);
		exit;
	} else {
		header("location: ./");
		exit;
	}
}
?> 