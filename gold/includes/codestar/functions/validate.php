<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.


/**
 *
 * Email validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'cs_validate_email' ) ) {
  function cs_validate_email( $value, $field ) {

    if ( ! sanitize_email( $value ) ) {
      return esc_html__( 'Please write a valid email address!', 'cs-framework' );
    }

  }
  add_filter( 'cs_validate_email', 'cs_validate_email', 10, 2 );
}

/**
 *
 * Numeric validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'cs_validate_numeric' ) ) {
  function cs_validate_numeric( $value, $field ) {

    if ( ! is_numeric( $value ) ) {
      return esc_html__( 'Please write a numeric data!', 'cs-framework' );
    }

  }
  add_filter( 'cs_validate_numeric', 'cs_validate_numeric', 10, 2 );
}

/**
 *
 * Required validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'cs_validate_required' ) ) {
  function cs_validate_required( $value ) {
    if ( empty( $value ) ) {
      return esc_html__( 'Fatal Error! This field is required!', 'cs-framework' );
    }
  }
  add_filter( 'cs_validate_required', 'cs_validate_required' );
}


if( ! function_exists( 'cs_validate_cron' ) ) {
  function cs_validate_cron( $value ) {
	
	wp_clear_scheduled_hook("my_task_hook");
		
	if ($value == "5min"){
		wp_schedule_event( time(), '5min', 'my_task_hook' );
	}
	else 
	{
		wp_schedule_event( time(), '15min', 'my_task_hook' );		

	}
  }

  
  add_filter( 'cs_validate_cron', 'cs_validate_cron',10,2 );
}

