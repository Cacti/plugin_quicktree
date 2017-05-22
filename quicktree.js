
jQuery(document).ready( function() {

    // bind for the popup tree-selection box
    jQuery('#qt_existing').bind('click', function (e) {
        jQuery('#qt_treeselector').show();
        e.preventDefault();

    });
    
    jQuery(document).bind('click', function(e) {
        var target = jQuery( e.target );

        // so we don't just undo the click on the link
        if( !target.is('#qt_existing')) {
            // if the target isn't contained by the popup, hide the popup
            if ( target.closest('#qt_treeselector').length < 1 ) {
                jQuery('#qt_treeselector').hide();
                return;
            }
      }

    });
    


});