var $ = jQuery;

$(function () {
    tabsInit();

    forceInsertNewData();

    disconnectAccountInit();

    listingsSelection();

    toggleCustomCss();

    cssFixes();

    obLayoutSelect();

    updateListingStatus();

    editPopoverInit();

    editDiscoveryUrl();

    saveSettingsInit();

    toggleDiscoveryPage();
});

function tabsInit() {
    $('#xola-settings-tabs').tabs();
    activeTab = $('#xola-settings-tabs').tabs("option", "active");
    $('#xola-onboarding-tabs').tabs();
}

function forceInsertNewData() {
    $('#forceInsertNewData').click(function (e) {
        e.preventDefault();

        var button = jQuery(this);

        var spinner = button.parent().find("#forceInsertNewDataProgressSpinner");

        button.css("display", "none");
        spinner.css("display", "inline-block");

        $.ajax({
            type: 'POST',
            url: AJAXURL,
            dataType: 'json',
            data: {
                action: 'forceInsertNewData'
            },
            success: function (r) {
                if (!r.success) {
                    $.alert('Something went wrong. Please try again later.');
                }
                button.css("display", "inline-block");
                $('span.last-synced span.last-synced-date').html(r.last_synced);
                $('#connected-listings .listings').replaceWith(r.html);
                spinner.hide();
            },
            error: function (jqXHR, text, error) {

            }
        });
    });
}

function disconnectAccountInit() {
    $('#xolaDisconnectAccount').click(function (e) {
        e.preventDefault();

        $.confirm({
            title: '<div class="text-center text-orange">WARNING</div>',
            content: '<div class="text-center"><strong>Disconnecting will disable the sync between Xola and Wordpress.</strong><br>' +
            'This means that your Product and Discovery pages will disappear. Would you like to continue?</div>',
            buttons: {
                confirm: function () {
                    disconnectAccount();
                },
                cancel: function () {
                }
            }
        });
    });
}

function disconnectAccount() {
    $.ajax({
        type: 'POST',
        url: AJAXURL,
        dataType: 'json',
        data: {
            action: 'disconnectAccount'
        },
        success: function (r) {
            if (!r.success) {
                $.alert('Something went wrong. Please try again later.');
            } else {
                refreshPage();
            }
        },
        error: function (jqXHR, text, error) {

        }
    });
}

function listingsSelection() {

    listingSelect();

    obSteps();

    var status = true;

    $('table.listings #listings-selection').click(function (e) {
        e.preventDefault();

        var cb = $('.listing-checkbox');

        cb.each(function () {
            this.checked = status;
        });

        var text = status ? 'Unselect All' : 'Select All';
        $(this).text(text);

        status = !status;
    });

    $('div.listings #listings-selection').click(function (e) {
        e.preventDefault();

        var cb = $('.listing-checkbox');

        cb.each(function () {
            this.checked = status;
        });

        $('.listing-row').each(function (key, el) {

            if (status) {
                $(el).addClass('selected');
            } else {
                $(el).removeClass('selected');
            }
        });

        var text = status ? 'Unselect All' : 'Select All';
        $(this).text(text);

        status = !status;
    });

    var productSelection = $('#product-fields-selection');

    var productStatus = true;
    if (productSelection.data('select') === 'unselect') {
        productStatus = false;
    }

    productSelection.click(function (e) {

        e.preventDefault();

        var cb = $('.product-fields-checkbox');

        cb.each(function () {
            this.checked = productStatus;
        });

        var text = productStatus ? 'Unselect All' : 'Select All';
        $(this).text(text);

        productStatus = !productStatus;
    });
}

function obSyncListings(callbackFunction) {
    var selectedListings = [];
    $('.listing-checkbox:checked').each(function () {
        selectedListings.push($(this).attr('value'));
    });

    $.ajax({
        type: 'POST',
        url: AJAXURL,
        dataType: 'json',
        data: {
            action: 'obSyncListings',
            listings: selectedListings
        },
        success: function (r) {
            if (!r.success) {
                $.alert('Something went wrong. Please try again later.');
            } else {
                // Call callback function
                window[callbackFunction]();
            }
        }
    });
}


function obCancelOnboarding() {

    $.ajax({
        type: 'POST',
        url: AJAXURL,
        dataType: 'json',
        data: {
            action: 'obCancelOnboarding'
        },
        success: function (r) {
            if (!r.success) {
                $.alert('Something went wrong. Please try again later.');
            } else {
                refreshPage();
            }
        }
    });
}

function listingSelect() {
    $('.ob div.listings').on('click', '.listing-row', function () {
        var t = $(this);

        var cb = t.find('.listing-checkbox');
        cb.prop('checked', !cb.is(':checked'));

        t.toggleClass('selected');
    });
}

function toggleCustomCss() {
    $('input[type=radio][name=xola_product_button_style]').change(function () {

        var customCss = $('#xola_product_button_style_custom_code');

        if (this.value === 'default') {
            customCss.hide();
        } else {
            customCss.show();
        }
    });

    $('input[type=radio][name=xola_product_gift_button_style]').change(function () {

        var customCss = $('#xola_product_gift_button_style_custom_code');

        if (this.value === 'default') {
            customCss.hide();
        } else {
            customCss.show();
        }
    });

    $('input[type=checkbox][name=xola_product_display_gift_button]').change(function () {

        var customCss = $('.gift_button_styles');

        if ($(this).is(':checked')) {
            customCss.show();
        } else {
            customCss.hide();
        }
    });
}

function obGoToStepTwo() {
    $.ajax({
        type: 'POST',
        url: AJAXURL,
        dataType: 'json',
        data: {
            action: 'obChangeStep',
            step: 2
        },
        success: function (r) {
            if (!r.success) {
                $.alert('Something went wrong. Please try again later.');
            } else {
                refreshPage();
            }
        },
        error: function (jqXHR, text, error) {

        }
    });
}

function obSteps() {

    $('#ob-back-to-step-1').click(function (e) {
        e.preventDefault();
        obCancelOnboarding();
    });

    $('#ob-go-to-step-2').click(function (e) {
        e.preventDefault();
        obSyncListings('obGoToStepTwo');
    });

    $('#ob-go-to-step-1').click(function (e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: AJAXURL,
            dataType: 'json',
            data: {
                action: 'obChangeStep',
                step: 1
            },
            success: function (r) {
                if (!r.success) {
                    $.alert('Something went wrong. Please try again later.');
                } else {
                    refreshPage();
                }
            },
            error: function (jqXHR, text, error) {

            }
        });
    });

    $('#ob-finish-steps').click(function (e) {
        e.preventDefault();

        var discoveryPageVisibility = 1;
        if($('#ob-discovery-checkbox').is(":checked")) {
            discoveryPageVisibility = 0;
        }

        $.ajax({
            type: 'POST',
            url: AJAXURL,
            dataType: 'json',
            data: {
                action: 'obChangeStep',
                layout: $('#ob-layout-type').val(),
                discovery: discoveryPageVisibility,
                step: -1
            },
            success: function (r) {
                if (!r.success) {
                    $.alert('Something went wrong. Please try again later.');
                } else {
                    refreshPage();
                }
            },
            error: function (jqXHR, text, error) {

            }
        });
    });

    $('#ob-discovery-checkbox').change(function (e) {
        if(jQuery(this).is(":checked")) {
            jQuery("#ob-layout a").removeClass("selected");
        }
    });
}

function cssFixes() {
    $('#xola-settings-tabs').removeAttr('class');
    $('#tab-general').removeAttr('class');
    $('#tab-advanced').removeAttr('class');
    $('#tab-discovery').removeAttr('class');
    $('#tab-product').removeAttr('class');
    $('#tab-login').removeAttr('class');
    $('#tab-register').removeAttr('class');
}

function obLayoutSelect() {
    $('.ob-layout-img').click(function (e) {
        e.preventDefault();

        if($("#ob-discovery-checkbox").is(":checked"))
            return;

        $('.ob-layout-img').removeClass('selected');

        var t = $(this);
        t.addClass('selected');

        var layout = t.data('layout-type');

        $('#ob-layout-type').val(layout);
    });
}


function updateListingStatus() {
    $('div#connected-listings').on("click", "a.toggle-listing", function (e) {
        e.preventDefault();

        var id = $(this).data("id");
        var productAction = 0;
        var discoveryAction = 0;

        if ($(this).is(".toggle-discovery-page")) {
            discoveryAction = $(this).data("action");
            productAction = Math.abs($(this).parent().find(".toggle-product-page").data("action") - 1);
        } else {
            productAction = $(this).data("action");
            discoveryAction = Math.abs($(this).parent().find(".toggle-discovery-page").data("action") - 1);
            if (productAction == 0) {
                discoveryAction = 0;
            }
        }

        $.ajax({
            type: 'POST',
            url: AJAXURL,
            dataType: 'json',
            data: {
                action: 'toggleListing',
                id: id,
                productAction: productAction,
                discoveryAction: discoveryAction
            },
            success: function (r) {
                if (!r.success) {
                    $.alert('Something went wrong. Please try again later.');
                } else {
                    $('#connected-listings .listings').replaceWith(r.html);

                    editPopoverInit();
                }
            },
            error: function (jqXHR, text, error) {

            }
        });
    })
}


function refreshPage() {
    location.reload();
}

var changeDetected = false;
var activeTab = null;

$("form.xola-settings-form input").change(function () {
    if($(this).is("div#tab-login input") || $(this).is("div#tab-register input"))
        return;

    changeDetected = true;
});

$("form.xola-settings-form textarea").change(function () {
    changeDetected = true;
});

$('#xola-settings-tabs li a').on("click", function (e) {
    activeTab = $('#xola-settings-tabs').tabs("option", "active");

    if (changeDetected) {
        saveChangesNotification();
    }
});

function saveChangesNotification() {
    $.confirm({
        title: '<div class="text-center text-orange">Unsaved Changes</div>',
        content: '<div class="text-center"><strong>You have unsaved changes, are you sure you want to leave?</strong><br>',
        buttons: {
            confirm: function () {
                changeDetected = false;
                activeTab = $('#xola-settings-tabs').tabs("option", "active");
            },
            cancel: function () {
                changeDetected = false;
                $('#xola-settings-tabs').tabs("option", "active", activeTab);
            }
        }
    });
}

function editPopoverInit() {
    //turn to inline mode
    $.fn.editable.defaults.mode = 'popup';

    $('.listing-slug').editable({
        success: function (response, newValue) {

            var id = $(this).data('id');

            $.ajax({
                type: 'POST',
                url: AJAXURL,
                dataType: 'json',
                data: {
                    action: 'changeListingSlug',
                    id: id,
                    slug: newValue
                },
                success: function (r) {
                    if (r.success) {
                        var url = $('#listing-url-' + id);

                        url.attr('href', r.data.url);
                        url.text('/' + r.data.slug);
                    }
                }
            });

        }
    });

    $('.edit-slug').click(function (e) {
        e.stopPropagation();
        e.preventDefault();

        var target = $(this).data('target');
        $(target).editable('toggle');
    });
}


function editDiscoveryUrl() {
    //turn to inline mode
    $.fn.editable.defaults.mode = 'popup';

    $('.discovery-url').editable({
        success: function (response, newValue) {

            $.ajax({
                type: 'POST',
                url: AJAXURL,
                dataType: 'json',
                data: {
                    action: 'changeDiscoveryPageUrl',
                    url: newValue
                },
                success: function (r) {
                    if (r.success) {
                        var url = $('#discovery-page-live-link');

                        url.attr('href', r.data.url);
                        url.text('/' + r.data.slug);
                    }
                }
            });

        }
    });

    $('.edit-discovery-url').click(function (e) {
        e.stopPropagation();
        e.preventDefault();

        var target = $(this).data('target');
        $(target).editable('toggle');
    });
}

function saveSettingsInit() {
    var button = $('#xola-save-settings');
    var spinner = $('#xola-save-settings-spinner');

    button.click(function (e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: AJAXURL,
            dataType: 'json',
            data: {
                action: 'saveSettings',
                data: $('#xola-settings-form').serialize()
            },
            success: function (r) {
                if (r.success) {
                    changeDetected = false;
                }
            },
            beforeSend: function () {
                button.attr('disabled', 'disabled');
                spinner.removeClass('hide');
            },
            complete: function () {
                button.removeAttr('disabled');
                spinner.addClass('hide');
            }
        });
    });
}


function toggleDiscoveryPage() {
    $("#xola_discovery_page_toggle").change(function () {

        if ($(this).is(":checked")) {
            $(".toggle-discovery-page").each(function () {
                if ($(this).prev().is(".active")) {
                    $(this).removeClass("hide");
                }
            });
        } else {
            $(".toggle-discovery-page").each(function () {
                $(this).addClass("hide");
            });
        }

    });
}