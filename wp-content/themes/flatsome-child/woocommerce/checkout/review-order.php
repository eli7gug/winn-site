<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table">

	<tr class="lines-in-order">
		<th><?php esc_html_e('Total number of lines in order','flatsome'); ?></th>
		<td data-title="<?php esc_attr_e('Total number of lines in order','flatsome'); ?>">
			<?php 
			$i=0;
			$quantity = 0;
			foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item){
				if(!empty($cart_item_key)){
					$i++;
					$quantity+= $cart_item['quantity'];
				}
			} //($woocommerce->cart->get_cart_total() );
			echo $i; ?></td>
	</tr>
	<tr class="items-in-order">
		<th><?php esc_html_e('Total number of items in order','flatsome'); ?></th>
		<td data-title="<?php esc_attr_e('Total number of items in order','flatsome'); ?>"><?php echo $quantity; ?></td>
	</tr>
	<tr>
		<th class="product-total"><?php esc_html_e( 'Cart totals', 'woocommerce' ); ?></th>
	</tr>
    <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
			<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
			<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
		</tr>
	<?php endforeach; ?>
    <?php 

    $cart_total = WC()->cart->subtotal;
	$cart_total_before_tax_array = array();
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$cart_total_before_tax = 0;
		$cart_total_before_tax += $cart_item['data']->get_price() * $cart_item['quantity'];
		$cart_total_before_tax_array[] = $cart_total_before_tax;
	}
	$cart_total_before_tax = wc_price(array_sum($cart_total_before_tax_array));
    ?>
    <tr class="cart-subtotal-before-vat">
        <th><?php esc_html_e( 'Total Before VAT', 'flatsome' ); ?></th>
        <td data-title="<?php esc_attr_e( 'Total Before VAT', 'flatsome' ); ?>">
        <?php echo $cart_total_before_tax; ?></td>
    </tr>
    	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<tr class="fee">
			<th><?php echo esc_html( $fee->name ); ?></th>
			<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
		</tr>
	<?php endforeach; ?>
	<tr class="cart-vat">
        <th><?php esc_html_e( 'VAT', 'flatsome' ); ?></th>
        <td data-title="<?php esc_attr_e( 'VAT', 'flatsome' ); ?>">
        <?php echo  wc_price(WC()->cart->get_taxes_total()); ?></td>
    </tr>
    <?php /* if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

        <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

        <?php wc_cart_totals_shipping_html(); ?>

        <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

    <?php endif;*/ ?>



	<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
		<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
			<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
				<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<th><?php echo esc_html( $tax->label ); ?></th>
					<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr class="tax-total">
				<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
				<td><?php wc_cart_totals_taxes_total_html(); ?></td>
			</tr>
		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<tr class="order-total">
		<th><?php esc_html_e( 'Total After VAT', 'flatsome' ); ?></th>
		<td><?php wc_cart_totals_order_total_html(); ?></td>
	</tr>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>


</table>
