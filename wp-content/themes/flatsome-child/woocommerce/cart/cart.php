<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.8.0
 */


defined( 'ABSPATH' ) || exit;

$row_classes     = array();
$main_classes    = array();
$sidebar_classes = array();

$auto_refresh  = get_theme_mod( 'cart_auto_refresh' );
//$row_classes[] = 'row-large';
$row_classes[] = 'row-divided';

if ( $auto_refresh ) {
	$main_classes[] = 'cart-auto-refresh';
}


$row_classes     = implode( ' ', $row_classes );
$main_classes    = implode( ' ', $main_classes );
$sidebar_classes = implode( ' ', $sidebar_classes );


do_action( 'woocommerce_before_cart' ); ?>
<div class="woocommerce row <?php echo $row_classes; ?>">
<div class="col large-9 pb-0 <?php echo $main_classes; ?>">

<?php wc_print_notices(); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
<div class="cart-wrapper sm-touch-scroll">

	<?php do_action( 'woocommerce_before_cart_table' ); ?>
	
	<?php 
	if(isMobileDevice()){
	?>
  	<div class="row row-main">
            <div class="large-12 col">
                <div class="col-inner">
                    <div class="woocommerce">
                    	<?php
                    	$i=0;
                    	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
									$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

								$product = wc_get_product($cart_item['product_id']);
									$id = $cart_item['product_id'];
									$packs = get_post_meta($id, 'packs', true);
						?>
                        <div class="cart_items_wrapper woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                            <div class="cart_product">
                                <div class="p-img product-thumbnail">
                                   <?php
									$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

									if ( ! $product_permalink ) {
										echo $thumbnail; // PHPCS: XSS ok.
									} else {
										printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
									}
									?>
                                </div>
                                <div class="p-details">
                                    <h4 class="p-name">
                                    	<?php
										if ( ! $product_permalink ) {
											echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
										} else {
											echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
										}

										do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

										// Meta data.
										echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

										// Backorder notification.
										if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
											echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
										}
										?>
                                    </h4> 
                                    <p class="mkt"><?php esc_attr_e('SKU','flatsome'); 
                                    echo ' : '.esc_attr($_product->get_sku()); ?></p>
                                    <p class="p-price">
                                    	<?php esc_attr_e( 'Total Quantity', 'flatsome' ); ?> <span> <?php echo $packs[0]['PACKQUANT']; ?> | <?php echo $cart_item['quantity']; ?></span>
                                    </p>
                                    <p class="p-price">
                                    	<?php 
                                    	esc_attr_e('Price','flatsome'); ?><span> <?php echo wc_price( wc_get_price_including_tax( $_product ) ); ?> | <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?></span>
                                    </p>
                                    <p class="p-price">
                                    	<?php 
                                    	esc_attr_e('Total Price','flatsome');
                                    	?>
                                    	<span> 
                                    		<?php
											$priceaftervat = number_format(($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']), '2', '.','');
											echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$priceaftervat.'</span>';
											?> | <?php
											echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
												?>
											
										</span>
                                    </p>
                                   
                                    
                                </div>
                                <div class="p-action">
                                    
                                    	<?php 
                                    	echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove delete-icon" aria-label="%s" data-product_id="%s" data-product_sku="%s"><img src="'.get_stylesheet_directory_uri().'/images/trash.png"></a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_html__( 'Remove this item', 'flatsome' ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
										);
										?>
                                    
                                    <a href="javascript:void(0);" class="edit-icon" data-uid="<?php echo $i; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/edit.png"></a>
                                </div>
                            </div>

                            <div class="c_edit_detail" id="edit_details_<?php echo $i; ?>">
                               <div class="p-pack">
                                  <div class="qty"><?php esc_attr_e( 'Total Quantity :', 'flatsome' ); ?> <span> <?php echo $cart_item['quantity']; ?></span></div>
                                  <div class="select-pack">
                                  	<?php
									$product = wc_get_product($cart_item['product_id']);
									$id = $cart_item['product_id'];
									$packs = get_post_meta($id, 'packs', true);
									?>
									<div class="pack-wrapper">
										<?php
										
										do_action('show_packs_on_cart_page',$_product);
										
									?>
                                  </div>
                               </div>
                               <div class="quantity-pack">
                               	<?php 
                               	if (!$product->is_sold_individually() && $product->is_purchasable()) {
									$steps  = $packs[0]['PACKQUANT'];
									woocommerce_quantity_input(array('input_value' => $cart_item['quantity'], 'min_value' => 0,'max_value'=> 0, 'step' => $steps), $_product);
									   
									//woocommerce_quantity_input(array('input_value' => $cart_item['quantity'], 'min_value' => 0)); 
								}else{
									sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								}
                               	?>
                               </div>
                           	</div>
                               <div class="unit-price">
                               	<?php
                                	esc_attr_e('Unit Price:','flatsome');
                                echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );  ?></span>
                               </div>
                               <div class="discount-price">
                               		<?php 
                               		$discount_price = number_format(($cart_item['line_total'] - $cart_item['line_subtotal']),'2','.','') ;
							
                               		?>
                                    <div> <?php 
                                    esc_attr_e('Price incl. Discount: ','flatsome');
                                    	$p_price = $cart_item['data']->get_price();
										$finalprice = number_format(($p_price - $discount_price),'2','.','') ;
								
										echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$finalprice.'</span>';?>
                                    </div>
                                    <div class="discount">
                                    	<?php 
                                    	
										echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$discount_price.'</span>'; ?>
									</div>
                               </div>
                               <div class="total-vat">
                                    <div>
                                    	<?php
                                    	esc_attr_e('Total incl. VAT :','flatsome');
                                    	$priceaftervat = number_format(($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']), '2', '.','');
										echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$priceaftervat.'</span>';
                                    	?>
                                    </div>
                                    <div class="total-price">
                                    	<?php
                                    	esc_attr_e('Total :','woocommmerce');
                                    	echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); 
										
										?>
                                    </div>
                               </div>
 
                               <div class="commentbox">
                               		<?php 
									$notes = isset( $cart_item['notes'] ) ? $cart_item['notes'] : '';
									 printf(
									 '<div><input placeholder="'.__("Add Comment","flatsome").'" type="text" class="%s" id="cart_notes_%s" data-cart-id="%s" value="%s" name="cart[%s][%s]"></div>','winn_cart_notes',
									 $cart_item_key,
									 $cart_item_key,
									 $notes,
									 $cart_item_key,
									 'notes'
									 );
									 ?>
                               </div>
                            </div>

                        </div>
                        <?php 
                    	}
                    	$i++;
                    } //foreach over
                        ?>
                    </div>
                </div> 
            </div>
            <div class="large-12 col">
            	<button type="submit" class="button primary mt-0 pull-left small" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'flatsome' ); ?>"><?php esc_html_e( 'Update cart', 'flatsome' ); ?></button>
			</div>
            
        </div>
  	<?php
		fl_woocommerce_version_check( '3.4.0' ) ? wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ) : wp_nonce_field( 'woocommerce-cart' );
	}else {
	?>
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
			<tr>
				<th class="product-name"><?php esc_html_e( 'Product', 'flatsome' ); ?></th>
				<th class="product-packs"></th>
				<th class="product-quantity"><?php esc_html_e( 'Quantity', 'flatsome' ); ?></th>
				<th class="product-price"><?php esc_html_e( 'Price', 'flatsome' ); ?></th>
				<th class="product-discount"><?php esc_html_e( 'Discount', 'flatsome' ); ?></th>
				<th class="product-price-afterdisc"><?php esc_html_e( 'Price after discount', 'flatsome' ); ?></th>
				<th class="product-total-before-vat"><?php esc_html_e( 'Total before VAT', 'flatsome' ); ?></th>
				<th class="product-total-after-vat"><?php esc_html_e( 'Total after VAT', 'flatsome' ); ?></th>
				<th class="product-actions"></th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			$i=1;
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<td rowspan="2"  class="product-thumbnail">
						<?php
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

						if ( ! $product_permalink ) {
							echo $thumbnail; // PHPCS: XSS ok.
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
						}
						?>
						</td>

						<td rowspan="2"  class="product-name" data-title="<?php esc_attr_e( 'Product', 'flatsome' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
						}

						// Mobile price.
						?>
							<div class="show-for-small mobile-product-price">
								<span class="mobile-product-price__qty"><?php echo $cart_item['quantity']; ?> x </span>
								<?php
									//echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
								?>
							</div>
						</td>
						<?php /* <td class="product-packs" data-title="<?php esc_attr_e('Packs','flatsome'); ?>">
							<?php
							$product = wc_get_product($cart_item['product_id']);
							$id = $cart_item['product_id'];
							$packs = get_post_meta($id, 'packs', true);
							?>
							<div class="pack-wrapper">
								<?php
								
								do_action('show_packs_on_cart_page',$product);

								if (!$product->is_sold_individually() && $product->is_purchasable()) {
									
									$steps  = $packs[0]['PACKQUANT'];
									woocommerce_quantity_input(array('input_value' => $packs[0]['PACKQUANT'], 'min_value' => 0,'max_value'=> 0, 'step' => $steps), $_product);
									
								}else{
									sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								}
							?>
							</div>
						</td>*/ ?>
						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'flatsome' ); ?>">
						
						<?php
						/*if ( $_product->is_sold_individually() ) {
							$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
						} else {
							$product_quantity = woocommerce_quantity_input(
								array(
									'input_name'   => "cart[{$cart_item_key}][qty]",
									'input_value'  => $cart_item['quantity'],
									'max_value'    => $_product->get_max_purchase_quantity(),
									'min_value'    => '0',
									'product_name' => $_product->get_name(),
								),
								$_product,
								false
							);
						}*/
						echo '<label>'.$cart_item['quantity'].'</label>';
						//echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
						?>
						
						</td>
						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'flatsome' ); ?>">
							<?php
							$p_price = $cart_item['data']->get_price();  //wc_get_price_excluding_tax($product);
							
								echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$p_price.'</span>';
								//echo apply_filters( 'woocommerce_cart_item_price',  WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key); // PHPCS: XSS ok.
							?>
						</td>
						<td class="product-discount" data-title="<?php esc_attr_e( 'Discount', 'flatsome' ); ?>">
							<?php 
							$discount_price = number_format(($cart_item['line_total'] - $cart_item['line_subtotal']),'2','.','') ;
							
							echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$discount_price.'</span>';
							?>
						</td>
						<td class="product-priceafterdiscount" data-title="<?php esc_attr_e( 'Product price after Discount', 'flatsome' ); ?>">
							<?php 
							
							$p_price = $cart_item['data']->get_price();  //wc_get_price_excluding_tax($product);
							if($discount_price > 0) {

								$finalprice = number_format(($p_price - $discount_price),'2','.','') ;
							}else{
								$finalprice = number_format($discount_price,'2','.','') ;
							}
							echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$finalprice.'</span>';
							?>
						</td>
						

						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Total Before VAT', 'flatsome' ); ?>">
							<?php
								echo wc_price($cart_item['line_subtotal']);
								//echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>
						<td class="product-totalaftervat" data-title="<?php esc_attr_e( 'Total After VAT', 'flatsome' ); ?>">
							<?php

								$priceaftervat = number_format(($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']), '2', '.','');
								echo '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency_symbol()  . '</span>'.$priceaftervat.'</span>';
							?>
						</td>

						<td class="product-remove">
							<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="delete-icon" aria-label="%s" data-product_id="%s" data-product_sku="%s"><img src="'.get_stylesheet_directory_uri().'/images/trash.png"></a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										esc_html__( 'Remove this item', 'flatsome' ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
							?>
							
						</td>
						
					</tr>
					 <tr class="actions-cart-items">
						
						<td colspan="6" class="comment-line" data-title="<?php esc_attr_e('Comment line','flatsome'); ?>">
							<?php 
								$notes = isset( $cart_item['notes'] ) ? $cart_item['notes'] : '';
								echo '<span>'.esc_html($notes).'</span>';
							?>	
						</td>
					
						<td class="edit-action-wrap"><a href="javascript:void(0);" class="edit-icon-desktop" data-uid="<?php echo $i; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/edit.png"></a>

					
							<div class="show-packs edit_details" id="edit_details_<?php echo $i; ?>" data-title="<?php esc_attr_e('Show packs ','flatsome'); ?>" style="display:none;">
								<?php
								$product = wc_get_product($cart_item['product_id']);
								$id = $cart_item['product_id'];
								$packs = get_post_meta($id, 'packs', true);
								
								do_action('show_packs_on_cart_page',$_product);
								// echo '<input type="hidden" value="'.$cart_item['quantity'].'" name="qty123">';
								if (!$product->is_sold_individually() && $product->is_purchasable()) {
									$steps  = $packs[0]['PACKQUANT'];
									woocommerce_quantity_input(array('input_name'   => "cart[{$cart_item_key}][qty]",'input_value' => $cart_item['quantity'], 'min_value' => 0,'product_name' => $_product->get_name(),'step'=>$steps),$_product); 
								}else{
									sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								}

								$notes = isset( $cart_item['notes'] ) ? $cart_item['notes'] : '';
								
								printf(
								'<div><input placeholder="'.__("Add Comment","flatsome").'" type="text" class="%s" id="cart_notes_%s" data-cart-id="%s" value="%s" name="cart[%s][%s]"></div>','winn_cart_notes',
								$cart_item_key,
								$cart_item_key,
								$notes,
								$cart_item_key,
								'notes'
								); 

								
								?>
								<button type="submit" class="button primary mt-0 pull-left small" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'flatsome' ); ?>"><?php esc_html_e( 'Update cart', 'flatsome' ); ?></button>
								
							</div>
						</td>
					</tr>
					<?php
				}
				$i++;
			}
			
			?>
			<?php fl_woocommerce_version_check( '3.4.0' ) ? wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ) : wp_nonce_field( 'woocommerce-cart' ); ?>
					
			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<tr>
				<td colspan="10" class="actions clear">
					
					<?php //do_action( 'woocommerce_cart_actions' ); ?>
					
					
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>
	<?php 
	} //desktop over
	?>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</div>
</form>
</div>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals large-3 col pb-0">
	<?php if ( get_theme_mod( 'cart_sticky_sidebar' ) ) { ?>
	<div class="is-sticky-column">
		<div class="is-sticky-column__inner">
	<?php } ?>

	<div class="cart-sidebar col-inner <?php echo $sidebar_classes; ?>">
		<?php
			/**
			 * Cart collaterals hook.
			 *
			 * @hooked woocommerce_cross_sell_display
			 * @hooked woocommerce_cart_totals - 10
			 */
			do_action( 'woocommerce_cart_collaterals' );
		?>
		<?php /*if ( wc_coupons_enabled() ) { ?>
		<form class="checkout_coupon mb-0" method="post">
			<div class="coupon">
				<h3 class="widget-title"><?php echo get_flatsome_icon( 'icon-tag' ); ?> <?php esc_html_e( 'Coupon', 'flatsome' ); ?></h3><input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'flatsome' ); ?>" /> <input type="submit" class="is-form expand" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'flatsome' ); ?>" />
				<?php do_action( 'woocommerce_cart_coupon' ); ?>
			</div>
		</form>
		<?php }*/ ?>
		<?php do_action( 'flatsome_cart_sidebar' ); ?>
	</div>
<?php if ( get_theme_mod( 'cart_sticky_sidebar' ) ) { ?>
	</div>
	</div>
<?php } ?>
</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>

<!-- <script type="text/javascript" src="<?php echo P18AW_ASSET_URL . 'packs.js';?>"></script> -->
<script>
	jQuery(document).ready(function(){
		jQuery('body').on('click','.edit-icon',function(){
			jQuery(this).toggleClass('active');
			var uniqueid = jQuery(this).attr('data-uid');
			jQuery('#edit_details_'+uniqueid).toggle();
			//jQuery(this).parents('.cart_items_wrapper').siblings('.c_edit_detail').css('display','flex');
		});
		jQuery('body').on('click','.edit-icon-desktop',function(){
			jQuery(this).toggleClass('active');
			var uniqueid = jQuery(this).attr('data-uid');
			jQuery('#edit_details_'+uniqueid).toggle();
			
			//jQuery(this).parents('.cart_items_wrapper').siblings('.c_edit_detail').css('display','flex');
		});

		jQuery('body').on('click', '.quantity',function(){
		    jQuery(this).parent().find('.button').prop('disabled', false); 
		});
		
		jQuery('body').on('keyup blur', '.winn_cart_notes',function(){
		    jQuery(this).parent().parent().find('.button').prop('disabled', false); 
		});

		// jQuery('body').on('click','button[name="update_cart"]', function(){

		// });
	});
</script>