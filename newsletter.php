<?php
/*
Plugin Name: Wordpress Newsletter
Plugin URI: http://smallwebsitehost.com/wordpress-newsletter-plugin/wordpress
Description: Create a  form to collect subscription requests and send email to the mailing lists. 
Version: 1.0
Autdor: Ian Sani
Autdor URI: http://www.smallwebsitehost.com/

    Copyright 2008  Ian sani (email : yulianto@solusiwebindo.com)

    tdis program is free software; you can redistribute it and/or modify
    it under tde terms of tde GNU General Public License as published by
    tde Free Software Foundation; eitder version 2 of tde License, or
    (at your option) any later version.

    tdis program is distributed in tde hope tdat it will be useful,
    but WItdOUT ANY WARRANTY; witdout even tde implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See tde
    GNU General Public License for more details.

    You should have received a copy of tde GNU General Public License
    along witd tdis program; if not, write to tde Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$wpnewsletter_db_version = "1.0";

session_start();

if (empty($_GET['kei'])){
	register_activation_hook(__FILE__, 'wpnewsletter_install');
	add_action('admin_menu', 'wpnewsletter_add_menu');
}
else if(!empty($_GET['kei']))
{
	wpnewsletter_opt_in();
}
function wpnewsletter_add_menu() {
	add_options_page('Newsletter', 'Newsletter', 6, __FILE__, 'wpnewsletter_settings' );
}

function wpnewsletter_show_optin_form() {	

	if (!empty($_POST['wpnewsletter_email'])) {
	
		wpnewsletter_opt_in();
	}
	
		$out = '<form action="" method="post">';
		$out .= '<table width="100%"  bgcolor="#EBF3FE">';
		$out .= '<tr><td colspan=2>'. stripslashes(get_option('wpnewsletter_form_header')) .'</td></tr>';
		$out .= '<tr><td>Name:</td><td><input type="text" name="wpnewsletter_name" id="wpnewsletter_name"/></td></tr>';
		$out .= '<tr><td>Email:</td><td><input type="text" name="wpnewsletter_email" id="wpnewsletter_email"/></td></tr>';
		$out .= '<tr><td>Enter security code:<img src='.get_bloginfo('wpurl').'/wp-content/plugins/wordpress-newsletter/captcha.php?width=50&height=25&characters=5" /></td><td><input type="text" name="security_code" size="5"></td></tr>';			
		$out .= '<tr><td colspan=2 align=center><input type="submit" value="Subscribe"/></td></tr>';
		$out .= '<tr><td colspan=2>'. stripslashes(get_option('wpnewsletter_form_footer')) .'<br/><small>Powered by <a href="http://smallwebsitehost.com" target="_blank">Newsletter plugin</a></small></td></tr>';
		$out .='</table></form>';
		echo $out;

}

function wpnewsletter_show_optin_div() {	
	//if (!empty($_POST['wpnewsletter_email'])) {
	//
	//	wpnewsletter_opt_in();
	//}
	
	$blogname = get_option('blogname');
	if($_COOKIE[$blogname+"pop"]=='')
	{
		setcookie($blogname+"pop", "1");
	?>
	<div id=floating style="position:absolute;visibility:none;z-index:20000;float:left;top:150px;left:200px;">
		<table cellpadding=0 cellspacing=3 bgcolor="#ffff00" width="400px">
			<script>
			function hide()
			{
				document.getElementById('floating').style.display = 'none';
			}
			</script>
			<tr style="BACKGROUND: white" align="center">
				<td colspan=2><b>Subscribe my newsletter</b>&nbsp;<a href="javascript:hide();">[X]</a></td>
			</tr>
			<?php
				$out = '<form action="" method="post">';
				if(stripslashes(get_option('wpnewsletter_form_header')) != '')
					$out .= '<tr><td colspan=2  align="center">'. stripslashes(get_option('wpnewsletter_form_header')) .'</td></tr>';
				$out .= '<tr  align="center"><td>Name:</td><td><input type="text" name="wpnewsletter_name" id="wpnewsletter_name"/></td></tr>';
				$out .= '<tr  align="center"><td>Email:</td><td><input type="text" name="wpnewsletter_email" id="wpnewsletter_email"/></td></tr>';
				$out .= '<tr  align="center"><td>Enter security code:<img src="'.get_bloginfo('wpurl').'/wp-content/plugins/wordpress-newsletter/captcha.php?width=50&height=25&characters=5" /></td><td><input type="text" name="security_code" size="5"></td></tr>';			
				$out .= '<tr  align="center"><td colspan=2 align=center><input type="submit" value="Subscribe"/></td></tr  align="center">';
				$out .= '<tr><td colspan=2>'. stripslashes(get_option('wpnewsletter_form_footer')) .'<br/><small>Powered by <a href="http://smallwebsitehost.com" target="_blank">Newsletter plugin</a></small></td></tr>';
				$out .='</form>';
				echo $out;
			?>
			<tr>
				<td align="center"  bgcolor="#EBF3FE" colspan="2"><a href="javascript:hide();">Close [X]</a><br/>
				</td>
			</tr>
			</table>
	</div>
	<?php }
	ob_end_flush();
}

function wpnewsletter_getip() {
	if (isset($_SERVER)) {
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_addr = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} 
		elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip_addr = $_SERVER["HTTP_CLIENT_IP"];
		} 
		else {
			$ip_addr = $_SERVER["REMOTE_ADDR"];
		}
	} 
	else {
		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ip_addr = getenv( 'HTTP_X_FORWARDED_FOR' );
		} 
		elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ip_addr = getenv( 'HTTP_CLIENT_IP' );
		} 
		else {
			$ip_addr = getenv( 'REMOTE_ADDR' );
		}
	}
	
	return $ip_addr;
}

function wpnewsletter_opt_in() {
	global $wpdb;
	$table_users = $wpdb->prefix . "newsletter_users";

	//trim the email
	if (empty($_POST['wpnewsletter_email'])) {

		if (!empty($_GET['kei'])) {
			wpnewsletter_optin_confirm();
		}
		else {
			$_POST['wpnewsletter_email'] = trim($_POST['wpnewsletter_email']);
			wpnewsletter_show_optin_form();
		}
		
		
	} 
	else {
	
		$name = stripslashes($_POST['wpnewsletter_name']);
		$name  = checkValid($name );

		$email = stripslashes($_POST['wpnewsletter_email']);
		$email = checkValid($email);

		//replace name		
		$find = array('/ä/','/ö/','/ü/','/ß/','/Ä/','/Ö/','/Ü/','/ /','/[:;]/');

		$replace = array('ae','oe','ue','ss','Ae','Oe','Ue','_','');

		$name = preg_replace ($find , $replace, strtolower($name));


		if($name == "" || $email == "")
			return;
		
		$wpnewsletter_custom_flds = "";
		if (!preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $email)) {
				echo "Email format is incorrect";
				wpnewsletter_show_optin_form();
		}
		else {
			if( $_SESSION['security_code'] == $_POST['security_code'] && !empty($_SESSION['security_code'] ) ) {

				$email_from = stripslashes(get_option('wpnewsletter_email_from'));

				$subject = stripslashes(get_option('wpnewsletter_email_subject'));
				
				$message = stripslashes(get_option('wpnewsletter_email_message'));
				
				//create activation link
				$url = get_bloginfo('wpurl') .'/wp-content/plugins/wordpress-newsletter/newsletter.php?';
			
				$wpnewsletter_ip = wpnewsletter_getip();
				
				$url .= "kei=".md5($email.$name);

				$message = str_replace('*link*', $url, $message);
					
				$blogname = get_option('blogname');
		$header = "From: $email_from\n"
			. "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
				$selectqry = "SELECT * FROM " . $table_users . " WHERE `email` = '" . $email ."'";
				if ($wpdb->query($selectqry)) {
					echo stripslashes(get_option('wpnewsletter_msg_dup'));
				}
				else {
					if (mail($email,$subject,$message, $header)) {

							$query = "INSERT INTO " . $table_users . " 
								(joindate, ip, email, joinstatus, name) 
								VALUES (
								now(),
								'" . $wpnewsletter_ip . "',
								'" . $email . "',0,
								'" . $name . "'	)";
						 	$result = $wpdb->query($query);
							//echo($query);
						
						echo stripslashes(get_option('wpnewsletter_msg_sent'));
						
						//ob_start();					
						//$_COOKIE["newslettername"] = $name;

						//ob_end_flush();
					} 
					else {
						echo stripslashes(get_option('wpnewsletter_msg_fail'));
					}
				}
					unset($_SESSION['security_code']);
				return 0;
			} else {
				// Insert your code for showing an error message here
				echo 'Sorry, you have provided an invalid security code. Please try again.';
		   }
		}
	}
}

function wpnewsletter_optin_confirm() {
require_once('setting.php');
	$wpdb = "wp_";

	mysql_connect($dbhost, $dbuser, $dbpass) or die("koneksi gagal");
	mysql_select_db($dbname);
	
	$wpnewsletter_ip = $_GET['kei'];

	$wpnewsletter_ip = checkValid($wpnewsletter_ip );

	if($_GET['type']=='remove')
	{
		$sql = "SELECT * FROM `" . $dbprefix . "newsletter_users` WHERE MD5(CONCAT(`email`, `name`)) = '" . $wpnewsletter_ip ."'";

		$result = mysql_query($sql );

		if ($result) {
			$row = mysql_fetch_assoc($result);

			if($row['id'])
			{

				$update = "UPDATE " . $dbprefix . "newsletter_users SET `joinstatus` = '3' WHERE `id` = ". $row['id'];
				$result = mysql_query($update );

				echo("You are unsubscribed now!");
			}
			else
			{
				echo("Failed to verify your email. There is no such user.");
			}
		}
		else
		{
			echo("Failed to verify your email.");
		}
	}
	else
	{
		$sql = "SELECT * FROM `". $dbprefix . "newsletter_users` WHERE MD5(CONCAT(`email`, `name`)) = '" . $wpnewsletter_ip ."' AND `joinstatus` = '0'";

		$result = mysql_query($sql );

		if ($result) {
			$row = mysql_fetch_assoc($result);
		
			if($row['id'])
			{
				$update = "UPDATE " . $dbprefix . "newsletter_users SET `joinstatus` = '1' WHERE `id` = ". $row['id'];
				$result = mysql_query($update );

				echo("Thank you. You are subscribed now!");

				$table_users = $wpdb . "newsletter_users";
				$result = mysql_query("SELECT * FROM $table_users where `id` = ". $row['id']);				
				$row = mysql_fetch_assoc($result);
				$email_to = $row['email'];
				
				$result = mysql_query("select * from wp_options where option_name ='wpnewsletter_email_from'");
				$row = mysql_fetch_assoc($result);
				$email_from = $row['option_value'];

				//send email to subscriber
				$result = mysql_query("select * from wp_options where option_name ='blogname'");
				$row = mysql_fetch_assoc($result);
				$blogname = $row['option_value'];
				$result = mysql_query("select * from wp_options where option_name ='blog_charset'");
				$row = mysql_fetch_assoc($result);
				
				$headers = "MIME-Version: 1.0\n";
				$headers .= "From: $blogname <$email_from>\n";

				$result = mysql_query("select * from wp_options where option_name ='blogname'");
				$row = mysql_fetch_assoc($result);
				$blogname = $row['option_value'];

				$result = mysql_query("select * from wp_options where option_name ='wpnewsletter_email_subject_subscriber'");
				$row = mysql_fetch_assoc($result);
				$subject = stripslashes($row['option_value']);

				$result = mysql_query("select * from wp_options where option_name ='wpnewsletter_email_message_subscriber'");
				$row = mysql_fetch_assoc($result);
				$message = stripslashes($row['option_value']);

				$message = str_replace("*name*", $user->name, $message);
				$subject = str_replace("*name*", $user->name, $subject);

				$result = mysql_query("select * from wp_options where option_name ='siteurl'");
				$row = mysql_fetch_assoc($result);
				$url = $row['option_value'] .'/wp-content/plugins/wordpress-newsletter/newsletter.php?type=remove&';
							
				$url .= "kei=".md5($user->email.$user->name);

				$message .= "\n\nYou can unsubscribe at ". $url;
				if (mail($email_to,$subject,$message,$headers)) {
					//echo "Emailed to " . $user->email."<br/>";		
				}
				else
				{
					//echo("failed email " + $user->email);
				}

			}
			else
			{
				echo("Failed to verify your email.");
			}
		}
		else
		{
			echo("Failed to verify your email.");
		}
	}
}

function wpnewsletter_install() {
	global $wpdb;
	global $wpnewsletter_db_version;
	
	$table_users = $wpdb->prefix . "newsletter_users";

	if($wpdb->get_var("show tables like '$table_users'") != $table_users) {

		// Table does not exist; create new
		$sql = "CREATE TABLE `" . $table_users . "` (
  			`id` bigint(11) NOT NULL auto_increment,
  			`joindate` datetime NOT NULL,
  			`ip` varchar(50) NOT NULL default '',
  			`name` varchar(50) NOT NULL default '',
 			`email` varchar(100) NOT NULL default '',
			`joinstatus` int NOT NULL default 0,
  			UNIQUE KEY `id` (`id`)
		);";
		$result = $wpdb->query($sql);

		// Insert initial data in table
		$insert = "INSERT INTO `$table_users` (`joindate`, `ip`, `email`, `name`,`joinstatus`) " .
			"VALUES (now(),'" . wpnewsletter_getip() .
			"','" . get_option('admin_email') . "','admin',1)";
		$result = $wpdb->query($insert);

		add_option("wpnewsletter_db_version", $wpnewsletter_db_version);

		// default values
		$blogname = get_option('blogname');
		add_option('wpnewsletter_email_from', get_option('admin_email') );
		add_option('wpnewsletter_email_subject', "$blogname - Newsletter subscription");
		add_option('wpnewsletter_email_message', "Thanks you for subscribing our newsletter at $blogname.\n
You can verify your email at *link*.\n\n

www.smallwebsitehost.com");
		
		add_option('wpnewsletter_email_subject_subscriber', "$blogname - Your subscription");
		add_option('wpnewsletter_email_message_subscriber', "Thanks you. Now you are subsribed at $blogname.\n");

		add_option('wpnewsletter_msg_dup', "<p>E-mail address already subscribed.</p>");
		add_option('wpnewsletter_msg_fail', "<p>Failed sending to e-mail address.</p>");
		add_option('wpnewsletter_msg_sent', "<p>Thanks for subscribing. Please check your email to verify. Don't forgot to check your spam folder.</p>");

		add_option('wpnewsletter_form_header', "Opt-in form header");
		add_option('wpnewsletter_form_footer', "Opt-in form footer");
		add_option('wpnewsletter_form_email', "E-mail:");
		add_option('wpnewsletter_form_fields', array("wpnewsletter_radio_in"=>"Subscribe","wpnewsletter_radio_out"=>"Unsubscribe"));
		add_option('wpnewsletter_form_send', "Join");
	}
}

function checkValid($str)
{
	$valid_string = "[\\\"\*\^\'\;\&\>\<]";
	if(ereg($valid_string,$str))
	{
		echo("<br/>Invalid value:".$str."<br>");
		echo("<a href='javascript:history.go(-1)'>Try again<a>.<br/>");
		return "";
	}
	else
	{
		return $str;
	}
}

function wpnewsletter_settings() {

	global $wpdb;

	$table_users = $wpdb->prefix . "newsletter_users";

	// if $_GET['user_id'] set tden delete user from list
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];

		// Delete user from database
		$delete = "DELETE FROM " . $table_users .
				" WHERE id = '" . $user_id . "'";
		$result = $wpdb->query($delete);

		// Notify admin of delete
		echo '<div id="message" class="updated fade"><p><strong>';
		_e('User deleted.', 'wpnewsletter_domain');
		echo '</strong></p></div>';
	}
					
	// Get current options from database
	$email_from = stripslashes(get_option('wpnewsletter_email_from'));
	$email_subject = stripslashes(get_option('wpnewsletter_email_subject'));
	$email_message = stripslashes(get_option('wpnewsletter_email_message'));
	$email_subject_subscriber = stripslashes(get_option('wpnewsletter_email_subject_subscriber'));
	$email_message_subscriber = stripslashes(get_option('wpnewsletter_email_message_subscriber'));
	$msg_dup = stripslashes(get_option('wpnewsletter_msg_dup'));
	$msg_fail = stripslashes(get_option('wpnewsletter_msg_fail'));
	$msg_sent = stripslashes(get_option('wpnewsletter_msg_sent'));

	$form_header = stripslashes(get_option('wpnewsletter_form_header'));
	$form_footer = stripslashes(get_option('wpnewsletter_form_footer'));
	$form_email = stripslashes(get_option('wpnewsletter_form_email'));
	$form_fields = (get_option('wpnewsletter_form_fields'));
	$form_send = stripslashes(get_option('wpnewsletter_form_send'));

	// Update options if user posted new information
	if( $_POST['process'] == 'edit' ) {
		// Read from form
		$email_from = stripslashes($_POST['wpnewsletter_email_from']);
		$email_subject = stripslashes($_POST['wpnewsletter_email_subject']);
		$email_message = stripslashes($_POST['wpnewsletter_email_message']);
		
		$email_subject_subscriber = stripslashes($_POST['wpnewsletter_email_subject_subscriber']);
		$email_message_subscriber = stripslashes($_POST['wpnewsletter_email_message_subscriber']);

		$msg_dup = stripslashes($_POST['wpnewsletter_msg_dup']);
		$msg_fail = stripslashes($_POST['wpnewsletter_msg_fail']);
		$msg_sent = stripslashes($_POST['wpnewsletter_msg_sent']);

		$form_header = stripslashes($_POST['wpnewsletter_form_header']);
		$form_footer = stripslashes($_POST['wpnewsletter_form_footer']);
		$form_email = stripslashes($_POST['wpnewsletter_form_email']);
		$form_fields = is_array($_POST['wpnewsletter_form_fld']) ? $_POST['wpnewsletter_form_fld'] : array();
		$form_send = stripslashes($_POST['wpnewsletter_form_send']);

		// Save to database
		update_option('wpnewsletter_email_from', $email_from );
		update_option('wpnewsletter_email_subject', $email_subject);
		update_option('wpnewsletter_email_message', $email_message);

		update_option('wpnewsletter_email_subject_subscriber', $email_subject_subscriber);
		update_option('wpnewsletter_email_message_subscriber', $email_message_subscriber);

		update_option('wpnewsletter_msg_dup', $msg_dup);
		update_option('wpnewsletter_msg_fail', $msg_fail);
		update_option('wpnewsletter_msg_sent', $msg_sent);

		update_option('wpnewsletter_form_header', $form_header);
		update_option('wpnewsletter_form_footer', $form_footer);
		update_option('wpnewsletter_form_email', $form_email);
		update_option('wpnewsletter_form_fields', ($form_fields));
		update_option('wpnewsletter_form_send', $form_send);

		//notify change
		echo '<div id="message" class="updated fade"><p><strong>';
		_e('Settings saved.', 'wpnewsletter_domain');
		echo '</strong></p></div>';
	}
	else if( $_POST['process'] == 'email' ) {
		$email_from = stripslashes(get_option('wpnewsletter_email_from'));
		
		$subject = stripslashes($_POST['wpnewsletter_subject']);
		$message = stripslashes($_POST['wpnewsletter_message']);
		
		$headers = "MIME-Version: 1.0\n";
		$blogname = get_option('blogname');
		$headers .= "From: $email_from\n";
		$headers .= "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";

		$users = $wpdb->get_results("SELECT * FROM $table_users where joinstatus=1 ORDER BY `id` DESC");

		foreach ($users as $user) {
				$subject = stripslashes($_POST['wpnewsletter_subject']);
				$message = stripslashes($_POST['wpnewsletter_message']);

				$message = str_replace("*name*", $user->name, $message);
				$subject = str_replace("*name*", $user->name, $subject);

				$url = get_bloginfo('wpurl') .'/wp-content/plugins/wordpress-newsletter/newsletter.php?type=remove&';
			
				$wpnewsletter_ip = wpnewsletter_getip();
				
				$url .= "kei=".md5($user->email.$user->name);

				$message .= "\n\nYou can unsubscribe at ". $url;
				
				if (@wp_mail($user->email,$subject,$message,$headers)) {
					echo "Emailed to " . $user->email."<br/>";		
				}
				else
				{
					echo("failed email " + $user->email);
				}
		}
	}
	else if( $_POST['process'] == 'export' ) {
		$File = $_POST['savefile'];
		if($File =='')
		{
			echo ("File Name Not Valid");
			return;
		}
		$users = $wpdb->get_results("SELECT * FROM $table_users where joinstatus=1 ORDER BY `id` DESC");

		foreach ($users as $user) {
			$Data .= $user->name . "!";
			$Data .= $user->email .":";
			$Data .= "\n";
		}
		
			$Handle = fopen("$File", 'w');
			fwrite($Handle, $Data);
			fclose($Handle); 
	}
	else if( $_POST['process'] == 'import' ) {
	global $wpdb;
	$table_users = $wpdb->prefix . "newsletter_users";

		$File = $_POST['openfile'];
		if($File =='')
		{
			echo ("File Name Not Valid");
			return;
		}
			$handle = fopen("$File", "r");
			$contents = fread($handle, filesize($File));
			$arr = split(":", $contents);
			for($i=0;$i<count($arr)-1;$i++)
			{
				//echo($arr[$i]);
				$arr1 = split("!", $arr[$i]);
				//print_r($arr1);
				
				$insert = "INSERT INTO `$table_users` (`joindate`, `ip`, `email`, `name`,`joinstatus`) " .
			"VALUES (now(),'127.0.0.1','" . $arr1[1] . "','$arr1[0]',1)";
				$result = $wpdb->query($insert);

			}
			fclose($handle);

	}
?>
<div class="wrap">
<h2>Send email</h2>
	<form action="" method="post">
    <input type="hidden" name="process" value="email" />
	<table width="100%"><tr><td>Subject:</td><td><input type="text" name="wpnewsletter_subject" id="wpnewsletter_subject" size="100"/></td></tr>
	<tr><td>Message: <br/>Type <b>*name*</b> to set the username</td><td><textarea rows=10 cols=100 name="wpnewsletter_message" id="wpnewsletter_message"/></textarea></td></tr></table>
	<p class="submit"><input type="submit" value="Send Newsletter"/></p></form>	
	
</div>
<div class="wrap">
<?php 
	$typequery = $_GET['type'];
	if( $typequery == '0')
		echo ('<h2>Not Opted-in User</h2>');
	else if( $typequery == '1')
		echo ('<h2>Opted-in User</h2>');
	else if( $typequery == '3')
		echo ('<h2>Removed User</h2>');
?>
<hr>
<h2>Export / Import</h2>
<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="process" value="export" />
	File Name:<input type="text" name="savefile" value="" size="50"/><input type="submit" value="Export"/</form>	
<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="process" value="import" />
	File Name:<input type="text" name="openfile" value="" size="50"/><input type="submit" value="Import"/><br/>You can see the format file <a href="http://smallwebsitehost.com/doc/format.txt" target="_blank">here</a>. I recommend backup your database first.</form><hr>
<a href="options-general.php?page=newsletter/newsletter.php">Show all</a> - <a href="options-general.php?page=newsletter/newsletter.php&type=1">Show Only Opt-in</a> - <a href="options-general.php?page=newsletter/newsletter.php&type=0">Show Not Opt-in</a> - <a href="options-general.php?page=newsletter/newsletter.php&type=3">Show Removed user</a><br/><br/>
<?php

	if($typequery !='')
		$typequery = ' where joinstatus = ' . $typequery;
		
	if ($users = $wpdb->get_results("SELECT * FROM $table_users $typequery ORDER BY `id` DESC")) {
		$user_no=0;
		$url = get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=newsletter/' .
			basename(__FILE__);
?>
<table class="widefat">
<thead>    
<tr>
<td scope="col">ID</td>
<td scope="col">Date Join</td>
<td scope="col">Opted-in</td>
<td scope="col">IP</td>
<td scope="col">Name</td>
<td scope="col">E-mail</td>
<td scope="col">Action</td>
</tr>
</thead>
<tbody>
<?php
		$url = $url . '&amp;user_id=';
		$offset=$_GET[offset];
		checkValid($offset);
		
		if($offset =='')
			$offset = 0;
			
		$limit = 50;
		
		$pagenumber =intval(count($users)/$limit);
		if(count($users)%$limit)
		{
			$pagenumber++;
		}

		//paging
		echo("Page: ");
		for($i=1;$i<=$pagenumber;$i++)
		{
			$newpage=$limit*($i-1);

			if($offset!=$newpage)
			{
				echo "[<a href='options-general.php?page=newsletter/newsletter.php&type=".$_GET['type']. "&offset=".$newpage."'>$i</a>]";
			}else
			{
				echo "[$i]";
			}
		}

		for($i=$offset;$i<$offset+$limit;$i++)
		{
			$user = $users[$i];
			//check if we need to print
			if(!$user->joindate)
				continue;
					
			if ($user_no&1) {
				echo "<tr class=\"alternate\">";
			} else {
				echo "<tr>";
			}
			$user_no=$user_no+1;
			echo "<td>$user->id</td>";
			echo "<td>" . $user->joindate . "</td>";
			echo "<td>";
			if($user->joinstatus == 1)
				echo "Yes";
			else if($user->joinstatus == 0)
				echo "No";
			else if ($user->joinstatus == 3)
				echo "Removed";
				
			echo "</td>";
			echo "<td>$user->ip</td>";
			echo "<td>$user->name</td>";
			echo "<td>$user->email</td>";
			echo "<td><a href=\"$url$user->id\" onclick=\"if(confirm('Are you sure you want to delete user witd ID $user->id?')) return; else return false;\">Delete</a></td>";
			echo "</tr>";
		}

		//paging
?>
</tbody>
</table>
<?php
		echo("Page: ");
		for($i=1;$i<=$pagenumber;$i++)
		{
			$newpage=$limit*($i-1);

			if($offset!=$newpage)
			{
				echo "[<a href='options-general.php?page=newsletter/newsletter.php&type=".$_GET['type']. "&offset=".$newpage."'>$i</a>]";
			}else
			{
				echo "[$i]";
			}
		}
?>
<p><em>How to use</em>: insert this code in your pages: &lt;?php wpnewsletter_opt_in(); ?&gt;</p></div>
<?php
	}
?>
<div class="wrap">
<h2>Newsletter</h2>

<form method="post" action="">
    <input type="hidden" name="process" value="edit" />
    <fieldset class="options"> <legend>General</legend> 
    <table widtd="100%" cellspacing="2" cellpadding="2">
      <tr valign="top"> 
        <td scope="row">Email sender:</td>
        <td> 
            <input type="text" name="wpnewsletter_email_from" id="wpnewsletter_email_from" value="<?php echo $email_from; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Autoresponder email subject to prospect subscriber:</td>
        <td> 
          <input type="text" name="wpnewsletter_email_subject" id="wpnewsletter_email_subject" value="<?php echo $email_subject; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Autoresponder email content to prospect subscriber:</td>
        <td> 
            <textarea name="wpnewsletter_email_message" id="wpnewsletter_email_message" rows="4" cols="40"><?php echo $email_message; ?></textarea>
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Autoresponder email subject to subscriber:</td>
        <td> 
          <input type="text" name="wpnewsletter_email_subject_subscriber" id="wpnewsletter_email_subject_subscriber" value="<?php echo $email_subject_subscriber; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Autoresponder email content to subscriber:</td>
        <td> 
            <textarea name="wpnewsletter_email_message_subscriber" id="wpnewsletter_email_message_subscriber" rows="4" cols="40"><?php echo $email_message_subscriber; ?></textarea>
        </td>
      </tr>

      <tr valign="top"> 
        <td scope="row" colspan=2>    </fieldset> <fieldset class="options"> <legend>Messages</legend> </td>
      </tr>
      <tr valign="top">
        <td scope="row">Duplicate e-mail address:</td>
        <td>
          <input type="text" name="wpnewsletter_msg_dup" id="wpnewsletter_msg_dup" value="<?php echo $msg_dup; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Failed to send email:</td>
        <td> 
          <input type="text" name="wpnewsletter_msg_fail" id="wpnewsletter_msg_fail" value="<?php echo $msg_fail; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Success send email:</td>
        <td> 
          <input type="text" name="wpnewsletter_msg_sent" id="wpnewsletter_msg_sent" value="<?php echo $msg_sent; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row" colspan=2>     </fieldset> <fieldset class="options"> 
    <legend>Front side form appearance and labels</legend>
 </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Opt-in Form header:</td>
        <td> 
          <textarea name="wpnewsletter_form_header" id="wpnewsletter_form_header" rows="4" cols="40"><?php echo $form_header; ?></textarea>
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Opt-in Form footer:</td>
        <td> 
          <textarea name="wpnewsletter_form_footer" id="wpnewsletter_form_footer" rows="2" cols="40"><?php echo $form_footer; ?></textarea>
        </td>
      </tr>
    </table>
</fieldset>
<p class="submit"><input type="submit" name="Submit" value="Update Settings &raquo;" /></p>
</form>
</div>
<?php 
}
?>
