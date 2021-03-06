<?php

namespace ADP\BaseVersion\Includes\External\WC;

use ADP\BaseVersion\Includes\Cart\Structures\CouponInterface;
use ADP\BaseVersion\Includes\Cart\Structures\CouponCart;
use ADP\BaseVersion\Includes\Cart\Structures\Coupon;
use ADP\BaseVersion\Includes\Cart\Structures\Fee;
use ADP\BaseVersion\Includes\Cart\Structures\ShippingAdjustment;
use ADP\BaseVersion\Includes\Context;
use WC_Cart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WcTotalsFacade {
	const KEY_TOTALS_ADP = 'adp';

	const KEY_FEE = 'fee';
	const KEY_COUPONS = 'coupons';
	const KEY_SHIPPING = 'shipping';
	const KEY_INITIAL_TOTALS = 'initial_totals';
	const KEY_CURRENCY = 'currency';

	/**
	 * @var WC_Cart
	 */
	protected $wcCart;

	/**
	 * @var Context
	 */
	protected $context;

	public function __construct( $context, $wcCart ) {
		$this->context = $context;
		$this->wcCart  = $wcCart;
	}

	/**
	 * @param Fee[] $fees
	 */
	public function insertFeesData( $fees ) {
		$listOfFees = array();
		foreach ( $fees as $fee ) {
			$listOfFees[] = array(
				'name'     => $fee->getName(),
				'type'     => $fee->getType(),
				'value'    => $fee->getValue(),
				'amount'   => $fee->getAmount(),
				'taxable'  => $fee->isTaxAble(),
				'taxClass' => $fee->getTaxClass(),
				'ruleId'   => $fee->getRuleId(),
			);
		}

		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ] ) ) {
			$totals[ self::KEY_TOTALS_ADP ] = array();
		}
		$totals[ self::KEY_TOTALS_ADP ][ self::KEY_FEE ] = $listOfFees;
		$this->wcCart->set_totals( $totals );
	}

	/**
	 * @return Fee[]
	 */
	public function getFees() {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ], $totals[ self::KEY_TOTALS_ADP ][ self::KEY_FEE ] ) ) {
			return array();
		}

		$fees = array();
		foreach ( $totals[ self::KEY_TOTALS_ADP ][ self::KEY_FEE ] as $feeData ) {
			$fee = new Fee( $this->context, $feeData['type'], $feeData['name'], $feeData['value'], $feeData['taxClass'],
				$feeData['ruleId'] );
			$fee->setAmount( $feeData['amount'] );
			$fees[] = $fee;
		}

		return $fees;
	}

	/**
	 * @param CouponInterface[][] $groupedCoupons
	 * @param CouponInterface[]   $singleCoupons
	 */
	public function insertCouponsData( $groupedCoupons, $singleCoupons ) {
		$groupCouponsData  = array();
		$singleCouponsData = array();

		foreach ( $groupedCoupons as $couponCode => $coupons ) {
			$groupCouponsData[ $couponCode ] = array();

			foreach ( $coupons as $coupon ) {
				if ( $couponData = $this->getCouponData( $coupon ) ) {
					$groupCouponsData[ $couponCode ][] = $couponData;
				}
			}
		}

		foreach ( $singleCoupons as $coupon ) {
			if ( $couponData = $this->getCouponData( $coupon ) ) {
				$singleCouponsData[ $coupon->getCode() ] = $this->getCouponData( $coupon );
			}
		}

		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ] ) ) {
			$totals[ self::KEY_TOTALS_ADP ] = array();
		}
		$totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] = array(
			'group'  => $groupCouponsData,
			'single' => $singleCouponsData,
		);
		$this->wcCart->set_totals( $totals );
	}

	/**
	 * @param CouponInterface $coupon
	 *
	 * @return array
	 */
	private function getCouponData( $coupon ) {
		if ( $coupon instanceof CouponCart ) {
			return array(
				'type'   => $coupon->getType(),
				'code'   => $coupon->getCode(),
				'value'  => $coupon->getValue(),
				'ruleId' => $coupon->getRuleId(),
			);
		} elseif ( $coupon instanceof Coupon ) {
			return array(
				'type'              => $coupon->getType(),
				'code'              => $coupon->getCode(),
				'value'             => $coupon->getValue(),
				'ruleId'            => $coupon->getRuleId(),
				'affectedCartItem'  => $coupon->getAffectedCartItemKey(),
				'affectedQty'       => $coupon->getAffectedCartItemQty(),
			);
		}

		return array();
	}

	/**
	 * @param array $data
	 *
	 * @return CouponInterface|null
	 */
	private function getCouponFromData( $data ) {
		if ( ! $data ) {
			return null;
		}

		$type = $data['type'];

		if ( in_array( $type, CouponCart::AVAILABLE_TYPES ) ) {
			$coupon = new CouponCart( $this->context, $type, $data['code'], $data['value'], $data['ruleId'] );
		} else {
			if ( isset($this->wcCart->cart_contents[ $data['affectedCartItem'] ]) ) {
				$affectedCartItem = new WcCartItemFacade( $this->context, $this->wcCart->cart_contents[ $data['affectedCartItem'] ], $data['affectedCartItem'] );
				$affectedCartItem->setQty( $data['affectedQty'] );
			} else {
				$affectedCartItem = null;
			}

			$coupon = new Coupon( $this->context, $type, $data['code'], $data['value'], $data['ruleId'], $affectedCartItem );
		}

		return $coupon;
	}

	/**
	 * @return CouponInterface[][]
	 */
	public function getGroupedCoupons() {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ], $totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] ) ) {
			return array();
		}

		$groupedCoupons = array();
		foreach ( $totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] as $key => $data ) {
			if ( $key === 'group' ) {
				foreach ( $data as $code => $coupons ) {
					$groupedCoupons[ $code ] = array();

					foreach ( $coupons as $couponData ) {
						if ( $coupon = $this->getCouponFromData( $couponData ) ) {
							$groupedCoupons[ $code ][] = $coupon;
						}
					}
				}
			}
		}

		return $groupedCoupons;
	}

	/**
	 * @return CouponInterface[]
	 */
	public function getSingleCoupons() {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ], $totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] ) ) {
			return array();
		}

		$singleCoupons = array();
		foreach ( $totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] as $key => $data ) {
			if ( $key === 'single' ) {
				foreach ( $data as $code => $couponData ) {
					if ( $coupon = $this->getCouponFromData( $couponData ) ) {
						$singleCoupons[ $code ] = $coupon;
					}
				}
			}
		}

		return $singleCoupons;
	}

	public function getAdpCoupons() {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ], $totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] ) ) {
			return array();
		}

		return array_keys( $totals[ self::KEY_TOTALS_ADP ][ self::KEY_COUPONS ] );
	}

	/**
	 * @param ShippingAdjustment[] $adjustments
	 */
	public function insertShippingData( $adjustments ) {
		$adjustmentData = array();

		foreach ( $adjustments as $adjustment ) {
			$adjustmentData[] = array(
				'type'   => $adjustment->getType(),
				'value'  => $adjustment->getValue(),
				'ruleId' => $adjustment->getRuleId(),
				'amount' => $adjustment->getAmount(),
			);
		}

		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ] ) ) {
			$totals[ self::KEY_TOTALS_ADP ] = array();
		}
		$totals[ self::KEY_TOTALS_ADP ][ self::KEY_SHIPPING ] = $adjustmentData;
		$this->wcCart->set_totals( $totals );
	}

	/**
	 * @return ShippingAdjustment[]
	 */
	public function getShippingAdjustments() {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ], $totals[ self::KEY_TOTALS_ADP ][ self::KEY_SHIPPING ] ) ) {
			return array();
		}

		$adjustments = array();
		foreach ( $totals[ self::KEY_TOTALS_ADP ][ self::KEY_SHIPPING ] as $key => $adjustmentData ) {
			$adj = new ShippingAdjustment( $this->context, $adjustmentData['type'], $adjustmentData['value'],
				$adjustmentData['ruleId'] );
			$adj->setAmount( $adjustmentData['amount'] );
			$adjustments[] = $adj;
		}

		return $adjustments;
	}

	/**
	 * @param array $initialTotals
	 */
	public function insertInitialTotals( $initialTotals ) {
		unset( $initialTotals[ self::KEY_TOTALS_ADP ] );
		$totals = $this->wcCart->get_totals();

		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ] ) ) {
			$totals[ self::KEY_TOTALS_ADP ] = array();
		}
		$totals[ self::KEY_TOTALS_ADP ][ self::KEY_INITIAL_TOTALS ] = $initialTotals;
		$this->wcCart->set_totals( $totals );
	}

	/**
	 * @return array
	 */
	public function getInitialTotals() {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ], $totals[ self::KEY_TOTALS_ADP ][ self::KEY_INITIAL_TOTALS ] ) ) {
			return array();
		}

		return $totals[ self::KEY_TOTALS_ADP ][ self::KEY_INITIAL_TOTALS ];
	}

	public function insertCurrency( $currencyCode ) {
		$totals = $this->wcCart->get_totals();
		if ( ! isset( $totals[ self::KEY_TOTALS_ADP ] ) ) {
			$totals[ self::KEY_TOTALS_ADP ] = array();
		}
		$totals[ self::KEY_TOTALS_ADP ][ self::KEY_CURRENCY ] = $currencyCode;
		$this->wcCart->set_totals( $totals );
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		$totals = $this->wcCart->get_totals();

		if ( isset( $totals[ self::KEY_TOTALS_ADP ][ self::KEY_CURRENCY ] ) ) {
			return $totals[ self::KEY_TOTALS_ADP ][ self::KEY_CURRENCY ];
		}

		return $this->context->getCurrencyCode();
	}
}
