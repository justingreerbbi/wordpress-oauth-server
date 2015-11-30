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
     *
     * @since 3.1.6 - Added custom query to handle includes without calling direct
     * @todo Break the rewrites out into a filter so we can modify them through a process instead of static
     */
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('oauth/(.+)' => 'index.php?oauth=' . $wp_rewrite->preg_index( 1 ) );
        $newRule += array('.well-known/(.+)' => 'index.php?well-known=' . $wp_rewrite->preg_index( 1 ) );
        $newRule += array('wpoauthincludes/(.+)' => 'index.php?wpoauthincludes=' . $wp_rewrite->preg_index( 1 ) );
        $newRules = $newRule + $rules;
        return $newRules;
    }
	
    /**
     * [add_query_vars description]
     * @param [type] $qvars [description]
     */
    function add_query_vars($qvars) {
        $qvars[] = 'oauth';
        $qvars[] = 'well-known';
        $qvars[] = 'wpoauthincludes';
        return $qvars;
    }
	
    /**
     * [flush_rewrite_rules description]
     * @return [type] [description]
     */
    function flush_rewrite_rules() {

        // Check to see if the main rewrite is used and skip if needed.
        $rules = get_option( 'rewrite_rules' );
        //if( isset( $rules['oauth/(.+)'] ) )
        //    return;

        global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
    }
	
	/**
     * [template_redirect_intercept description]
     * @return [type] [description]
     */
    function template_redirect_intercept( $template ) {
        global $wp_query;
        if ( $wp_query->get('oauth') || $wp_query->get('well-known') ) {
            require_once dirname( dirname(__FILE__) ) . '/library/class-wo-api.php';
            exit;
        }

        /** @since 3.1.6 | used by admin only */
        if ( $wp_query->get('wpoauthincludes') ) {
            $allowed_includes = array(
                'create' => dirname( WPOAUTH_FILE ) . '/library/content/create-new-client.php',
                'edit' => dirname( WPOAUTH_FILE ) . '/library/content/edit-client.php'
            );
            if(array_key_exists($wp_query->get('wpoauthincludes'), $allowed_includes) && current_user_can('manage_options')) {
                require_once $allowed_includes[$wp_query->get('wpoauthincludes')];
            }
        }

        return $template;
    }

}
$WO_Rewrites = new WO_Rewrites();
add_filter( 'rewrite_rules_array' , array($WO_Rewrites, 'create_rewrite_rules' ));
add_filter( 'query_vars' , array($WO_Rewrites, 'add_query_vars'));
add_filter( 'wp_loaded' , array($WO_Rewrites, 'flush_rewrite_rules'));

add_filter( 'template_include', array($WO_Rewrites, 'template_redirect_intercept'), 100);
//add_action( 'template_redirect', array( $WO_Rewrites, 'template_redirect_intercept') );