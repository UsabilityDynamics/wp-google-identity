<?php
/**
 * Shortcode: [wpgi_signin]
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPGI {

  if( !class_exists( 'UsabilityDynamics\WPGI\Signin_Button_Shortcode' ) ) {

    class Signin_Button_Shortcode extends Shortcode {

      /**
       * Constructor
       */
      public function __construct() {
        $options = array(
          'id' => 'wpgi_signin',
          'params' => array(),
          'description' => __( 'Renders Sign-In Button.', ud_get_wpp_av('domain') ),
          'group' => 'Google Identity',
        );
        parent::__construct( $options );
      }

      /**
       *  Renders Shortcode
       */
      public function call( $atts = "" ) {
        //$data = shortcode_atts( array(), $atts );
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
        </script><div id="wpgi_sign"></div><?php
      }

    }

    new Signin_Button_Shortcode();

  }

}

