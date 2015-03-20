=== WP OAuth Server ===

Contributors: justingreerbbi
Donate link: http://justin-greer.com/
Tags: oauth2, OAuth provider, Provider, OAuth, OAuth client, Single Sign On, SSO, OpenID Connect, OIDC, OpenID, Connect
Requires at least: 3.9
Tested up to: 4.2
Stable tag: 3.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use WordPress to Power Your OAuth Server

== Description ==

This plugin is an OAuth 2.0 compatible authorization method using WordPress users. 
The goal of WordPress OAuth Server is to provide an easy to use authentication and authorization
method that 3rd party services can use to securely connect to any server running a WordPress site.

= Features =

* Unlimited Clients
* Firewall
* Support for Implicit Flow
* Built-In Resource Server
* Automated Authorization
* Expendable and Flexible 
* Well Documented

= Supported Grant Types =

* Authentication Code
* User Credentials
* Client Credentials
* Refresh Token
* OpenID Connect

= How to Use =

Visit https://wp-oauth.com/knowledge-base/ for detailed documentation on installing, configuring and using 
WordPress OAuth Server.

= Licensing = 

Visit https://wp-oauth.com for more information about WordPress OAuth Server licensing. Licensing
WordPress OAuth Server if not required to use this plugin.

= Minimum Requirements =

* PHP 5.3.9 or greater *(5.4+ recommended)*
* OpenSSL installed and enabled if you plan on using OpenID Connect

= Other Information =

* NOTE: As of 3.0.0, there are no backward compatibility for any version older than 3.0.0. We are working on a migration tool, but as of now any upgrade from 2.x to 3.x will need to be done manually.*
* NOTE: The plugin will not be supported if you are using a Windows Server.

= Support =

Support requests should be made by opening a support request at https://wp-oauth.com/account/submit-ticket/.

== Installation ==

1. Upload `oauth-provider` to the `/wp-content/plugins/` directory or use the built in plugin install by WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click 'Settings' and then 'permalinks'. Then simply click 'Save Changes' to flush the rewrite rules so that OAuth2 Provider
1. Your Ready to Rock

== Frequently Asked Questions ==

= How do I add a APP/Client? =
Click on `Settings->OAuth Server`. Click on the `Clients` tab and then `Add New Client`. Enter the client information and your are done.

= Does WordPress OAuth Server Support SSO (Single Sign On) =
Yes, WordPress OAuth Server does support Single Sign On for both Traditional OAuth2 Flow and OpenID Connect.

= Is there support for this plugin? Can you help me? =
You can visit our https://wp-oauth.com/account/submit-ticket/ to open up a support request directly with developers.

= Can you set this up for me on my current website? =
* DRINKS COFFEE * Can I? "YES". You are more than welcome to contact us with if you should ever need assistance.

= How do I use WordPress OAuth Server? =
You can visit https://wp-oauth.com/knowledge-base/. You will find in-depth documentation as well as examples of how to get started.

== Upgrade Notice ==

Version 2.0.0 and lower are not compatible with version 3.0.0. If you have built your service using version 2.0.0 or lower, visit https://wp-oauth.com/account/submit-ticket/ to open a new request support request.

For any upgrade or modification, PLEASE PLEASE PLEASE make a full backup of your data. 

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
* Fixed improper loading of plugin style sheet.
* Removed garbage data when plugin is activated. It was not being used and cluttering the code base as well as the database.
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

= 3.0.1 =
* Updated cover image.
* Fixed documentation links.
* Added "Server Status" tab
* Cleaned up "Advanced Configuration" contents.

= 3.0.2 =
* Re added Authorization Code Enable Option
* API unavailable error now uses OAuth Response object
* API now reports when access token is not provided during resource calls

= 3.0.3 =
* Modified how clients are added and edited
* Add Pro Features
* Added additional information to "Server Status" Tab
* Minor Clean Up

= 3.0.4 = 
* Updated Readme.txt content
* Add more descriptive text during PHP version check
* Fixed license links
* Added Access Token and Refresh Token lifetime settings
* Added upgrade method to ensure proper installing of new features

= 3.0.5 =
* Removed permalink check. OAuth Server now works without the use of permalinks.
* Fixed install functionality. Not all tables were being installed.
* Added support for cytpto tokens.
* Added OpenID Connect abilities.
* Mapped OpenID Claims to default user values
* Added index to token table and increased access_token length to support crypto tokens in the future.
* Added "email" to default me resource to support OpenID Connect 1.0
* Added generic key signing for all clients.
* Added public endpoint for verifying id_token (/oauth/public_key)