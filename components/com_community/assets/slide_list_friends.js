( function( $, window, document ){
	
	$( document ).ready( function(){
		
	  var lis = $( '#community-wrap ul.sugerencias' ).find('li');
	  
	  lis.eq(0).show();
	  
	  $( 'a.no_acepta' ).on( 'click' , function(e){
			showNextUser();
		});
		
		$( 'body' ).delegate( '#cWindowAction .pull-right', 'click', function( e ){
			joms.friends.addNow();
			showNextUser();	
		});

	});
	
	function showNextUser(){
		
		var lis = $( '#community-wrap ul.sugerencias' ).find('li');
		var displayed = 0;
		
		// Get the display block one
		$.each( lis, function( key, value ){
			
			if( $( value ).css( 'display' ) == 'block' ){
				displayed = key;
			}
		
		});
		
		lis.hide();
		
		if( (displayed + 1) < lis.length ){
			lis[ displayed + 1 ].show();
		}
		
		
	}
	
	

})( joms.jQuery, this, this.document, undefined );