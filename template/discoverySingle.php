<?php
use \xola\XolaData;
use \xola\XolaAccount;

$slug = get_query_var('xola_discovery_single_slug');
$fullListingData = XolaData::getProductBySlug($slug);

if(is_null($fullListingData)) {
    wp_redirect(home_url());
    exit;
}

$listing = unserialize($fullListingData->data);

$times = XolaData::getAvailableTimings($listing);
$sellerId = trim(XolaAccount::getSellerId(), '"');
$price = XolaData::getPrice($listing, true);
$duration = XolaData::getDuration($listing);

$relatedListings = XolaData::getRelatedListings($listing["id"]);

$buttonTitleOption = XOLA_PREFIX . 'product_button_text';
$buttonTitle = get_option($buttonTitleOption);
if(!$buttonTitle)
    $buttonTitle = "Book Now";

$checkoutTypeOption = XOLA_PREFIX . 'product_checkout_type';
$checkoutType = get_option($checkoutTypeOption);

$buttonStyleTypeOption = XOLA_PREFIX . 'product_button_style';
$buttonStyleType = get_option($buttonStyleTypeOption);
$buttonStyle = "";

if($buttonStyleType == "custom") {
    $buttonStyleOption = XOLA_PREFIX . 'product_button_style_custom_code';
    $buttonStyle = get_option($buttonStyleOption);
    $buttonStyle = trim(preg_replace("#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s","", $buttonStyle));
}

$buttonTypeOption = XOLA_PREFIX . "product_checkout_type";
$buttonType = get_option($buttonTypeOption);

$buttonTimelineId = $fullListingData->timeline_checkout_button_id;
$buttonGiftId = $fullListingData->gift_checkout_button_id;

$giftButtonUsedOption = XOLA_PREFIX . 'product_display_gift_button';
$giftButtonUsed = get_option($giftButtonUsedOption);

$giftbuttonTitleOption = XOLA_PREFIX . 'product_gift_button_text';
$giftButtonTitle = get_option($giftbuttonTitleOption);
if(!$giftButtonTitle)
    $giftButtonTitle = "Purchase as gift";

$giftButtonStyleTypeOption = XOLA_PREFIX . 'product_gift_button_style';
$giftButtonStyleType = get_option($giftButtonStyleTypeOption);
$giftButtonStyle = "";

if($giftButtonStyleType == "custom") {
    $giftButtonStyleOption = XOLA_PREFIX . 'product_gift_button_style_custom_code';
    $giftButtonStyle = get_option($giftButtonStyleOption);
    $giftButtonStyle = trim(preg_replace("#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s","", $giftButtonStyle));
}

$googleMapOption = XOLA_PREFIX . 'google_maps_api_key';
$googleMapKey = get_option($googleMapOption);

$hideDuration = true;
$productSectionsOption = XOLA_PREFIX . 'product_visible_listings';
$productSections = get_option($productSectionsOption);
if(is_array($productSections)) {
    $productSections = array_flip($productSections);
    if(isset($productSections["duration"])) {
        $hideDuration = false;
    }
}

$googleAnalyticsCodeVar =  XOLA_PREFIX . 'google_analytics_code';
$googleAnalyticsCode = get_option($googleAnalyticsCodeVar);

?>

<?php get_header(); ?>

<div class="container-fluid listing-page">
    <div class="left-area col-lg-8 col-md-7 col-sm-6">
        <div class="content-container listing-content">
            <div class="listing-details">
                <div class="description">
                    <h1 class="text-uppercase"><?php echo $listing["name"]; ?></h1>
                    <div class="timings">
                        <div class="days">
                            <?php echo $times["days"]; ?>
                        </div>
                        <div class="times <?php if($hideDuration) echo 'hidden'; ?>">
                            <?php echo $duration; ?>
                        </div>
                    </div>
                    <?php if(count($productSections) && isset($productSections["description"])): ?>
                        <p>
                            <?php echo $listing["desc"]; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if(count($productSections) && isset($productSections["whats_included"])): ?>
            <div class="content-container listing-content">
                <div class="listing-points <?php if(!count($listing["included"]) || !count($listing["notIncluded"])) echo 'single-line'; ?>">
                    <?php if(count($listing["included"])): ?>
                        <div class="included">
                            <h3 class="text-uppercase">What's included ?</h3>
                            <ul>
                                <?php foreach($listing["included"] as $included): ?>
                                    <li><span><?php echo $included; ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if(count($listing["notIncluded"])): ?>
                        <div class="bring">
                            <h3 class="text-uppercase">What to bring ?</h3>
                            <ul>
                                <?php foreach($listing["notIncluded"] as $included): ?>
                                    <li><span><?php echo $included; ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if(count($productSections) && isset($productSections["activity_location"])): ?>
            <div class="content-container listing-content">
                <div class="listing-map">
                    <h3 class="text-uppercase">Activity location</h3>
                    <div id="map-canvas"></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(count($productSections) && isset($productSections["other_considerations"]) && isset($listing["other"]) && strlen($listing["other"])): ?>
            <div class="content-container listing-content">
                <div class="listing-other">
                    <h3 class="text-uppercase">Other considerations</h3>
                    <div>
                        <p><?php echo $listing["other"]; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="powered-by-xola">
            <a href="https://www.xola.com?utm_source=wp&utm_medium=logo&utm_campaign=pb" target="_blank">
                <img src="<?php echo plugins_url() . "/xola-bookings-for-tours-activities/assets/images/xola-logo.png"; ?>" />
            </a>
        </div>
    </div>
    <div class="right-area col-lg-4 col-md-5 col-sm-6">
        <div class="hero-banner">
            <?php foreach($listing["medias"] as $image): ?>
                <div class="image">
                    <?php $httpsUrl = str_replace("http:", "https:", $image["src"]); ?>
                    <img src="<?php echo substr($httpsUrl, 0, strrpos($httpsUrl, "?")) . "?width=400&height=300"; ?>" />
                </div>
            <?php endforeach; ?>
        </div>
        <div class="listing-details">
            <div class="order">
                <div class="price">
                    <?php echo $price; ?>
                </div>
                <div class="buttons">
                    <?php if($buttonType == "timeline" && strlen($buttonTimelineId) > 0): ?>
                        <div class="xola-checkout xola-custom" data-button-id="<?php echo $buttonTimelineId; ?>">
                            <button type="button" style="<?php if($buttonStyle) echo $buttonStyle; ?>">
                                <span><?php echo $buttonTitle; ?></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="xola-checkout xola-custom" data-seller="<?php echo $sellerId; ?>" data-experience="<?php echo $listing["id"]; ?>" data-version="2">
                            <button type="button" style="<?php if($buttonStyle) echo $buttonStyle; ?>">
                                <span><?php echo $buttonTitle; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if($buttonGiftId && $giftButtonUsed): ?>
                        <div class="xola-gift xola-custom" data-button-id="<?php echo $buttonGiftId; ?>">
                            <button type="button" class="gift" style="<?php if($giftButtonStyle) echo $giftButtonStyle; ?>">
                                <span><?php echo $giftButtonTitle; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if(count($relatedListings) > 0 && count($productSections) && isset($productSections["other_activities"])): ?>
    <div class="container-fluid listing-page fullwidth">
        <div class="content-container other-activities">
            <h2>Other Activities</h2>
            <div class="listing-grid">
                <?php foreach($relatedListings as $list): ?>
                    <?php $l = $list->data; ?>
                    <?php $times = XolaData::getAvailableTimings($l); ?>
                    <?php $duration = XolaData::getDuration($l); ?>
                    <?php $price = XolaData::getPrice($l); ?>
                    <div class="col-xs-12 col-sm-6 col-md-4 item">
                        <a href="<?php echo XolaData::getListingUrl($list); ?>" title="<?php echo $l["name"]; ?>">
                            <div class="item-content">
                                <div class="image">
                                    <img src="<?php echo XolaData::getImageUrl($l["medias"][0]["src"]); ?>" alt="<?php echo $l["name"]; ?>" title="<?php echo $l["name"]; ?>" />
                                </div>
                                <div class="details">
                                    <h3><?php echo $l["name"]; ?></h3>
                                    <p class="description grid-description">
                                        <?php echo $l["excerpt"]; ?>
                                    </p>
                                    <div class="timings <?php if($hideDuration) echo 'hide-duration'; ?>">
                                        <div class="left">
                                            <p><?php echo $times["days"]; ?> </p>
                                            <p><?php echo $times["times"]; ?></p>
                                        </div>
                                        <div  class="right <?php if($hideDuration) echo 'hidden'; ?>">
                                            <p><?php echo $duration; ?></p>
                                        </div>
                                    </div>
                                    <div class="price">
                                        <?php echo $price; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    var infiniteScroll = false;
    var map;
    var mapCoordinateX = '<?php echo $listing["geo"]["lat"]; ?>';
    var mapCoordinateY = '<?php echo $listing["geo"]["lng"]; ?>';
    var initButtonPositionY = 0;
    var initButtonPositionX = 0;
    var initButtonWidth = 0;
    var bottomButtonPositionY = 0;
    var mobileScreen = false;

    jQuery(document).ready(function() {
        jQuery('.hero-banner').slick({autoplay: true, autoplaySpeed: 4000, dots: true});
        jQuery(window).scrollTop(0);
        appendGooglemap();
        checkFluid();
        rearrangeMobileContainers();
        caclulateButtonPosition();
    });

    jQuery(document).scroll(function () {
        if(mobileScreen) {
            mobileFloatPurchaseButtons();
        } else {
            floatPurchaseButtons();
        }
    });

    jQuery(window).resize(function() {
        checkFluid();
        rearrangeMobileContainers();
        caclulateButtonPosition();
        if(mobileScreen) {
            mobileFloatPurchaseButtons();
        } else {
            floatPurchaseButtons();
        }
    });

    function initializeGM() {
        var mapCanvas = document.getElementById('map-canvas');
        if(!jQuery(mapCanvas).length)
            return;

        var mapOptions = {
            center: new google.maps.LatLng(mapCoordinateX, mapCoordinateY),
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            title: '<?php echo str_replace("'", "`", $listing["name"]); ?>',
            mapTypeControl:false,
            scrollwheel: false,
            zoomControlOptions: { style:google.maps.ZoomControlStyle.SMALL },
            styles: [{}]

        };

        map = new google.maps.Map(mapCanvas, mapOptions);

        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(mapCoordinateX, mapCoordinateY),
            map: map,
            title: '<?php echo str_replace("'", "`", $listing["name"]); ?>'
        });
    }

    function appendGooglemap() {
        if (typeof google === 'object' && typeof google.maps === 'object') {
            initializeGM();
        } else {
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.src = "https://maps.googleapis.com/maps/api/js?key=<?php echo $googleMapKey; ?>&callback=initializeGM";
            document.body.appendChild(script);
        }
    }

    function floatPurchaseButtons() {

        if(jQuery("div.right-area").outerHeight() > jQuery("div.left-area").outerHeight())
            return;

        caclulateButtonPosition();
        var topOffset = parseInt(jQuery(window).scrollTop());
        var buttonsContainer = jQuery(".listing-page .listing-details .order");
        var containerHeight = buttonsContainer.height();

        if(topOffset + containerHeight + 50 > bottomButtonPositionY) {
            buttonsContainer.addClass("stop").addClass("floating");
            buttonsContainer.css("top", bottomButtonPositionY - containerHeight - 50 + "px").css("right", "15px");
            buttonsContainer.css("width", initButtonWidth + "px");
        } else if (topOffset > initButtonPositionY) {
            buttonsContainer.addClass("floating");
            buttonsContainer.removeClass("stop");
            buttonsContainer.css("top", "50px");
            buttonsContainer.css("right", initButtonPositionX + "px");
            buttonsContainer.css("width", initButtonWidth + "px");
        }
        else {
            resetButtonContainer();
        }
    }

    function mobileFloatPurchaseButtons() {
        caclulateButtonPosition();
        var topOffset = parseInt(jQuery(window).scrollTop());
        var buttonsContainer = jQuery(".listing-page .listing-details .order .buttons");

        var containerHeight = parseInt(jQuery(".listing-page .listing-details .order .price").height()) + parseInt(buttonsContainer.height());
        if (topOffset > initButtonPositionY + containerHeight) {
            buttonsContainer.addClass("sticky");
        } else {
            resetButtonContainer();
        }
    }

    function resetButtonContainer() {
        jQuery(".listing-page .listing-details .order").removeClass("stop").removeClass("floating").css("top", "0px").css("right",  "0px").css("width", "100%");
        jQuery(".listing-page .listing-details .order .buttons").removeClass("sticky");
    }

    function checkFluid() {
        var container = jQuery("#content");
        var buttonsContainer = jQuery(".listing-page .listing-details .order");

        if(jQuery(window).width() <= 820) {
            mobileScreen = true;
        } else {
            mobileScreen = false;
        }

        if(!jQuery("#content").length)
            container = jQuery(".content");
        if(jQuery(container).length && jQuery(container).width() < 1300 && !mobileScreen) {
            jQuery(".listing-page").addClass("fluid");
            if(parseInt(buttonsContainer.outerWidth()) < 290) {
                buttonsContainer.css("width", buttonsContainer.outerWidth());
            }
        } else {
            jQuery(".listing-page").removeClass("fluid");
        }
    }

    function caclulateButtonPosition() {
        var buttonsContainer = jQuery(".listing-page .listing-details .order");
        resetButtonContainer();

        var pos = buttonsContainer.offset();
        initButtonPositionY = pos.top;
        initButtonPositionX = jQuery(window).width() - pos.left - buttonsContainer.width();
        initButtonWidth = parseInt(buttonsContainer.width());

        bottomButtonPositionY = jQuery("div.left-area").outerHeight();
    }

    function rearrangeMobileContainers() {
        if(mobileScreen) {
            jQuery(".listing-page .right-area").insertBefore(jQuery(".listing-page .left-area"));
        } else {
            jQuery(".listing-page .right-area").insertAfter(jQuery(".listing-page .left-area"));
        }
    }

</script>

<script type="text/javascript"> (function() { var co=document.createElement("script"); co.type="text/javascript"; co.async=true; co.src="https://xola.com/checkout.js"; var s=document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(co, s); })(); </script>

<?php get_footer(); ?>

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


