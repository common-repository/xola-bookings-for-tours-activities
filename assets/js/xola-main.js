var $ = jQuery;

var infiniteScrollMark = 0;
var infiniteScrollFlag = false;
var currentPage = 1;

$(function () {
    if (infiniteScroll === true) {
        calculateInfiniteScrollMark();
    }

    xolaGripMatchHeight();
    fixedHeaderContent();
});

jQuery(document).scroll(function () {
    if (infiniteScroll !== true)
        return;

    var topOffset = parseInt(jQuery(window).scrollTop()) + parseInt(jQuery(window).height());

    if (!infiniteScrollFlag && topOffset > infiniteScrollMark) {
        runInfiniteScroll();
        calculateInfiniteScrollMark();
    }
});


function calculateInfiniteScrollMark() {
    var offset = jQuery(".listing-content").offset();
    infiniteScrollMark = offset.top + parseInt(jQuery(".listing-content").height());
}

jQuery(document).on("keyup", "#filter_keyword", function (e) {
    fillGrid();
});

function filterListingsByKeyWord() {

    filtered_listings_json = new Array();

    if (jQuery("#filter_keyword").val() == "") {

        for (var i = 0; i < listings_json.length; i++) {
            var item = listings_json[i];
            filtered_listings_json.push(item);
        }
        return;
    }

    var search_results = search.search(jQuery("#filter_keyword").val());

    for (var i = 0; i < listings_json.length; i++) {
        addItemToFilteredItems(search_results, listings_json[i]);
    }


}

function addItemToFilteredItems(search_results, item) {
    for (var i = 0; i < search_results.length; i++) {
        var result_id = search_results[i]["id"];
        if(result_id == item.id) {
            filtered_listings_json.push(item);
            break;
        }

    }
}

function reverseOrder() {
    filtered_listings_json = filtered_listings_json.reverse();
    listings_json = listings_json.reverse();
    fillGrid();
};


function runInfiniteScroll() {

    if (currentPage >= totalPages || infiniteScrollFlag) {
        return;
    }

    infiniteScrollFlag = true;

    jQuery(".discovery-page .ajax-spinner").show();

    for(var i=shownItems; i<=shownItems+pageSize && i< filtered_listings_json.length; i++) {
        var item = filtered_listings_json[i];
        insertItemIntoGrid(item);
        shownItems = shownItems + 1;
    }

    jQuery(".discovery-page .ajax-spinner").hide();
    infiniteScrollFlag = false;
    currentPage = currentPage + 1;

    xolaGripMatchHeight();
}

function xolaGripMatchHeight() {
    // Discovery page
    jQuery('.listing-content.listing-grid .item .item-content h3').matchHeight();
    jQuery('.listing-content.listing-grid .item .item-content .description').matchHeight();
    jQuery('.listing-content.listing-grid .item .item-content .timings').matchHeight();

    jQuery('.listing-content.listing-grid .item .item-content').matchHeight();

    // Discovery Single
    jQuery('.listing-page .other-activities .listing-grid div.item .item-content h3').matchHeight();
    jQuery('.listing-page .other-activities .listing-grid div.item .item-content .description').matchHeight();
    jQuery('.listing-page .other-activities .listing-grid div.item .item-content .timings').matchHeight();

    jQuery('.listing-page .other-activities .listing-grid div.item .item-content').matchHeight();
}


function insertItemIntoGrid(item) {

    var price = item.formatted_price;
    var image = item.formatted_image;
    var days = item.main_timings_days;
    var times = item.main_timings_times;
    var duration = item.formatted_duration;
    var url = item.formatted_url;

    var new_item = jQuery(".dummy-item").clone();
    new_item.removeClass("dummy-item");
    new_item.attr("id", item.id);

    new_item.find("h3").html(item.name);
    new_item.find("p.description").html(item.excerpt);
    new_item.find("div.price").html(price);
    var img = jQuery('<img>');
    img.attr("src", image).attr("alt", item.name);
    new_item.find("div.image").append(img);
    new_item.find("div.timings div.left p:nth-child(1)").html(item.formatted_times_days);
    new_item.find("div.timings div.left p:nth-child(2)").html(item.formatted_times_hours);
    new_item.find("div.timings div.right").html(duration);

    new_item.find("a").attr("href", url).attr("title", item.name);

    jQuery(new_item).insertBefore(jQuery(".listing-content .dummy-item"));
    //jQuery(".listing-content").append(new_item);
}

function fillGrid() {

    purgeGrid();

    filterListingsByKeyWord();

    for (var i = 0; i < filtered_listings_json.length; i++) {
        var item = filtered_listings_json[i];
        insertItemIntoGrid(item);
    }

    xolaGripMatchHeight();
}

function purgeGrid() {
    jQuery(".listing-content div.item").each(function() {
        if(!jQuery(this).is(".dummy-item"))
            jQuery(this).remove();
    });
}

function fixedHeaderContent() {
    if(jQuery("header").is(".fixed.scroll_header_top_area")) {
        jQuery(".content").addClass("fixed_content");
    }
}
