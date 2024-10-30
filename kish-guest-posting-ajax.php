<?php
$root = dirname(dirname(dirname(dirname(__FILE__))));
include_once($root.'/wp-load.php');
if (! wp_verify_nonce($_POST['security'], 'kgp_nonce') ) die('Security check'); 
switch($_POST['req']) {
	case 'initgppage' :
	kgp_user_init_page();
	break;
	default:
	case 'update_settings' :
	kgp_update_settings($_POST);
	break;
	case 'new_post_form' :
	kgp_write_gp();
	break;
	case 'save_new_post' :
	kgp_save_post($_POST);
	break;
	case 'preview_post' :
	kgp_preview_post($_POST);
	break;
	case 'showauthorposts' :
	kgp_load_author_posts($_POST);
	break;
	case 'showloginform' :
	kgp_login_form();
	break;
	case 'processlogin' :
	kgp_process_login($_POST['uname'], $_POST['pword']);
	break;
	case 'edit_post_form' :
	kgp_edit_gp($_POST['postid']);
	break;
	case 'logout' :
	kgp_wp_logout();
	break;
	case 'showregform' :
	kgp_reg_form();
	break;
	case 'processregistration' :
	kgp_process_reg($_POST['kgp_reg_uname'], $_POST['kgp_email']);
	break;
	case 'manageprofile' :
	kgp_profile_form();
	break;
	case 'editprofile' :
	kgp_update_profile($_POST);
	break;
	case 'postcapping' :
	kgp_check_post_capping();
	break;
	default:
	break;
}
?>