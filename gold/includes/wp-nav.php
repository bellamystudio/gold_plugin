<?php

function cptui_register_my_cpts_metal_prices() {

	/**
	 * Post Type: Metal Prices.
	 */

	$labels = array(
		"name" => __( "Metal Prices", "twentyfifteen" ),
		"singular_name" => __( "Metal Price", "twentyfifteen" ),
		"menu_name" => __( "Metal Prices", "twentyfifteen" ),
		"all_items" => __( "All Metal Prices", "twentyfifteen" ),
		"add_new" => __( "Add New", "twentyfifteen" ),
		"add_new_item" => __( "Add New Metal Price", "twentyfifteen" ),
		"edit_item" => __( "Edit Metal Price", "twentyfifteen" ),
		"new_item" => __( "New Metal Price", "twentyfifteen" ),
		"view_item" => __( "View Metal Price", "twentyfifteen" ),
		"view_items" => __( "View Metal Prices", "twentyfifteen" ),
		"search_items" => __( "Search Metals", "twentyfifteen" ),
		"not_found" => __( "No Metal", "twentyfifteen" ),
		"not_found_in_trash" => __( "No Metals found in Bin", "twentyfifteen" ),
		"featured_image" => __( "Metal Image", "twentyfifteen" ),
		"set_featured_image" => __( "Set Metal Image", "twentyfifteen" ),
		"remove_featured_image" => __( "Remove Metal Image", "twentyfifteen" ),
		"use_featured_image" => __( "Use as a Metal image", "twentyfifteen" ),
		"archives" => __( "Metal Archive", "twentyfifteen" ),
		"insert_into_item" => __( "Insert in Metal Price", "twentyfifteen" ),
		"uploaded_to_this_item" => __( "Uploaded to this Metal", "twentyfifteen" ),
		"filter_items_list" => __( "Filter Metals List", "twentyfifteen" ),
		"items_list_navigation" => __( "Metals List Nav", "twentyfifteen" ),
		"items_list" => __( "Metals List", "twentyfifteen" ),
		"attributes" => __( "Metal Price Attributes", "twentyfifteen" ),
	);

	$args = array(
		"label" => __( "Metal Prices", "twentyfifteen" ),
		"labels" => $labels,
		"description" => "Cost of Metal Set By Admins",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "metal_prices", "with_front" => true ),
		"query_var" => true,
		"menu_position" => 5,
		"menu_icon" => "http://clientdev.org/bog/wp-content/uploads/2017/11/gold.png",
		"supports" => array( "title", "revisions" ),
	);

	register_post_type( "metal_prices", $args );
}

add_action( 'init', 'cptui_register_my_cpts_metal_prices' );
