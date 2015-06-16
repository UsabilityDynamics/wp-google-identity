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
        } catch ( \Exception $e ) {

          //*
          echo "<pre>";
          print_r( $e->getMessage() );
          echo "</pre>";
          die();
          //*/

        }

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
