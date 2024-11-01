<?php

define('XOLA_PREFIX', 'xola_');
define('XOLA_NONCE_ACTION', 'xolaNonce');
define('XOLA_ADMIN_MENUS_PATH', XOLA_PLUGIN_ROOT . 'includes' . XOLA_DS . 'adminMenus' . XOLA_DS);
define('XOLA_LANG', get_bloginfo('language'));

define('XOLA_ASSETS_PATH', XOLA_PLUGIN_ROOT . 'assets' . XOLA_DS);
define('XOLA_ASSETS_URI', XOLA_PLUGIN_URI . 'assets' . XOLA_DS);

define('XOLA_DISCOVERY_DEFAULT', 'discovery');
define('XOLA_DISCOVERY_GRID', 'grid');
define('XOLA_DISCOVERY_LIST', 'rows');

define('XOLA_DEFAULT_CURRENCY', '$');

$onboardingFinished = booleanValue(get_option(XOLA_PREFIX . 'onboarding'));
define('XOLA_ONBOARDING_FINISHED', $onboardingFinished);

$onboardingPending = booleanValue(get_option(XOLA_PREFIX . 'onboarding_pending'));
define('XOLA_ONBOARDING_PENDING', $onboardingPending);


function booleanValue($value) {

    $return = false;

    if(is_numeric($value)) {
        if($value != 0) $return = true;
    }
    else if(is_array($value)) {
        if(count($value)) $return = true;
    }
    else if(is_string($value)) {
        if($value != '') $return = true;
    }

    return $return;
}