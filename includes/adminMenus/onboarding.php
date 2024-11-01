<?php

use xola\XolaPlugin;
use xola\XolaSession;

// LOADING PARTS OF BOOTSTRAP ONLY ON THIS PAGE
echo '<style>';
require_once(XOLA_ASSETS_PATH . 'css/xola-admin-bootstrap.min.css');
echo '</style>';

$pageUrl = admin_url('admin.php?page=xola-plugin-settings');

$session = new XolaSession;

$errors = $session->getErrors();

if (XOLA_ONBOARDING_PENDING) {
    $messages = array('<span>Your request is pending.</span>');
}

$session->clearInfo();

$hasErrorClass = '';

if (!XolaPlugin::isTlsVersionValid()) {
    $errors[] = 'Xola Plugin requires TLS v1.2, and it will not work well on lower versions.';
}
?>

<div class="xola-admin-container">

    <div class="row">
        <div class="col-xs-12 text-center mt-4">

            <img src="<?= XOLA_ASSETS_URI . 'images/xola-logo-black-small.png' ?>" alt="XOLA LOGO">
            <p class="h1 mt-2">Wordpress Plugin</p>
        </div>

        <?php if (!empty($errors)):
            $hasErrorClass = 'has-error'; ?>
            <div class="col-md-4 col-md-offset-4 col-xs-12 col-xs-offset-0 text-center mt-4">
                <?php foreach ($errors as $item): ?>
                    <div class="alert alert-danger xola-ob-alert-danger" role="alert">
                        <?= nl2br($item) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($messages)): ?>
            <div class="col-md-4 col-md-offset-4 col-xs-12 col-xs-offset-0 text-center mt-4">
                <?php foreach ($messages as $item): ?>
                    <div class="alert alert-success xola-ob-alert-success" role="alert">
                        <?= nl2br($item) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <span class="mt-5"></span>
        <?php endif; ?>

        <div class="col-xs-12 text-center">
            <p class="h4">Are you currently a Xola customer ?</p>
        </div>

        <div class="col-md-4 col-md-offset-4 col-xs-12 col-xs-offset-0 text-center mt-4">
            <div id="xola-settings-tabs">
                <ul class="list-inline xola-ob-tabs">
                    <li class="col-xs-6 px-0">
                        <a href="<?= $pageUrl ?>#tab-login">Yes</a>
                    </li>
                    <li class="col-xs-6 px-0">
                        <a href="<?= $pageUrl ?>#tab-register">Not yet</a>
                    </li>
                </ul>

                <div class="tabs-content">
                    <div id="tab-login">
                        <form method="post" class="xola-settings-form">
                            <div class="form-group <?= $hasErrorClass ?>">
                                <input type="email" class="form-control xola-ob-form-control" placeholder="Email"
                                       name="email">
                            </div>

                            <div class="form-group <?= $hasErrorClass ?>">
                                <input type="password" class="form-control xola-ob-form-control" placeholder="Password"
                                       name="password">
                            </div>

                            <input type="hidden" name="action" value="ob-login">
                            <button type="submit" class="btn btn-block xola-ob-btn">Sign In</button>
                            <?php wp_nonce_field(XOLA_NONCE_ACTION, XOLA_NONCE_ACTION); ?>
                        </form>
                    </div>

                    <div id="tab-register">

                        <!--[if lte IE 8]>
                        <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
                        <![endif]-->
                        <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>
                        <script>
                            hbspt.forms.create({
                                portalId: "4845250",
                                formId: "033ca56c-6e41-4c4e-aaee-02b595bbda70"
                            });
                        </script>

                        <p class="mb-0 xola-ob-link"><a href="tel:+14154049652">+1 (415) 404-9652</a></p>
                        <p class="m-0 xola-ob-link"><a href="mailto:join@xola.com">join@xola.com</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
