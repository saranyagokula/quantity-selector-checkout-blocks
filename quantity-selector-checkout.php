<?php
/**
 * Plugin Name: Quantity Selector Checkout
 * Description: Adds a quantity selector to the checkout page and updates prices dynamically.
 * Version: 1.0
 * Author: Your Name
 */

// Register and enqueue the JavaScript file
function qsc_enqueue_scripts() {

    // Enqueue the JavaScript file
    wp_enqueue_script(
        'qsc-js', // Handle for the JavaScript file
        plugins_url('quantity-selector-checkout.js', __FILE__), // URL to the script
        array('jquery'), // Ensure jQuery is loaded first
        null, // Version of the script (optional)
        true // Load the script in the footer
    );

    // Pass AJAX URL to JavaScript
    wp_localize_script(
        'qsc-js', // Handle for the script
        'qscParams', // Name of the JavaScript object to contain data
        array(
            'ajax_url' => admin_url('admin-ajax.php'), // AJAX URL to be used in JavaScript
        )
    );

    // Enqueue the CSS file
    wp_enqueue_style(
        'qsc-css', // Handle for the CSS file
        plugins_url('delete-icon.css', __FILE__) // URL to the CSS file
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
                // Check if itemId and quantity are set
                if (isset($data['itemId']) && isset($data['quantity'])) {
                    $item_id = intval($data['itemId']);
                    $quantity = intval($data['quantity']);

                    // Update the cart item quantity
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        if ($cart_item['product_id'] === $item_id) {
                            WC()->cart->set_quantity($cart_item_key, $quantity);
                        }
                    }

                    // Recalculate cart totals
                    WC()->cart->calculate_totals();
                }
            },
        ]
    );
});
?>
