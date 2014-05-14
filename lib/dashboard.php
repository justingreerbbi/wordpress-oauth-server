<?php 
 /**
  * DASHBOARD UI
  * 
  * @author Justin Greer <support@wpkeeper.com>
  * @version 1.0.1
  */
function wp_oauth2_complete_init_dashboard() {
	
	require_once(plugin_dir_path( __FILE__ )."classes/admin/IOAuth2Storage.php");	// INCLUDE OAuth 2.0 STORAGE
	require_once(plugin_dir_path( __FILE__ )."classes/admin/OAuthMain.php"); 		 // INCLUDE THE OAuth ADMIN OBJECT
	$oauthStorage = new IOAuth2StorageWP();	    // STORAGE OBJECT
	$admin = new oauthAdmin();					 // ADMIN OBJECT
	
	$messageType;								  // MESSAGE TYPE HOLDER							
	$messagetext;								  // MESSAGE TEXT HOLDER
	
  	wp_register_style( 'wp_oauth2_provider_stylesheet', WP_OAUTH2_URL . '/lib/assets/css/layout.css' );
  	wp_enqueue_style('wp_oauth2_provider_stylesheet' );

	if(isset($_POST['op2action']) && $_POST['op2action'] == 'Add Client'){
		$oauthStorage->addClient($_POST['mdop_name'], $_POST['mdop_redirect_uri']);
		}
	if(isset($_GET['delete']) && $_GET['delete'] != ''){
		global $wpdb;
		$wpdb->delete($wpdb->prefix.'oauth2_clients', array('client_id'=> $_GET['delete']));
		}
	
	// Added to be used through out the plugin backend
	$adminUrl = admin_url();
?>
   
	<h2 class="section_title"> WordPress OAuth2 Provider </h2>
	<hr />

	<aside id="sidebar" class="column">
			<h3>Support</h3>
			<hr />
			<p></p>
            <p><a href="https://github.com/justingreerbbi/wordpress-oauth" target="_blank">GitHub Home</a></p>
            <p><a href="https://github.com/justingreerbbi/wordpress-oauth" target="_blank">Online Documentation</a></p>	
	</aside><!-- end of sidebar -->
	
	<section id="main" class="column">
		<?php if (!empty($messageType) && !empty($messageText)):?>
			<h4 class="<?php print $messageType; ?>"><?php print $messageText; ?></h4>
		<?php endif; ?>
		
		<article class="module width_full">
		<header>
        	<h3 class="tabs_involved">Consumer Manager <span style="float:right;"><?php print $admin->ConsumerCount(); ?> Consumers</span></h3>
		</header>

		<div class="tab_container">
			<div id="tab1" class="tab_content">
			<table class="tablesorter" cellspacing="0"> 
			<thead> 
				<tr> 
   					<th>Name</th> 
    				<th>Key</th> 
    				<th>Secret</th> 
    				<th>Redirect URI</th> 
    				<th>Actions</th> 
				</tr> 
			</thead> 
			<tbody> 
				<?php $admin->listConsumers(); ?>
			</tbody> 
			</table>
			</div><!-- end of #tab1 -->
            
		</div><!-- end of .tab_container -->
		
		</article><!-- end of content manager article -->
		
		
		<div class="clear"></div>
        
        <!-- MESSAGES FROM THE CREATORS -->
        <article class="module width_3_quarter">
			<header><h3>Add Client</h3></header>
			<div class="module_content">
				<form name="mdop_add_client" method="post" action="<?php print $adminUrl; ?>admin.php?page=wp_oauth2_complete">
                	<table width="564" border="0">
                      <tr>
                        <td>Name:</td>
                        <td><input type="text" name="mdop_name" id="mdop" value=""/></td>
                        <td>Redirect URI</td>
                        <td><input type="text" name="mdop_redirect_uri" id="mdop_redirect_uri" value="" /></td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><input type="submit" name="op2action" value="Add Client"/></td>
                      </tr>
                    </table>
              </form>
				<div class="clear"></div>
			</div>
		</article>
		
		<!--<h4 class="alert_warning">A Warning Alert</h4>
		
		<h4 class="alert_error">An Error Message</h4>
		
		<h4 class="alert_success">A Success Message</h4>-->
		
		<div class="spacer"></div>
	</section>
    
    
<?php } ?>