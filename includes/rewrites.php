<?php
/**
 * WordPress Mobile Oauth Rewrites
 * 
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress Mobile Oauth
 */
class WO_Rewrites {

    /**
     * [create_rewrite_rules description]
     * @param  [type] $rules [description]
     * @return [type]        [description]
     */
    function create_rewrite_rules($rules) 
    {
        global $wp_rewrite;
        $newRule = array('oauth/(.+)' => 'index.php?oauth='.$wp_rewrite->preg_index(1));
        $newRules = $newRule + $rules;
        return $newRules;
    }
	
    /**
     * [add_query_vars description]
     * @param [type] $qvars [description]
     */
    function add_query_vars($qvars) 
    {
        $qvars[] = 'oauth';
        return $qvars;
    }
	
    /**
     * [flush_rewrite_rules description]
     * @return [type] [description]
     */
    function flush_rewrite_rules() 
    {
        global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
    }
	
	/**
     * [template_redirect_intercept description]
     * @return [type] [description]
     */
    function template_redirect_intercept() 
    {
        global $wp_query;
        if ( $wp_query->get('oauth') ) 
        {
            require_once( dirname(dirname(__FILE__)) . '/library/class-wo-api.php' );
            exit;
        }
    }

}
$WO_Rewrites = new WO_Rewrites();
add_filter( 'rewrite_rules_array' , array( $WO_Rewrites , 'create_rewrite_rules' ));
add_filter( 'query_vars' , array( $WO_Rewrites , 'add_query_vars'));
add_filter( 'wp_loaded' , array( $WO_Rewrites , 'flush_rewrite_rules'));
add_action( 'template_redirect', array( $WO_Rewrites, 'template_redirect_intercept') );