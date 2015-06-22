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
          'description' => __( 'Renders Sign-In Button.', ud_get_wp_google_identity('domain') ),
          'group' => 'Google Identity',
        );
        parent::__construct( $options );
      }

      /**
       *  Renders Shortcode
       */
      public function call( $atts = "" ) {
        if( !ud_get_wp_google_identity()->is_valid() ) {
          return false;
        }
        ?><script type="text/javascript" src="//www.gstatic.com/authtoolkit/js/gitkit.js"></script>
        <link type=text/css rel=stylesheet href="//www.gstatic.com/authtoolkit/css/gitkit.css" />
        <script type=text/javascript>
          window.google.identitytoolkit.signInButton(
            '#wpgi_sign',
            {
              widgetUrl: "<?php echo trailingslashit( get_permalink( ud_get_wp_google_identity( 'signin.page' ) ) ); ?>",
              signOutUrl: "<?php echo trailingslashit( home_url() ); ?>"
              <?php if( ud_get_wp_google_identity( 'signin.popup' ) == '1' ) echo ', popupMode: "true"'; ?>
              <?php
              // Optional - Begin the sign-in flow immediately on page load.
              //            Note that if this is true, popupMode param is ignored
              //loginFirst: true,

              // Optional - Cookie name (default: gtoken)
              //            NOTE: Also needs to be added to config of ‘widget
              //                  page’. See below
              //cookieName: ‘example_cookie’,
              ?>
            }
          );
        </script><div id="wpgi_sign"></div><?php
      }

    }

    new Signin_Button_Shortcode();

  }

}

