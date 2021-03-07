<?php

namespace ADP\BaseVersion\Includes\Admin;

use ADP\BaseVersion\Includes\Common\Database;
use ADP\BaseVersion\Includes\Context;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MetaBoxOrderAppliedDiscountRules {
	private static $rules = null;

	public static function init() {
		global $post;

		self::$rules = Database::get_applied_rules_for_order( $post->ID );

		if ( ! empty( self::$rules ) ) {
			add_meta_box( 'wdp-order-applied-rules', __( 'Applied discounts', 'advanced-dynamic-pricing-for-woocommerce' ), array('ADP\BaseVersion\Includes\Admin\MetaBoxOrderAppliedDiscountRules', 'output'), 'shop_order', 'side' );
		}
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		$context = new Context();
		$rules = self::$rules;

		?>
        <style> .wdp-aplied-rules, .wdp-aplied-rules td:first-child {
                width: 100%;
            } </style>
        <table class="wdp-aplied-rules">
			<?php foreach ( self::$rules as $row ):
				$amount = self::rule_amount($row);

				if ( $context->isHideRulesWithoutDiscountInOrderEditPage() && empty( $amount ) ) {
					continue;
				}

				?>
                <tr>
                    <td><a href="<?php echo self::rule_url( $row ); ?>"><?php echo $row->title; ?></a></td>
                    <td><?php
						echo empty( $amount ) ? '-' : wc_price( $amount );
						?>
                    </td>
                </tr>
			<?php endforeach; ?>
        </table>
		<?php
	}

	private static function rule_url( $row ) {
		return add_query_arg( array(
			'rule_id' => $row->id,
			'tab'     => 'rules',
		), admin_url( 'admin.php?page=wdp_settings' ) );
	}

	private static function rule_amount( $row ) {
		return floatval( $row->amount + $row->extra + $row->gifted_amount );
	}
}
