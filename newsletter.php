<?php
/*
Plugin Name: Wordpress Newsletter
Plugin URI: http://www.smallwebsitehost.com/wordpress-newsletter
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



if(!empty($_GET['kei']))
{

	wpnewsletter_opt_in();
}
else
{
	register_activation_hook(__FILE__, 'wpnewsletter_install');
	add_action('admin_menu', 'wpnewsletter_add_menu');
}

function wpnewsletter_add_menu() {
	add_options_page('Newsletter', 'Newsletter', 6, __FILE__, 'wpnewsletter_settings' );
}

function wpnewsletter_show_optin_form() {	
	$out = '<form action="" metdod="post">';
	$out .= '<table width="100%"  bgcolor="#EBF3FE">';
	$out .= '<tr><td colspan=2>'. stripslashes(get_option('wpnewsletter_form_header')) .'</td></tr>';
	$out .= '<tr><td>Name:</td><td><input type="text" name="wpnewsletter_name" id="wpnewsletter_name"/></td></tr>';
	$out .= '<tr><td>Email:</td><td><input type="text" name="wpnewsletter_email" id="wpnewsletter_email"/></td></tr>';
	$out .= '<tr><td colspan=2 align=center><input type="submit" value="Subscribe"/></td></tr>';
	$out .= '<tr><td colspan=2>'. stripslashes(get_option('wpnewsletter_form_footer')) .'</td></tr>';
	$out .='</table></form>';
	echo $out;
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
	if (empty($_GET['wpnewsletter_email'])) {

		if (!empty($_GET['kei'])) {
			wpnewsletter_optin_confirm();
		}
		else {
			$_POST['wpnewsletter_email'] = trim($_POST['wpnewsletter_email']);
			wpnewsletter_show_optin_form();
		}
	} 
	else {
		$name = stripslashes($_GET['wpnewsletter_name']);
		$name  = checkValid($name );

		$email = stripslashes($_GET['wpnewsletter_email']);
		$email = checkValid($email);

		if($name == "" || $email == "")
			return;
		
		$wpnewsletter_custom_flds = "";
		if (!preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $email)) {
				echo "Email format is incorrect";
				wpnewsletter_show_optin_form();
		}
		else {
				$email_from = stripslashes(get_option('wpnewsletter_email_from'));
				$subject = stripslashes(get_option('wpnewsletter_email_subject'));
				
				$message = stripslashes(get_option('wpnewsletter_email_message'));
				
				//create activation link
				$url = get_bloginfo('wpurl') .'/wp-content/plugins/newsletter/newsletter.php?';
			
				$wpnewsletter_date = date("Y-m-d H:m:s");
				$wpnewsletter_ip = wpnewsletter_getip();
				
				$url .= "kei=".md5($email.$name);

				$message = str_replace('*link*', $url, $message);
					
				$headers = "MIME-Version: 1.0\n";
				$blogname = get_option('blogname');
				$headers .= "From: $blogname <$email_from>\n";
				$headers .= "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";
		
				$wpnewsletter_date = date("Y-m-d H:m:s");
				$wpnewsletter_ip = wpnewsletter_getip();
				$selectqry = "SELECT * FROM " . $table_users . " WHERE `email` = '" . $email ."'";
				if ($wpdb->query($selectqry)) {
					echo stripslashes(get_option('wpnewsletter_msg_dup'));
				}
				else {
					if (mail($email,$subject,$message,$headers)) {
							
							$query = "INSERT INTO " . $table_users . " 
								(joindate, ip, email, joinstatus, name) 
								VALUES (
								'" . $wpnewsletter_date . "',
								'" . $wpnewsletter_ip . "',
								'" . $email . "',0,
								'" . $name . "'	)";
						 	$result = $wpdb->query($query);
							//echo($query);
						
						echo stripslashes(get_option('wpnewsletter_msg_sent'));
					} 
					else {
						echo stripslashes(get_option('wpnewsletter_msg_fail'));
					}
				}
		}
	}
}

function wpnewsletter_optin_confirm() {
require_once('setting.php');
	global $wpdb;


	mysql_connect($dbhost, $dbuser, $dbpass) or die("koneksi gagal");
	mysql_select_db($dbname);
	
	$wpnewsletter_ip = $_GET['kei'];

	$wpnewsletter_ip = checkValid($wpnewsletter_ip );

	$sql = "SELECT * FROM `wp_newsletter_users` WHERE MD5(CONCAT(`email`, `name`)) = '" . $wpnewsletter_ip ."' AND `joinstatus` = '0'";

	$result = mysql_query($sql );

	if ($result) {
		$row = mysql_fetch_assoc($result);

		if($row['id'])
		{

			$update = "UPDATE wp_newsletter_users SET `joinstatus` = '1' WHERE `id` = ". $row['id'];
			$result = mysql_query($update );

			echo("Thank you. You are subscribed now!");
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
			`joinstatus` bit NOT NULL default 0,
  			UNIQUE KEY `id` (`id`)
		);";
		$result = $wpdb->query($sql);

		// Insert initial data in table
		$insert = "INSERT INTO `$table_users` (`joindate`, `ip`, `email`, `name`,`joinstatus`) " .
			"VALUES ('" . time() . "','" . wpnewsletter_getip() .
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
		
		add_option('wpnewsletter_msg_dup', "<p>E-mail address already subscribed.</p>");
		add_option('wpnewsletter_msg_fail', "<p>Failed sending to e-mail address.</p>");
		add_option('wpnewsletter_msg_sent', "<p>Thanks for subscribing. Please check your email to verify.</p>");

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
		$headers .= "From: $blogname <$email_from>\n";
		$headers .= "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";

		$users = $wpdb->get_results("SELECT * FROM $table_users ORDER BY `id` DESC");

		foreach ($users as $user) {
			$message = str_replace("*name*", $user->name, $message);
			$subject = str_replace("*name*", $user->name, $subject);

				if (@wp_mail($user->email,$subject,$message,$headers)) {
					echo "Emailed to " . $user->email."<br/>";		
				}
				else
				{
					echo("failed email " + $user->email);
				}
		}
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
        <td scope="row">Autoresponder message to subscriber, subject:</td>
        <td> 
          <input type="text" name="wpnewsletter_email_subject" id="wpnewsletter_email_subject" value="<?php echo $email_subject; ?>" size="40" />
        </td>
      </tr>
      <tr valign="top"> 
        <td scope="row">Autoresponder message to subscriber, content:</td>
        <td> 
            <textarea name="wpnewsletter_email_message" id="wpnewsletter_email_message" rows="4" cols="40"><?php echo $email_message; ?></textarea>
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
<div class="wrap">
<h2>Send email</h2>
	<form action="" method="post">
    <input type="hidden" name="process" value="email" />
	<table width="100%"><tr><td>Subject:</td><td><input type="text" name="wpnewsletter_subject" id="wpnewsletter_subject" size="100"/></td></tr>
	<tr><td>Message: <br/>Type <b>*name*</b> to set the username</td><td><textarea rows=10 cols=100 name="wpnewsletter_message" id="wpnewsletter_message"/></textarea></td></tr></table>
	<p class="submit"><input type="submit" value="Send Newsletter"/></p></form>	
</div>
<div class="wrap">
<h2>Users joined</h2>
<?php
	if ($users = $wpdb->get_results("SELECT * FROM $table_users ORDER BY `id` DESC")) {
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
		foreach ($users as $user) {
			if ($user_no&1) {
				echo "<tr class=\"alternate\">";
			} else {
				echo "<tr>";
			}
			$user_no=$user_no+1;
			echo "<td>$user->id</td>";
			echo "<td>" . date(get_option('date_format'), $user->time). " " . date(get_option('time_format'), $user->time) . "</td>";
			echo "<td>";
			echo $user->joinstatus ? "Yes" : "No";
			echo "</td>";
			echo "<td>$user->ip</td>";
			echo "<td>$user->name</td>";
			echo "<td>$user->email</td>";
			echo "<td><a href=\"$url$user->id\" onclick=\"if(confirm('Are you sure you want to delete user witd ID $user->id?')) return; else return false;\">Delete</a></td>";
			echo "</tr>";
		}
?>
</tbody>
</table>
<p><em>How to use</em>: insert this code in your pages: &lt;?php wpnewsletter_opt_in(); ?&gt;</p></div>
<?php
	}
}

?>