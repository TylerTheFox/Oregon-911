<?php
define('IN_PHPBB', true);
define('OR911_INFO', true);
$phpbb_root_path = '/home/oregon911/oregon911.net/discussion/';
$phpEx           = substr(strrchr(__FILE__, '.'), 1);
require_once($phpbb_root_path . 'common.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);

$LoggedIn = False;

if ($user->data['username'] == 'Anonymous' or $user->data['is_bot']) {
	$LoggedIn = False; 
} else {
	$LoggedIn = True;
}
?>