<?php
/*
 * Plugin Name: User Session Synchronizer
 * Version: 1.3
 * Plugin URI: https://github.com/rafasashi/user-session-synchronizer/archive/master.zip
 * Description: Keep the user logged in from one wordpress to another by synchronizing user data and cookie session.
 * Author: Rafasashi
 * Author URI: https://www.linkedin.com/in/raphaeldartigues
 * Requires at least: 4.3
 * Tested up to: 4.3
 *
 * Text Domain: user-session-synchronizer
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Rafasashi
 * @since 1.0.0
 */
 
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	/**
	* Minimum version required
	*
	*/
	if ( get_bloginfo('version') < 4.3 ) return;
	
	/**
	* Add donation link
	*
	*/
	function user_session_synchronizer_row_meta( $links, $file ){
		if ( strpos( $file, basename( __FILE__ ) ) !== false ) {
			$new_links = array( '<a href="https://www.paypal.me/recuweb" target="_blank">' . __( 'Donate', 'cleanlogin' ) . '</a>' );
			$links = array_merge( $links, $new_links );
		}
		return $links;
	}
	add_filter('plugin_row_meta', 'user_session_synchronizer_row_meta', 10, 2);

	// Load plugin class files
	require_once( 'includes/class-user-session-synchronizer.php' );
	require_once( 'includes/class-user-session-synchronizer-email-verification.php' );
	require_once( 'includes/class-user-session-synchronizer-settings.php' );
	require_once( 'includes/class-user-session-synchronizer-session-control.php' );
	
	// Load plugin libraries
	require_once( 'includes/lib/class-user-session-synchronizer-admin-api.php' );
	require_once( 'includes/lib/class-user-session-synchronizer-post-type.php' );
	require_once( 'includes/lib/class-user-session-synchronizer-taxonomy.php' );

	/**
	 * Returns the main instance of User_Session_Synchronizer to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object User_Session_Synchronizer
	 */
	function User_Session_Synchronizer () {
		
		$instance = User_Session_Synchronizer::instance( __FILE__, '1.0.0' );	
		
		if ( is_null( $instance->emailVerification ) ) {
			$instance->emailVerification = User_Session_Synchronizer_Email_Verification::instance( $instance );
		}
		
		if ( is_null( $instance->settings ) ) {
			$instance->settings = User_Session_Synchronizer_Settings::instance( $instance );
		}
		
		if ( is_null( $instance->sessionControl ) ) {
			$instance->sessionControl = User_Session_Synchronizer_Session_Control::instance( $instance );
		}

		return $instance;
	}

	User_Session_Synchronizer();