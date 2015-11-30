<?php
/**
 * Plugin Name: WP OAuth Server
 * Plugin URI: http://wp-oauth.com
 * Version: 3.1.92
 * Description: Use WordPress to power your OAuth Server. Provide Single Sign On and other OAuth functionality.
 * Author: Justin Greer
 * Author URI: http://wp-oauth.com
 * License: GPL2
 * Text Domain: wp-oauth
 *
 * This program is GLP but; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of.
 *
 * @author  Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */

if (!function_exists('add_filter')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if (!defined('WPOAUTH_FILE')) {
	define('WPOAUTH_FILE', __FILE__);
}

/** 
 * 5.4 Strict Mode Temp Patch
 *
 * Since PHP 5.4, WP will through notices due to the way WP calls statically
 */
add_action("wp_loaded", '_wo_register_files');
function _wo_register_files() {
	wp_register_style('wo_admin', plugins_url('/assets/css/admin.css', __FILE__));
	wp_register_script('wo_admin', plugins_url('/assets/js/admin.js', __FILE__));
}

/** Grab the main class file */
require_once( dirname(__FILE__) . '/wp-oauth-main.php');


/**
 * @todo  Move setup and upgrade inside the function wo_plugin_activate()
 */
register_activation_hook(__FILE__, array(new WO_Server, 'setup'));
register_activation_hook(__FILE__, array(new WO_Server, 'upgrade'));