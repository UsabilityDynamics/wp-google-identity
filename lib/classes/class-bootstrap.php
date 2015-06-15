<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPGI {

  if( !class_exists( 'UsabilityDynamics\WPGI\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPGI\Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {

        /**
         * Initiate Plugin Settings
         */
        $this->define_settings();

        /**
         * Load Plugin logic here...
         */
        new Core();

        /**
         * May be load Shortcodes
         */
        $this->load_files( $this->path('lib/shortcodes', 'dir') );
        

        
      }

      /**
       * Define Plugin Settings
       *
       * Examples:
       *
       * to get text domain:
       * $this->get( 'domain' );
       *
       * to get specific value from 'deep' array:
       * $this->get( 'config.hello' );
       *
       * to set value:
       * $this->set( 'config.hello', 'world' );
       *
       * to save data to DB:
       * $this->settings->commit();
       *
       * @author peshkov@UD
       */
      private function define_settings() {

        /**
         * Be sure Settings class exists.
         *
         * Class declared in WP-Property plugin and added to autoload.
         * ( vendor/libraries/usabilitydynamics/lib-settings )
         */
        if( !class_exists( '\UsabilityDynamics\Settings' ) ) {
          return;
        }

        $this->settings = new \UsabilityDynamics\Settings(array(
          'key' => 'wpgi_settings',
          'store' => 'options',
          'data' => array(
            'name' => $this->name,
            'version' => $this->args['version'],
            'domain' => $this->domain,
          )
        ));

        /**
         * Probably add default settings from composer.json
         * In some cases it's needed to prevent overwriting settings on data saving.
         *
         * @see: composer.json
         * {
         *   extra: {
         *     schemas: {
         *       settings: {
         *         // Here we go.
         *       }
         *     }
         *   }
         * }
         *
         */
        $default = $this->get_schema('extra.schemas.settings');
        if (is_array($default)) {
          $this->set(\UsabilityDynamics\Utility::extend($default, $this->get()));
        }

      }

      /**
       * Includes all PHP files from specific folder
       *
       * @param string $dir Directory's path
       * @author peshkov@UD
       */
      public function load_files($dir = '') {
        $dir = trailingslashit($dir);
        if (!empty($dir) && is_dir($dir)) {
          if ($dh = opendir($dir)) {
            while (( $file = readdir($dh) ) !== false) {
              if (!in_array($file, array('.', '..')) && is_file($dir . $file) && 'php' == pathinfo($dir . $file, PATHINFO_EXTENSION)) {
                include_once( $dir . $file );
              }
            }
            closedir($dh);
          }
        }
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

      /**
       * Return localization's list.
       *
       * Example:
       * If schema contains l10n.{key} values:
       *
       * { 'config': 'l10n.hello_world' }
       *
       * the current function should return something below:
       *
       * return array(
       *   'hello_world' => __( 'Hello World', $this->domain ),
       * );
       *
       * @author peshkov@UD
       * @return array
       */
      public function get_localization() {
        return apply_filters( 'wpgi::get_localization', array(
          'wpgi_settings' => __( 'WP Google Identity', $this->domain ),
          'wpgi_page_title' => __( 'WP Google Identity Settings', $this->domain ),
          'oauth' => __( 'OAuth', $this->domain ),
          'oauth_menu_desc' => __( 'OAuth Settings', $this->domain ),
          'google' => __( 'Google', $this->domain ),
          'facebook' => __( 'Facebook', $this->domain ),
          'paypal' => __( 'Paypal', $this->domain ),
          'enabled' => __( 'Enabled', $this->domain ),
          'api_key' => __( 'API Key', $this->domain ),
          'redirect_uri' => __( 'Redirect URI', $this->domain ),
          'javascript_origins' => __( 'Javascript Origins', $this->domain ),
          'general' => __( 'General', $this->domain ),
          'general_menu_desc' => __( 'General Settings', $this->domain ),
          'signin_settings' => __( 'Sign-In Button', $this->domain ),
          'page' => __( 'Page', $this->domain ),
          'signin_page_desc' => __( 'Page which is used for Sign-Up', $this->domain ),
          'popup' => __( 'Popup Enabled', $this->domain ),
          'signin_popup_desc' => __( 'Show page in popup. If disabled, user will be redirected to Sign-Up page directly.', $this->domain ),
        ) );
      }

      /**
       * Determine if Utility class contains missed method
       * in other case, just return NULL to prevent ERRORS
       *
       * @author peshkov@UD
       */
      public function __call( $name, $arguments ) {
        if (is_callable(array('\UsabilityDynamics\WPGI\Utility', $name))) {
          return call_user_func_array(array('\UsabilityDynamics\WPGI\Utility', $name), $arguments);
        } else {
          return NULL;
        }
      }

    }

  }

}
