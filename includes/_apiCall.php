<?php

if (!function_exists('xolaExecuteRequest')) {

    function xolaExecuteRequest($url, $headers = null)
    {
        $args = array(
            "headers" => array(),
            "timeout" => 100,
        );

        if (!empty($headers)) {
            if (!empty($headers['username']) && $headers['password']) {
                $args["headers"]["Authorization"] = 'Basic ' . base64_encode( $headers['username'] . ':' . $headers['password'] );
            }
        }

        $args["headers"]["X-API-VERSION"] = "2018-06-13";

        $response = wp_remote_get($url, $args);

        if(is_wp_error($response)) {
            $error = $response->get_error_message();
            echo "Remote Error #:" . $error;
        } else {
            $array = json_decode($response["body"], true);
            return $array;
        }

        return false;
    }
}

if (!function_exists('xolaExecutePost')) {

    function xolaExecutePost($url, $headers, $postData)
    {

        $args = array(
            "headers" => array(),
            "body" => json_encode($postData),
            "timeout" => 100,
        );

        if (!empty($headers)) {
            if (!empty($headers['username']) && $headers['password']) {
                $args["headers"]["Authorization"] = 'Basic ' . base64_encode( $headers['username'] . ':' . $headers['password'] );
            }
        }

        $args["headers"]["X-API-VERSION"] = "2018-06-13";

        $response = wp_remote_post($url, $args);

        if(is_wp_error($response)) {
            $error = $response->get_error_message();
            echo "Remote Error #:" . $error;
        } else {
            $array = json_decode($response["body"], true);
            return $array;
        }

        return false;
    }
}

if (!function_exists('xolaExecuteDelete')) {

    function xolaExecuteDelete($url, $headers)
    {
        $args = array(
            "headers" => array(),
            "method" => "DELETE",
            "timeout" => 100,
        );

        if (!empty($headers)) {
            if (!empty($headers['username']) && $headers['password']) {
                $args["headers"]["Authorization"] = 'Basic ' . base64_encode( $headers['username'] . ':' . $headers['password'] );
            }
        }

        $args["headers"]["X-API-VERSION"] = "2018-06-13";

        $response = wp_remote_request($url, $args);

        if(is_wp_error($response)) {
            $error = $response->get_error_message();
            echo "Remote Error #:" . $error;
        } else {
            $array = json_decode($response["body"], true);
            return $array;
        }

        return false;
    }
}