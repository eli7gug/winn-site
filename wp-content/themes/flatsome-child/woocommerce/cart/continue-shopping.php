<?php
/**
 * Continue Shopping Button
 *
 * @package Flatsome/WooCommerce/Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="continue-shopping pull-left text-left">
	<a class="button-continue-shopping button primary is-outline"  href="<?php echo esc_url( apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
		<?php echo esc_html__( 'Continue shopping', 'woocommerce' ); ?><!-- ( is_rtl() ? '&#8594;' : '&#8592;' ) . '&nbsp;' . -->
	</a>
</div>
