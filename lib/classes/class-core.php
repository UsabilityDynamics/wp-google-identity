<?php
/**
 * Plugin Core
 *
 * Adds specific hooks ( actions, filters )
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPGI {

  if( !class_exists( 'UsabilityDynamics\WPGI\Core' ) ) {

    final class Core {

      /**
       * Constructor
       *
       * @author peshkov@UD
       */
      public function __construct(){

        /* Adds Javascript for Sign-In button */
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ), 1 );

      }

      /**
       * Sign In Button Front End
       */
      static public function wp_enqueue_scripts() {
        ?><script type="text/javascript" src="//www.gstatic.com/authtoolkit/js/gitkit.js"></script>
        <link type=text/css rel=stylesheet href="//www.gstatic.com/authtoolkit/css/gitkit.css" />
        <script type=text/javascript>
          window.google.identitytoolkit.signInButton(
            '#wpgi_sign',
            {
              widgetUrl: "https://www.usabilitydynamics.org/signup/",
              signOutUrl: "https://www.usabilitydynamics.com"

              // Optional - Begin the sign-in flow in a popup window
              //popupMode: true,

              // Optional - Begin the sign-in flow immediately on page load.
              //            Note that if this is true, popupMode param is ignored
              //loginFirst: true,

              // Optional - Cookie name (default: gtoken)
              //            NOTE: Also needs to be added to config of ‘widget
              //                  page’. See below
              //cookieName: ‘example_cookie’,
            }
          );
        </script><?php
      }

    }

  }

}
