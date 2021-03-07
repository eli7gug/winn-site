<?php

namespace ADP\BaseVersion\Includes\Cart;

use ADP\BaseVersion\Includes\Cart\Structures\FreeCartItemChoices;
use ADP\BaseVersion\Includes\Enums\GiftChoiceMethodEnum;
use ADP\BaseVersion\Includes\Enums\GiftChoiceTypeEnum;
use ADP\BaseVersion\Includes\External\CacheHelper;
use ADP\BaseVersion\Includes\Rule\ProductStock\ProductStockController;

class FreeCartItemChoicesSuitability {
	/**
	 * @param FreeCartItemChoices    $cartItemChoices
	 *
	 * @return int[]
	 */
	protected function getMatchedProductsIds($cartItemChoices) {
		$includeIds = array();

		foreach ( $cartItemChoices->getChoices() as $choice ) {
			if ( $choice->getType()->equals( GiftChoiceTypeEnum::PRODUCT() ) ) {
				if ( $choice->getMethod()->equals( GiftChoiceMethodEnum::IN_LIST() ) ) {
					$includeIds = array_merge( $includeIds, $choice->getValues() );
				}
			}
		}

		return $includeIds;
	}

	/**
	 * @param FreeCartItemChoices    $cartItemChoices
	 * @param ProductStockController $ruleUsedStock
	 *
	 * @return array
	 */
	public function getProductsSuitableToGift( $cartItemChoices, $ruleUsedStock ) {
		$result = array();

		$productIds = $this->getMatchedProductsIds( $cartItemChoices );

		$giftQty = $cartItemChoices->getRequiredQty();

		foreach ( $productIds as $productId ) {
			$product  = CacheHelper::getWcProduct( $productId );
			if( !$product ) // skip deleted products
				continue;

			$qtyToAdd = $ruleUsedStock->getQtyAvailableForSale( $product->get_id(), $giftQty, $product->get_parent_id() );

			$result[] = array( $productId, $qtyToAdd );
			$giftQty  -= $qtyToAdd;

			if ( $giftQty <= 0 ) {
				break;
			}
		}

		return $result;
	}

	/**
	 * @param FreeCartItemChoices $cartItemChoices
	 * @param array               $queryArgs
	 *
	 * @return array
	 */
	public function getMatchedProductsGlobalQueryArgs( $cartItemChoices, $queryArgs ) {
		$includeIds = array();
		$excludeIds = array();

		foreach ( $cartItemChoices->getChoices() as $choice ) {
			if ( $choice->getType()->equals( GiftChoiceTypeEnum::PRODUCT() ) ) {
				if ( $choice->getMethod()->equals( GiftChoiceMethodEnum::IN_LIST() ) ) {
					$includeIds = array_merge( $includeIds, $choice->getValues() );
				} elseif ( $choice->getMethod()->equals( GiftChoiceMethodEnum::NOT_IN_LIST() ) ) {
					$excludeIds = array_merge( $excludeIds, $choice->getValues() );
				}
			}
		}

		$queryArgs['include'] = $includeIds;

		return $queryArgs;
	}

	/**
	 * @param FreeCartItemChoices $cartItemChoices
	 * @param \WC_Product         $product
	 *
	 * @return bool
	 */
	public function isProductMatched( $cartItemChoices, $product ) {
		if ( count( $cartItemChoices->getChoices() ) === 0 ) {
			return false;
		}

		if ( $product instanceof \WC_Product_Grouped) {
			return false;
		}

		$result = true;
		foreach ( $cartItemChoices->getChoices() as $choice ) {
			$choiceMatch = false;

			if ( $choice->getType()->equals( GiftChoiceTypeEnum::PRODUCT() ) ) {
				if ( $choice->getMethod()->equals( GiftChoiceMethodEnum::IN_LIST() ) ) {
					$choiceMatch = in_array( $product->get_id(), $choice->getValues(), true );
				}
			}

			$result &= $choiceMatch;
		}

		return $result;
	}
}
