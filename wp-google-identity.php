<?php
/**
 * Plugin Name: WP Google Identity
 * Plugin URI: https://usabilitydynamics.com
 * Description: Google Identity for WordPress
 * Author: Usability Dynamics, Inc.
 * Version: 1.0.0
 * Text Domain: wp_google_identity
 * Author URI: https://www.usabilitydynamics.com
 *
 * GitHub Plugin URI: UsabilityDynamics/wp-google-identity
 * GitHub Branch: v1.0
 *
 * Copyright 2012 - 2015 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

if( !function_exists( 'ud_get_wp_google_identity' ) ) {

  /**
   * Returns WP Google Identity Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   * @param bool $key
   * @param null $default
   * @return
   */
  function ud_get_wp_google_identity( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPGI\Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_google_identity' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_check_wp_google_identity() {
    global $_ud_wp_google_identity_error;
    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp_google_identity' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp_google_identity' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp_google_identity' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPGI\Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp_google_identity' ) );
      }
    } catch( Exception $e ) {
      $_ud_wp_google_identity_error = $e->getMessage();
      return false;
    }
    return true;
  }

}

if( !function_exists( 'ud_wp_google_identity_message' ) ) {
  /**
   * Renders admin notes in case there are errors on plugin init
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_wp_google_identity_message() {
    global $_ud_wp_google_identity_error;
    if( !empty( $_ud_wp_google_identity_error ) ) {
      $message = sprintf( __( '<p><b>%s</b> can not be initialized. %s</p>', 'wp_google_identity' ), 'WP Google Identity', $_ud_wp_google_identity_error );
      echo '<div class="error fade" style="padding:11px;">' . $message . '</div>';
    }
  }
  add_action( 'admin_notices', 'ud_wp_google_identity_message' );
}

if( ud_check_wp_google_identity() ) {
  //** Initialize. */
  ud_get_wp_google_identity();
}