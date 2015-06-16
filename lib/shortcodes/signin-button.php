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
        /**
         * Be sure if Sign-In is enabled. */
        if( ud_get_wp_google_identity( 'signin.enabled' ) !== '1' ) {
          return;
        }
        /** Be sure that Browser API Key is set. */
        $api_key = ud_get_wp_google_identity( 'oauth.google.api_key' );
        if( empty( $api_key ) ) {
          return;
        }
        /** Be sure that Sign-In page is set. */
        $signin_page_id = ud_get_wp_google_identity( 'signin.page' );
        if( empty( $signin_page_id ) || !get_permalink( $signin_page_id ) ) {
          return;
        }
        /** Be sure that config file is set and exists. */
        $config_file = ud_get_wp_google_identity( 'oauth.google.config_file_path' );
        if( !file_exists( $config_file ) ) {
          return;
        }
        ?><script type="text/javascript" src="//www.gstatic.com/authtoolkit/js/gitkit.js"></script>
        <link type=text/css rel=stylesheet href="//www.gstatic.com/authtoolkit/css/gitkit.css" />
        <script type=text/javascript>
          window.google.identitytoolkit.signInButton(
            '#wpgi_sign',
            {
              widgetUrl: "<?php echo trailingslashit( get_permalink( $signin_page_id ) ); ?>",
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

