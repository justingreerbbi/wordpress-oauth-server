<?php
/**
 * Admin Menu 
 */
class WPOAuth_Admin_Main {

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
      add_options_page('OAuth Options', 'OAuth Options', 'manage_options', 'wo_options', array($this, 'options_do_page'));
  }

  /**
   * [options_do_page description]
   * @return [type] [description]
   */
  public function options_do_page() {
      $options = get_option($this->option_name);
      ?>
      <div class="wrap">
          <h2>WordPress OAuth Options</h2>
          <form method="post" action="options.php">
              <?php settings_fields('wo_options'); ?>
              <table class="form-table">
                  <tr valign="top">
                    <th scope="row">API Enabled:</th>
                      <td>
                        <input type="checkbox" name="<?php echo $this->option_name?>[enabled]" value="1" <?php echo $options["enabled"] == "1" ? "checked='checked'" : ""; ?> />
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