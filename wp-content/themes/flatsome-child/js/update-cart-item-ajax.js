jQuery(document).ready(function(){
	jQuery('.winn_cart_notes').on('change keyup paste',function(){
	 	/*jQuery('.cart_totals').block({
	 		message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
	 	});*/
	 	var cart_id = jQuery(this).data('cart-id');
	 	jQuery.ajax(
	 	{
	 		type: 'POST',
	 		url: admin_vars.ajaxurl,
	 		data: {
	 			action: 'winn_update_cart_notes',
	 			security: jQuery('#woocommerce-cart-nonce').val(),
	 			notes: jQuery('#cart_notes_' + cart_id).val(),
	 			cart_id: cart_id
	 		},
		 	success: function( response ) {
		 		//jQuery('.cart_totals').unblock();
		 	}
		}
	 	)
	});

	jQuery(document).on('click change', '.pack-wrapper .pri-packs', function() {
		var pack_code = jQuery('option:selected', this).attr('pack-code');
		var step_val = jQuery(this).next('.quantity.buttons_added').find('.qty').attr('step');

		jQuery(this).parent().parent().find('.step-custom-fields').find('.custom_pack_step').val(step_val);
		jQuery(this).parent().parent().find('.add-to-cart-button').find('.add_to_cart_button').attr('data-product_pack_step',step_val);
		jQuery(this).parent().parent().find('.add-to-cart-button').find('.add_to_cart_button').attr('data-product_pack_code',pack_code);
	})
});