<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Address Data Store
 *
 * @version  3.0.0
 */
class Address_Data_Store {

	/**
	 * Stores updated props.
	 *
	 * @var array
	 */
	protected $updated_props = array();

	/*
	|--------------------------------------------------------------------------
	| CRUD Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Method to create a new address in the database.
	 *
	 * @param WC_DepartmentAddress $address DepartmentAddress object.
	 */
	public function create( &$address ) {
    global $wpdb;
		$id = $wpdb->insert($wpdb->prefix.WOOCOMMERCE_DEPARTMENT_ADDRESS,
			array(
        'department_name'      => $address->get_department_name(),
        'address'   => $address->get_address(),
        'city'      => $address->get_city(),
        'zip_code'  => $address->get_zip_code(),
        'state'     => $address->get_state(),
        'created_user_id'   => get_current_user_id()
			)
		);
    if ( $id ) {
      $address->set_id( $id );
    }else{
			if($wpdb->last_error !== '') :
				$wpdb->print_error();
			endif;
		}
	}

	/**
	 * Method to read a address from the database.
	 *
	 * @param WC_DepartmentAddress $address DepartmentAddress object.
	 * @throws Exception If invalid address.
	 */
	public function read( &$address ) {
    global $wpdb;
		$address->set_defaults();
    $sql = "SELECT * FROM {$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS."  WHERE address_id=".$address->get_id();
    $post_object = $wpdb->get_row($sql);
		if ( ! $address->get_id() || ! $post_object) {
			throw new Exception( __( 'Invalid address.', 'woocommerce-departmentaddress' ) );
		}

		$address->set_props(
			array(
        'department_name'              => $post_object->department_name,
        'address'           => $post_object->address,
        'city'              => $post_object->city,
        'zip_code'          => $post_object->zip_code,
        'state'             => $post_object->state,
			)
		);
		$address->set_object_read( true );
	}

	/**
	 * Method to update a address in the database.
	 *
	 * @param WC_DepartmentAddress $address DepartmentAddress object.
	 */
	public function update( &$address ) {
    global $wpdb;
		// Only update the post when the post data changes.
    $address_data = array(
      'department_name'      => $address->get_department_name(),
      'address'   => $address->get_address(),
      'city'      => $address->get_city(),
      'zip_code'  => $address->get_zip_code(),
			'state'     => $address->get_state(),
			'note'     => $address->get_note()
    );
    $wpdb->update( $wpdb->prefix.WOOCOMMERCE_DEPARTMENT_ADDRESS, $address_data, array( 'address_id' => $address->get_id() ) );
	}

	/**
	 * Method to delete a address from the database.
	 *
	 * @param WC_DepartmentAddress $address DepartmentAddress object.
	 * @param array      $args Array of args to pass to the delete method.
	 */
	public function delete( &$address, $args = array() ) {
    global $wpdb;
		$id = $address->get_id();
    $wpdb->delete($wpdb->prefix.WOOCOMMERCE_DEPARTMENT_ADDRESS,array('address_id'=>$id));
	}

	/**
	 * Returns an array of addresss.
	 *
	 * @param  array $args Args to pass to WC_DepartmentAddress_Query().
	 * @return array|object
	 * @see cs_get_addresss
	 */
	public function get_addresses( $args = array() ) {
		global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS."  WHERE 1";
    $results = $wpdb->get_results($sql);

		return $results;
	}
	public function check_duplicate($id, $value, $field){
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS."` WHERE `$field` = '$value' and address_id !=$id");
		return count($results)>0;
	}
	/**
	 * Search address data for a term and return ids.
	 *
	 * @param  string   $term Search term.
	 * @param  string   $type Type of address.
	 * @param  bool     $include_variations Include variations in search or not.
	 * @param  bool     $all_statuses Should we search all statuses or limit to published.
	 * @param  null|int $limit Limit returned results. @since 3.5.0.
	 * @return array of ids
	 */
	public function search_addresses( $term=null, $page = 1,$perpage = 100 ) {
		global $wpdb;
		$from = ($page-1)*$perpage;
		if($from<0)$from = 0;
		$limit_query = "limit $from,$perpage";
		$search_results = $wpdb->get_results(
			// phpcs:disable
			"SELECT * FROM {$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS."
			WHERE 1
			ORDER BY department_name ASC
			$limit_query
			"
			// phpcs:enable
		);		
		return $search_results;
	}
	public function get_by_name($name){
		global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS."  WHERE department_name='$name'";
    $post_object = $wpdb->get_row($sql);
		return $post_object;
	}
}
