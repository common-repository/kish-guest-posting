<?php
/*
Plugin Name: Kish Guest Posting
Plugin URI: http://kishpress.com/blog/2011/02/25/guest-posting-plugin/
Description: You can use this plugin for Guest Post Submission
Version:1.2
Author: Kishore Asokan
Author URI: http://kishpress.com
Follow Me on Twitter: http://www.twitter.com/kishpress
*/

/*  
	License: GPLv2 or later
*/
$kroot = str_replace("\\", "/", dirname(__FILE__));
$root = dirname(dirname(dirname(dirname(__FILE__))));
global $kishGuestPost;
include_once($kroot.'/functions.php');
add_action('admin_menu', 'kgp_add_admin');
if( function_exists('register_activation_hook') ) {
	register_activation_hook(__FILE__,"kgp_install");
}
?>