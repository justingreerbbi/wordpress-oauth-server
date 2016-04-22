<?php
/**
 * Plugin Name: WP OAuth Server
 * Plugin URI: http://wp-oauth.com
 * Version: 3.1.98
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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if (! defined( 'WPOAUTH_FILE' ) ) {
	define( 'WPOAUTH_FILE', __FILE__ );
}

/** 
 * 5.4 Strict Mode Temp Patch
 *
 * Since PHP 5.4, WP will through notices due to the way WP calls statically
 */
function _wo_server_register_files() {
	wp_register_style( 'wo_admin', plugins_url( '/assets/css/admin.css', __FILE__ ) );
	wp_register_script( 'wo_admin', plugins_url( '/assets/js/admin.js', __FILE__ ) );
}
add_action( 'wp_loaded', '_wo_server_register_files' );

require_once( dirname(__FILE__) . '/wp-oauth-main.php' );

/**
 * Adds/registers query vars
 * @return void
 */
function _wo_server_register_query_vars() {
	_wo_server_register_rewrites();

	global $wp;
	$wp->add_query_var( 'oauth' );
	$wp->add_query_var( 'well-known' );
	$wp->add_query_var( 'wpoauthincludes' );
}
add_action( 'init', '_wo_server_register_query_vars' );

/**
 * Registers rewrites for OAuth2 Server
 * 
 * - authorize
 * - token
 * - .well-known
 * - wpoauthincludes
 * 
 * @return void
 */
function _wo_server_register_rewrites() {
	add_rewrite_rule( '^oauth/(.+)','index.php?oauth=$matches[1]','top' );
	add_rewrite_rule( '^.well-known/(.+)','index.php?well-known=$matches[1]','top' );
	add_rewrite_rule( '^wpoauthincludes/(.+)','index.php?wpoauthincludes=$matches[1]','top' );
}

/**
 * [template_redirect_intercept description]
 * @return [type] [description]
 */
function _wo_server_template_redirect_intercept( $template ) {
	global $wp_query;

	if ( $wp_query->get( 'oauth' ) || $wp_query->get( 'well-known' ) ) {
		require_once dirname( __FILE__ ) . '/library/class-wo-api.php';
		exit;
	}

	if ( $wp_query->get( 'wpoauthincludes' ) ) {
			$allowed_includes = array(
					'create' => dirname( WPOAUTH_FILE ) . '/library/content/create-new-client.php',
					'edit' => dirname( WPOAUTH_FILE ) . '/library/content/edit-client.php'
			);
			if( array_key_exists( $wp_query->get( 'wpoauthincludes' ), $allowed_includes ) && current_user_can( 'manage_options' ) ) {
					require_once $allowed_includes[$wp_query->get( 'wpoauthincludes' )];
			}
	}

	return $template;
}
add_filter( 'template_include', '_wo_server_template_redirect_intercept', 100);

/**
 * OAuth2 Server Activation
 * @param  [type] $network_wide [description]
 * @return [type]               [description]
 */
function _wo_server_activation( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
				$mu_blogs = wp_get_sites();
				foreach ( $mu_blogs as $mu_blog ) {
						switch_to_blog( $mu_blog['blog_id'] );
						_wo_server_register_rewrites();
						flush_rewrite_rules();
				}
				restore_current_blog();
		} else {
				_wo_server_register_rewrites();
				flush_rewrite_rules();
		}
}
register_activation_hook( __FILE__, '_wo_server_activation' );

/**
 * OAuth Server Deactivation
 * @param  [type] $network_wide [description]
 * @return [type]               [description]
 */
function _wo_server_deactivation( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
				$mu_blogs = wp_get_sites();
				foreach ( $mu_blogs as $mu_blog ) {
						switch_to_blog( $mu_blog['blog_id'] );
						flush_rewrite_rules();
				}
				restore_current_blog();
		} else {
				flush_rewrite_rules();
		}
}
register_deactivation_hook( __FILE__, '_wo_server_deactivation' );

/**
 * @todo  Move setup and upgrade inside the function wo_plugin_activate()
 */
register_activation_hook(__FILE__, array(new WO_Server, 'setup'));
register_activation_hook(__FILE__, array(new WO_Server, 'upgrade'));

function wo_admin_notice_upgrade_jump() {
		?>
		<div class="notice notice-warning">
			<p>WP OAuth Server has updated to version 3.2.x. Click <a href="https://wp-oauth.com/2016/04/upgrading-3-1-97-3-2-0/">here</a> for more information.</p>
		</div>
		<?php
}
add_action( 'admin_notices', 'wo_admin_notice_upgrade_jump' );

/**
 * Control Upgrade from the the WP OAuth Server instead
 * @return [type] [description]
 */
function wo_licensed_upgrade(){
	$wo_options = get_option( 'wo_options' );
	if( isset( $wo_options['license'] ) ) {
		if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once( dirname( __FILE__ ) . '/library/updater.php' );
		}
		$license_key = trim( $wo_options['license'] ); 
		$edd_updater = new EDD_SL_Plugin_Updater( 'https://wp-oauth.com', __FILE__, array(
			'version' 		=> '3.1.98',
			'license' 		=> $license_key,
			'item_name'     => 'WP OAuth Server',
			'author' 		=> 'Justin Greer',
			'url'           => home_url()
		) );
	}
}
add_action('init', 'wo_licensed_upgrade');