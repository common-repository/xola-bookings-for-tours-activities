<?php


namespace xola;


class XolaData
{
    protected static $tableName = 'xola_listings';
    protected static $wpdb;
    protected static $mysqli;
    const BATCH_SIZE = 9;

    function __construct()
    {
        global $wpdb;

        self::$wpdb = $wpdb;

        self::$tableName = self::$wpdb->prefix . self::$tableName;

        $host = DB_HOST;
        //removing port info from host name
        if(strrpos(DB_HOST,":") !== false) {
            $host = substr(DB_HOST,0, strrpos(DB_HOST,":"));
        }

        self::$mysqli = new \mysqli($host, DB_USER, DB_PASSWORD, DB_NAME);
    }

    static function createTables()
    {
        $charsetCollate = self::$wpdb->get_charset_collate();
        $tableName = self::$tableName;

        $sql = "CREATE TABLE `{$tableName}` (
              `id` varchar(30) NOT NULL DEFAULT '',
              `data` mediumtext NOT NULL,
              `slug` varchar(255) NOT NULL,
              `price` int(11) DEFAULT NULL COMMENT 'Zero-decimal currencies: amount in cents. For example, for amount of $1.00, you would set price to 100 (100 cents)...',
              `connected` tinyint(4) DEFAULT '0',
              `show_on_discovery` tinyint(4) DEFAULT '0',
              `timestamp` int(11) DEFAULT NULL,
              `is_new` tinyint(1) DEFAULT 0,
              `timeline_checkout_button_id` varchar(64),
              `gift_checkout_button_id` varchar(64),
              PRIMARY KEY (`id`),
              KEY `slug` (`slug`),
              KEY `connected` (`connected`),
              KEY `price` (`price`)
            ) ENGINE=MyISAM $charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    static function dropTable()
    {
        $tableName = self::$tableName;
        $sql = "DROP TABLE IF EXISTS `{$tableName}`";
        self::$wpdb->query($sql);
    }

    static function insertNewData()
    {
        $listingData = XolaAccount::getAvailableListings();

        $oldIds = self::getAllListingIds();
        $ids = array();

        if (!empty($listingData) && isset($listingData['data']) && !empty($listingData['data'])) {
            $listings = $listingData['data'];

            $newIds = self::checkForNewListings($listings, $oldIds);

            foreach ($listings as $listing) {

                $isNew = in_array($listing['id'], $newIds);
                $fullListingDBData = self::getFullListingData($listing['id']);

                $timelineButtonId = null;
                if ($isNew || (!empty($fullListingDBData->id) && empty($fullListingDBData->timeline_checkout_button_id))) {
                    $timelineButtonId = XolaAccount::createCheckoutButton($listing, "timeline");
                } else {
                    $timelineButtonId = $fullListingDBData->timeline_checkout_button_id;
                }

                $giftButtonId = null;
                if ($isNew || (!empty($fullListingDBData->id) && empty($fullListingDBData->gift_checkout_button_id))) {
                    $giftButtonId = XolaAccount::createCheckoutButton($listing, "gift");
                } else {
                    $giftButtonId = $fullListingDBData->gift_checkout_button_id;
                }

                $data = array(
                    'id'                          => $listing['id'],
                    'data'                        => serialize($listing),
                    'slug'                        => sanitize_title($listing['name']),
                    'price'                       => self::preparePrice($listing['price']),
                    'timestamp'                   => time(),
                    'is_new'                      => $isNew,
                    'timeline_checkout_button_id' => $timelineButtonId,
                    'gift_checkout_button_id'     => $giftButtonId,
                );

                $ids[] = $listing['id'];

                // insert into db
                self::insert($data);
            }

            if (!empty($ids)) {
                self::removeOldListings($oldIds, $ids);
            }

            self::syncListings();
        }
    }

    static function insert($data)
    {
        self::$wpdb->replace(self::$tableName, $data);
    }

    static function removeOldListings($oldIds, $newIds)
    {
        $diff = array_diff($oldIds, $newIds);

        if (!empty($diff)) {
            foreach ($diff as $item) {
                $timelineButtonId = XolaData::fetchTimelineButtonId($item);
                $giftButtonId = XolaData::fetchGiftButtonId($item);
                self::$wpdb->delete(self::$tableName, array('id' => $item));
                XolaAccount::deleteCheckoutButton($timelineButtonId);
                XolaAccount::deleteCheckoutButton($giftButtonId);
            }
        }
    }

    static function checkForNewListings($listings, $oldIds)
    {
        $ids = array();

        foreach ($listings as $listing) {
            $ids[] = $listing['id'];
        }

        return array_diff($ids, $oldIds);
    }

    static function connectListings($listings, $connect = true)
    {
        if (!empty($listings)) {
            foreach ($listings as $listing) {
                self::connectListing($listing, $connect);
            }
            // Update options
            $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
            update_option($xolaVisibleListingsName, self::getConnectedListingIds());
        }

    }

    static function syncListingsByListingIds($listings)
    {
        if (!empty($listings)) {
            $allListings = self::getAllListingIds();
            $disconnectIds = array_diff($allListings, $listings);

            // disconnect
            foreach ($disconnectIds as $listingId) {
                self::connectListing($listingId, false);
                self::connectDiscoveryListing($listingId, false);
            }

            // connect selected listings
            foreach ($listings as $listingId) {
                self::connectListing($listingId, true);
                self::connectDiscoveryListing($listingId, true);
            }

            // Update options
            $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
            update_option($xolaVisibleListingsName, $listings);
        }
    }

    static function connectDiscoveryListing($id, $discovery = true)
    {
        self::$wpdb->update(self::$tableName, array('show_on_discovery' => intval($discovery)), array('id' => $id));
    }

    static function connectListing($id, $connect = true)
    {
        self::$wpdb->update(self::$tableName, array('connected' => intval($connect)), array('id' => $id));
    }

    static function syncListings()
    {
        $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
        $xolaVisibleListingsVal = get_option($xolaVisibleListingsName);

        $allIds = self::getAllListingIds();

        if (!empty($xolaVisibleListingsVal) && is_array($xolaVisibleListingsVal)) {
            if (!empty($allIds)) {
                foreach ($allIds as $id) {
                    $connect = false;
                    if (in_array($id, $xolaVisibleListingsVal)) {
                        $connect = true;
                    }

                    self::connectListing($id, $connect);
                }
            }
        }

        $xolaDiscoveryListingsName = XOLA_PREFIX . 'discovery_visible_listings';
        $xolaDiscoveryListingsVal = get_option($xolaDiscoveryListingsName);

        if (!empty($xolaDiscoveryListingsVal) && is_array($xolaDiscoveryListingsVal)) {
            if (!empty($allIds)) {
                foreach ($allIds as $id) {
                    $discovery = false;
                    if (in_array($id, $xolaDiscoveryListingsVal)) {
                        $discovery = true;
                    }

                    self::connectDiscoveryListing($id, $discovery);
                }
            }
        }

    }

    static function updateListing($id, $connect, $showOnDiscovery)
    {
        self::$wpdb->update(self::$tableName, array('connected' => intval($connect), 'show_on_discovery' => $showOnDiscovery, 'is_new' => 0), array('id' => $id));
    }

    static function updateListingById($id, $data)
    {
        $updated = self::$wpdb->update(self::$tableName, $data, array('id' => $id));

        $listing = false;
        if (booleanValue($updated)) {
            $listing = self::getListingsById($id);
        }

        return $listing;
    }

    static function getAllListingIds()
    {
        $ids = array();

        $tableName = self::$tableName;
        $sql = "SELECT `id` FROM `{$tableName}`";
        $results = self::$wpdb->get_results($sql);

        if (!empty($results)) {
            foreach ($results as $item) {
                $ids[] = $item->id;
            }
        }

        return $ids;
    }

    static function countListings()
    {
        $tableName = self::$tableName;
        $sql = "SELECT count(`id`) AS total, sum(`connected` = 1) AS `connected`, sum(`is_new` = 1) AS `new`
                FROM `{$tableName}`";
        $results = self::$wpdb->get_row($sql);

        $html = "<span class='available'>" . $results->total . " listings available</span> / " .
            "<span class='connected'>" . $results->connected . " listings connected</span> / " .
            "<span class='new'>" . $results->new . " listings new</span>";

        return $html;
    }

    static function getConnectedListingIds()
    {
        $ids = array();

        $tableName = self::$tableName;
        $sql = "SELECT `id` FROM `{$tableName}` WHERE `connected` = 1";
        $results = self::$wpdb->get_results($sql);

        if (!empty($results)) {
            foreach ($results as $item) {
                $ids[] = $item->id;
            }
        }

        return $ids;
    }


    static function getDiscoveryListingIds()
    {
        $ids = array();

        $tableName = self::$tableName;
        $sql = "SELECT `id` FROM `{$tableName}` WHERE `connected` = 1 AND `show_on_discovery` = 1";
        $results = self::$wpdb->get_results($sql);

        if (!empty($results)) {
            foreach ($results as $item) {
                $ids[] = $item->id;
            }
        }

        return $ids;
    }

    // CURRENTLY NOT IN USE
    // Note: $keyword should have 3+ letters
    static function searchByKeyword($keyword, $unserialize = false)
    {
        $tableName = self::$tableName;
        $sql = "SELECT *, MATCH(`search`) AGAINST('{$keyword}' IN BOOLEAN MODE) AS `weight`
                FROM `{$tableName}`
                WHERE MATCH(`search`) AGAINST('{$keyword}' IN BOOLEAN MODE)
                ORDER BY `weight` DESC";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);

        if ($unserialize) {
            $results = self::getUnserializedData($results);
        }

        return $results;
    }

    static function getAllListings($unserialize = false, $forceNewData = false)
    {
        if ($forceNewData) {
            self::insertNewData();
        }

        $tableName = self::$tableName;
        $sql = "SELECT * FROM `{$tableName}` ORDER BY `slug`";

        $results = self::$wpdb->get_results($sql);

        if ($unserialize) {
            $results = self::getUnserializedData($results);
        }

        return $results;
    }

    static function getListingsById($id, $unserialize = false)
    {
        $tableName = self::$tableName;

        if (is_array($id)) {
            $id = implode("','", $id);
            $sql = "SELECT * FROM `{$tableName}` WHERE `id` IN ('{$id}')";
            $sql = self::prepare($sql);
            $results = self::$wpdb->get_results($sql);
        } else {
            $sql = "SELECT * FROM `{$tableName}` WHERE `id` = '{$id}'";
            $sql = self::prepare($sql);
            $results = self::$wpdb->get_row($sql);
        }

        if ($unserialize) {
            $results = self::getUnserializedData($results);
        }

        return $results;
    }


    static function getNextListingBatch($page, $pagesize, $unserialize = false)
    {

        $tableName = self::$tableName;
        $sql = "SELECT * FROM `{$tableName}` ORDER BY `id` LIMIT " . intval($pagesize) . " OFFSET " . intval($page * $pagesize);

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);

        if ($unserialize) {
            $results = self::getUnserializedData($results);
        }

        return $results;
    }

    static function getConnectedListings($unserialize = false)
    {
        $tableName = self::$tableName;
        $sql = "SELECT * FROM `{$tableName}` WHERE `connected` = 1 ORDER BY `id`";

        $results = self::$wpdb->get_results($sql);

        if ($unserialize) {
            $results = self::getUnserializedData($results);
        }

        return $results;
    }

    static function getProductBySlug($slug, $unserialize = false)
    {
        $tableName = self::$tableName;
        $sql = "SELECT * FROM {$tableName} WHERE `slug` = '{$slug}' AND `connected`=1 LIMIT 1";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_row($sql);

        if (!count($results))
            return null;

        if ($unserialize) {
            $results = self::getUnserializedData($results);
        }

        return $results;
    }

    private static function getUnserializedData($data)
    {
        if (!empty($data) && is_array($data)) {
            foreach ($data as $item) {
                if (isset($item->data) && !empty($item->data)) {
                    $unserialized = unserialize($item->data);

                    $item->data = $unserialized;
                }
            }
        } else {
            if (isset($data->data) && !empty($data->data)) {
                $unserialized = unserialize($data->data);

                $data->data = $unserialized;
            }
        }

        return $data;
    }

    static function getImageUrl($url)
    {
        $coreUrl = substr($url, 0, strrpos($url, "?"));
        $coreUrl = str_replace("http:", "https:", $coreUrl);

        return $coreUrl . "?size=large";
    }


    static function getAvailableTimings($item)
    {

        if (!isset($item["schedules"])) {
            return "";
        }

        $schedules = $item["schedules"];

        $totalTimes = 0;
        $days = array();

        foreach ($schedules as $s) {

            if ($s["type"] != "available") {
                continue;
            }

            if (isset($s["days"])) {
                foreach ($s["days"] as $d) {
                    $days[] = $d;
                }

                if (isset($s["times"])) {
                    $totalTimes += count($s["days"]) * count($s["times"]);
                } else {
                    $totalTimes += count($s["days"]);
                }
            }
        }

        $days = implode(", ", array_unique($days));
        $days = str_replace("0", "Sun.", $days);
        $days = str_replace("1", "Mon.", $days);
        $days = str_replace("2", "Tue.", $days);
        $days = str_replace("3", "Wed.", $days);
        $days = str_replace("4", "Thu.", $days);
        $days = str_replace("5", "Fri.", $days);
        $days = str_replace("6", "Sat.", $days);

        $daytimearray = array("days" => $days, "times" => $totalTimes . " available times");

        if ($totalTimes == 0) {
            $daytimearray = array("days" => "", "times" => "Flexible Start Times");
        } 

        return $daytimearray;

    }

    static function getDuration($item)
    {
        $duration = $item["duration"];

        if ($duration == 1) {
            return "1 minute";
        } elseif ($duration <= 59) {
            return $duration . " minutes";
        }

        $duration = $duration / 60;

        if ($duration < 24) {
            if ($duration == 1)
                return "1 hour";
            else
                return $duration . " hours";
        } elseif ($duration == 24) {
            return "1 day";
        } else {
            return ($duration / 24) . " days";
        }
    }

    static function getPrice($item, $spanContainer = false)
    {
        $price = $item["currency"] . $item["price"] . "/" . $item["priceType"];

        if ($spanContainer) {
            $price = $item["currency"] . $item["price"] . "<span>/" . $item["priceType"] . "</span>";
        } else {
            $price = str_replace("person", "pers", $price);
        }

        $price = str_replace("USD", "$", $price);

        return $price;
    }

    // Zero-decimal currencies
    // format price from DB (cents) to human readable value
    static function getFormattedPrice($price, $currency = false)
    {
        $price = number_format($price / 100, 2);

        if ($currency) {
            $price = XOLA_DEFAULT_CURRENCY . $price;
        }

        return $price;
    }

    // Zero-decimal currencies
    // prepare price to be saved to DB - price in cents
    static function preparePrice($price)
    {
        return intval($price) * 100;
    }

    static function getListingsTableHtml($forceNewData = false)
    {
        $listings = XolaData::getAllListings(true, $forceNewData);

        $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
        $xolaVisibleListingsVal = get_option($xolaVisibleListingsName);
        $xolaVisibleListingsVal = empty($xolaVisibleListingsVal) ? array() : $xolaVisibleListingsVal;

        ob_start();
        if (!empty($listings)): ?>
            <table class="listings">
                <tbody>
                <tr class="listings-header">
                    <td colspan="3">
                        <a href="#" id="listings-selection" class="no-outline">Select All</a>
                    </td>
                </tr>

                <?php foreach ($listings as $listing):
                    $listingData = $listing->data; ?>
                    <tr class="listing-row">
                        <td>
                            <?php
                            $pic = XolaData::getImageUrl($listingData["medias"][0]["src"]);

                            if (!empty($pic)): ?>
                                <img src="<?= $pic; ?>">
                            <?php endif; ?>
                        </td>
                        <td><?= $listingData['name'] ?></td>
                        <td class="text-right">
                            <input type="checkbox" class="listing-checkbox hidden hide"
                                   value="<?= $listingData['id'] ?>"
                                   name="<?= $xolaVisibleListingsName ?>[]" id="listing_<?= $listingData['id'] ?>">

                            <?php if (in_array($listingData['id'], $xolaVisibleListingsVal)): ?>
                                <p><span>&#10004;</span> Synced</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        <?php else: ?>
            <p>No listings found.</p>
        <?php endif;

        return ob_get_clean();
    }

    static function getListingsHtml($forceNewData = false, $navButtons = true)
    {
        $listings = XolaData::getAllListings(true, $forceNewData);

        $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
        $xolaVisibleListingsVal = get_option($xolaVisibleListingsName);
        $xolaVisibleListingsVal = empty($xolaVisibleListingsVal) ? array() : $xolaVisibleListingsVal;

        ob_start();
        if (!empty($listings)): ?>
            <div class="listings">
                <div class="listings-header">
                    <a href="#" id="listings-selection" class="no-outline">Select All</a>
                </div>

                <ul>
                    <?php foreach ($listings as $listing):
                        $listingData = $listing->data;
                        $newClass = booleanValue($listing->is_new) ? ' new' : '';

                        $selected = (in_array($listingData['id'], $xolaVisibleListingsVal)) ? true : false;

                        $selectedClass = $selected ? 'selected' : '';
                        ?>
                        <li class="listing-row <?= $newClass ?> <?= $selectedClass ?>">
                            <div class="listing-row-wrap">
                                <div class="col-xs-2">
                                    <div class="listing-cell img-holder">
                                        <?php
                                        $pic = XolaData::getImageUrl($listingData["medias"][0]["src"]);

                                        if (!empty($pic)): ?>
                                            <img src="<?= $pic; ?>">
                                            <span class="dashicons dashicons-yes"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-xs-7">
                                    <div class="listing-cell">
                                        <p class="title"><?= $listingData['name'] ?></p>
                                    </div>
                                </div>

                                <div class="col-xs-3 text-right">
                                    <?php if ($listing->is_new): ?>
                                        <div class="listing-cell inline-block p-0">
                                            <p class="new"><span>&#10004;</span> New</p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="listing-cell inline-block p-0 mr-3">
                                        <input type="checkbox" class="listing-checkbox hidden hide"
                                               value="<?= $listingData['id'] ?>"
                                               name="<?= $xolaVisibleListingsName ?>[]"
                                               id="listing_<?= $listingData['id'] ?>"
                                               <?php if($selectedClass) echo 'checked'; ?> >
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

            </div>
            <div class="listings-footer">
                <?php if ($navButtons): ?>
                    <div id="nav-buttons" class="col-xs-12 text-right mt-3">
                        <button type="button" id="ob-back-to-step-1" class="cancel-listings xola-button large button-white">
                            Cancel
                        </button>
                        <button type="button" id="ob-go-to-step-2" class="next-listings xola-button large button-blue">
                            Next
                        </button>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p>No listings found.</p>
            <div class="listings-footer">
                <?php if ($navButtons): ?>
                    <div id="nav-buttons" class="col-xs-12 text-right mt-3">
                        <button type="button" id="ob-back-to-step-1" class="cancel-listings xola-button large button-white">
                            Cancel
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif;

        return ob_get_clean();
    }


    static function getSettingsListingsHtml($forceNewData = false)
    {
        $listings = XolaData::getAllListings(true, $forceNewData);

        $xolaVisibleListingsName = XOLA_PREFIX . 'visible_listings';
        $xolaVisibleListingsVal = get_option($xolaVisibleListingsName);
        $xolaVisibleListingsVal = empty($xolaVisibleListingsVal) ? array() : $xolaVisibleListingsVal;

        $xolaDiscoveryPageLabelName = XOLA_PREFIX . 'discovery_page_label';
        $discovery = get_option($xolaDiscoveryPageLabelName);
        if (empty($discovery)) {
            $discovery = XOLA_DISCOVERY_DEFAULT;
        }

        $discoveryEnabledName = XOLA_PREFIX . 'discovery_page_toggle';
        $discoveryEnabled = get_option($discoveryEnabledName);

        ob_start();
        if (!empty($listings)): ?>
            <div class="listings">
                <ul>
                    <?php foreach ($listings as $listing):
                        $listingData = $listing->data;
                        $newClass = booleanValue($listing->is_new) ? ' new' : '';
                        $productPageActive = $listing->connected;
                        $discoveryPageActive = $listing->show_on_discovery;

                        $localListingData = self::getListingsById($listing->id);
                        ?>
                        <li class="listing-row <?= $newClass ?>">
                            <div class="listing-row-wrap">
                                <div class="listing">
                                    <div class="listing-cell img-holder">
                                        <?php
                                        $pic = XolaData::getImageUrl($listingData["medias"][0]["src"]);

                                        if (!empty($pic)): ?>
                                            <img src="<?= $pic; ?>">
                                            <span class="dashicons dashicons-yes"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="listing-cell">
                                        <p class="title"><?= $listingData['name'] ?></p>
                                    </div>
                                    <?php if ($listing->is_new): ?>
                                        <div class="listing-cell inline-block p-0">
                                            <p class="new">New</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="show">
                                    <div class="listing-cell inline-block p-0">
                                        <a href="#"
                                           class="toggle-listing toggle-product-page <?php if ($productPageActive) echo 'active'; ?>"
                                           data-id="<?php echo $listing->id; ?>"
                                           data-action="<?php echo abs($productPageActive - 1); ?>">Product Page</a>
                                        <a href="#"
                                               class="toggle-listing toggle-discovery-page <?php if ($discoveryPageActive) echo 'active'; ?> <?php if (!$discoveryEnabled || !$productPageActive) echo 'hide'; ?>"
                                               data-id="<?php echo $listing->id; ?>"
                                               data-action="<?php echo abs($discoveryPageActive - 1); ?>">Discovery
                                                Page</a>

                                    </div>
                                </div>
                                <div class="url">
                                    <div class="listing-cell inline-block p-1">
                                        <?php if (in_array($listingData['id'], $xolaVisibleListingsVal)): ?>
                                            <?php $listingUrl = XolaData::getRawListingUrl($listingData['id']); ?>
                                            <a id="listing-url-<?= $listingData['id'] ?>" target="_blank"
                                               href="<?= $listingUrl; ?>"><?= '/' . $localListingData->slug ?></a>

                                            <a id="listing-slug-<?= $listingData['id'] ?>" class="listing-slug"
                                               data-type="text" data-id="<?= $listingData['id'] ?>"
                                               data-value="<?= $localListingData->slug ?>"
                                               href="<?= $listingUrl; ?>"><?= '/' . $localListingData->slug ?></a>

                                            <a href="#" class="edit-slug"
                                               data-target="#listing-slug-<?= $listingData['id'] ?>"
                                               data-url="#listing-url-<?= $listingData['id'] ?>">edit</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

            </div>
            <div class="listings-footer">
            </div>

        <?php else: ?>
            <p>No listings found.</p>
        <?php endif;

        return ob_get_clean();
    }


    // Date filter
    static function getListingsByDate($startDate, $endDate, $format = 'Y-m-d')
    {
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);

        $args = array('start_date' => $startDate->getTimestamp(), 'end_date' => $endDate->getTimestamp());

        $results = XolaAccount::getListingsByFilter('date', $args);

        $ids = array();
        $listings = array();
        if (!empty($results)) {
            foreach ($results as $experience) {
                $ids[] = $experience['experience']['id'];
            }

            $ids = array_unique($ids);

            $allListings = self::getConnectedListings(true);
            foreach ($allListings as $listing) {
                if (in_array($listing->id, $ids)) {
                    $listings[] = $listing;
                }
            }
        }

        return $listings;
    }

    // Number of passengers filter
    static function getListingIdsByAvailability($passengers, $startDate = null, $endDate = null, $format = 'Y-m-d')
    {
        $allIds = self::getDiscoveryListingIds();

        $args = array('ids' => $allIds);

        if (isset($startDate) && !empty($startDate)) {
            $args['start_date'] = $startDate;
        }

        if (isset($endDate) && !empty($endDate)) {
            $args['end_date'] = $endDate;
        }

        $results = XolaAccount::getListingsByFilter('availability', $args);

        $ids = array();

        if (!empty($results)) {
            foreach ($results as $id => $availableDates) {
                if (!empty($availableDates)) {
                    $found = false;
                    foreach ($availableDates as $time => $availability) {
                        if (!empty($availability)) {
                            foreach ($availability as $item) {
                                if (intval($item) >= intval($passengers)) {
                                    $found = true;
                                    $ids[] = $id;
                                    break;
                                }
                            }
                        }
                        if ($found) {
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($ids)) {
            $ids = array_unique($ids);

            return $ids;
        }

        return false;
    }

    /**
     * $filters can have: "passengers", "start_date", "end_date", "price", "keyword", "format"
     *
     * @param $filters
     * @return array|bool|mixed|null|object
     */
    static function getListingsByFilters($filters)
    {
        if (isset($filters) && !empty($filters)) {
            $passengers = null;
            $startDate = null;
            $endDate = null;

            if (isset($filters['passengers']) && !empty($filters['passengers'])) {
                $passengers = intval(trim($filters['passengers']));
            }

            if (isset($filters['format']) && !empty($filters['format'])) {
                $format = $filters['format'];
            }

            if (isset($filters['start_date']) && !empty($filters['start_date'])) {
                $startDate = trim($filters['start_date']);
            }

            if (isset($filters['end_date']) && !empty($filters['end_date'])) {
                $endDate = trim($filters['end_date']);
            }

            // build query with price and keyword
            $tableName = self::$tableName;
            $where = '';

            if (!empty($passengers)) {
                $ids = self::getListingIdsByAvailability($passengers, $startDate, $endDate, $format);
            } else {
                $ids = self::getDiscoveryListingIds();
            }

            // if empty then no results are found
            if (!empty($ids)) {
                $ids = implode("','", $ids);
                $where .= "`id` IN ('{$ids}')";
            } else {
                $where = "`id` = 0";
            }

            $priceOrder = "`price` DESC";
            if (isset($filters['price']) && !empty($filters['price'])) {
                if (trim($filters['price']) == 'asc') {
                    $priceOrder = "`price` ASC";
                }
            }

            if (!empty($where)) {
                $where = " WHERE " . $where;
            }

            $sql = "SELECT * FROM `{$tableName}` {$where} ORDER BY {$priceOrder}";

            $sql = self::prepare($sql);

            $results = self::$wpdb->get_results($sql);

            $results = self::getUnserializedData($results);

            return $results;
        }

        return false;
    }


    static function parseUrlParams()
    {

        $passengers = null;
        $start_date = null;
        $end_date = null;
        $price = null;
        $keyword = null;

        if (isset($_GET['passengers'])) {
            $passengers = $_GET['passengers'];
        }

        if (isset($_GET['start_date'])) {
            $start_date = $_GET['start_date'];
        }

        if (isset($_GET['end_date'])) {
            $end_date = $_GET['end_date'];
        }

        if (isset($_GET['keyword'])) {
            $keyword = $_GET['keyword'];
        }

        if (isset($_GET['price'])) {
            $price = $_GET['price'];
        }

        return array("passengers" => $passengers, "start_date" => $start_date, "end_date" => $end_date, "price" => $price, "keyword" => $keyword);

    }


    static function parsePostParams()
    {

        $passengers = null;
        $start_date = null;
        $end_date = null;
        $price = null;
        $keyword = null;

        if (isset($_POST['passengers'])) {
            $passengers = $_POST['passengers'];
        }

        if (isset($_POST['start_date'])) {
            $start_date = $_POST['start_date'];
        }

        if (isset($_POST['end_date'])) {
            $end_date = $_POST['end_date'];
        }

        if (isset($_POST['keyword'])) {
            $keyword = $_POST['keyword'];
        }

        if (isset($_POST['price'])) {
            $price = $_POST['price'];
        }

        return array("passengers" => $passengers, "start_date" => $start_date, "end_date" => $end_date, "price" => $price, "keyword" => $keyword);

    }

    private static function prepare($string)
    {
        $string = htmlspecialchars($string);
        $string = str_replace("\n", '', $string);

        $string = mysqli_real_escape_string(self::$mysqli, $string);
        $string = stripslashes($string);

        return $string;
    }


    static function getListingUrl($listing)
    {

        $xolaDiscoveryPageLabelName = XOLA_PREFIX . 'discovery_page_label';
        $discovery = get_option($xolaDiscoveryPageLabelName);
        if (empty($discovery)) {
            $discovery = XOLA_DISCOVERY_DEFAULT;
        }

        $url = get_site_url() . "/" . $discovery . "/" . $listing->slug . "/";

        return $url;
    }


    static function getLatestTimestamp()
    {
        $tableName = self::$tableName;
        $sql = "SELECT max(timestamp) as 'latest' FROM `{$tableName}`";
        $results = self::$wpdb->get_results($sql);

        if (!count($results)) {
            return 0;
        }

        return date("m/d/Y H:i A", $results[0]->latest);
    }


    static function getRawListingUrl($listingId)
    {
        $tableName = self::$tableName;
        $sql = "SELECT slug FROM `{$tableName}` WHERE `id`='" . $listingId . "'";
        $results = self::$wpdb->get_results($sql);

        if (!count($results)) {
            return "";
        }

        $slug = $results[0]->slug;

        return self::buildUrlFromSlug($slug);
    }

    static function buildUrlFromSlug($slug)
    {
        $xolaDiscoveryPageLabelName = XOLA_PREFIX . 'discovery_page_label';
        $discovery = get_option($xolaDiscoveryPageLabelName);
        if (empty($discovery)) {
            $discovery = XOLA_DISCOVERY_DEFAULT;
        }

        return get_site_url() . '/' . $discovery . '/' . $slug . '/';
    }

    static function getRelatedListings($listingId)
    {

        $tableName = self::$tableName;

        $ids = self::getDiscoveryListingIds();
        $ids = implode("','", $ids);

        $sql = "SELECT *
                FROM `{$tableName}`
                WHERE id!='{$listingId}'
                AND   id in ('{$ids}')
                ORDER BY RAND()
                LIMIT 3";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);
        $results = self::getUnserializedData($results);

        return $results;
    }

    static function fetchTimelineButtonId($listingId)
    {
        $tableName = self::$tableName;

        $sql = "SELECT timeline_checkout_button_id
                FROM `{$tableName}`
                WHERE id='{$listingId}'";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);
        $results = self::getUnserializedData($results);

        if (count($results)) {
            return $results[0]->timeline_checkout_button_id;
        } else {
            return null;
        }
    }


    static function fetchGiftButtonId($listingId)
    {
        $tableName = self::$tableName;

        $sql = "SELECT gift_checkout_button_id
                FROM `{$tableName}`
                WHERE id='{$listingId}'";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);
        $results = self::getUnserializedData($results);

        if (count($results)) {
            return $results[0]->gift_checkout_button_id;
        } else {
            return null;
        }
    }


    static function fetchTimlineButtons()
    {
        $tableName = self::$tableName;
        $sql = "SELECT id, timeline_checkout_button_id
                FROM `{$tableName}`";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);
        $results = self::getUnserializedData($results);

        return $results;
    }


    static function sanitizeString($string)
    {
        $string = trim($string);
        $string = htmlentities($string);
        $string = strip_tags($string);

        return $string;
    }

    static function formatDateForApiCall($date)
    {
        list($month, $day, $year) = explode("/", $date);

        return date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
    }

    static function registerWebhook($eventName)
    {
        $hookId = get_option(XOLA_PREFIX . "webhook_id_" . $eventName);
        if (!$hookId) {
            XolaAccount::registerWebhook($eventName);
        }
    }

    static function getFullListingData($listingId)
    {
        $tableName = self::$tableName;
        $sql = "SELECT * FROM `{$tableName}` WHERE id='{$listingId}'";

        $sql = self::prepare($sql);
        $results = self::$wpdb->get_results($sql);
        $results = self::getUnserializedData($results);

        if (count($results))
            return $results[0];
        else
            return null;
    }

}

new XolaData;