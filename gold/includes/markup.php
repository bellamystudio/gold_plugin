<?php
/*
From: WooCommerce Bulk Discount
RI: http://wordpress.org/plugins/woocommerce-bulk-discount/
Author: Rene Puchinger
Version: 2.4.5
Author URI: https://profiles.wordpress.org/rene-puchinger/
License: GPL3

Modified to work as a markup 
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return; // Check if WooCommerce is active

if ( !class_exists( 'Woo_Bulk_Discount_Plugin_t4m' ) ) {

	class Woo_Bulk_Discount_Plugin_t4m {

		var $discount_coeffs;
		var $bulk_discount_calculated = false;

		public function __construct() {

			load_plugin_textdomain( 'wc_bulk_discount', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

			$this->current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';

			$this->settings_tabs = array(
				'bulk_discount' => __( 'Metal Price Markup', 'wc_bulk_discount' )
			);

			add_action( 'admin_enqueue_scripts', array( $this, 'action_enqueue_dependencies_admin' ) );
			add_action( 'wp_head', array( $this, 'action_enqueue_dependencies' ) );

			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

			add_action( 'woocommerce_settings_tabs', array( $this, 'add_tab' ), 10 );

			// Run these actions when generating the settings tabs.
			foreach ( $this->settings_tabs as $name => $label ) {
				add_action( 'woocommerce_settings_tabs_' . $name, array( $this, 'settings_tab_action' ), 10 );
				add_action( 'woocommerce_update_options_' . $name, array( $this, 'save_settings' ), 10 );
			}

			// Add the settings fields to each tab.
			add_action( 'woocommerce_bulk_discount_settings', array( $this, 'add_settings_fields' ), 10 );

			add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ) );

		}
	
		/**
		 * Main processing hooks
		 */
		public function woocommerce_loaded() {

			add_action( 'woocommerce_before_calculate_totals', array( $this, 'action_before_calculate' ), 10, 1 );
			add_action( 'woocommerce_calculate_totals', array( $this, 'action_after_calculate' ), 10, 1 );
			add_action( 'woocommerce_before_cart_table', array( $this, 'before_cart_table' ) );
			add_action( 'woocommerce_single_product_summary', array( $this, 'single_product_summary' ), 45 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'filter_subtotal_price' ), 10, 2 );
			add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'filter_subtotal_price' ), 10, 2 );
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'filter_subtotal_order_price' ), 10, 3 );
			add_filter( 'woocommerce_product_write_panel_tabs', array( $this, 'action_product_write_panel_tabs' ) );
			
		//	add_filter( 'woocommerce_get_price_html', 'wpa83367_price_html', 100, 2 );


			
			if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
				add_filter( 'woocommerce_product_data_panels', array( $this, 'action_product_write_panels' ) );
			} else {
				add_filter( 'woocommerce_product_write_panels', array( $this, 'action_product_write_panels' ) );
			}
			add_action( 'woocommerce_process_product_meta', array( $this, 'action_process_meta' ) );
			add_filter( 'woocommerce_cart_product_subtotal', array( $this, 'filter_cart_product_subtotal' ), 10, 3 );



			// Added by IB to update the the product
			//add_filter( 'woocommerce_product_get_price', array( $this, 'filter_cart_product_price' ), 10, 2 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'filter_cart_product_price' ), 10, 2 );
			
			// IB END
			
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'order_update_meta' ) );

			if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {
				add_filter( 'woocommerce_cart_item_price', array( $this, 'filter_item_price' ), 10, 2 );
				add_filter( 'woocommerce_update_cart_validation', array( $this, 'filter_before_calculate' ), 10, 1 );
			} else {
				add_filter( 'woocommerce_cart_item_price_html', array( $this, 'filter_item_price' ), 10, 2 );
			}

		}
		




		function return_custom_price($price, $product) {			
			global $post, $blog_id;
			print_r($product);
			$price = get_post_meta($post->ID, '_regular_price');
			$post_id = $post->ID;
			$price = ($price[0]*2);
			$price = 100;
			return $price;
		}

		/**
		 * Add action links under WordPress > Plugins
		 *
		 * @param $links
		 * @return array
		 */
		public function action_links( $links ) {

			$settings_slug = 'woocommerce';

			if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {

				$settings_slug = 'wc-settings';

			}

			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=' . $settings_slug . '&tab=bulk_discount' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * For given product, and quantity return the price modifying factor (percentage discount) or value to deduct (flat & fixed discounts).
		 *
		 * @param $product_id
		 * @param $quantity
		 * @param $order
		 * @return float
		 */
		protected function get_discounted_coeff( $product_id, $quantity ) {

			$q = array( 0.0 );
			$d = array( 0.0 );

			$configurer = get_page_by_title( 'wc_bulk_discount_configurer', OBJECT, 'product' );
			if ( $configurer && $configurer->ID && $configurer->post_status == 'private' ) {
				$product_id = $configurer->ID;
			}

			$product = $this->get_product($product_id);
			if ($product instanceof WC_Product_Variation) {
				$product_id = $product->get_parent_id();
			}
			
			/* Find the appropriate discount coefficient by looping through up to the five discount settings */
			for ( $i = 1; $i <= 5; $i++ ) {
				array_push( $q, get_post_meta( $product_id, "_bulkdiscount_quantity_$i", true ) );
				if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) {
					array_push( $d, get_post_meta( $product_id, "_bulkdiscount_discount_flat_$i", true ) ? get_post_meta( $product_id, "_bulkdiscount_discount_flat_$i", true ) : 0.0 );
				} else if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) {
					array_push( $d, get_post_meta( $product_id, "_bulkdiscount_discount_fixed_$i", true ) ? get_post_meta( $product_id, "_bulkdiscount_discount_fixed_$i", true ) : 0.0 );
				} else {
					array_push( $d, get_post_meta( $product_id, "_bulkdiscount_discount_$i", true ) ? get_post_meta( $product_id, "_bulkdiscount_discount_$i", true ) : 0.0 );
				}
				if ( $quantity >= $q[$i] && $q[$i] > $q[0] ) {
					$q[0] = $q[$i];
					$d[0] = $d[$i];
				}
			}
			
			// for percentage discount convert the resulting discount from % to the multiplying coefficient
			if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) {
				return max( 0, $d[0] * $quantity );
			}
//			return ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ? max( 0, $d[0] ) : min( 1.0, max( 0, ( 100.0 - round( $d[0], 2 ) ) / 100.0 ) );

		// IB Creating markup instead of mark down
		
		 $percent =  max( 1, (  round( $d[0], 2 ) / 100.0 ) +1);
		 
		 write_log("Percent Markup: " . $percent);
		  
			return ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ? max( 0, $d[0] ) : $percent;

		}

		/**
		 * Filter product price so that the discount is visible.
		 *
		 * @param $price
		 * @param $values
		 * @return string
		 */
		public function filter_item_price( $price, $values ) {

			if ( !$values || @!$values['data'] ) {
				return $price;
			}
			if ( $this->coupon_check() ) {
				return $price;
			}
			$_product = $values['data'];
			if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
				return $price;
			}
			if ( ( get_option( 'woocommerce_t4m_show_on_item', 'yes' ) == 'no' ) ) {
//				return $price;
			}
			if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
				return $price; // for flat discount this filter has no meaning
			}
			if ( empty( $this->discount_coeffs ) || !isset( $this->discount_coeffs[$this->get_actual_id( $_product )] )
				|| !isset( $this->discount_coeffs[$this->get_actual_id( $_product )]['orig_price'] ) || !isset( $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] )
			) {
				$this->gather_discount_coeffs();
			}
			$coeff = $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'];
			if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' && $coeff == 0 ) || ( get_option( 'woocommerce_t4m_discount_type', '' ) == '' && $coeff == 1.0 ) ) {
				return $price; // no price modification
			}

			if ( !$this->bulk_discount_calculated ) {
				if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
					$discprice = $this->get_price( $_product->get_price() - $coeff );
				} else if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) ) {
					$discprice = $this->get_price( $_product->get_price() - $coeff / $this->discount_coeffs[$this->get_actual_id( $_product )]['quantity'] );
				} else {
					$discprice = $this->get_price( $_product->get_price() * $coeff );
				}
			} else {
				$discprice = $this->get_price( $_product->get_price() );
			}

			$oldprice = $this->get_price( $this->discount_coeffs[$this->get_actual_id( $_product )]['orig_price'] );
			$old_css = esc_attr( get_option( 'woocommerce_t4m_css_old_price', 'color: #777; text-decoration: line-through; margin-right: 4px;' ) );
			$new_css = esc_attr( get_option( 'woocommerce_t4m_css_new_price', 'color: #4AB915; font-weight: bold;' ) );

			if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) {
				return "<span class='discount-info' title='" . sprintf( __( '%s bulk discount applied!', 'wc_bulk_discount' ), $this->get_price( $coeff / $this->discount_coeffs[$this->get_actual_id( $_product )]['quantity'] ) ) . "'>" .
				"<span class='old-price' style='$old_css'>$oldprice</span>" .
				"<span class='new-price' style='$new_css'>$discprice</span></span>";

			} else {
				
				// Added by IB
				if ( ( get_option( 'woocommerce_t4m_show_on_item', 'yes' ) == 'no' ) ) {
					return "<span class='discount-info' title='" . sprintf( __( '%s%% bulk discount applied!', 'wc_bulk_discount' ), round( ( 1.0 - $coeff ) * 100.0, 2 ) ) . "'>" .
					"<span class='new-price' >$discprice</span></span>";
				} else {
						return "<span class='discount-info' title='" . sprintf( __( '%s%% bulk discount applied!', 'wc_bulk_discount' ), round( ( 1.0 - $coeff ) * 100.0, 2 ) ) . "'>" .
					"<span class='old-price' style='$old_css'>$oldprice</span>" .
					"<span class='new-price' style='$new_css'>$discprice</span></span>";			
					
				}

			}

		}

		/**
		 * Filter product price so that the discount is visible.
		 *
		 * @param $price
		 * @param $values
		 * @return string
		 */
		public function filter_subtotal_price( $price, $values ) {

			if ( !$values || !$values['data'] ) {
				return $price;
			}
			if ( $this->coupon_check() ) {
				return $price;
			}
			$_product = $values['data'];
			if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
				return $price;
			}
			if ( ( get_option( 'woocommerce_t4m_show_on_subtotal', 'yes' ) == 'no' ) ) {
				return $price;
			}
			if ( empty( $this->discount_coeffs ) || !isset( $this->discount_coeffs[$this->get_actual_id( $_product )] )
				|| !isset( $this->discount_coeffs[$this->get_actual_id( $_product )]['orig_price'] ) || !isset( $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] )
			) {
				$this->gather_discount_coeffs();
			}
			$coeff = $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'];
			if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' && $coeff == 0 ) || ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' && $coeff == 0 ) || ( get_option( 'woocommerce_t4m_discount_type', '' ) == '' && $coeff == 1.0 ) ) {
				return $price; // no price modification
			}
			$new_css = esc_attr( get_option( 'woocommerce_t4m_css_new_price', 'color: #4AB915; font-weight: bold;' ) );
			$bulk_info = sprintf( __( 'Incl. %s markup', 'wc_bulk_discount' ), ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' || get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ? get_woocommerce_currency_symbol() . $coeff : ( round( ( $coeff ) * 100, 2 )-100 . "%" ) ) );

			return "<span class='discount-info' title='$bulk_info'>" .
			"<span>$price</span>" .
			"<span class='new-price' style='$new_css'> ($bulk_info)</span></span>";

		}

		/**
		 * Gather discount information to the array $this->discount_coefs
		 */
		protected function gather_discount_coeffs() {

			global $woocommerce;

			$cart = $woocommerce->cart;
			$this->discount_coeffs = array();

			// Inserted by IB to stop notice 
			if (!empty($cart)) 
			{
				if ( sizeof( $cart->cart_contents ) > 0 ) {
					foreach ( $cart->cart_contents as $cart_item_key => $values ) {
						$_product = $values['data'];
						$quantity = 0;
						if ( get_option( 'woocommerce_t4m_variations_separate', 'yes' ) == 'no' && $_product instanceof WC_Product_Variation && $this->get_parent($_product) ) {
							$parent = $this->get_parent($_product);
							foreach ( $cart->cart_contents as $valuesInner ) {
								$p = $valuesInner['data'];
								if ( $p instanceof WC_Product_Variation && $this->get_parent($p) && $this->get_product_id($this->get_parent($p)) == $this->get_product_id($parent) ) {
									$quantity += $valuesInner['quantity'];
									$this->discount_coeffs[$this->get_variation_id($_product)]['quantity'] = $quantity;
								}
							}
						} else {
							$quantity = $values['quantity'];
						}
						$this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] = $this->get_discounted_coeff( $this->get_product_id($_product), $quantity );
						$this->discount_coeffs[$this->get_actual_id( $_product )]['orig_price'] = $_product->get_price();
						$this->discount_coeffs[$this->get_actual_id( $_product )]['quantity'] = $quantity;
					}
				}
			}

		}

		/**
		 * Filter product price so that the discount is visible during order viewing.
		 *
		 * @param $price
		 * @param $values
		 * @return string
		 */
		public function filter_subtotal_order_price( $price, $values, $order ) {

			if ( !$values || !$order ) {
				return $price;
			}
			if ( $this->coupon_check() ) {
				return $price;
			}

			$_product = $this->get_product( $values['product_id'] );
			if ( get_post_meta( $values['product_id'], "_bulkdiscount_enabled", true ) != '' && get_post_meta( $values['product_id'], "_bulkdiscount_enabled", true ) !== 'yes' ) {
				return $price;
			}
			if ( ( get_option( 'woocommerce_t4m_show_on_order_subtotal', 'yes' ) == 'no' ) ) {
				return $price;
			}
			$actual_id = $values['product_id'];
			if ( $_product && $_product instanceof WC_Product_Variable && $values['variation_id'] ) {
				$actual_id = $values['variation_id'];
			}
			$discount_coeffs = $this->gather_discount_coeffs_from_order( $this->get_product_id($order) );
			if ( empty( $discount_coeffs ) ) {
				return $price;
			}
			@$coeff = $discount_coeffs[$actual_id]['coeff'];
			if ( !$coeff ) {
				return $price;
			}
			$discount_type = get_post_meta( $this->get_product_id($order), '_woocommerce_t4m_discount_type', true );
			if ( ( $discount_type == 'flat' && $coeff == 0 ) || ( $discount_type == 'fixed' && $coeff == 0 ) || ( $discount_type == '' && $coeff == 1.0 ) ) {
				return $price; // no price modification
			}
			$new_css = esc_attr( get_option( 'woocommerce_t4m_css_new_price', 'color: #4AB915; font-weight: bold;' ) );
			$bulk_info = sprintf( __( 'Incl. %s markup', 'wc_bulk_discount' ), ( $discount_type == 'flat' || $discount_type == 'fixed' ? get_woocommerce_currency_symbol() . $coeff : ( round( ( $coeff ) * 100, 2 ) -100  . "%" ) ) );

			return "<span class='discount-info' title='$bulk_info'>" .
			"<span>$price</span>" .
			"<span class='new-price' style='$new_css'> ($bulk_info)</span></span>";

		}

		/**
		 * Gather discount information from order.
		 *
		 * @param $order_id
		 * @return array
		 */
		protected function gather_discount_coeffs_from_order( $order_id ) {

			$meta = get_post_meta( $order_id, '_woocommerce_t4m_discount_coeffs', true );

			if ( !$meta ) {
				return null;
			}

			$order_discount_coeffs = json_decode( $meta, true );
			return $order_discount_coeffs;

		}

		/**
		 * Hook to woocommerce_before_calculate_totals action.
		 *
		 * @param WC_Cart $cart
		 */
		public function action_before_calculate( WC_Cart $cart ) {

			if ( $this->coupon_check() ) {
				return;
			}

			if ( $this->bulk_discount_calculated ) {
				return;
			}

			$this->gather_discount_coeffs();

			if ( sizeof( $cart->cart_contents ) > 0 ) {

				foreach ( $cart->cart_contents as $cart_item_key => $values ) {
					$_product = $values['data'];
					if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
						continue;
					}
					if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
						$row_base_price = max( 0, $_product->get_price() - ( $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] / $values['quantity'] ) );
					} else if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) ) {
						$row_base_price = max( 0, $_product->get_price() - ( $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] / $values['quantity'] ) );
					} else {
						$row_base_price = $_product->get_price() * $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'];
					}

					$values['data']->set_price( $row_base_price );
				}

				$this->bulk_discount_calculated = true;

			}

		}

		public function filter_before_calculate( $res ) {

			global $woocommerce;

			if ( $this->bulk_discount_calculated ) {
				return $res;
			}

			$cart = $woocommerce->cart;

			if ( $this->coupon_check() ) {
				return $res;
			}

			$this->gather_discount_coeffs();

			if ( sizeof( $cart->cart_contents ) > 0 ) {

				foreach ( $cart->cart_contents as $cart_item_key => $values ) {
					$_product = $values['data'];
					if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
						continue;
					}
					if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
						$row_base_price = max( 0, $_product->get_price() - ( $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] / $values['quantity'] ) );
					} else if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) ) {
						$row_base_price = max( 0, $_product->get_price() - ( $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'] / $values['quantity'] ) );
					} else {
						$row_base_price = $_product->get_price() * $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'];
					}

					$values['data']->set_price( $row_base_price );
				}

				$this->bulk_discount_calculated = true;

			}

			return $res;

		}

		/**
		 * @param $product
		 * @return int
		 */
		protected function get_actual_id( $product ) {

			if ( $product instanceof WC_Product_Variation ) {
				return $this->get_variation_id($product);
			} else {
				return $this->get_product_id($product);
			}

		}

		/**
		 * Hook to woocommerce_calculate_totals.
		 *
		 * @param WC_Cart $cart
		 */
		public function action_after_calculate( WC_Cart $cart ) {

			if ( $this->coupon_check() ) {
				return;
			}

			if ( !$this->bulk_discount_calculated ) {
				return;
			}

			if ( sizeof( $cart->cart_contents ) > 0 ) {
				foreach ( $cart->cart_contents as $cart_item_key => $values ) {
					$_product = $values['data'];
					if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
						continue;
					}
					$values['data']->set_price( $this->discount_coeffs[$this->get_actual_id( $_product )]['orig_price'] );
				}
				$this->bulk_discount_calculated = false;
			}

		}

		/**
		 * Show discount info in cart.
		 */
		public function before_cart_table() {

			if ( get_option( 'woocommerce_t4m_cart_info' ) != '' ) {
				echo "<div class='cart-show-discounts'>";
				echo get_option( 'woocommerce_t4m_cart_info' );
				echo "</div>";
			}

		}

		/**
		 * Hook to woocommerce_cart_product_subtotal filter.
		 *
		 * @param $subtotal
		 * @param $_product
		 * @param $quantity
		 * @param WC_Cart $cart
		 * @return string
		 */
		public function filter_cart_product_subtotal( $subtotal, $_product, $quantity ) {
			
			if ( !$_product || !$quantity ) {
				return $subtotal;
			}
			if ( $this->coupon_check() ) {
				return $subtotal;
			}
			if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
				return $subtotal;
			}

			$coeff = $this->discount_coeffs[$this->get_actual_id( $_product )]['coeff'];
			write_log($coeff);
			if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
				$newsubtotal = $this->get_price( max( 0, ( $_product->get_price() * $quantity ) - $coeff ) );
			} else if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) ) {
				$newsubtotal = $this->get_price( max( 0, ( $_product->get_price() * $quantity ) - $coeff ) );
			} else {
				$newsubtotal = $this->get_price( $_product->get_price() * $quantity * $coeff );
			}

			return $newsubtotal;

		}

		/**
		 * Hook to woocommerce_product_price filter.
		 * BY IB
		 * @param $subtotal
		 * @param $_product
		 * @param WC_Cart $cart
		 * @return string
		 */

		public function filter_cart_product_price( $theprice, $_product ) {
		$this->gather_discount_coeffs();
			$quantity = 1;
			if ( !$_product || !$quantity ) {
				return $subtotal;
			}
			if ( $this->coupon_check() ) {
				return $subtotal;
			}
			if ( get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) != '' && get_post_meta( $this->get_product_id($_product), "_bulkdiscount_enabled", true ) !== 'yes' ) {
				return $subtotal;
			}

			$coeff = $this->get_discounted_coeff( $this->get_product_id($_product), $quantity );
			

			if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
				$newsubtotal = max( 0, ( $theprice * $quantity ) - $coeff ) ;
			} else if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) ) {
				$newsubtotal = max( 0, ( $theprice * $quantity ) - $coeff );
			} else {
					$newsubtotal = $this->get_price( $_product->get_price() * $quantity * $coeff );
			}

			return $newsubtotal;

		}



		/**
		 * Store discount info in order as well
		 *
		 * @param $order_id
		 */
		public function order_update_meta( $order_id ) {

			update_post_meta( $order_id, "_woocommerce_t4m_discount_type", get_option( 'woocommerce_t4m_discount_type', '' ) );
			update_post_meta( $order_id, "_woocommerce_t4m_discount_coeffs", json_encode( $this->discount_coeffs ) );

		}

		/**
		 * Display discount information in Product Detail.
		 */
		public function single_product_summary() {

			global $thepostid, $post;
			if ( !$thepostid ) $thepostid = $post->ID;

			echo "<div class='productinfo-show-discounts'>";
			echo get_post_meta( $thepostid, '_bulkdiscount_text_info', true );
			echo "</div>";

		}

		/**
		 * Add entry to Product Settings.
		 */
		public function action_product_write_panel_tabs() {

			$style = '';

			if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {
				$style = 'style = "padding: 10px !important"';
			}

			echo '<li class="bulkdiscount_tab bulkdiscount_options"> <a href="#bulkdiscount_product_data" ' . $style . '>' . __(' Metal Price Markups', 'wc_bulk_discount' ) . '</a></li>';

		}

		/**
		 * Add entry content to Product Settings.
		 */
		public function action_product_write_panels() {

			global $thepostid, $post;

			if ( !$thepostid ) $thepostid = $post->ID;
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function () {
					var e = jQuery( '#bulkdiscount_product_data' );
					<?php
					for($i = 1; $i <= 6; $i++) :
					?>
					e.find( '.block<?php echo $i; ?>' ).hide();
					e.find( '.options_group<?php echo max($i, 2); ?>' ).hide();
					e.find( '#add_discount_line<?php echo max($i, 2); ?>' ).hide();
					e.find( '#add_discount_line<?php echo $i; ?>' ).click( function () {
						if ( <?php echo $i; ?> == 1 || ( e.find( '#_bulkdiscount_quantity_<?php echo max($i-1, 1); ?>' ).val() != '' &&
							<?php if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) : ?>
							e.find( '#_bulkdiscount_discount_flat_<?php echo max($i-1, 1); ?>' ).val() != ''
						<?php elseif ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) : ?>
						e.find( '#_bulkdiscount_discount_fixed_<?php echo max($i-1, 1); ?>' ).val() != ''
						<?php else: ?>
						e.find( '#_bulkdiscount_discount_<?php echo max($i-1, 1); ?>' ).val() != ''
						<?php endif; ?>
						) )
						{
							e.find( '.block<?php echo $i; ?>' ).show( 400 );
							e.find( '.options_group<?php echo min($i+1, 6); ?>' ).show( 400 );
							e.find( '#add_discount_line<?php echo min($i+1, 5); ?>' ).show( 400 );
							e.find( '#add_discount_line<?php echo $i; ?>' ).hide( 400 );
							e.find( '#delete_discount_line<?php echo min($i+1, 6); ?>' ).show( 400 );
							e.find( '#delete_discount_line<?php echo $i; ?>' ).hide( 400 );
						}
						else
						{
							alert( '<?php _e( 'Please fill in the current line before adding new line.', 'wc_bulk_discount' ); ?>' );
						}
					} );
					e.find( '#delete_discount_line<?php echo max($i, 1); ?>' ).hide();
					e.find( '#delete_discount_line<?php echo $i; ?>' ).click( function () {
						e.find( '.block<?php echo max($i-1, 1); ?>' ).hide( 400 );
						e.find( '.options_group<?php echo min($i, 6); ?>' ).hide( 400 );
						e.find( '#add_discount_line<?php echo min($i, 5); ?>' ).hide( 400 );
						e.find( '#add_discount_line<?php echo max($i-1, 1); ?>' ).show( 400 );
						e.find( '#delete_discount_line<?php echo min($i, 6); ?>' ).hide( 400 );
						e.find( '#delete_discount_line<?php echo max($i-1, 2); ?>' ).show( 400 );
						e.find( '#_bulkdiscount_quantity_<?php echo max($i-1, 1); ?>' ).val( '' );
						<?php
							if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) :
						?>
						e.find( '#_bulkdiscount_discount_flat_<?php echo max($i-1, 1); ?>' ).val( '' );
						<?php elseif ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ): ?>
						e.find( '#_bulkdiscount_discount_fixed_<?php echo max($i-1, 1); ?>' ).val( '' );
						<?php else: ?>
						e.find( '#_bulkdiscount_discount_<?php echo max($i-1, 1); ?>' ).val( '' );
						<?php endif; ?>
					} );
					<?php
					endfor;
					for ($i = 1, $j = 2; $i <= 5; $i++, $j++) {
						$cnt = 1;
						if (get_post_meta($thepostid, "_bulkdiscount_quantity_$i", true) || get_post_meta($thepostid, "_bulkdiscount_quantity_$j", true)) {
							?>
					e.find( '.block<?php echo $i; ?>' ).show();
					e.find( '.options_group<?php echo $i; ?>' ).show();
					e.find( '#add_discount_line<?php echo $i; ?>' ).hide();
					e.find( '#delete_discount_line<?php echo $i; ?>' ).hide();
					e.find( '.options_group<?php echo min($i+1,6); ?>' ).show();
					e.find( '#add_discount_line<?php echo min($i+1,6); ?>' ).show();
					e.find( '#delete_discount_line<?php echo min($i+1,6); ?>' ).show();
					<?php
					$cnt++;
				}
			}
			if ($cnt >= 6) {
				?>e.find( '#add_discount_line6' ).show();
					<?php
			}
			?>
				} );
			</script>

			<div id="bulkdiscount_product_data" class="panel woocommerce_options_panel">

				<div class="options_group">
					<?php
					woocommerce_wp_checkbox( array( 'id' => '_bulkdiscount_enabled', 'value' => get_post_meta( $thepostid, '_bulkdiscount_enabled', true ) ? get_post_meta( $thepostid, '_bulkdiscount_enabled', true ) : 'yes', 'label' => __( 'Metal Price Markup enabled', 'wc_bulk_discount' ) ) );
					woocommerce_wp_textarea_input( array( 'id' => "_bulkdiscount_text_info", 'label' => __( 'Markup product description', 'wc_bulk_discount' ), 'description' => __( 'Optionally enter markup information that will be visible on the product page.', 'wc_bulk_discount' ), 'desc_tip' => 'yes', 'class' => 'fullWidth' ) );
					?>
				</div>

				<?php
				for ( $i = 1;
				      $i <= 5;
				      $i++ ) :
					?>

					<div class="options_group<?php echo $i; ?>">
						<a id="add_discount_line<?php echo $i; ?>" class="button-secondary"
						   href="#block<?php echo $i; ?>"><?php _e( 'Add markup line', 'wc_bulk_discount' ); ?></a>
						<a id="delete_discount_line<?php echo $i; ?>" class="button-secondary"
						   href="#block<?php echo $i; ?>"><?php _e( 'Remove last markup line', 'wc_bulk_discount' ); ?></a>

						<div class="block<?php echo $i; ?> <?php echo ( $i % 2 == 0 ) ? 'even' : 'odd' ?>">
							<?php
							woocommerce_wp_text_input( array( 'id' => "_bulkdiscount_quantity_$i", 'label' => __( 'Quantity (min.)', 'wc_bulk_discount' ), 'type' => 'number', 'description' => __( 'Enter the minimal quantity for which the markup applies.', 'wc_bulk_discount' ), 'custom_attributes' => array(
								'step' => '1',
								'min' => '1'
							) ) );
							if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) {
								woocommerce_wp_text_input( array( 'id' => "_bulkdiscount_discount_flat_$i", 'type' => 'number', 'label' => sprintf( __( 'Markup (%s)', 'wc_bulk_discount' ), get_woocommerce_currency_symbol() ), 'description' => sprintf( __( 'Enter the flat markup in %s.', 'wc_bulk_discount' ), get_woocommerce_currency_symbol() ), 'custom_attributes' => array(
									'step' => 'any',
									'min' => '0'
								) ) );
							} else if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) {
								woocommerce_wp_text_input( array( 'id' => "_bulkdiscount_discount_fixed_$i", 'type' => 'number', 'label' => sprintf( __( 'Markup (%s)', 'wc_bulk_discount' ), get_woocommerce_currency_symbol() ), 'description' => sprintf( __( 'Enter the fixed markup in %s.', 'wc_bulk_discount' ), get_woocommerce_currency_symbol() ), 'custom_attributes' => array(
									'step' => 'any',
									'min' => '0'
								) ) );
							} else {
								woocommerce_wp_text_input( array( 'id' => "_bulkdiscount_discount_$i", 'type' => 'number', 'label' => __( 'Markup (%)', 'wc_bulk_discount' ), 'description' => __( 'Enter the markup in percents (Allowed values: 0 to 1000).', 'wc_bulk_discount' ), 'custom_attributes' => array(
									'step' => 'any',
									'min' => '0',
									'max' => '1000'
								) ) );
							}
							?>
						</div>
					</div>

				<?php
				endfor;
				?>

				<div class="options_group6">
					<a id="delete_discount_line6" class="button-secondary"
					   href="#block6"><?php _e( 'Remove last markup line', 'wc_bulk_discount' ); ?></a>
				</div>

				<br/>

			</div>

		<?php
		}

		/**
		 * Enqueue frontend dependencies.
		 */
		public function action_enqueue_dependencies() {

			//wp_register_style( 'woocommercebulkdiscount-style', plugins_url( 'css/style.css', __FILE__ ) );
			//wp_enqueue_style( 'woocommercebulkdiscount-style' );
			//wp_enqueue_script( 'jquery' );

		}

		/**
		 * Enqueue backend dependencies.
		 */
		public function action_enqueue_dependencies_admin() {

			//wp_register_style( 'woocommercebulkdiscount-style-admin', plugins_url( 'css/admin.css', __FILE__ ) );
			//wp_enqueue_style( 'woocommercebulkdiscount-style-admin' );
			//wp_enqueue_script( 'jquery' );

		}

		/**
		 * Updating post meta.
		 *
		 * @param $post_id
		 */
		public function action_process_meta( $post_id ) {

			if ( isset( $_POST['_bulkdiscount_text_info'] ) ) update_post_meta( $post_id, '_bulkdiscount_text_info', stripslashes( $_POST['_bulkdiscount_text_info'] ) );

			if ( isset( $_POST['_bulkdiscount_enabled'] ) && $_POST['_bulkdiscount_enabled'] == 'yes' ) {
				update_post_meta( $post_id, '_bulkdiscount_enabled', stripslashes( $_POST['_bulkdiscount_enabled'] ) );
			} else {
				update_post_meta( $post_id, '_bulkdiscount_enabled', stripslashes( 'no' ) );
			}

			for ( $i = 1; $i <= 5; $i++ ) {
				if ( isset( $_POST["_bulkdiscount_quantity_$i"] ) ) update_post_meta( $post_id, "_bulkdiscount_quantity_$i", stripslashes( $_POST["_bulkdiscount_quantity_$i"] ) );
				if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ) {
					if ( isset( $_POST["_bulkdiscount_discount_flat_$i"] ) ) update_post_meta( $post_id, "_bulkdiscount_discount_flat_$i", stripslashes( $_POST["_bulkdiscount_discount_flat_$i"] ) );
				} else if ( ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) ) {
					if ( isset( $_POST["_bulkdiscount_discount_fixed_$i"] ) ) update_post_meta( $post_id, "_bulkdiscount_discount_fixed_$i", stripslashes( $_POST["_bulkdiscount_discount_fixed_$i"] ) );
				} else {
					if ( isset( $_POST["_bulkdiscount_discount_$i"] ) ) update_post_meta( $post_id, "_bulkdiscount_discount_$i", stripslashes( $_POST["_bulkdiscount_discount_$i"] ) );
				}
			}

		}

		/**
		 * @access public
		 * @return void
		 */
		public function add_tab() {

			$settings_slug = 'woocommerce';

			if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {

				$settings_slug = 'wc-settings';

			}

			foreach ( $this->settings_tabs as $name => $label ) {
				$class = 'nav-tab';
				if ( $this->current_tab == $name )
					$class .= ' nav-tab-active';
				echo '<a href="' . admin_url( 'admin.php?page=' . $settings_slug . '&tab=' . $name ) . '" class="' . $class . '">' . $label . '</a>';
			}

		}

		/**
		 * @access public
		 * @return void
		 */
		public function settings_tab_action() {

			global $woocommerce_settings;

			// Determine the current tab in effect.
			$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

			do_action( 'woocommerce_bulk_discount_settings' );

			// Display settings for this tab (make sure to add the settings to the tab).
			woocommerce_admin_fields( $woocommerce_settings[$current_tab] );

		}

		/**
		 * Save settings in a single field in the database for each tab's fields (one field per tab).
		 */
		public function save_settings() {

			global $woocommerce_settings;

			// Make sure our settings fields are recognised.
			$this->add_settings_fields();

			$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_update_options_' );
			woocommerce_update_options( $woocommerce_settings[$current_tab] );

		}

		/**
		 * Get the tab current in view/processing.
		 */
		public function get_tab_in_view( $current_filter, $filter_base ) {

			return str_replace( $filter_base, '', $current_filter );

		}


		/**
		 * Add settings fields for each tab.
		 */
		public function add_settings_fields() {
			global $woocommerce_settings;

			// Load the prepared form fields.
			$this->init_form_fields();

			if ( is_array( $this->fields ) )
				foreach ( $this->fields as $k => $v )
					$woocommerce_settings[$k] = $v;
		}

		/**
		 * Prepare form fields to be used in the various tabs.
		 */
		public function init_form_fields() {
			global $woocommerce;

			// Define settings
			$this->fields['bulk_discount'] = apply_filters( 'woocommerce_bulk_discount_settings_fields', array(

				array( 'name' => __( 'Metal Price Markup', 'wc_bulk_discount' ), 'type' => 'title', 'desc' => __( 'The following options are specific to pmetal price markup.', 'wc_bulk_discount' ) . '<br /><br/><strong><i>' . __( 'After changing the settings, it is recommended to clear all sessions in WooCommerce &gt; System Status &gt; Tools.', 'wc_bulk_discount' ) . '</i></strong>', 'id' => 't4m_bulk_discounts_options' ),

				array(
					'title' => __( 'Markup Type', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_discount_type',
					'desc' => sprintf( __( 'Select the type of markup. Percentage markup amount of %% from the price while flat and fixed markup adds fixed amount in %s', 'wc_bulk_discount' ), get_woocommerce_currency_symbol() ) . '. PLEASE READ THE DOCUMENTATION CAREFULLY.',
					'desc_tip' => true,
					'std' => 'yes',
					'type' => 'select',
					'css' => 'min-width:200px;',
					'class' => 'chosen_select',
					'options' => array(
						'' => __( 'Percentage Markup', 'wc_bulk_discount' ),
						'flat' => __( 'Flat Markup', 'wc_bulk_discount' ),
						'fixed' => __( 'Fixed Markup', 'wc_bulk_discount' )
					)
				),

				array(
					'name' => __( 'Treat product variations separately', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_variations_separate',
					'desc' => __( 'You need to have this option unchecked to apply markup to variations by shared quantity.', 'wc_bulk_discount' ),
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes'
				),

				array(
					'name' => __( 'Remove any markup if a coupon code is applied', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_remove_discount_on_coupon',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes'
				),

				array(
					'name' => __( 'Show markup information next to cart item price', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_show_on_item',
					'desc' => __( 'Applies only to percentage markup.', 'wc_bulk_discount' ),
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes'
				),

				array(
					'name' => __( 'Show markup information next to item subtotal price', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_show_on_subtotal',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes'
				),

				array(
					'name' => __( 'Show markup information next to item subtotal price in order history', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_show_on_order_subtotal',
					'desc' => __( 'Includes showing markup in order e-mails and invoices.', 'wc_bulk_discount' ),
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes'
				),

				array(
					'name' => __( 'Optionally enter information about markups visible on cart page.', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_cart_info',
					'type' => 'textarea',
					'css' => 'width:100%; height: 75px;'
				),

				array(
					'name' => __( 'Optionally change the CSS for old price on cart before markup.', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_css_old_price',
					'type' => 'textarea',
					'css' => 'width:100%;',
					'default' => 'color: #777; text-decoration: line-through; margin-right: 4px;'
				),

				array(
					'name' => __( 'Optionally change the CSS for new price on cart after markup.', 'wc_bulk_discount' ),
					'id' => 'woocommerce_t4m_css_new_price',
					'type' => 'textarea',
					'css' => 'width:100%;',
					'default' => 'color: #4AB915; font-weight: bold;'
				),


				array( 'type' => 'sectionend', 'id' => 'woocommerce_t4m_bulk_discount_notice_text' )

			) ); // End settings

		}

		/**
		 * Includes inline JavaScript.
		 *
		 * @param $js
		 */
		protected function run_js( $js ) {

			global $woocommerce;

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( $js );
			} else {
				$woocommerce->add_inline_js( $js );
			}

		}

		/**
		 * @return bool
		 */
		protected function coupon_check() {

			global $woocommerce;

			if ( get_option( 'woocommerce_t4m_remove_discount_on_coupon', 'yes' ) == 'no' ) return false;
			return !( empty( $woocommerce->cart->applied_coupons ) );
		}

		protected function get_product_id($_product) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
				return $_product->get_id();
			}
			return $_product->id;
		}

		protected function get_price($price) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
				return wc_price($price);
			} else {
				return woocommerce_price($price);
			}
		}

		protected function get_product($id) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
				return wc_get_product($id);
			} else {
				return get_product($id);
			}
		}
		
		protected function get_variation_id($_product) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
				return $_product->get_id();
			} else {
				return $_product->variation_id;
			}
		}

		protected function get_parent($_product) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
				return $this->get_product($_product->get_parent_id());
			} else {
				return $_product->parent;
			}
		}
		
	}

	new Woo_Bulk_Discount_Plugin_t4m();

}