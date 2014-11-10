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
		"enabled" 						=> 1,
		"refresh_tokens_enabled" 		=> 1,
		"refresh_token_lifespan" 		=> 1,
		"refresh_token_lifespan_unit" 	=> "year",
		"auth_code_expiration_time" 	=> 10,
		"access_token_lifespan"	 		=> 3600
		);

	/**
	 * Construct Mehtod
	 * Setup autoload functionality and initiate plugin inlcudes
	 */
	function __construct ()
	{
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
		
		add_action("wp_loaded", array(__CLASS__, "includes"));
		register_activation_hook(__FILE__, array($this, 'setup'));
	}

	/**
	 * Load intance of the plugin
	 */
	public static function instance ()
	{
		if ( is_null( self::$_instance ) ) 
		{
			self::$_instance = new self();
		}
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
		require_once( dirname(__FILE__) . '/includes/admin-options.php');
		require_once( dirname(__FILE__) . '/includes/rewrites.php');
		require_once( dirname(__FILE__) . '/includes/filters.php');
		require_once( dirname(__FILE__) . '/includes/types.php');

		// If WP is doing any ajax calls, let include them.
		if ( defined( 'DOING_AJAX' ) ) {
			$this->ajax_includes();
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