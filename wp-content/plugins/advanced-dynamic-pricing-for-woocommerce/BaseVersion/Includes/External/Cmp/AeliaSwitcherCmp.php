<?php


namespace ADP\BaseVersion\Includes\External\Cmp;


use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Currency;
use ADP\BaseVersion\Includes\CurrencyController;
use Aelia\WC\CurrencySwitcher\Definitions;
use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;

class AeliaSwitcherCmp {

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @var Aelia_Plugin
	 */
	protected $aeliaPlugin;

	public function __construct( $context ) {
		$this->context = $context;
		$this->loadRequirements();
	}

	public function loadRequirements() {
		if ( ! did_action( 'plugins_loaded' ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called earlier the %2$s action.',
				'advanced-dynamic-pricing-for-woocommerce' ), 'load_requirements', 'plugins_loaded' ), WC_ADP_VERSION );
		}
		$this->aeliaPlugin = ( class_exists("WC_Aelia_CurrencySwitcher") && isset( $GLOBALS[WC_Aelia_CurrencySwitcher::$plugin_slug] ) ) ? $GLOBALS[WC_Aelia_CurrencySwitcher::$plugin_slug] : NULL ; //settings_controller
	}

	public function isActive() {
		return !is_null( $this->aeliaPlugin );
	}

	/**
	 * @return Currency|null
	 * @throws \Exception
	 */
	protected function getDefaultCurrency() {
		return $this->getCurrency( $this->aeliaPlugin->base_currency() );
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

		return new Currency( $code, get_woocommerce_currency_symbol( $code ), $this->getExchangeRate( $code ) );
	}


	private function getExchangeRate( $selected_currency ) {
		$settingsController = $this->aeliaPlugin->settings_controller();
		// Retrieve exchange rates from the configuration
		$exchange_rates = $settingsController->get_exchange_rates();

		$result = isset( $exchange_rates[ $selected_currency ] ) ? $exchange_rates[ $selected_currency ] : null;
		if( empty( $result ) ) {
			$this->aeliaPlugin->trigger_error( Definitions::ERR_INVALID_CURRENCY, E_USER_WARNING, array( $selected_currency ) );
		}

		return $result;
	}

	/**
	 * @return Currency|null
	 * @throws \Exception
	 */
	protected function getCurrentCurrency() {
		return $this->getCurrency( $this->aeliaPlugin->get_selected_currency() );
	}

	public function modifyContext() {
		$this->context->currencyController = new CurrencyController( $this->context, $this->getDefaultCurrency() );
		$this->context->currencyController->setCurrentCurrency( $this->getCurrentCurrency() );
	}

}
