<?php
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
	        	<p>Check out the <a href="http://wp-oauth.com/documentation/">documentation</a>.</p>
	       
	        	<form method="post" action="options.php">
	          	<?php settings_fields('wo_options'); ?>

	          	<div id="wo_tabs">
								<ul>
							  	<li><a href="#general-settings">General Settings</a></li>
							  	<li><a href="#advanced-configuration">Advanced Configuration</a></li>
							  	<li><a href="#clients">Clients</a></li>
							  	<li><a href="#server-status">Server Status</a></li>
								</ul>
							  
								<!-- GENERAL SETTINGS -->
								<div id="general-settings">
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
							  	<p>Need Help? Check out the <a href="http://wp-oauth.com/documentation/getting-started/configuring/advanced-configuration/" target="_blank">Advanced Configuration Docunentation</a></p>

			            <h3>Grant Types <hr></h3>
			            <p>Control which Grant Types that the server will accept.</p>
									<table class="form-table">
			              
			              <!--<tr valign="top">
			               	<th scope="row">Authorization Code:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[auth_code_enabled]" value="1" <?php echo $options["auth_code_enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">HTTP redirects and WP login form when authenticating.</p>
			              	  </td>
			              </tr>-->
			              
			              <tr valign="top">
			               	<th scope="row">Client Credentials:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[client_creds_enabled]" value="1" <?php echo $options["client_creds_enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">Enable "Client Credentials" Grant Type</p>
			              	  </td>
			              </tr>
			              
			              <tr valign="top">
			               	<th scope="row">User Credentials:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[user_creds_enabled]" value="1" <?php echo $options["user_creds_enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">Enable "User Credentials" Grant Type</p>
			              	  </td>
			              </tr>
			              
			              <tr valign="top">
			               	<th scope="row">Refresh Tokens:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[refresh_tokens_enabled]" value="1" <?php echo $options["refresh_tokens_enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">Enable "Refresh Token" Grant Type</p>
			              	  </td>
			              </tr>

			              <tr valign="top">
			               	<th scope="row">Allow Implicit:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[implicit_enabled]" value="1" <?php echo $options["implicit_enabled"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">Enable "Authorization Code (Implicit)" <a href="http://wp-oauth.com/documentation/server-api/which-grant-type-to-use/">What is this?</a></p>
			              	  </td>
			              </tr>
			         
			            </table>

			            <h3>Misc Settings <hr></h3>
									<table class="form-table">
										<tr valign="top">
			               	<th scope="row">Key Length</th>
			                  <td>
			                  	<input type="number" name="<?php echo $this->option_name?>[client_id_length]" min="10" value="<?php echo $options["client_id_length"]; ?>" />
			                  	<p class="description">Length of Client ID and Client Secrets when generated.</p>
			              	  </td>
			              </tr>
										<tr valign="top">
			               	<th scope="row">Require Exact Redirect URI:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[require_exact_redirect_uri]" value="1" <?php echo $options["require_exact_redirect_uri"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">Enable if exact redirect URI is required when authenticating.</p>
			              	  </td>
			              </tr>

			              <tr valign="top">
			               	<th scope="row">Enforce State Parameter:</th>
			                  <td>
			                  	<input type="checkbox" name="<?php echo $this->option_name?>[enforce_state]" value="1" <?php echo $options["enforce_state"] == "1" ? "checked='checked'" : ""; ?> />
			                  	<p class="description">Enable if the "state" paramter is required when authenticating. </p>
			              	  </td>
			              </tr>
									</table>
							  </div>

							  <!-- CLIENTS -->
							  <div id="clients">
							  	<h2>
							  		Clients 
							  		<a href="#TB_inline?width=600&height=550&inlineId=add-new-client" class="add-new-h2 thickbox" title="Add New Client">Add New Client</a>
							  	</h2>
							  	
							  	<?php
							  	$wp_list_table = new WO_Table();
									$wp_list_table->prepare_items();
									$wp_list_table->display();
									?>
							  </div>

							  <div id="server-status">
							  	<h2>Server Status</h2>
							  	<p>
							  		The following information is a guide. 
							  	</p>
							  	<table>
							  		<tr>
							  			<th style="text-align:right;">PHP Version (<?php echo PHP_VERSION; ?>): </th>
							  			<td>
							  				<?php echo version_compare(PHP_VERSION, '5.3.9') >= 0 ? " <span style='color:green;'>OK</span>":" <span style='color:red;'>Warning</span>"; ?>
							  			</td>
							  		</tr>
							  		<tr>
							  			<th style="text-align:right;">Running CGI: </th>
							  			<td><?php echo substr(php_sapi_name(), 0, 3) != 'cgi' ? " <span style='color:green;'>OK</span>":" <span style='color:orange;'>Notice</span> Header 'Authorization Basic' may not work as expected."; ?></td>
							  		</tr>
							  	</table>
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

		// Check box values
	  $input["enabled"] = isset($input["enabled"]) ? $input["enabled"] : 0;
	  $input["auth_code_enabled"] = isset($input["auth_code_enabled"]) ? $input["auth_code_enabled"] : 0;
	  $input["client_creds_enabled"] = isset($input["client_creds_enabled"]) ? $input["client_creds_enabled"] : 0;
	  $input["user_creds_enabled"] = isset($input["user_creds_enabled"]) ? $input["user_creds_enabled"] : 0;
	  $input["refresh_tokens_enabled"] = isset($input["refresh_tokens_enabled"]) ? $input["refresh_tokens_enabled"] : 0;
	  $input["implicit_enabled"] = isset($input["implicit_enabled"]) ? $input["implicit_enabled"] : 0;

	  $input["require_exact_redirect_uri"] = isset($input["require_exact_redirect_uri"]) ? $input["require_exact_redirect_uri"] : 0;
	  $input["enforce_state"] = isset($input["enforce_state"]) ? $input["enforce_state"] : 0;
	  
	  return $input;
	}
}
WPOAuth_Admin::init();