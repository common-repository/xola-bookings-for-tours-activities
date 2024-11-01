<?php


// LOADING PARTS OF BOOTSTRAP ONLY ON THIS PAGE
echo '<style>';
require_once(XOLA_ASSETS_PATH . 'css/xola-admin-bootstrap.min.css');
echo '</style>';

$pageUrl = admin_url('admin.php?page=xola-plugin-settings');
?>

<div class="xola-admin-container mt-4">

    <div class="row">
        <div class="ob-title col-md-10 col-md-offset-1 col-xs-12 col-xs-offset-0 text-left">
            <div class="ob-row">
                <div class="col-xs-9">
                    <h2>Select discovery page layout</h2>
                </div>

                <div class="col-xs-3 text-right">
                    <h4>STEP <span class="circle">1</span><span class="circle filled">2</span></h4>
                </div>
            </div>
        </div>

        <div id="ob-layout" class="col-md-10 col-md-offset-1 col-xs-12 col-xs-offset-0 text-center">

            <div class="ob-row">
                <div class="col-xs-6">
                    <a href="#" class="ob-layout-img list" data-layout-type="rows"></a>
                </div>
                <div class="col-xs-6">
                    <a href="#" class="ob-layout-img grid" data-layout-type="grid"></a>
                </div>
            </div>

            <div class="ob-row discovery-checkbox-row">
                <input type="checkbox" value="1" id="ob-discovery-checkbox" name="ob-discovery-checkbox" /> <span>I don't want a discovery page</span>
            </div>

            <div class="listings-footer text-right layout-type-button-cont">
                <input type="hidden" name="ob-layout-type" value="list" id="ob-layout-type">

                <button type="button" id="ob-go-to-step-1" class="xola-button large button-white">Back</button>
                <button type="button" id="ob-finish-steps" class="next-listings xola-button large button-green">Finish
                </button>
            </div>
        </div>
    </div>

</div>
