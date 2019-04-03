<?php
class Nocks_WC_Gateway_Balance extends Nocks_WC_Gateway_Abstract
{
	/**
	 * @return string
	 */
	public function getNocksMethodId ()
	{
		return 'balance';
	}

	/**
	 * @return string
	 */
	public function getDefaultTitle ()
	{
		return __('Nocks Balance', 'nocks-checkout-for-woocommerce');
	}

	/**
	 * @return string
	 */
	protected function getSettingsDescription()
	{
		return __('Accept Nocks Balance payments', 'nocks-checkout-for-woocommerce');
	}

	/**
	 * @return string
	 */
	protected function getDefaultDescription ()
	{
		/* translators: Default description, displayed above dropdown */
		return __('Pay with Nocks Balance', 'nocks-checkout-for-woocommerce');
	}
}
