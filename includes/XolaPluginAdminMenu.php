<?php

namespace xola;

class XolaPluginAdminMenu
{
    function __construct()
    {
        // load admin js
        add_action('admin_enqueue_scripts', function () {
            // Load JS
            wp_register_script('jquery-confirm-js', XOLA_ASSETS_URI . 'js/jquery-confirm.min.js', array('jquery'), '3.2.0', true);
            wp_enqueue_script('jquery-confirm-js');

            wp_register_script('jquery-poshytip-js', XOLA_ASSETS_URI . 'js/jquery.poshytip.min.js', array('jquery'), '1.2', true);
            wp_enqueue_script('jquery-poshytip-js');

            wp_register_script('jquery-editable-js', XOLA_ASSETS_URI . 'js/jquery-editable-poshytip.min.js', array('jquery'), '1.5.1', true);
            wp_enqueue_script('jquery-editable-js');

            wp_register_script('xola-admin-js', XOLA_ASSETS_URI . 'js/xola-admin.js', array('jquery-ui-tabs'), '1.0', true);
            wp_enqueue_script('xola-admin-js');

            // Load CSS
            wp_register_style('jquery-confirm-css', XOLA_ASSETS_URI . 'css/jquery-confirm.min.css');
            wp_enqueue_style('jquery-confirm-css');

            wp_register_style('jquery-poshytip-css', XOLA_ASSETS_URI . 'css/poshytip-darkgray.css');
            wp_enqueue_style('jquery-poshytip-css');

            wp_register_style('jquery-editable-css', XOLA_ASSETS_URI . 'css/jquery-editable.css');
            wp_enqueue_style('jquery-editable-css');

            wp_register_style('xola-admin-css', XOLA_ASSETS_URI . 'css/xola-admin.css');
            wp_enqueue_style('xola-admin-css');
        }, 100);
    }

    static function settingsPageInit()
    {
        add_menu_page(
            __('XOLA', XOLA_LANG),
            __('XOLA', XOLA_LANG),
            'manage_options',
            'xola-plugin-settings',
            function () {
                $nonce = &$_REQUEST[XOLA_NONCE_ACTION];

                if (wp_verify_nonce($nonce, XOLA_NONCE_ACTION)) {

                    $action = &$_REQUEST['action'];

                    $data = $_POST;
                    switch ($action) {
                        case 'settings':
                            self::saveSettings($data);
                            break;
                        case 'ob-login':
                            self::obLogin($data);
                            break;
                        case 'ob-register':
                            self::obRegister($data);
                            break;
                    }
                }

                $onboardingFinished = booleanValue(get_option(XOLA_PREFIX . 'onboarding'));

                if (!$onboardingFinished) {
                    $step = intval(get_option(XOLA_PREFIX . 'onboarding_step'));

                    switch ($step) {
                        case 0:
                            include_once XOLA_ADMIN_MENUS_PATH . 'onboarding.php';
                            break;
                        case 1:
                            include_once XOLA_ADMIN_MENUS_PATH . 'onboardingSync.php';
                            break;
                        case 2:
                            include_once XOLA_ADMIN_MENUS_PATH . 'onboardingLayout.php';
                            break;
                    }
                } else {
                    include_once XOLA_ADMIN_MENUS_PATH . 'pluginSettings.php';
                }
            },
            XOLA_ASSETS_URI . 'images/logo-small.png'
        );
    }

    static function saveSettingsAjax($data)
    {
        self::saveSettings($data);

        XolaPlugin::flushingRules();
    }

    private static function saveSettings($data)
    {
        $xolaUsername = '';
        $xolaPass = '';

        foreach ($data as $field => $value) {
            if (strpos($field, XOLA_PREFIX) === 0) {
                if (!is_array($value)) {
                    $value = trim($value);
                }

                if (strcmp($field, XOLA_PREFIX . 'google_analytics_code') === 0 && !empty($value)) {
                    $value = stripslashes(wp_filter_post_kses(addslashes($value)));
                }

                if (strcmp($field, XOLA_PREFIX . 'product_button_style_custom_code') === 0 && !empty($value)) {
                    $value = stripslashes($value);
                }

                if (strcmp($field, XOLA_PREFIX . 'product_gift_button_style_custom_code') === 0 && !empty($value)) {
                    $value = stripslashes($value);
                }

                if (strcmp($field, XOLA_PREFIX . 'google_maps_api_key') === 0 && !empty($value) && (wp_get_theme() == "Bridge Child" || wp_get_theme() == "Bridge")) {
                    XolaPluginAdminMenu::updateBridgeGoogleMapKey($value);
                }

                update_option($field, $value);

                if (strcmp($field, 'xola_account_email') === 0 && !empty($value)) {
                    $xolaUsername = $value;
                }

                if (strcmp($field, 'xola_account_password') === 0 && !empty($value)) {
                    $xolaPass = $value;
                }

            }
        }

        if (!isset($data['xola_product_visible_listings'])) {
            update_option('xola_product_visible_listings', '');
        }

        // account ID and API key
        $xolaAccountIdName = XOLA_PREFIX . 'account_id';
        $xolaAccountIdVal = get_option($xolaAccountIdName);

        $xolaApiKeyName = XOLA_PREFIX . 'account_api_key';
        $xolaApiKeyVal = get_option($xolaApiKeyName);

        if (empty($xolaAccountIdVal) || empty($xolaApiKeyVal) && !empty($xolaPass)) {
            $xolaUser = XolaAccount::getUserId($xolaUsername, $xolaPass);

            update_option($xolaAccountIdName, $xolaUser['id']);
            update_option($xolaApiKeyName, $xolaUser['apiKey']);
        }
    }

    private static function obLogin($data)
    {
        $xolaUser = XolaAccount::getUserId($data['email'], $data['password']);

        if (isset($xolaUser['id']) && !empty($xolaUser['id'])) {
            XolaData::createTables();

            update_option(XOLA_PREFIX . 'account_email', $data['email']);
            update_option(XOLA_PREFIX . 'account_password', $data['password']);
            update_option(XOLA_PREFIX . 'account_id', $xolaUser['id']);
            update_option(XOLA_PREFIX . 'account_api_key', $xolaUser['apiKey']);

            XolaData::insertNewData();
            XolaData::registerWebhook("create");
            XolaData::registerWebhook("update");
            XolaData::registerWebhook("delete");

            update_option(XOLA_PREFIX . 'onboarding_step', 1);
        } else {
            $session = new XolaSession;

            $error = '<span>Incorrect email or password.</span><br><span>Please try again.</span>';
            $session->addError($error);
        }
    }

    private static function obRegister($data)
    {
        $accountData = array(
            'first-name'   => $data['first-name'],
            'last-name'    => $data['last-name'],
            'email'        => $data['email'],
            'phone-number' => $data['phone-number'],
            'company-url'  => $data['company-url'],
        );

        $emailSent = XolaAccount::requestAccount($accountData);

        if ($emailSent) {
            // SET xola_onboarding_pending to true
            update_option(XOLA_PREFIX . 'onboarding_pending', true);
        } else {
            $session = new XolaSession;

            $error = '<span>Send request fail.</span><br><span>Please try again.</span>';
            $session->addError($error);
        }
    }

    private static function updateBridgeGoogleMapKey($key) {
        $bridgeData = get_option("qode_options_proya");
        $bridgeData["google_maps_api_key"] = $key;
        update_option("qode_options_proya", $bridgeData);
    }
}

new XolaPluginAdminMenu;