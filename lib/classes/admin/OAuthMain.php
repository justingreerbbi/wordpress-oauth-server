<?php

/**
* oauthAdmin handles the the basic main functions of the dashboard
*
* @auther Justin Greer
*/
class oauthAdmin {

	public function __consutruct(){}

	public function __destruct(){}

	/**
	* Consumers that are registered in the system
	*
	* @return int Number of Consumers Registered in the database
	*/
	public function ConsumerCount(){
		global $wpdb;
		$count = $wpdb->query("SELECT * FROM oauth2_clients");
		return $wpdb->num_rows;
	}
	
	/**
	* Formatted list of consumers
	*
	* @return string Formatted list of the registered consumers
	*/
	public function listConsumers(){
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM oauth2_clients");
			foreach($results as $single){
				print '<tr>';
   				print 	'<td><a href="javascript:void(0);" title="Edit this Consumer" class="editor-link ' . $single->client_id . '">' . $single->name . '</a></td>'; 
    			print 	'<td>' . $single->client_id . '</td>'; 
    			print 	'<td>' . $single->client_secret . '</td>'; 
    			print 	'<td>' . $single->redirect_uri . '</td>';
				print 	'<td><a href="' . admin_url() . 'admin.php?page=wp_oauth2_complete&delete=' . $single->client_id . '" title="Delete this client" onclick="return confirm(\'Are you sure you want to delete this client\')">Delete</a></td>';
				print '</tr>'; 
						
			}
	}
	
}

?>