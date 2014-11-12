<?php
class WO_Server
{

	/**
	 * @var version Current version of the plugin
	 */
	public static $version = "1.0.0";

	/**
	 * @var _instance Current scope of the plugin
	 */
	public static $_instance = null;

	/**
	 * Default settings
	 * @var array
	 */
	protected $defualt_settings = array(
		"enabled" 											=> 1,
		"refresh_tokens_enabled" 				=> 1,
		"refresh_token_lifespan" 				=> 1,
		"refresh_token_lifespan_unit" 	=> "year",
		"auth_code_expiration_time" 		=> 10,
		"access_token_lifespan"	 				=> 3600,
		"client_id_length"							=> 30,
		"license"												=> null
		);

	/**
	 * Construct Mehtod
	 * Setup autoload functionality and initiate plugin inlcudes
	 */
	function __construct ()
	{

		/** define some basics  */
		if (! defined( "WOABSPATH" ) )
			define("WOABSPATH", dirname( __FILE__ ) );
		if (! defined( "WOURI" ) )
				define( "WOURI", plugins_url("/", __FILE__) );

		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
		
		add_action("init", array(__CLASS__, "includes"));
		add_action("wp_loaded", array(__CLASS__, "register_scripts"));
		add_action("wp_loaded", array(__CLASS__, "register_styles"));

		/** activation hook for the server */
		register_activation_hook(__FILE__, array($this, 'setup'));
	}

	/**
	 * Load intance of the plugin
	 */
	public static function instance ()
	{
		if ( is_null( self::$_instance ) ) 
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @todo  Convert
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'oauthserver' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @todo  Convert
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'oauthserver' ), '2.1' );
	}

	/**
	 * Autoload all the classes on demand.
	 * All WO classes are located in library directory.
	 * @return [type] [description]
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
	 * WP OAuth includes. Nothing to special here
	 * @return void
	 */
	public static function includes ()
	{
		require_once( dirname(__FILE__) . '/includes/functions.php');
		require_once( dirname(__FILE__) . '/includes/upgrade.php');
		require_once( dirname(__FILE__) . '/includes/admin-options.php');
		require_once( dirname(__FILE__) . '/includes/rewrites.php');
		require_once( dirname(__FILE__) . '/includes/filters.php');
		
		/** include the ajax class if DOING_AJAX is defined */
		if ( defined( 'DOING_AJAX' ) ) {
			require_once( dirname(__FILE__) . '/includes/ajax/class-wo-ajax.php');
		}
	}

	/**
	 * Ajax inlcludes
	 * @return void
	 */
	public function ajax_includes() {
		include_once( 'includes/class-wo-ajax.php' );
	}

	/**
	 * register plugin styles
	 * @return [type] [description]
	 */
	public function register_styles ()
	{
		wp_register_style( 'wo_admin', plugins_url( '/assets/css/admin.css', __FILE__ )  );
	}

	/**
	 * register plugin scripts
	 * @return [type] [description]
	 */
	public function register_scripts ()
	{
		wp_register_script( 'wo_admin', plugins_url( '/assets/js/admin.js', __FILE__ ) );
	}

	/**
	 * [setup description]
	 * @return void
	 */
	public function setup ()
	{
		$options = get_option("wo_options");
		if(! isset($options["enabled"]) )
			update_option("wo_options", $this->defualt_settings);
	}

}

function _WO ()
{
	return WO_Server::instance();
}
$GLOBAL['WO'] = _WO();