<?php

class Nocks_WC_Gateway_Sepa extends Nocks_WC_Gateway_Abstract
{
	/**
	 * @return string
	 */
	public function getNocksMethodId ()
	{
		return 'sepa';
	}

	public function getSourceCurrency()
	{
		return 'EUR';
	}

	/**
	 * @return string
	 */
	public function getDefaultTitle ()
	{
		return __('SEPA', 'nocks-checkout-for-woocommerce');
	}

	/**
	 * @return string
	 */
	protected function getSettingsDescription() {
		return __('Accept SEPA payments with Nocks', 'nocks-checkout-for-woocommerce');
	}

	/**
	 * @return string
	 */
	protected function getDefaultDescription ()
	{
		return __('Pay with SEPA', 'nocks-checkout-for-woocommerce');
	}

	public function init_form_fields()
	{
		parent::init_form_fields();

		$this->form_fields = array_merge($this->form_fields, array(
			'initial_order_status' => array(
				'title'       => __('Initial order status', 'nocks-checkout-for-woocommerce'),
				'type'        => 'select',
				'options'     => array(
					self::STATUS_ON_HOLD => wc_get_order_status_name(self::STATUS_ON_HOLD),
					self::STATUS_PENDING => wc_get_order_status_name(self::STATUS_PENDING),
				),
				'default'     => self::STATUS_ON_HOLD,
				'description' => sprintf(
					__('SEPA can take longer than a few hours to complete. The initial order state is then set to \'%s\'. This ensures the order is not cancelled when the setting %s is used.', 'nocks-checkout-for-woocommerce'),
					wc_get_order_status_name(self::STATUS_ON_HOLD),
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory') . '" target="_blank">' . __('Hold Stock (minutes)', 'woocommerce') . '</a>'
				)
			),
		));
	}

	public function getInitialOrderStatus()
	{
		return self::STATUS_ON_HOLD;
	}
}
