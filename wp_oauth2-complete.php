<?php
/*
Plugin Name: WP OAuth2 Complete
Plugin URI: http://justin-greer.com/oauth2-provider-complete-wordpress-plugin
Description: Allows Wordpress to use OAuth2 structure and become a provider
Version: 1.0.2
Author: jgwpk
Author URI: http://justin-greer.com
License: GPL2

  Copyright 2012  WP OAuth2 Complete (email : support@wpkeeper.com)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Adds the menu to the the WordPress Administrator Panel 
 */
function wp_oauth2_complete_menu() {
	add_menu_page( 'WP OAuth2 Complete', 'Provider', 'manage_options', 'wp_oauth2_complete', 'wp_oauth2_complete_init_dashboard' );
}
add_action( 'admin_menu', 'wp_oauth2_complete_menu' );

/**
 * Require the dashboard into the plugin - Contains the dashboard function 
 */
require_once( dirname(__FILE__).'/lib/dashbaord.php');

/**
 * Defines the path to the OAuth2 Complete plugin
 */
define('WP_OAUTH2_PATH', plugin_dir_path(__FILE__));

/**
 * Registers the Style Sheet with WordPress for OAuth2 dashboard
 */
wp_register_style( 'wp_oauth2_provider_stylesheet', site_url().'/wp-content/plugins/oauth2-provider/lib/assets/css/layout.css' );

/**
 *	Global OAuth2 for WordPress Version
 */
global $wp_oath2_complete_version;

/**
 * Global the $wpdb object
 * 
 * @global object Default WordPress Database Class
 */
global $wpdb;

/**
 * Set the version of OAuth2
 */
$wp_oath2_complete_version = "1.0.0";

/**
 * Installs the needed databases if not already installed. This function should only be fired when plugin is activated
 */
function wp_oauth2_complete_install() {
   
   // OPTION TABLE
	$install_options_table = "CREATE TABLE oauth2_options (
  	id INT NOT NULL AUTO_INCREMENT,
 	version VARCHAR(55) DEFAULT '' NOT NULL,
	enabled INT(1) NOT NULL,
	draft INT(1) NOT NULL,
  	UNIQUE KEY id (id)
    );";
	
	// CONSUMER TABLE
   	$install_auth_table = "CREATE TABLE oauth2_auth_codes (
	code varchar(40) NOT NULL,
	client_id varchar(40) NOT NULL,
	user_id int(11) UNSIGNED NOT NULL,
	redirect_uri varchar(200) NOT NULL,
	expires int(11) NOT NULL,
	scope varchar(255) DEFAULT NULL,
	PRIMARY KEY (code)
  	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	// CONSUMER NONCE TABLE
   	$install_clients_table = "CREATE TABLE oauth2_clients (
	name varchar(40) NOT NULL,
	client_id varchar(40) NOT NULL,
	client_secret varchar(20) NOT NULL,
	redirect_uri varchar(255) NOT NULL,
	PRIMARY KEY (client_id)
  	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	  
	// TOKEN TABLE
   	$install_token_table = "CREATE TABLE oauth2_access_tokens (
	oauth_token varchar(40) NOT NULL,
	client_id varchar(40) NOT NULL,
	user_id int(11) UNSIGNED NOT NULL,
	expires int(11) NOT NULL,
	scope varchar(255) DEFAULT NULL,
	PRIMARY KEY (oauth_token)
  	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  
  	// TOKEN REFRESH TABLE
	$install_refresh_token_table = "CREATE TABLE oauth2_refresh_tokens (
	refresh_token varchar(40) NOT NULL,
	client_id varchar(40) NOT NULL,
	user_id int(11) UNSIGNED NOT NULL,
	expires int(11) NOT NULL,
	scope varchar(255) DEFAULT NULL,
	PRIMARY KEY (refresh_token)
  	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	/**
	 * ONLY run this using WordPress. Let WordPress do all the heavy lifting!!!! It cleaner...
	 */
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');		// REQUIRE THE UPGRADE CORE FUNCTIONS
   dbDelta($install_options_table);								// RUN THE SQL THROUGH THE UPGRADE SCRIPT
   dbDelta($install_auth_table);								// INSTALL AUTH CODES TABLE
   dbDelta($install_clients_table);								// INSTALL CLIENTS TABLE
   dbDelta($install_token_table);								// INSTALL TOKENS TABLE
   dbDelta($install_refresh_token_table);						// INSTALL TOKEN REFRESH				
 
}

/**
* Installs the default options into the table "ouath2_options"
*
* @return void
*/
function wp_oauth2_complete_install_data() {
   global $wpdb;
   global $wp_oath2_complete_version;

   $rows_affected = $wpdb->insert( 'oauth2_options', array( 'version' => $wp_oath2_complete_version, 'enabled' => 1, 'draft'=> '20') );
}


/**
 * Run the install of the tables 
 */
register_activation_hook(__FILE__,'wp_oauth2_complete_install');				 // REGISTER THE CREATION OF THE TABLE
register_activation_hook(__FILE__,'wp_oauth2_complete_install_data');			// REGISTER THE INSTALLATION OF THE INTIAL DATA


/**
 * OAuth2 Provider WordPress Rewrite rules class
 * DON NOT TOUCH THIS CLASS UNLESS YOU KNOW WHAT YOU ARE DOING
 * 
 * @since 1.0.0
 */
class OAuth2Rewrites {

	/**
	 * Activates the rewrite rules
	 * 
	 * @todo Run this function only when the plugin is acivated
	 */
    function activate() {
    	
    	/**
		 * Rewrite Hook
		 * @global object $wp_rewrite WordPress hook for rewriting pretty URLs 
		 */
        global $wp_rewrite;
		
		/**
		 * Flush the rewrites so the changes can take effect
		 */
        $this->flush_rewrite_rules();
    }

   /**
    * Creates the rewrite rules that the plugin needs to function
    * @return void
    */
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('oauth/(.+)' => 'index.php?oauth='.$wp_rewrite->preg_index(1));
        $newRules = $newRule + $rules;
        return $newRules;
    }
	
	/**
	 * Tell WordPress that we want to include "oauth" as something to look for in the permalinks
	 * @since 1.0.0
	 */
    function add_query_vars($qvars) {
        $qvars[] = 'oauth';
        return $qvars;
    }
	
	/**
	 * Flushes the permalink rules and resets them.
	 * This adds any new rules into the mix
	 * 
	 * @since 1.0.0
	 */
    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
	
	/**
	 * Tells WordPress that when oauth is being called that we want stop and start using the OAuth2 Provider API hook
	 * 
	 * @since 1.0.0
	 */
    function template_redirect_intercept() {
    	
		/**
		 * @global $wp_query Hooks into WordPress Queries
		 */
        global $wp_query;
		
		/**
		 * Check if "oauth" is found and is so than use OAuth2 Providers hook
		 * 
		 * @since 1.0.0
		 */
        if ($wp_query->get('oauth')) {
           require_once(dirname(__FILE__). '/lib/classes/OAuth2_API.php');
           exit;
        }
    }
	
	/**
	 * Creates a JSON output
	 * 
	 * @since 1.0.0
	 * @uses output
	 * @deprecated Generic Output. Not needed or used not more. Scheduled to be removed 1.0.1
	 */
    function pushoutput($message) {
        $this->output($message);
    }
	
    function output( $output ) {
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );

        // Commented to display in browser.
        // header( 'Content-type: application/json' );

        echo json_encode( $output );
    }
}

$OAuth2RewritesCode = new OAuth2Rewrites();

/**
 * Does not seem to be working like it should
 * 
 * @todo Take this activation hook out and rewrite class
 * @todo This is not working and will have to see why it is not flushing the rewrites properly
 */
register_activation_hook( __file__, array($OAuth2RewritesCode, 'activate') );

/**
 * Create all the hooks the link this all together with WordPress
 */
add_filter( 'rewrite_rules_array' , array($OAuth2RewritesCode , 'create_rewrite_rules' ));
add_filter( 'query_vars' , array($OAuth2RewritesCode , 'add_query_vars'));
add_filter( 'admin_init' , array($OAuth2RewritesCode , 'flush_rewrite_rules'));

/**
 * Add action hook to WordPress
 * This was just added and seems to work pretty good
 * 
 * @todo Look into this a little more clean layout and hook
 */
add_action( 'template_redirect', array($OAuth2RewritesCode, 'template_redirect_intercept') );
?>