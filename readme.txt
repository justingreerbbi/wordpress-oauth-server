=== WordPress OAuth Server ===

Contributors: justingreerbbi
Donate link: http://justin-greer.com/
Tags: oauth2, OAuth provider, Provider, OAuth, OAuth client, Single Sign On, SSO
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This project is an OAuth 2.0 compatible authentication method for WordPress.

== Description ==

WP OAuth allows your WordPress site become a SSO authentication endpoint as well has allow to you tie
in 3rd party apps and software that requires login.

= Supported Grant Types =

* Authentication Code
* Implicit 
* User Credentials
* Client Credentials
* Refresh Token

WP OAuth Server does not currently support `Jwt Bearer` or `Crypto Tokens`.

== Installation ==

1. Upload `wordpress-oauth` to the `/wp-content/plugins/` directory or use the built in plugin install by WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click 'Settings' and then 'permalinks'. Then simply click 'Save Changes' to flush the rewrite rules so that OAuth2 Provider
1. Your Ready to Rock

== Frequently Asked Questions ==

= How do I add a APP/Client? =

Click on `Settings->OAuth Server`. Click on the `Clients` tab and then `Add New Client`. Enter the client information and your are done.

= Does WordPress OAuth Server Support SSO (Single Sign On) =

Yes, WordPress OAuth Server does support Single Sign On.

= Is there support for this plugin? Can you help me? =

You can visit our <a href="http://wp-oauth.com/forums/" title="WordPress OAuth Server">support forum</a> for support. Although it takes the hard part away from dealing with OAuth it will require some knowledge on your behalf.

= Can you set this up for me on my current website? =

* DRINKS COFFEE * Can I? "YES". You are more than welcome to contact us with if you should ever need assistance.

= How do I use WordPress OAuth Server? =

You can visit <a href="http://wp-oauth.com">http://wp-oauth.com</a>.

== Upgrade Notice ==

Version 2.0.0 and lower are not compatiable with version 3.0.0. If you have built your service using version 2.0.0 or lower, visit <a href="http://wp-oauth.com">http://wp-oauth.com</a> support forums.

For any upgrade, PLEASE PLEASE PLEASE make a full backup of your data. 

== Screenshots ==

1. Adding a Client

== Changelog ==

= 1.0.0 =
* INITIAL BUILD

= 1.0.1 =
* Re-worked Readme.txt
* Fixed absolute paths causing 404 Error when WordPress is running under a sub directory (Using admin_url() currently)

= 1.0.2 = 
* Fixed Broken login redirect

= 1.0.3 =
* Fixed Admin URL links for plugin dashboard

= 2.0.0 =
* Rebuild init plugin code structure for more flexibility and scalability.
* Added prefix to all DB connections
* Changed install query to use the InnoDB engine for better support and performance.
* Fixed improper loading of plugin stylesheet.
* Removed garbage data when plugin is activated. It was not being used and cluttering the codebase as well as the database.
* Move action template_redirect to rewrites file
* Added login form support for installs that are installed in sub directory
* Added missing in documentation for when calling requesting_token
* Suppressed some errors that was preventing a proper JSON return when `WP_DEBUG` was enabled.
* Added a client sample script to help learn the basics of connecting to the provider plugin.
* Add legacy installer that will hopefully keep old data in tacked while updating to the new structure with no data loss.
* Removed plugin logging as it was not really needed and caused more issues that it was worth.

= 3.0.0 =
* Updated and rebuilt structure.
* Visit <a href="http://wp-oauth.com">http://wp-oauth.com</a> for documentation and more information.
