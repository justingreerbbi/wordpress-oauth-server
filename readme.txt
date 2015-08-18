=== WP OAuth Server ===

Contributors: justingreerbbi
Donate link: http://justin-greer.com/
Tags: oauth2, OAuth provider, Provider, OAuth, OAuth client, Single Sign On, SSO, OpenID Connect, OIDC, OpenID, Connect
Requires at least: 4.2.4
Tested up to: 4.2.4
Stable tag: 3.1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and Manage an OAuth 2.0 server powered by WordPress. Become a Single Sign On Provider and or resource server.

== Description ==

This plugin is a full OAuth 2.0 authorization server/provider for WordPress. 
The goal of WP OAuth Server is to provide an easy to use authorization method that 3rd party platforms can use to securely authorize users from your WordPress site.

= Features =

* Unlimited Clients
* Firewall
* Support for Implicit Flow
* Built-In Resource Server
* Automated Authorization
* Extendable

= Supported Grant Types =

* Authentication Code
* User Credentials
* Client Credentials
* Refresh Token
* OpenID Connect with discovery

= How to Use =

Visit https://wp-oauth.com/knowledge-base/ for detailed documentation on installing, configuring and using 
WordPress OAuth Server.

= Licensing = 

WP OAuth Server is free to used. Please support the project by licensing. You can view more information at
https://wp-oauth.com.

= Minimum Requirements =

* PHP 5.3.9 or greater *(latest version recommended)*
* OpenSSL installed and enabled if you plan on using OpenID Connect

= Other Information =

* NOTE: As of 3.0.0, there are no backward compatibility for any version older than 3.0.0
* NOTE: Due to IIS's inability play nice, WP OAuth Server may work but is not supported on Windows OS.

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

= 3.1.5 =
* Addressed security issues on older PHP versions as well as Windows OS.
* Added checks to help ensure that the environment is supported before WP OAuth Server can be ran.
* Add filter 'wo_scopes' to allow for extendability.

= 3.1.4 =
* Fixed bug in refresh token that prevented use of refresh tokens

= 3.1.3 =
* Forced all expires_in parameter in JSON to be an integer
* Add determine_current_user hook for WP core authentication functionality
* Added authentication support for WP REST API

= 3.1.2 =
* Patch to possible exploit when editing a client.
* Slight UI changes.
* Patched auth code table for large id_tokens.
* Fixed security issue with token lifetime.

= 3.1.1 =
* Client name is not click able to show edit popup
* Fixed issue with missing exits in API

= 3.1.0 =
* Added specific OpenSSL bit length for systems that are not create keys at 2048 by default.
* Added urlSafeBase64 encoding to Modulus and Exponent on delivery.
* Tweak redirect location in API when a user is not logged in

= 3.0.9 =
* Added userinfo endpoint to /.well-known/openid-configuration 
* Fixed improper return of keys when for public facing /.well-known
* Auto generation of new certificates during activation to ensure all server have a different signature

= 3.0.8 =
* Switched JWT Signing to uses RS256 instead of HS256.
* Added OpenID Discovery with REQUIRED fields and values.
* "sub" now complies with OpenID specs for format type.
* Added JWT return for public key when using OpenID Discovery.

= 3.0.7 =
* Bug fix in OpenID

= 3.0.6 =
* Fixed "Undefined Error" in Authorization Controller. Credit to Frédéric. Thank You!
* Remove "Redirect URI" Column from clients table to clean up table on smaller screens.
* Updated banner and plugin icon.

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

= 3.0.4 = 
* Updated Readme.txt content
* Add more descriptive text during PHP version check
* Fixed license links
* Added Access Token and Refresh Token lifetime settings
* Added upgrade method to ensure proper installing of new features

= 3.0.3 =
* Modified how clients are added and edited
* Add Pro Features
* Added additional information to "Server Status" Tab
* Minor Clean Up

= 3.0.2 =
* Re added Authorization Code Enable Option
* API unavailable error now uses OAuth Response object
* API now reports when access token is not provided during resource calls

= 3.0.1 =
* Updated cover image.
* Fixed documentation links.
* Added "Server Status" tab
* Cleaned up "Advanced Configuration" contents.

= 3.0.0 =
* Updated and rebuilt structure.
* Visit <a href="http://wp-oauth.com">http://wp-oauth.com</a> for documentation and more information.

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

= 1.0.3 =
* Fixed Admin URL links for plugin dashboard

= 1.0.2 = 
* Fixed Broken login redirect

= 1.0.1 =
* Re-worked Readme.txt
* Fixed absolute paths causing 404 Error when WordPress is running under a sub directory (Using admin_url() currently)

= 1.0.0 =
* INITIAL BUILD