(function ($){
    $(document).ready( function () {
        $( 'form.variations_form' ).on( 'found_variation', function( event, variation ){
            let var_id = variation.variation_id;

            if ( !var_id ) {
                var_id = $(this).closest('.product').attr('id').replace('product-', '');
            }

            $.ajax({
                type: 'POST',
                url: brlabelsHelper.ajax_url,
                data: {
                    'action': 'variation_label',
                    'variation_id': var_id
                },
                success: function(result){
                    var labels_parent = $('.berocket_better_labels').parent();
                    $('.berocket_better_labels').trigger('br-remove-old-labels').remove();

                    labels_parent.append(result);
                    $('.berocket_better_labels').trigger('br-update-product');

                    berocket_regenerate_tooltip();
                }
            });
        });
    }); 
})(jQuery);