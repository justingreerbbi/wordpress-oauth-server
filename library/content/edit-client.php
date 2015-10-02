<?php
/**
 * Edit a Client
 * @author Justin Greer <justin@justin-greer.com>
 *
 * @todo Although this file works is it is very import that we do it right. Now that we have it working and it
 * is secure, we need to start tweaking the file to be more WP compliant. Quality Matters!
 *
 * @todo Add an additional check to ensure that the form is being loaded by WordPress.
 * @todo Load WP core JS and styles for the plugin. It will be more cleaner and not rely on external JS libs.
 */

/** should stop 99% exploits */
if(! current_user_can('manage_options') || !isset($_REQUEST['client_id']) || ! wp_verify_nonce($_REQUEST['_wp_nonce'], 'wpo-edit-client') )
	wp_die('');

global $wpdb;
$client_info = $wpdb->get_row($wpdb->prepare("
	SELECT * 
	FROM {$wpdb->prefix}oauth_clients 
	WHERE client_id='%s'", array($_GET['client_id']))
);

/** simple check if the client exists */
if(!$client_info)
	exit('Unauthorized Access');

/** listen for post back */
if(isset($_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'edit-client')){
	global $wpdb;
	$update_client = $wpdb->update("{$wpdb->prefix}oauth_clients",
		array(
			'redirect_uri' => $_POST['client-redirect-uri'],
			'name' => $_POST['client-name'],
			'description' => $_POST['client-description']
			),
		array(
			'client_id' => $_GET['client_id']
		));

	print 'Reloading...<script>window.parent.location.reload();</script>';
	exit;
}
?>
<style>
body {
	background: #f1f1f1;
	font-family: Arial, Helvetica, sans-serif;
}
h2 {
	font-size: 23px;
	font-weight: 400;
	padding: 9px 15px 4px 0;
	line-height: 29px;
	margin-bottom: 10px;
}
p.intro {
	margin: 2px 0 5px;
	color: #666;
	font-style: italic;
	font-size: 13px;
}
table {
	border-collapse: collapse;
	margin-top: .5em;
	width: 100%;
	clear: both;
	
	border-left: #0074a2 solid 4px;
	margin-top: 50px;
}
th {
	vertical-align: top;
	text-align: left;
	/*padding: 20px 10px 20px 0;*/
	width: 160px;
	line-height: 1.3;
	font-weight: bold;
	padding-left: 15px;
}
th label {
	color: #222;
}

td.controls {
	text-align: right;
}
form {}
	input[type="text"]:focus,
	textarea:focus {
		outline: 0;
	}
	input[type="text"] {
		width: 25em;
		padding: 5px;
		font-size: 14px;
		background: #fefefe;
	}
	textarea {
		width: 25em;
		height: 80px;
		padding: 5px;
		font-size: 14px;
		resize: none;
		background: #fefefe;
	}
	input[type="submit"]{
		margin: 20px 0;
		background: #0074a2;
		color: #fff;
		border: none;
		padding: 10px 20px;
		margin-right: 70px;
		font-size: 14px;
	}
	fieldset {
		border: none;
	}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<div class="wo-popup-inner">
	
	<form id="example-advanced-form" action="" method="post">
    <h2>Edit Client</h2>
    <p class="intro">
    	Edit the client information below:
    </p>

    <table>
    	<tr align="top">
    		<th><label>Client Name:</label> </th>
    		<td>
    			<input type="text" name="client-name" autofocus="yes" value="<?php echo $client_info->name;?>" />
    			<br/><br/>
    		</td>
    	</tr>
    	<tr align="top">
    		<th><label>Redirect URI:</label> </th>
    		<td>
    			<input type="text" name="client-redirect-uri" placeholder="Leave Blank for Client Credentials" value="<?php echo $client_info->redirect_uri;?>"/>
    			<br/><br/>
    		</td>
    	</tr>
    	<tr align="top">
    		<th><label>Client Description:</label> </th>
    		<td><textarea name="client-description" placeholder="Short and Sweet Description"><?php echo $client_info->description;?></textarea></td>
    	</tr>
    	<tr align="top">
    		<td><asd/td>
    		<td class="controls">
    			<?php wp_nonce_field( 'edit-client' ); ?>
    			<input type="submit" value="Update Client" />
    		</td>
    	</tr>
    </table>
	</form>

</div>
<?php exit; ?>