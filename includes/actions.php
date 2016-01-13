<?php
/**
 * WP OAuth Server Actions
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */

/**
 * Invalidate any token and refresh tokens during password reset
 * @param  object $user     WP_User Object
 * @param  String $new_pass New Password
 * @return Void           
 *
 * @since 3.1.8
 */
function wo_password_reset_action( $user, $new_pass ) {
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user->ID ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user->ID ) );
}
add_action( 'password_reset', 'wo_password_reset_action', 10, 2 );

/**
 * [wo_profile_update_action description]
 * @param  int  $user_id 	WP User ID
 * @return Void         	
 */
function wo_profile_update_action( $user_id ) {
  if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
  	return;
  }
  global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user_id ) );
}
add_action( 'profile_update', 'wo_profile_update_action' );