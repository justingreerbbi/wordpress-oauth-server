<?php
/**
 * WordPress OAuth Server Main Class
 * Responsible for being the main handler
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */
class WO_Server {

	/** Version */
	public $version = "3.1.5";

	/** Server Instance */
	public static $_instance = null;

	/** Default Settings */
	protected $defualt_settings = array(
		"enabled" => 1,
		"client_id_length" => 30,
		"auth_code_enabled" => 1,
		"client_creds_enabled" => 0,
		"user_creds_enabled" => 0,
		"refresh_tokens_enabled" => 0,
		"implicit_enabled" => 0,
		"require_exact_redirect_uri" => 0,
		"enforce_state" => 0,
		"refresh_token_lifetime" => 3600, // 1 Hour
		"access_token_lifetime"	=> 86400, // 24 Hours
		"use_openid_connect" => 0,
		"id_lifetime" => 3600  
	);

	/**
	 * [__construct description]
	 */
	function __construct() {

		if (!defined("WOABSPATH")) {
			define("WOABSPATH", dirname(__FILE__));
		}

		if (!defined("WOURI")) {
			define("WOURI", plugins_url("/", __FILE__));
		}

		if (function_exists("__autoload")) {
			spl_autoload_register("__autoload");
		}
		spl_autoload_register(array($this, 'autoload'));

		/**
		 * Custom auth hook
		 * This MUST run before anything just to be safe. 20 seems to be the highest so we will stay close with 
		 * priority set to 21
		 *
		 * @since 3.1.3
		 */
		add_filter( 'determine_current_user', array($this, '_wo_authenicate_bypass'), 10);

		/** 
		 * load all dependents
		 *
		 * @since 1.0.0
		 */
		add_action("init", array(__CLASS__, "includes"));

	}

	/**
	 * Awesomeness for 3rd party support
	 * 
	 * Filter; determine_current_user
	 * Other Filter: check_authentication
	 *
	 * This creates a hook in the determine_current_user filter that can check for a valid access_token and 
	 * user services like WP JSON API and WP REST API.
	 * @param  [type] $o [description]
	 * @return [type]    [description]
	 *
	 * @author Mauro Constantinescu Modified slightly but still a contribution to the project.
	 */
	public function _wo_authenicate_bypass( $user_id ) {
		if ( $user_id && $user_id > 0 ) 
			return (int)$user_id;

		/** Extra code but if the user is already logged in, there is no need to re query the DB */
		$o = get_option( 'wo_options' );
		if ( $o['enabled'] == 0 ) 
		return (int)$user_id;
		
		require_once( dirname( WPOAUTH_FILE ) . '/library/OAuth2/Autoloader.php');
		OAuth2\Autoloader::register();
		$server = new OAuth2\Server( new OAuth2\Storage\Wordpressdb() );
		$request = OAuth2\Request::createFromGlobals();
		if ( $server->verifyResourceRequest( $request ) ) {
			$token = $server->getAccessTokenData( $request );
			if ( isset( $token['user_id'] ) && $token['user_id'] > 0 )
				return (int)$token['user_id'];	
		}
	}

	/**
	 * populate the instance if the plugin for extendability
	 * @return object plugin instance
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * setup plugin class autoload
	 * @return void
	 */
	public function autoload($class) {
		$path = null;
		$class = strtolower($class);
		$file = 'class-' . str_replace('_', '-', $class) . '.php';

		if (strpos($class, "wo_") === 0) {
			$path = dirname(__FILE__) . '/library/' . trailingslashit(substr(str_replace('_', '-', $class), 18));
		}

		if ($path && is_readable($path . $file)) {
			include_once $path . $file;
			return;
		}
	}

	/**
	 * plugin includes called during load of plugin
	 * @return void
	 */
	public static function includes() {
		require_once dirname(__FILE__) . '/includes/functions.php';
		require_once dirname(__FILE__) . '/includes/filters.php';
		require_once dirname(__FILE__) . '/includes/admin-options.php';
		require_once dirname(__FILE__) . '/includes/rewrites.php';

		/** include the ajax class if DOING_AJAX is defined */
		if (defined('DOING_AJAX')) {
			require_once dirname(__FILE__) . '/includes/ajax/class-wo-ajax.php';
		}

		/** Daily Crons */
		if ( ! wp_next_scheduled( 'wo_daily_tasks_hook' ) ) {
		  wp_schedule_event( time(), 'hourly', 'wo_daily_tasks_hook' );
		}
	}

	/**
	 * plugin setup. this is only ran on activation
	 * @return [type] [description]
	 */
	public function setup() {
		$options = get_option("wo_options");
		if (!isset($options["enabled"])) {
			update_option("wo_options", $this->defualt_settings);
		}

		$this->install();
	}

	/**
	 * Plugin Init
	 * Runs checks and rgisters welcome screen
	 * @return [type] [description]
	 */
	public function plugin_init () {
		add_dashboard_page(
			__( 'About WP OAuth',  'wp-oauth' ),
			__( 'About WP OAuth',  'wp-oauth' ),
			'manage_options',
			'wpo-about',
			array( $this, 'about_screen' )
		);
	}

	/**
	 * About Screen
	 * Page loaded when plugin activation hook
	 * @return [type] [description]
	 */
	public function about_screen ()
	{
		wp_enqueue_style('wo_admin');
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( esc_html__( 'Welcome to WP OAuth Server %s', 'wp-oauth' ), $this->version ); ?></h1>
			<div class="about-text"><?php printf( esc_html__( 'Thank You for using WP OAuth Server %s. WordPress OAuth Server is bundled with everything you need to run your own OAuth 2.0 Provider Server.', 'wp-oauth' ), $this->version ); ?></div>
			<div class="wo-badge">Version <?php echo $this->version; ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpo-about' ), 'index.php' ) ) ); ?>">
					<?php esc_html_e( 'What&#39;s New', 'wp-oauth' ); ?>
				</a>
				<a class="nav-tab" href="https://wp-oauth.com/knowledge-base/" target="_blank">
					<?php esc_html_e( 'Documentation', 'wp-oauth' ); ?>
				</a>
				<a class="nav-tab" href="https://wordpress.org/support/view/plugin-reviews/oauth2-provider#rate-response" target="_blank">
					<span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( 'Rate Plugin', 'wp-oauth' ); ?>
				</a>
				<a class="nav-tab" href="https://wp-oauth.com/" target="_blank">
					<?php esc_html_e( 'Purchase License', 'wp-oauth' ); ?>
				</a>
			</h2>

			<div class="changelog">

				<!-- Feature Headline -->
				<div class="changelog headline-feature">
					
					<div style="text-align: center;">
						<h2 style="text-align: center;">So What's New?</h2>
						<strong>
							This release of WP OAuth Server is a big one when it comes to OpenID Connect and some big name OpenID clients. 
						</strong>
					</div>

					<div class="feature-section">
						<div class="col">
							<h3>Version <?php echo $this->version; ?> changelog</h3>
							<p>
								<ul>
									<li>
										- Forced all expires_in parameter in JSON to be an integer.
									</li>
									<li>
										- Add determine_current_user hook.
									</li>
								</ul>
							</p>
						</div>
						<div class="col">
							<h3>WP REST API SUPPORT</h3>
							<p>
								<?php echo $this->version; ?> adds authentication support for WP REST API.
							</p>
							<img src="<?php echo plugins_url('assets/images/openid-config-json.png', WPOAUTH_FILE ); ?>" />
						</div>
					</div>

					<div class="feature-section">
						<div class="col">
							<h3>Server Signing</h3>
							<p>
								<?php echo $this->version; ?> adds even more security by signing OpenID tokens. This makes your OAuth Server have its own finger print which then can be verified at any point by a client.
							</p>
							<div style="text-align: center">
								<img src="<?php echo plugins_url('assets/images/cer.png', WPOAUTH_FILE ); ?>" />
							</div>
						</div>
						<div class="col">
							<h3>Credits</h3>
							<p>
								It is never a one person job to make a product like WP OAuth Server. Whether it the core team or the community, we want to make sure that they get the credit they deserve.
								<ul>
									<li>- WP OAuth Core Development Team</li>
									<li>- Mauro Constantinescu WP REST API support.</li>
								</ul>
							</p>
						</div>
					</div>

					<div class="clear"></div>
				</div>

				<hr />
				<div class="changelog feature-list">
					<hr>
					<div class="return-to-dashboard">
								<a href="<?php echo esc_url( admin_url("plugins.php")); ?>">Return to Plugins</a> |
								<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wo_settings' ), 'options-general.php' ) ) ); ?>">Go to Dashboard → OAuth Server</a>
					</div>
				</div>

		<?php
	}

	/**
	 * plugin update check
	 * @return [type] [description]
	 */
	public function install() {
		/** Install the required tables in the database */
		global $wpdb;
		$charset_collate = '';

		/** Set charset to current wp option */
		if (!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		/** Set collate to current wp option */
		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		/** Update the version in the database */
		update_option("wpoauth_version", $this->version);

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
				id									 INT 						NOT NULL AUTO_INCREMENT,
				access_token         VARCHAR(4000) 	NOT NULL,
        client_id            VARCHAR(80)    NOT NULL,
        user_id              VARCHAR(80),
        expires              TIMESTAMP      NOT NULL,
        scope                VARCHAR(4000),
        PRIMARY KEY (id)
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
        id_token            VARCHAR(3000),
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

		$sql7 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}oauth_public_keys (
        client_id            VARCHAR(80),
        public_key           VARCHAR(2000),
        private_key          VARCHAR(2000),
        encryption_algorithm VARCHAR(100) DEFAULT 'RS256',
        PRIMARY KEY (client_id)
      );
			";

		/** @todo LEGACY id_token PATCH - REMOVE IN VERSION 3.2.0*/
		$wpdb->query("ALTER TABLE {$wpdb->prefix}oauth_authorization_codes MODIFY id_token VARCHAR(3000)");

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql1);
		dbDelta($sql2);
		dbDelta($sql3);
		dbDelta($sql4);
		dbDelta($sql5);
		dbDelta($sql6);
		dbDelta($sql7);

		/**
		 * Create certificates for signing
		 *
		 */
		if(function_exists('openssl_pkey_new')){
			$res = openssl_pkey_new(array(
			    "private_key_bits" => 2048,
			    "private_key_type" => OPENSSL_KEYTYPE_RSA,
			));
			openssl_pkey_export($res, $privKey);
			file_put_contents(dirname(WPOAUTH_FILE) . '/library/keys/private_key.pem', $privKey);

			$pubKey = openssl_pkey_get_details($res);
			$pubKey = $pubKey["key"];
			file_put_contents(dirname(WPOAUTH_FILE) . '/library/keys/public_key.pem', $pubKey);
		}

	}

	/**
	 * Upgrade method
	 * 
	 */
	public function upgrade () {
		$options = get_option('wo_options');

		// added 3.0.4
		if(!$options['access_token_lifetime'])
			$options['access_token_lifetime'] = 3600;

		// added 3.0.4
		if(!$options['refresh_token_lifetime'])
			$options['refresh_token_lifetime'] = 86400;

		// added 3.0.5
		if(!$options['id_token_lifetime'])
			$options['id_token_lifetime'] = 3600;

		// added 3.0.5
		if(!$options['use_openid_connect'])
			$options['use_openid_connect'] = 3600;

		update_option('wo_options', $options);

		/** @todo Modification of collation for existing tables. Implanting in 3.2.0 */
		//global $wpdb;
		//$wpdb->query("alter table <some_table> convert to character set utf8 collate utf8_unicode_ci;");

	}

}

function _WO() {
	return WO_Server::instance();
}
$GLOBAL['WO'] = _WO();