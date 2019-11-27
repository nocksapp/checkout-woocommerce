<?php

class Nocks_WC_Gateway_Ideal extends Nocks_WC_Gateway_Abstract
{
	public $has_fields = true;

	/**
	 * @return string
	 */
	public function getNocksMethodId ()
	{
		return 'ideal';
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
		return __('iDEAL', 'nocks-checkout-for-woocommerce');
	}

	/**
	 * @return string
	 */
	protected function getSettingsDescription() {
		return __('Accept iDEAL payments with Nocks', 'nocks-checkout-for-woocommerce');;
	}

	/**
	 * @return string
	 */
	protected function getDefaultDescription ()
	{
		return __('Pay with iDEAL', 'nocks-checkout-for-woocommerce');
	}

	public function init_form_fields()
	{
		parent::init_form_fields();

		$this->form_fields = array_merge($this->form_fields, array(
			'issuers_empty_option' => array(
				'title'       => __('Issuers empty option', 'nocks-checkout-for-woocommerce'),
				'type'        => 'text',
				'description' => __('First default option in the iDEAL issuers drop down.', 'nocks-checkout-for-woocommerce'),
				'default'     => __('Select your bank', 'nocks-checkout-for-woocommerce'),
				'desc_tip'    => true,
			),
		));
	}

	/**
	 * Validate frontend fields.
	 *
	 * Validate payment fields on the frontend.
	 *
	 * @return bool
	 */
	public function validate_fields() {
		if (!$this->getSelectedIssuer()) {
			wc_add_notice(__('Please select your bank', 'nocks-checkout-for-woocommerce'), 'error');
			return false;
		}

		return parent::validate_fields();
	}

	/**
	 * Display fields below payment method in checkout
	 */
	public function payment_fields()
	{
		parent::payment_fields();
		try {
			$issuers = Nocks_WC_Plugin::getApiHelper()->getApiClient()->getIdealIssuers();
			$selected_issuer = $this->getSelectedIssuer();

			$options = array_reduce(array_keys($issuers), function ($res, $issuer) use ($issuers, $selected_issuer) {
				return $res . '<option value="' . $issuer . '" ' . ( $selected_issuer === $issuer ? ' selected=""' : '' ) . '>' . $issuers[$issuer] . '</option>';
			}, '<option value="">' . esc_html(__($this->get_option( 'issuers_empty_option', ''), 'nocks-checkout-for-woocommerce')) . '</option>');

			echo wpautop(wptexturize('<select name="' . Nocks_WC_Plugin::PLUGIN_ID . '_issuer_' . $this->id . '">' . $options . '</select>'));
		} catch(Exception $e) {
			// Fallback on no issuer selection so the user can choose on the nocks checkout page
		}
	}
}
