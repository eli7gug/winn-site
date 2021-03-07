<?php

namespace ADP\BaseVersion\Includes\Cart\Structures;

use ADP\BaseVersion\Includes\Rule\Interfaces\Rule;
use ADP\BaseVersion\Includes\Structures\GiftChoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class FreeCartItemChoices {
	/**
	 * @var GiftChoice[]
	 */
	protected $choices;

	/**
	 * @var float
	 */
	protected $requiredQty;

	public function __construct() {
		$this->choices     = array();
		$this->requiredQty = floatval( 0 );
	}

	public function __clone() {
		$newChoices = array();
		foreach ( $this->choices as $newChoice ) {
			$newChoices[] = clone $newChoice;
		}
		$this->choices = $newChoices;
	}

	/**
	 * @param GiftChoice[] $choices
	 */
	public function setChoices( $choices ) {
		if ( ! is_array( $choices ) ) {
			return;
		}

		$this->choices = array();
		foreach ( $choices as $choice ) {
			if ( $choice instanceof GiftChoice ) {
				$this->choices[] = $choice;
			}
		}
	}

	/**
	 * @return GiftChoice[]
	 */
	public function getChoices() {
		return $this->choices;
	}


	/**
	 * @param float $requiredQty
	 */
	public function setRequiredQty( $requiredQty ) {
		if ( is_numeric( $requiredQty ) ) {
			$this->requiredQty = floatval( $requiredQty );
		}
	}

	/**
	 * @return float
	 */
	public function getRequiredQty() {
		return $this->requiredQty;
	}

	/**
	 * @param Rule $rule
	 * @param int  $index
	 * @param bool $isAllowToSelect
	 *
	 * @return string
	 */
	public function generateHash( $rule, $index, $isAllowToSelect ) {
		$pieces = array( $rule->getHash(), strval( $index ), strval( $isAllowToSelect ), serialize( $this ) );

		return md5( join( "_", $pieces ) );
	}

	/**
	 * @return array
	 */
	public function __serialize() {
		return array(
			'choices'     => $this->choices,
			'requiredQty' => $this->requiredQty,
		);
	}

	/**
	 * @param array $data
	 */
	public function __unserialize( $data ) {
		$this->choices     = $data['choices'];
		$this->requiredQty = $data['requiredQty'];
	}
}
