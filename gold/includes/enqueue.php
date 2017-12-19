<?php
/**
 * Load related files
 * Project: GOLD
 * 
 * Author: Ian Bryce | Bellamy Studios
 * 
 *
**/


function gold_scripts_func() {
	// Long winded way to enqueue, however, it provides auto versioning to allow development on the fly and ensuring the right version appears on the site.
	// Gets file modification time via filemtime
	
	$script_path = GOLD_PLUGIN_DIR . "/js/gold.js";
	$script_ver  = filemtime($script_path);
	
	
	wp_register_script('gold_js', GOLD_PLUGIN_URL . 'js/gold.js' , array('jquery'),'1.1.'.$script_ver, true);
	wp_enqueue_script('gold_js');
	
	$style_path = GOLD_PLUGIN_DIR . "/css/gold_style.css";
	$style_ver  = filemtime($style_path);

	wp_register_style('gold_stylesheet', GOLD_PLUGIN_URL . 'css/gold_style.css', array(),'1.1.'.$style_ver, true);
	wp_enqueue_style('gold_stylesheet');
	
	
}

add_action( 'wp_enqueue_scripts', 'gold_scripts_func' );  

