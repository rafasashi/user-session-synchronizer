<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class User_Session_Synchronizer_Default_Class {

	/**
	 * The single instance of User_Session_Synchronizer_Default_Class.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */

	public $defaultClass = array();

	public function __construct ( $parent ) {
		
		$this->parent = $parent;

		//HOOKS HERE
	}

	//METHODES HERE

	/**
	 * Main User_Session_Synchronizer_Default_Class Instance
	 *
	 * Ensures only one instance of User_Session_Synchronizer_Default_Class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see User_Session_Synchronizer()
	 * @return Main User_Session_Synchronizer_Default_Class instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}