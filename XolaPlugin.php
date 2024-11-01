<?php

/**
 * Plugin Name: Xola Bookings for Tours & Activities
 * Plugin URI:  https://wordpress.org/plugins/xola-bookings-for-tours-activities/
 * Description: The Xola Booking plugin adds powerful, versatile booking capabilities to your websites in seconds. The plugin integrates with your Xola account, creating a beautiful storefront where visitors can browse your activities, make bookings, and pay securely online.
 * Version:     1.6
 * Author:      xola.com
 * Author URI:  https://www.xola.com/
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: xola
 */

namespace xola;

class XolaPlugin
{
    protected static $session;

    function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'onInstall'));
        register_deactivation_hook(__FILE__, array($this, 'onUninstall'));

        self::includes();

        self::$session = new XolaSession;

        add_action('admin_menu', array('xola\XolaPluginAdminMenu', 'settingsPageInit'));

        add_action('generate_rewrite_rules', array($this, 'rewriteRules'));
        add_filter('init', array($this, 'addQueryVars'));

        add_filter('template_include', array($this, 'templateRedirectIntercept'));

        // Load Scripts and Styles
        add_action('wp_enqueue_scripts', array($this, 'loadScriptsAndStyles'));

        add_action('admin_notices', array($this, 'xolaShowNotices'));
    }

    static function onInstall()
    {
        // Flush rules so new ones start working
        self::flushingRules();

        XolaData::createTables();

        XolaData::insertNewData();

        self::addDiscoveryToNav();
    }

    static function onUninstall()
    {
        XolaData::dropTable();
    }

    static function includes()
    {
        define('XOLA_DS', '/');
        define('XOLA_PLUGIN_ROOT', dirname(__FILE__) . XOLA_DS);
        define('XOLA_PLUGIN_URI', plugin_dir_url(__FILE__));

        // Autoload includes
        $includes = glob(__DIR__ . XOLA_DS . 'includes' . XOLA_DS . '*.php', GLOB_NOSORT);

        if (!empty($includes)) {
            foreach ($includes as $include) {
                require_once($include);
            }
        }
    }

    static function rewriteRules($wp_rewrite)
    {
        $xolaDiscoveryPageLabelName = XOLA_PREFIX . 'discovery_page_label';
        $discovery = get_option($xolaDiscoveryPageLabelName);

        $discoveryEnabledName = XOLA_PREFIX . 'discovery_page_toggle';
        $discoveryEnabled = get_option($discoveryEnabledName);

        if (empty($discovery)) {
            $discovery = XOLA_DISCOVERY_DEFAULT;
        }

        $new_rules = array(
            "{$discovery}/([^/]*)/?$"  => 'index.php?xola_discovery_single=true&xola_discovery_single_slug=$matches[1]', // discovery product single
            '^xola-webhook/([^/]*)/?$' => 'index.php?xola_webhook=true&xola_webhook_hash_key=$matches[1]',
        );

        if ($discoveryEnabled) {
            $new_rules = array(
                "{$discovery}/?$"          => 'index.php?xola_discovery_page=true', // archive discovery
                "{$discovery}/([^/]*)/?$"  => 'index.php?xola_discovery_single=true&xola_discovery_single_slug=$matches[1]', // discovery product single
                '^xola-webhook/([^/]*)/?$' => 'index.php?xola_webhook=true&xola_webhook_hash_key=$matches[1]',
            );
        }

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;

        return $wp_rewrite;
    }

    static function flushingRules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    static function addQueryVars()
    {
        global $wp;
        $wp->add_query_var('xola_discovery_page');
        $wp->add_query_var('xola_discovery_single');
        $wp->add_query_var('xola_discovery_single_slug');

        // Webhook endpoint
        $wp->add_query_var('xola_webhook');
        $wp->add_query_var('xola_webhook_hash_key');
    }

    static function templateRedirectIntercept($original_template)
    {
        $discovery = get_query_var('xola_discovery_page');
        $discoverySingle = get_query_var('xola_discovery_single');
        $webhook = get_query_var('xola_webhook');

        if (booleanValue($discovery)) {
            XolaPlugin::addXolaBodyClass();

            return plugin_dir_path(__FILE__) . XOLA_DS . 'template' . XOLA_DS . 'discovery.php';
        } elseif (booleanValue($discoverySingle)) {
            $discoverySingleSlug = get_query_var('xola_discovery_single_slug');
            XolaPlugin::addXolaBodyClass();

            return plugin_dir_path(__FILE__) . XOLA_DS . 'template' . XOLA_DS . 'discoverySingle.php';
        } elseif (booleanValue($webhook)) {
            self::xolaWebhook();
        } else {
            return $original_template;
        }
    }

    static function addXolaBodyClass()
    {
        add_filter('body_class', function ($classes) {
            return array_merge($classes, array('xola'));
        });
    }

    static function loadScriptsAndStyles()
    {
        $discovery = get_query_var('xola_discovery_page');
        $discoverySingle = get_query_var('xola_discovery_single');

        if (booleanValue($discovery) || booleanValue($discoverySingle)) {
            wp_register_script('xola-bootstrap-js', XOLA_ASSETS_URI . 'js/bootstrap.min.js', array('jquery'), '3.3.7', true);
            wp_enqueue_script('xola-bootstrap-js');

            wp_register_script('xola-bootstrap-moment', XOLA_ASSETS_URI . 'js/moment.min.js', array('xola-bootstrap-js'), '2.18.1', true);
            wp_enqueue_script('xola-bootstrap-moment');

            wp_register_script('xola-bootstrap-datepicker', XOLA_ASSETS_URI . 'js/daterangepicker.js', array('xola-bootstrap-js'), '2.1.15', true);
            wp_enqueue_script('xola-bootstrap-datepicker');

            wp_register_script('xola-slick-js', XOLA_ASSETS_URI . 'js/slick.min.js', array('jquery'), '1.6', true);
            wp_enqueue_script('xola-slick-js');

            wp_register_script('js-search-min-js', XOLA_ASSETS_URI . 'js/js-search.min.js', array('jquery'), '1.0', true);
            wp_enqueue_script('js-search-min-js');

            wp_register_script('js-match-height-min-js', XOLA_ASSETS_URI . 'js/jquery.matchHeight-min.js', array('jquery'), '0.7.2', true);
            wp_enqueue_script('js-match-height-min-js');

            wp_register_script('xola-main-js', XOLA_ASSETS_URI . 'js/xola-main.js', array('jquery'), '1.2', true);
            wp_enqueue_script('xola-main-js');

            // Load CSS
            wp_register_style('xola-bootstrap-css', XOLA_ASSETS_URI . 'css/bootstrap.min.css');
            wp_enqueue_style('xola-bootstrap-css');

            wp_register_style('xola-datepicker-css', XOLA_ASSETS_URI . 'css/daterangepicker.css');
            wp_enqueue_style('xola-datepicker-css');

            wp_register_style('xola-slick-css', XOLA_ASSETS_URI . 'css/slick.css');
            wp_enqueue_style('xola-slick-css');

            wp_register_style('xola-slick-theme-css', XOLA_ASSETS_URI . 'css/slick-theme.css');
            wp_enqueue_style('xola-slick-theme-css');

            wp_register_style('xola-main-css', XOLA_ASSETS_URI . 'css/xola-main.css');
            wp_enqueue_style('xola-main-css');

            wp_register_style('xola-font-css', '//fonts.googleapis.com/css?family=Maven+Pro');
            wp_enqueue_style('xola-font-css');
        }
    }

    static function addDiscoveryToNav()
    {
        $navOptName = XOLA_PREFIX . 'nav_items_added';
        $alreadyAdded = get_option($navOptName);
        $alreadyAdded = booleanValue($alreadyAdded);

        if (!$alreadyAdded) {

            $menus = get_nav_menu_locations();

            if (!empty($menus)) {

                $xolaDiscoveryPageLabelName = XOLA_PREFIX . 'discovery_page_label';
                $xolaDiscoveryPageLabelVal = get_option($xolaDiscoveryPageLabelName);

                if (empty($xolaDiscoveryPageLabelVal)) {
                    $xolaDiscoveryPageLabelVal = XOLA_DISCOVERY_DEFAULT;
                }

                $url = site_url() . '/' . $xolaDiscoveryPageLabelVal;

                foreach ($menus as $menuLocationSlug => $menuId) {

                    $menuItems = wp_get_nav_menu_items($menuId);

                    $inMenu = false;

                    foreach ($menuItems as $item) {
                        if (strcmp($item->post_title, 'Discovery') === 0 && strcmp($item->url, $url) === 0) {
                            $inMenu = true;
                            break;
                        }
                    }

                    if (!$inMenu) {
                        $itemData = array(
                            'menu-item-object-id'   => 0,
                            'menu-item-parent-id'   => 0,
                            'menu-item-position'    => 0, // end of menu
                            'menu-item-object'      => 'custom',
                            'menu-item-type'        => 'custom',
                            'menu-item-status'      => 'publish',
                            'menu-item-title'       => 'Discovery',
                            'menu-item-url'         => $url,
                            'menu-item-description' => '',
                            'menu-item-attr-title'  => '',
                            'menu-item-target'      => '',
                            'menu-item-classes'     => '',
                        );

                        wp_update_nav_menu_item($menuId, 0, $itemData);
                    }

                }

                update_option($navOptName, true);
            }
        }
    }

    static function replaceDiscoveryPageUrlInNav($oldLabel, $newLabel)
    {
        $oldLabel = trim($oldLabel);
        $newLabel = trim($newLabel);

        if (strcmp($oldLabel, $newLabel) !== 0) {
            $menus = get_nav_menu_locations();

            if (!empty($menus)) {

                if (empty($oldLabel)) {
                    $oldLabel = XOLA_DISCOVERY_DEFAULT;
                }

                $url = site_url() . '/' . $oldLabel;

                foreach ($menus as $menuLocationSlug => $menuId) {

                    $menuItems = wp_get_nav_menu_items($menuId);

                    $inMenu = false;
                    $menuItemId = null;

                    foreach ($menuItems as $item) {
                        if (strcmp($item->post_title, 'Discovery') === 0 && strcmp($item->url, $url) === 0) {
                            $inMenu = true;
                            $menuItemId = $item->ID;
                            break;
                        }
                    }

                    $newUrl = site_url() . '/' . $newLabel;

                    $itemData = array(
                        'menu-item-object-id'   => 0,
                        'menu-item-parent-id'   => 0,
                        'menu-item-position'    => 0, // end of menu
                        'menu-item-object'      => 'custom',
                        'menu-item-type'        => 'custom',
                        'menu-item-status'      => 'publish',
                        'menu-item-title'       => 'Discovery',
                        'menu-item-url'         => $newUrl,
                        'menu-item-description' => '',
                        'menu-item-attr-title'  => '',
                        'menu-item-target'      => '',
                        'menu-item-classes'     => '',
                    );

                    if (!$inMenu) {
                        $menuItemId = 0;
                    }

                    wp_update_nav_menu_item($menuId, $menuItemId, $itemData);
                }
            }
        }
    }

    static function isTlsVersionValid()
    {
        $curl = curl_version();

        $version = floatval($curl['version']);

        // CURL_SSLVERSION_TLSv1_2 => 6
        if ($version >= 6) {
            return true;
        }

        return false;
    }

    static function xolaWebhook()
    {
        $hashKey = get_query_var('xola_webhook_hash_key');
        $generatedHashKey = md5("xola-webhook-" . get_option(XOLA_PREFIX . "account_email"));

        if ($hashKey != $generatedHashKey)
            return;

        sleep(5);

        XolaData::insertNewData();

        $errors = array();

        if (empty($errors)) {
            $out = array(
                'success' => true,
            );
        } else {
            $out = array(
                'success' => false,
                'errors'  => $errors,
            );
        }

        echo json_encode($out);
        exit;
    }

    static function xolaMoveThemesToWpThemesDir()
    {
        $bridgeDir = XOLA_PLUGIN_ROOT . 'includes/files/themes/Bridge/theme-files';

        $themesDir = get_theme_root();

        if (!file_exists($themesDir . '/bridge') && !file_exists($themesDir . '/bridge-child') && file_exists($bridgeDir)) {
            try {

                // Copy Bridge theme (with child theme)
                foreach (
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($bridgeDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::SELF_FIRST) as $item
                ) {
                    if ($item->isDir()) {
                        mkdir($themesDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    } else {
                        copy($item, $themesDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    }
                }

                self::$session->addMessage('Bridge theme is successfully moved to your theme library. You can install it now. <br> You can check Bridge theme documentation <a href="' . XOLA_PLUGIN_URI . 'includes/files/themes/Bridge/documentation/bridge/original-documentation/index.html' . '" target="_blank">here.</a>');

            } catch (\Exception $e) {
                self::$session->addError('There was a problem with unpacking themes. Please move themes manually to "/wp-content/themes" directory.');
            }
        }
    }

    static function xolaShowNotices()
    {

        $errors = self::$session->getErrors();

        $messages = self::$session->getMessages();

        self::$session->clearInfo();

        if (!empty($errors)):
            ob_start();
            foreach ($errors as $error):?>
                <div class="notice notice-warning is-dismissible">
                    <p><?= _($error); ?></p>
                </div>
                <?= ob_get_clean();
            endforeach;
        endif;

        if (!empty($messages)):
            ob_start();
            foreach ($messages as $msg):?>
                <div class="notice notice-success is-dismissible">
                    <p><?= _($msg); ?></p>
                </div>
                <?= ob_get_clean();
            endforeach;
        endif;
    }
}

new XolaPlugin;