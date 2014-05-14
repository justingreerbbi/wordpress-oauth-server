<?php
/**
 * OAuth2 Provider WordPress Rewrite rules class
 * DON NOT TOUCH THIS CLASS UNLESS YOU KNOW WHAT YOU ARE DOING
 * 
 * @since 1.0.0
 */
class OAuth2Rewrites {

    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('oauth/(.+)' => 'index.php?oauth='.$wp_rewrite->preg_index(1));
        $newRules = $newRule + $rules;
        return $newRules;
    }
	
    function add_query_vars($qvars) {
        $qvars[] = 'oauth';
        return $qvars;
    }
	
    function flush_rewrite_rules() {
        global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
    }
	
	/**
	 * template_redirect_intercept
	 * Template redirect for WP OAuth2
	 * @param {}
	 */
    function template_redirect_intercept() {
        global $wp_query;
        if ($wp_query->get('oauth')) {
            require_once( dirname(__FILE__) . '/classes/OAuth2_API.php' );
            exit;
        }
    }
	
	/**
	 * Creates a JSON output
	 * 
	 * @since 1.0.0
	 * @uses output
	 * @deprecated Generic Output. Not needed or used not more. Scheduled to be removed 1.0.6
	 */
    function pushoutput($message) {
        $this->output($message);
    }
	
    function output( $output ) {
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
        echo json_encode( $output );
    }
}
$OAuth2RewritesCode = new OAuth2Rewrites();

/**
 * Create all the hooks the link this all together with WordPress
 */
add_filter( 'rewrite_rules_array' , array( $OAuth2RewritesCode , 'create_rewrite_rules' ));
add_filter( 'query_vars' , array( $OAuth2RewritesCode , 'add_query_vars'));
add_filter( 'wp_loaded' , array( $OAuth2RewritesCode , 'flush_rewrite_rules'));

add_action( 'template_redirect', array( $OAuth2RewritesCode, 'template_redirect_intercept') );