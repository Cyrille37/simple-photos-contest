<?php
require_once( __DIR__ . '/../openid/openid.php');
require_once(__DIR__ . '/../auth.php' );

try {
	if (!isset($_GET['openid_mode']) || $_GET['openid_mode'] == 'cancel') {
		$openid = new LightOpenID();
		$openid->identity = 'https://www.google.com/accounts/o8/id';
		//$openid->required = array('namePerson/first', 'namePerson/last', 'contact/email');
		$openid->required = array('contact/email');
		header('Location: ' . $openid->authUrl());
	}
	else {
		$openid = new LightOpenID();
		if ($openid->validate()) {
			$google_id = $openid->identity;
			$attributes = $openid->getAttributes();
			$email = $attributes['contact/email'];
			$first_name = isset($attributes['namePerson/first']) ? $attributes['namePerson/first'] : '';
			$last_name = isset($attributes['namePerson/last']) ? $attributes['namePerson/last'] : '';
			$signature = aef_auth_generate_signature($google_id);
			?>
			<html>
				<head>
					<script>
						function init() {
							window.opener.aef_vote_auth_callback({
								'social_auth_provider' : 'google', 
								'social_auth_signature' : '<?php echo $signature ?>',
								'social_auth_openid_identity' : '<?php echo $google_id ?>',
								'social_auth_email' : '<?php echo $email ?>',
								'social_auth_first_name' : '<?php echo $first_name ?>',
								'social_auth_last_name' : '<?php echo $last_name ?>'
							});
							window.close();
						}
					</script>
				</head>
				<body onload="init();">
				</body>
			</html>      
			<?php
		}
	}
}
catch (ErrorException $e) {
	echo $e->getMessage();
}
?>