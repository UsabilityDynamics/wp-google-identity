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
          ud_get_wp_google_identity()->get_schema( 'extra.schemas.ui'
        ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

      }

      /**
       *
       */
      public function enqueue_scripts() {

        $screen = get_current_screen();

        if( $screen->id = 'settings_page_wp_google_identity' ) {
          wp_enqueue_style( 'wpgi-admin', ud_get_wp_google_identity()->path( 'static/styles/admin.css', 'url' ) );
        }

      }

    }

  }
}
