<?php

use xola\XolaData;

// LOADING PARTS OF BOOTSTRAP ONLY ON THIS PAGE
echo '<style>';
require_once(XOLA_ASSETS_PATH . 'css/xola-admin-bootstrap.min.css');
echo '</style>';

$pageUrl = admin_url('admin.php?page=xola-plugin-settings');
?>

<div class="xola-admin-container ob ob-sync mt-4">

    <div class="row">
        <div class="ob-title col-md-8 col-md-offset-2 col-xs-12 col-xs-offset-0 text-left">
            <div class="ob-row">
                <div class="col-xs-9">
                    <h2>Select listings to sync</h2>
                </div>

                <div class="col-xs-3 text-right">
                    <h4>STEP <span class="circle filled">1</span><span class="circle">2</span></h4>
                </div>
            </div>

            <h5>We'll automatically generate product pages for you (this can be changed later).</h5>
        </div>

        <div id="connected-listings" class="col-md-8 col-md-offset-2 col-xs-12 col-xs-offset-0 text-center">
            <?= XolaData::getListingsHtml(true, true); ?>
        </div>
    </div>

</div>
