<?php

/**
 * Admin UI ( Settings )
 *
 * @author peshkov@UD
 */

namespace UsabilityDynamics\WPGI {

  if (!class_exists('UsabilityDynamics\WPGI\Admin')) {

    /**
     *
     *
     * @author peshkov@UD
     */
    class Admin {

      /**
       * Constructor
       *
       * @author peshkov@UD
       */
      public function __construct() {

        /* Setup Admin Interface */
        $this->ui = new \UsabilityDynamics\UI\Settings(
          ud_get_wp_google_identity()->settings,
          ud_get_wp_google_identity()->get_schema( 'extra.schemas.ui' )
        );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

        add_filter( 'ud::ui::field::readonly::value', array( __CLASS__, 'prepare_field_value' ), 10, 2 );
      }

      /**
       * Takes care about readonly values
       * on 'WP Google Identity' settings page
       *
       * @param $value
       * @param $field
       * @return string
       */
      static public function prepare_field_value( $value, $field ) {
        switch( $field->id ) {
          case 'oauth_google_redirect_uri':
            $page_id = ud_get_wp_google_identity( 'signin.page' );
            if( $page_id && $page_id > 0 ) {
              $permalink = get_permalink( $page_id );
            }
            if( !empty( $permalink ) ) {
              $value = untrailingslashit( $permalink );
            } else {
              $value = strtoupper( __( 'Setup Sign-In Page at first', ud_get_wp_google_identity( 'domain' ) ) );
            }
            break;
          case 'oauth_google_javascript_origins':
            $value = untrailingslashit( home_url() );
            break;
        }
        return $value;
      }

      /**
       * Add necessary scripts and styles
       * on WP Google Identity pages.
       *
       */
      static public function enqueue_scripts() {

        $screen = get_current_screen();

        if( $screen->id == 'settings_page_wp_google_identity' ) {
          wp_enqueue_style( 'wpgi-admin', ud_get_wp_google_identity()->path( 'static/styles/admin.css', 'url' ) );
        }

      }

    }

  }
}
