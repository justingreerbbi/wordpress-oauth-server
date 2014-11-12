<?php
/**
 * Plugin Name: OAuth2 Server
 * Plugin URI: 
 * Version: 1.0.0
 * Description: The first true all-in-one OAuth solution for WordPress, including Single Sign On.
 * Author: Justin Greer
 * Author URI: http://justin-greer.com
 * License: GPL2
 * Text Domain: wp-oauth
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *
 * @author  Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth
*/
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! defined( 'WPOAUTH_FILE' ) ) {
	define( 'WPOAUTH_FILE', __FILE__ );
}

// Load WP OAuth Plugin
require_once( dirname( __FILE__ ) . '/wp-oauth-main.php' );