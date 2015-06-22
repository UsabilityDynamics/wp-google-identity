<?php
/**
 * Sign-In page
 */

/**
 * Set page where user will be redirected to
 * after successful login.
 */

global $wp_query;
$trnst = get_transient( 'wpgi_signin_redirect_to' );
if(
  !empty( $wp_query->queried_object ) &&
  empty( $trnst ) &&
  isset( $_REQUEST[ 'mode' ] ) &&
  $_REQUEST[ 'mode' ] == 'select'
) {
  $referer = $_SERVER[ 'HTTP_REFERER' ];
  $blog = get_blog_details();
  if( strpos( $referer, $blog->domain ) !== false ) {
    $current = get_permalink( $wp_query->queried_object->ID );
    $current = trim( str_replace( home_url(), '', $current ), '/\\' );
    if( !empty( $current ) && strpos( $referer, $current ) === false ) {
      set_transient( 'wpgi_signin_redirect_to', $referer );
    }
  }
}

$providers = array();
foreach( ud_get_wp_google_identity( 'providers', array() ) as $provider => $v ) {
  if( $v == '1' && $provider !== 'password_account' ) {
    array_push( $providers, $provider );
  }
}

if( !empty( $providers ) ) {
  $providers = '"' . implode( '","', $providers ) . '"';
} else {
  $providers = '';
}

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
      idps: [ <?php echo $providers; ?> ],
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
      <?php if( ud_get_wp_google_identity( 'providers.yahoo' ) == '1' ) {
        ?>JSON.parse('<?php echo json_encode(file_get_contents("php://input")); ?>')<?php
      } else {
        ?>'JAVASCRIPT_ESCAPED_POST_BODY'<?php
      } ?>);
  </script>
  <?php if( ud_get_wp_google_identity( 'providers.password_account' ) !== '1' ) : ?>
    <style>form .gitkit-recommend-option, form .gitkit-text { display: none; }</style>
  <?php endif; ?>
  <?php do_action( 'wpgi_signin_page_header' ); ?>
</head>
<body>

<!-- Include the sign in page widget with the matching 'gitkitWidgetDiv' id -->
<div id="gitkitWidgetDiv"></div>
<!-- End identity toolkit widget -->

</body>
</html>