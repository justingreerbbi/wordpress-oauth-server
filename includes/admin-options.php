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
	    add_options_page('OAuth Server Settings', 'OAuth Server', 'manage_options', 'wo_settings', array($this, 'options_do_page'));
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
    	$scopes = apply_filters('WO_Scopes', null);
    	error_reporting(0);
    	add_thickbox();
	    ?>
	    <div class="wrap">
	        <h2>Server Confirguration</h2>
	        <p>Need Help? Check out the <a href="#">Documentation</a></p>

	        <!--<div class="updated error">
			        <p>You are not licensed to use the advanced configuration portion of WordPress OAuth Server. Get your license by registering at <a href="http://wp-oauth.com" target="_blank">http://wp-oauth.com</a>.</p>
			    </div>-->
	       
	        <form method="post" action="options.php">
	            <?php settings_fields('wo_options'); ?>

	            <!-- Tabs Trials -->
	            <div id="wo_tabs">
							  
							  <!-- TABS UI -->
							  <ul>
							    <li><a href="#general-settings">General Settings</a></li>
							    <li><a href="#advanced-configuration">Advanced Configuration</a></li>
							    <li><a href="#shortcodes">Shortcodes</a></li>
							    <li><a href="#clients">Clients</a></li>
							  </ul>
							  
							  <!-- GENERAL SETTINGS -->
							  <div id="general-settings">
							  	<!--<h2>General Settings</h2>-->
							  	
							  	<table class="form-table">
			                <tr valign="top">
			                	<th scope="row">API Enabled:</th>
			                    <td>
			                    	<input type="checkbox" name="<?php echo $this->option_name?>[enabled]" value="1" <?php echo $options["enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                    	<p class="description">When disabled, API will present a "Server is Temporarily Unavailable" message.</p>
			                    </td>
			                </tr>
			            </table>  
							  </div>
 
							  <!-- ADVANCED CONFIGURATION -->
							  <div id="advanced-configuration">

							  	<h2>Advanced Configuration</h2>
							  	<p>
							  		Advanced Configuration for OAuth Server.
							  	</p>

							  	<h3>Server Type<hr></h3>
							    <table class="form-table">
			              <tr valign="top">
			               	<th scope="row">Server Type</th>
			                  <td>
			                  	<select name="<?php echo $this->option_name;?>[server_type]">
				                  	<option value="private" <?php echo $options["server_type"]=='private' ? "selected":"";?>>Private</option>
				                  	<option value="public" <?php echo $options["server_type"]=='public' ? "selected":"";?>>Public</option>
			                  	</select>
			                  	<p class="description">
			                  		<strong>Private: </strong> Only Admins can manage clients (server is used soley for an application ect..)<br>
			                  		<strong>Public: </strong> Front-end user can add clients as needed (user can create applications that integrate with your this server).
			                  	</p>
			              	  </td>
			              </tr>
			            </table>

							  	<h3>Scopes<hr></h3>
							    <table class="form-table">
			              <tr valign="top">
			               	<th scope="row">Enabled Scopes</th>
			                  <td>
			                  	<div class="notification">
			                  		Scopes not yet supported -> Still in Development
			                  	</div>
			                  	<?php foreach($scopes as $scope=>$enabled): ?>
			                  		<!--<input type="checkbox" name="<?php echo $this->option_name; ?>[scopes][<?php echo $scope; ?>]" value="1"  <?php if($this->option_name["scopes"][$scope] == 1) print 'checked="checked"'; ?> /><?php echo ucfirst($scope); ?><br/>-->
			                  	<?php endforeach; ?>
			              	  </td>
			              </tr>
			            </table>


							 		<h3>Key Generation <hr></h3>
							    <table class="form-table">
			              <tr valign="top">
			               	<th scope="row">Key Lengths</th>
			                  <td>
			                  	<input type="number" name="<?php echo $this->option_name?>[client_id_length]" min="60" value="<?php echo $options["client_id_length"]; ?>" />
			                  	<p class="description">Length of Client ID and Client Secrets when generated.</p>
			              	  </td>
			              </tr>
			              <tr valign="top">
			               	<th scope="row">Additional Key Characters</th>
			                  <td>
			                  	<input type="text" name="<?php echo $this->option_name?>[additional_key_characters]" value="<?php echo $options["additional_key_characters"]; ?>" />
			                  	<p class="description">Keys generated use 0-9 a-Z. You can add adition characters to key that are being generated ( example: #$%^&#@)</p>
			                  </td>
			              </tr>
			            	</table>

			            	<h3>Resfresh Tokens <hr></h3>
			              <table class="form-table">
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
			              </table>

			              <h3>Tokens & Authentication Codes<hr></h3>
			              <table class="form-table">
			                <tr valign="top">
			                	<th scope="row">
			                		Authentication Code Lifespan:
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

							  <div id="shortcodes">
							  	<h2>Coming Soon</h2>
							  	<p>
							  		Shortcodes will give the front-end user the ability to control their own clients. This will only work if the OAuth Server is setup as a public server.
							  	</p>
							  	
							  	<!--<table class="form-table">
			                <tr valign="top">
			                	<th scope="row">API Enabled:</th>
			                    <td>
			                    	<input type="checkbox" name="<?php echo $this->option_name?>[enabled]" value="1" <?php echo $options["enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                    	<p class="description">When disabled, API will present a "Server is Temporarily Unavailable" message.</p>
			                    </td>
			                </tr>
			            </table>-->  
							  </div>

							  <!-- CLIENTS -->
							  <div id="clients">
							  	<h2>Clients <a href="#TB_inline?width=600&height=550&inlineId=add-new-client" class="add-new-h2 thickbox" title="Add New Client">Add New Client</a></h2>
							  	
							  	<?php
							  	$wp_list_table = new WO_Table();
									$wp_list_table->prepare_items();
									$wp_list_table->display();
									?>

							
							  </div>
 

							</div>
	            
	            <p class="submit">
	                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	            </p>
	        </form>

	        <!-- ADD NEW CLIENT HIDDEN FROM -->
	        <div id="add-new-client" style="display:none;">
						<div class="wo-popup-inner">
							<h3 class="header">Add a New Client</h3>
							<form id="create-new-client" action="/" method="get">
								<label>Client Name *</label>
								<input type="text" name="client_name" placeholder="Client Name"/>

								<label>Redirct URI *</label>
								<input type="text" name="redirect_uri" placeholder="Redirect URI"/>

								<label>Client Description</label>
								<textarea name="client_description"></textarea>

								<?php submit_button("Add Client"); ?>
							</form>
						</div>
					</div>

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