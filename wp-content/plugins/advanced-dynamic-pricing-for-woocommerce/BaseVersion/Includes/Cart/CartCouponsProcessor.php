<?php

namespace ADP\BaseVersion\Includes\Cart;

use ADP\BaseVersion\Includes\Cart\Structures\Cart;
use ADP\BaseVersion\Includes\Cart\Structures\CartContext;
use ADP\BaseVersion\Includes\Cart\Structures\CouponInterface;
use ADP\BaseVersion\Includes\Cart\Structures\CouponCart;
use ADP\BaseVersion\Includes\Cart\Structures\Coupon;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\External\WC\PriceFunctions;
use ADP\BaseVersion\Includes\External\WC\WcCartItemFacade;
use ADP\BaseVersion\Includes\External\WC\WcCouponFacade;
use ADP\BaseVersion\Includes\External\WC\WcTotalsFacade;
use WC_Cart;
use WC_Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CartCouponsProcessor {
	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @var CouponInterface[][]
	 */
	protected $groupedCoupons;

	/**
	 * @var CouponInterface[]
	 */
	protected $singleCoupons;

	/**
	 * @var WcCouponFacade[]
	 */
	protected $readyCouponData;

	/**
	 * @var CartContext
	 */
	protected $cartContext;

	public function __construct( $context ) {
		$this->context = $context;
		$this->purge();
	}

	/**
	 * @param Cart $cart
	 */
	public function refreshCoupons( $cart ) {
		$context = $cart->get_context();
		$this->purge();

		foreach ( $cart->getCoupons() as $coupon ) {
			$coupon = clone $coupon;

			if ( empty( $coupon->getValue() ) || empty( $coupon->getCode() ) ) {
				continue;
			}

			if ( $coupon instanceof CouponCart ) {
				if ( $coupon->isType( $coupon::TYPE_FIXED_VALUE ) ) {
					if ( $context->is_combine_multiple_discounts() ) {
						$coupon->setCode( $context->get_option( 'default_discount_name' ) );
					}
					$this->addGroupCoupon( $coupon );
				} elseif ( $coupon->isType( $coupon::TYPE_PERCENTAGE ) ) {
					$this->addSingleCoupon( $coupon );
				}
			} elseif ( $coupon instanceof Coupon ) {
				if ( $context->is_combine_multiple_discounts() ) {
					$coupon->setCode( $context->get_option( 'default_discount_name' ) );
					$this->addGroupCoupon( $coupon );
				} else {
					$this->addGroupCoupon( $coupon );
				}
			}
		}

		// remove postfix for single %% discount
		if ( count( $this->singleCoupons ) == 1 ) {
			$coupon = reset( $this->singleCoupons );
			$coupon->setCode( str_replace( ' #1', '', $coupon->getCode() ) );
			$this->singleCoupons = array( $coupon->getCode() => $coupon );
		}

		$this->cartContext = $cart->get_context();
	}

	/**
	 * @param WC_Cart $wcCart
	 */
	public function applyCoupons( &$wcCart ) {
		$couponCodesToApply = array_merge( array_keys( $this->groupedCoupons ), array_keys( $this->singleCoupons ) );

		$appliedCoupons = $wcCart->applied_coupons;

		foreach ( $couponCodesToApply as $couponCode ) {
			if ( ! in_array( $couponCode, $appliedCoupons ) ) {
				$appliedCoupons[] = $couponCode;
			}
		}

		$wcCart->applied_coupons = $appliedCoupons;
		$this->prepareCouponsData( $wcCart );
	}

	/**
	 * @param WC_Cart $wcCart
	 */
	public function sanitize( &$wcCart ) {
		$appliedCoupons = $wcCart->applied_coupons;

		foreach ( $appliedCoupons as $index => $couponCode ) {
			if ( isset( $this->readyCouponData[ $couponCode ] ) ) {
				unset( $appliedCoupons[ $index ] );
			}
		}

		$wcCart->applied_coupons = array_values( $appliedCoupons );
		$this->purge();
	}

	public function setFilterToInstallCouponsData() {
		add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'getCouponData' ), 10, 3 );
	}

	public function unsetFilterToInstallCouponsData() {
		remove_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'getCouponData' ), 10 );
	}

	public function setFiltersToSupportPercentLimitCoupon() {
		add_filter( 'woocommerce_coupon_discount_types', array( $this, 'addPercentLimitCouponDiscountType' ), 10, 1 );
		add_filter( 'woocommerce_product_coupon_types', array( $this, 'addPercentLimitCouponProductType' ), 10, 1 );
		add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'getPercentLimitCouponDiscountAmount' ), 10, 5 );
		add_filter( 'woocommerce_coupon_custom_discounts_array', array( $this, 'processPercentLimitCoupon' ), 10, 2 );
	}

	public function setFilterToReplaceCouponMessageIfDisableCoupons() {
		$context = $this->context;
		if ( $context->get_option( 'external_coupons_behavior' ) === 'disable_if_any_rule_applied'
		     || $context->get_option( 'external_coupons_behavior' ) === 'disable_if_any_of_cart_items_updated'
		) {
			add_filter( 'woocommerce_coupon_message', array( $this, 'replaceCouponMessageIfDisableExternalCoupons' ), 10, 3 );
		}
	}

	public function unsetFiltersToSupportPercentLimitCoupon() {
		remove_filter( 'woocommerce_coupon_discount_types', array( $this, 'addPercentLimitCouponDiscountType'), 10 );
		remove_filter( 'woocommerce_product_coupon_types', array( $this, 'addPercentLimitCouponProductType' ), 10 );
		remove_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'getPercentLimitCouponDiscountAmount' ), 10 );
	}

	public function setFiltersToSupportExactItemApplicationOfReplacementCoupon() {
		if ( ! $this->context->isAllowExactApplicationOfReplacementCoupon() ) {
			return;
		}

		add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'getExactItemApplicationCouponDiscountAmount' ), 10, 5 );
		add_filter( 'woocommerce_cart_coupon_types', array( $this, 'addExactItemApplicationCouponCartType' ), 10, 1 );
		add_filter( 'woocommerce_coupon_discount_types', array( $this, 'addExactItemApplicationCouponDiscountType' ), 10, 1 );
	}

	public function unsetFiltersToSupportExactItemApplicationOfReplacementCoupon() {
		remove_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'getExactItemApplicationCouponDiscountAmount'), 10 );
		remove_filter( 'woocommerce_cart_coupon_types', array( $this, 'addExactItemApplicationCouponCartType' ), 10 );
		remove_filter( 'woocommerce_coupon_discount_types', array( $this, 'addExactItemApplicationCouponDiscountType' ), 10 );
	}

	public function unsetFilterToReplaceCouponMessageIfDisableCoupons() {
		remove_filter( 'woocommerce_coupon_message', array( $this, 'replaceCouponMessageIfDisableExternalCoupons' ), 10 );
	}

	/**
	 * This filter allows custom coupon objects to be created on the fly.
	 *
	 * @param false     $couponData
	 * @param mixed     $couponCode Coupon code
	 * @param WC_Coupon $wcCoupon
	 *
	 * @return array|mixed
	 */
	public function getCouponData( $couponData, $couponCode, $wcCoupon ) {
		if ( isset( $this->readyCouponData[ $couponCode ] ) ) {
			$readyCouponFacade = $this->readyCouponData[ $couponCode ];
			$parts             = $readyCouponFacade->getParts();

			$wcCouponFacade = new WcCouponFacade( $this->context, $wcCoupon );
			$wcCouponFacade->setParts( $parts );
			$wcCouponFacade->updateCoupon();


			$couponData = array(
				'discount_type' => $readyCouponFacade->coupon->get_discount_type( 'edit' ),
				'amount'        => $readyCouponFacade->coupon->get_amount( 'edit' ),
			);

			//support max discount for percentage coupon
			if ( $coupon = reset( $parts ) ) {
				/** @var CouponCart $coupon */
				if ( $couponData['discount_type'] === WcCouponFacade::TYPE_PERCENT && $coupon->isMaxDiscountDefined() ) {
					$couponData['discount_type'] = WcCouponFacade::TYPE_CUSTOM_PERCENT_WITH_LIMIT;
				}
			}
		}

		return $couponData;
	}

	public function addPercentLimitCouponDiscountType( $discount_types ) {
		$discount_types[ WcCouponFacade::TYPE_CUSTOM_PERCENT_WITH_LIMIT ] = __( 'WDP Coupon',
			'advanced-dynamic-pricing-for-woocommerce' );

		return $discount_types;
	}

	public function addPercentLimitCouponProductType( $discount_types ) {
		$discount_types[] = WcCouponFacade::TYPE_CUSTOM_PERCENT_WITH_LIMIT;

		return $discount_types;
	}

	public function addExactItemApplicationCouponDiscountType( $discountTypes ) {
		$discountTypes[ WcCouponFacade::TYPE_ADP_FIXED_CART_ITEM ] = __( 'WDP Coupon (exact item application)',
			'advanced-dynamic-pricing-for-woocommerce' );

		return $discountTypes;
	}

	public function addExactItemApplicationCouponCartType( $discountTypes ) {
		$discountTypes[] = WcCouponFacade::TYPE_ADP_FIXED_CART_ITEM;

		return $discountTypes;
	}

	/**
	 * @param float     $discountAmount
	 * @param float     $discountingAmount
	 * @param array     $cartItem
	 * @param bool      $single
	 * @param WC_Coupon $coupon
	 *
	 * @return float|int
	 */
	public function getPercentLimitCouponDiscountAmount(
		$discountAmount,
		$discountingAmount,
		$cartItem,
		$single,
		$coupon
	) {
		if ( $coupon->get_discount_type() === WcCouponFacade::TYPE_CUSTOM_PERCENT_WITH_LIMIT ) {
			$discountAmount = (float) $coupon->get_amount() * ( $discountingAmount / 100 );
		}

		return $discountAmount;
	}

	/**
	 * @param float[]
	 * @param \WC_Coupon $coupon
	 *
	 * @return float[]
	 */
	public function processPercentLimitCoupon( $couponDiscounts, $coupon ) {
		if ( $coupon->get_discount_type() === WcCouponFacade::TYPE_CUSTOM_PERCENT_WITH_LIMIT ) {
			$coupon_code    = $coupon->get_code();
			$wdpCoupon      = $this->singleCoupons[ $coupon_code ];
			$discountAmount = array_sum( $couponDiscounts );

			$maxDiscount = $wdpCoupon->getMaxDiscount() * pow( 10, wc_get_price_decimals() );
			if ( $discountAmount > $maxDiscount ) {
				$itemDiscount = round( (float) $maxDiscount / count( $couponDiscounts ) );
				$k            = 0;
				foreach ( $couponDiscounts as $key => $discount ) {
					if ( $k >= count( $couponDiscounts ) - 1 ) {
						$couponDiscounts[ $key ] = $maxDiscount - $itemDiscount * $k;
						break;
					}
					$couponDiscounts[ $key ] = $itemDiscount;
					$k ++;
				}
			}
		}

		return $couponDiscounts;
	}

	/**
	 * @param WC_Cart $wcCart
	 */
	public function updateTotals( $wcCart ) {
		$globalContext = $this->cartContext->getGlobalContext();
		$totalsWrapper = new WcTotalsFacade( $globalContext, $wcCart );
		$totalsWrapper->insertCouponsData( $this->groupedCoupons, $this->singleCoupons );
	}

	public function getExactItemApplicationCouponDiscountAmount( $amount, $discountingAmount, $cartItem, $single, $wcCoupon ) {
		$facade       = new WcCartItemFacade( $this->context, $cartItem );
		$couponFacade = new WcCouponFacade( $this->context, $wcCoupon );

		/** @var Coupon[] $coupons */
		$coupons = array_filter( $couponFacade->getParts(), function ( $coupon ) {
			return $coupon instanceof Coupon;
		} );

		if ( count( $coupons ) === 0 ) {
			return $amount;
		}

		$newAmount = floatval(0);
		foreach ( $coupons as $coupon ) {
			if ( $coupon->getAffectedCartItemKey() === $facade->getKey() || $coupon->getAffectedCartItemKey() === $facade->getOriginalKey() ) {
				$newAmount += $coupon->getValue();
			}
		}

		if ( $newAmount > 0 ) {
			$priceFunctions = new PriceFunctions( $this->context );
			$args           = array( 'price' => $newAmount );
			if ( $this->context->get_is_prices_include_tax() ) {
				$newAmount = $priceFunctions->getPriceIncludingTax( $facade->getProduct(), $args );
			} else {
				$newAmount = $priceFunctions->getPriceExcludingTax( $facade->getProduct(), $args );
			}

			$newAmount = round( $newAmount, wc_get_rounding_precision() );
		}

		return $newAmount;
	}

	/**
	 * @param string     $msg
	 * @param string     $msgCode
	 * @param \WC_Coupon $coupon
	 *
	 * @return string
	 */
	public function replaceCouponMessageIfDisableExternalCoupons( $msg, $msgCode, $coupon ) {
		if ( $coupon->get_discount_type( 'edit' ) === WcCouponFacade::TYPE_ADP_RULE_TRIGGER ) {
			return $msg;
		}

		if ( $msgCode === $coupon::WC_COUPON_SUCCESS ) {
			wc_add_notice( __( 'Sorry, coupons are disabled for these products.',
				'advanced-dynamic-pricing-for-woocommerce' ), 'error' );

			return "";
		}

		return $msg;
	}

	/**
	 * @param WC_Cart $wcCart
	 */
	protected function prepareCouponsData( $wcCart ) {
		foreach ( $this->groupedCoupons as $couponCode => $coupons ) {
			$coupon_type = WcCouponFacade::TYPE_FIXED_CART;
			$amount      = floatval( 0 );

			$appliedCoupons = array();
			foreach ( $coupons as $coupon ) {
				if ( $coupon instanceof CouponCart ) {
					if ( $coupon->isType( $coupon::TYPE_FIXED_VALUE ) ) {
						$amount           += $coupon->getValue();
						$appliedCoupons[] = $coupon;
					}
				} elseif ( $coupon instanceof Coupon ) {
					$amount           += $coupon->getValue() * $coupon->getAffectedCartItemQty();
					if ( $this->context->isAllowExactApplicationOfReplacementCoupon() ) {
						$coupon_type      = WcCouponFacade::TYPE_ADP_FIXED_CART_ITEM;
					}
					$appliedCoupons[] = $coupon;
				}
			}

			if ( $amount > 0 ) {
				$this->addReadyCouponData( $couponCode, $coupon_type, $amount, $appliedCoupons );
			}
		}

		foreach ( $this->singleCoupons as $coupon ) {
			$coupon_type = WcCouponFacade::TYPE_FIXED_CART;

			if ( $coupon instanceof CouponCart ) {
				if ( $coupon->isType( $coupon::TYPE_PERCENTAGE ) ) {
					$coupon_type = WcCouponFacade::TYPE_PERCENT;
				}
			} elseif ( $coupon instanceof Coupon ) {
				$coupon_type = WcCouponFacade::TYPE_FIXED_PRODUCT;
			}

			$this->addReadyCouponData( $coupon->getCode(), $coupon_type, $coupon->getValue(), array( $coupon ) );
		}
	}

	protected function addReadyCouponData( $code, $type, $amount, $parts ) {
		if ( isset( $this->readyCouponData[ $code ] ) ) {
			return;
		}

		$couponFacade = new WcCouponFacade( $this->context, new \WC_Coupon() );
		$couponFacade->coupon->set_virtual( true );
		$couponFacade->coupon->set_code( $code );
		$couponFacade->coupon->set_discount_type( $type );
		$couponFacade->coupon->set_amount( $amount );
		$couponFacade->setParts( $parts );

		$this->readyCouponData[ $code ] = $couponFacade;
	}

	/**
	 * @param CouponInterface $coupon
	 */
	protected function addGroupCoupon( $coupon ) {
		if ( ! isset( $this->groupedCoupons[ $coupon->getCode() ] ) ) {
			$this->groupedCoupons[ $coupon->getCode() ] = array();
		}

		$this->groupedCoupons[ $coupon->getCode() ][] = $coupon;
	}

	/**
	 * @param CouponInterface $coupon
	 */
	protected function addSingleCoupon( $coupon ) {
		if ( ! isset( $this->singleCoupons[ $coupon->getCode() ] ) ) {
			$this->singleCoupons[ $coupon->getCode() ] = $coupon;

			return;
		}

		// add "#1" to the end of the coupon label once
		$firstCoupon = $this->singleCoupons[ $coupon->getCode() ];
		if ( strpos( $firstCoupon->getLabel(), "#1" ) === false ) {
			$firstCoupon->setLabel( sprintf( "%s #%s", $coupon->getCode(), 1 ) );
		}

		$count = 1;
		do {
			$couponCode  = sprintf( "%s_%s", $coupon->getCode(), $count );
			$couponLabel = sprintf( "%s #%s", $coupon->getCode(), $count + 1 );
			$count ++;
		} while ( isset( $this->singleCoupons[ $couponCode ] ) );

		$coupon->setCode( $couponCode );
		$coupon->setLabel( $couponLabel );
		$this->singleCoupons[ $coupon->getCode() ] = $coupon;
	}

	protected function purge() {
		$this->groupedCoupons  = array();
		$this->singleCoupons   = array();
		$this->readyCouponData = array();
	}
}
