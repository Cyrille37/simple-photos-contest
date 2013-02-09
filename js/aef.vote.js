/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready( function() {

	jQuery('a.social_auth_facebook').click(function() {
		
		var client_id = jQuery('input.social_auth_facebook_client_id').val();
		var redirect_uri = jQuery('input.social_auth_facebook').val();

		if(client_id == '') {
			alert('Sorry, the Facebook provider is not configured.')
		} else {
			window.open('https://graph.facebook.com/oauth/authorize?client_id=' + client_id + '&redirect_uri=' + redirect_uri + '&scope=email',
				'','scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
		}
	});

	jQuery('a.social_auth_google').click(function() {

		var redirect_uri = jQuery('input.social_auth_google').val();
		window.open(redirect_uri,'','scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
	});
	
	jQuery("a.social_auth_yahoo").click( function() {
		var redirect_uri = jQuery('input.social_auth_yahoo').val();
		window.open(redirect_uri,'','scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
	});

	jQuery('#social-auth-form').hide();
	jQuery('#vote-form').hide();
	
});

window.aef_social_auth = function(auth_callback_result) {

	jQuery('#aef-vote-loader').show();

	var params = {};
	jQuery.each(auth_callback_result, function(key, value) { 
		params[key] = value ;
	});  
	params.action = 'vote_auth' ;

	jQuery.post(
		AefPC.ajaxurl,
		params,
		function( jsonString ) {
			console.dir( jsonString );
			var res = JSON.parse(jsonString);
			jQuery('#aef-vote-loader').hide();
			if( res.command == 'auth_ok' )
			{
				jQuery('#social-auth-form').hide();
				jQuery('#vote-form').show();		
			}
			else if( res.command == 'error' )
			{
				alert( res.message );
			}
			else{
				alert('unknow result');
			}
		}
		);

};

/**
 * Function to call on vote dialog open event.
 * It calls the server to find the voter's status (ajax action vote_init).
 */
var aef_vote_dialog_onOpen = function ()
{

	jQuery.post(
		//
		AefPC.ajaxurl,
		{
			action : 'vote_init'
		},
		function( jsonString ) {
			console.dir( jsonString );
			var res = JSON.parse(jsonString);

			jQuery('#aef-vote-loader').hide();

			switch(res.command)
			{
				case 'show_auth_buttons':
					jQuery('#social-auth-form').show();
					break;
				case 'show_vote':
					jQuery('#vote-form .aef-vote-voter-email').text(res.voter_email);
					gallery.current_index
					jQuery('#vote-form').show();
					break;
				default:
					alert('unknow error');
					break;
			}
		}

		);
}
