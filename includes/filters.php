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
add_filter("WO_API_Errors", "wo_api_errors", 1);
function wo_api_errors ( $errors )
{
	$errors["invalid_access_token"] = "The access token is invalid or has expired";
	$errors["invalid_refresh_token"] = "The refresh token is invalid or has expired";
	return $errors;
}

//add_filter();