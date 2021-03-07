<?php

namespace ADP\BaseVersion\Includes\External\Cmp;


use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Currency;
use ADP\BaseVersion\Includes\CurrencyController;
use ADP\BaseVersion\Includes\Rule\Interfaces\Rule;
use ADP\BaseVersion\Includes\Translators\RuleTranslator;

class VillaThemeMultiCurrencyCmp {
	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @var WOOMULTI_CURRENCY_F_Data|WOOMULTI_CURRENCY_Data
	 */
	protected $villaTheme;

	public function __construct( $context ) {
		$this->context = $context;
		$this->loadRequirements();
	}

	public function loadRequirements() {
		if ( ! did_action( 'plugins_loaded' ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called earlier the %2$s action.',
				'advanced-dynamic-pricing-for-woocommerce' ), 'load_requirements', 'plugins_loaded' ), WC_ADP_VERSION );
		}

		if ( class_exists( '\WOOMULTI_CURRENCY_F_Data') ) {
			$this->villaTheme = \WOOMULTI_CURRENCY_F_Data::get_ins();
		} elseif ( class_exists( '\WOOMULTI_CURRENCY_Data') ) {
			$this->villaTheme = \WOOMULTI_CURRENCY_Data::get_ins();
		} else {
			$this->villaTheme = null;
		}
	}

	public function isActive() {
		return ! is_null( $this->villaTheme );
	}

	/**
	 * @param string $currency
	 *
	 * @return array|null
	 */
	protected function getCurrencyData( $currency ) {
		if ( ! $this->isActive() ) {
			return null;
		}

		$currencyData = null;
		$currencies   = $this->villaTheme->get_list_currencies();

		if ( isset( $currencies[ $currency ] ) && ! is_null( $currencies[ $currency ] ) ) {
			$currencyData = $currencies[ $currency ];
		}

		return $currencyData;
	}

	/**
	 * @return Currency|null
	 * @throws \Exception
	 */
	protected function getDefaultCurrency() {
		return $this->getCurrency( $this->villaTheme->get_default_currency() );
	}

	/**
	 * @param string $code
	 *
	 * @return Currency|null
	 * @throws \Exception
	 */
	protected function getCurrency( $code ) {
		if ( ! $this->isActive() ) {
			return null;
		}

		$currencyData = $this->getCurrencyData( $code );

		if ( ! $currencyData ) {
			return null;
		}
		return new Currency( $code, get_woocommerce_currency_symbol( $code ), $currencyData['rate'] );
	}

	/**
	 * @return Currency|null
	 * @throws \Exception
	 */
	protected function getCurrentCurrency() {
		return $this->getCurrency( $this->villaTheme->get_current_currency() );
	}

	public function modifyContext() {
		$this->context->currencyController = new CurrencyController( $this->context, $this->getDefaultCurrency() );
		$this->context->currencyController->setCurrentCurrency( $this->getCurrentCurrency() );
	}
}
