<?php


namespace xola;


class XolaCron
{
    function __construct()
    {
        if (!wp_get_schedule('xolaScheduleCronJobs')) {
            add_action('init', array($this, 'xolaScheduleCronJobs'), 10);
        }

        add_action('xolaScheduleCronJobs', array($this, 'getNewListings'));
    }

    static function xolaScheduleCronJobs()
    {
        wp_schedule_event(time(), 'daily', 'xolaScheduleCronJobs');
    }

    static function getNewListings()
    {
        XolaData::insertNewData();
    }
}

new XolaCron;