<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK SETTINGS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$settings           = array(
  'menu_title'      => 'GOLD Options',
  'menu_type'       => 'menu', // menu, submenu, options, theme, etc.
  'menu_slug'       => 'cs-options',
  'ajax_save'       => false,
  'show_reset_all'  => false,
  'framework_title' => 'Gold Settings',
);

// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// FRAMEWORK OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$options        = array();

// ----------------------------------------
// a option section for options overview  -
// ----------------------------------------
$options[]      = array(
  'name'        => 'feeds',
  'title'       => 'METAL FEEDS',
  'icon'        => 'fa fa-star',
  
  
  'sections' => array(

    // -----------------------------
    // begin: Gold options         -
    // -----------------------------
    array(
      'name'      => 'gold_options',
      'title'     => 'Gold Options',
      'icon'      => 'fa fa-check',
	  
    // begin: fields
    'fields'      => array(

    // begin: a field
    array(
      'id'      => 'gold_price_feed',
      'type'    => 'text',
      'title'   => 'Gold Price Feed',
    ),
    // end: a field

    // begin: a field
    array(
      'id'      => 'gold_diff_price_feed',
      'type'    => 'text',
      'title'   => 'Gold Difference Price Feed',
    ),
	
    // end: a field
    array(
      'id'      => 'gold_price_threshold',
      'type'    => 'text',
      'title'   => 'Minimum Gold price',
    ),
	
    // end: a field
   array(
      'id'      => 'gold_price_fallback',
      'type'    => 'text',
      'title'   => 'Fallback Gold price',
    ),
	
	
    // end: a field


    array(
      'id'      => 'gold_enabled',
      'type'    => 'switcher',
      'title'   => 'Enabled',
      'label'   => '',
    ),

	), //end: fields
   ), // end: options


    // -----------------------------
    // begin: Silver Options         -
    // -----------------------------
    array(
      'name'      => 'silver_options',
      'title'     => 'Silver Options',
      'icon'      => 'fa fa-check',
	  
    // begin: fields
    'fields'      => array(

    // begin: a field
    array(
      'id'      => 'silver_price_feed',
      'type'    => 'text',
      'title'   => 'Silver Price Feed',
    ),
    // end: a field

    // begin: a field
    array(
      'id'      => 'silver_diff_price_feed',
      'type'    => 'text',
      'title'   => 'Silver Difference Price Feed',
    ),
    // end: a field
	

    // end: a field
    array(
      'id'      => 'silver_price_threshold',
      'type'    => 'text',
      'title'   => 'Minimum Silver price',
    ),
	
    // end: a field
   array(
      'id'      => 'silver_price_fallback',
      'type'    => 'text',
      'title'   => 'Fallback Silver price',
    ),
	
	

    array(
      'id'      => 'silver_enabled',
      'type'    => 'switcher',
      'title'   => 'Enabled',
      'label'   => '',
    ),

	), //end: fields
   ), // end: options


    // -----------------------------
    // begin: Platinum Options         -
    // -----------------------------
    array(
      'name'      => 'platinum_options',
      'title'     => 'Platinum Options',
      'icon'      => 'fa fa-check',
	  
    // begin: fields
    'fields'      => array(

    // begin: a field
    array(
      'id'      => 'platinum_price_feed',
      'type'    => 'text',
      'title'   => 'Platinum Price Feed',
    ),
    // end: a field
	
    // begin: a field
    array(
      'id'      => 'platinum_diff_price_feed',
      'type'    => 'text',
      'title'   => 'Platinum Difference Price Feed',
    ),
    // end: a field

    // end: a field
    array(
      'id'      => 'platinum_price_threshold',
      'type'    => 'text',
      'title'   => 'Minimum Platinum price',
    ),
	
    // end: a field
   array(
      'id'      => 'platinum_price_fallback',
      'type'    => 'text',
      'title'   => 'Fallback Platinum price',
    ),
	


    array(
      'id'      => 'platinum_enabled',
      'type'    => 'switcher',
      'title'   => 'Enabled',
      'label'   => '',
    ),

	), //end: fields
   ), // end: options



    // -----------------------------
    // begin: Palladium Options         -
    // -----------------------------
    array(
      'name'      => 'palladium_options',
      'title'     => 'Palladium Options',
      'icon'      => 'fa fa-check',
	  
    // begin: fields
    'fields'      => array(

    // begin: a field
    array(
      'id'      => 'palladium_price_feed',
      'type'    => 'text',
      'title'   => 'Palladium Price Feed',
    ),
    // end: a field
	
	
    // begin: a field
    array(
      'id'      => 'palladium_diff_price_feed',
      'type'    => 'text',
      'title'   => 'Palladium Difference Price Feed',
    ),
    // end: a field

    // end: a field
    array(
      'id'      => 'palladium_price_threshold',
      'type'    => 'text',
      'title'   => 'Minimum Palladium price',
    ),
	
    // end: a field
   array(
      'id'      => 'palladium_price_fallback',
      'type'    => 'text',
      'title'   => 'Fallback Palladium price',
    ),
	


    array(
      'id'      => 'palladium_enabled',
      'type'    => 'switcher',
      'title'   => 'Enabled',
      'label'   => '',
    ),

	), //end: fields
   ), // end: options

   // -----------------------------
    // begin: Palladium Options         -
    // -----------------------------
    array(
      'name'      => 'feed_settings',
      'title'     => 'Feed Setting',
      'icon'      => 'fa fa-check',
	  
    // begin: fields
    'fields'      => array(

    // end: a field

        array(
          'id'      => 'feed_collect_time',
          'type'    => 'select',
          'title'   => 'Feed collection times',
          'options' => array(
            '5min'   => '5 Minutes',
            '15min'  => '15 Minutes'
          ),
		  'validate' => 'cron'
        ),
		array(
          'id'      => 'prune_feeds',
          'type'    => 'select',
          'title'   => 'Prune Feeds After',
          'options' => array(
            '1 Hour'   => '1 Hour',
            '12 Hours' => '12 Hours',
            '1 Day'    => '1 Day',
            '1 Week'    => '1 Week',
            '1 Month'    => '1 Month',
            '1 Year'    => '1 Year',          )
        ),

    array(
      'id'      => 'feeds_enabled',
      'type'    => 'switcher',
      'title'   => 'Enabled',
      'label'   => '',
    ),
	
		
    // end: a field
   array(
      'id'      => 'admin_email',
      'type'    => 'text',
      'title'   => 'Admin email for problems',
    ),
	

	), //end: fields
   ), // end: options










  ), // end: section
);

// ------------------------------
// a seperator                  -
// ------------------------------
$options[] = array(
  'name'   => 'seperator_1',
  'title'  => 'Admin Settings',
  'icon'   => 'fa fa-bookmark'
);

// ------------------------------
// backup                       -
// ------------------------------
$options[]   = array(
  'name'     => 'backup_section',
  'title'    => 'Backup',
  'icon'     => 'fa fa-shield',
  'fields'   => array(

    array(
      'type'    => 'notice',
      'class'   => 'warning',
      'content' => 'You can save your current options. Download a Backup and Import.',
    ),

    array(
      'type'    => 'backup',
    ),

  )
);


CSFramework::instance( $settings, $options );
