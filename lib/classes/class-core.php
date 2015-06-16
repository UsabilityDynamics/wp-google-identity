<?php
/**
 * Plugin Core
 *
 * Adds specific hooks ( actions, filters )
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPGI {

  use MatthiasMullie\Minify\Exception;

  if( !class_exists( 'UsabilityDynamics\WPGI\Core' ) ) {

    final class Core {

      /**
       * Constructor
       *
       * @author peshkov@UD
       */
      public function __construct(){

        $this->maybe_identify_user();

        /* Init Admin UI */
        if( is_admin() ) {
          new Admin();
        }

        add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );

      }

      /**
       *
       */
      private function maybe_identify_user() {
        $config_file = ud_get_wp_google_identity( 'oauth.google.config_file_path' );
        if( !file_exists( $config_file ) ) {
          return;
        }

        try {

          $gitkitClient = \Gitkit_Client::createFromFile( $config_file );
          $gitkitUser = $gitkitClient->getUserInRequest();

          if( !empty( $gitkitUser ) ) {

            /**
             *
             */
            if( is_user_logged_in() ) {
              $user = get_user_by( 'id', get_current_user_id() );
              if( $user->user_email !== $gitkitUser->getEmail() ) {
                $this->logout();
              }
            }
            /**
             *
             */
            else {

              $user = get_user_by( 'email', $gitkitUser->getEmail() );

              /**
               * Login already existing user
               */
              if( $user && !is_wp_error( $user ) ) {
                $this->authenticate_by_id( $user->ID );
              }
              /**
               * Create new user
               */
              else {
                /* Be sure that registration is enabled! */
                if ( get_option( 'users_can_register' ) ) {

                  $user_id = wp_insert_user( array(
                    'user_login' =>  $gitkitUser->getEmail(),
                    'user_email' => $gitkitUser->getEmail(),
                    'user_pass' => wp_generate_password(),
                    'display_name' => $gitkitUser->getDisplayName(),
                  ) );

                  $this->authenticate_by_id( $user_id );

                } else {

                  //@TODO
                  die( 'WTF' );

                }

              }

            }

          } else {

            if( is_user_logged_in() ) {
              $this->logout();
            }

          }

        } catch ( \Exception $e ) {

          // echo "<pre>"; print_r( $e->getMessage() ); echo "</pre>"; die();

          if( is_user_logged_in() ) {
            $this->logout();
          }

        }

      }

      /**
       * Authenticate user by ID
       */
      private function authenticate_by_id( $id ) {
        wp_logout();
        wp_set_current_user ( $id );
        wp_set_auth_cookie  ( $id );
        $redirect_to = home_url();
        wp_safe_redirect( $redirect_to );
        exit();
      }

      /**
       * Safe Logout
       */
      private function logout() {
        setcookie( 'gtoken', ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH, COOKIE_DOMAIN );
        wp_logout();
        $redirect_to = home_url();
        wp_safe_redirect( $redirect_to );
        exit();
      }

      /**
       * Determine if current page is Sign-In page.
       * If so, show custom HTML data and die.
       */
      static public function template_redirect() {
        global $wp_query;

        if( ud_get_wp_google_identity( 'signin.page' ) == $wp_query->queried_object_id ) {
          include( ud_get_wp_google_identity()->path( 'static/views/signin-page.php', 'dir' ) );
          die();
        }

      }

    }

  }

}
