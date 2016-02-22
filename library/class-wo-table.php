<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class WO_Table extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	 function __construct() {
		 parent::__construct( array(
		'singular'=> 'wp_list_text_link', //Singular label
		'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
		'ajax'  => false //We won't support Ajax for this table
		) );
	 }

	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			return false;
		}
		if ( $which == "bottom" ){
		 return false;
		}
	}

	/**
	 * Overide default functionality to remove _nonce field
	 * @return [type] [description]
	 */
	function display_tablenav ( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions">
			 <?php $this->bulk_actions( $which ); ?>
			</div>  
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
	<?php
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return $columns= array(
			'name'=>__('Name'),
			'description' => __('Description'),
			//'user_id' => __('User ID'),
			'client_id' => __('Client ID') //,
			//'redirect_uri' => __('Redirect URI')
		);
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			//'name'  => array('name'),
			//'user_id'=>array('user_id')
		);
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();

		$query = "SELECT * FROM {$wpdb->prefix}oauth_clients";

		$orderby = ! empty( $_GET['orderby'] ) ? mysql_real_escape_string( $_GET['orderby'] ) : 'ASC';
		$order = ! empty( $_GET['order'] ) ? mysql_real_escape_string( $_GET['order'] ) : '';
		
		if( ! empty( $orderby ) & ! empty( $order ) ) { 
			$query .= ' ORDER BY ' . $orderby . ' ' . $order; 
		}

		$totalitems = $wpdb->query( $query );
		$perpage = 5;
		$paged = ! empty( $_GET['paged'] ) ? mysql_real_escape_string( $_GET['paged'] ) : '';
		
		if( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) { 
			$paged = 1; 
		}

		$totalpages = ceil( $totalitems / $perpage );

		if( ! empty( $paged ) && ! empty( $perpage ) ) {
			$offset = ( $paged - 1 ) * $perpage;
				$query.= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
		}

		$this->set_pagination_args( array(
			'total_items' => $totalitems,
			'total_pages' => $totalpages,
			'per_page'	  => $perpage,
		) );
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $wpdb->get_results($query);
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display_rows() {
		//Get the records registered in the prepare_items method
		$records = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden ) = $this->get_column_info();

		//Loop for each record
		if(!empty($records)){foreach($records as $rec){

			//Open the line
			echo '<tr id="record_'.$rec->client_id.'">';
			foreach ( $columns as $column_name => $column_display_name ) {

				//Style attributes for each col
				$class = "class='$column_name column-$column_name'";
				$style = "";
				if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				$attributes = $class . $style;

				//edit link
				//$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->client_id;

				//Display the cell
				switch ( $column_name ) {
					case "name": 
						$edit_link = site_url() . '?wpoauthincludes=edit&_wp_nonce=' . wp_create_nonce( 'wpo-edit-client' ) . '&client_id='.$rec->client_id.'&TB_iframe=true&width=600&height=420';
						echo '<td '.$attributes.'><strong><a class="thickbox" href="' . $edit_link . '" title="Edit">'.stripslashes($rec->name).'</a></strong><div class="row-actions"><span class="edit"><a class="thickbox" href="' . $edit_link . '" title="Edit Client">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Delete this Client permanently" onclick="wo_remove_client(\''.str_replace(" ","",$rec->client_id).'\');" href="#">Delete</a> | </span><span class="view"><a href="#TB_inline?width=300&height=100&inlineId=show_secret_'.$rec->client_id.'" class="thickbox" title="Viewing Secret for '.$rec->name.'">Show Secret</a></span></div> <div id="show_secret_'.$rec->client_id.'" style="display:none;"><h3 style="text-align:center;margin-top:40px;">'.$rec->client_secret.'</h3></div>'; break;
					
					case "description": echo '<td '.$attributes.'>'.stripslashes($rec->description).'</td>'; break;
					//case "user_id": echo '<td '.$attributes.'>'.stripslashes($rec->user_id).'</td>';  break;
					case "client_id": echo '<td '.$attributes.'>'.$rec->client_id.'</td>'; break;
				 // case "redirect_uri": echo '<td '.$attributes.'>'.$rec->redirect_uri.'</td>'; break;
					case "col_link_visible": echo '<td '.$attributes.'>'.$rec->link_visible.'</td>'; break;
				}
			}

			//Close the line
			echo'</tr>';
		}}
	}
}
?>