<?php
// Add custom Theme Functions here

function isMobileDevice() { 
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo 
|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i" 
, $_SERVER["HTTP_USER_AGENT"]); 
} 
/**
 * Enqueue our JS file
 */
function winn_enqueue_scripts() {
	wp_register_script( 'cart-script', get_stylesheet_directory_uri() . '/js/update-cart-item-ajax.js', array( 'jquery' ), time(), true );
	wp_localize_script('cart-script','admin_vars',array('ajaxurl' => admin_url( 'admin-ajax.php' )));
	wp_enqueue_script( 'cart-script' );
	wp_enqueue_style( 'select2', get_stylesheet_directory_uri().'/css/select2.css' );
	wp_enqueue_script( 'select2', get_stylesheet_directory_uri() . '/js/select2.min.js', array( 'jquery' ) );
}
add_action( 'wp_enqueue_scripts', 'winn_enqueue_scripts' );

add_action('woocommerce_before_shop_loop_item_title','before_title_sku');
function before_title_sku(){
	global $product;
	echo '<p><span>'. __('SKU','flatsome') . '</span><span> : </span><span>' . esc_html($product->get_sku()) .' </span></p>';
}
/**
 * Adds a quantity before cartesian.
 */
function add_quantity_before_cart() {
	$product = wc_get_product(get_the_ID());
	$id = get_the_ID();
	$packs = get_post_meta($id, 'packs', true);
	?>
	<div class="pack-wrapper">
	<?php
	/*if (!empty($packs)) {
		?>
		<label for="pri-packs"><?php _e('Choose a pack:', 'storefront');?></label>

		<select name="packs" id="pri-packs">

			<?php
		foreach ($packs as $pack) {
			echo $pack['PACKNAME'] . ' ' . $pack['PACKQUANT'] . '<br>';
			echo ' <option value="' . $pack['PACKQUANT'] . '">' . $pack['PACKNAME'] . '</option>';
		}
		?>
		</select>
		<?php
	}*/
	do_action('show_packs_on_cart_page',$product);
	
	
	if (!$product->is_sold_individually() && $product->is_purchasable()) {
		
		woocommerce_quantity_input(array('input_value'=> 0, 'min_value' => 0, 'max_value' => $product->get_stock_quantity() )); /*, 'max_value' => $product->get_stock_quantity()*/
		//echo '<input name="quantity_nisl" value="'.$inputqty.'" type="number"/>';
	} 
	change_quantity_when_added_to_cart();
	?>
	</div>
	
	<?php

}
add_action('woocommerce_after_shop_loop_item_title', 'add_quantity_before_cart',5);

function change_quantity_when_added_to_cart(){
	add_filter( 'woocommerce_quantity_input_args' , function( $input_args , $product ) {
		$product_cart_id = WC()->cart->generate_cart_id( $product->get_id() );
		$cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
		$cart_quantity = WC()->cart->get_cart_item($cart_item_key)['quantity'];
		$inputqty = ($cart_item_key) ? $cart_quantity : '0';
			
			$input_args[ 'input_value' ] = $inputqty;
			
		
		return $input_args;
	
	}, 10 , 2 );
}
//remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action('woocommerce_after_shop_loop_item_title','nisl_button_woocommerce_add_cart',30);
function nisl_button_woocommerce_add_cart(){
	 global $product;
	 if($product && is_user_logged_in() ) {
	 	$packs = get_post_meta($product->get_id(), 'pri_packs', true);
	 	// print_r($packs);
		 ?>
		 <div class="add-to-cart-button">
		 <?php
			$product_cart_id = WC()->cart->generate_cart_id( $product->get_id() );
			$cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
			$cart_quantity = WC()->cart->get_cart_item($cart_item_key)['quantity'];
			if($cart_item_key && $product->is_in_stock()){
				echo '<p class="confirm_add">'. __('Added ','flatsome').$cart_quantity. __(' units to cart','flatsome').'</p>';
				$dnone = "style='display:none';";
			}
		if($product->is_in_stock()){ 
		 ?>
			<a href="javascript:void(0);" data-quantity="1" <?php echo $dnone; ?> class="primary is-small mb-0 button product_type_simple add_to_cart_button" data-product_id="<?php echo $product->get_id(); ?>" data-product_sku="<?php echo $product->get_sku(); ?>" data-product_pack_step="<?php echo $packs[0]['PACKQUANT']; ?>" data-product_pack_code="<?php echo $packs[0]['PACKCODE']; ?>" ><?php echo __('Add to Cart','flatsome'); ?></a>
		<?php } ?>
		</div>
		<?php
	}
}


/**
 * Shows the stock. on single product page
 */
//add_action('woocommerce_after_shop_loop_item', 'show_stock',50);
add_action('woocommerce_after_add_to_cart_quantity', 'show_stock',10);
function show_stock(){
	global $product, $woocommerce_loop;
	$stock = number_format($product->get_stock_quantity(), 0, '', '');
	if($stock <= 0) {
		$cls = "red";
	}
	else {
		$cls = "gray";
	}
	?>
	<div class="tot-amt-wrap <?php echo $cls; ?>"><!-- totalamt-wrapper -->
		<?php
		if($woocommerce_loop['name'] != 'related'){
			echo '<p>'. __("Inventory","flatsome").": ". __("In Stock","flatsome"). '</p>';
		
		?>
		<label>
			<div>
			<?php 
				echo '<span>'. __('Total Price','flatsome').' :</span>';
				echo '<span><small class="tot-price-product"> 0</small>'.get_woocommerce_currency_symbol().'</span>';	
			?>
			</div>
			
			<script>
				jQuery(function($){
					var price = <?php echo $product->get_price(); ?>;
						

					$('.single-product .product-main input[name=quantity]').change(function(){
						console.log('in on change event '+this.value);
						if(this.value > 0) {
							var product_total = parseFloat(price * this.value);

							$('.tot-price-product').text(product_total.toFixed(2));

						}else {
							$('.tot-price-product').text(0);
						}
					});
					$('.single-product .product-main .pri-packs').change(function(){
						console.log('in on change event '+$('.single-product .product-main input[name=quantity]').value);
						if($('.single-product .product-main input[name=quantity]').value > 0) {
							var product_total = parseFloat(price * this.value);

							$('.tot-price-product').text(product_total.toFixed(2));

						}else {
							$('.tot-price-product').text(0);
						}
					});
				});
			</script>
		</label>
		<?php 
		} ?>	
	</div>
	
	<?php
	
}

//add_action( 'woocommerce_single_product_summary', 'login_button_on_product_page', 35 );
function login_button_on_product_page() {
    global $product;
	 if($product && is_user_logged_in()) { 
		$product_cart_id = WC()->cart->generate_cart_id( $product->get_id() );
		$cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
		$cart_quantity = WC()->cart->get_cart_item($cart_item_key)['quantity'];
		if($cart_item_key){
			echo '<p class="confirm_add">'. __('Added ','flatsome').$cart_quantity. __(' units to cart','flatsome').'</p>';
			$dnone = 'display:none;';
		}
		
	} 
    //echo '<button type="button" data-default_text="Login" data-default_icon="sf-icon-account" class="product_type_simple button alt" onclick="window.location=\'' . esc_attr($link) . '\'"><i class="sf-icon-account"></i><span>Login</span></button>';
}
/**
 * add class to product loop on shop and category page
 */
add_filter('post_class', function ($classes, $class, $product_id) {
	if (is_product_category() || is_shop()) {
		//only add these classes if we're on a product category page.
		$classes = array_merge(['theme-products'], $classes);
	}
	return $classes;
}, 10, 3);


/**
 * Add product SKU on single product page after product title
 */
add_action('woocommerce_single_product_summary', 'replace_product_title_by_product_sku', 5);
function replace_product_title_by_product_sku() {
	global $product, $woocommerce_loop;
	if($woocommerce_loop['name'] != 'related'){
	?>
		<div class="display-sku-barcode">
		<?php
		if ($product->get_sku()) {
			//remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
			echo '<p><span>'. __('SKU','flatsome') . '</span><span> : </span><span>' . esc_html($product->get_sku()) .' </span></p>';
		}
		echo '<p><span>'. __('Barcode','flatsome').'</span><span> : </span><span>'.esc_html(get_post_meta($product->get_id(),'simply_barcode',true)) .' </span></p>';
		?>
		</div>
	<?php
	}
	change_quantity_when_added_to_cart();
}

/**
 * remove excerpt and add description tabs there - beside product image on single product page
 */
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
add_action('woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 40);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);

add_action('woocommerce_single_product_summary', 'show_previous_purchases', 50);
function show_previous_purchases(){
	echo do_shortcode('[block id="previous-purchases-on-product-page"]');
}
/**
 * footer script for header menu hover
 */
add_action('wp_footer', 'custom_footer_script', 10);
function custom_footer_script() {
	$path = get_stylesheet_directory_uri();
	?>
	<script>
		jQuery(document).ready(function(){



			jQuery('.header-nav-main li').hover(function(){

					jQuery('.header-main').addClass('z-indexup');

			},function(){
					jQuery('.header-main').removeClass('z-indexup');

			}
			);

			//jQuery('.single-product .cart .quantity.buttons_added').find('.minus').val('').css('background','url("<?php echo $path; ?>/images/down-arrow.png")');
			//jQuery('.single-product .cart .quantity.buttons_added').find('.plus').val('').css('background','url("<?php echo $path; ?>/images/up-arrow.png")');

			jQuery('.woocommerce-cart .cart_item .quantity.buttons_added').find('.minus').val('').css('background','url("<?php echo $path; ?>/images/cart-down-arrow.png")');
			jQuery('.woocommerce-cart .cart_item .quantity.buttons_added').find('.plus').val('').css('background','url("<?php echo $path; ?>/images/up-arrow.png")');
			/*jQuery('.header-nav-main li .nav-dropdown li').hover(function(){
					jQuery('.header-main').addClass('z-indexup');

			}, function(){

					jQuery('.header-main').removeClass('z-indexup');

			});*/

          
			
		});

		/* Price filter on shop page */
		jQuery('.wpfCheckboxHier select').change(function(){
			console.log('IN');
		    setTimeout(function(){
		        location.reload();
		    }, 1000);
		})
		
	</script>
	<?php
}
// Disable product review (tab)
function woo_remove_product_tabs($tabs) {
	unset($tabs['reviews']); 					// Remove Reviews tab

	return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );


/*add_action('woocommerce_before_single_product', 'backto_category', 5, 0);
function backto_category() {
	global $post;
	$terms = get_the_terms($post->ID, 'product_cat');

	foreach ($terms as $term) {

		$product_cat_id = $term->term_id;

	}
	_e('<p class="backtocat"><a href="#">Back to </a></p>');

}*/

/**
 * Update cart item notes
 */
function winn_update_cart_notes() {
 // Do a nonce check
	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'woocommerce-cart' ) ) {
		wp_send_json( array( 'nonce_fail' => 1 ) );
	 	exit;
	}
 // Save the notes to the cart meta
 	$cart = WC()->cart->cart_contents;
 	$cart_id = $_POST['cart_id'];
 	$notes = $_POST['notes'];
 	$cart_item = $cart[$cart_id];
 	$cart_item['notes'] = $notes;
 	echo $notes;
 	WC()->cart->cart_contents[$cart_id] = $cart_item;
 	WC()->cart->set_session();
 	wp_send_json( array( 'success' => 1 ) );
 	exit;
}
// add_action( 'wp_ajax_winn_update_cart_notes', 'winn_update_cart_notes' );

function winn_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
 	foreach( $item as $cart_item_key=>$cart_item ) {
 		if( isset( $cart_item['notes'] ) ) {
 			$item->add_meta_data( 'notes', $cart_item['notes'], true );
 		}

 		/*$user_id = get_current_user_id();
		$get_pack_step = get_user_meta( $user_id, 'products_pack_step', true );
		$unserialize_data = unserialize($get_pack_step);

		if(!empty($unserialize_data)) {
			foreach($unserialize_data as $key=>$value) {
				if($unserialize_data[$key]['product_id'] == $item->get_product_id()) {
					$item->add_meta_data( 'pack_step', $unserialize_data[$key]['product_pack_step'], true );
					$item->add_meta_data( 'pack_code', $unserialize_data[$key]['product_pack_code'], true );
					
				}
			}
		}*/
 	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'winn_checkout_create_order_line_item', 10, 4 );

// Register main datepicker jQuery plugin script
add_action( 'wp_enqueue_scripts', 'enabling_date_picker' );
function enabling_date_picker() {
    // Only on front-end and checkout page
    if( is_admin() || ! is_checkout() ) return;

    // Load the datepicker jQuery-ui plugin script
    wp_enqueue_script('jquery-ui-datepicker',get_stylesheet_directory_uri().'/js/jquery-ui.js');
    wp_enqueue_style('jquery-ui',get_stylesheet_directory_uri().'/css/jquery-ui.css');
}
// Add custom checkout datepicker field
add_action( 'woocommerce_before_order_notes', 'checkout_display_datepicker_custom_field' );
function checkout_display_datepicker_custom_field( $checkout ) {
    $field_id = 'order_duedate';

    echo '<div id="datepicker-wrapper">';

    woocommerce_form_field( $field_id, array(
        'type' => 'text',
        'class'=> array( 'form-row-wide'),
        'label' => __('Due Date','flatsome'),
        'required' => true,
        'readonly' => true, // Or false
        
    ), '' );

    echo '</div>';


    // Jquery: Enable the Datepicker
    ?>
    <script language="javascript">
    jQuery( function($){
        var a = '#<?php echo $field_id ?>';
        jQuery(a).datepicker({
            dateFormat: 'dd-mm-yy', // ISO formatting date
            minDate : 0 + 1,
        });
    });
    </script>
    <?php
}

// Field validation
add_action( 'woocommerce_after_checkout_validation', 'checkout_datepicker_custom_field_validation', 10, 2 );
function checkout_datepicker_custom_field_validation( $data, $errors ) {
    $field_id = 'order_duedate';

    if ( isset($_POST[$field_id]) && empty($_POST[$field_id]) ) {
        $errors->add( 'validation', __('You must choose a due date.', 'woocommerce') ); 
    }else if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST[$field_id] )){
        $errors->add( 'validation', __('You must choose a valid date.', 'woocommerce') ); 

    }
}

// Save field
add_action( 'woocommerce_checkout_create_order', 'save_datepicker_custom_field_value', 10, 2 );
function save_datepicker_custom_field_value( $order, $data ){
    $field_id = 'order_duedate';
    $meta_key = '_'.$field_id;

    if ( isset($_POST[$field_id]) && ! empty($_POST[$field_id]) ) {
        $order->update_meta_data( $meta_key, esc_attr($_POST[$field_id]) ); 
    }
}


// Display custom field value in admin order pages
add_action( 'woocommerce_admin_order_data_after_billing_address', 'admin_display_date_custom_field_value', 10, 1 );
function admin_display_date_custom_field_value( $order ) {
    $meta_key   = '_order_duedate';
    $meta_value = $order->get_meta( $meta_key ); // Get carrier company

    if( ! empty($meta_value) ) {
        // Display
        echo '<p><strong>' . __("Due Date", "woocommerce") . '</strong>: ' . $meta_value . '</p>';
    }
}

// Display custom field value after shipping line everywhere (orders and emails)
//add_filter( 'woocommerce_get_order_item_totals', 'display_date_custom_field_value_on_order_item_totals', 10, 3 );
function display_date_custom_field_value_on_order_item_totals( $total_rows, $order, $tax_display ){
    $field_id   = 'order_duedate';
    $meta_key   = '_order_duedate';
    $meta_value = $order->get_meta( $meta_key ); // Get carrier company
    //echo 'meta value '.$meta_value;
    if( ! empty($meta_value) ) {
        $new_total_rows = [];

        // Loop through order total rows
        foreach( $total_rows as $key => $values ) {
            $new_total_rows[$key] = $values;

            // Inserting the carrier company under shipping method
            if( $key === 'shipping' ) {
                $new_total_rows[$field_id] = array(
                    'label' => __("Due Date", "woocommerce") . ':',
                    'value' => $meta_value,
                );
            }
        }
        return $new_total_rows;
    }
    return $total_rows;
}

/**
 * 
 * Shows the packs on cartesian page. Add action hook for displaying packs on cart page.
 */
function show_packs_on_cart_page() {
	do_action('show_packs_on_cart_page');
}
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

//Remove Fields from the edit billing address - My account page only
add_filter('woocommerce_billing_fields','wpb_custom_billing_fields');
function wpb_custom_billing_fields( $fields = array() ) {
	if( ! is_account_page() ) return $fields;

	$fields['billing_options'] = array(
        'label' => __('Job Title', 'flatsome'),
        'required' => true,
        'clear' => false,
        'type' => 'text',
        'class' => array('')
    );

	unset($fields['billing_last_name']);
	unset($fields['billing_company']);
	unset($fields['billing_email']);

	$fields['billing_country']['priority'] = 1; 
	$fields['billing_address_1']['priority'] = 2; 
	$fields['billing_address_2']['priority'] = 3; 
	$fields['billing_city']['priority'] = 4; 
	$fields['billing_postcode']['priority'] = 5; 
	$fields['billing_first_name']['priority'] = 6;
	$fields['billing_phone']['priority'] = 7;

	return $fields;
}
// disable add to cart for non registered user
function woocommerce_template_loop_add_to_cart( $args = array() ) {
    global $product;

    if ( $product && is_user_logged_in()) {		
	 
        $defaults = array(
            'quantity'   => 1,
            'class'      => implode(
                ' ',
                array_filter(
                    array(
                        'button',
                        'product_type_' . $product->get_type(),
                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                        $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                    )
                )
            ),
            'attributes' => array(
                'data-product_id'  => $product->get_id(),
                'data-product_sku' => $product->get_sku(),
                'aria-label'       => $product->add_to_cart_description(),
				'rel'              => 'nofollow',
				
            ),
        );

        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

        if ( isset( $args['attributes']['aria-label'] ) ) {
            $args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
        }

        wc_get_template( 'loop/add-to-cart.php', $args );
    }
}

function edit_price_display($price) {
    global $product,$woocommerce_loop;
	$display_text = '<span class="display-text-vat">'.__('Price including VAT','flatsome').'</span>';
	
    if(is_singular('product') && $woocommerce_loop['name'] != 'related') {
        
        
        $display_price = '<ins><span class="display-text">'.__('Price','flatsome').'</span><span class="price-excld-vat amount"> '.get_woocommerce_currency_symbol() . number_format(wc_get_price_excluding_tax($product),3,'.','') .'</span></ins>';
        
        return $display_price . '<div class="price-title-wrapper">'.$display_text . $price . '</div>';
    } else {
        return $price;	
    }
}
add_filter('woocommerce_get_price_html', 'edit_price_display', 5);

//Remove Fields from the edit shipping address - My account page only
add_filter('woocommerce_shipping_fields','custom_override_checkout_fields');
function custom_override_checkout_fields($fields) {


	if( ! is_account_page() && !is_checkout()) return $fields;
	

	$fields['shipping_options'] = array(
        'label' => __('Job Title', 'flatsome'),
        'required' => true,
        'clear' => false,
        'type' => 'text',
        'class' => array('')
    );

    unset($fields['shipping_last_name']);
	unset($fields['shipping_company']);
	unset($fields['shipping_email']);


	$fields['shipping_country']['priority'] = 1; 
	$fields['shipping_address_1']['priority'] = 2; 
	$fields['shipping_address_2']['priority'] = 3; 
	$fields['shipping_city']['priority'] = 4; 
	$fields['shipping_postcode']['priority'] = 5; 
	$fields['shipping_first_name']['priority'] = 6;
	$fields['shipping_phone']['priority'] = 7;
	$fields['shipping_phone']['label'] = __('Phone', 'flatsome');

	return $fields;


}

// remove company and postcode from shipping to new address - T160
add_filter('woocommerce_checkout_fields','override_checkout_fields', 9999);
function override_checkout_fields($fields) {

	unset($fields['shipping']['shipping_postcode']);
	unset($fields['shipping']['shipping_company']);
	unset($fields['billing']['billing_country']);


	
	foreach ( $fields['billing'] as $key => $field ){
		$fields['billing'][$key]['custom_attributes']['readonly'] = 'readonly';
		$fields['billing'][$key]['class'][] = 'user_details';
		$fields['billing'][$key]['label'] = "";    
		$fields['billing'][$key]['required'] = false;
        
    }
	$fields['billing']['billing_postcode']['label'] = __('P.O.', 'flatsome');
	$fields['billing']['billing_postcode']['required'] = false;
	$fields['billing']['billing_postcode']['class'][] = 'op_class';


	return $fields;


}


/**
 * Add the field to the checkout
 */
add_action( 'woocommerce_after_order_notes', 'my_custom_checkout_field' );

function my_custom_checkout_field( $checkout ) {

	$checked = $checkout->get_value( 'check-user-details' ) ? $checkout->get_value( 'check-user-details' ) : 1;
    woocommerce_form_field( 'check-user-details', array(
        'type'          => 'checkbox',
        'class'         => array('check-user-details form-row-wide'),
		'label'         => __('Shipping to User Address', 'flatsome'),
		), 
		$checked);

}



//Close "Ship to A Different Address" by Default - T161
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );




add_action( 'woocommerce_before_edit_account_address_form', 'custom_login_text', 9999, 0 );
function custom_login_text() {
	$user = wp_get_current_user();
    echo '<p>'.__('Hi ','flatsome').$user->user_firstname.__(', here are all the details of your account: orders you placed on the site and personal details. If necessary, option to update them easily','flatsome').'</p>';
}


/* T44- Add to cart button change to "update cart button" */
function webroom_quantity_handler() {

	wc_enqueue_js( '
	jQuery(function($) {
	$(document).on("change", "input.qty", function() {
		if($(this).parents(".price-wrapper").siblings(".add-to-cart-button").has("p.confirm_add").length) {
			$(this).parents(".price-wrapper").siblings(".add-to-cart-button").find(".add_to_cart_button").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
			$(this).parents(".price-wrapper").siblings(".add-to-cart-button").children("p.confirm_add").hide();
			$(this).parents(".price-wrapper").siblings(".add-to-cart-button").find(".add_to_cart_button").addClass("blue_update_cart").text("Update Cart");
			$(this).parents(".price-wrapper").siblings(".add-to-cart-button").find(".add_to_cart_button.added").show();
		}else{
			$(this).parents(".price-wrapper").siblings(".add-to-cart-button").find(".add_to_cart_button").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
		}
	});
	' );
	
	wc_enqueue_js( '
	$(document.body).on("adding_to_cart", function() {
		$("a.added_to_cart").remove();
		console.log("here adding");
		
	});
	});
	' );
	
	wc_enqueue_js( '
	$(document.body).on("added_to_cart", function( data ) {
		console.log("data"); console.log(data);
		var qty = $(".added_to_cart").siblings(".add_to_cart_button").attr("data-quantity");
		$(".added_to_cart").after("<p class=\'confirm_add\'>"+qty + " Pcs added</p>");
		$(".added_to_cart").css("display","none");
		$(".added_to_cart").siblings(".add_to_cart_button.added").css("display","none");
	});
	' );

	
}

//add_action( 'init', 'webroom_quantity_handler' );

add_action('wp_enqueue_scripts','add_custom_scripts',5);
function add_custom_scripts(){
	
	wp_register_script('custom-js',get_stylesheet_directory_uri().'/js/custom.js',array(),time());
	wp_enqueue_script('custom-js');
	$script_params = array(
		
		'updatecart' => __('Update Cart','flatsome'),
		'added' => __( 'Added ','flatsome' ),
		'units' => __( ' units to cart','flatsome' )
	);

	wp_localize_script( 'custom-js', 'scriptParams', $script_params );
}

add_action('wp_ajax_nisl_woocommerce_ajax_remove_from_cart', 'nisl_woocommerce_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_nisl_woocommerce_ajax_remove_from_cart', 'nisl_woocommerce_ajax_remove_from_cart');
function nisl_woocommerce_ajax_remove_from_cart(){
	
	$product_id = $_POST['product_id'];
	$quantity = $_POST['quantity'];
	$product_pack_step = $_POST['product_pack_step'];
	$product_cart_id = WC()->cart->generate_cart_id( $product_id );
   	$cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
	if ( $cart_item_key ) {
		WC()->cart->remove_cart_item( $cart_item_key );
		
		//WC()->cart->add_to_cart( $product_id, $quantity );
	}
	
	wp_die();
}

add_action('wp_ajax_nisl_woocommerce_ajax_add_to_cart', 'nisl_woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_nisl_woocommerce_ajax_add_to_cart', 'nisl_woocommerce_ajax_add_to_cart');
function nisl_woocommerce_ajax_add_to_cart(){

	$product_id = $_POST['product_id'];
	$quantity = $_POST['quantity'];
	$product_pack_step = $_POST['product_pack_step'];
	$product_pack_code = $_POST['product_pack_code'];
	$product_cart_id = WC()->cart->generate_cart_id( $product_id );
	$cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
	$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
	if($passed_validation) {   

		WC()->cart->add_to_cart( $product_id, $quantity );

		$user_id = get_current_user_id();
		$get_pack_step = get_user_meta( $user_id, 'products_pack_step', true );
		$unserialize_data = unserialize($get_pack_step);

		$new_data = array();
		if(!empty($unserialize_data)) {
			$c = 0;
			$new_data = $unserialize_data;
			
			foreach($unserialize_data as $key=>$value) {
				if($unserialize_data[$key]['product_id'] == $product_id) {
					$unserialize_data[$key]['product_pack_step'] = $product_pack_step;
					$unserialize_data[$key]['product_pack_code'] = $product_pack_code;
					$c = 1;
				}
			}

			if($c != 1) {
				end($unserialize_data);
				$key = key($unserialize_data);
				

				$new_key = $key+1;
				$unserialize_data[$new_key]['product_id'] = $product_id;
				$unserialize_data[$new_key]['product_pack_step'] = $product_pack_step;
				$unserialize_data[$new_key]['product_pack_code'] = $product_pack_code;
				$serialize_data = serialize($unserialize_data);
			} else {
				$serialize_data = serialize($unserialize_data);
			}
			update_user_meta( $user_id, 'products_pack_step', $serialize_data );
		} else {
			$new_data[] = array( 'product_id'=>$product_id, 'product_pack_step'=>$product_pack_step, 'product_pack_code'=>$product_pack_code );
			$serialize_data = serialize($new_data);
			update_user_meta( $user_id, 'products_pack_step', $serialize_data );
		}
	}
	wp_die();
}

/*  $debug_tags = array();
add_action( 'all', function ( $tag ) {
	global $debug_tags;
	
		if ( in_array( $tag, $debug_tags ) ) {
			return;
		}
		echo "<pre>" . $tag . "</pre>";
		$debug_tags[] = $tag;
	
} );  */

add_filter( 'woocommerce_update_cart_action_cart_updated', 'custom_update_cart' );
function custom_update_cart( $cart_updated ) {
	$cart = WC()->cart->cart_contents;

	// loop over the cart
	foreach($_POST['cart'] as $cartItemKey => $values) {

	    $cartItem = $cart[$cartItemKey];

	    $yourCustomValue = $values['notes'];

	    $cartItem['notes'] = $yourCustomValue;

	    WC()->cart->cart_contents[$cartItemKey] = $cartItem;

	}
	// WC()->cart->set_cart_contents( $contents );
	return $cart_updated;
}

/* Hide Download tab from the account page */
add_filter( 'woocommerce_account_menu_items', 'custom_remove_downloads_my_account', 999 );
function custom_remove_downloads_my_account( $items ) {
    unset($items['downloads']);
    return $items;
}


add_filter( 'gettext', 'bbloomer_translate_woocommerce_strings', 999, 3 );
  
function bbloomer_translate_woocommerce_strings( $translated, $untranslated, $domain ) {
 
   if ( ! is_admin() && 'woocommerce' === $domain ) { 
		
      switch ( $translated) {
 
			case 'Update Cart' :
	
				$translated = __('Update Cart','flatsome');
				break;
				
			case 'Pcs added' :
				$translated = __('Pcs added','flatsome');
				break;

			case 'Order Notes' :
				$translated = __('Order Notes','flatsome');
				break;
			
		}
	}
		if(!is_admin() && 'yith-woocommerce-wishlist' === $domain) {
			switch ( $translated) {
				case 'No products added to the wishlist' :
					$translated = __('No products added to the wishlist','flatsome');
					break;
				
				/* case 'My wishlist on' :
					$translated = __('My wishlist on',$domain);
					break;
			
				case 'Product Name':
					$translated = __('Product Name',$domain);
					break;  */
			}
		}
		if(!is_admin()){
			switch($translated) {
				case 'My Wishlist' :
					$translated = __('My Wishlist','flatsome');
					break;
				
			}
		}
    
  
   return $translated;
}

/* Price wise sorting */
add_filter('woocommerce_get_catalog_ordering_args', function ($args) {
    $orderby_value = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby'));

    if ('price' == $orderby_value) {
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        $args['meta_key'] = '_price';
    }

    if ('price-desc' == $orderby_value) {
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        $args['meta_key'] = '_price';
    }

    return $args;
});

/* Search by product sku and simple barcode */
function search_by_sku( $search, $query_vars ) {
    global $wpdb;
    if(isset($query_vars->query['s']) && !empty($query_vars->query['s'])){
        $args = array(
            'posts_per_page'  => -1,
            'post_type'       => 'product',
            'meta_query' => array(
            	'relation' => 'OR',
                array(
                    'key' => '_sku',
                    'value' => $query_vars->query['s'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'simply_barcode',
                    'value' => $query_vars->query['s'],
                    'compare' => 'LIKE'
                )
            )
        );
        $posts = get_posts($args);
        if(empty($posts)) return $search;
        $get_post_ids = array();
        foreach($posts as $post){
            $get_post_ids[] = $post->ID;
        }
        if(sizeof( $get_post_ids ) > 0 ) {
                $search = str_replace( 'AND (((', "AND ((({$wpdb->posts}.ID IN (" . implode( ',', $get_post_ids ) . ")) OR (", $search);
        }
    }
    return $search;
    
}
add_filter( 'posts_search', 'search_by_sku', 999, 2 );

/* add_action('init','get_user_id_function',10);
function get_user_id_function(){

	add_filter('woocommerce_payment_gateways', 'add_other_payment_gateway',10);
}
function add_other_payment_gateway( $gateways ){
	$user_id = get_current_user_id();
	$getpayterm = get_user_meta($user_id,'priority_payment_terms',true);
	if(!empty($getpayterm)) {
		
		$gateways['user_'.$user_id] = 'WC_Gateway_'.$getpayterm;
	}
	//echo 'here new'; print_r($user_id);
	return $gateways; 
} */

// add_action( 'woocommerce_before_shop_loop', 'create_filter' );
add_shortcode('pack_filter', 'create_filter');
function create_filter() {
	// echo do_shortcode('[wpf-filters id=1]'); 
	$args = array ( 
		'post_type'  => 'product',
		'posts_per_page'  => -1,
		'meta_query' => array( 
			array( 
				'key' => 'packs', 
				'value' => '',
				'compare' => '!='
			), 
		), 
	);

	$products = get_posts($args);
	$get_post_ids = array();
	$get_pack_name = array();
    foreach($products as $product){
        $get_post_ids[] = $product->ID;
        $get_packs_value = get_post_meta( $product->ID, 'packs', true );

        foreach( $get_packs_value as $pack_value ){
        	if( !in_array($pack_value['PACKNAME'], $get_pack_name))
        		$get_pack_name[] = $pack_value['PACKNAME'];
        }
    }
 
	?>
	<div id="archive-filters" class="wpfFilterContent1">
		<select name="filter_packs" id="filter_packs">
			<option value=""><?php echo __('Packs','flatsome'); ?></option>
		<?php
		foreach($get_pack_name as $value) {
			echo '<option value="'.$value.'">'.$value.'</option>';
		}
		?>
		</select>
	</div>

		<script>
		/*jQuery(document).ready(function($){
			$('.wpfFilterContent select').each(function(){
			    var $this = $(this), numberOfOptions = $(this).children('option').length;
			  
			    $this.addClass('select-hidden'); 
			    $this.wrap('<div class="select"></div>');
			    $this.after('<div class="select-styled"></div>');

			    var $styledSelect = $this.next('div.select-styled');
			    $styledSelect.text($this.children('option').eq(0).text());
			  
			    var $list = $('<ul />', {
			        'class': 'select-options'
			    }).insertAfter($styledSelect);
			  
			    for (var i = 0; i < numberOfOptions; i++) {
			        $('<li />', {
			            text: $this.children('option').eq(i).text(),
			            rel: $this.children('option').eq(i).val()
			        }).appendTo($list);
			    }
			  
			    var $listItems = $list.children('li');
			  
			    $styledSelect.click(function(e) {
			        e.stopPropagation();
			        $('div.select-styled.active').not(this).each(function(){
			            $(this).removeClass('active').next('ul.select-options').hide();
			        });
			        $(this).toggleClass('active').next('ul.select-options').toggle();
			    });
			  
			    $listItems.click(function(e) {
			        e.stopPropagation();
			        $styledSelect.text($(this).text()).removeClass('active');
			        $this.val($(this).attr('rel'));
			        $list.hide();
			        // console.log('Value::',$this.val());
			    });
			  
			    $(document).click(function() {
			        $styledSelect.removeClass('active');
			        $list.hide();
			    });

			});
		});*/
		(function($) {
			$(document).on('change', '#filter_packs', function(){
				var pack_name = $(this).val();
				// var url = '<?php echo home_url('property'); ?>';
				var url = window.location.pathname;
				url += '?packs='+pack_name;
				
				// reload page
				window.location.replace( url );
			})
		})(jQuery);
		// (function($) {
		// 	// change
		// 	$('#archive-filters').on('change', 'input[type="checkbox"]', function(){

		// 		// vars
		// 		var url = '<?php echo home_url('property'); ?>';
		// 			args = {};
					
		// 		// loop over filters
		// 		$('#archive-filters .filter').each(function(){
		// 			// vars
		// 			var filter = $(this).data('filter'),
		// 				vals = [];
					
		// 			// find checked inputs
		// 			$(this).find('input:checked').each(function(){
		// 				vals.push( $(this).val() );
		// 			});
					
		// 			// append to args
		// 			args[ filter ] = vals.join(',');
		// 		});
				
		// 		// update url
		// 		url += '?';
				
		// 		// loop over args
		// 		$.each(args, function( name, value ){
		// 			url += name + '=' + value + '&';
		// 		});
				
		// 		// remove last &
		// 		url = url.slice(0, -1);
		// 		// reload page
		// 		window.location.replace( url );
		// 	});
		// })(jQuery);
		</script>
		<style>
		/*.select-hidden {
			display: none;
			visibility: hidden;
			padding-right: 10px;
		}
		.select {
			cursor: pointer;
			display: inline-block;
			position: relative;
			font-size: 16px;
			color: #fff;
			width: 220px;
			height: 40px;
		}
		.select-styled {
			position: absolute; 
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			background-color: #999;
			padding: 8px 15px;
			transition: all 0.2s ease-in;
			
		}
		.select-styled:after {
			content:"";
			width: 0;
			height: 0;
			border: 7px solid transparent;
			border-color: #fff transparent transparent transparent;
			position: absolute;
			top: 16px;
			right: 10px;
		}
		.select-styled:hover {
			background-color: #999;
		}
		.select-styled:active, .select-styled.active {
			background-color: #999;
		}
		.select-styled.active:after {
			top: 9px;
			border-color: transparent transparent #fff transparent;
		}

		.select-options {
			display: none; 
			position: absolute;
			top: 100%;
			right: 0;
			left: 0;
			z-index: 999;
			margin: 0;
			padding: 0;
			list-style: none;
			background-color: #999;
		}
		li {
			margin: 0;
			padding: 12px 0;
			text-indent: 15px;
			border-top: 1px solid #999;
			transition: all 0.15s ease-in;
		}
		li:hover {
			color: #999;
			background: #fff;
		}
		li[rel="hide"] {
			display: none;
		}*/

		</style>
<?php }

add_action('pre_get_posts', 'my_pre_get_posts', 10, 1);
function my_pre_get_posts( $query ) {
	
	if( is_admin() ) return;
	
	if( !$query->is_main_query() ) return;

	$meta_query = $query->get('meta_query');

	if( empty($_GET['packs' ]) ) {
		return;
	}
	$value = $_GET['packs'];

	$meta_query[] = array(
        'key' => 'packs',
        'value' => '\;i\:PACKNAME\;|\"' . $value . '\";',
        'compare' => 'REGEXP'
    );
	
	$query->set('meta_query', $meta_query);

}


add_action( 'woocommerce_review_order_after_shipping', 'output_total_excluding_tax' );
function output_total_excluding_tax() {
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$subtotal = 0;
		$subtotal += $cart_item['data']->get_price() * $cart_item['quantity'];
	}
}

//add_action( 'init', 'delete_product_images', 10, 1 );

function delete_product_images( $post_id )
{
    $product = wc_get_product( $post_id );

    if ( !$product ) {
        return;
    }

    $featured_image_id = $product->get_image_id();
    $image_galleries_id = $product->get_gallery_image_ids();

    if( !empty( $featured_image_id ) ) {
		wp_delete_post( $featured_image_id );
    }

    if( !empty( $image_galleries_id ) ) {
        foreach( $image_galleries_id as $single_image_id ) {
			wp_delete_post( $single_image_id );
        }
    }
}

add_action( 'woocommerce_add_order_item_meta', 'add_order_item_meta' , 10, 2);
function add_order_item_meta ( $item_id, $values ) {
	$user_id = get_current_user_id();
	$get_pack_step = get_user_meta( $user_id, 'products_pack_step', true );
	$unserialize_data = unserialize($get_pack_step);

	if(!empty($unserialize_data)) {
		foreach($unserialize_data as $key=>$value) {
			if($unserialize_data[$key]['product_id'] == $values['product_id']) {
				wc_add_order_item_meta( $item_id, 'pack_step', $unserialize_data[$key]['product_pack_step'] );
				wc_add_order_item_meta( $item_id, 'pack_code', $unserialize_data[$key]['product_pack_code'] );
				unset($unserialize_data[$key]);
			}
		}
		$serialize_data = serialize($unserialize_data);
		update_user_meta( $user_id, 'products_pack_step', $serialize_data );
	}
}

add_filter( 'woocommerce_cart_contents_count', 'bbloomer_alter_cart_contents_count', 9999, 1 );
function bbloomer_alter_cart_contents_count( $count ) {
   $count = count( WC()->cart->get_cart() );
   return $count;
}

add_action( 'woocommerce_before_add_to_cart_button', 'misha_before_add_to_cart_btn' );
function misha_before_add_to_cart_btn(){
	global $product;
	if($product && is_user_logged_in() ) {
	 	$packs = get_post_meta($product->get_id(), 'pri_packs', true);
		?>
		<div class="step-custom-fields">
            <input type="hidden" class="custom_pack_step" name="pack_step" value="<?php echo $packs[0]['PACKQUANT']; ?>">
        </div>
        <div class="clear"></div>
        <script>
        jQuery(document).ready(function(){
	        jQuery(document).on('click change', '.product-info.summary .pri-packs', function() {
				jQuery('.cart .custom_pack_step').val(jQuery(this).val());
			})
		})
        </script>
    <?php }
}

//Hide Shipping Fields for Local Pickup
add_action( 'woocommerce_after_checkout_form', 'bbloomer_disable_shipping_local_pickup' );
  
function bbloomer_disable_shipping_local_pickup( $available_gateways ) {
    
   // Part 1: Hide shipping based on the static choice @ Cart
 
   $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
   $chosen_shipping = $chosen_methods[0];
   if ( 0 === strpos( $chosen_shipping, 'local_pickup' ) ) {
   ?>
      <script type="text/javascript">
         jQuery('#customer_details .woocommerce-shipping-fields').fadeOut();
      </script>
   <?php  
   } 
 
   // Part 2: Hide shipping based on the dynamic choice @ Checkout
 
   ?>
      <script type="text/javascript">
         jQuery('form.checkout').on('change','input[name^="shipping_method"]',function() {
            var val = jQuery( this ).val();
            if (val.match("^local_pickup")) {
                     jQuery('#customer_details .woocommerce-shipping-fields').fadeOut();
               } else {
               jQuery('#customer_details .woocommerce-shipping-fields').fadeIn();
            }
         });
      </script>
   <?php
  
}

function ss_cart_updated( $cart_item_key, $cart ) {
    
    $product_id = $cart->cart_contents[ $cart_item_key ]['product_id'];

    $user_id = get_current_user_id();
	$get_pack_step = get_user_meta( $user_id, 'products_pack_step', true );
	$unserialize_data = unserialize($get_pack_step);

	if(!empty($unserialize_data)) {
		foreach($unserialize_data as $key=>$value) {
			if($unserialize_data[$key]['product_id'] == $product_id) {
				unset($unserialize_data[$key]);
			}
		}
		$serialize_data = serialize($unserialize_data);
		update_user_meta( $user_id, 'products_pack_step', $serialize_data );
	}

}
add_action( 'woocommerce_remove_cart_item', 'ss_cart_updated', 10, 2 );


/****************  auto generate sub menu   **************************/
function _custom_nav_menu_item( $title, $url, $order, $parent = 0 ){
  $item = new stdClass();
  $item->ID = 1000000 + $order + $parent;
  $item->db_id = $item->ID;
  $item->title = $title;
  $item->url = $url;
  $item->menu_order = $order;
  $item->menu_item_parent = $parent;
  $item->type = '';
  $item->object = '';
  $item->object_id = '';
  $item->classes = array();
  $item->target = '';
  $item->attr_title = '';
  $item->description = '';
  $item->xfn = '';
  $item->status = '';
  return $item;
}

add_filter("wp_get_nav_menu_items", function ($items, $menu, $args) {
    if( $menu->term_id != 379 ) return $items; // Where 24 is Menu ID, so the code won't affect other menus.

    // don't add child categories in administration of menus
    if (is_admin()) {
        return $items;
    }
    $ctr = ($items[sizeof($items)-1]->ID)+1;
    foreach ($items as $index => $i)
    {
        if ("product_cat" !== $i->object) {
            continue;
        }
        $menu_parent = $i->ID;
        $terms = get_terms( array('taxonomy' => 'product_cat', 'parent'  => $i->object_id ) );
        foreach ($terms as $term) {
            $new_item = _custom_nav_menu_item( $term->name, get_term_link($term), $ctr, $menu_parent );
            $items[] = $new_item;
            $new_id = $new_item->ID;
            $ctr++;
            $terms_child = get_terms( array('taxonomy' => 'product_cat', 'parent'  => $term->term_id ) );
            if(!empty($terms_child))
            {
                foreach ($terms_child as $term_child)
                {
                    $new_child = _custom_nav_menu_item( $term_child->name, get_term_link($term_child), $ctr, $new_id );
                    $items[] = $new_child;
                    $ctr++;
                }
            }
        }
    }

    return $items;
}, 10, 3);
