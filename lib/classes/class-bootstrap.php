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

        $this->validate_settings();

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

      }

      /**
       * Validate Google Identity Toolkit API settings
       *
       */
      private function validate_settings() {

        /* Show errors only if WP Google Identity Sign-In enabled. */
        if( $this->get( 'signin.enabled' ) == '1' ) {

          /** Be sure that Browser API Key is set. */
          $api_key = $this->get( 'oauth.google.api_key' );
          if( empty( $api_key ) ) {
            $this->errors->add( __( '<b>Browser API Key</b> is not set.', $this->get( 'domain' ) ) );
          }
          $client_id = $this->get( 'oauth.google.client_id' );
          if( empty( $client_id ) ) {
            $this->errors->add( __( '<b>Client ID</b> is not set.', $this->get( 'domain' ) ) );
          }
          $service_account_email = $this->get( 'oauth.google.service_account_email' );
          if( empty( $service_account_email ) ) {
            $this->errors->add( __( '<b>Service Account Email</b> is not set.', $this->get( 'domain' ) ) );
          }
          /** Be sure that Sign-In page is set. */
          $signin_page_id = $this->get( 'signin.page' );
          if( empty( $signin_page_id ) ) {
            $this->errors->add( __( '<b>Sign-In Page</b> is not set.', $this->get( 'domain' ) ) );
          }
          /** Be sure that config file is set and exists. */
          $private_key_file = $this->get( 'oauth.google.private_key_file' );
          if( empty( $private_key_file ) || !file_exists( $private_key_file ) ) {
            $this->errors->add( __( '<b>Private Key path</b> it not set or file does not exist.', $this->get( 'domain' ) ) );
          }

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

        /**
         * Adviser on Settings page
         */
        ob_start();
        ?>
        <div class="adviser">
          <label><?php _e( 'Be advised', $this->domain ); ?>:</label>
          <ul>
            <li><?php printf( __( 'You can find how to configure your Google Identity service <a target="_blank" href="%s">here</a>.', $this->domain ), "https://developers.google.com/identity/toolkit/web/configure-service" ); ?></li>
            <li><?php _e( 'Use <b>[wpgi_signin]</b> shortcode to add Sign-In button on you site.', $this->domain ); ?></li>
          </ul>
        </div>
        <?php
        $description = ob_get_clean();

        return apply_filters( 'wpgi::get_localization', array(
          'wpgi_settings' => __( 'WP Google Identity', $this->domain ),
          'wpgi_page_title' => __( 'WP Google Identity Settings', $this->domain ),
          'google_api_settings' => __( 'Google Identity Toolkit API', $this->domain ),
          'enabled' => __( 'Enabled', $this->domain ),
          'api_key' => __( 'Browser API key', $this->domain ),
          'api_key_desc' => sprintf( __( 'Go to the <a target="_blank" href="%s">Google Developers Console</a>, add new or select existing project and create a Browser API key below the Client ID section of the Credentials page so that your app can access Google APIs', $this->domain ), 'https://console.developers.google.com/' ),
          'redirect_uri' => __( 'Redirect URI', $this->domain ),
          'javascript_origins' => __( 'Javascript Origins', $this->domain ),
          'general' => __( 'General', $this->domain ),
          'general_menu_desc' => $description,
          'signin_settings' => __( 'Sign-In Process', $this->domain ),
          'signin_page' => __( 'Sign-In Page', $this->domain ),
          'signin_page_desc' => __( 'Page which is used for Sign-In. Just add any page and select it here. Do not worry about content, - it will be automatically overwritten.', $this->domain ),
          'popup' => __( 'Popup Enabled', $this->domain ),
          'signin_popup_desc' => __( 'Show Sign-In page in popup. If disabled, user will be redirected to Sign-Up page directly.', $this->domain ),
          'signin_enabled_desc' => __( 'If disabled, native WordPress Sign-In logic is being used.', $this->domain ),
          'redirect_uri_desc' => sprintf( __( 'Copy this value to <b>Authorized Redirect URI</b> field on creating client ID in <a target="_blank" href="%s">Google Developers console</a>.', $this->domain ), 'https://console.developers.google.com/' ),
          'javascript_origins_desc' => sprintf( __( 'Copy this value to <b>Authorized JavaScript Origins</b> field on creating client ID in <a target="_blank" href="%s">Google Developers console</a>.', $this->domain ), 'https://console.developers.google.com/' ),
          'private_key_file' => __( 'Private Key file (p12)', $this->domain ),
          'private_key_file_desc' => __( 'Upload private key file (p12) to your site and set absolute DIR path to it here.', $this->domain ),
          'client_id' => __( 'Client ID', $this->domain ),
          'client_id_desc' => __( 'Be sure you download Server-side configuration file (<b>gitkit-server-config.json</b>) from Identity Toolkit original console. Open the file and copy value of <b>clientId</b>.', $this->domain ),
          'service_account_email' => __( 'Service Account Email', $this->domain ),
          'service_account_email_desc' => __( 'Be sure you download Server-side configuration file (<b>gitkit-server-config.json</b>) from Identity Toolkit original console. Open the file and copy value of <b>serviceAccountEmail</b>.', $this->domain ),
          'disable_native_login' => __( 'Disable native login page', $this->domain ),
          'disable_native_login_desc' => __( 'Optional. User will be redirected to front page. <b>Use it very carefully!</b>', $this->domain ),
          'signin_success_page' => __( 'Sign-In Success page', $this->domain ),
          'signin_success_page_desc' => __( 'Optional. Where user will be redirected to after successful login.', $this->domain ),
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
