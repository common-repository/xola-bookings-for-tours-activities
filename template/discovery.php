<?php use \xola\XolaData;

$params = XolaData::parseUrlParams();
$passengers = $params["passengers"];
$start_date = $params["start_date"];
$end_date = $params["end_date"];
$keyword = $params["keyword"];
$price = $params["price"];

//clearing up parameters for DB fetch
$params["keyword"] = "";
$params["price"] = "";

$xolaDiscoveryPagePaginationCountName = XOLA_PREFIX . 'discovery_page_listing_count';

$listings = XolaData::getListingsByFilters($params);
$totalItemCount = count($listings);

$searchIndex = array();

$gridTypeVar = XOLA_PREFIX . 'discovery_page_layout';
$gridType = get_option($gridTypeVar);

$priceRangeFilterVar = XOLA_PREFIX . 'discovery_price_sort';
$priceRangeFilter = get_option($priceRangeFilterVar);
$dateRangeFilterVar = XOLA_PREFIX . 'discovery_date_range';
$dateRangeFilter = get_option($dateRangeFilterVar);
$guestCountFilterVar = XOLA_PREFIX . 'discovery_guest_count';
$guestCountFilter = get_option($guestCountFilterVar);

$googleAnalyticsCodeVar =  XOLA_PREFIX . 'google_analytics_code';
$googleAnalyticsCode = get_option($googleAnalyticsCodeVar);

$hideDuration = true;
$productSectionsOption = XOLA_PREFIX . 'product_visible_listings';
$productSections = get_option($productSectionsOption);
if(is_array($productSections)) {
    $productSections = array_flip($productSections);
    if(isset($productSections["duration"])) {
        $hideDuration = false;
    }
}


if(!$gridType)
    $gridType = XOLA_DISCOVERY_GRID;

?>

<?php get_header(); ?>

    <div class="container-fluid discovery-page <?php if(!$dateRangeFilter && !$guestCountFilter) echo 'no-filters'; ?>">

        <div class="col-md-12 toolbar searchtoolbar">
            <h1 class="title">Find an activity</h1>

            <div class="filters <?php if(!$dateRangeFilter) echo 'no-date-range-filter'; ?> <?php if(!$guestCountFilter) echo 'no-guest-count-filter'; ?>">
                <?php if($dateRangeFilter): ?>
                    <div class="left-cont">
                        <div class="top-label">When are you coming?</div>
                        <div class="date-span">
                            <span class="from">From</span>
                            <span class="arrow">&rarr;</span>
                            <span class="to">To</span>
                        </div>
                        <div class="top-label secret-input">
                            <input type="text" id="date_from_to" name="date_from"/>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="right-cont">
                    <div class="date-span">
                        <?php if($guestCountFilter): ?>
                            <div class="cont">
                                <div class="top-label">How many people?</div>
                                <div class="input-field">
                                    <input type="number" id="no_guests" min="0" />
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="cont">
                            <button onclick="submitRequest();">Search &rarr;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-container">

            <div class="col-xs-12 col-md-12 listings">
                <div class="toolbar <?php if(!$priceRangeFilter) echo 'no-price-filter'; ?>">
                    <div class="left-cont">
                        <h2>Available Activities</h2>
                        <?php if($priceRangeFilter): ?>
                            <a href="#" id="price-mobile" class="price_<?php if($price) echo $price; else echo "desc"; ?>" data-direction="<?php if($price) echo $price; else echo "desc"; ?>">Price</a>
                        <?php endif; ?>
                    </div>
                    <div class="right-cont">
                        <input type="text" id="filter_keyword" name="filter_keyword" placeholder="Filter by keyword" value="<?php echo $keyword; ?>"/>
                        <?php if($priceRangeFilter): ?>
                            <a href="#" id="price" class="price_<?php if($price) echo $price; else echo "desc"; ?>" data-direction="<?php if($price) echo $price; else echo "desc"; ?>">Price</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-12 text-center listing-content listing-<?php echo $gridType; ?>">
                <?php $index = 0; ?>
                <?php foreach($listings as $list): ?>
                    <?php $l = $list->data; ?>
                    <?php $times = XolaData::getAvailableTimings($l); ?>
                    <?php $duration = XolaData::getDuration($l); ?>
                    <?php $price = XolaData::getPrice($l); ?>
                    <?php $l["formatted_duration"] = $duration; ?>
                    <?php $l["formatted_image"] = XolaData::getImageUrl($l["medias"][0]["src"]); ?>
                    <?php $l["formatted_url"] = XolaData::getListingUrl($list); ?>
                    <?php $l["formatted_times_days"] = $times["days"]; ?>
                    <?php $l["formatted_times_hours"] = $times["times"]; ?>
                    <?php $l["formatted_price"] = $price; ?>
                    <?php $searchIndex[] = $l; ?>

                    <div class="col-xs-12 col-sm-6 col-md-4 item" id="<?php echo $l["id"]; ?>">
                        <a href="<?php echo $l["formatted_url"]; ?>" title="<?php echo $l["name"]; ?>">
                            <div class="item-content text-left">
                                <div class="image">
                                    <img src="<?php echo $l["formatted_image"]; ?>" alt="<?php echo $l["name"]; ?>" title="<?php echo $l["name"]; ?>" />
                                </div>
                                <div class="details">
                                    <h3><?php echo $l["name"]; ?></h3>
                                    <?php if($gridType == XOLA_DISCOVERY_GRID): ?>
                                        <p class="description grid-description">
                                            <?php echo $l["excerpt"]; ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="timings <?php if($hideDuration) echo 'hide-duration'; ?>">
                                        <div class="left">
                                            <p><?php echo $times["days"]; ?><p>
                                            <p><?php echo $times["times"]; ?></p>
                                        </div>
                                        <div class="right <?php if($hideDuration) echo 'hidden'; ?>">
                                            <p><?php echo $duration; ?></p>
                                        </div>
                                    </div>
                                    <?php if($gridType == XOLA_DISCOVERY_LIST): ?>
                                        <p class="description grid-description">
                                            <?php echo $l["excerpt"]; ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="price">
                                        <?php echo $price; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php $index++; ?>
                <?php endforeach; ?>
                <div class="col-xs-12 col-sm-6 col-md-4 item dummy-item" id="dummy-item">
                    <a href="#" title="">
                        <div class="item-content text-left">
                            <div class="image">
                            </div>
                            <div class="details">
                                <h3></h3>
                                <?php if($gridType == XOLA_DISCOVERY_GRID): ?>
                                    <p class="description grid-description">
                                    </p>
                                <?php endif; ?>
                                <div class="timings">
                                    <div class="left">
                                        <p></p>
                                        <p></p>
                                    </div>
                                    <div  class="right">
                                    </div>
                                </div>
                                <?php if($gridType == XOLA_DISCOVERY_LIST): ?>
                                    <p class="description grid-description">
                                    </p>
                                <?php endif; ?>
                                <div class="price">
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xs-12 col-md-12 logo-container">
                <div class="powered-by-xola">
                    <a href="https://www.xola.com?utm_source=wp&utm_medium=logo&utm_campaign=pb" target="_blank">
                        <img src="<?php echo plugins_url() . "/xola-bookings-for-tours-activities/assets/images/xola-logo.png"; ?>" />
                    </a>
                </div>
            </div>
        </div>

        <div class="ajax-spinner">
            <img src="<?php echo plugins_url() . "/xola-bookings-for-tours-activities/assets/images/ajax-loader.gif"; ?>" />
        </div>

    </div>

    <script>
        var infiniteScroll = false;
        var pageSize = 100;
        var shownItems = 0;
        var totalPages = 1;
        var mobileScreen = false;

        jQuery(document).ready(function() {

            var params = {};

            params["autoApply"] = true;

            <?php if($start_date): ?>
            params["startDate"] = '<?php echo $start_date; ?>';
            <?php endif; ?>
            <?php if($end_date): ?>
            params["endDate"] = '<?php echo $end_date; ?>';
            <?php endif; ?>

            if(jQuery('#date_from_to').length) {
                jQuery('#date_from_to').daterangepicker(params);
                if(params["startDate"] != undefined || params["endDate"] != undefined)
                    setLabels();
            }

            if(jQuery("#filter_keyword").val() != "") {
                filterListingsByKeyWord();
            }

            updatePlaceholder();
        });

        jQuery(window).resize(function() {
            updatePlaceholder();
        });

        jQuery(".filters span").click(function() {
            jQuery('#date_from_to').click();
        });

        jQuery('#date_from_to').on('apply.daterangepicker', function(ev, picker) {
            setLabels();
            submitRequest();
        });

        jQuery('#date_from_to').on('cancel.daterangepicker', function(ev, picker) {
            resetLabels();
        });

        jQuery('#date_from_to').on('outsideClick.daterangepicker', function(ev, picker) {
            setLabels();
            submitRequest();
        });

        jQuery('#no_guests').on('change', function(event) {
            submitRequest();
        });

        function updatePlaceholder() {
            if(jQuery(window).width() <= 820) {
                mobileScreen = true;
                jQuery("#no_guests").attr("placeholder", "How many people?");
            } else {
                mobileScreen = false;
                jQuery("#no_guests").attr("placeholder", "");
            }
        }

        function resetLabels() {
            jQuery(".filters .date-span .from").html("From");
            jQuery(".filters .date-span .to").html("To");
        }

        function setLabels() {
            var dateRange = jQuery('#date_from_to').val().split("-");

            var dateFrom = dateRange[0];
            var dateTo = dateRange[1];

            jQuery(".filters .date-span .from").html("From: " + dateFrom);
            jQuery(".filters .date-span .to").html("To: " + dateTo);
        }

        jQuery("#price").click(function(e) {
            e.preventDefault();

            if(jQuery(this).data("direction") == "desc") {
                jQuery(this).data("direction", "asc");
                jQuery("#price-mobile").data("direction", "asc");
            } else {
                jQuery(this).data("direction", "desc");
                jQuery("#price-mobile").data("direction", "desc");
            }

            jQuery(this).removeClass("price_asc").removeClass("price_desc").addClass("price_" + jQuery(this).data("direction"));

            reverseOrder();
        });

        jQuery("#price-mobile").change(function() {
            e.preventDefault();

            if(jQuery(this).data("direction") == "desc") {
                jQuery(this).data("direction", "asc");
                jQuery("#price").data("direction", "asc");
            } else {
                jQuery(this).data("direction", "desc");
                jQuery("#price").data("direction", "desc");
            }

            jQuery(this).removeClass("price_asc").removeClass("price_desc").addClass("price_" + jQuery(this).data("direction"));

            reverseOrder();
        });

        function submitRequest() {

            var coreUrl = window.location.href.split('?')[0];

            var glue = "?";

            var start_date = 'From';
            var end_date = 'To';

            var passengers = jQuery("#no_guests").val();

            if(jQuery("div.date-span span.from").length) {
                start_date = jQuery("div.date-span span.from").html().replace("From: ", "").trim();
            }

            if(jQuery("div.date-span span.to").length) {
                end_date = jQuery("div.date-span span.to").html().replace("To: ", "").trim();
            }

            var price = jQuery("#price").data("direction");
            if(passengers == "" && start_date != "" && end_date != "") {
                passengers = 1;
            }

            if(passengers > 1) {
                coreUrl = coreUrl + glue + "passengers=" + passengers;
                glue = "&"
            }

            if(start_date != 'From') {
                coreUrl = coreUrl + glue + "start_date=" + start_date;
                glue = "&"
            } else {
                start_date = '';
            }

            if(end_date != 'To') {
                coreUrl = coreUrl + glue + "end_date=" + end_date;
                glue = "&"
            } else {
                end_date = '';
            }

            jQuery(".discovery-page .ajax-spinner").show();
            purgeGrid();

            jQuery.post(AJAXURL, {action: "filterData", passengers: passengers, start_date: start_date, end_date: end_date, price: price },
                function (data) {
                    data = JSON.parse(data);

                    if (data.success === true) {
                        var items = data.data;
                        listings_json = new Array();
                        for (i = 0; i < items.length; i++) {
                            listings_json.push(items[i].data);
                        }
                    } else if (data.success === false) {
                        listings_json = new Array();
                    }

                    fillGrid();
                    jQuery(".discovery-page .ajax-spinner").hide();
                }
            );

        }
    </script>

<?php get_footer(); ?>

    <script>
        var listings_json = JSON.parse('<?php echo addslashes(json_encode($searchIndex)); ?>');
        var filtered_listings_json = JSON.parse('<?php echo addslashes(json_encode($searchIndex)); ?>');
        var search = '';

        jQuery(document).ready(function() {
            search = new JsSearch.Search('id');
            search.addIndex('name');
            search.addIndex('desc');
            search.addDocuments(listings_json);
        });
    </script>

<?php if(strlen($googleAnalyticsCode)): ?>
    <script>
        var google_analytics_loaded = false;
        var google_check_attempt = 0;

        function check_ga() {
            google_check_attempt = google_check_attempt + 1;

            if (typeof ga === 'function') {
                google_analytics_loaded = true;
                return;
            } else {
                if(google_check_attempt < 5) {
                    setTimeout(check_ga, 500);
                }
            }

            if(google_check_attempt == 5) {
                load_ga();
            }

        }

        function load_ga() {
            <?php echo stripslashes($googleAnalyticsCode); ?>
        }

        jQuery(document).ready(function(){ check_ga(); });
    </script>
<?php endif; ?>