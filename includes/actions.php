<?php
/**
 * Actions used for WP OAuth Server
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */

/** 
 * Invalidate any tokens that belong to the user during a password reset.
 *
 * @since 3.1.8
 */
add_action( 'password_reset', 'wo_password_reset_action', 10, 2 );
function wo_password_reset_action( $user, $new_pass ) {
	global $wpdb;

	// Delete any access tokens belonging to the user changing their password
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user->ID ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user->ID ) );
}

/** 
 * Invalidate any tokens that belong to the user during a password change.
 *
 * @since 3.1.8
 */
add_action( 'profile_update', 'wo_profile_update_action' );
function wo_profile_update_action( $user_id ) {
  if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
  	return;
  }

  global $wpdb;
  $wpdb->show_errors();
  // Delete any access tokens belonging to the user changing their password
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user_id) );
}