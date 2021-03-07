<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;
global $woocommerce;
?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2><?php esc_html_e( 'Cart totals', 'woocommerce' ); ?></h2>

	<table cellspacing="0" class="shop_table shop_table_responsive winn_shop_table_wrapper">
		<?php 
		$shippingtotal = WC()->cart->shipping_total;
		//$cart_total1 = number_format(WC()->cart->total + WC()->cart->tax_total,'2','.','');
        $cart_total = WC()->cart->subtotal;
        $cart_total_before_tax_array = array();
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_total_before_tax = 0;
			$cart_total_before_tax += $cart_item['data']->get_price() * $cart_item['quantity'];
			$cart_total_before_tax_array[] = $cart_total_before_tax;
        }
        $cart_total_before_tax = wc_price(array_sum($cart_total_before_tax_array));
		?>
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Total Before VAT', 'flatsome' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total Before VAT', 'flatsome' ); ?>">
			<?php echo $cart_total_before_tax; ?></td>
		</tr>
		<tr class="cart-subtotal-after-vat">
			<th><?php esc_html_e( 'Total After VAT', 'flatsome' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total After VAT', 'flatsome' ); ?>">
			<?php echo wc_price($cart_total); ?>
		</td>
		</tr>

		<?php /*foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach;*/ ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>

			<tr class="shipping">
				<th class="cart-left-title"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php woocommerce_shipping_calculator(); ?></td>
			</tr>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				// translators: %s location. 
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
					<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<?php /* <tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr> */ ?>
		<tr class="count-wrap">
			<th colspan="2" class="cart-left-title"><?php esc_html_e('Total count','flatsome'); ?></th>
			
		</tr>
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

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</table>

	

	<div class="cart-buttons row">
		<div class="continue-shopping large-6 col">
			<?php do_action( 'woocommerce_cart_actions' ); ?>
		</div>
		<div class="wc-proceed-to-checkout large-6 col">
			<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
		</div>
	</div>
	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
