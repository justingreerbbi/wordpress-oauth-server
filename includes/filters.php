<?php
/**
 * WordPress Mobile OAuth Filters
 * @author Justin Greer <justin@justin-greer.com>
 */

/**
 * WordPress OAuth Server Error Filter
 * @deprecated Schedule for removal. The PHP server handles all these now.
 */
add_filter("WO_API_Errors", "wo_api_error_setup", 1);
function wo_api_error_setup ( $errors )
{
	$errors["invalid_access_token"] = "The access token is invalid or has expired";
	$errors["invalid_refresh_token"] = "The refresh token is invalid or has expired";
  $errors["invalid_credentials"] = "Invalid user credentials";
	return $errors;
}

/**
 * Default Method Filter for the resource server API calls
 */
add_filter('wo_endpoints', 'wo_default_endpoints', 1);
function wo_default_endpoints ()
{
  $endpoints = array(
    'me' => array('func' =>'_wo_method_me')
    );
  return $endpoints;
}

/**
 * DEFAULT ME METHOD - DO NOT REMOVE DIRECTLY
 * This is the default resource call "/oauth/me". Do not edit nor remove.
 */
function _wo_method_me ( $token=null )
{
  $user_id = &$token['user_id'];

  global $wpdb;
  $me_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}users WHERE ID=$user_id", ARRAY_A);
  
  /** prevent sensative data - makes me happy ;) */
  unset($me_data['user_pass']);
  unset($me_data['user_activation_key']);
  unset($me_data['user_url']);
  $response = new OAuth2\Response($me_data);
  $response->send();
  exit;
}
