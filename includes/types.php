<?php
/**
 * Custom Post Type
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */
add_action( 'init', 'wo_create_post_type' );
function wo_create_post_type() {
  register_post_type( 'wo_clients',
    array(
      'labels' => array(
        'name' => __( 'Clients' ),
        'singular_name' => __( 'Client' )
      ),
      'public' => true,
      'has_archive' => true,
    )
  );
}