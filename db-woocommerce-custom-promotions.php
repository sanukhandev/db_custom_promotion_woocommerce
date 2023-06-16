<?php
/*
Plugin Name: DB Custom WooCommerce Offers
Description: Adds custom discount rules to WooCommerce.
Version: 1.0
Author: Sanu Khan
Author URI: mailto:sanulgbello@gmail.com
*/

add_action( 'woocommerce_cart_calculate_fees', 'woo_promotional_offers_discount' );

function woo_promotional_offers_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    // Arrays to hold the products for each category
    $bogo_products = array();
    $buy4_pay2_products = array();

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product_cats = wp_get_post_terms( $product_id, 'product_cat' );

        foreach ( $product_cats as $cat ) {
            // group products by category
            switch ( $cat->slug ) {
                case 'buy-one-get-one':
                    $bogo_products[] = $cart_item;
                    break;
                case 'buy-four-pay-for-two':
                    $buy4_pay2_products[] = $cart_item;
                    break;
                default:
                    if (strpos($cat->slug, 'pick-any-3-at-') === 0) {
                        $pick_any_3_at_fixed_price_products[] = $cart_item;
                    }   
                    break;
                
            }
        }
    }
    if ( !empty($pick_any_3_at_fixed_price_products) ) {
        $pick_any_3_at_fixed_price_discount = calculate_discount_pick_any_3_at_fixed_cost( $pick_any_3_at_fixed_price_products );
        if ( $pick_any_3_at_fixed_price_discount['value'] > 0 ) {
            $cart->add_fee( __( $pick_any_3_at_fixed_price_discount['name'], 'woocommerce' ), - $pick_any_3_at_fixed_price_discount['value'] );
        }
    }

    if ( !empty($bogo_products) ) {
        $bogo_discount = calculate_discount_bogo( $bogo_products );
        if ( $bogo_discount['value'] > 0 ) {
            $cart->add_fee( __( $bogo_discount['name'], 'woocommerce' ), - $bogo_discount['value'] );
        }
    }
    if ( !empty($buy4_pay2_products) ) {
        $buy4_pay2_discount = calculate_discount_buy4_pay2( $buy4_pay2_products );
        if ( $buy4_pay2_discount['value'] > 0 ) {
            $cart->add_fee( __( $buy4_pay2_discount['name'], 'woocommerce' ), - $buy4_pay2_discount['value'] );
        }
    }
}
function calculate_discount_pick_any_3_at_fixed_cost( $cart_items ) {
    $discount = 0;
    $fixed_price = 0;

    $product_id = $cart_items[0]['product_id'];
    $product_cats = wp_get_post_terms( $product_id, 'product_cat' );

    foreach ( $product_cats as $cat ) {
        $category_parts = explode( '-', $cat->slug );

        if ( count( $category_parts ) > 1 && $category_parts[ count( $category_parts ) - 2 ] === 'at' ) {
            $fixed_price = (float) $category_parts[ count( $category_parts ) - 1 ];
            break; // Stop the loop after finding the first matching category
        }
    }

    // Calculate the total price and quantity of items in the cart
    $total_price = 0;
    $total_quantity = 0;
    foreach ( $cart_items as $cart_item ) {
        $total_price += $cart_item['data']->get_price() * $cart_item['quantity'];
        $total_quantity += $cart_item['quantity'];
    }

    // Calculate the number of groups of 3 items
    $group_count = floor( $total_quantity / 3 );

    // Calculate the discount based on the fixed price and the number of groups
    $discount = $group_count * $fixed_price;

    // Adjust the discount if there are any remaining items
    $remaining_quantity = $total_quantity % 3;
    $remaining_discount = $remaining_quantity * $cart_items[0]['data']->get_price();
    $discount += $remaining_discount;

    $discount = $total_price - $discount;

    return array(
        'name' => 'Pick Any 3 At Fixed Cost Discount',
        'value' => $discount,
    );
}





function calculate_discount_bogo( $cart_items ) {
    $prices = array();
    // Get all prices
    foreach ( $cart_items as $cart_item ) {
        for ( $i = 0; $i < $cart_item['quantity']; $i++ ) {
            $prices[] = $cart_item['data']->get_price();
        }
    }

    // Sort the prices in descending order
    sort($prices, SORT_NUMERIC);

    $discount = 0;
    // Take the highest price for every pair of items
    for ( $i = 0; $i < count($prices); $i += 2 ) {
        if (isset($prices[$i])) {
            $discount += $prices[$i];
        }
    }

    return array(
        'name' => 'Buy One Get One Discount',
        'value' => $discount,
    );
}


function calculate_discount_buy4_pay2( $cart_items ) {
    $prices = array();
    foreach ( $cart_items as $cart_item ) {
        for ( $i = 0; $i < $cart_item['quantity']; $i++ ) {
            $prices[] = $cart_item['data']->get_price();
        }
    }

    // If less than 4 items, no discount
    if ( count($prices) < 4 ) {
        return 0;
    }

    // Sort the prices in descending order
    sort($prices, SORT_NUMERIC);

    // Remove the prices of the lowest 2 items out of every 4 items
    for ( $i = 3; $i < count($prices); $i += 4 ) {
        unset($prices[$i]);
        unset($prices[$i-1]);
    }

    // The discount is the sum of the remaining prices (the highest 2 out of every 4)
    return array(
        'name' => 'Buy Four Pay For Two Discount',
        'value' => array_sum($prices),
    );
}
?>
