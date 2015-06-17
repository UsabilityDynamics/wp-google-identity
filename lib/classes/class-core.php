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

        add_action( 'init', array( __CLASS__, 'identify_user' ) );

        add_action( 'init', array( __CLASS__, 'on_disabled_native_login' ) );

        add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );

        /**
         * Clear Google Session on WordPress logout
         */
        add_action( 'wp_logout', array( __CLASS__, 'clear_google_session' ));

        /* Init Admin UI */
        if( is_admin() ) {
          new Admin();
        }

      }

      /**
       * Redirect user to Front page
       * if Native Login page is disabled in settings.
       *
       */
      static public function on_disabled_native_login() {
        global $pagenow;
        if( 'wp-login.php' == $pagenow &&  ud_get_wp_google_identity( 'signin.disable_native_login' ) == '1' ) {
          if( isset( $_REQUEST[ 'action' ] ) && in_array( $_REQUEST[ 'action' ], array( 'logout' ) ) ) {
            return;
          }
          wp_redirect( home_url() );
          exit();
        }
      }

      /**
       * Handles WordPress login, logout and user registration.
       * Note: Google credentials must be set.
       */
      static public function identify_user() {

        $private_key_file = ud_get_wp_google_identity( 'oauth.google.private_key_file' );
        if( !file_exists( $private_key_file ) ) {
          return;
        }

        $client_id = ud_get_wp_google_identity( 'oauth.google.client_id' );
        if( empty( $client_id ) ) {
          return;
        }

        $service_account_email = ud_get_wp_google_identity( 'oauth.google.service_account_email' );
        if( empty( $service_account_email ) ) {
          return;
        }

        /** Be sure that Sign-In page is set. */
        $signin_page_id = ud_get_wp_google_identity( 'signin.page' );
        if( empty( $signin_page_id ) || !get_permalink( $signin_page_id ) ) {
          return;
        }

        try {

          //$gitkitClient = \Gitkit_Client::createFromFile( $config_file );

          $gitkitClient = \Gitkit_Client::createFromConfig( array(
            "clientId" => $client_id,
            "serviceAccountEmail" => $service_account_email,
            "serviceAccountPrivateKeyFile" => $private_key_file,
            "widgetUrl" => trailingslashit( get_permalink( $signin_page_id ) ),
            "cookieName" => "gtoken",
          ) );
          $gitkitUser = $gitkitClient->getUserInRequest();

          if( !empty( $gitkitUser ) ) {

            /**
             *
             */
            if( is_user_logged_in() ) {
              $user = get_user_by( 'id', get_current_user_id() );
              if( $user->user_email !== $gitkitUser->getEmail() ) {
                self::logout();
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
                self::authenticate_by_id( $user->ID );
              }
              /**
               * Create new user
               */
              else {
                /* Be sure that registration is enabled! */
                if( is_multisite() && get_site_option( 'registration' ) == 'none' ) {
                  self::logout();
                } elseif ( !is_multisite() && get_option( 'users_can_register' ) == '0' ) {
                  self::logout();
                } else {

                  $user_id = wp_insert_user( array(
                    'user_login' =>  $gitkitUser->getEmail(),
                    'user_email' => $gitkitUser->getEmail(),
                    'user_pass' => wp_generate_password(),
                    'display_name' => $gitkitUser->getDisplayName(),
                  ) );

                  self::authenticate_by_id( $user_id );

                }

              }

            }

          } else {

            if( is_user_logged_in() && !current_user_can( 'manage_options' ) ) {
              self::logout();
            }

          }

        } catch ( \Exception $e ) {

          //echo "<pre>"; print_r( $e->getMessage() ); echo "</pre>"; die();

          self::clear_google_session();

          if( is_user_logged_in() && !current_user_can( 'manage_options' ) ) {
            self::logout();
          }

        }

      }

      /**
       * Authenticate user by ID
       */
      static public function authenticate_by_id( $id ) {
        wp_set_current_user ( $id );
        wp_set_auth_cookie  ( $id );
        $redirect_to = ud_get_wp_google_identity( 'signin.signin_success_page' );
        if( !empty( $redirect_to ) ) {
          $redirect_to = get_permalink($redirect_to);
        }
        if( empty( $redirect_to ) ) {
          $redirect_to = home_url();
        }
        wp_safe_redirect( $redirect_to );
        exit();
      }

      /**
       * Safe Logout
       */
      static public function logout() {
        wp_logout();
        $redirect_to = home_url();
        wp_safe_redirect( $redirect_to );
        exit();
      }

      /**
       * Clear Google Session
       */
      static public function clear_google_session() {
        setcookie( 'gtoken', '', time() - YEAR_IN_SECONDS, COOKIEPATH );
        setcookie( 'gtoken', ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH );
        setcookie( 'gtoken', ' ', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'gtoken', ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH, COOKIE_DOMAIN );
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
