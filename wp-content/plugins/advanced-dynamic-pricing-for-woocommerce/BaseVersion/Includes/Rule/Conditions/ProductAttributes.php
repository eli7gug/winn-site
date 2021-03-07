<?php

namespace ADP\BaseVersion\Includes\Rule\Conditions;

use ADP\BaseVersion\Includes\Rule\ConditionsLoader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ProductAttributes extends AbstractConditionCartItems {
	protected $filter_type = 'product_attributes';

	public static function getType() {
		return 'product_attributes';
	}

	public static function getLabel() {
		return __( 'Product attributes', 'advanced-dynamic-pricing-for-woocommerce' );
	}

	public static function getTemplatePath() {
		return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/products/product-attributes.php';
	}

	public static function getGroup() {
		return ConditionsLoader::GROUP_CART_ITEMS;
	}
}