<?php
require_once(__DIR__ . '/../auth.php' );
require_once( __DIR__ . '/../../aef-photos-contest.php' );
//require_once( __DIR__ . '/facebook.php' );

$aefPC = new AefPhotosContest();

$client_id = $aefPC->getOption('facebookClientId');
$secret_key = $aefPC->getOption('facebookSecretKey');

if(isset($_GET['code'])) {

		$code = $_GET['code'];
		// $access_token is set by parse_str()
		$access_token = null ;
		parse_str(
			aef_curl_get_contents("https://graph.facebook.com/oauth/access_token?" .
			'client_id=' . $client_id . '&redirect_uri=' . urlencode(AefPhotosContest::$plugin_url . 'auth/facebook/callback.php') .
			'&client_secret=' .  $secret_key .
			'&code=' . urlencode($code)));
		$signature = aef_auth_generate_signature($access_token);  
	?>
	<html>
	<head>
	<script>
	function init() {
		window.opener.aef_vote_auth_callback({
			'social_auth_provider' : 'facebook',
			'social_auth_signature' : '<?php echo $signature ?>',
			'social_auth_access_token' : '<?php echo $access_token ?>'});
		window.close();
	}
	</script>
	</head>
	<body onload="init();">
	</body>
	</html>
	<?php

} else {
  $redirect_uri = urlencode(AefPhotosContest::$plugin_url . 'auth/facebook/callback.php');
  wp_redirect('https://graph.facebook.com/oauth/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&scope=email');
}
?>