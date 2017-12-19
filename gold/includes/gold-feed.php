<?php

function set_up_feeds()
{
	write_log("SETUP FEED: Start");
	$feeds_enabled  = cs_get_option("feeds_enabled");
		// Check if we can get feeds first
	if ($feeds_enabled)
	{

		$json_price_feed   = cs_get_option("json_price_feed");
		$json_feed_enabled = cs_get_option("json_feed_enabled");
		
		if (($json_feed_enabled) && (!empty($json_price_feed)) )
		{
      write_log('fetching json feed');
      $content = file_get_contents($json_price_feed);
      $json = json_decode($content, true);

      $gold_price = gram_price($json['gold_ask_gbp_toz']); 
			$gold_diff  = $json['gold_change_percent_gbp_toz'];
			write_feed_to_db("Gold",$gold_price,$gold_diff);
      		
			$silver_price = gram_price($json['silver_ask_gbp_toz']); 
			$silver_diff  = $json['silver_change_percent_gbp_toz'];
			write_feed_to_db("Silver",$silver_price,$silver_diff);

			$platinum_price = gram_price($json['platinum_ask_gbp_toz']);
			$platinum_diff  = $json['platinum_change_percent_gbp_toz'];
			write_feed_to_db("Platinum",$platinum_price,$platinum_diff);

      $palladium_price = gram_price($json['palladium_ask_gbp_toz']);
			$palladium_diff  = $json['palladium_change_percent_gbp_toz'];;
			write_feed_to_db("Palladium",$palladium_price,$palladium_diff);
		}
		
	}
	
	write_log("SETUP FEED: End");
}

function gram_price($oz_price) {
  $gram_price = $oz_price / 31.1035;
  return number_format((float)$gram_price, 2, '.', '');
}

/**
  *
 * Write the values to a database
 *
 **/
 
function write_feed_to_db($feed_type,$metal_price,$metal_price_diff)
{
 	global $post;

	// Get the Gold ID
	//  $term = term_exists('gold', 'metal_type');	
	//  $category_id = $term['term_id'];
	
	if ( (!empty($metal_price)) && (!empty($metal_price_diff)) )
	{
	  $meta_data = array();
	  $meta_data['metal_price'] = $metal_price;	
	  $meta_data['metal_price_diff'] = $metal_price_diff;	
	 
	  $my_post = array(
			'post_title'    => $feed_type . ' Price - ' . date("D M j G:i:s T Y"),
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'	  	=> 'metal_prices',
			'meta_input' => array(
				'_custom_metal_price_options' => $meta_data,
			),
		);
	  
		// Insert the post into the database
	   $post_id = wp_insert_post( $my_post );
	   // Set the taxonomy so we know what this price belong to
	   
	   $tax_id = wp_set_object_terms($post_id,$feed_type,'metal_type');
		if ( is_wp_error( $tax_id ) ) {
		// There was an error somewhere and the terms couldn't be set.
			write_log("ERROR: Taxonomy could not be set up" . $feed_type);
	   }
	   // Below is an alterantive was to write to the DB for the Gold Prices
	   //update_post_meta($post_id,  'metal_price' ,$metal_price);
	
		write_log("FEED: Insert Post" . $post_id);
	} 
		else 
	{
		write_log("ERROR FEED: We are unable to write DB as the feed values were null for ". $feed_type);
	}
}

/**
 *
 * Draw down feed from URL
 *
 **/
// function get_feed($url,$feed_type)
// {
// 	write_log("GET FEED: START ");	
// 	global $post;
	 
// 	$args = array(
// 		'timeout'     => 5,
// 		'redirection' => 5,
// 		'httpversion' => '1.0',
// 		'user-agent'  => 'WordPress/4.9'. home_url(),
// 		'blocking'    => true,
// 		'headers'     => array(),
// 		'cookies'     => array(),
// 		'body'        => null,
// 		'compress'    => false,
// 		'decompress'  => true,
// 		'sslverify'   => true,
// 		'stream'      => false,
// 		'filename'    => null
// 	); 


// 	write_log("GET FEED: Getting Feed " . $url);
// 	$response = wp_remote_get($url,$args);
	
// 	if ( is_array( $response ) ) {
//   		$header = $response['headers']; // array of http header lines
// 		if ($response['response']['code'] == 200) { 
// 			// This mean we loaded everything okay
//   				$metal_price = $response['body']; // use the content
// 			// Store the body
// 			write_log("GET FEED: Successful - ". $metal_price);
		
// 		} else {
// 				write_log("GET FEED: Failed");
// 				write_log($response);			
// 		}
// 	}

//     write_log("GET FEED: END ");		
// 	return $metal_price;

// }

/**
 *
 * Custom function for Usort to bring highest price to the top
 * 
 **/
 

function sortByPrice($x, $y) {
    return $y['metal_price'] - $x['metal_price'];
}


/**
 *
 *  Get the metal price
 * 
 **/
 
function calculate_metal_price($prices,$metal_type,$include_diff = false)
{
	
	// Sort from high to low
	// remove emmpy or null
	// get average and highest
	switch ($metal_type) {
		case 'gold':
			$price_threshold  = cs_get_option("gold_price_threshold");
			$price_fallback  	= cs_get_option("gold_price_fallback");
			break;
		case 'silver':
			$price_threshold  = cs_get_option("silver_price_threshold");
			$price_fallback  	= cs_get_option("silver_price_fallback");
			break;
		case 'platinum':
			$price_threshold  = cs_get_option("platinum_price_threshold");
			$price_fallback  	= cs_get_option("platinum_price_fallback");
			break;
		case 'palladium':
			$price_threshold   	= cs_get_option("palladium_price_threshold");
			$price_fallback  	= cs_get_option("palladium_price_fallback");
			break;					
	}
	
	$admin_email  =  cs_get_option("admin_email");
	
	
	$new_prices = array();
	$new_prices_diff = array();
		
	usort($prices, 'sortByPrice');
	$prices = remove_empty($prices); // drop any blank elements


	$sum = array_sum(array_column($prices, 'metal_price'));;
	$average = $sum / count($prices);
	$highest = $prices[0]['metal_price'];
	$diff  = $prices[0]['metal_price_diff'];
	
	
	if (($highest < $price_threshold) || ($highest == 0))
		{
		// tell admins we have issues ith price
			wp_mail( $admin_email, 'Feed Issues', $metal_type . 'is either 0 or below threshold '. $highest . ' < '. $price_threshold);
			write_log("ERROR: Price too low" . $highest . " < ". $price_threshold);
			write_log("ERROR: Override price with fall back");
			$highest = $price_fallback;			
		}
	
	write_log("CALCULATION PRICE: " . $highest.  " - ". $average);  
	if ($include_diff)
	{
		return array(round($highest,2),$diff);
	} else {
		return round($highest,2);
	}
}

function remove_empty($arr) {
 	foreach($arr as $key=>$values){
    $a = array_keys($values);
    $n = count($a);
    for($i=0,$count=0;$i<$n;$i++){
        if($values[$a[$i]]==NULL){
	        unset($arr[$key]);
        }
    }
    if($count==$n){
    }
}
	return $arr;
}

function _remove_empty_internal($value) {
  return !empty($value) || $value === 0;
}


/**
 *
 * Create Metal Price Short code
 *
 **/
  
function metal_price_shortcode($type)
{
	extract(shortcode_atts(array(
        'type' => 'type'
    ), $type));
  
  	$args = array(
				'numberposts' => 10,
				'offset' => 0,
				'category' => 0,
				'orderby' => 'post_date',
				'order' => 'DESC',
				'include' => '',
				'exclude' => '',
				'meta_key' => '',
				'meta_value' =>'',
				'post_type' => 'metal_prices',
				'post_status' => 'publish',
				'tax_query' => array(
						array(
						  'taxonomy' => 'metal_type',
						  'field' => 'slug',
						  'terms' => $type, // Where term_id of Term 1 is "1".
						  'include_children' => false
						),
					),
				'suppress_filters' => true
			);
		
			$recent_prices = wp_get_recent_posts( $args, ARRAY_A );	
			
			$prices = array();
			
			foreach ($recent_prices as $recent_price)
			{
				$prices[] = get_post_meta($recent_price['ID'],'_custom_metal_price_options',true);
			}
			
			$metal_price = calculate_metal_price($prices,$type,true);
			
  			$metal_html = "<span class='metal_prices'>";
			$metal_html .= "<span class='metal_price'>Price: " . $metal_price[0] . "</span><span class='metal_price_diff'> Change: " . $metal_price[1] ."</span>";		
			$metal_html .= "</span";
			
	return $metal_html;	
	
}

add_shortcode("metal_price","metal_price_shortcode");