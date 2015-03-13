<?php
/**
 * WordPress OAuth Server AJAX functionality
 * @var array
 */
$ajax_events = array(
	'remove_client' => false
);

/** loop though all the ajax events and add then as needed */
foreach ( $ajax_events as $ajax_event => $nopriv ) {
	add_action( 'wp_ajax_wo_' . $ajax_event, 'wo_ajax_'.$ajax_event );
	if ( $nopriv ) 
		add_action( 'wp_ajax_nopriv_wo_' . $ajax_event, 'wo_ajax_'.$ajax_event );
}

function wo_ajax_remove_client () {
	global $wpdb;
	$action = $wpdb->delete( "{$wpdb->prefix}oauth_clients", array( 'client_id' =>  $_POST['data']) );
	if($action){
		print "1";
	}else{
		print "System Error: Could not remove the client from the server.";
	}
	exit;
}