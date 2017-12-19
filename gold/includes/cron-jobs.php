<?php

/**
 *
 * Create new Cron Schedules 
 *
 **/
 
function my_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    if(!isset($schedules["15min"])){
        $schedules["15min"] = array(
            'interval' => 15*60,
            'display' => __('Once every 15 minutes'));
    }
    return $schedules;
}

add_filter('cron_schedules','my_cron_schedules');


/**
 * 
 * Register Cron Hooks in WordPress
 *
 **/

if (!wp_next_scheduled('my_task_hook')) {

	if (cs_get_option("feed_collect_time") == "5min"){
		wp_schedule_event( time(), '5min', 'my_task_hook' );
	}
	else 
	{
		wp_schedule_event( time(), '15min', 'my_task_hook' );		
	}
	write_log('CRON: Adding Cron to System');
} else {
	write_log('CRON: Already Added');		
}


/**
 * 
 * My Cron Task to Execute
 *
 **/
 

function my_task_function() {
	write_log('CRON: Called and Actioned');
	set_up_feeds();
	prune_posts();

	// wp_mail( 'ian@digitalmovement.co.uk', 'Automatic email', 'Automatic scheduled 5 mine from WordPress.');
}

add_action ( 'my_task_hook', 'my_task_function' );


/**
 *
 * Remove cron jobs upon deactivation
 * 
 **/
 
function cron_deactivation() {
	wp_clear_scheduled_hook('my_task_hook');
}

register_deactivation_hook(GOLD_PLUGIN_FULL_PATH, 'cron_deactivation');

