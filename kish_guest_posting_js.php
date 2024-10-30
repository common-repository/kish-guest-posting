<?php 
$root = dirname(dirname(dirname(dirname(__FILE__))));
include_once($root.'/wp-load.php');
$uploads = wp_upload_dir();
$kgp_domain=str_replace('http://','',get_bloginfo('url'));
$kgp_domain=str_replace('www','',$kgp_domain);
$arr = parse_url(get_bloginfo('url'));
?>
var kgp_loader = '<?php echo WP_PLUGIN_URL; ?>/kish-guest-posting/img/loader.gif';
var kgp_ajax_url='<?php echo WP_PLUGIN_URL; ?>/kish-guest-posting/kish-guest-posting-ajax.php';
var img_base='<?php echo $uploads['url']; ?>/';
var cursorPos=0;
var newPostData='';
var pageMode='';
var subPageLoaded=false;
var kgpEditor;
<?php if(is_user_logged_in()): ?>
var kgpInitMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_init_logged')); ?>';
<?php else: ?>
var kgpInitMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_init')); ?>';
<?php endif; ?>
var kgpNewPostMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_newpost')); ?>';
var kgpEditPostMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_editpost')); ?>';
var kgpManageProfMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_manageprofile')); ?>';
var kgpLoginPageMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_loginpage')); ?>';
var kgpNewRegMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_newreg')); ?>';
var kgpManagePostsMsg='<?php echo kgp_replace_variables(get_option('kgp_msg_manageposts')); ?>';
<?php if (isset($_COOKIE["kgp_registered"])) : ?>
var kgp_reg_block=true;
<?php else :?>
var kgp_reg_block=false;
<?php endif; ?>
var kgp_ref_url='<?php echo $arr['host']; ?>';
var kgp_rdiv_arr=new Array("kgp_resultdiv_submission", "kgp_resultdiv_posts", "kgp_resultdiv_forms", "kgp_resultdiv_profile"); 
jQuery(document).ready(function(){
	var security='<?php echo wp_create_nonce  ('kgp_nonce'); ?>';
	kgp_init(security);
});
function kgp_init(security) {
	dataToSend='req=initgppage&security=' + security;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_main").html(data);
		kgp_show_msg(kgpInitMsg);
	},"html");
}
function kgp_update_settings(security) {
	var progdiv=jQuery("#kgp_save_settings").html();
	jQuery("#kgp_save_settings").html('<img src=' + kgp_loader + '>');
	dataToSend='req=update_settings&kgp_gp_page=' + jQuery("#kgp_gp_page").val();
	dataToSend+='&kgp_allow_gp=' + jQuery('#kgp_allow_gp:checked').val();
	dataToSend+='&kgp_akismet_check=' + jQuery('#kgp_akismet_check:checked').val();
	dataToSend+='&kgp_block_admin=' + jQuery('#kgp_block_admin:checked').val();
	dataToSend+='&kgp_author_prof_top=' + jQuery('#kgp_author_prof_top:checked').val();
	dataToSend+='&kgp_author_prof_bottom=' + jQuery('#kgp_author_prof_bottom:checked').val();
	dataToSend+='&kgp_allow_edit_published_post=' + jQuery('#kgp_allow_edit_published_post:checked').val();
	dataToSend+='&kgp_send_email_post_submit=' + jQuery('#kgp_send_email_post_submit:checked').val();
	dataToSend+='&kgp_send_email_published_post_edited=' + jQuery('#kgp_send_email_published_post_edited:checked').val();
	dataToSend+='&kgp_post_submission_capping=' + jQuery('#kgp_post_submission_capping').val();
	dataToSend+='&kgp_user_reg_capping=' + jQuery('#kgp_user_reg_capping').val();
	dataToSend+='&kgp_send_email_author=' + jQuery('#kgp_send_email_author').val();
	dataToSend+='&kgp_post_author_profile=' + jQuery('#kgp_post_author_profile').val();
	dataToSend+='&kgp_msg_init=' + jQuery('#kgp_msg_init').val();
	dataToSend+='&kgp_msg_init_logged=' + jQuery('#kgp_msg_init_logged').val();
	dataToSend+='&kgp_msg_newpost=' + jQuery('#kgp_msg_newpost').val();
	dataToSend+='&kgp_msg_editpost=' + jQuery('#kgp_msg_editpost').val();
	dataToSend+='&kgp_msg_manageprofile=' + jQuery('#kgp_msg_manageprofile').val();
	dataToSend+='&kgp_msg_loginpage=' + jQuery('#kgp_msg_loginpage').val();
	dataToSend+='&kgp_msg_newreg=' + jQuery('#kgp_msg_newreg').val();
	dataToSend+='&kgp_msg_manageposts=' + jQuery('#kgp_msg_manageposts').val();
	dataToSend+='&kgp_terms=' + jQuery('#kgp_terms').val();
	dataToSend+='&security=' + security;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_save_settings").html(progdiv);
	},"html");
}
function kgp_load_new_post_form(security) {
	var progdiv=jQuery("#kgp_new_post_butt").html();
	var w=jQuery("#kgp_new_post_butt").width();
	jQuery("#kgp_new_post_butt").css("width", w+"px");
	jQuery("#kgp_new_post_butt").css("display", "inline-block");
	jQuery("#kgp_new_post_butt").html('<img src=' + kgp_loader + '>');
	dataToSend='req=postcapping&security=' + security;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		if(data=='true') {
			alert('You have already submitted a post few minutes back. You can submit only one post per hours');
			jQuery("#kgp_resultdiv_submission").html('You have already submitted a post few minutes back. You can submit only one post per hours');
			jQuery("#kgp_new_post_butt").html('');
			return false;
		}
		else {
		if(pageMode=='editPost') {
			if(!confirm("You are editing a post and not saved, Do you want to discard the edit?")) {
				return false;
			}
			kgp_discard_post(security);
		}
		pageMode='newPost';
		kgp_show_msg(kgpNewPostMsg);
		if(subPageLoaded && jQuery("#kgp_resultdiv_submission").html().length>100) {
			jQuery('#kgp_resultdiv_posts').slideUp('fast', function() {
				jQuery('#kgp_resultdiv_submission').slideDown('fast');
				kgp_show_resultDiv('kgp_resultdiv_submission');
				jQuery("#kgp_new_post_butt").html(progdiv);
			});	
			return false;
		}
		subPageLoaded=true;
		dataToSend='req=new_post_form';
		dataToSend+='&security=' + security;
		jQuery.post(kgp_ajax_url,dataToSend,function(data) {
			jQuery("#kgp_resultdiv_submission").html(data);
			kgp_show_resultDiv('kgp_resultdiv_submission');
			jQuery("#kgp_new_post_butt").html(progdiv);	
			kgp_editor_init();
			kgp_uploadify();
		},"html");
		}
	},"html");
	
}
function kgp_discard_post(security) {
	kgpEditor.removeInstance('kgp_content');
	pageMode='';
	subPageLoaded=false;
	kgp_load_new_post_form(security);
}
function kgp_discard_edit(security) {
	var w=jQuery("#kgp_discard_edit_butt").width();
	jQuery("#kgp_discard_edit_butt").css("width", w+"px");
	jQuery("#kgp_discard_edit_butt").css("display", "inline-block");
	jQuery("#kgp_discard_edit_butt").html('<img src=' + kgp_loader + '>');
	kgp_manage_posts(security, '', 0);
}
function kgp_load_posteditform(security, postid) {
	if(pageMode=='newPost') {
		if(!confirm("You are started working on a new post and have not saved? Do you want to discard the post?")) {
			return false;
		}
	}
	pageMode='editPost';
	kgp_show_msg(kgpEditPostMsg);
	if(subPageLoaded) {
		kgpEditor.removeInstance('kgp_content');
	}
	subPageLoaded=true;
	jQuery("#kgp_edit_post_id_"+postid).html('<img src=' + kgp_loader + '>');
	dataToSend='req=edit_post_form';
	dataToSend+='&security=' + security + '&postid=' + postid;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_resultdiv_submission").html(data);
		kgp_show_resultDiv('kgp_resultdiv_submission');
		kgp_editor_init();
		kgp_uploadify();
	},"html");
}
function kgp_uploadify() {
	jQuery("#uploadify").uploadify({
		'uploader'       : '<?php echo WP_PLUGIN_URL; ?>/kish-guest-posting/uploadify/scripts/uploadify.swf',
		'script'         : '<?php echo WP_PLUGIN_URL; ?>/kish-guest-posting/uploadify/scripts/uploadify.php',
		'cancelImg'      : '<?php echo WP_PLUGIN_URL; ?>/kish-guest-posting/uploadify/scripts/cancel.png',
		'folder'         : '<?php kgp_img_folder_path(); ?>',
		'queueID'        : 'fileQueue',
		'auto'           : true,
		'fileDesc'			:'Image Files Only',
		'fileExt'		 : '*.gif;*.jpg;*.png;*.jpeg',	
		'multi'          : true,
		onComplete: function(event, queueID, fileObj, response, data) {
			jQuery("#kgp_upload_prog").html('<img src=' + kgp_loader + '>');
			kgp_upload_image(fileObj.name, 'image_upload_results', 'k-post-prog-new-post');
		}
	});
}
function kgp_upload_image(file, resultdiv, progdiv) {
	jQuery("#image_upload_results").append('<a href="#"  onclick="kgp_insert_img(\''+img_base+file+'\');return false;"><img style="width:100px;height:auto;margin-right:5px;" src="'+img_base+file+'"></a>');
	jQuery("#kgp_upload_prog").html('');
}
function kgp_rewamp_editor() {
	kgpEditor.removeInstance('kgp_content');
}
function kgp_insert_img(url) {
	var text = '<img src="' + url +'">';
	var e = nicEditors.findEditor('kgp_content');
	var editingArea = e.getElm();
	var tcontent = e.getContent();
	var userSelection = e.getSel();
	editingArea.focus();
	if (document.selection) {
	   editingArea.focus();
	   userSelection.createRange().text = text;
   	}
    else {
		if (userSelection.getRangeAt) {
	       range = userSelection.getRangeAt(0);
	    }
	    else {
	        range = editingArea.ownerDocument.createRange();
	        range.setStart(userSelection.anchorNode, userSelection.anchorOffset);
	        range.setEnd(userSelection.focusNode, userSeletion.focusOffset);
      	}
      	var fragment = editingArea.ownerDocument.createDocumentFragment();
      	var wrapper = editingArea.ownerDocument.createElement('div');
      	wrapper.innerHTML = text;
      	while (wrapper.firstChild) {
        	fragment.appendChild(wrapper.firstChild);
      	}
      	range.deleteContents();
      	range.insertNode(fragment);
    }
}
function kgp_editor_init() {
	kgpEditor = new nicEditor({buttonList : ['bold','italic','underline','left','center', 'right', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'removeformat', 'link', 'unlink', 'fontFormat','xhtml'],iconsPath : '<?php echo kgp_get_folder_path(); ?>', maxHeight : 400}).panelInstance('kgp_content');
}
function kgp_save_post(security) {
	var kgpError='';
	var e = nicEditors.findEditor('kgp_content');
	var content = e.getContent();
	if(jQuery("#kgp_post_title").val().length<5) {
		kgpError='Please check the title of the post!!';
	}
	if(jQuery("#kgp_post_tags").val().length<3) {
		kgpError+='\nPlease check the tags of the post!!';
	}
	if(jQuery("#kgp_post_cats").val().length==0) {
		kgpError+='\nPlease select a category!!';
	}
	if(jQuery("#kgp_author_name").val().length==0) {
		kgpError+='\nPlease Enter the author name!!';
	}
	if(jQuery("#kgp_author_email").val().length==0) {
		kgpError+='\nPlease enter the author email!!';
	}
	if(jQuery("#kgp_author_profile").val().length==0) {
		kgpError+='\nPlease complete the Profile details!!';
	}
	if(content.length<100) {
		kgpError+='\nPlease enter some text in the post details!!';
	}
	if(kgpError.length>0) {
		alert(kgpError);
		kgp_show_post_form();
		return false;
	}
	if(jQuery('#kgp_accept_terms:checked').val()!='on') {
		alert('You should Agree to the Terms and condtions of Guest Posting!!');
		return false;
	}
	jQuery("#kgp_post_save_butt").html('<img src=' + kgp_loader + '>');
	dataToSend='req=save_new_post&kgp_post_title=' + jQuery("#kgp_post_title").val();
	dataToSend+='&kgp_post_tags=' + jQuery("#kgp_post_tags").val();
	dataToSend+='&kgp_post_cats=' + jQuery("#kgp_post_cats").val();
	dataToSend+='&content=' + content;
	dataToSend+='&kgp_author_name=' + jQuery("#kgp_author_name").val();
	dataToSend+='&kgp_author_email=' + jQuery("#kgp_author_email").val();
	dataToSend+='&kgp_author_url=' + jQuery("#kgp_author_url").val();
	dataToSend+='&kgp_author_profile=' + jQuery("#kgp_author_profile").val();
	dataToSend+='&security=' + security +'&postid=' + jQuery("#kgp_edit_post_id").val();
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_post_form").html(data);
		jQuery('html, body').animate({scrollTop:jQuery("#kgp_post_preview")}, 'slow');
		subPageLoaded=false;
		pageMode='postSaved';
		kgp_show_msg('Post Saved..');
	},"html");
}
function kgp_preview_post(security, postid) {
	var progdiv=jQuery("#kgp_preview_butt").html();
	var w=jQuery("#kgp_preview_butt").width();
	jQuery("#kgp_preview_butt").css("width", w+'px');
	jQuery("#kgp_preview_butt").css("display", "inline-block");
	jQuery("#kgp_preview_butt").html('<img src=' + kgp_loader + '>');
	var e = nicEditors.findEditor('kgp_content');
	var content = e.getContent();
	content = content.replace(/\&amp;/gi,"\%26");
	content = content.replace(/\&/gi,"\%26");
	dataToSend='req=preview_post&kgp_post_title=' + jQuery("#kgp_post_title").val();
	dataToSend+='&kgp_post_tags=' + jQuery("#kgp_post_tags").val();
	dataToSend+='&kgp_post_cats=' + jQuery("#kgp_post_cats").val();
	dataToSend+='&content=' + content;
	dataToSend+='&kgp_author_name=' + jQuery("#kgp_author_name").val();
	dataToSend+='&kgp_author_email=' + jQuery("#kgp_author_email").val();
	dataToSend+='&kgp_author_url=' + jQuery("#kgp_author_url").val();
	dataToSend+='&kgp_author_profile=' + jQuery("#kgp_author_profile").val();
	dataToSend+='&security=' + security + '&postid=' + postid;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery('#kgp_post_tools').slideUp('slow', function() {
		   	jQuery("#kgp_post_preview").html(data);
		   	jQuery('#kgp_post_preview').slideDown('slow');
			jQuery("#kgp_post_preview").html(data);
			jQuery("#kgp_preview_butt").html(progdiv);
		});
	},"html");
}
function kgp_show_post_form() {
	jQuery('#kgp_post_tools').slideDown('slow', function() {
		jQuery('#kgp_post_preview').slideUp('slow');
	});
}
function kgp_manage_posts(security, status, page) {
	var progdiv=jQuery("#kgp_manage_posts_butt"+status).html();
	var w=jQuery("#kgp_manage_posts_butt"+status).width();
	jQuery("#kgp_manage_posts_butt"+status).css("width", w+'px');
	jQuery("#kgp_manage_posts_butt"+status).css("display", "inline-block");
	jQuery("#kgp_manage_posts_butt"+status).html('<img src=' + kgp_loader + '>');
	jQuery("#kgp_manage_posts_butt"+status).width(w);
	dataToSend='req=showauthorposts&security=' + security + '&status=' + status + '&page=' + page;
	kgp_show_msg(kgpManagePostsMsg);
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		if(subPageLoaded) {
			jQuery("#kgp_resultdiv_posts").html(data);
			jQuery("#kgp_manage_posts_butt"+status).html(progdiv);
		}
		else {
			jQuery("#kgp_resultdiv_posts").html(data);
			jQuery("#kgp_manage_posts_butt"+status).html(progdiv);
		}
		kgp_show_resultDiv('kgp_resultdiv_posts');
	},"html");
}
function kgp_show_login_form(security) {
	kgp_show_msg(kgpLoginPageMsg);
	var progdiv=jQuery("#kgp_to_login_link").html();
	var w=jQuery("#kgp_to_login_link").width();
	jQuery("#kgp_to_login_link").css("width", w+'px');
	jQuery("#kgp_to_login_link").css("display", "inline-block");
	jQuery("#kgp_to_login_link").html('<img src=' + kgp_loader + '>');
	dataToSend='req=showloginform&security=' + security;
	kgp_show_resultDiv('kgp_resultdiv_forms');
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_resultdiv_forms").html(data);
		jQuery("#kgp_to_login_link").html(progdiv);
	},"html");
}
function kgp_show_reg_form(security) {
	if(kgp_reg_block) {
		alert('Registration Blocked - Try after sometime!!');
		kgp_show_msg('Registration Disabled!!');
		return false;
	}
	kgp_show_msg(kgpNewRegMsg);
	var progdiv=jQuery("#kgp_to_reg_form").html();
	var w=jQuery("#kgp_to_reg_form").width();
	jQuery("#kgp_to_reg_form").css("width", w+'px');
	jQuery("#kgp_to_reg_form").css("display", "inline-block");
	jQuery("#kgp_to_reg_form").html('<img src=' + kgp_loader + '>');
	dataToSend='req=showregform&security=' + security;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_resultdiv_forms").html(data);
		jQuery("#kgp_to_reg_form").html(progdiv);
		kgp_show_resultDiv('kgp_resultdiv_forms');
	},"html");
}
function kgp_show_resultDiv(resultDiv) {
	jQuery("#" + resultDiv).slideDown('slow', function() {
			for(var i in kgp_rdiv_arr) {
				var value = kgp_rdiv_arr[i];
				if(value==resultDiv) {
					//alert(value);
				}
				else {
					jQuery("#"+value).slideUp('slow');
				}
			}
		});
}
function kgp_process_reg(security) {
	if(jQuery("#kgp_reg_uname").val().length<=5) {
		alert('Username should be minimum 5 letters!!');
		jQuery("#kgp_reg_uname").focus();
		return false;
	}
	if(jQuery("#kgp_email").val().length<=3) {
		alert('Invalid Email!!');
		jQuery("#kgp_email").focus();
		return false;
	}
	var progdiv=jQuery("#kgp_reg_button").html();
	jQuery("#kgp_reg_button").html('<img src=' + kgp_loader + '>');
	dataToSend='req=processregistration&security=' + security + '&kgp_reg_uname=' +jQuery("#kgp_reg_uname").val()+ '&kgp_email=' + jQuery("#kgp_email").val();
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		if(data=='Please check your email for further instructions!!') {
			jQuery("#kgp_resultdiv").html(data);
			kgp_set_Cookie( 'kgp_registered', 'registered', 1, '/', document.domain, '' );
		}
		else {
			jQuery("#kgp_reg_pdiv").html(data);
			jQuery("#kgp_reg_button").html(progdiv);
		}
	},"html");
}
function kgp_wp_logout(security) {
	var w=jQuery("#kgp_logout_butt").width();
	jQuery("#kgp_logout_butt").css("width", w+'px');
	jQuery("#kgp_logout_butt").css("display", "inline-block");
	jQuery("#kgp_logout_butt").html('<img src=' + kgp_loader + '>');
	dataToSend='req=logout&security=' + security;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		if(data=='loggedout') {
			window.location='<?php echo get_option('siteurl').'?page_id='.get_option('kgp_gp_page'); ?>'; 
		}
	},"html");
}
function kgp_process_login(security) {
	var progdiv=jQuery("#kgp_login_button").html();
	jQuery("#kgp_login_button").html('<img src=' + kgp_loader + '>');
	dataToSend='req=processlogin&security=' + security + '&uname=' +jQuery("#kgp_login_uname").val()+ '&pword=' + jQuery("#kgp_login_pword").val();
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		if(data=='goahead') {
			window.location='<?php echo get_option('siteurl').'?page_id='.get_option('kgp_gp_page'); ?>'; 
		}
		else {
			jQuery("#kgp_login_pdiv").html(data);
			jQuery("#kgp_login_button").html(progdiv);
		}
	},"html");
}
function kgp_manage_profile(security) {
	kgp_show_msg(kgpManageProfMsg);
	var progdiv=jQuery("#kgp_manage_profile").html();
	var w=jQuery("#kgp_manage_profile").width();
	jQuery("#kgp_manage_profile").css("width", w+'px');
	jQuery("#kgp_manage_profile").css("display", "inline-block");
	jQuery("#kgp_manage_profile").html('<img src=' + kgp_loader + '>');
	dataToSend='req=manageprofile&security=' + security;
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kgp_resultdiv_profile").html(data);
		jQuery("#kgp_manage_profile").html(progdiv);
		kgp_show_resultDiv('kgp_resultdiv_profile');
	},"html");
}
function kgp_save_profile(security) {
	jQuery("#kp_progress").html('<img src=' + kgp_loader + '></img>');
	if(jQuery("#kppassword").val()!=jQuery("#kppasswordrepeat").val()) {
		alert('Passwords does not match');
		jQuery("#kp_progress").html('');
		return false;
	}
	dataToSend='req=editprofile&pword=' + jQuery("#kppassword").val() + '&pword2=' + jQuery("#kppasswordrepeat").val() + '&security=' + security + '&kpfname=' + jQuery("#kpfname").val() + '&kplname=' + jQuery("#kplname").val() + '&kmpuserid=' + jQuery("#kmpuserid").val();
	dataToSend+='&kgpdisplayname='+ jQuery("#kgpdisplayname").val() + '&kgp_author_url='+ jQuery("#kgp_author_url").val() + '&kgp_author_profile='+ jQuery("#kgp_author_profile").val();
	jQuery.post(kgp_ajax_url,dataToSend,function(data) {
		jQuery("#kp_progress").html(data);
	},"html");
}
// utils
function kgp_show_msg(msg) {
	jQuery('#kgp_msg').fadeTo('slow', 0.1, function() {
    	jQuery('#kgp_msg').html(msg);
      	jQuery('#kgp_msg').fadeTo('slow', 1.0);
    });
}
function kgp_set_Cookie( name, value, expires, path, domain, secure ) {
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() )
	if ( expires ) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name + "=" +escape( value ) +
	( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
	( ( path ) ? ";path=" + path : "" ) +
	( ( domain ) ? ";domain=" + domain : "" ) +
	( ( secure ) ? ";secure" : "" );
}