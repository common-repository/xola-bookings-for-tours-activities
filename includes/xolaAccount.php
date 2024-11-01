<?php

namespace xola;

class XolaAccount
{
    protected static $apiBaseUrl = 'https://xola.com/api/';

    static function getApiKey($username = null, $password = null, $userId = null)
    {
        $url = self::$apiBaseUrl . "users/{$userId}/apiKey";

        $args = array("headers" => array(
            'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
        ),
            "timeout" => 100,
        );

        $response = wp_remote_get($url, $args);

        if(is_wp_error($response)) {
            $error = $response->get_error_message();
            echo "Remote Error #:" . $error;
        } else {
            $array = json_decode($response["body"], true);
            return $array;
        }
    }

    static function getUserId($username = null, $password = null)
    {

        if (empty($username)) {
            $xolaEmailName = XOLA_PREFIX . 'account_email';
            $username = get_option($xolaEmailName);
        }

        if (empty($password)) {
            $xolaPassName = XOLA_PREFIX . 'account_password';
            $password = get_option($xolaPassName);
        }

        $url = self::$apiBaseUrl . 'users/me';

        $args = array("headers" => array(
            'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
        ),
            "timeout" => 100,
        );

        $response = wp_remote_get($url, $args);

        if(is_wp_error($response)) {
            $error = $response->get_error_message();
            echo "Remote Error #:" . $error;
        } else {
            $array = json_decode($response["body"], true);
            return $array;
        }
    }

    static function requestAccount($data = array())
    {
        // Make sure that email content type is set to HTML
        add_filter('wp_mail_content_type', function () {
            return 'text/html';
        });

        $firstName = XolaData::sanitizeString($data['first-name']);
        $lastName = XolaData::sanitizeString($data['last-name']);
        $email = XolaData::sanitizeString($data['email']);
        $phoneNumber = XolaData::sanitizeString($data['phone-number']);
        $companyUrl = XolaData::sanitizeString($data['company-url']);

        $subject = 'New Xola customer request';

        ob_start(); ?>
        <h2>New Xola customer request</h2>

        <dl>
            <dt>First Name:</dt>
            <dd><?= $firstName ?></dd>

            <dt>Last Name:</dt>
            <dd><?= $lastName ?></dd>

            <dt>Email:</dt>
            <dd><?= $email ?></dd>

            <dt>Phone Number:</dt>
            <dd><?= $phoneNumber ?></dd>

            <dt>Company URL:</dt>
            <dd><?= $companyUrl ?></dd>
        </dl>
        <?php
        $msg = ob_get_clean();

        $to = 'join@xola.com';
        $mail = wp_mail($to, $subject, $msg);

        // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', function () {
            return 'text/html';
        });

        return $mail;
    }

    static function getAvailableListings()
    {
        $url = self::$apiBaseUrl . 'experiences?seller=' . XolaAccount::getSellerId() . "&apiKey=" . XolaAccount::getStoredApiKey();
        $data = xolaExecuteRequest($url);

        while(isset($data["paging"]["next"])) {
            $more_data = xolaExecuteRequest(self::$apiBaseUrl . str_replace("/api/", "", $data["paging"]["next"]));
            $data["data"] = array_merge($data["data"], $more_data["data"]);
            unset($data["paging"]["next"]);
            if(isset($more_data["paging"]["next"]))
                $data["paging"]["next"] = $more_data["paging"]["next"];
        }

        return $data;
    }

    static function getListingsByFilter($type, $filters)
    {
        $url = self::$apiBaseUrl;
        switch ($type) {
            case 'date':
                $sid = XolaAccount::getSellerId();

                $url .= "events?seller={$sid}&start={$filters['start_date']}&end={$filters['end_date']}";
                break;
            case 'availability':
                $ids = implode(',', $filters['ids']);

                $url .= "availability?experience={$ids}";

                if (isset($filters['start_date']) && !empty($filters['start_date'])) {
                    $date = XolaData::formatDateForApiCall($filters['start_date']);
                    $url .= "&start={$date}";
                }

                if (isset($filters['end_date']) && !empty($filters['end_date'])) {
                    $date = XolaData::formatDateForApiCall($filters['end_date']);
                    $url .= "&end={$date}";
                }

                break;
            default:
                return false;
                break;
        }

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        return xolaExecuteRequest($url, array('username' => $username, 'password' => $password));
    }

    static function getSellerId()
    {
        return get_option(XOLA_PREFIX . 'account_id');
    }


    static function getStoredApiKey()
    {
        return get_option(XOLA_PREFIX . 'account_api_key');
    }


    static function getButtons()
    {
        $url = self::$apiBaseUrl . 'buttons?seller=' . XolaAccount::getSellerId();

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        return xolaExecuteRequest($url, array('username' => $username, 'password' => $password));
    }

    static function createCheckoutButton($listing, $type)
    {
        if(!is_array($listing))
            $listing = unserialize($listing);

        $url = self::$apiBaseUrl . 'buttons?seller=' . XolaAccount::getSellerId();

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        $postData = array();
        $postData["seller"]["id"] = XolaAccount::getSellerId();
        $postData["name"] = $listing["name"] . " WP Button";
        $postData["type"] = $type;
        $postData["items"] = array();

        $item = array();
        $item["name"] = $listing["name"];
        $item["experience"] = $listing["id"];
        $item["type"] = "experience";
        $item["sequence"] = 0;

        $postData["items"][] = $item;

        $response = xolaExecutePost($url, array('username' => $username, 'password' => $password), $postData);

        if (isset($response["id"]))
            return $response["id"];
        else
            return null;
    }


    static function deleteCheckoutButton($buttonId)
    {

        $url = self::$apiBaseUrl . 'buttons/' . $buttonId;

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        return xolaExecuteDelete($url, array('username' => $username, 'password' => $password));
    }

    static function fetchCheckoutButton($buttonId)
    {

        $url = self::$apiBaseUrl . 'buttons/' . $buttonId;

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        return xolaExecuteRequest($url, array('username' => $username, 'password' => $password));
    }


    static function registerWebhook($eventName)
    {

        $url = self::$apiBaseUrl . "users/" . get_option(XOLA_PREFIX . "account_id") . "/hooks";

        $hash = md5("xola-webhook-" . get_option(XOLA_PREFIX . "account_email"));

        $eventName = "experience." . $eventName;
        $callback = get_site_url() . "/xola-webhook/" . $hash;

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        $postData = array();
        $postData["eventName"] = $eventName;
        $postData["url"] = $callback;

        $response = xolaExecutePost($url, array('username' => $username, 'password' => $password), $postData);

        if (isset($response["id"]))
            update_option(XOLA_PREFIX . "webhook_id_" . $eventName, $response["id"]);
        else
            update_option(XOLA_PREFIX . "webhook_id_" . $eventName, null);

    }


    static function listWebhook()
    {
        $url = self::$apiBaseUrl . "users/" . get_option(XOLA_PREFIX . "account_id") . "/hooks";

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        return xolaExecuteRequest($url, array('username' => $username, 'password' => $password));
    }


    static function deleteWebhook($eventName)
    {

        $hookId = get_option(XOLA_PREFIX . "webhook_id_" . $eventName);
        update_option(XOLA_PREFIX . "webhook_id", null);
        $url = self::$apiBaseUrl . "users/" . get_option(XOLA_PREFIX . "account_id") . "/hooks/" . $hookId;

        $xolaEmail = XOLA_PREFIX . 'account_email';
        $username = get_option($xolaEmail);
        $xolaPass = XOLA_PREFIX . 'account_password';
        $password = get_option($xolaPass);

        return xolaExecuteDelete($url, array('username' => $username, 'password' => $password));
    }
}

new XolaAccount;