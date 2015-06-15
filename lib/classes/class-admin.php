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

      }

    }

  }
}
