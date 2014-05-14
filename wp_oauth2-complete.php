<?php
/*
Plugin Name: WP OAuth2 Complete
Plugin URI: https://github.com/justingreerbbi/wordpress-oauth
Description: Allows Wordpress to use OAuth2 structure and become a provider
Version: 2.0.0
Author: Justin Greer (justingreerbbi), Joel Wickard (jwickard), Neil Pullman (neiltron)
Author URI: https://github.com/justingreerbbi/wordpress-oauth
License: GPL2
*/

class WP_OAuth {

	/**
	 * @var version - WP OAuth2 Version
	 */
	public $version = '2.0.0';

	/**
	 * _construct
	 */
	function __construct(){
		add_action( 'plugins_loaded' , array( $this, '_init' ));
		add_action( 'wp_enqueue_scripts', array( $this, '_registerStyles') );
		add_action( 'admin_menu', array( $this, '_adminMenu') );
	}

	/**
	 * _start
	 * Defines all the basic awesomness that is needed thoughout the plguin
	 * @action wp_oauth2_start
	 */
	function _start(){
		do_action( 'wp_oauth2_start' );
		define( 'WP_OAUTH2_PATH', plugin_dir_path(__FILE__));
		define( 'WP_OAUTH2_ABSPATH' , dirname( __FILE__ ) );
		define( 'WP_OAUTH2_URL', plugins_url('/', __FILE__ ) );
	}

	/**
	 * _init
	 * Kickstarts the plugin
	 * @action wp_oauth2_init
	 */
	function _init(){
		do_action( "wp_oauth2_init" );
		$this->_start();
		$this->_includes();
		$this->_load();
	}

	/**
	 * _includes
	 * Inludes all the init files for the plugin
	 * 
	 * @action wp_oauth2_init
	 * @todo move all the includes into an array and pass them through a filter.
	 * This will allow for developers to create custom hooks if need be. 
	 */
	function _includes(){
		do_action( "wp_oauth2_init" );
		require_once( dirname(__FILE__).'/lib/dashboard.php');
		require_once( dirname(__FILE__).'/lib/rewrites.php');
	}

	/**
	 * _load
	 * description will go here
	 */
	function _load(){

		// @todo Tie in language file in the future
		// load_plugin_textdomain('wpbu', false, basename( dirname( __FILE__ ) ) . '/languages');
	}

	/**
	 * _registerStyles
	 * Registers the plugin stylsheet
	 * @action wp_oauth2_styles
	 */
	function _registerStyles(){
		do_action('wp_oauth2_styles');
		wp_register_style( 'wp_oauth2_provider_stylesheet', plugins_url( '/lib/assets/css/layout.css') );
	}

	/**
	 * _adminMenu
	 * Sets the plugin admin menu
	 */
	function _adminMenu(){
		add_menu_page( 'WP OAuth2 Complete', 'Provider', 'manage_options', 'wp_oauth2_complete', 'wp_oauth2_complete_init_dashboard' );
	}

	/**
	 * _activate
	 * Contains the SQL bare bones when activating WP_OAuth2
	 */
	function _activate(){

		// Run a legacy upgrade first - This may be a life saver ;)
		$this->_legacyupgrade();

		// Used for gather the db prefix
		global $wpdb;

		// OPTION TABLE
		$install_options_table = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."oauth2_options (
	  	id INT NOT NULL AUTO_INCREMENT,
	 	version VARCHAR(55) DEFAULT '' NOT NULL,
		enabled INT(1) NOT NULL,
		draft INT(1) NOT NULL,
	  	UNIQUE KEY id (id)
	    );";
		
		// CONSUMER TABLE
	   	$install_auth_table = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."oauth2_auth_codes (
		code varchar(40) NOT NULL,
		client_id varchar(40) NOT NULL,
		user_id int(11) UNSIGNED NOT NULL,
		redirect_uri varchar(200) NOT NULL,
		expires int(11) NOT NULL,
		scope varchar(255) DEFAULT NULL,
		PRIMARY KEY (code)
	  	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		
		// CONSUMER NONCE TABLE
	   	$install_clients_table = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."oauth2_clients (
		name varchar(40) NOT NULL,
		client_id varchar(40) NOT NULL,
		client_secret varchar(20) NOT NULL,
		redirect_uri varchar(255) NOT NULL,
		PRIMARY KEY (client_id)
	  	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		  
		// TOKEN TABLE
	   	$install_token_table = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."oauth2_access_tokens (
		oauth_token varchar(40) NOT NULL,
		client_id varchar(40) NOT NULL,
		user_id int(11) UNSIGNED NOT NULL,
		expires int(11) NOT NULL,
		scope varchar(255) DEFAULT NULL,
		PRIMARY KEY (oauth_token)
	  	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	  
	  	// TOKEN REFRESH TABLE
		$install_refresh_token_table = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."oauth2_refresh_tokens (
		oauth_token varchar(40) NOT NULL,
		client_id varchar(40) NOT NULL,
		user_id int(11) UNSIGNED NOT NULL,
		expires int(11) NOT NULL,
		scope varchar(255) DEFAULT NULL,
		PRIMARY KEY (oauth_token)
	  	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
		
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($install_options_table);
	    dbDelta($install_auth_table);
	    dbDelta($install_clients_table);
	    dbDelta($install_token_table);
	    dbDelta($install_refresh_token_table);

	}

	/**
	 * _legacyupgrade
	 * Creates for pre existing tables form older installs so that data is not lost during upgrade from 1.x to verson 2.X
	 * @link http://dev.mysql.com/doc/refman/5.0/en/rename-table.html
	 * @todo Do this right. As of right now we are just supressing errors. If someone wants to make this right, be my guest but it works for me.
	 */
	function _legacyupgrade(){
		global $wpdb;
		$wpdb->hide_errors();
		@$wpdb->query("RENAME TABLE oauth2_options TO {$wpdb->prefix}oauth2_options");
		@$wpdb->query("RENAME TABLE oauth2_auth_codes TO {$wpdb->prefix}oauth2_auth_codes");
		@$wpdb->query("RENAME TABLE oauth2_access_tokens TO {$wpdb->prefix}oauth2_access_tokens");
		@$wpdb->query("RENAME TABLE oauth2_refresh_tokens TO {$wpdb->prefix}oauth2_refresh_tokens");
		$wpdb->show_errors();
	}

	/**
	 * _deactivate
	 * Not being used as of version 2.0.0
	 */
	function _deactivate(){}

}
$WP_OAuth = new WP_OAuth;
register_activation_hook(__FILE__, array( $WP_OAuth, '_activate'));