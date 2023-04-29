(function( $ ) {
	'use strict';
  var $noticeDivCSS = jQuery( '<style type="text/css">#scorg-live-reload {position: fixed;bottom: 0;right: 0; display:none; background: black;color: white;padding: 5px 10px;font-size: 13px;}</style>' );
  jQuery("head").append( $noticeDivCSS );
  var $noticeDiv = jQuery( '<div id="scorg-live-reload">CSS reloaded</div>' );
  jQuery("body").append( $noticeDiv )
  /* broadcast channel */
	const scorg_channel = new BroadcastChannel('scorg_channel');
  scorg_channel.onmessage = function (message){
    if(message.data == "reload"){
      SCORGLiveReload();
    }
  }
	/* broadcast channel */
  
  $( document ).keydown( function(e){
      if( e.key != 'F9' ){
          return true;
      }
      
      SCORGLiveReload();
  });
})( jQuery );


function SCORGLiveReload(){
  var styles = document.getElementsByTagName( "LINK" );
  for( var x in styles ){
    if( typeof styles[x].href != 'undefined' && styles[x].href.match( /\.css(\?.+)?$/ ) ){
      var newLink = styles[x].href.replace( /[\?|&]_live=\d+(\.\d+)?/, '' );
      var randStr = '_live=' + ( Math.random() * 9999999 );
      
      if( newLink.match (/\.css(\?.*)$/ ) ){
        newLink += '&' + randStr;
      } else {
        newLink += '?' + randStr;
      }
       
      styles[x].href = newLink;
    }
  }
  
  jQuery("#scorg-live-reload").hide(0).delay(300).fadeIn( 600 );
   
  setTimeout( function(){
    jQuery('#scorg-live-reload').slideUp( 300, function(){
      jQuery(this).hide(0);
    } );
  }, 2000  );
}


