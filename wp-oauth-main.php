<?php
class WO_Server
{
	public static $version = "3.0.0";
	public static $_instance = null;
	protected $defualt_settings = array(
		"enabled" 											=> 1,
		"client_id_length" 							=> 30,
		"auth_code_enabled" 						=> 1,
		"client_creds_enabled" 					=> 0,
		"user_creds_enabled" 						=> 0,
		"refresh_tokens_enabled"	 			=> 0,
		"implicit_enabled"							=> 0,
		"require_exact_redirect_uri"		=> 0,
		"enforce_state"									=> 0
		);

	function __construct ()
	{
		if (! defined( "WOABSPATH" ) )
			define("WOABSPATH", dirname( __FILE__ ) );
		if (! defined( "WOURI" ) )
				define( "WOURI", plugins_url("/", __FILE__) );

		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
		
		/** load all dependants */
		add_action("init", array(__CLASS__, "includes"));

		/** check if permalinks are set */
    if (! get_option('permalink_structure') )
        add_action('admin_notices', array(__CLASS__, 'permalink_notice'));
	}

	/**
	 * populate the instance if the plugin for exstendability
	 * @return object plugin instance
	 */
	public static function instance ()
	{
		if ( is_null( self::$_instance ) ) 
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * setup plugin class autoload
	 * @return void
	 */
	public function autoload ($class)
	{
		$path  = null;
		$class = strtolower( $class );
		$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

		if( strpos( $class, "wo_") === 0 )
		{
			$path = dirname( __FILE__ ) . '/library/' . trailingslashit(substr(str_replace( '_', '-', $class ), 18));
		}

		if ( $path && is_readable( $path . $file ) ) 
		{
			include_once( $path . $file );
			return;
		}
	}

	/**
	 * plugin includes called during load of plugin
	 * @return void
	 */
	public static function includes ()
	{
		require_once( dirname(__FILE__) . '/includes/functions.php');
		require_once( dirname(__FILE__) . '/includes/admin-options.php');
		require_once( dirname(__FILE__) . '/includes/rewrites.php');
		require_once( dirname(__FILE__) . '/includes/filters.php');
		
		/** include the ajax class if DOING_AJAX is defined */
		if ( defined( 'DOING_AJAX' ) )
			require_once( dirname(__FILE__) . '/includes/ajax/class-wo-ajax.php');
	}

	/**
	 * plugin setup. this is only ran on activation
	 * @return [type] [description]
	 */
	public function setup ()
	{
		$options = get_option("wo_options");
		if(!isset($options["enabled"]) )
			update_option("wo_options", $this->defualt_settings);

		$this->install();
	}

	/** 
	 * Error is the permalinks are not set
	 * @return [type] [description]
	 */
	public function permalink_notice ()
	{
		 echo '<div id="message" class="error"><p>WordPress OAuth Server Requires <a href="options-permalink.php">Permalinks</a> other than <strong>Default</strong>.</p></div>';
	}

	/**
	 * plugin update check
	 * @return [type] [description]
	 */
	public function install ()
	{
		/** install the required tables in the database */
		global $wpdb;
		$charset_collate = '';
		
		/** set charset to current wp option */
		if ( ! empty( $wpdb->charset ) )
  		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

  	/** set collate to current wp option */
		if ( ! empty( $wpdb->collate ) )
  		$charset_collate .= " COLLATE {$wpdb->collate}";

		/** update the plugin version in the database */
		update_option("wpoauth_version", self::$version);

		$sql1 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_clients (
        client_id             VARCHAR(80)   NOT NULL,
        client_secret         VARCHAR(80)   NOT NULL,
        redirect_uri          VARCHAR(2000),
        grant_types           VARCHAR(80),
        scope                 VARCHAR(4000),
        user_id               VARCHAR(80),
        name                  VARCHAR(80),
        description           LONGTEXT,
        PRIMARY KEY (client_id)
      );
			";
			
			$sql2 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_access_tokens (
				access_token         VARCHAR(40)    NOT NULL,
        client_id            VARCHAR(80)    NOT NULL,
        user_id              VARCHAR(80),
        expires              TIMESTAMP      NOT NULL,
        scope                VARCHAR(4000),
        PRIMARY KEY (access_token)
      );
			";
			
			$sql3 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_refresh_tokens (
				refresh_token       VARCHAR(40)    NOT NULL,
        client_id           VARCHAR(80)    NOT NULL,
        user_id             VARCHAR(80),
        expires             TIMESTAMP      NOT NULL,
        scope               VARCHAR(4000),
        PRIMARY KEY (refresh_token)
      );
			";

			$sql4 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_authorization_codes (
        authorization_code  VARCHAR(40)    NOT NULL,
        client_id           VARCHAR(80)    NOT NULL,
        user_id             VARCHAR(80),
        redirect_uri        VARCHAR(2000),
        expires             TIMESTAMP      NOT NULL,
        scope               VARCHAR(4000),
        id_token            VARCHAR(1000),
        PRIMARY KEY (authorization_code)
      );
			";
			
			$sql5 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_scopes (
        scope               VARCHAR(80)  NOT NULL,
        is_default          BOOLEAN,
        PRIMARY KEY (scope)
      );
			";

			$sql6 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_jwt (
        client_id           VARCHAR(80)   NOT NULL,
        subject             VARCHAR(80),
        public_key          VARCHAR(2000) NOT NULL,
        PRIMARY KEY (client_id)
      );
			";

			$sql6 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_public_keys (
        client_id            VARCHAR(80),
        public_key           VARCHAR(2000),
        private_key          VARCHAR(2000),
        encryption_algorithm VARCHAR(100) DEFAULT 'RS256',
        PRIMARY KEY (client_id)
      );
			";
			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql1 );	
		dbDelta( $sql2 );	
		dbDelta( $sql3 );	
		dbDelta( $sql4 );	
		dbDelta( $sql5 );	
		dbDelta( $sql6 );	
	}

}

function _WO ()
{
	return WO_Server::instance();
}
$GLOBAL['WO'] = _WO();