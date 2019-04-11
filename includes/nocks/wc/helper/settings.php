<?php

class Nocks_WC_Helper_Settings
{
    const DEFAULT_TIME_PAYMENT_CONFIRMATION_CHECK = '3:00';

    /**
     * Merchant Accounts
     *
     * @var array
     */
    protected $merchant_accounts = array();

    /**
     * @return bool
     */
    public function isTestModeEnabled() {
        return trim(get_option($this->getSettingId('test_mode_enabled'))) === 'yes';
    }

    /**
     * @return null|string
     */
    public function getApiKey() {
        return trim(get_option($this->getSettingId('live_api_key')));
    }

    /**
     * @param bool $test_mode
     * @return null|string
     */
    public function getMerchantAccount() {
        return trim(get_option($this->getSettingId('merchant_account')));
    }

    /**
     * Get current locale
     *
     * @return string
     */
    public function getCurrentLocale() {
        return apply_filters('wpml_current_language', get_locale());
    }

    /**
     * @return bool
     */
    public function isDebugEnabled() {
        return get_option($this->getSettingId('debug'), 'yes') === 'yes';
    }

    /**
     * @return string
     */
    public function getGlobalSettingsUrl() {
        return admin_url('admin.php?page=wc-settings&tab=checkout#' . Nocks_WC_Plugin::PLUGIN_ID);
    }

    /**
     * @return string
     */
    public function getLogsUrl() {
        return admin_url('admin.php?page=wc-status&tab=logs');
    }

	/**
	 * @param null $apiKey
	 * @param null $testMode
	 *
	 * @return array
	 * @throws Nocks_WC_Exception_CouldNotConnectToNocks
	 */
    public function getNocksMerchants($apiKey = null, $testMode = null) {
        try {
            $api_helper = Nocks_WC_Plugin::getApiHelper();
            $api_client = $api_helper->getApiClient();

            // Try to load Nocks Merchant Accounts
            $this->merchant_accounts = $api_client->getMerchants($apiKey, $testMode);

            return $this->merchant_accounts;
        } catch (Nocks_Exception $e) {
            throw new Nocks_WC_Exception_CouldNotConnectToNocks($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array $settings
     * @return array
     */
    public function addGlobalSettingsFields(array $settings) {
        $content = '' . $this->getPluginStatus() . $this->getNocksMethods();
        $debug_desc = __('Log plugin events.', 'nocks-checkout-for-woocommerce');

        // For WooCommerce 2.2.0+ display view logs link
        if (version_compare(Nocks_WC_Plugin::getStatusHelper()->getWooCommerceVersion(), '2.2.0', ">=")) {
            $debug_desc .= ' <a href="' . $this->getLogsUrl() . '">' . __('View logs', 'nocks-checkout-for-woocommerce') . '</a>';
        }
        // Display location of log files
        else {
            /* translators: Placeholder 1: Location of the log files */
            $debug_desc .= ' ' . sprintf(__('Log files are saved to <code>%s</code>', 'nocks-checkout-for-woocommerce'), defined('WC_LOG_DIR') ? WC_LOG_DIR : WC()->plugin_path() . '/logs/');
        }

        $settings_helper = Nocks_WC_Plugin::getSettingsHelper();

        // Global Nocks settings
        $nocks_settings = array(
            array(
                'id'    => $this->getSettingId('title'),
                'title' => __('Nocks Checkout settings', 'nocks-checkout-for-woocommerce'),
                'type'  => 'title',
                'desc'  => '<p id="' . Nocks_WC_Plugin::PLUGIN_ID . '">' . $content . '</p>' . '<p>' . __('The following options are required to use the plugin and are used by all Nocks payment methods', 'nocks-checkout-for-woocommerce') . '</p>',
            ),
	        array(
		        'id'       => $this->getSettingId('test_mode_enabled'),
		        'title'    => __('Enable test mode', 'nocks-checkout-for-woocommerce'),
		        'default'  => 'no',
		        'type'     => 'checkbox',
		        'desc_tip' => __('Enable test mode if you want to test the plugin without using real payments. (Sandbox API key required)', 'nocks-checkout-for-woocommerce'),
	        ),
            array(
                'id'          => $this->getSettingId('live_api_key'),
                'title'       => __('API key', 'nocks-checkout-for-woocommerce'),
                'default'     => '',
                'type'        => 'textarea',
                'desc'        => __('Please enter your <a target="_blank" href="https://www.nocks.com/account/api/personal-tokens">Nocks API key</a> to select a merchant account. No API-key? Create one <a target="_blank" href="https://www.nocks.com/account/api/personal-tokens">here</a> and provide the following permissions: <br/><strong>transaction.create, transaction.read and merchant.read</strong>', 'nocks-checkout-for-woocommerce'),
                'css'         => 'height: 200px; width: 100%;',
                'placeholder' => __('Please paste your Nocks API Key here', 'nocks-checkout-for-woocommerce'),
            ),
            array(
                'id'          => $this->getSettingId('merchant_account'),
                'title'       => __('Nocks Merchant Account', 'nocks-checkout-for-woocommerce'),
                'type'        => 'select',
                'description' => __('Please select a merchant account.', 'nocks-checkout-for-woocommerce'),
                'default'     => '',
                'options' => $settings_helper->getNocksMerchants(isset($_POST['nocks-checkout-for-woocommerce_live_api_key']) ? $_POST['nocks-checkout-for-woocommerce_live_api_key'] : null, $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['nocks-checkout-for-woocommerce_test_mode_enabled']) : null),
            ),
            array(
                'id'      => $this->getSettingId('debug'),
                'title'   => __('Debug Log', 'nocks-checkout-for-woocommerce'),
                'type'    => 'checkbox',
                'desc'    => $debug_desc,
                'default' => 'yes',
            ),
            array(
                'id'   => $this->getSettingId('sectionend'),
                'type' => 'sectionend',
            ),
        );

        return $this->mergeSettings($settings, $nocks_settings);
    }

    public function getPaymentConfirmationCheckTime() {
        $time = strtotime(self::DEFAULT_TIME_PAYMENT_CONFIRMATION_CHECK);
        $date = new DateTime();

        if ($date->getTimestamp() > $time) {
            $date->setTimestamp($time);
            $date->add(new DateInterval('P1D'));
        }
        else {
            $date->setTimestamp($time);
        }

        return $date->getTimestamp();
    }

    /**
     * Get plugin status
     *
     * - Check compatibility
     * - Check Nocks API connectivity
     *
     * @return string
     */
    protected function getPluginStatus() {
        $status = Nocks_WC_Plugin::getStatusHelper();

        if (!$status->isCompatible()) {
            // Just stop here!
            return '' . '<div class="notice notice-error">' . '<p><strong>' . __('Error', 'nocks-checkout-for-woocommerce') . ':</strong> ' . implode('<br/>', $status->getErrors()) . '</p></div>';
        }

        try {
            // Check Nocks Merchants
            $status->getNocksMerchants();

            $api_status = '' . '<p>' . __('Nocks status:', 'nocks-checkout-for-woocommerce') . ' <span style="color:green; font-weight:bold;">' . __('Connected', 'nocks-checkout-for-woocommerce') . '</span>' . '</p>';
            $api_status_type = 'updated';
        } catch (Nocks_WC_Exception_CouldNotConnectToNocks $e) {
            $api_status = '' . '<p style="font-weight:bold;"><span style="color:red;">Communicating with Nocks failed:</span> ' . esc_html($e->getMessage()) . '</p>' . '<p>Please check the following conditions. You can ask your system administrator to help with this.</p>'

                . '<ul style="color: #2D60B0;">' . ' <li>Please check if you\'ve inserted your API key correctly.</li>' . ' <li>Make sure outside connections to <strong>' . esc_html(Nocks_WC_Helper_Api::getApiEndpoint()) . '</strong> are not blocked.</li>' . ' <li>Make sure SSL v3 is disabled on your server. Nocks does not support SSL v3.</li>' . ' <li>Make sure your server is up-to-date and the latest security patches have been installed.</li>' . '</ul><br/>'

                . '<p>Please contact <a href="mailto:info@nocks.com">info@nocks.com</a> if this still does not fix your problem.</p>';

            $api_status_type = 'error';
        } catch (Nocks_WC_Exception_InvalidApiKey $e) {
            $api_status = '<p style="color:red; font-weight:bold;">' . esc_html($e->getMessage()) . '</p>';
            $api_status_type = 'error';
        }

        return '' . '<div id="message" class="' . $api_status_type . ' fade notice">' . $api_status . '</div>';
    }

    /**
     * @param string $gateway_class_name
     * @return string
     */
    protected function getGatewaySettingsUrl($gateway_class_name) {
        return admin_url('admin.php?page=wc-settings&tab=checkout&section=' . sanitize_title(strtolower($gateway_class_name)));
    }

    protected function getNocksMethods() {
        $content = '';
        $content .= __('Payment methods:', 'nocks-checkout-for-woocommerce');

        $content .= '<table style="width: 500px">';
        foreach (Nocks_WC_Plugin::$GATEWAYS as $gateway_classname) {
            $gateway = new $gateway_classname;

            if ($gateway instanceof Nocks_WC_Gateway_Abstract) {
                $content .= '<tr>';
                $content .= '<td style="width: 10px;"><img src="' . esc_attr($gateway->getIconUrl()) . '" alt="' . esc_attr($gateway->getDefaultTitle()) . '" title="' . esc_attr($gateway->getDefaultTitle()) . '" style="width: 25px; vertical-align: bottom;" /></td>';
                $content .= '<td>' . esc_html($gateway->getDefaultTitle()).'</td>';
                $content .= '<td><a href="' . $this->getGatewaySettingsUrl($gateway_classname) . '">' . strtolower(__('Edit', 'nocks-checkout-for-woocommerce')) . '</a></td>';
                $content .= '</tr>';
            }
        }

        $content .= '</table>';
        $content .= '<div class="clear"></div>';

        return $content;
    }

    /**
     * @param string $setting
     * @return string
     */
    protected function getSettingId($setting) {
        global $wp_version;

        $setting_id = Nocks_WC_Plugin::PLUGIN_ID . '_' . trim($setting);
        $setting_id_length = strlen($setting_id);

        $max_option_name_length = 191;

        /**
         * Prior to WooPress version 4.4.0, the maximum length for wp_options.option_name is 64 characters.
         * @see https://core.trac.wordpress.org/changeset/34030
         */
        if ($wp_version < '4.4.0') {
            $max_option_name_length = 64;
        }

        if ($setting_id_length > $max_option_name_length) {
            trigger_error("Setting id $setting_id ($setting_id_length) to long for database column wp_options.option_name which is varchar($max_option_name_length).", E_USER_WARNING);
        }

        return $setting_id;
    }

    /**
     * @param array $settings
     * @param array $nocks_settings
     * @return array
     */
    protected function mergeSettings(array $settings, array $nocks_settings) {
        $new_settings = array();
        $nocks_settings_merged = false;

        // Find payment gateway options index
        foreach ($settings as $index => $setting) {
            if (isset($setting['id']) && $setting['id'] == 'payment_gateways_options' && (!isset($setting['type']) || $setting['type'] != 'sectionend')) {
                $new_settings = array_merge($new_settings, $nocks_settings);
                $nocks_settings_merged = true;
            }

            $new_settings[] = $setting;
        }

        // Nocks settings not merged yet, payment_gateways_options not found
        if (!$nocks_settings_merged) {
            // Append Nocks settings
            $new_settings = array_merge($new_settings, $nocks_settings);
        }

        return $new_settings;
    }
}
