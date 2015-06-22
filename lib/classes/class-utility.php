<?php

/**
 * Helper Functions List
 *
 * @class Utility
 */

namespace UsabilityDynamics\WPGI {

  if (!class_exists('UsabilityDynamics\WPGI\Utility')) {

    class Utility {

      /**
       * Determines if Google Identity settings are set
       * and they are valid.
       *
       */
      public function is_valid() {
        /**
         * Be sure if Sign-In is enabled. */
        if( ud_get_wp_google_identity( 'signin.enabled' ) !== '1' ) {
          return false;
        }
        /** Be sure that Browser API Key is set. */
        $api_key = ud_get_wp_google_identity( 'oauth.google.api_key' );
        if( empty( $api_key ) ) {
          return false;
        }
        $client_id = ud_get_wp_google_identity( 'oauth.google.client_id' );
        if( empty( $client_id ) ) {
          return false;
        }
        $service_account_email = ud_get_wp_google_identity( 'oauth.google.service_account_email' );
        if( empty( $service_account_email ) ) {
          return false;
        }
        /** Be sure that Sign-In page is set. */
        $signin_page_id = ud_get_wp_google_identity( 'signin.page' );
        if( empty( $signin_page_id ) || !get_permalink( $signin_page_id ) ) {
          return false;
        }
        /** Be sure that config file is set and exists. */
        $private_key_file = ud_get_wp_google_identity( 'oauth.google.private_key_file' );
        if( !file_exists( $private_key_file ) ) {
          return false;
        }

      }

    }

  }
}