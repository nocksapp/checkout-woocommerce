<?php
class Nocks_WC_Gateway_Gulden extends Nocks_WC_Gateway_Abstract
{
    /**
     * @return string
     */
    public function getNocksMethodId ()
    {
        return 'gulden';
    }

	public function getSourceCurrency()
	{
		return 'NLG';
	}

    /**
     * @return string
     */
    public function getDefaultTitle ()
    {
        return __('Gulden', 'nocks-checkout-for-woocommerce');
    }

	/**
	 * @return string
	 */
	protected function getSettingsDescription()
	{
		return __('Accept Gulden payments with Nocks', 'nocks-checkout-for-woocommerce');
	}

    /**
     * @return string
     */
    protected function getDefaultDescription ()
    {
        /* translators: Default description, displayed above dropdown */
        return __('Pay with Gulden', 'nocks-checkout-for-woocommerce');
    }

    /**
     * Display fields below payment method in checkout
     */
    public function payment_fields() {
        // Display description above issuers
        // wp_enqueue_style( 'nocks_checkout_add_currency_css_nlg', plugins_url('assets/guldensign/guldensign.css', dirname(__FILE__)));

        parent::payment_fields();
        try {
        	$currency = get_woocommerce_currency();
        	$amount = WC()->cart->total;
        	if ($currency !== 'NLG') {
		        $priceData = Nocks_WC_Plugin::getApiHelper()->getApiClient()->calculatePrice( get_woocommerce_currency(), WC()->cart->total, "NLG" );
		        $amount    = $priceData['source_amount']['amount'];
	        }

	        $html = '<br/>' . __('Estimated total amount of Gulden: ', 'nocks-checkout-for-woocommerce') . '<i class="guldensign"></i>'.($amount).'';
        } catch(Exception $e) {
            $html = '<br/>' . __('We cannot calculate the amount of Guldens at this moment.', 'nocks-checkout-for-woocommerce');
        }


        echo wpautop(wptexturize($html));
    }
}
