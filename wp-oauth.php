<?php
/**
 * Plugin Name: WordPress OAuth Server
 * Plugin URI: 
 * Version: 3.0.0
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

/** 5.4 Strict Mode Temp Patch */
add_action("wp_loaded", '_wo_register_files');
function _wo_register_files ()
{
	wp_register_style( 'wo_admin', plugins_url( '/assets/css/admin.css', __FILE__ )  );
	wp_register_script( 'wo_admin', plugins_url( '/assets/js/admin.js', __FILE__ ) );
}

require_once( dirname( __FILE__ ) . '/wp-oauth-main.php' );
register_activation_hook( __FILE__, array(new WO_Server, 'setup'));