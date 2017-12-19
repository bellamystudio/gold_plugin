<?php
function register_my_cpts_metal_prices() {

	/**
	 * Post Type: Metal Prices.
	 */

	$labels = array(
		"name" => __( "Metal Prices", "storefront" ),
		"singular_name" => __( "Metal Price", "storefront" ),
		"menu_name" => __( "Metal Prices", "storefront" ),
		"all_items" => __( "All Metal Prices", "storefront" ),
		"add_new" => __( "Add New", "storefront" ),
		"add_new_item" => __( "Add New Metal Price", "storefront" ),
		"edit_item" => __( "Edit Metal Price", "storefront" ),
		"new_item" => __( "New Metal Price", "storefront" ),
		"view_item" => __( "View Metal Price", "storefront" ),
		"view_items" => __( "View Metal Prices", "storefront" ),
		"search_items" => __( "Search Metals", "storefront" ),
		"not_found" => __( "No Metal", "storefront" ),
		"not_found_in_trash" => __( "No Metals found in Bin", "storefront" ),
		"featured_image" => __( "Metal Image", "storefront" ),
		"set_featured_image" => __( "Set Metal Image", "storefront" ),
		"remove_featured_image" => __( "Remove Metal Image", "storefront" ),
		"use_featured_image" => __( "Use as a Metal image", "storefront" ),
		"archives" => __( "Metal Archive", "storefront" ),
		"insert_into_item" => __( "Insert in Metal Price", "storefront" ),
		"uploaded_to_this_item" => __( "Uploaded to this Metal", "storefront" ),
		"filter_items_list" => __( "Filter Metals List", "storefront" ),
		"items_list_navigation" => __( "Metals List Nav", "storefront" ),
		"items_list" => __( "Metals List", "storefront" ),
		"attributes" => __( "Metal Price Attributes", "storefront" ),
	);

	$args = array(
		"label" => __( "Metal Prices", "storefront" ),
		"labels" => $labels,
		"description" => "Cost of Metal Set By Admins",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => true,
		"show_in_menu" => true,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "metal_prices", "with_front" => true ),
		"query_var" => true,
		"menu_position" => 5,
		"menu_icon" => GOLD_PLUGIN_URL . "images/gold.png",
		"supports" => array( "title", "revisions", "page-attributes" ),
		"taxonomies" => array( "metal_type" ),
	);

	register_post_type( "metal_prices", $args );
}

add_action( 'init', 'register_my_cpts_metal_prices' );



function register_my_taxes_metal_type() {

	/**
	 * Taxonomy: Metal Types.
	 */

	$labels = array(
		"name" => __( "Metal Types", "storefront" ),
		"singular_name" => __( "Metal Type", "storefront" ),
	);

	$args = array(
		"label" => __( "Metal Types", "storefront" ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Metal Types",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'metal_type', 'with_front' => true, ),
		"show_admin_column" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"show_in_quick_edit" => false,
	);
	register_taxonomy( "metal_type", array("product","metal_prices" ), $args );
}

add_action( 'init', 'register_my_taxes_metal_type' );



/**
 *
 * Check if the metals exist, if not left create them
 *
 **/
 

function check_if_metal_types_exist()
{
	write_log("Checking Metal Types Exist");
	$term = term_exists('gold', 'metal_type');
	if ($term === null) {
		write_log("Making GOLD");

		wp_insert_term(
		  'Gold', // the term 
		  'metal_type', // the taxonomy
		  array(
			'description'=> 'Gold Metal',
			'slug' => 'gold',
			'parent'=> 0  // get numeric term id
		  )
		);
	}
	
	$term = term_exists('silver', 'metal_type');
	if ($term === null) {
		write_log("Making SILVER");

		wp_insert_term(
		  'Silver', // the term 
		  'metal_type', // the taxonomy
		  array(
			'description'=> 'Silver Metal',
			'slug' => 'silver',
			'parent'=> 0  // get numeric term id
		  )
		);
	}

	$term = term_exists('platinum', 'metal_type');
	if ($term === null) {
		write_log("Making Platinum");

		wp_insert_term(
		  'Platinum', // the term 
		  'metal_type', // the taxonomy
		  array(
			'description'=> 'Platinum Metal',
			'slug' => 'platinum',
			'parent'=> 0  // get numeric term id
		  )
		);
	}

	$term = term_exists('palladium', 'metal_type');
	if ($term === null) {
		write_log("Making Palladium");

		wp_insert_term(
		  'Palladium', // the term 
		  'metal_type', // the taxonomy
		  array(
			'description'=> 'Palladium Metal',
			'slug' => 'palladium',
			'parent'=> 0  // get numeric term id
		  )
		);
	}
	
	
	write_log("End of Metal Types Exist");			
}




