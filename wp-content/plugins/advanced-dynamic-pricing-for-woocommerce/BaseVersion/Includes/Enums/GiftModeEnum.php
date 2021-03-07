<?php

namespace ADP\BaseVersion\Includes\Enums;


/**
 * @method static self USE_PRODUCT_PROM_FILTER()
 * @method static self GIFTABLE_PRODUCTS()
 * @method static self ALLOW_TO_CHOOSE()
 * @method static self ALLOW_TO_CHOOSE_FROM_PRODUCT_CAT()
 */
class GiftModeEnum extends BaseEnum {
	const __default = self::USE_PRODUCT_PROM_FILTER;

	const USE_PRODUCT_PROM_FILTER = 'use_product_from_filter';
	const GIFTABLE_PRODUCTS = 'giftable_products';
	const ALLOW_TO_CHOOSE = 'allow_to_choose';
	const ALLOW_TO_CHOOSE_FROM_PRODUCT_CAT = 'allow_to_choose_from_product_cat';

	/**
	 * @param self $variable
	 *
	 * @return bool
	 */
	public function equals( $variable ) {
		return parent::equals( $variable );
	}
}
