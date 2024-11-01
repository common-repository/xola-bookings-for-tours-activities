<?php

use xola\XolaData;

$googleMapsApiKeyName = XOLA_PREFIX . 'google_maps_api_key';
$googleMapsApiKeyVal = get_option($googleMapsApiKeyName);

$googleAnalyticsCodeName = XOLA_PREFIX . 'google_analytics_code';
$googleAnalyticsCodeVal = get_option($googleAnalyticsCodeName);

// XOLA account
$xolaEmailName = XOLA_PREFIX . 'account_email';
$xolaEmailVal = get_option($xolaEmailName);
$xolaPassName = XOLA_PREFIX . 'account_password';
$xolaPassVal = get_option($xolaPassName);
$xolaFullNameName = XOLA_PREFIX . 'account_full_name';
$xolaFullNameVal = get_option($xolaFullNameName);

$xolaAccountIdName = XOLA_PREFIX . 'account_id';
$xolaAccountIdVal = get_option($xolaAccountIdName);

$xolaApiKeyName = XOLA_PREFIX . 'account_api_key';
$xolaApiKeyVal = get_option($xolaApiKeyName);

// Switches
$xolaDiscoveryPageName = XOLA_PREFIX . 'discovery_page_toggle';
$xolaDiscoveryPageVal = booleanValue(get_option($xolaDiscoveryPageName));

// Visible listings
$xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
$xolaVisibleListingsVal = get_option($xolaVisibleListingsName);
$xolaVisibleListingsVal = empty($xolaVisibleListingsVal) ? array() : $xolaVisibleListingsVal;

// Discovery Page
$xolaDiscoveryLayoutName = XOLA_PREFIX . 'discovery_page_layout';
$xolaDiscoveryLayoutVal = get_option($xolaDiscoveryLayoutName);

$xolaDiscoveryPageLabelName = XOLA_PREFIX . 'discovery_page_label';
$xolaDiscoveryPageLabelVal = get_option($xolaDiscoveryPageLabelName);

$xolaDiscoveryPagePaginationCountName = XOLA_PREFIX . 'discovery_page_listing_count';
$xolaDiscoveryPagePaginationCountVal = get_option($xolaDiscoveryPagePaginationCountName);

if (empty($xolaDiscoveryPageLabelVal)) {
    $xolaDiscoveryPageLabelVal = XOLA_DISCOVERY_DEFAULT;
}

// Discovery Page Filters
$xolaDiscoveryDateRangeName = XOLA_PREFIX . 'discovery_date_range';
$xolaDiscoveryDateRangeVal = booleanValue(get_option($xolaDiscoveryDateRangeName));

$xolaDiscoveryGuestCountName = XOLA_PREFIX . 'discovery_guest_count';
$xolaDiscoveryGuestCountVal = booleanValue(get_option($xolaDiscoveryGuestCountName));

$xolaDiscoveryPriceSortName = XOLA_PREFIX . 'discovery_price_sort';
$xolaDiscoveryPriceSortVal = booleanValue(get_option($xolaDiscoveryPriceSortName));

// Product Page
$xolaProductCheckoutTypeName = XOLA_PREFIX . 'product_checkout_type';
$xolaProductCheckoutTypeVal = get_option($xolaProductCheckoutTypeName);

$xolaProductButtonTextName = XOLA_PREFIX . 'product_button_text';
$xolaProductButtonTextVal = get_option($xolaProductButtonTextName);

$xolaProductButtonStyleName = XOLA_PREFIX . 'product_button_style';
$xolaProductButtonStyleVal = get_option($xolaProductButtonStyleName);

$xolaProductButtonStyleCustomCodeDefault = '
/* BOOK NOW BUTTON STYLING
 * below is the default button styling
 * you are free to make changes or add new items to the CSS below   
 */
/* 
    height: 60px;
    width: 290px;
    border-radius: 4px;
    background-color: #8bc540;
    color: #ffffff;
    font-family: Helvetica, "Helvetica Neue", Arial, sans-serif;
    font-size: 18px;
*/';

$xolaProductButtonStyleCustomCodeName = XOLA_PREFIX . 'product_button_style_custom_code';
$xolaProductButtonStyleCustomCodeVal = get_option($xolaProductButtonStyleCustomCodeName);
$xolaProductButtonStyleCustomCodeVal = empty($xolaProductButtonStyleCustomCodeVal) ? $xolaProductButtonStyleCustomCodeDefault : $xolaProductButtonStyleCustomCodeVal;

$xolaProductDisplayGiftButtonName = XOLA_PREFIX . 'product_display_gift_button';
$xolaProductDisplayGiftButtonVal = get_option($xolaProductDisplayGiftButtonName);

$xolaProductGiftButtonTextName = XOLA_PREFIX . 'product_gift_button_text';
$xolaProductGiftButtonTextVal = get_option($xolaProductGiftButtonTextName);

$xolaProductGiftButtonStyleName = XOLA_PREFIX . 'product_gift_button_style';
$xolaProductGiftButtonStyleVal = get_option($xolaProductGiftButtonStyleName);

$xolaProductGiftButtonStyleCustomCodeName = XOLA_PREFIX . 'product_gift_button_style_custom_code';
$xolaProductGiftButtonStyleCustomCodeVal = get_option($xolaProductGiftButtonStyleCustomCodeName);
$xolaProductGiftButtonStyleCustomCodeVal = empty($xolaProductGiftButtonStyleCustomCodeVal) ? $xolaProductButtonStyleCustomCodeDefault : $xolaProductGiftButtonStyleCustomCodeVal;

$xolaProductVisibleListingsName = XOLA_PREFIX . 'product_visible_listings';
$xolaProductVisibleListingsVal = get_option($xolaProductVisibleListingsName);
$xolaProductVisibleListingsVal = empty($xolaProductVisibleListingsVal) ? array() : $xolaProductVisibleListingsVal;


$onboardingStep = intval(get_option(XOLA_PREFIX . 'onboarding_step'));

// Other
$pageUrl = admin_url('admin.php?page=xola-plugin-settings');

// LOADING PARTS OF BOOTSTRAP ONLY ON THIS PAGE
echo '<style>';
require_once(XOLA_ASSETS_PATH . 'css/xola-admin-bootstrap.min.css');
echo '</style>';
?>

<h2><?= __('XOLA Settings', XOLA_LANG); ?></h2>

<form method="post" class="xola-settings-form" id="xola-settings-form">

    <hr>

    <div id="xola-settings-tabs">

        <div class="wp-filter">
            <ul class="filter-links">
                <li class="plugin-install-featured">
                    <a href="<?= $pageUrl ?>#tab-general">General</a>
                </li>
                <li class="plugin-install-popular">
                    <a href="<?= $pageUrl ?>#tab-discovery">Discovery Page</a>
                </li>
                <li class="plugin-install-recommended">
                    <a href="<?= $pageUrl ?>#tab-product">Product Page</a>
                </li>
                <li class="plugin-install-advanced">
                    <a href="<?= $pageUrl ?>#tab-advanced">Advanced Configuration</a>
                </li>
            </ul>

        </div>

        <div class="tabs-content">
            <div id="tab-general">

                <?php if($onboardingStep == 2): ?>
                    <div class="onboarding-complete">
                        <a class="close" href="#" onclick="jQuery(this).parent().hide(); return false;">
                            x
                        </a>
                        <div class="text">
                            Congratulations, you've successfully created Xola-Powered pages for your listings!
                            <?php if($xolaDiscoveryPageVal): ?>
                                You can adjust your pages’ settings here in Wordpress or click <a target="_blank" href="<?= site_url() . '/' . $xolaDiscoveryPageLabelVal ?>">here</a> to see your new discovery page.
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php update_option(XOLA_PREFIX . 'onboarding_step', 3); ?>
                <?php endif; ?>

                <h3>General Settings</h3>

                <div class="email-data">
                    <div class="label">
                        <span>Xola e-mail</span>
                    </div>
                    <div class="value">
                        <span><?php echo $xolaEmailVal; ?></span>
                        <a href="#" id="xolaDisconnectAccount">sign out</a>
                    </div>

                </div>

                <table class="form-table">
                    <tbody>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaDiscoveryPageName ?>"><?= __('Discovery Page On/Off', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input type="hidden" value="0" name="<?= $xolaDiscoveryPageName ?>">
                            <label class="switch">
                                <input type="checkbox" name="<?= $xolaDiscoveryPageName ?>" value="1"
                                       id="<?= $xolaDiscoveryPageName ?>" <?= $xolaDiscoveryPageVal ? ' checked' : '' ?>>
                                <div class="xola-slider round">&nbsp;</div>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <div id="connected-listings" class="plugin-settings-page">
                                <div class="listings-header">
                                    <div class="listing">
                                        <span class="title">Listings</span>

                                        <button class="button-primary btn btn-primary btn-sm" type="button"
                                                id="forceInsertNewData">Sync Latest Data
                                        </button>

                                        <button class="button-primary btn btn-primary btn-sm btn-synced" type="button"
                                                disabled onclick="return false;">Synced
                                        </button>

                                        <div class="sk-fading-circle" id="forceInsertNewDataProgressSpinner">
                                            <div class="sk-circle1 sk-circle"></div>
                                            <div class="sk-circle2 sk-circle"></div>
                                            <div class="sk-circle3 sk-circle"></div>
                                            <div class="sk-circle4 sk-circle"></div>
                                            <div class="sk-circle5 sk-circle"></div>
                                            <div class="sk-circle6 sk-circle"></div>
                                            <div class="sk-circle7 sk-circle"></div>
                                            <div class="sk-circle8 sk-circle"></div>
                                            <div class="sk-circle9 sk-circle"></div>
                                            <div class="sk-circle10 sk-circle"></div>
                                            <div class="sk-circle11 sk-circle"></div>
                                            <div class="sk-circle12 sk-circle"></div>
                                        </div>

                                        <span class="last-synced">
                                            Last Sync:
                                            <span class="last-synced-date">
                                                <?php echo XolaData::getLatestTimestamp(); ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="show">
                                        <span class="title">Show/Hide</span>
                                    </div>
                                    <div class="url">
                                        <span class="title">URL</span>
                                    </div>
                                </div>


                                <?= XolaData::getSettingsListingsHtml(false); //change to enable/disable fetch every time on settings page      ?>
                            </div>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div id="tab-discovery">

                <h3>Discovery Page Settings</h3>

                <table class="form-table">
                    <tbody>

                    <tr>
                        <th scope="row">
                            <label><?= __('Layout', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input id="<?= $xolaDiscoveryLayoutName . '_grid' ?>" type="radio"
                                   name="<?= $xolaDiscoveryLayoutName ?>" value="grid" class="blue"
                                <?= $xolaDiscoveryLayoutVal === 'grid' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaDiscoveryLayoutName . '_grid' ?>"><span><span></span></span>Grid</label>

                            <input id="<?= $xolaDiscoveryLayoutName . '_rows' ?>" type="radio"
                                   name="<?= $xolaDiscoveryLayoutName ?>" value="rows" class="blue"
                                <?= $xolaDiscoveryLayoutVal === 'rows' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaDiscoveryLayoutName . '_rows' ?>"><span><span></span></span>Rows</label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaDiscoveryPageLabelName ?>"><?= __('Page URL', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <a href="<?= site_url() . '/' . $xolaDiscoveryPageLabelVal ?>" target="_blank"
                               id="discovery-page-live-link"><?= '/' . $xolaDiscoveryPageLabelVal; ?></a>

                            <a href="<?= site_url() . '/' . $xolaDiscoveryPageLabelVal ?>" target="_blank"
                               class="discovery-url" data-type="text" id="<?= $xolaDiscoveryPageLabelName ?>"
                               data-id="<?= $xolaDiscoveryPageLabelName ?>"
                               data-value="<?= $xolaDiscoveryPageLabelVal ?>"><?= '/' . $xolaDiscoveryPageLabelVal; ?></a>

                            <a href="#" class="edit-discovery-url"
                               data-target="#<?= $xolaDiscoveryPageLabelName ?>"
                               data-url="#<?= $xolaDiscoveryPageLabelName ?>">edit</a>
                        </td>
                    </tr>

                    </tbody>
                </table>

                <h3>Filter Settings</h3>

                <table class="form-table">
                    <tbody>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaDiscoveryDateRangeName ?>"><?= __('Date Range', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input type="hidden" value="0" name="<?= $xolaDiscoveryDateRangeName ?>">
                            <label class="switch">
                                <input type="checkbox" name="<?= $xolaDiscoveryDateRangeName ?>" value="1"
                                       id="<?= $xolaDiscoveryDateRangeName ?>" <?= $xolaDiscoveryDateRangeVal ? ' checked' : '' ?>>
                                <div class="xola-slider round">&nbsp;</div>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaDiscoveryGuestCountName ?>"><?= __('Guest Count', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input type="hidden" value="0" name="<?= $xolaDiscoveryGuestCountName ?>">
                            <label class="switch">
                                <input type="checkbox" name="<?= $xolaDiscoveryGuestCountName ?>" value="1"
                                       id="<?= $xolaDiscoveryGuestCountName ?>" <?= $xolaDiscoveryGuestCountVal ? ' checked' : '' ?>>
                                <div class="xola-slider round">&nbsp;</div>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaDiscoveryPriceSortName ?>"><?= __('Price Sort', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input type="hidden" value="0" name="<?= $xolaDiscoveryPriceSortName ?>">
                            <label class="switch">
                                <input type="checkbox" name="<?= $xolaDiscoveryPriceSortName ?>" value="1"
                                       id="<?= $xolaDiscoveryPriceSortName ?>" <?= $xolaDiscoveryPriceSortVal ? ' checked' : '' ?>>
                                <div class="xola-slider round">&nbsp;</div>
                            </label>
                        </td>
                    </tr>

                    </tbody>
                </table>

            </div>

            <div id="tab-product">

                <h3>Product Page Settings</h3>

                <h4 class="border-bottom">Book now widget</h4>
                <table class="form-table">
                    <tbody>

                    <tr>
                        <th scope="row">
                            <label><?= __('Checkout type', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input id="<?= $xolaProductCheckoutTypeName . '_single' ?>" type="radio"
                                   name="<?= $xolaProductCheckoutTypeName ?>" value="single" class="blue"
                                <?= $xolaProductCheckoutTypeVal === 'single' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaProductCheckoutTypeName . '_single' ?>"><span><span></span></span>Single
                                item</label>

                            <input id="<?= $xolaProductCheckoutTypeName . '_timeline' ?>" type="radio"
                                   name="<?= $xolaProductCheckoutTypeName ?>" value="timeline" class="blue"
                                <?= $xolaProductCheckoutTypeVal === 'timeline' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaProductCheckoutTypeName . '_timeline' ?>"><span><span></span></span>Timeline</label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaProductButtonTextName ?>"><?= __('Button text', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input name="<?= $xolaProductButtonTextName ?>" id="<?= $xolaProductButtonTextName ?>"
                                   value="<?= $xolaProductButtonTextVal ?>" class="regular-text" type="text"
                                   placeholder="Book Now" maxlength="25">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label><?= __('Button style', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input id="<?= $xolaProductButtonStyleName . '_default' ?>" type="radio"
                                   name="<?= $xolaProductButtonStyleName ?>" value="default" class="blue"
                                <?= $xolaProductButtonStyleVal === 'default' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaProductButtonStyleName . '_default' ?>"><span><span></span></span>Default</label>

                            <input id="<?= $xolaProductButtonStyleName . '_custom' ?>" type="radio"
                                   name="<?= $xolaProductButtonStyleName ?>" value="custom" class="blue"
                                <?= $xolaProductButtonStyleVal === 'custom' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaProductButtonStyleName . '_custom' ?>"><span><span></span></span>Custom</label>

                            <textarea class="large-text code" name="<?= $xolaProductButtonStyleCustomCodeName ?>"
                                      id="<?= $xolaProductButtonStyleCustomCodeName ?>" rows="10" cols="50"
                                      style="display:<?= $xolaProductButtonStyleVal === 'default' ? 'none' : 'block' ?>;"
                                      placeholder="Type CSS here..."><?= $xolaProductButtonStyleCustomCodeVal ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="<?= $xolaProductDisplayGiftButtonName ?>"><?= __('Display Gift Button', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input type="hidden" value="0" name="<?= $xolaProductDisplayGiftButtonName ?>">
                            <label class="switch">
                                <input type="checkbox" name="<?= $xolaProductDisplayGiftButtonName ?>" value="1"
                                       id="<?= $xolaProductDisplayGiftButtonName ?>" <?= $xolaProductDisplayGiftButtonVal ? ' checked' : '' ?>>
                                <div class="xola-slider round">&nbsp;</div>
                            </label>
                        </td>
                    </tr>

                    <tr class="gift_button_styles"
                        style="<?php if (!$xolaProductDisplayGiftButtonVal) echo 'display: none;'; ?>">
                        <th scope="row">
                            <label for="<?= $xolaProductGiftButtonTextName ?>"><?= __('Gift button text', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input name="<?= $xolaProductGiftButtonTextName ?>"
                                   id="<?= $xolaProductGiftButtonTextName ?>"
                                   value="<?= $xolaProductGiftButtonTextVal ?>" class="regular-text" type="text"
                                   placeholder="Purchase as gift" maxlength="25">
                        </td>
                    </tr>

                    <tr class="gift_button_styles"
                        style="<?php if (!$xolaProductDisplayGiftButtonVal) echo 'display: none;'; ?>">
                        <th scope="row">
                            <label><?= __('Gift button style', XOLA_LANG); ?></label>
                        </th>
                        <td>
                            <input id="<?= $xolaProductGiftButtonStyleName . '_default' ?>" type="radio"
                                   name="<?= $xolaProductGiftButtonStyleName ?>" value="default" class="blue"
                                <?= $xolaProductGiftButtonStyleVal === 'default' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaProductGiftButtonStyleName . '_default' ?>"><span><span></span></span>Default</label>

                            <input id="<?= $xolaProductGiftButtonStyleName . '_custom' ?>" type="radio"
                                   name="<?= $xolaProductGiftButtonStyleName ?>" value="custom" class="blue"
                                <?= $xolaProductGiftButtonStyleVal === 'custom' ? ' checked="checked"' : '' ?>>
                            <label for="<?= $xolaProductGiftButtonStyleName . '_custom' ?>"><span><span></span></span>Custom</label>

                            <textarea class="large-text code" name="<?= $xolaProductGiftButtonStyleCustomCodeName ?>"
                                      id="<?= $xolaProductGiftButtonStyleCustomCodeName ?>" rows="10" cols="50"
                                      style="display:<?= $xolaProductGiftButtonStyleVal === 'default' ? 'none' : 'block' ?>;"
                                      placeholder="Type CSS here..."><?= $xolaProductGiftButtonStyleCustomCodeVal ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" class="fullwidth" colspan="2">
                            <h4 class="border-bottom"><?= __('Body', XOLA_LANG); ?></h4>
                        </th>
                    </tr>
                    <tr>
                        <td colspan="2">

                            <?php
                            $productVisibleFields = array(
                                'description'          => 'Description',
                                'duration'             => 'Duration',
                                'whats_included'       => "What's included / What to bring",
                                'activity_location'    => 'Activity Location',
                                'other_considerations' => 'Other Considerations',
                                'other_activities'     => 'Other Activities'
                            );
                            ?>
                            <table class="listings">
                                <tbody>
                                <tr class="listings-header">
                                    <td colspan="2">Visible Fields</td>

                                    <td class="text-right">
                                        <?php if (count($productVisibleFields) === count($xolaProductVisibleListingsVal)): ?>
                                            <a href="#" id="product-fields-selection" class="no-outline"
                                               data-select="unselect">Unselect All</a>
                                        <?php else: ?>

                                            <a href="#" id="product-fields-selection" class="no-outline"
                                               data-select="select">Select All</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php

                                if (!empty($productVisibleFields)):
                                    foreach ($productVisibleFields as $field => $title): ?>
                                        <tr class="listing-row">
                                            <td style="width:5%;">
                                                <input type="checkbox" class="product-fields-checkbox blue square"
                                                       name="<?= $xolaProductVisibleListingsName ?>[]"
                                                       value="<?= $field ?>"
                                                       id="product_fields_<?= $field ?>"
                                                    <?= in_array($field, $xolaProductVisibleListingsVal) ? ' checked="checked"' : '' ?>><label
                                                        for="product_fields_<?= $field ?>"><span></span></label>
                                            </td>
                                            <td colspan="2"><label
                                                        for="product_fields_<?= $field ?>"><?= $title ?></label></td>
                                        </tr>
                                    <?php endforeach;
                                endif; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    </tbody>
                </table>

            </div>

            <div id="tab-advanced">

                <h3>Advanced Configuration</h3>

                <table class="form-table">
                    <tbody>

                    <tr>
                        <th scope="row">
                            <label for="<?= $googleMapsApiKeyName ?>"><?= __('Google Maps API key', XOLA_LANG); ?></label>
                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key"
                               target="_blank" class="needhelp">need help?</a>
                        </th>
                        <td>
                            <input type="password" name="<?= $googleMapsApiKeyName ?>" id="<?= $googleMapsApiKeyName ?>"
                                   value="<?= $googleMapsApiKeyVal ?>" class="regular-text" type="text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="<?= $googleAnalyticsCodeName ?>"><?= __('Google Analytics Code', XOLA_LANG); ?></label>
                            <a href="https://support.google.com/analytics/answer/1008080?hl=en" target="_blank"
                               class="needhelp">need help?</a>
                        </th>
                        <td>
                            <textarea name="<?= $googleAnalyticsCodeName ?>" id="<?= $googleAnalyticsCodeName ?>"
                                      rows="10" cols="50"
                                      class="large-text code"><?= stripslashes($googleAnalyticsCodeVal) ?></textarea>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <hr>

    <div class="submit">
        <input type="button" id="xola-save-settings" name="Submit" class="button-primary"
               value="<?php esc_attr_e('Save Changes') ?>"/>

        <div class="sk-fading-circle hide" id="xola-save-settings-spinner">
            <div class="sk-circle1 sk-circle"></div>
            <div class="sk-circle2 sk-circle"></div>
            <div class="sk-circle3 sk-circle"></div>
            <div class="sk-circle4 sk-circle"></div>
            <div class="sk-circle5 sk-circle"></div>
            <div class="sk-circle6 sk-circle"></div>
            <div class="sk-circle7 sk-circle"></div>
            <div class="sk-circle8 sk-circle"></div>
            <div class="sk-circle9 sk-circle"></div>
            <div class="sk-circle10 sk-circle"></div>
            <div class="sk-circle11 sk-circle"></div>
            <div class="sk-circle12 sk-circle"></div>
        </div>
    </div>

    <input type="hidden" name="action" value="settings">
    <?php wp_nonce_field(XOLA_NONCE_ACTION, XOLA_NONCE_ACTION); ?>
</form>