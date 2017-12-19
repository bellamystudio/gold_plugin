<?php if (!defined('ABSPATH')) {die;} // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK SETTINGS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$settings = array(
    'menu_title' => 'GOLD Options',
    'menu_type' => 'menu', // menu, submenu, options, theme, etc.
    'menu_slug' => 'cs-options',
    'ajax_save' => false,
    'show_reset_all' => false,
    'framework_title' => 'Gold Settings',
);

// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$options = array();

// ----------------------------------------
// a option section for options overview  -
// ----------------------------------------
$options[] = array(
    'name' => 'feeds',
    'title' => 'METAL FEEDS',
    'icon' => 'fa fa-star',

    'sections' => array(

        // -----------------------------
        // begin: Gold options         -
        // -----------------------------
        array(
            'name' => 'feed_options',
            'title' => 'Feed Options',
            'icon' => 'fa fa-check',

            // begin: fields
            'fields' => array(

                // begin: a field
                array(
                    'id' => 'json_price_feed',
                    'type' => 'text',
                    'title' => 'JSON Price Feed',
                ),
                // end: a field

                array(
                    'id' => 'json_feed_enabled',
                    'type' => 'switcher',
                    'title' => 'Enabled',
                    'label' => '',
                ),

            ), //end: fields
        ), // end: options

        // -----------------------------
        // begin: Palladium Options         -
        // -----------------------------
        array(
            'name' => 'feed_settings',
            'title' => 'Feed Setting',
            'icon' => 'fa fa-check',

            // begin: fields
            'fields' => array(

                // end: a field

                array(
                    'id' => 'feed_collect_time',
                    'type' => 'select',
                    'title' => 'Feed collection times',
                    'options' => array(
                        '5min' => '5 Minutes',
                        '15min' => '15 Minutes',
                    ),
                    'validate' => 'cron',
                ),
                array(
                    'id' => 'prune_feeds',
                    'type' => 'select',
                    'title' => 'Prune Feeds After',
                    'options' => array(
                        '1 Hour' => '1 Hour',
                        '12 Hours' => '12 Hours',
                        '1 Day' => '1 Day',
                        '1 Week' => '1 Week',
                        '1 Month' => '1 Month',
                        '1 Year' => '1 Year'),
                ),

                array(
                    'id' => 'feeds_enabled',
                    'type' => 'switcher',
                    'title' => 'Enabled',
                    'label' => '',
                ),

                // end: a field
                array(
                    'id' => 'admin_email',
                    'type' => 'text',
                    'title' => 'Admin email for problems',
                ),

            ), //end: fields
        ), // end: options

    ), // end: section
);

// ------------------------------
// a seperator                  -
// ------------------------------
$options[] = array(
    'name' => 'seperator_1',
    'title' => 'Admin Settings',
    'icon' => 'fa fa-bookmark',
);

// ------------------------------
// backup                       -
// ------------------------------
$options[] = array(
    'name' => 'backup_section',
    'title' => 'Backup',
    'icon' => 'fa fa-shield',
    'fields' => array(

        array(
            'type' => 'notice',
            'class' => 'warning',
            'content' => 'You can save your current options. Download a Backup and Import.',
        ),

        array(
            'type' => 'backup',
        ),

    ),
);

CSFramework::instance($settings, $options);
