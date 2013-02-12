/*
 * aef.vote.js 
 */

var openVoteBox = function()
{
	jQuery.fancybox({
		href: AefPC.ajaxurl,
		width: 500 ,
		height: 340,
		ajax : {
			type: 'POST',
			data: 'action=vote_init'
		},
		onStart: function ()
		{
			this.ajax.data += '&photo_id='+getCurrentPhotoId() ;
		}
	});
	
};

function voteLogout()
{
	var params = {};  
	params.action = 'vote_init' ;
	params.logout = true ;

	jQuery.post(
		AefPC.ajaxurl,
		params,
		function( jsonString ) {
			setTimeout( 'openVoteBox()', 1000 );
			jQuery.fancybox.close();
		}
	);

}

window.aef_vote_auth_callback = function(auth_callback_result) {

	var params = {};
	jQuery.each(auth_callback_result, function(key, value) { 
		params[key] = value ;
	});  
	params.action = 'vote_auth' ;

	jQuery.post(
		AefPC.ajaxurl,
		params,
		function( jsonString ) {
			var res = JSON.parse(jsonString);
			if( res.command == 'auth_ok' )
			{
				setTimeout( 'openVoteBox()', 1000 );
				jQuery.fancybox.close();
			}
			else
			{
				if( res.command == 'error' ){
					alert( res.message );
				}else{
					alert('unknow result');
				}
			}
		}
		);

};

function isValidEmail(email) {
	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	if( !emailReg.test( email ) ) {
		return false;
	} else {
		return true;
	}
}
