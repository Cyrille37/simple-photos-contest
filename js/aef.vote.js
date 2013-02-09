/*
 * aef.vote.js 
 */

var openVoteBox = function()
{
	jQuery.fancybox({
		href: AefPC.ajaxurl,
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
