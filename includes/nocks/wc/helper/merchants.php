<?php
class Nocks_WC_Helper_Merchants
{
    /**
     * Merchant Accounts
     *
     * @var array
     */
    protected $merchant_accounts = array();

    /**
     * Minimal required WooCommerce version
     *
     * @var string
     */
    const MIN_WOOCOMMERCE_VERSION = '2.1.0';

    /**
     * @var string[]
     */
    protected $errors = array();

    /**
     * @return bool
     */
    public function hasErrors ()
    {
        return !empty($this->errors);
    }

    /**
     * @return string[]
     */
    public function getErrors ()
    {
        return $this->errors;
    }

    /**
     * @throws Nocks_WC_Exception_CouldNotConnectToNocks
     */
    public function getNocksMerchants ()
    {
        $api_helper = Nocks_WC_Plugin::getApiHelper();
        $api_client = $api_helper->getApiClient();

        try
        {
            $api_helper = Nocks_WC_Plugin::getApiHelper();
            $api_client = $api_helper->getApiClient();

            // Try to load Nocks Merchant Accounts

            $this->merchant_accounts = $api_client->merchant_accounts->all();
            return $this->merchant_accounts;
        }
        catch (Nocks_Exception $e)
        {
            throw new Nocks_WC_Exception_CouldNotConnectToNocks(
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @return Nocks_WC_CompatibilityChecker
     */
    protected function getApiClientCompatibilityChecker ()
    {
        return new Nocks_WC_CompatibilityChecker();
    }
}
