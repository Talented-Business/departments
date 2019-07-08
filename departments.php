<?php
/**
 * Plugin Name: Department Address
 * Plugin URI: https://#
 * Description: Manage Department Address for shipping.
 * Version: 1.0
 * Author: Lazutina
 * Author URI: https://#
 * Text Domain: woocommerce
 * Domain Path: /i18n/languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Departments{
	/**
	 * Hook in tabs.
	 */
	public static function init() {
    add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
    add_action( 'init', array( __CLASS__, 'add_my_account_endpoint' ) );
    //add_filter( 'query_vars', array( __CLASS__, 'query_vars' ), 0 );
    add_action( 'wp_loaded',array( __CLASS__, 'form_handle' ),10);
    add_action( 'woocommerce_account_departments_endpoint', array( __CLASS__, 'account_endpoint' ));
    //add_action('woocommerce_before_variations_form',array(__CLASS__,'before_variations_form'),10);
    //woocommerce_get_item_data   customize display
    add_action('woocommerce_account_menu_items',array(__CLASS__,'account_menu_items'),20,2);
    add_action('woocommerce_ajax_save_product_variations',array(__CLASS__,'save_product_variations'),10);
    add_filter( 'woocommerce_get_endpoint_url', array(__CLASS__,'list_endpoint'), 10, 4 );
    add_filter('woocommerce_order_item_get_formatted_meta_data',array(__CLASS__,'order_item_get_formatted_meta_data'),10,2);
    include 'data-store.php';
    include 'department.php';
  }
  static function save_product_variations($product_id){
    $cats = get_the_terms( $product_id, 'product_cat' );
    foreach($cats as $category){
      if($category->slug=='vidant-health-shopping-area'){
        $departments = self::get_addresses();
        $new_departments = array();
        foreach($departments as $department){
          $new_departments[] = $department->department_name;
        }
        self::save_department_attribute($product_id,$new_departments);
      }
    }    
  }
  static function order_item_get_formatted_meta_data($formatted_meta,$object){
    foreach($formatted_meta as $index=>$item){
      if($item->key == 'department'){
        $department = WC_DepartmentAddress::get_by_name($item->value);
        $formatted_meta[$index]->display_value ="$item->value<address>{$department->address}:{$department->city}:{$department->zip_code}:{$department->state}</address>";
      }
    }
    return $formatted_meta;
  }
  static function add_my_account_endpoint(){
    if( is_user_logged_in() ) {
      $user = wp_get_current_user();
      $roles = ( array ) $user->roles;
      if(in_array('vidant_shopper',$roles)){
        add_rewrite_endpoint( 'departments', EP_PAGES );
        //flush_rewrite_rules();
      }
    }
  }
  static function get_addresses(){
		$data_store = new Address_Data_Store;
    $addresses   = $data_store->search_addresses( null);
    return $addresses;
  }
  static function account_endpoint(){
    $addresses = self::get_addresses();
    $template_base  = plugin_dir_path( __FILE__ ) . 'templates/';
		wc_get_template(
			'myaccount/departments.php', array(
				'current_user' => get_user_by( 'id', get_current_user_id() ),
				'departments'  => $addresses,
      ),
      '',
      $template_base
		);
  }
  static function form_handle(){
    if(isset($_POST['department-action'])&&wp_verify_nonce( $_POST['departments-edit-address-nonce'], 'departments-edit_address' )&&($_POST['department-action'] != 'delete_address')){
      $zipcode = sanitize_text_field($_POST['zip_code']);
      $department_name = sanitize_text_field($_POST['department_name']);
      $fields = array('department_name'=>array('label'=>'Name'),
                      'address'=>array('label'=>'Address'),
                      'city'=>array('label'=>'City'),
                      'zip_code'=>array('label'=>'Zip'),
                      'state'=>array('label'=>'State'));
      foreach ( $fields as $key=>$field ) {
        if ( empty( $_POST[ $key ] ) ) {
          wc_add_notice( sprintf( __( '%s is a required field.', 'departments' ), $field['label'] ), 'error' );
        }
      }
      if ( $zipcode && ! WC_Validation::is_postcode( $zipcode, $_POST['country'] ) ) {
        wc_add_notice( __( 'Please enter a valid postcode / ZIP.', 'departments' ), 'error' );
      }else{
        $zipcode = wc_format_postcode( $zipcode, $_POST['country'] );
      }
      $department =  new WC_DepartmentAddress;
      if(isset($_POST['department-id'])){
        $department->set_id(intval($_POST['department-id']));
      }
      if($department->check_duplicate($department_name,'department_name')){
        wc_add_notice( __( 'Please enter other Practice Name.', 'departments' ), 'error' );
      }
      if ( 0 === wc_notice_count( 'error' ) ) {
        if($_POST['department-action'] == 'edit_address'){
          $department->set_props(
            array(
              'department_name'=>$department_name,
              'address'=>sanitize_text_field($_POST['address']),
              'city'=>sanitize_text_field($_POST['city']),
              'zip_code'=>$zipcode,
              'state'=>sanitize_text_field($_POST['state']),
              'note'=>sanitize_textarea_field($_POST['note'])
            )
          );
          $department->save();
        }
      }
      if ( ! empty( $e ) ) {
        wc_add_notice( $e->getMessage(), 'error' );
      }
      self::update_attributes();
    }elseif($_POST['department-action'] == 'delete_address'){
        $department =  new WC_DepartmentAddress(intval($_POST['department-id']));
        $department->delete();
        self::update_attributes();
    }
  }
  private static function update_attributes(){
    $departments = self::get_addresses();
    $new_departments = array();
    foreach($departments as $department){
      $new_departments[] = $department->department_name;
    }
    $args     = array( 'post_type' => 'product', 'product_cat'=>'vidant-health-shopping-area', 'posts_per_page' => -1 );
    $products = get_posts( $args );
    foreach($products as $product){
      self::save_department_attribute($product->ID,$new_departments);
    }
  }
  private function save_department_attribute($id,$new_departments=array()){
    $product = wc_get_product($id);
    $attributes = $product->get_attributes();
    $new_attributes = array();
    if(isset($attributes['department'])){
      foreach($attributes as $key=>$attribute){
        $new_attribute = clone $attribute;
        if($key=='department')$new_attribute->set_options( $new_departments );
        $new_attributes[] = $new_attribute;
      }
    }else{
      foreach($attributes as $key=>$attribute){
        $new_attribute = clone $attribute;
        $new_attributes[] = $new_attribute;
      }
      $attribute_object = new WC_Product_Attribute();
      $attribute_object->set_id( 0 );
      $attribute_object->set_name( "Department" );
      $attribute_object->set_options( $new_departments );
      $attribute_object->set_position( '0' );
      $attribute_object->set_visible( 1 );
      $attribute_object->set_variation( 1 );
      $new_attributes[] = $attribute_object;
    }
    $product->set_attributes($new_attributes);
    $product->save();    
  }
  static function before_variations_form(){
    $addresses = self::get_addresses();
    ?>
      <style>
        table.variations td.label{width:130px}
        table.variations td.value{width:200px}
      </style> 
      <table class="variations" cellspacing="0">
        <tbody>
          <tr>
            <td class="label"><label for="quantity">Practice</label></td>
            <td class="value">
            <select id="pa_department" class="" name="attribute_pa_department" data-attribute_name="attribute_pa_department" data-show_option_none="yes">
              <option value="">Choose an option</option> 
              <?php foreach($addresses as $key=>$address){?>
                <option value="<?=$key?>" class="attached enabled"><?=$address->department_name ?></option>
              <?php }?>
            </select>
            </td>
          </tr>
          </tbody>
      </table>    
    <?php
  }
  static function account_menu_items($items, $endpoints){
    if( is_user_logged_in() ) {
      $user = wp_get_current_user();
      $roles = ( array ) $user->roles;
      if(in_array('vidant_shopper',$roles)){
        $new = array( 'department_address' => 'Practices' );
 
        // array_slice() is good when you want to add an element between the other ones
        $items = array_slice( $items, 0, 2, true ) 
        + $new 
        + array_slice( $items, 2, NULL, true );
      }
    }
    return $items;
  }
  static function list_endpoint( $url, $endpoint, $value, $permalink ){
  
    if( $endpoint === 'department_address' ) {
  
      // ok, here is the place for your custom URL, it could be external
      $url = site_url('/my-account/departments/');
  
    }
    return $url;
  
  }
  static function check_version(){
    global $wpdb;
    if ( !$wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS."'" ) ) {
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta( self::get_schema() );
    }
  }
  static function get_schema(){
    global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
    CREATE TABLE {$wpdb->prefix}".WOOCOMMERCE_DEPARTMENT_ADDRESS." (
      address_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      department_name varchar(190) NOT NULL UNIQUE,
      address varchar(255) NOT NULL,
      city varchar(255) NOT NULL,
      zip_code varchar(255) NOT NULL,
      state varchar(255) NOT NULL,
      note varchar(255) NOT NULL,
      term_id BIGINT NOT NULL,
      created_user_id int NOT NULL default 0,
      PRIMARY KEY  (address_id)
    ) $collate;";
    return $tables;    
  }
}
Departments::init();
//add_action('wp','testing');
function testing(){
  $product = wc_get_product(337);
  $cats = get_the_terms( 337, 'product_cat' );
  foreach($cats as $category){
    if($category->slug=='vidant-health-shopping-area'){
      var_dump('category');die;
    }
  }
}