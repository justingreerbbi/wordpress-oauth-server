=== OAuth2 Complete For WordPress ===
Contributors: jgwpk
Donate link: http://justin-greer.com/donate
Tags: oauth2, oath provider, provider, oauth, oauth client, signle sign on, sso
Requires at least: 3.4.2
Tested up to: 3.5.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows WordPress to use OAuth 2 and become a SSO - Data provider. Your site will be able provided Single Sign On and also deliver authorized user data.

== Description ==

OAuth2 Complete is a ONE OF A KIND plugin that instanly turns your WordPress webste into a valid OAuth v2 Provider. The plugin is built using OAuth2 Draft 20 standards. The backend is designed or extremly easy use for any level of experience. OAuth is a great tool but leaves most developers behind since it a bit technical.
The plugin has aleady done the hard part for you.

Current Features Features:

*   Allows for Single Sign On Abilities
*   Backend Panel for adding Apps/Clients
*	3 Methods pre built in to allow for a plug and play system

== Installation ==

1. Upload `ouath2-complete` to the `/wp-content/plugins/` directory or use the built in plugin install by WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click 'Settings' and then 'permalinks'. Then simply click 'Save Changes' to flush the rewrite rules so that OAuth2 Provider
1. Your Ready to Rock

== Frequently Asked Questions ==

= How do I add a APP/Client? =

Visit the OAuth2 Complete dashboard by clicking `Provider` in the WordPress admin panel. Once you are in teh dashboard there is a form to label `Add a Client`. Give your client a name and a redirect URI. The redirect URI is the HTTP location where the user will be returned to after authinicating (Your client should provide this for you). Click `Add Client` and you will that the client has been added to your Client Manager.

= How does a client connect to my website to use the Single Sign On? =

Currently there is 3 Methods that OAuth2 Provider has built in:

1. http://example.com/oauth/authorize

1. http://example.com/oauth/request_token

1. http://example.com/oauth/request_access

Authorize requires only 3 parameters:

* client_id
* response_type - Supported value's = `code`
* state
* Example call `http://example.com/oauth/authorize?client_id=the_client_id&state=anything_you_want&response_type=code`

Request Token Requires only 4 parameters

* code - This is auth code returned from the authorize call
* grant_type - Supported value's = `authorization_code`
* client_id
* client_secret
* Example call `http://example.com/oauth/request_token?code=the_auth_key_sent_back_from_the_authorize_call&grant_typ=authorization_code&client_id=the_client_id&client_secret=the_client_secret`

Request Access Requires only 1 parmeter

* access_token - This is the access_token provided from the Request Token call
* Example Call `http://example.com/oauth/request_access?access_token=the_token_from_the_request_call`


NOTE: All returns will be in JSON format.

= Is there support for this plugin? Can you help me? =

You can visit our <a href="http://justin-greer.com/forums/forum/wordpress-oauth2-provider-plugin/" title="WordPress OAuth2 Provider Plugin">support forum</a> for support. Although it takes the hard part away from dealing with OAuth it will require some knowledge on your behalf. We are glad to help as much as resonibily possible but there has to be a line drawn somewhere.

= Can you set this up for me on my current website? =

Can we? "YES". But thats a different story. You are more than welcome to contact us with if you should ever need assistance.

= What information does the a authorized client have access to? =

By default OAuth2 Provider delivers ALL the information about the user that logged in. We are planning on adding a easy to use dashboard to limit data.

= Do you have a tutorial I can follow ? =

Yes we do. You can view a video titorial here. If you prefer readin then you may have to wait until the full documentation is complete. We are working hard to make it as easy and painless as possible for you to have a premium feature.

= Where can I download the SDK's for OAuth2 Provider =

You can visit our websit <a href="http://justin-greer.com">Here</a>

== Upgrade Notice ==

When Upgrading OAuth2 Provider we serioulsy recommend creating a backup of your site. We will try to create updates that will be flawless. Hopefully any future updates will not chnage to the point where it will stop working. All updates will be ran through multiple tests before being released. In the event that the a upgrade of OAuth2 Provider is realeased and you decide to update, we (balackbird Interactive) can not and will not be held responsible
for any damages done to your website, business, or part that pertains to your website. Upgrade at your OWN RISK!

== Screenshots ==

== Changelog ==

= 1.0.0 =
*INITIAL BUILD 