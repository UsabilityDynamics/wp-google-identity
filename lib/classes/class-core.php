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

        add_action( 'template_redirect', array( __CLASS__, 'maybe_show_error_message' ) );

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
        if( !ud_get_wp_google_identity()->is_valid() ) {
          return;
        }
        if( 'wp-login.php' == $pagenow &&  ud_get_wp_google_identity( 'signin.disable_native_login' ) == '1' ) {
          if( isset( $_REQUEST[ 'action' ] ) && in_array( $_REQUEST[ 'action' ], array( 'logout' ) ) ) {
            return;
          }
          wp_redirect( home_url() );
          exit();
        }
      }

      /**
       * Handles WordPress authentication, logout and user registration.
       *
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
             * Check already logged in user
             */
            if( is_user_logged_in() ) {

              $user = get_user_by( 'id', get_current_user_id() );

              /**
               * Prevent using non-provider's account
               */
              if( $user->user_email !== $gitkitUser->getEmail() ) {
                throw new \Exception( __( 'User Session Error Occurred.', ud_get_wp_google_identity( 'domain' ) ) );
              }

              /**
               * It should not happen
               * but we get access in case if Account Chooser user ID is stored in WP usermeta
               * and they are equal
               */
              else if( !$gitkitUser->getProviderId() ) {

                /** Determine if custom Password Account is enabled by plugin's settings. */
                if( ud_get_wp_google_identity( 'providers.password_account' ) !== '1' ) {
                  /* Remove User from Account Chooser */
                  $gitkitClient->deleteUser( $gitkitUser->getUserId() );
                  throw new \Exception( __( 'Sorry, but Password Account is not enabled. Please, use any other provider for sign in.', ud_get_wp_google_identity( 'domain' ) ) );
                }

                $user_id = get_user_meta( $user->ID, 'wpgi_provider_custom', true );
                if( empty( $user_id ) || $user_id !== $gitkitUser->getUserId() ) {
                  /* Remove User from Account Chooser */
                  $gitkitClient->deleteUser( $gitkitUser->getUserId() );
                  throw new \Exception( __( 'The Account with provided email already exists. You can use Custom Password Account only for creating new Account on site.', ud_get_wp_google_identity( 'domain' ) ) );
                }
              }

              /**
               * Break login if email is not verified
               * It's only related to case when email belongs to provider
               */
              else if( !$gitkitUser->isEmailVerified() ) {
                /* Remove User from Account Chooser */
                $gitkitClient->deleteUser( $gitkitUser->getUserId() );
                throw new \Exception( __( 'Email is not verified.', ud_get_wp_google_identity( 'domain' ) ) );
              }

            }

            /**
             * Maybe authenticate user
             *
             */
            else {

              $user = get_user_by( 'email', $gitkitUser->getEmail() );

              /**
               * May be log in already existing user
               */
              if( $user && !is_wp_error( $user ) ) {

                /**
                 * It should not happen
                 * but we get access in case if Account Chooser user ID is stored in WP usermeta
                 * and they are equal
                 */
                if( !$gitkitUser->getProviderId() ) {

                  /** Determine if custom Password Account is enabled by plugin's settings. */
                  if( ud_get_wp_google_identity( 'providers.password_account' ) !== '1' ) {
                    /* Remove User from Account Chooser */
                    $gitkitClient->deleteUser( $gitkitUser->getUserId() );
                    throw new \Exception( __( 'Sorry, but Password Account is not enabled. Please, use any other provider for sign in.', ud_get_wp_google_identity( 'domain' ) ) );
                  }

                  $user_id = get_user_meta( $user->ID, 'wpgi_provider_custom', true );
                  if( empty( $user_id ) || $user_id !== $gitkitUser->getUserId() ) {
                    /* Remove User from Account Chooser */
                    $gitkitClient->deleteUser( $gitkitUser->getUserId() );
                    throw new \Exception( __( 'The Account with provided email already exists. You can use Custom Password Account only for creating new Account on site.', ud_get_wp_google_identity( 'domain' ) ) );
                  }

                }
                /**
                 * Break login if email is not verified
                 * It's only related to case when email belongs to provider
                 */
                else if( !$gitkitUser->isEmailVerified() ) {
                  /* Remove User from Account Chooser */
                  $gitkitClient->deleteUser( $gitkitUser->getUserId() );
                  throw new \Exception( __( 'Email is not verified.', ud_get_wp_google_identity( 'domain' ) ) );
                }

                self::authenticate_by_id( $user->ID, $gitkitUser );

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

                  self::authenticate_by_id( $user_id, $gitkitUser );

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

          set_transient( 'wpgi_error_message', $e->getMessage() );

          self::clear_google_session();

          if( is_user_logged_in() ) {
            self::logout();
          }

        }

      }

      /**
       * Authenticate user by ID
       *
       * @param int $user_id
       * @param object $gitkitUser
       * @throws \Exception
       */
      static public function authenticate_by_id( $user_id, $gitkitUser ) {

        /**
         * Before proceeding,
         * we're updating provider's information
         * for current user.
         */
        if( !$gitkitUser->getProviderId() ) {
          $meta = 'wpgi_provider_custom';
        } else {
          $meta = sanitize_key( 'wpgi_provider_' . $gitkitUser->getProviderId() );
        }
        $id = get_user_meta( $user_id, $meta, true );
        if( !empty( $id ) ) {
          if( $id !== $gitkitUser->getUserId() ) {
            throw new \Exception( 'Invalid Account', ud_get_wp_google_identity( 'domain' ) );
          }
        } else {
          update_user_meta( $user_id, $meta, $gitkitUser->getUserId() );
        }

        wp_set_current_user ( $user_id );
        wp_set_auth_cookie  ( $user_id );

        self::redirect();

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
       * Determine 'redirect to' page
       * and do redirect.
       *
       */
      static public function redirect(){

        $referer = get_transient( 'wpgi_signin_redirect_to' );

        $redirect_to = ud_get_wp_google_identity( 'signin.signin_success_page' );
        if( !empty( $redirect_to ) ) {
          $redirect_to = get_permalink($redirect_to);
        }

        if( empty( $redirect_to ) ) {
          if( !empty( $referer ) ) {
            $redirect_to = $referer;
          } else {
            $redirect_to = home_url();
          }
        }

        $redirect_to = apply_filters( 'wpgi_signin_redirect_to', $redirect_to, $referer );

        delete_transient( 'wpgi_signin_redirect_to' );

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

      /**
       * Maybe print sign in Error message
       *
       */
      static public function maybe_show_error_message() {
        $message = get_transient( 'wpgi_error_message' );
        if( !empty( $message ) ) {
          wp_enqueue_style( 'wpgi-error-handler', ud_get_wp_google_identity()->path( 'static/styles/wpgi-error-handler.css', 'url' ) );
          wp_enqueue_script( 'wpgi-error-handler', ud_get_wp_google_identity()->path( 'static/scripts/wpgi-error-handler.js', 'url' ), array( 'jquery' ) );
          wp_localize_script( 'wpgi-error-handler', 'wpgi_err', array(
            'message' => sprintf( __( 'Sign In Error: %s', ud_get_wp_google_identity( 'domain' ) ), $message ),
            'close' => __( 'Close', ud_get_wp_google_identity( 'domain' ) )
          ) );
          delete_transient( 'wpgi_error_message' );
          do_action( 'wpgi::error::handler', $message );
        }

      }

    }

  }

}
