<?php

namespace ADP\BaseVersion\Includes\Rule\Structures;

use ADP\BaseVersion\Includes\Enums\Exceptions\UnexpectedValueException;
use ADP\BaseVersion\Includes\Enums\GiftModeEnum;
use ADP\BaseVersion\Includes\Structures\GiftChoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Gift {
	/**
	 * @var GiftChoice[]
	 */
	protected $choices;

	/**
	 * @var float
	 */
	protected $qty;

	/**
	 * @var GiftModeEnum
	 */
	protected $mode;

	public function __construct() {
		$this->choices       = array();
		$this->qty           = floatval( 0 );
		$this->mode          = new GiftModeEnum();
	}

	/**
	 * @param GiftChoice[] $choices
	 *
	 * @return self
	 */
	public function setChoices( $choices ) {
		$this->choices = array_filter(
			$choices,
			function ( $choice ) {
				return $choice instanceof GiftChoice && $choice->isValid();
			}
		);

		return $this;
	}

	/**
	 * @return GiftChoice[]
	 */
	public function getChoices() {
		return $this->choices;
	}

	/**
	 * @param float $qty
	 *
	 * @return self
	 */
	public function setQty( $qty ) {
		if ( is_numeric( $qty ) ) {
			$qty = floatval( $qty );
			if ( $qty >= 0 ) {
				$this->qty = floatval( $qty );
			}
		}

		return $this;
	}

	/**
	 * @return float
	 */
	public function getQty() {
		return $this->qty;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		if ( ! isset( $this->choices, $this->qty ) ) {
			return false;
		}

		return $this->qty > floatval( 0 );
	}

	/**
	 * @return false
	 */
	public function isAllowToSelect() {
		return $this->mode->equals( GiftModeEnum::ALLOW_TO_CHOOSE() ) || $this->mode->equals( GiftModeEnum::ALLOW_TO_CHOOSE_FROM_PRODUCT_CAT() );
	}

	/**
	 * @param GiftModeEnum $mode
	 */
	public function setMode( $mode ) {
		if ( $mode instanceof GiftModeEnum ) {
			$this->mode = $mode;
		}
	}

	/**
	 * @return GiftModeEnum
	 */
	public function getMode() {
		return $this->mode;
	}

	/**
	 * @return array
	 */
	public function __serialize() {
		return array(
			'choices' => $this->choices,
			'qty'     => $this->qty,
			'mode'    => $this->mode->getValue(),
		);
	}

	/**
	 * @param array $data
	 */
	public function __unserialize( $data ) {
		$this->choices = $data['choices'];
		$this->qty     = $data['qty'];

		try {
			$this->mode = new GiftModeEnum( $data['allowToSelect'] );
		} catch ( UnexpectedValueException $e ) {
			$this->mode = new GiftModeEnum();
		} catch ( \ReflectionException $e ) {
			$this->mode = new GiftModeEnum();
		}
	}
}
