<?php
  /**
   * Abstract Data.
   *
   * Handles generic data interaction which is implemented by
   * the different data store classes.
   *
   * @class       WC_Data
   * @version     3.0.0
   * @package     WooCommerce/Classes
   */
  
  if ( ! defined( 'ABSPATH' ) ) {
    exit;
  }
  define('WOOCOMMERCE_DEPARTMENT_ADDRESS','woocommerce_department_address');
  /**
   * Abstract WC Data Class
   *
   * Implemented by classes using the same CRUD(s) pattern.
   *
   * @version  2.6.0
   * @package  WooCommerce/Abstracts
   */
  class WC_DepartmentAddress{
  
    /**
     * ID for this object.
     *
     * @since 3.0.0
     * @var int
     */
    protected $id = 0;
  
    /**
     * Core data for this object. Name value pairs (name + default value).
     *
     * @since 3.0.0
     * @var array
     */
    protected $data = array(
      'department_name'    => '',
      'address'            => '',
      'city'               => '',
      'zip_code'           => '',
      'state'              => '',
      'note'               => '',
    );
  
    /**
     * Core data changes for this object.
     *
     * @since 3.0.0
     * @var array
     */
    protected $changes = array();
  
    /**
     * This is false until the object is read from the DB.
     *
     * @since 3.0.0
     * @var bool
     */
    protected $object_read = false;
  
    /**
     * This is the name of this object type.
     *
     * @since 3.0.0
     * @var string
     */
    protected $object_type = 'address';
  
    /**
     * Extra data for this object. Name value pairs (name + default value).
     * Used as a standard way for sub classes (like product types) to add
     * additional information to an inherited class.
     *
     * @since 3.0.0
     * @var array
     */
    protected $extra_data = array();
  
    /**
     * Set to _data on construct so we can track and reset data if needed.
     *
     * @since 3.0.0
     * @var array
     */
    protected $default_data = array();
  
    /**
     * Contains a reference to the data store for this class.
     *
     * @since 3.0.0
     * @var object
     */
    protected $data_store;
  
    /**
     * Stores meta in cache for future reads.
     * A group must be set to to enable caching.
     *
     * @since 3.0.0
     * @var string
     */
    protected $cache_group = '';
  
    /**
     * Stores additional meta data.
     *
     * @since 3.0.0
     * @var array
     */
    protected $meta_data = null;
  
    /**
     * Default constructor.
     *
     * @param int|object|array $read ID to load from the DB (optional) or already queried data.
     */
    public function __construct( $address = 0 ) {
      $this->data         = array_merge( $this->data, $this->extra_data );
      $this->default_data = $this->data;
      if ( is_numeric( $address ) && $address > 0 ) {
        $this->set_id( $address );
      } elseif ( $address instanceof self ) {
        $this->set_id( absint( $address->get_id() ) );
      } elseif ( ! empty( $address->ID ) ) {
        $this->set_id( absint( $address->ID ) );
      } else {
        $this->set_object_read( true );
      }
  
      $this->data_store = new Address_Data_Store;
      if ( $this->get_id() > 0 ) {
        $this->data_store->read( $this );
      }
    }
  
    /**
     * Only store the object ID to avoid serializing the data object instance.
     *
     * @return array
     */
    public function __sleep() {
      return array( 'id' );
    }
  
    /**
     * Re-run the constructor with the object ID.
     *
     * If the object no longer exists, remove the ID.
     */
    public function __wakeup() {
      try {
        $this->__construct( absint( $this->id ) );
      } catch ( Exception $e ) {
        $this->set_id( 0 );
        $this->set_object_read( true );
      }
    }
  
    /**
     * When the object is cloned, make sure meta is duplicated correctly.
     *
     * @since 3.0.2
     */
    public function __clone() {
    }
  
    /**
     * Get the data store.
     *
     * @since  3.0.0
     * @return object
     */
    public function get_data_store() {
      return $this->data_store;
    }
  
    /**
     * Returns the unique ID for this object.
     *
     * @since  2.6.0
     * @return int
     */
    public function get_id() {
      return $this->id;
    }
  	public function get_department_name( $context = 'view' ) {
      return $this->get_prop( 'department_name', $context );
    }
    public function get_address( $context = 'view' ) {
      return $this->get_prop( 'address', $context );
    }
    public function get_city( $context = 'view' ) {
      return $this->get_prop( 'city', $context );
    }
    public function get_zip_code( $context = 'view' ) {
      return $this->get_prop( 'zip_code', $context );
    }
    public function get_state( $context = 'view' ) {
      return $this->get_prop( 'state', $context );
    }
    public function get_note( $context = 'view' ) {
      return $this->get_prop( 'note', $context );
    }
          
    /**
     * Delete an object, set the ID to 0, and return result.
     *
     * @since  2.6.0
     * @param  bool $force_delete Should the date be deleted permanently.
     * @return bool result
     */
    public function delete( $force_delete = false ) {
      if ( $this->data_store ) {
        $this->data_store->delete( $this, array( 'force_delete' => $force_delete ) );
        $this->set_id( 0 );
        return true;
      }
      return false;
    }
  
    /**
     * Save should create or update based on object existence.
     *
     * @since  2.6.0
     * @return int
     */
    public function save() {
      if ( $this->data_store ) {
        
        if ( $this->get_id() ) {
          $this->data_store->update( $this );
        } else {
          $this->data_store->create( $this );
        }
      }
      return $this->get_id();
    }
  
    /**
     * Change data to JSON format.
     *
     * @since  2.6.0
     * @return string Data in JSON format.
     */
    public function __toString() {
      return json_encode( $this->get_data() );
    }
  
    /**
     * Returns all data for this object.
     *
     * @since  2.6.0
     * @return array
     */
    public function get_data() {
      return array_merge( array( 'id' => $this->get_id() ), $this->data);
    }
  
    /**
     * Returns array of expected data keys for this object.
     *
     * @since   3.0.0
     * @return array
     */
    public function get_data_keys() {
      return array_keys( $this->data );
    }
  
    /**
     * Returns all "extra" data keys for an object (for sub objects like product types).
     *
     * @since  3.0.0
     * @return array
     */
    public function get_extra_data_keys() {
      return array_keys( $this->extra_data );
    }
  
  
    
    /**
     * Set ID.
     *
     * @since 3.0.0
     * @param int $id ID.
     */
    public function set_id( $id ) {
      $this->id = absint( $id );
    }
    public function set_department_name( $department_name ) {
      $this->set_prop( 'department_name', $department_name );
    }
    public function set_address( $value ) {
      $this->set_prop( 'address', $value );
    }
    public function set_city( $value ) {
      $this->set_prop( 'city', $value );
    }
    public function set_zip_code( $value ) {
      $this->set_prop( 'zip_code', $value );
    }
    public function set_state( $value ) {
      $this->set_prop( 'state', $value );
    }
    public function set_note( $value ) {
      $this->set_prop( 'note', $value );
    }
      
    /**
     * Set all props to default values.
     *
     * @since 3.0.0
     */
    public function set_defaults() {
      $this->data        = $this->default_data;
      $this->changes     = array();
      $this->set_object_read( false );
    }
  
    /**
     * Set object read property.
     *
     * @since 3.0.0
     * @param boolean $read Should read?.
     */
    public function set_object_read( $read = true ) {
      $this->object_read = (bool) $read;
    }
  
    /**
     * Get object read property.
     *
     * @since  3.0.0
     * @return boolean
     */
    public function get_object_read() {
      return (bool) $this->object_read;
    }
    public static function get_by_name($name){
      $obj = new WC_DepartmentAddress;
      $object = $obj->data_store->get_by_name($name);
      return $object;
    }
    /**
     * Set a collection of props in one go, collect any errors, and return the result.
     * Only sets using public methods.
     *
     * @since  3.0.0
     *
     * @param array  $props Key value pairs to set. Key is the prop and should map to a setter function name.
     * @param string $context In what context to run this.
     *
     * @return bool|WP_Error
     */
    public function set_props( $props, $context = 'set' ) {
      $errors = new WP_Error();
  
      foreach ( $props as $prop => $value ) {
        try {
          if ( 'meta_data' === $prop ) {
            continue;
          }
          $setter = "set_$prop";
          if ( ! is_null( $value ) && is_callable( array( $this, $setter ) ) ) {
            $reflection = new ReflectionMethod( $this, $setter );
  
            if ( $reflection->isPublic() ) {
              $this->{$setter}( $value );
            }
          }
        } catch ( WC_Data_Exception $e ) {
          $errors->add( $e->getErrorCode(), $e->getMessage() );
        }
      }
  
      return count( $errors->get_error_codes() ) ? $errors : true;
    }
  
    /**
     * Sets a prop for a setter method.
     *
     * This stores changes in a special array so we can track what needs saving
     * the the DB later.
     *
     * @since 3.0.0
     * @param string $prop Name of prop to set.
     * @param mixed  $value Value of the prop.
     */
    protected function set_prop( $prop, $value ) {
      if ( array_key_exists( $prop, $this->data ) ) {
        if ( true === $this->object_read ) {
          if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
            $this->changes[ $prop ] = $value;
          }
        } else {
          $this->data[ $prop ] = $value;
        }
      }
    }
  
    /**
     * Return data changes only.
     *
     * @since 3.0.0
     * @return array
     */
    public function get_changes() {
      return $this->changes;
    }
  
    /**
     * Merge changes with data and clear.
     *
     * @since 3.0.0
     */
    public function apply_changes() {
      $this->data    = array_replace_recursive( $this->data, $this->changes ); // @codingStandardsIgnoreLine
      $this->changes = array();
    }
  
    /**
     * Prefix for action and filter hooks on data.
     *
     * @since  3.0.0
     * @return string
     */
    protected function get_hook_prefix() {
      return 'woocommerce_' . $this->object_type . '_get_';
    }
  
    /**
     * Gets a prop for a getter method.
     *
     * Gets the value from either current pending changes, or the data itself.
     * Context controls what happens to the value before it's returned.
     *
     * @since  3.0.0
     * @param  string $prop Name of prop to get.
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return mixed
     */
    protected function get_prop( $prop, $context = 'view' ) {
      $value = null;
  
      if ( array_key_exists( $prop, $this->data ) ) {
        $value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->data[ $prop ];
  
        if ( 'view' === $context ) {
          $value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this );
        }
      }
  
      return $value;
    }
  
    /**
     * Sets a date prop whilst handling formatting and datetime objects.
     *
     * @since 3.0.0
     * @param string         $prop Name of prop to set.
     * @param string|integer $value Value of the prop.
     */
    protected function set_date_prop( $prop, $value ) {
      try {
        if ( empty( $value ) ) {
          $this->set_prop( $prop, null );
          return;
        }
  
        if ( is_a( $value, 'WC_DateTime' ) ) {
          $datetime = $value;
        } elseif ( is_numeric( $value ) ) {
          // Timestamps are handled as UTC timestamps in all cases.
          $datetime = new WC_DateTime( "@{$value}", new DateTimeZone( 'UTC' ) );
        } else {
          // Strings are defined in local WP timezone. Convert to UTC.
          if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
            $offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
            $timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
          } else {
            $timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $value ) ) ) );
          }
          $datetime  = new WC_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );
        }
  
        // Set local timezone or offset.
        if ( get_option( 'timezone_string' ) ) {
          $datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
        } else {
          $datetime->set_utc_offset( wc_timezone_offset() );
        }
  
        $this->set_prop( $prop, $datetime );
      } catch ( Exception $e ) {} // @codingStandardsIgnoreLine.
    }
    public function check_duplicate($value,$prop){
      return $this->data_store->check_duplicate($this->get_id(),$value,$prop);
    }
    /**
     * When invalid data is found, throw an exception unless reading from the DB.
     *
     * @throws WC_Data_Exception Data Exception.
     * @since 3.0.0
     * @param string $code             Error code.
     * @param string $message          Error message.
     * @param int    $http_status_code HTTP status code.
     * @param array  $data             Extra error data.
     */
    protected function error( $code, $message, $http_status_code = 400, $data = array() ) {
      throw new WC_Data_Exception( $code, $message, $http_status_code, $data );
    }
  }
  