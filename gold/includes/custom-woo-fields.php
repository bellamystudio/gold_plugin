<?php

write_log('LOADING: Woo Fields');

/**
 * Adding New WooCommerce Fields for Gold Price ****
 *
 * @return Null
 **/

function add_custom_price_box() {
/*

To get the data out from this field
From the editor click find content-single-product.php file and add the following line of code at appropriate place.

echo get_post_meta( get_the_ID(), ‘gold_fine_content’, true ); 
 
*/
 


	woocommerce_wp_text_input(
		array(
			'id' => 'gold_fine_content',
			'class' => 'wc_input_gold_fine_content short',
			'label' => __( 'Fine Content', 'woocommerce' )
		)
	);	
}

add_action( 'woocommerce_product_options_general_product_data', 'add_custom_price_box' );

/**
 * Save or Update WooCommerce Meta Data for Gold Prices 
 *
 * @return null
 **/

function custom_woocommerce_process_product_meta( $post_id ) {


	$gold_fine_content =  stripslashes( $_POST['gold_fine_content'] );
	update_post_meta( $post_id, 'gold_fine_content',  $gold_fine_content );
}



add_action( 'woocommerce_process_product_meta', 'custom_woocommerce_process_product_meta', 2 );
add_action( 'woocommerce_process_product_meta_variable', 'custom_woocommerce_process_product_meta', 2 );


/**
 *
 * Auto update cart after quantity change
 *
 * @return  string
 **/
 
 
// We are going to hook this on priority 31, so that it would display below add to cart button - Hopefully! 


function woocommerce_total_product_price() {
    global $woocommerce, $product;
    // let's setup our divs
    echo sprintf('<div id="product_total_price" style="margin-bottom:20px;display:none">%s %s</div>',__('Product Total:','woocommerce'),'<span class="price">'.$product->get_price().'</span>');
    echo sprintf('<div id="cart_total_price" style="margin-bottom:20px;display:none">%s %s</div>',__('Cart Total:','woocommerce'),'<span class="price">'.$product->get_price().'</span>');
    ?>
        <script>
            jQuery(function($){
                var price = <?php echo $product->get_price(); ?>,
                    current_cart_total = <?php echo $woocommerce->cart->cart_contents_total; ?>,
                    currency = '<?php echo get_woocommerce_currency_symbol(); ?>';
 
                $('[name=quantity]').change(function(){
                    if (!(this.value < 1)) {
                        var product_total = parseFloat(price * this.value),
                        cart_total = parseFloat(product_total + current_cart_total);
 
                        $('#product_total_price .price').html( currency + product_total.toFixed(2));
                        $('#cart_total_price .price').html( currency + cart_total.toFixed(2));
                    }
                    $('#product_total_price,#cart_total_price').toggle(!(this.value <= 1));
 
                });
            });
        </script>
    <?php
}

add_action( 'woocommerce_single_product_summary', 'woocommerce_total_product_price', 31 );


