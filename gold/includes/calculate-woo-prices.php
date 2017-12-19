<?
write_log('LOADING: Woo Calculator');

/**
 *
 * Get all products and variations and sort alphbetically, return in array (title, sku, id) ***
 *
 **/

function get_woocommerce_product_list($this_metal_type) {
	
	global $posts;
	
	$full_product_list = array();
	$args  = array( 'post_type' => array('product', 'product_variation'), 'posts_per_page' => -1,
		'tax_query' => array(
						array(
						  'taxonomy' => 'metal_type',
						  'field' => 'slug',
						  'terms' => $this_metal_type, // Where term_id of Term 1 is "1".
						  'include_children' => false
						),
					),
	
	);
	$loop = new WP_Query( $args);
  
 
	while ( $loop->have_posts() ) : $loop->the_post();
		$theid = get_the_ID();
		$product = new WC_Product($theid);
		
		// its a variable product
		if( get_post_type() == 'product_variation' ){
			$parent_id = wp_get_post_parent_id($theid );
			$sku = get_post_meta($theid, '_sku', true );
			$item_weight = get_post_meta($theid, '_weight', true );

			$terms = get_the_terms( $theid, 'metal_type' );
			$metal_type = $terms[0]->slug;
			
			$thetitle = get_the_title( $parent_id);
 
    // ****** Some error checking for product database *******
            // check if variation sku is set
            if ($sku == '') {
                if ($parent_id == 0) {
            		// Remove unexpected orphaned variations.. set to auto-draft
            		$false_post = array();
                    $false_post['ID'] = $theid;
                    $false_post['post_status'] = 'auto-draft';
               //     wp_update_post( $false_post );
                    if (function_exists(add_to_debug)) add_to_debug('false post_type set to auto-draft. id='.$theid);
                } else {
                    // there's no sku for this variation > copy parent sku to variation sku
                    // & remove the parent sku so the parent check below triggers
                    $sku = get_post_meta($parent_id, '_sku', true );
                    if (function_exists(add_to_debug)) add_to_debug('empty sku id='.$theid.'parent='.$parent_id.'setting sku to '.$sku);
                //    update_post_meta($theid, '_sku', $sku );
                //    update_post_meta($parent_id, '_sku', '' );
                }
            }
 	// ****************** end error checking *****************
 
        // its a simple product
        } else {
            $sku = get_post_meta($theid, '_sku', true );
			$item_weight = get_post_meta($theid, '_weight', true );
			if ($item_weight < 1) { $item_weight = 1; 
				write_log("ERROR: Wieght Less Than 1");	
			}
		 
            $thetitle = get_the_title();
			$terms = get_the_terms( $theid, 'metal_type' );
			$metal_type = $terms[0]->slug;

        }
        // add product to array but don't add the parent of product variations
        if (!empty($sku)) $full_product_list[] = array($thetitle, $sku, $metal_type, $theid,$item_weight);
    endwhile; wp_reset_query();
    // sort into alphabetical order, by title
    sort($full_product_list);
    return $full_product_list;
}

/**
 *
 * Write the feed information for metal prices to the database
 *
 **/

function update_prices_based_on_feed()
{
	  global $product,$post;
	// Loop metal types, gold, silver etc... 
	// Get the last know good price
	// Loop ID's
	// Update Base Price
	$taxonomies = get_terms('metal_type'); 
	
	foreach ($taxonomies as $this_tax)
	{
		$this_metal_type = $this_tax->slug;

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
						  'terms' => $this_metal_type, // Where term_id of Term 1 is "1".
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
			
		//	print_r($prices);
			
					
		//	$metal_price = $prices[0]['metal_price']; 
			$metal_price = calculate_metal_price($prices,$this_metal_type);
		//	print_r($metal_price);

			//print_r($prices);
			$products = get_woocommerce_product_list($this_metal_type);	
		//	print_r($products);
			
			foreach ($products as $product)
			{
				$product_id = $product[3]; // ID is store in the 4th element
				$this_price = $metal_price * $product[4]; // Metal price * Weight  
				$this_product = wc_get_product( $product_id );
				 update_post_meta($product_id, '_price', (float)$this_price);
				 update_post_meta($product_id, '_regular_price', (float)$this_price);
			}
			
	}
}
