<?php
/**
 * Sign-In page
 */
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <script type="text/javascript" src="//www.gstatic.com/authtoolkit/js/gitkit.js"></script>
  <link type="text/css" rel="stylesheet" href="//www.gstatic.com/authtoolkit/css/gitkit.css" />
  <script type="text/javascript">
    var config = {
      apiKey: '<?php echo ud_get_wp_google_identity( 'oauth.google.api_key' ) ?>',
      signInSuccessUrl: '/',
      idps: [ "google", "facebook" ],
      oobActionUrl: '/',
      siteName: '<?php echo get_bloginfo( 'name' ) ?>',
      acUiConfig: {
        title: '<?php printf( __( 'Sign In to %s', ud_get_wp_google_identity( 'domain' ) ), get_bloginfo( 'name' ) ); ?>',
        favicon: '<?php echo trailingslashit( home_url() ); ?>favicon.ico'
      }
    };
    // The HTTP POST body should be escaped by the server to prevent XSS
    window.google.identitytoolkit.start(
      '#gitkitWidgetDiv', // accepts any CSS selector
      config,
      'JAVASCRIPT_ESCAPED_POST_BODY');
  </script>

</head>
<body>

<!-- Include the sign in page widget with the matching 'gitkitWidgetDiv' id -->
<div id="gitkitWidgetDiv"></div>
<!-- End identity toolkit widget -->

</body>
</html>