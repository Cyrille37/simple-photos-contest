/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

console.log('ajaxurl: '+AefPC.ajaxurl);


function testVote()
{
	jQuery.post(

		// see tip #1 for how we declare global javascript variables
		AefPC.ajaxurl,
		{
			// here we declare the parameters to send along with the request
			// this means the following action hooks will be fired:
			// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
			action : 'vote',

			// other parameters can be added along with "action"
			toto : "coucou"
		},

		function( response ) {
			alert( response );
		}
		
		);
}
