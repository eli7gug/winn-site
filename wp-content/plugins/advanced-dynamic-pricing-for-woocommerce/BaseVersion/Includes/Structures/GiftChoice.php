<?php

namespace ADP\BaseVersion\Includes\Structures;

use ADP\BaseVersion\Includes\Enums\GiftChoiceMethodEnum;
use ADP\BaseVersion\Includes\Enums\GiftChoiceTypeEnum;

class GiftChoice {
	/**
	 * @var GiftChoiceTypeEnum
	 */
	protected $type;

	/**
	 * @var GiftChoiceMethodEnum
	 */
	protected $method;

	/**
	 * @var array
	 */
	protected $values;

	public function __construct() {
		$this->type   = new GiftChoiceTypeEnum();
		$this->method = new GiftChoiceMethodEnum();
		$this->values = array();
	}

	/**
	 * @param GiftChoiceTypeEnum $type
	 *
	 * @return self
	 */
	public function setType( $type ) {
		if ( $type instanceof GiftChoiceTypeEnum ) {
			$this->type = $type;
		}

		return $this;
	}

	/**
	 * @param GiftChoiceMethodEnum $method
	 *
	 * @return self
	 */
	public function setMethod( $method ) {
		if ( $method instanceof GiftChoiceMethodEnum ) {
			$this->method = $method;
		}

		return $this;
	}

	/**
	 * @param array $values
	 *
	 * @return self
	 */
	public function setValues( $values ) {
		if ( is_array( $values ) ) {
			$this->values = $values;
		}

		return $this;
	}

	/**
	 * @return GiftChoiceTypeEnum
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return GiftChoiceMethodEnum
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}

	public function isValid() {
		if ( strval( $this->type ) === GiftChoiceTypeEnum::CLONE_ADJUSTED ) {
			return true;
		}

		return count( $this->values ) > 0;
	}

	/**
	 * @return array
	 */
	public function __serialize() {
		return array(
			'type'   => $this->type,
			'method' => $this->method,
			'values' => $this->values,
		);
	}

	/**
	 * @param array $data
	 */
	public function __unserialize( $data ) {
		$this->type   = $data['type'];
		$this->method = $data['method'];
		$this->values = $data['values'];
	}
}
