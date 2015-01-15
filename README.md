# WordPress OAuth Server

Current Version: 3.0.1

This project is an OAuth 2.0 compatible authentication method for WordPress. The goal of WP OAuth Server 
(WordPress Open Authentication) is to provide an easy to use authentication method that 3rd party services can use to securely connect to any server running WordPress site.

You can find online documentation by visiting [http://wp-oauth.com](http://wp-oauth.com)

### Framework

This project is built on top of [Brent Shaffer's](https://github.com/bshaffer) PHP OAuth Server project.

### Supported Grant Types
* Authentication Code
* Implicit 
* User Credentials
* Client Credentials
* Refresh Token

WP OAuth Server does not currently support `Jwt Bearer` or `Crypto Tokens`.

## Installation

1. Upload `oauth2-provider` to the `/wp-content/plugins/` directory or use the built-in plugin install system
1. Activate the plugin through the `Plugins` menu in WordPress
1. Click `Settings` and then `Permalinks`. Then simply click `Save Changes` to flush the rewrite rules.
1. You're Ready to Rock


## Adding a new Client?

Visit the dashboard by clicking `Provider` in the WordPress admin panel under `Settings`. Once you are in the dashboard, there is a form labeled `Add Client`. Give your client a name and a redirect URI and description. The redirect URI is the HTTP location where the user will be returned to after authenticating (your client should provide this for you). Click `Add Client`.

## Authentication Documentation

*The following documentation assumes that you are famialr with PHP and at least a basic understand the workflow for OAuth 2.0 works.*

Since the main framework of this plugin was built on Brent Shaffers sevrver, you can follow his documentation. The only difference is the endpoints. The plugin endpoints are below:

- `/oauth/authorize`
- `oauth/token`

Brent Shaffer has created a very detailed Step-by-Step guide to using the Authentication API. You can view the 
homepage of this documentation [here](http://bshaffer.github.io/oauth2-server-php-docs/cookbook/). 



## Resource API

{STILL NEEDS DOCUMENTATION}

## What you need to know

Before you actually start, there is a few things that should be mentioned as it mat just make your day easier.

* This plugin uses `register_styles` and `register_scripts` within the main plugin class. This will throw strict warnings when using PHP 5.4 or higher. This is enterfere with the header responses and will cause output errors all over the place. For the time being, turn Strict Mode off for the server. 

* This one goes hand in hand with the previous note. Ensure the WP_DEGUG is set to `false` to prevent headache.

## Development / Developer Notes

* Clients in the oauth_clients table that have the ID of `0` belong to the OAuth server. These clients should be treated as a private resource and not used for more than one client type. DO NOT use the same client id for multiple clients. Each device, plaform, software using the OAuth Server level client_id should have their own client_id.

* It is solely the responsibility of the client to store user sessions. The OAuth server currently does not store any sessions for you.

* Auth Code Life Time - 30 Secounds (this will not change for security reasons)

* 

## TODO

* Look into cleaning up the headers when the server is responding.
* Clean up All un needed classes and abstracts in the original OAuth Server.

## Exstentions

* API Firewall
* JSON-API Hook

## 3rd Party Exstension Examples

* [OAuth2 Complete For WordPress strategy for Passport](https://www.npmjs.com/package/passport-oauth2-complete-for-wordpress) - Author: [Ido Ran](http://github.com/ido-ran).