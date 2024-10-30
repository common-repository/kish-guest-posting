<?php
/*
 * Author : Kishore Asokan (kishore@asokans.com)
 * For reuse permission, please contact me
 * http://www.asokans.com
 */
class kishGP {  	
   	var $siteName, $siteURL, $adminEmail, $blogDefaultRole; 
   	var $userID, $userName, $userFirstName, $userLastName, $userDisplayName, $userProfile, $userURL, $userEmail, $userPassword, $changedPassword;
	var $nonce, $settingsHead; 
   	var $spamCheck, $allowGP, $blockWPAdmin, $addProfTopPost, $addProfBottomPost, $allowPubPostEdit, $emailAdminPostSubmit;
   	var $userSubmissionCapping, $userRegCapping;
   	var $emailAdminPubPostEdited, $emailAuthorPostPub, $authorProfile, $initMsgVistor, $initMsgLogged;
   	var $msgNewPost, $msgEditPost, $msgManageProf, $msgManagePosts, $msgLoginPage, $msgRegPage, $msgTerms;
   	var $isUserLoggedIn;
   	var $postID, $postTitle, $postContent, $postDate, $postPermalink, $postGuestAuthor, $postAuthor, $postAuthorUrl, $postAuthorProfile, $postAuthorEmail, $postStatus, $postTags, $postCategory, $postType;

   	public function kishGP() {      
   		$this->settingsHead = '<h2>Kish Guest Posting Settings</h2>';
   		$this->adminEmail = get_option('admin_email');
   		$this->blogDefaultRole = get_option('default_role');
   		$this->siteName = get_option('blogname');
 		$this->siteURL = get_option('siteurl');
		$args = func_get_args();
		if($args) {
			$this->url=$args[0];
		}
   	}
   	public function loadSettings() {
   		$this->spamCheck = get_option('kgp_akismet_check')=='on' ? true : false;
   		$this->allowGP = get_option('kgp_allow_gp')=='on' ? true : false;   
   		$this->blockWPAdmin = get_option('kgp_block_admin')=='on' ? true : false;   
   		$this->addProfTopPost = get_option('kgp_author_prof_top')=='on' ? true : false;   
   		$this->addProfBottomPost = get_option('kgp_author_prof_bottom')=='on' ? true : false;   
   		$this->allowPubPostEdit = get_option('kgp_allow_edit_published_post')=='on' ? true : false;   
   		$this->emailAdminPostSubmit = get_option('kgp_send_email_post_submit')=='on' ? true : false;
   		$this->emailAdminPubPostEdited = get_option('kgp_send_email_published_post_edited')=='on' ? true : false;
   		$this->emailAuthorPostPub = get_option('kgp_send_email_author')=='on' ? true : false;  
   		$this->userSubmissionCapping = get_option('kgp_post_submission_capping'); 
   		$this->userRegCapping = get_option('kgp_user_reg_capping'); 
   		$this->initMsgVistor = get_option('kgp_msg_init'); 
   		$this->initMsgLogged = get_option('kgp_msg_init_logged'); 
   		$this->authorProfile = get_option('kgp_post_author_profile'); 
   		$this->msgNewPost = get_option('kgp_msg_newpost'); 
   		$this->msgEditPost = get_option('kgp_msg_editpost');
   		$this->msgManageProf = get_option('kgp_msg_manageprofile');
   		$this->msgManagePosts = get_option('kgp_msg_manageposts');
   		$this->msgLoginPage = get_option('kgp_msg_loginpage');
   		$this->msgRegPage = get_option('kgp_msg_newreg');
   		$this->msgTerms = get_option('kgp_terms');
   	}
   	public function loadCurrentUser() {
   		$current_user = wp_get_current_user();
	   	if ( 0 == $current_user->ID ) {
		    $this->isUserLoggedIn = false;
		} else {
		    $this->isUserLoggedIn = true;
		    $this->userID = $current_user->ID;
			$this->userPassword = $current_user->user_pass;
		    $this->userName = $current_user->user_login;
		    $this->userFirstName = $current_user->user_firstname;
		    $this->userLastName = $current_user->user_lastname;
		    $this->userEmail = $current_user->user_email;
		    $this->userDisplayName = $current_user->display_name;
		    $this->changedPassword = get_user_meta($current_user->ID, 'kgp_pw_changed', true)=='changed' ? true : false;
		    $this->userProfile = get_user_meta($current_user->ID, 'description', true);
		    $this->userURL = $current_user->user_url;
		}
   	}
	public function saveUserInfo($arr) {
		$this->loadCurrentUser();
	   	if ($arr['kmpuserid']!=$this->userID ) {
		    return false;
		} 
		else {
			$userinfo['ID']=$this->userID;
		   	if(strlen($arr['pword'])) {
				$userinfo['user_pass']=$arr['pword'];
				update_user_meta( $this->userID, 'kgp_pw_changed', 'changed' );
			}
			if(strlen($arr['kpfname'])) {
				$userinfo['first_name']=$arr['kpfname'];
			}
			if(strlen($arr['kplname'])) {
				$userinfo['last_name']=$arr['kplname'];
			}
			if(strlen($arr['kgpdisplayname'])) {
				$userinfo['display_name']=$arr['kgpdisplayname'];
			}
			if(strlen($arr['kgp_author_url'])) {
				$userinfo['user_url']=$arr['kgp_author_url'];
			}
			if(strlen($arr['kgp_author_profile'])) {
				update_user_meta( $this->userID, 'description', $arr['kgp_author_profile'] );
			}
			require_once(ABSPATH . WPINC . '/registration.php');
			if(wp_update_user($userinfo)==$this->userID) {
				$this->loadCurrentUser();
				return true;
			}
			else {
				return false;
			}
		}
	}
	public function createDB() {
		global $wpdb;
		$sql="CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kgp_logs` (
		`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`IP` VARCHAR( 100 ) NOT NULL ,
		`time` BIGINT NOT NULL ,
		`name` VARCHAR( 100 ) NOT NULL ,
		`email` VARCHAR( 100 ) NOT NULL ,
		`attempt` VARCHAR( 100 ) NOT NULL
		) ENGINE = MYISAM ;";
		if(mysql_query($sql, $wpdb->dbh)) {}
		else { echo "Error Creating Table"; }
		echo $sql;
	}
   	public function setNonce() { $this->nonce = wp_create_nonce  ('kgp_nonce'); }
	public function getNonce() { 
		$this->setNonce();
		return $this->nonce;  
	}
	public function saveSettings($args) {
		foreach($args as $key=>$value) {
			if($key!='req') {
				update_option($key, $value);
			}
		}
		$this->loadSettings();
	}
	public function getPost($postid) {
		$thispost = get_post($postid); 
		return $thispost;
	}
	public function loadPost($args) {
		$this->postID = $args->ID;
		$this->postTitle = $args->post_title;
		$this->postContent = $args->post_content;
		$this->postDate = $args->post_date;
		$this->postPermalink = $args->guid;
		$this->postAuthor = $args->post_author;
		$this->postGuestAuthor = get_post_meta($this->postID, 'kgp_author_name',true);
		$this->postAuthorUrl = get_post_meta($this->postID, 'kgp_author_url',true);
		$this->postAuthorProfile = get_post_meta($this->postID, 'kgp_author_profile',true);
		$this->postAuthorEmail = get_post_meta($this->postID, 'kgp_author_email',true);
		$this->postStatus = $args->post_status;
		$this->postCategory = get_the_category($this->postID);
		$this->postType = get_the_tags($this->postID);
		$this->postTags = $args->ID;
	}
	public function savePost($args) {
		$status='pending';
		if($args['postid']>0) {
			$postid = $args['postid'];
			$thispost = $this->getPost($postid); 
			$status = $thispost->post_status;
		}
		$my_post = array('ID' => $args['postid'],'post_category' =>array($args['kgp_post_cats']), 'post_content' => stripslashes($args['content']), 'post_status' => $status, 'post_title' => stripslashes($args['kgp_post_title']), 'post_type' => 'post', 'tags_input' => $args['kgp_post_tags']);
		$success=wp_insert_post( $my_post , true);
		return $success;
	}
	public function updateAuthorInfo($args, $postid) {
		update_post_meta($postid, 'kgp_author_email', $args['kgp_author_email'] );
		update_post_meta($postid, 'kgp_author_url', $args['kgp_author_url'] );
		update_post_meta($postid, 'kgp_author_profile', $args['kgp_author_profile'] );
		update_post_meta($postid, 'kgp_author_name', $args['kgp_author_name'] );
	}
	public function sendNotificationsPostSubmit($args) {
		if($this->emailAuthorPostPub) {
			$user_email = $args['kgp_author_email'];
			$headers = 'From: '.$this->siteName.'<'.$this->adminEmail.'>' . "\r\n";
			$headers .= 'Bcc: '.$admin_email . "\r\n";
			$message="Hi ".$args['kgp_author_name']."\n\n";
			$message.="Thank you for your submission\n\n";
			$message.="Post Title : ".stripslashes($args['kgp_post_title'])."\n\n";
			$message.="We will update you once the post is published!!\n\n\n\n";
			$message.="Cheers!!\n\n";
			$kgp_domain=str_replace('http://','',get_bloginfo('url'));
			$kgp_domain=str_replace('www','',$kgp_domain);
			setcookie("kgp_post_submitted", "submitted", time()+3600, "/", $kgp_domain );
			$this->insertLog($args['kgp_author_name'], $args['kgp_author_email'], 'Post Submission');
			wp_mail($user_email, 'Your Post Submission for '.get_option('blogname'), $message, $headers);
			echo "Post submitted for Moderations.";
		}
	}
	public function sendNotificationsPostPublish($post) {
		if($this->emailAuthorPostPub) {
			$user_email = $args['kgp_author_email'];
			$headers = 'From: '.$this->siteName.'<'.$this->adminEmail.'>' . "\r\n";
			$headers .= 'Bcc: '.$admin_email . "\r\n";
			$message="Hi ".$args['kgp_author_name']."\n\n";
			$message.="Thank you for your submission\n\n";
			$message.="Post Title : ".stripslashes($args['kgp_post_title'])."\n\n";
			$message.="We will update you once the post is published!!\n\n\n\n";
			$message.="Cheers!!\n\n";
			$kgp_domain=str_replace('http://','',get_bloginfo('url'));
			$kgp_domain=str_replace('www','',$kgp_domain);
			setcookie("kgp_post_submitted", "submitted", time()+3600, "/", $kgp_domain );
			$this->insertLog($args['kgp_author_name'], $args['kgp_author_email'], 'Post Submission');
			wp_mail($user_email, 'Your Post Submission for '.get_option('blogname'), $message, $headers);
			echo "Post submitted for Moderations.";
		}
	}
	public function insertLog($name, $email, $attempt) {
		global $wpdb;
		$ip=$this->getIpAddr();
		$sql="INSERT INTO `".$wpdb->prefix."kgp_logs` (IP, time, name, email, attempt) VALUES ('".$ip."', ".time().", '".$name."', '".$email."', '".$attempt."');";
		if(mysql_query($sql, $wpdb->dbh)) {		
		
		}
		else {
			echo "Error Saving log";
		}
	}
	public function defaultSettings() {
		if(strlen(get_option('kgp_user_reg_capping'))) {
			return false;
		}
		update_option('kgp_akismet_check', 'on');
		update_option('kgp_block_admin', 'on');
		update_option('kgp_author_prof_bottom', 'on');
		update_option('kgp_post_author_profile', '<p>This Guest Post is done by <a href="%kgp_author_url%">%kgp_author_name%</a></p><p>%kgp_author_profile%</p>');
		update_option('kgp_msg_init', 'Welcome to Guest Posting');
		update_option('kgp_msg_init_logged', 'Hi %display_name%');
		update_option('kgp_msg_newpost', 'Write new posts');
		update_option('kgp_msg_editpost', 'You can edit your posts and re-submit');
		update_option('kgp_msg_manageprofile', 'Create a strong profile to be shown on your post pages');
		update_option('kgp_msg_manageposts', 'You can edit the posts that you have submitted');
		update_option('kgp_msg_loginpage', 'Login and manage your posts');
		update_option('kgp_msg_newreg', 'You can register as user for this blog and manage your Guest Posts');
		update_option('kgp_terms', 'You agree to the terms and conditions of Guest Posting of this blog');
		update_option('kgp_post_submission_capping', 60);
		update_option('kgp_user_reg_capping', 3600);
	}
	public function getIpAddr() {
	    if (!empty($_SERVER['HTTP_CLIENT_IP']))   {
	      $ip=$_SERVER['HTTP_CLIENT_IP'];
	    }
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   {
	      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    else {
	      $ip=$_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}
	public function checkSpam ($content) {
		$isSpam = FALSE;
		$content = (array) $content;
		if (function_exists('akismet_init')) {
			$wpcom_api_key = get_option('wordpress_api_key');
			if (!empty($wpcom_api_key)) {
				global $akismet_api_host, $akismet_api_port;
				// set remaining required values for akismet api
				$content['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
				$content['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
				$content['referrer'] = $_SERVER['HTTP_REFERER'];
				$content['blog'] = get_option('home');
				if (empty($content['referrer'])) {
					$content['referrer'] = get_permalink();
				}
				$queryString = '';
				foreach ($content as $key => $data) {
					if (!empty($data)) {
						$queryString .= $key . '=' . urlencode(stripslashes($data)) . '&';
					}
				}
				$response = akismet_http_post($queryString, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
				if ($response[1] == 'true') {
					update_option('akismet_spam_count', get_option('akismet_spam_count') + 1);
					$isSpam = TRUE;
				}
			}
		}
		return $isSpam;
	}
	public function checkAttempts($attempt) {
		global $wpdb;
		$ip=$this->getIpAddr();
		$sql = "SELECT * FROM ".$wpdb->prefix."kgp_logs WHERE IP = '{$ip}' AND attempt = '{$attempt}' ORDER BY ID DESC LIMIT 0 , 1";
		$results=$wpdb->get_results($sql, OBJECT);
		if($results) {
			$attemptDiff = round(abs(time() - $results[0]->time) / 60,2);
			if($attempt=='Post Submission') {
				return $attemptDiff < $this->userSubmissionCapping ? true : false ;
			}
			if($attempt=='New Registration') {
				return $attemptDiff < $this->userRegCapping ? true : false ;
			}
		}
		else {
			return false;
		}
	}
	// Prints
	public function printTableOpen($style) {
		echo "<table style=\"{$style}\">";
	}
	public function printTableClose() {
		echo "</table>";
	}
}
?>