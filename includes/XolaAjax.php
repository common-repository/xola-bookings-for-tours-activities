<?php


namespace xola;


class XolaAjax
{

    function __construct()
    {
        add_action('wp_head', array($this, 'addAjaxUrlCallback'));
        add_action('admin_footer', array($this, 'addAjaxUrlCallback'));

        add_action('wp_ajax_filterData', array($this, 'filterData'));

        add_action('wp_ajax_forceInsertNewData', array($this, 'forceInsertNewData'));

        add_action('wp_ajax_syncListings', array($this, 'syncListings'));

        add_action('wp_ajax_obSyncListings', array($this, 'obSyncListings'));

        add_action('wp_ajax_syncListingsTable', array($this, 'syncListingsTable'));

        add_action('wp_ajax_obChangeStep', array($this, 'obChangeStep'));

        add_action('wp_ajax_obCancelOnboarding', array($this, 'disconnectAccount'));

        add_action('wp_ajax_disconnectAccount', array($this, 'disconnectAccount'));

        add_action('wp_ajax_toggleListing', array($this, 'toggleListing'));

        add_action('wp_ajax_changeListingSlug', array($this, 'changeListingSlug'));

        add_action('wp_ajax_changeDiscoveryPageUrl', array($this, 'changeDiscoveryPageUrl'));

        add_action('wp_ajax_saveSettings', array($this, 'saveSettings'));
    }

    static function addAjaxUrlCallback()
    {
        ob_start(); ?>
        <script>var AJAXURL = '<?= admin_url('admin-ajax.php'); ?>';</script>
        <?php echo ob_get_clean();
    }

    static function filterData()
    {
        $params = XolaData::parsePostParams();
        $data = XolaData::getListingsByFilters($params);

        if (count($data)) {
            foreach ($data as $item) {
                $item->data["formatted_duration"] = XolaData::getDuration($item->data);
                $item->data["formatted_image"] = XolaData::getImageUrl($item->data["medias"][0]["src"]);
                $item->data["formatted_url"] = XolaData::getListingUrl($item);
                $timings = XolaData::getAvailableTimings($item->data);
                $item->data["formatted_times_days"] = $timings["days"];
                $item->data["formatted_times_hours"] = $timings["times"];
                $item->data["formatted_price"] = XolaData::getPrice($item->data);
            }

            $output = array(
                'success' => true,
                'data'    => $data,
            );
        } else {
            $output = array(
                'success' => false,
                'data'    => $data,
            );
        }

        echo json_encode($output);
        exit;
    }

    static function forceInsertNewData()
    {
        XolaData::insertNewData();
        $html = XolaData::getSettingsListingsHtml();

        $output = array(
            'success'     => true,
            'html'        => $html,
            'last_synced' => XolaData::getLatestTimestamp(),
        );

        self::transmit($output);
    }

    static function syncListings()
    {
        $errors = array();

        $listings = $_REQUEST['listings'];

        switch ($_REQUEST['job']) {
            case 'sync':
                $connect = true;
                break;
            case 'unsync':
                $connect = false;
                break;
            default:
                $errors[] = 'Job name missing.';
                break;
        }

        if (empty($errors)) {
            XolaData::connectListings($listings, $connect);

            $html = XolaData::getListingsHtml();
            $counts = XolaData::countListings();

            $output = array(
                'success' => true,
                'html'    => $html,
                'counts'  => $counts,
            );
        } else {
            $output = array(
                'success' => false,
                'errors'  => $errors,
            );
        }

        self::transmit($output);
    }

    static function obSyncListings()
    {
        $errors = array();

        $listings = $_REQUEST['listings'];

        if (empty($errors)) {
            XolaData::syncListingsByListingIds($listings);

            $output = array(
                'success' => true,
            );
        } else {
            $output = array(
                'success' => false,
                'errors'  => $errors,
            );
        }

        self::transmit($output);
    }

    static function syncListingsTable()
    {
        $errors = array();

        $listings = $_REQUEST['listings'];

        switch ($_REQUEST['job']) {
            case 'sync':
                $connect = true;
                break;
            case 'unsync':
                $connect = false;
                break;
            default:
                $errors[] = 'Job name missing.';
                break;
        }

        if (empty($errors)) {
            XolaData::connectListings($listings, $connect);

            $html = XolaData::getListingsTableHtml();

            $output = array(
                'success' => true,
                'html'    => $html,
            );
        } else {
            $output = array(
                'success' => false,
                'errors'  => $errors,
            );
        }

        self::transmit($output);
    }

    static function obChangeStep()
    {
        $step = $_REQUEST['step'];

        if (isset($step) && !empty($step) && is_numeric($step)) {
            $step = intval($step);

            switch ($step) {
                case 1:
                case 2:
                    update_option(XOLA_PREFIX . 'onboarding_step', $step);
                    break;
                case -1:

                    if (isset($_REQUEST['layout'])) {
                        $layout = trim($_REQUEST['layout']);

                        if ($layout === 'list' || $layout === 'grid') {
                            $xolaDiscoveryLayoutName = XOLA_PREFIX . 'discovery_page_layout';

                            update_option($xolaDiscoveryLayoutName, $layout);
                        }
                    }

                    if (isset($_REQUEST['discovery'])) {
                        $discovery = $_REQUEST['discovery'];
                        $xolaDiscoveryToggle = XOLA_PREFIX . 'discovery_page_toggle';
                        update_option($xolaDiscoveryToggle, $discovery);
                    }

                    update_option(XOLA_PREFIX . 'onboarding', true);
                    break;
            }

            $output = array(
                'success' => true,
            );
        } else {
            $output = array(
                'success' => false,
                'errors'  => array('Step param missing.'),
            );
        }

        self::transmit($output);
    }

    static function disconnectAccount()
    {
        // Remove listings data from DB
        XolaData::dropTable();
        XolaAccount::deleteWebhook("create");
        XolaAccount::deleteWebhook("update");
        XolaAccount::deleteWebhook("delete");

        // Remove username and password
        delete_option(XOLA_PREFIX . 'account_email');
        delete_option(XOLA_PREFIX . 'account_password');

        // Remove onboarding data
        delete_option(XOLA_PREFIX . 'onboarding_pending');
        delete_option(XOLA_PREFIX . 'onboarding_step');
        delete_option(XOLA_PREFIX . 'onboarding');

        self::transmit(array(
            'success' => true,
        ));
    }


    static function toggleListing()
    {

        $errors = array();

        $id = $_REQUEST['id'];
        $productAction = $_REQUEST['productAction'];
        $discoveryAction = $_REQUEST['discoveryAction'];

        XolaData::updateListing($id, $productAction, $discoveryAction);

        $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
        update_option($xolaVisibleListingsName, XolaData::getConnectedListingIds());
        $xolaDiscoveryListingsName = XOLA_PREFIX . 'discovery_visible_listings';
        update_option($xolaDiscoveryListingsName, XolaData::getDiscoveryListingIds());

        if (empty($errors)) {
            $html = XolaData::getSettingsListingsHtml();

            $output = array(
                'success' => true,
                'html'    => $html,
            );
        } else {
            $output = array(
                'success' => false,
                'errors'  => $errors,
            );
        }

        self::transmit($output);
    }

    static function changeListingSlug()
    {
        $success = false;
        $data = array();

        if (isset($_REQUEST['id']) && !empty($_REQUEST['id']) && isset($_REQUEST['slug']) && !empty($_REQUEST['slug'])) {
            $id = $_REQUEST['id'];

            $slug = trim($_REQUEST['slug'], ' /');

            $listing = XolaData::updateListingById($id, array('slug' => $slug));

            if ($listing) {
                $data = array(
                    'url'  => XolaData::buildUrlFromSlug($listing->slug),
                    'slug' => $listing->slug,
                );
            }

            $success = true;
        }

        self::transmit(array(
            'success' => $success,
            'data'    => $data,
        ));
    }

    static function changeDiscoveryPageUrl()
    {
        $success = false;
        $data = array();

        if (isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {

            $slug = trim($_REQUEST['url'], ' /');

            update_option(XOLA_PREFIX . 'discovery_page_label', $slug);
            XolaPlugin::replaceDiscoveryPageUrlInNav(get_option(XOLA_PREFIX . 'discovery_page_label'), $slug);
            XolaPlugin::flushingRules();

            $data = array(
                'url'  => "/" . $slug,
                'slug' => $slug,
            );

            $success = true;
        }

        self::transmit(array(
            'success' => $success,
            'data'    => $data,
        ));
    }

    static function saveSettings()
    {
        parse_str($_POST['data'], $data);

        XolaPluginAdminMenu::saveSettingsAjax($data);

        self::transmit(array(
            'success' => true,
        ));
    }

    private static function transmit($output)
    {
        echo json_encode($output);
        exit;
    }
}

new XolaAjax;