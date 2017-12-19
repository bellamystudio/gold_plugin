<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// METABOX OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$options      = array();


// -----------------------------------------
// Post Metabox Options                    -
// -----------------------------------------
$options[]    = array(
  'id'        => '_custom_metal_price_options',
  'title'     => 'Extra Metal Information',
  'post_type' => 'metal_prices',
  'context'   => 'normal',
  'priority'  => 'default',
  'sections'  => array(

    array(
      'name'   => 'section_4',
      'fields' => array(

        array(
          'id'    => 'metal_price',
          'type'  => 'text',
          'title' => 'Metal Price',
        ),   
        array(
          'id'    => 'metal_price_diff',
          'type'  => 'text',
          'title' => 'Metal Price Differnece',
        ),   

      ),
    ),

  ),
);

CSFramework_Metabox::instance( $options );
