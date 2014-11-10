<?php
/**
 * Admin Menu 
 */
class WPOAuth_Admin {

	/**
	 * WO Options Name
	 * @var string
	 */
	protected $option_name = 'wo_options';

	/**
	 * [_init description]
	 * @return [type] [description]
	 */
	public static function init ()
	{
		add_action('admin_init', array(new self, 'admin_init'));
		add_action('admin_menu', array(new self, 'add_page'));
	}

	/**
	 * [admin_init description]
	 * @return [type] [description]
	 */
	public function admin_init() {
	    register_setting('wo_options', $this->option_name, array($this, 'validate'));
	}

	/**
	 * [add_page description]
	 */
	public function add_page() {
	    add_options_page('OAuth Settings', 'OAuth Settings', 'manage_options', 'wo_settings', array($this, 'options_do_page'));
	}

	/**
	 * loads the plugin styles and scripts into scope
	 * @return [type] [description]
	 */
	public function admin_head ()
	{
		wp_enqueue_style( 'wo_admin' );
		wp_enqueue_script( 'wo_admin' );
		wp_enqueue_script( 'jquery-ui-tabs' );
	}

	/**
	 * [options_do_page description]
	 * @return [type] [description]
	 */
	public function options_do_page() {
	    $options = get_option( $this->option_name );
    	$this->admin_head();
	    ?>
	    <div class="wrap">
	        <h2>Server Confirguration</h2>
	        <p>Need Help? Check out the <a href="#">documentation</a></p>
	        <div class="updated error">
			        <p>Be sure to understand OAuth protocols before you start making changes.</p>
			    </div>
	        <form method="post" action="options.php">
	            <?php settings_fields('wo_options'); ?>

	            <!-- Tabs Trials -->
	            <div id="wo_tabs">
							  <ul>
							    <li><a href="#tabs-1">General Settings</a></li>
							    <li><a href="#advanced-configuration">Advanced Configuration</a></li>
							  </ul>
							  <div id="tabs-1">
							  	<h3>General Settings</h3>
							  	<p>
							  		General setting are where you can do, blah blah blah
							  	</p>
							  	<table class="form-table">
			                <tr valign="top">
			                	<th scope="row">API Enabled:</th>
			                    <td>
			                    	<input type="checkbox" name="<?php echo $this->option_name?>[enabled]" value="1" <?php echo $options["enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                    	<p class="description">When disabled, API will present a "Server is Temporarily Unavailable" message.</p>
			                    </td>
			                </tr>
			                <tr valign="top">
			                	<th scope="row">Refresh Tokens Enabled:</th>
			                    <td>
			                    	<input type="checkbox" name="<?php echo $this->option_name?>[refresh_tokens_enabled]" value="1" <?php echo $options["refresh_tokens_enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                    </td>
			                </tr>
			                <tr valign="top">
			                	<th scope="row">
			                		Refresh Token Lifespan:
			                	</th>
			                    <td>
			                    	<input type="number" name="<?php echo $this->option_name?>[refresh_token_lifespan]" min="0" max="100" value="<?php echo $options["refresh_token_lifespan"]; ?>" />
			                    	<select name="<?php echo $this->option_name?>[refresh_token_lifespan_unit]?>">
			                    		<option value="minute">Minute</option>
			                    		<option value="hour">Hours</option>
			                    		<option value="day">Days</option>
			                    		<option value="month">Months</option>
			                    		<option value="years">Years</option>
			                    	</select>
			                    	<p class="description">0 = Never Expires</p>
			                    </td>
			                </tr>

			                <tr valign="top">
			                	<th scope="row">
			                		Auth code lifespan:
			                	</th>
			                    <td>
			                    	<input type="number" name="<?php echo $this->option_name?>[auth_code_expiration_time]" min="1" max="10" value="<?php echo $options["auth_code_expiration_time"]; ?>" /> Minutes
			                    	<p class="description">Between 1 and 10 minutes.</p>
			                    </td>
			                </tr>

			                <tr valign="top">
			                	<th scope="row">
			                		Access Token lifespan:
			                	</th>
			                    <td>
			                    	<input type="number" name="<?php echo $this->option_name?>[access_token_lifespan]" value="<?php echo $options["access_token_lifespan"]; ?>" /> seconds
			                    	<p class="description">0 = Unlimited</p>
			                    </td>
			                </tr>
			            </table>  
							  </div>
 
							  <!-- ADVANCED CONFIGURATION -->
							  <div id="advanced-configuration">
							 		<h3>Advanced Configuration</h3>
							    <table class="form-table">
			                <tr valign="top">
			                	<th scope="row">Client ID Length</th>
			                    <td>
			                    	<input type="number" name="<?php echo $this->option_name?>[client_id_length]" min="60" value="<?php echo $options["client_id_length"]; ?>" />
			                    </td>
			                </tr>

			                <tr valign="top">
			                	<th scope="row">Additional Key Characters</th>
			                    <td>
			                    	<input type="text" name="<?php echo $this->option_name?>[additional_key_characters]" value="<?php echo $options["additional_key_characters"]; ?>" />
			                    </td>
			                </tr>
			            </table>
							  </div>
							

							</div>



	            
	            <p class="submit">
	                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	            </p>
	        </form>
	    </div>
	    <?php
	}

	/**
	 * WO options validation
	 * @param  [type] $input [description]
	 * @return [type]        [description]
	 */
	public function validate($input) {
	    $input["enabled"] = isset($input["enabled"]) ? $input["enabled"] : 0;
	    return $input;
	}
}
WPOAuth_Admin::init();