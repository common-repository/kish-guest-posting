<?php
$root = dirname(dirname(dirname(dirname(__FILE__))));
$kroot = str_replace("\\", "/", dirname(__FILE__));
file_exists($root.'/wp-load.php') ? require_once($root.'/wp-load.php') : require_once($root.'/wp-config.php');
include_once('kgp.class.php');
global $kishGuestPost;
$kishGuestPost = new kishGP();
//define('KGP_CHK_SPAM', get_option('kgp_akismet_check')=='on' ? true : false, true);
//define('KGP_ALLOW_GP', get_option('kgp_allow_gp')=='on' ? true : false, true);
//define('KGP_BLOCK_WP_ADMIN', get_option('kgp_block_admin')=='on' ? true : false, true);
//define('KGP_ALLOW_EDIT_PUB_POST', get_option('kgp_allow_edit_published_post')=='on' ? true : false, true);
define('KGP_SHOW_AP_TOP', get_option('kgp_author_prof_top')=='on' ? true : false, true);
define('KGP_SHOW_AP_BOTTOM', get_option('kgp_author_prof_bottom')=='on' ? true : false, true);
define('KGP_SEND_EMAIL_POST_SUBMIT', get_option('kgp_send_email_post_submit')=='on' ? true : false, true);
define('KGP_SEND_EMAIL_PUB_POST_EDITED', get_option('kgp_send_email_published_post_edited')=='on' ? true : false, true);
define('KGP_INIT_MSG', get_option('kgp_msg_init'), true);
define('KGP_WRITE_GP_MSG', get_option('kgp_msg_newpost'), true);
define('KGP_EDIT_POST_MSG', get_option('kgp_msg_editpost'), true);
define('KGP_MANAGE_PROF_MSG', get_option('kgp_msg_manageprofile'), true);
define('KGP_VIEW_POSTS_MSG', get_option('kgp_msg_manageposts'), true);
define('KGP_LOGIN_PAGE_MSG', get_option('kgp_msg_loginpage'), true);
define('KGP_REG_PAGE_MSG', get_option('kgp_msg_newreg'), true);
define('KGP_USER_REG_CAPPING', 3600, true); // in minutes
define('KGP_USER_SUBMISSION_CAPPING', 60, true); // in minutes

function kgp_add_admin() {
	$plugin_page=add_menu_page('Kish Guest Posting', 'Kish Guest Posting', 8, 'kish-guest-posting', 'kgp_settings');
	add_action( 'admin_head-'. $plugin_page, 'kgp_addHeaderCode' );
}
function kgp_wp_admin_init() {
	global $kishGuestPost; 
	if($kishGuestPost->blockWPAdmin) {
		if (strpos(strtolower($_SERVER['REQUEST_URI']),'/wp-admin/') !== false) {
			$current_user = wp_get_current_user();
			if ( $current_user->ID !=1 ) {
				wp_redirect( get_option('siteurl').'?page_id='.get_option('kgp_gp_page'), 302 );
			}
		}
	}
}
add_action('admin_init','kgp_wp_admin_init',100);
function kgp_add_tinymce() {
    wp_print_scripts('jquery-ui-core');
}
function kgp_addHeaderCode() {
	echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL ."/kish-guest-posting/kish_guest_posting_js.php\"></script>\n";
}

function kgp_addScripts() {
	echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"" . WP_PLUGIN_URL ."/kish-guest-posting/style.css\" />";
	if(is_page(get_option('kgp_gp_page'))) {
		kgp_add_tinymce();
		echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL ."/kish-guest-posting/kish_guest_posting_js.php\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL ."/kish-guest-posting/uploadify/scripts/jquery-1.4.2.min.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL ."/kish-guest-posting/uploadify/scripts/swfobject.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL ."/kish-guest-posting/uploadify/scripts/jquery.uploadify.v2.1.4.min.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL ."/kish-guest-posting/editor/nicEdit.js\"></script>\n";
		?>
		<script>
		jQuery(document).ready(function(){
			kgp_uploadify();
		});
		</script>
		<?php
	}
} 
add_action('wp_head', 'kgp_addScripts');
function kgp_settings() { 	
	$nonce= wp_create_nonce ('kgp_nonce'); 
	global $kishGuestPost; 
	$kishGuestPost->loadSettings();
	echo $kishGuestPost->settingsHead; 
	$kishGuestPost->printTableOpen("width:98%;border:1px solid #CACACA;padding:10px;");?>
		<tr>
			<td colspan="2" style="border:1px solid #CACACA;padding:10px;margin-bottom:10px;">
				<p>You can customize this plugin using the settings below. If you have more than one blog, you can 
				try <a target="_blank" href="http://kishpress.com/kish-multi-pro/">Kish Multi Pro</a>, plugin to manage multiple blogs from a single blog.</p>
				<p>My other plugin <a target="_blank" href="http://kishpress.com/kish-twit-pro/">Kish Twit Pro</a> can be used to manage all your twitter accounts from a single blog.</p>
			</td>
		</tr>
		<tr>
			<td style="width:40%;">Guest Posting Page</td>
			<td><?php kgp_get_posts(); ?></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->allowGP ? "checked" : ""; ?>
			<td>Guest Posting Enabled</td>
			<td><input style="float:left" type="checkbox" id="kgp_allow_gp" name="kgp_allow_gp" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->spamCheck ? "checked" : ""; ?>
			<td>Akismet Spam check Enabled</td>
			<td><input style="float:left" type="checkbox" id="kgp_akismet_check" name="kgp_akismet_check" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->blockWPAdmin ? "checked" : ""; ?>
			<td>Block Admin Area for users</td>
			<td><input style="float:left" type="checkbox" id="kgp_block_admin" name="kgp_block_admin" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->addProfTopPost ? "checked" : ""; ?>
			<td>Add Author Profile on Top of Post</td>
			<td><input style="float:left" type="checkbox" id="kgp_author_prof_top" name="kgp_author_prof_top" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked =$kishGuestPost->addProfBottomPost ? "checked" : ""; ?>
			<td>Add Author Profile on Bottom of Post</td>
			<td><input style="float:left" type="checkbox" id="kgp_author_prof_bottom" name="kgp_author_prof_bottom" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->allowPubPostEdit ? "checked" : ""; ?>
			<td>Allow Author to edit posts already published</td>
			<td><input style="float:left" type="checkbox" id="kgp_allow_edit_published_post" name="kgp_allow_edit_published_post" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->emailAdminPostSubmit ? "checked" : ""; ?>
			<td>Send email notification to blog admin when a post is submitted</td>
			<td><input style="float:left" type="checkbox" id="kgp_send_email_post_submit" name="kgp_send_email_post_submit" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->emailAdminPubPostEdited ? "checked" : ""; ?>
			<td>Send email notification to blog admin when published post is edited</td>
			<td><input style="float:left" type="checkbox" id="kgp_send_email_published_post_edited" name="kgp_send_email_published_post_edited" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<?php $checked = $kishGuestPost->emailAuthorPostPub ? "checked" : ""; ?>
			<td>Send email notification to Author on Post Status Change</td>
			<td><input style="float:left" type="checkbox" id="kgp_send_email_author" name="kgp_send_email_author" <?php echo $checked; ?>></td>
		</tr>
		<tr>
			<td style="vertical-align: top;border:1px solid #CACACA;padding:10px;margin:5px 0px 5px 0px;"">
			<strong>You can define the messages to be shown. You can use the following variables which would replace the value according to the user logged in</strong>
			<ul>
				<li><code>%display_name%</code> = > Display Name</li>
				<li><code>%user_firstname%</code> = > First Name</li>
				<li><code>%user_lastname%</code> = > Last Name</li>
				<li><code>%user_email%</code> = > User Email</li>
			</ul>
			</td>
			<td style="vertical-align: top;border:1px solid #CACACA;padding:10px;margin:5px 0px 5px 0px;"">
			<strong>You can define the author profile to be show. You can use the following variables which would replace the value according to the user logged in</strong>
			<ul>
				<li><code>%kgp_author_email%</code> = > Author Email</li>
				<li><code>%kgp_author_url%</code> = > Author Email</li>
				<li><code>%kgp_author_profile%</code> = > Author Profile Text</li>
				<li><code>%kgp_author_name%</code> = > Author Name</li>
			</ul>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Post Submission Capping: </td>
			<td><input type="text"  style="width:100%;" id="kgp_post_submission_capping" value="<?php echo stripslashes($kishGuestPost->userSubmissionCapping); ?>"></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">User Registration Capping: </td>
			<td><input type="text" style="width:100%;" id="kgp_user_reg_capping" value="<?php echo stripslashes($kishGuestPost->userRegCapping); ?>"></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Author Profile: </td>
			<td><textarea style="width:100%;height:50px;" id="kgp_post_author_profile"><?php echo stripslashes($kishGuestPost->authorProfile); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Initial Message Visitor: </td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_init"><?php echo stripslashes($kishGuestPost->initMsgVistor); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Initial Message Author (Logged): </td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_init_logged"><?php echo stripslashes($kishGuestPost->initMsgLogged); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">New Post Message</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_newpost"><?php echo stripslashes($kishGuestPost->msgNewPost); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Edit Post Message</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_editpost"><?php echo stripslashes($kishGuestPost->msgEditPost); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Manage Profile Message</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_manageprofile"><?php echo stripslashes($kishGuestPost->msgManageProf); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Manage Posts Message</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_manageposts"><?php echo stripslashes($kishGuestPost->msgManagePosts); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Login Page Message</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_loginpage"><?php echo stripslashes($kishGuestPost->msgLoginPage); ?></textarea></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">New Registration Message</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_msg_newreg"><?php echo stripslashes($kishGuestPost->msgRegPage); ?></textarea></td>
		</tr>
		<tr>
		
			<td style="vertical-align: top;">Guest Post Terms & Conditions</td>
			<td><textarea style="width:100%;height:50px;" id="kgp_terms"><?php echo stripslashes($kishGuestPost->msgTerms); ?></textarea></td>
		</tr>
		<?php if(get_option('default_role')=='subscriber') : ?>
		<tr>
			<td style="color:#CA0000;font-weight:bold;">Default User Role</td>
			<td style="color:#CA0000;font-weight:bold;">Your Default User Role is <?php echo $kishGuestPost->blogDefaultRole; ?>. Please change to aleast Contributor</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding-top:10px;"></td>
			<td style="padding-top:10px;" id="kgp_save_settings"><input type="button" onclick="kgp_update_settings('<?php echo $kishGuestPost->getNonce(); ?>');" value="Save Settings"></td>
		</tr>
	<?php $kishGuestPost->printTableClose(); ?>
<?php }
function kgp_get_posts() { 
	global $post;
	$nonce= wp_create_nonce  ('kgp_nonce');
	$myposts = get_posts('post_type=page'); ?>
	<select id="kgp_gp_page">
	<?php foreach($myposts as $post) :
	setup_postdata($post);
	if(get_option('kgp_gp_page')==$post->ID) : ?>
	<option SELECTED value="<?php echo $post->ID; ?>"><?php the_title(); ?></option>
	<?php else : ?>
	<option value="<?php echo $post->ID; ?>"><?php the_title(); ?></option>
	<?php endif; ?>
	<?php endforeach; ?>
	</select>
<?php }
function kgp_update_settings($args) {
	global $kishGuestPost;
	$kishGuestPost->saveSettings($args);
}
function kgp_creat_gp_page($content) {
	global $post;
	if(is_page(get_option('kgp_gp_page'))) {
		return $content.'<div style="clear:both;margin-bottom:5px;"></div><div id="kgp_main"><img src="'.WP_PLUGIN_URL.'/kish-guest-posting/img/loader.gif"></div>';
	}
	else {
		if(KGP_SHOW_AP_TOP && is_single() && get_post_meta($post->ID, 'kgp_author_profile', true)) {
			$ap='<div class="kgp_author_bio">'.kgp_replace_author_bio($post->ID).'</div>';
			$content=$ap.$content;
		}
		elseif(KGP_SHOW_AP_BOTTOM && is_single() && get_post_meta($post->ID, 'kgp_author_profile', true)) {
			$ap='<div class="kgp_author_bio">'.kgp_replace_author_bio($post->ID).'</div>';
			$content=$content.$ap;
		}
		return $content;
	}
}
function kgp_replace_author_bio($postid) {
	$msg=stripslashes(get_option('kgp_post_author_profile'));
	$arrchangables = array('kgp_author_email', 'kgp_author_url', 'kgp_author_profile', 'kgp_author_name');
	foreach($arrchangables as $var) {
	   	$msg=str_replace('%'.$var.'%', strip_tags(get_post_meta($postid, $var, true)), $msg);
	}
   	return $msg;
}
add_action('the_content', 'kgp_creat_gp_page' );
function kgp_user_init_page() {
	global $kishGuestPost;
	$kishGuestPost->loadSettings();
	$kishGuestPost->loadCurrentUser();
	$nonce= $kishGuestPost->getNonce();
	//$current_user = wp_get_current_user(); 
	$c='<div class="kgp_butts"><div id="kgp_msg" style="font-weight:bold;margin:5px 0px 5px 0px;padding:5px;border:1px solid #CACACA;display:block;min-height:50px;"></div>';
	if ( !$kishGuestPost->isUserLoggedIn && !$kishGuestPost->allowGP) {
		$c.= '<span style="margin-left:10px;" id="kgp_to_login_link"><input type="button" value="Please Login to post as an author" onclick="kgp_show_login_form(\''.$nonce.'\');return false;" href="'.wp_login_url( get_permalink()).'"></span>';
		$c.= '<span style="margin-left:10px;" id="kgp_to_reg_form"><input type="button" value="Register" onclick="kgp_show_reg_form(\''.$nonce.'\');return false;" href="'.wp_login_url( get_permalink()).'"></span>';
	}
	else {
		if ( $kishGuestPost->isUserLoggedIn) {
			$c.='<span id="kgp_new_post_butt"><input type="button" value="Write Post" onclick="kgp_load_new_post_form(\''.$nonce.'\');"></span>';
			$c.='<span style="margin-left:10px;" id="kgp_manage_posts_butt"><input type="button" value="Manage Your Posts" onclick="kgp_manage_posts(\''.$nonce.'\', \'\', 0);"></span>';
			$c.='<span style="margin-left:10px;" id="kgp_manage_profile"><input type="button" value="Manage Profile" onclick="kgp_manage_profile(\''.$nonce.'\', \'\', 0);"></span>';
			$c.='<span style="margin-left:10px;" id="kgp_logout_butt"><input type="button" value="Logout" onclick="kgp_wp_logout(\''.$nonce.'\', \'\', 0);"></span>';
		}
		else {
			$c.='<span id="kgp_new_post_butt"><input type="button" value="Write Guest Post" onclick="kgp_load_new_post_form(\''.$nonce.'\');"></span>';
			$c.= '<span style="margin-left:10px;" id="kgp_to_login_link"><input type="button" value="Please Login to post as an author" onclick="kgp_show_login_form(\''.$nonce.'\');return false;" href="'.wp_login_url( get_permalink()).'"></span>';
			$c.= '<span style="margin-left:10px;" id="kgp_to_reg_form"><input type="button" value="Register" onclick="kgp_show_reg_form(\''.$nonce.'\');return false;" href="'.wp_login_url( get_permalink()).'"></span>';
		}
	}
	$c.='</div>';
	$c.='<div id="kgp_resultdiv" style="min-height:500px;display:block">';
	$c.='<div id="kgp_resultdiv_submission"></div>';
	$c.='<div id="kgp_resultdiv_posts"></div>';
	$c.='<div id="kgp_resultdiv_profile"></div>';
	$c.='<div id="kgp_resultdiv_forms"></div>';
	$c.='</div>';
	echo $c;
}
function kgp_save_post($args) {
	global $kishGuestPost;
	$kishGuestPost->loadSettings();
	$kishGuestPost->loadCurrentUser();
	$nonce= $kishGuestPost->getNonce();
	if($kishGuestPost->spamCheck) {
		$content['comment_author'] = $args['kgp_author_name'];
		$content['comment_author_email'] = $args['kgp_author_email'];
		$content['comment_author_url'] = $args['kgp_author_url'];
		$content['comment_content'] = stripslashes($args['content']);
		if (!$kishGuestPost->checkSpam ($content)) {
			echo "Spam Suspected by Akismet";
			exit;
		}
	}
	$savedPostId = $kishGuestPost->savePost($args);
	if($savedPostId >0) {
		$kishGuestPost->updateAuthorInfo($args, $savedPostId);
		$kishGuestPost->sendNotificationsPostSubmit($args);
	}
}
function kgp_preview_post($args) { ?>
	<?php $nonce= wp_create_nonce  ('kgp_nonce'); ?>
	<h1 class="entry-title"><?php echo stripslashes($args['kgp_post_title']); ?></h1>
	<div class="entry-content"> 
	<p><?php  echo stripslashes($args['content']); ?></p>
	<?php $arr=array('type'=> 'post','hide_empty' => 0,'orderby'=> 'name');
	$categories=  get_categories($arr);
	 foreach ($categories as $category) {
	 	if($category->term_id==$args['kgp_post_cats']) {
	 		$postcat=$category->cat_name;
	 	}
	 }
	 ?>
	<strong>Tags : </strong><?php echo $args['kgp_post_tags']; ?></br>
	<strong>Category : </strong><?php echo $postcat; ?></br>
	<p>
	Guest Post By <a href="<?php echo $args['kgp_author_url']; ?>"><?php echo $args['kgp_author_name']; ?></a><br>
	<?php echo strip_tags($args['kgp_author_profile']); ?>
	</p>
	<div style="margin:5px 0px 5px 0px;">
	<?php if(strlen(get_option('kgp_terms'))) :?>
	<table>
		<tr>
			<td style="width:10%;vertical-align: top;"><input style="float:left" type="checkbox" id="kgp_accept_terms" name="kgp_accept_terms"></td>
			<td style="vertical-align: top;"><div style="height:50px;overflow:auto;width:100%;"><?php echo get_option('kgp_terms');?></div></td>
		</tr>
	</table>
	<?php endif; ?>
	<?php if($args['postid']>0) : ?>
	<span id="kgp_post_save_butt"><input type="button" value="Save Edit and Submit for Moderation" onclick="kgp_save_post('<?php echo $nonce; ?>');">
	<?php else : ?>
	<span id="kgp_post_save_butt"><input type="button" value="Submit for Moderation" onclick="kgp_save_post('<?php echo $nonce; ?>');">
	<?php endif; ?>
	<span id="kgp_post_cont_editing_butt"><input type="button" value="Continue Editing" onclick="kgp_show_post_form();">
	</div>
	</div> 
	
<?php }
function kgp_check_post_capping() {
	global $kishGuestPost;
	$kishGuestPost->loadSettings();
	if($kishGuestPost->checkAttempts('Post Submission')) {
		echo 'true';
	}
	else {
		echo 'false';
	}
}
function kgp_write_gp() { 
	global $kishGuestPost;
	$kishGuestPost->loadSettings();
	if($kishGuestPost->checkAttempts('Post Submission')) {
		echo "You have already submitted a post few minutes back. You can submit only one post per hour!!";
	}
	else {
		$kishGuestPost->loadCurrentUser();
	?>
	<div id="kgp_post_form">
		<div style="margin:5px 0px 10px 0px" id="kgp_post_preview"></div>
		<div id="kgp_post_tools">
			<div id="fileQueue"></div>
			<div id="kgp_upload_prog"></div>
			<input type="file" name="uploadify" id="uploadify" />
			<p><a href="javascript:jQuery('#uploadify').uploadifyClearQueue()">Cancel All Uploads</a></p>
			<div id="image_upload_results"></div>
			<table style="width:100%">
				<tr>
					<td style="width:20%;vertical-align: top;">Title</td>
					<td style="width:80%;vertical-align: top;"><input id="kgp_post_title" type="text" style="width:100%" placeholder="Your Smart Post Title"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Tags</td>
					<td style="width:80%;vertical-align: top;"><input id="kgp_post_tags" type="text" style="width:100%" placeholder="Enter Tags"></td>
				</tr>
				<tr>
					<td>Category</td>
					<td><select id="kgp_post_cats"> 
						 <option value=""><?php echo attribute_escape(__('Select A Category')); ?></option> 
						 <?php 
						 $arr=array('type'=> 'post','hide_empty' => 0,'orderby'=> 'name');
						  $categories=  get_categories($arr); 
						  foreach ($categories as $category) {
						  	$option = '<option value="'.$category->term_id.'">';
							$option .= $category->cat_name;
							$option .= '</option>';
							echo $option;
						  }
						 ?>
						</select>
					</td>
				</tr>
			</table>
			
			<div>
			<textarea name="kgp_content" id="kgp_content" style="width:100%;height:200px;"></textarea>
			<table style="width:100%;margin-top:10px;">
				<tr>
					<td style="width:20%;vertical-align: top;">Name</td>
					<td style="width:80%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $kishGuestPost->userDisplayName; ?>" id="kgp_author_name" placeholder="Your Name Here"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Email</td>
					<td style="width:80%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $kishGuestPost->userEmail; ?>" id="kgp_author_email" placeholder="Your Email Here - Don't Worry its Safe"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Website URL</td>
					<td style="width:80%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $kishGuestPost->userURL; ?>" id="kgp_author_url" placeholder="Your Website URL"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Your Profile</td>
					<td style="width:80%;vertical-align: top;"><textarea id="kgp_author_profile" placeholder="Your Short Profile" style="width:100%;height:100px;"><?php echo $kishGuestPost->userProfile;  ?></textarea></td>
				</tr>
			</table>
			<div style="float:right;margin:5px 0px 10px 0px;">
				<?php $nonce= wp_create_nonce  ('kgp_nonce'); ?>
				<span id="kgp_preview_butt"><input type="button" value="Preview Post" onclick="kgp_preview_post('<?php echo $nonce; ?>');"></span>
				<span id="kgp_discard_butt"><input type="button" value="Discard Post" onclick="kgp_discard_post('<?php echo $nonce; ?>');"></span>
			</div>
			</div>
		</div>
	</div>
	<?php }
}
function kgp_edit_gp($postid) { 
	$current_user = wp_get_current_user();
	global $post;
	query_posts('p='.$postid); 
	if ( have_posts() ) : while ( have_posts() ) : the_post();
		$posttags = get_the_tags();
	$kgpost=$post;
	endwhile; else:
	endif;
	wp_reset_query();
	if ($posttags) {
		foreach($posttags as $tag) {
		    $tags = $tag->name . ','; 
		}
	}
	$tags=substr($tags,0, -1);
	$cats= get_the_category( $postid );
	$kgpcategory=$cats[0]->cat_ID;	?>
	<div id="kgp_post_form">
		<div style="margin:5px 0px 10px 0px" id="kgp_post_preview"></div>
		<div id="kgp_post_tools">
			<div id="fileQueue"></div>
			<input type="file" name="uploadify" id="uploadify" />
			<p><a href="javascript:jQuery('#uploadify').uploadifyClearQueue()">Cancel All Uploads</a></p>
			<div id="image_upload_results"></div>
			<table style="width:100%">
				<tr>
					<td style="width:20%;vertical-align: top;">Title</td>
					<td style="width:80%;vertical-align: top;"><input id="kgp_post_title" type="text" style="width:100%" value="<?php echo $post->post_title; ?>"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Tags :</td>
					<td style="width:80%;vertical-align: top;"><input id="kgp_post_tags" type="text" style="width:100%" value="<?php echo $tags; ?>"></td>
				</tr>
				<tr>
					<td>Category :</td>
					<td><select id="kgp_post_cats"> 
						 <option value=""><?php echo attribute_escape(__('Select A Category')); ?></option> 
						 <?php 
						 $arr=array('type'=> 'post','hide_empty' => 0,'orderby'=> 'name');
						  $categories=  get_categories($arr); 
						  foreach ($categories as $category) {
						  	if($category->term_id==$kgpcategory) {
						  		$option = '<option SELECTED value="'.$category->term_id.'">';
						  	}
						  	else {
						  		$option = '<option value="'.$category->term_id.'">';
						  	}
							$option .= $category->cat_name;
							$option .= '</option>';
							echo $option;
						  }
						 ?>
						</select>
					</td>
				</tr>
			</table>
			
			<div>
			<textarea name="kgp_content" id="kgp_content" style="width:100%;height:200px;"><?php echo $post->post_content; ?></textarea>
			<table style="width:100%;margin-top:10px;">
				<tr>
					<td style="width:20%;vertical-align: top;">Name</td>
					<td style="width:80%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $current_user->display_name; ?>" id="kgp_author_name" placeholder="Your Name Here"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Email</td>
					<td style="width:80%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $current_user->user_email; ?>" id="kgp_author_email" placeholder="Your Email Here - Don't Worry its Safe"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Website URL</td>
					<td style="width:80%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $current_user->user_url; ?>" id="kgp_author_url" placeholder="Your Website URL"></td>
				</tr>
				<tr>
					<td style="width:20%;vertical-align: top;">Your Profile</td>
					<td style="width:80%;vertical-align: top;"><textarea id="kgp_author_profile" placeholder="Your Short Profile" style="width:100%;height:100px;"><?php echo get_user_meta($current_user->ID, 'description', true);  ?></textarea></td>
				</tr>
			</table>
			<div>
				<input type="hidden" id="kgp_edit_post_id" value="<?php echo $postid; ?>">
				<?php $nonce= wp_create_nonce  ('kgp_nonce'); ?>
				<span id="kgp_preview_butt"><input type="button" value="Preview Edit" onclick="kgp_preview_post('<?php echo $nonce; ?>', '<?php echo $postid; ?>');"></span>
				<span id="kgp_discard_edit_butt"><input type="button" value="Discard Edit" onclick="kgp_discard_edit('<?php echo $nonce; ?>');"></span>
			</div>
			</div>
		</div>
	</div>
<?php }
function kgp_load_author_posts($args) {
	global $kishGuestPost;
	if(!strlen($args['status'])) {
		$status='pending, publish, draft, future, trash';
	}
	else {
		$status=$args['status'];
	}
	$nonce= wp_create_nonce  ('kgp_nonce');
	$numposts=100;
	if(!strlen($args['page'])) {
		$page=0;
	}
	else {
		$page=$args['page'];
	}
	global $post;
	$offset=$page*$numposts;
	$current_user = wp_get_current_user();
	$myposts = get_posts('author='.$current_user->ID.'&numberposts='.$numposts.'.&post_status='.$status.'.&offset='.$offset); ?>
	<div>
	<span style="margin-left:0px;" id="kgp_manage_posts_butt"><a href="#" onclick="kgp_manage_posts('<?php echo $nonce; ?>', '', 0);return false;">All</a></span>
	<span style="margin-left:10px;" id="kgp_manage_posts_buttpublish"><a href="#" onclick="kgp_manage_posts('<?php echo $nonce; ?>', 'publish', 0);return false;">Only Published</a></span>
	<span style="margin-left:10px;" id="kgp_manage_posts_buttpending"><a href="#" onclick="kgp_manage_posts('<?php echo $nonce; ?>', 'pending', 0);return false;">Pending</a></span>
	<span style="margin-left:10px;" id="kgp_manage_posts_buttdraft"><a href="#" onclick="kgp_manage_posts('<?php echo $nonce; ?>', 'draft', 0);return false;">Drafts</a></span>
	<span style="margin-left:10px;" id="kgp_manage_posts_butttrash"><a href="#" onclick="kgp_manage_posts('<?php echo $nonce; ?>', 'trash', 0);return false;">Rejected</a></span>
	<span style="margin-left:10px;" id="kgp_manage_posts_buttfuture"><a href="#" onclick="kgp_manage_posts('<?php echo $nonce; ?>', 'future', 0);return false;">Scheduled</a></span>
	</div>
	<table style="width:100%">
	<?php foreach($myposts as $post) :
	//setup_postdata($post);?>
	<?php //print_r($post); ?>
	<?php $arrstatus=kgp_post_status_style($post->post_status);?>
	<tr style="background:<?php echo $arrstatus['bg'] ?>;">
		<td style="width:80%"><?php echo $post->post_title; ?></td>
		<td style="width:20%"><?php echo $arrstatus['status']; ?></td>
		<?php if(!$kishGuestPost->allowPubPostEdit && $post->post_status=='publish') : ?>
		<td style="width:20%">Locked</td>
		<?php elseif($post->post_status=='trash') : ?>
		<td style="width:20%">Locked</td>
		<?php else : ?>
		<td id="kgp_edit_post_id_<?php echo $post->ID; ?>" style="width:20%"><a href="#" onclick="kgp_load_posteditform('<?php echo $nonce; ?>', '<?php echo $post->ID; ?>'); return false;">Edit</a></td>
		<?php endif; ?>
	</tr>
	<?php endforeach; ?>
	</table>
<?php }
function kgp_post_status_style($status) {
	switch($status) {
	case 'publish' :
	return array('bg'=>"#FFFFFF", 'status'=>"Published") ;
	break;
	case 'draft' :
	return array('bg'=>"#EFF8A3", 'status'=>"Draft - Edit & Resubmit") ;
	break;
	case 'pending' :
	return array('bg'=>"#B7F8A3", 'status'=>"Pending Moderation") ;
	break;
	case 'trash' :
	return array('bg'=>"#F8A3A3", 'status'=>"Rejected") ;
	break;
	case 'future' :
	return array('bg'=>"#A3BBF8", 'status'=>"Scheduled") ;
	break;
	default  :
	return array('bg'=>"#FFFFFF", 'status'=>"?") ;
	break;
	}
}
function kgp_login_form() { ?>
	<?php $nonce= wp_create_nonce  ('kgp_nonce'); ?>
	<table style="width:70%">
		<tr>
			<td style="width:30%;">User Name</td>
			<td><input style="width:100%" type="text" id="kgp_login_uname"></td>
		<tr>
			<td style="width:30%;">Password</td>
			<td><input style="width:100%" type="password" id="kgp_login_pword"></td>
		</tr>
		<tr>
			<td style="color:#CA0000" id="kgp_login_pdiv"></td>
			<td id="kgp_login_button"><input type="button" value="Login" onclick="kgp_process_login('<?php echo $nonce; ?>')"></td>
		</tr>
	</table>
<?php }
function kgp_reg_form() { 
	global $kishGuestPost;
	$kishGuestPost->loadSettings();
	$nonce= wp_create_nonce  ('kgp_nonce'); 
	if($kishGuestPost->checkAttempts('New Registration')) :
		echo "New Registration Disabled!! Try after Sometime:(";
	else :
	?>
	<table style="width:70%">
		<tr>
			<td style="width:30%;">User Name</td>
			<td><input style="width:100%" type="text" id="kgp_reg_uname"></td>
		</tr>
		<tr>
			<td style="width:30%;">Email</td>
			<td><input style="width:100%" type="text" id="kgp_email"></td>
		</tr>
		<tr>
			<td style="width:30%;color:#CA0000" id="kgp_reg_pdiv"></td>
			<td id="kgp_reg_button">
				<input type="button" value="Register" onclick="kgp_process_reg('<?php echo $nonce; ?>')">
				<input type="button" value="Cancel" onclick="kgp_init('<?php echo $nonce; ?>')">
			</td>
		</tr>
	</table>
	<?php endif; 
}
function kgp_process_login($username, $password) {
	if(wp_login( $username, $password)) {
		global $using_cookie;
		$using_cookie = true;
		wp_setcookie($username, $password, $using_cookie);
		echo 'goahead';
	}
	else {
		echo "Sorry - Wrong Login details";
	}
}
function kgp_process_reg($user_name, $user_email) {
	require_once(ABSPATH . WPINC . '/registration.php');
	if(!strlen($user_name)) {
		echo "Invalid Username!!";
		exit;
	}
	if(is_email( $user_email )) {
		$user_id = username_exists( $user_name );
		if($user_id) {
			echo "Username Already Taken!";
			exit;
		}
		$emailuid = email_exists($user_email);
		if($emailuid) {
			echo "This Email is already registered !!";
			exit;
		}
		if ( !$user_id && !$emailuid) {
			$random_password = wp_generate_password( 12, false );
			$user_id = wp_create_user( $user_name, $random_password, $user_email );
			if($user_id >0) {
				global $kishGuestPost;
				$kishGuestPost->loadSettings();
				$admin_email = $kishGuestPost->adminEmail;
				$blogname = $kishGuestPost->siteName;
				$loginurl = $kishGuestPost->siteURL.'?page_id='.get_option('kgp_gp_page');
				$headers = 'From: '.$blogname.'<'.$admin_email.'>' . "\r\n";
				$headers .= 'Bcc: '.$admin_email . "\r\n";
				$message="Hi {$user_name}\n\n";
				$message.="Your Loging Details\n\n";
				$message.="Username : {$user_name}\n\n";
				$message.="Password : {$random_password}\n\n";
				$message.="Login Url : {$loginurl}\n\n\n\n";
				$message.="Cheers!!\n\n";
				$kgp_domain=str_replace('http://','',$kishGuestPost->siteURL);
				$kgp_domain=str_replace('www','',$kgp_domain);
				setcookie("kgp_registered", "registered", time()+3600, "/", $kgp_domain );
				$kishGuestPost->insertLog($user_name, $user_email, 'New Registration');
		   		wp_mail($user_email, 'Login Details for '.get_option('blogname'), $message, $headers);
		   		echo "Please check your email for further instructions!!";
			}
		} 
		else {
			echo "Username already registered, try another!!";
		}
	}
	else {
		echo "Invalid Email";
	}

}
function kgp_profile_form() {
	global $kishGuestPost;
	$kishGuestPost->loadCurrentUser();
	$nonce= $kishGuestPost->getNonce(); 
	if (!$kishGuestPost->isUserLoggedIn) :
		echo "Please Login to view your profile..";
	else : ?> 
	<div id ="kishpress_profile">
	<?php if(!$kishGuestPost->changedPassword) : ?>
		<p class="kgp_error">You have not changed the auto-generated password.Please change to something that you may remember.</p>
	<?php endif; ?>
	<table align="center" width="80%">
		<tr>
			<td style="width:25%;vertical-align: top;">User Name</td>
			<td style="width:75%;vertical-align: top;"><?php echo $kishGuestPost->userName; ?></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Email</td>
			<td style="width:75%;vertical-align: top;" id="kpemail" ><?php echo $kishGuestPost->userEmail; ?>
			<input type="hidden" id="kpemailhidden" value="<?php echo $kishGuestPost->userEmail; ?>">
			</td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">First Name</td>
			<td style="width:75%;vertical-align: top;"><input id="kpfname" style="width:100%;" type="text" value="<?php echo $kishGuestPost->userFirstName; ?>"></input></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Last Name</td>
			<td style="width:75%;vertical-align: top;"><input id="kplname" style="width:100%;" type="text" value="<?php echo $kishGuestPost->userLastName; ?>"></input></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Display Name</td>
			<td style="width:75%;vertical-align: top;"><input id="kgpdisplayname" style="width:100%;" type="text" value="<?php echo $kishGuestPost->userDisplayName; ?>"></input></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Password</td>
			<td style="width:75%;vertical-align: top;"><input id="kppassword" style="width:100%;" type="password"></input></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Password Repeat</td>
			<td style="width:75%;vertical-align: top;"><input id="kppasswordrepeat" style="width:100%;" type="password"></input></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Website URL</td>
			<td style="width:75%;vertical-align: top;"><input type="text" style="width:100%;" value ="<?php echo $kishGuestPost->userURL; ?>" id="kgp_author_url"></td>
		</tr>
		<tr>
			<td style="width:25%;vertical-align: top;">Your Profile</td>
			<td style="width:75%;vertical-align: top;"><textarea id="kgp_author_profile" placeholder="Your Short Profile" style="width:100%;height:100px;"><?php echo $kishGuestPost->userProfile;  ?></textarea></td>
		</tr>
		<tr>
			<td id="kp_progress" style="color:#CA0000;font-weight:bold"></td>
			<input type="hidden" id="kmpsecurity" value="<?php echo $nonce; ?>">
			<input type="hidden" id="kmpuserid" value="<?php echo $kishGuestPost->userID; ?>">
			<td style="padding:5px">
			<input type="button" value="Update Profile" onclick="kgp_save_profile('<?php echo $nonce; ?>');">
			</td>
			</tr>
	</table>
	</div>
	<?php endif; ?>
<?php }
function kgp_replace_variables($msg) {
	global $current_user;
   	get_currentuserinfo();
   	if ($current_user->ID > 0) {
	   	$msg=trim(str_replace(array("\r\n", "\n", "\r"),'\n',$msg));
	   	$arrchangables = array('display_name', 'user_firstname', 'user_lastname', 'user_email');
	   	foreach($arrchangables as $var) {
	   		$msg=str_replace('%'.$var.'%', $current_user->$var, $msg);
	   	}
	}
	else {
		$msg=trim(str_replace(array("\r\n", "\n", "\r"),'\n',$msg));
	   	$arrchangables = array('display_name', 'user_firstname', 'user_lastname', 'user_email');
	   	foreach($arrchangables as $var) {
	   		$msg=str_replace('%'.$var.'%', "", $msg);
	   	}
	}
   	return $msg;
}
function kgp_update_profile($arr) {
	global $kishGuestPost;
	if($kishGuestPost->saveUserInfo($arr)) {
		echo "Profile Updated";
	}
	else {
		echo "Error";
	}
}
function kgp_wp_logout() {
	wp_logout();
	echo 'loggedout';
}
function kgp_img_folder_path() {
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	$uploads = wp_upload_dir(); 
	$uploadpath = $uploads['path'];
	$imgfolder=str_replace($root.'/','',$uploadpath);
	echo '../'.$imgfolder;
}
function kgp_create_db() {
	global $kishGuestPost;
	$kishGuestPost->createDB();
}
function kgp_install() {
	kgp_create_db();
	kgp_default_settings();
}
function kgp_insert_log($name, $email, $attempt) {
	global $kishGuestPost;
	$kishGuestPost->insertLog($name, $email, $attempt);
}
function kgp_default_settings() {
	global $kishGuestPost;
	$kishGuestPost->defaultSettings();
}
function kgp_send_notification_post_publish($post) {
	global $kishGuestPost;
	$kishGuestPost->loadSettings();
	if($kishGuestPost->emailAuthorPostPub) {
		//$kishGuestPost->loadPost($post);
		//print_r($kishGuestPost->postType);
		//exit;
	}
	return $post;
}
add_action('pending_to_publish', 'kgp_send_notification_post_publish');
add_action('draft_to_publish', 'kgp_send_notification_post_publish');
function kgp_get_folder_path() {
	$url = plugins_url( 'editor/', __FILE__ );
	$icon = $url . 'nicEditorIcons.gif';
	return $icon;
}
?>