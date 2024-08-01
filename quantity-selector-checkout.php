<?php
/**
 * Plugin Name: Quantity Selector Checkout
 * Description: Adds a quantity selector to the checkout page and updates prices dynamically.
 * Version: 1.0
 * Author: Your Name
 */

// Register and enqueue the JavaScript file
function qsc_enqueue_scripts() {
    wp_enqueue_script(
        'qsc-js', // 1. Handle for the JavaScript file
        plugins_url('quantity-selector-checkout.js', __FILE__), // 2. URL to the script
        array('jquery'), // 3. Ensure jQuery is loaded first
        null, // 4. Version of the script (optional)
        true // 5. Load the script in the footer
    );

    // Pass AJAX URL to JavaScript
    wp_localize_script(
        'qsc-js', // Handle for the script
        'qscParams', // Name of the JavaScript object to contain data
        array(
            'ajax_url' => admin_url('admin-ajax.php'), // AJAX URL to be used in JavaScript
        )
    );
}
add_action('wp_enqueue_scripts', 'qsc_enqueue_scripts'); // Hook into WordPress to run the function

// Register the callback for cart updates
add_action('woocommerce_blocks_loaded', function() {
    // Register the callback function
    woocommerce_store_api_register_update_callback(
        [
            'namespace' => 'quantity-selector',
            'callback'  => function( $data ) {
                // Check if itemId and action are set
                if (isset($data['itemId'])) {
                    $item_id = intval($data['itemId']);
                    
                    if (isset($data['action']) && $data['action'] === 'delete') {
                        // Remove the cart item
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            if ($cart_item['product_id'] === $item_id) {
                                WC()->cart->remove_cart_item($cart_item_key);
                            }
                        }
                    } elseif (isset($data['quantity'])) {
                        $quantity = intval($data['quantity']);

                        // Update the cart item quantity
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            if ($cart_item['product_id'] === $item_id) {
                                WC()->cart->set_quantity($cart_item_key, $quantity);
                            }
                        }
                    }

                    // Recalculate cart totals
                    WC()->cart->calculate_totals();
                }
            },
        ]
    );
});

// Function to create the delete icon HTML
function qsc_get_delete_icon_html($cart_item_key) {
    $product_id = WC()->cart->get_cart_item($cart_item_key)['product_id'];
    return sprintf(
        '<span class="delete-icon" title="%s" data-item-id="%s">&times;</span>',
        __('Remove this item', 'woocommerce'),
        esc_attr($product_id)
    );
}

// Add delete icon HTML to cart item name
add_filter('woocommerce_cart_item_name', function($item_name, $cart_item, $cart_item_key) {
    return $item_name . qsc_get_delete_icon_html($cart_item_key);
}, 10, 3);
