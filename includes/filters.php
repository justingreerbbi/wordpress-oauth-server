<?php
/**
 * WordPress Mobile OAuth Filters
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress Mobile Oauth
 */

/**
 * WO API Errors Filter
 * OAuth standards do not call for custom errors nor explain how to handle certian aspects other than
 * using the generic errors. This filter allows us to have controll of the errors inside the API 
 * without havign to touch the actual core API
 *
 * Errors
 * - invalid_access_token
 * - invalid_refresh_token
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
 * Default Scopes Supported
 * @since 0.2
 * @todo Link scopes to checks system that is dynamic some how when performing API calls
 *
 * "scope_name" => enabled/disabled
 */
add_filter("WO_Scopes", "wo_scopes_setup");
function wo_scopes_setup ()
{
  $scopes = array(
    'general' => true,
    'email' => true,
    'media' => true,
    'posts' => true,
    );
  return $scopes;
}