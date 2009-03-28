=== Simple Newsletter Plugin ===
Contributors: Yulianto (Ian)
Donate link: http://smallwebsitehost.com/
Tags: Newsletter, opt-in, email marketing, newsletter plugin
Requires at least: 2.6.0
Tested up to: 2.6.5
Stable tag: 1.0

This plugin is a simple newsletter plugin. It can show opt-in form, save opt-in email and name, and send emails to your opt-in list. It also have import and export email data ability. You can comment <a href="http://smallwebsitehost.com/wordpress-newsletter-plugin/wordpress/" target="_blank" title="wordpress newsletter plugin">here</a>.

== Description ==

This plugin is a simple newsletter plugin. It can show opt-in form, save opt-in email and name, and send emails to your opt-in list. It also have import and export email data ability. You can comment and ask about the plugin <a href="http://smallwebsitehost.com/wordpress-newsletter-plugin/wordpress/" target="_blank" title="wordpress newsletter plugin">here</a>. For latest update please download <a href="http://smallwebsitehost.com/doc/wordpress-newsletter.zip">here.

<b>Update December 11, 2008</b>

-Can filter data by Opted-in user, not opted-in user, and removed user
-Paging system for opted-in data

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Update setting.php with your database settings. You can find the settings in 'wp-config.php' in your www directory.
4. Place `<?php wpnewsletter_opt_in(); ?>` in your templates. 
5. If you want to add show pop up opt-in form, place `<?php wpnewsletter_show_optin_div(); ?>` in your templates. Put <?php ob_start(); ?> on the first line of your header.php theme file.