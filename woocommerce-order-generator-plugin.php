<?php
/**
 * Plugin Name: WooCommerce Test Orders Generator
 * Description: Generate test orders and products in WooCommerce with random data.
 * Version: 1.4.0
 * Author: Matthew Gros
 * Website: http://matthewpg.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Add a submenu under WooCommerce menu
add_action( 'admin_menu', 'wc_test_orders_menu' );
function wc_test_orders_menu() {
    add_submenu_page(
        'woocommerce',
        'Test Orders & Products Generator',
        'Test Orders Generator',
        'manage_woocommerce',
        'wc-test-orders-generator',
        'wc_test_orders_page'
    );
}

function wc_test_orders_page() {
    if ( isset( $_POST['generate_test_orders'] ) ) {
        $order_count = isset( $_POST['order_count'] ) ? absint( $_POST['order_count'] ) : 1;
        $products_per_order = isset( $_POST['products_per_order'] ) ? absint( $_POST['products_per_order'] ) : 1;
        $include_canada = isset( $_POST['include_canada'] );
        $include_usa = isset( $_POST['include_usa'] );
        wc_generate_test_orders( $order_count, $products_per_order, $include_canada, $include_usa );
        echo '<div class="updated"><p>Generated ' . $order_count . ' test orders with ' . $products_per_order . ' products per order.</p></div>';
    }

    if ( isset( $_POST['generate_test_products'] ) ) {
        $product_count = isset( $_POST['product_count'] ) ? absint( $_POST['product_count'] ) : 1;
        wc_generate_test_products( $product_count );
        echo '<div class="updated"><p>Generated ' . $product_count . ' test products.</p></div>';
    }

    echo '<div class="wrap">
        <h1>Generate Test Data</h1>
        <form method="post">
            <h2>Generate Test Orders</h2>
            <label for="order_count">Number of Orders:</label>
            <input type="number" name="order_count" id="order_count" value="1" min="1" required />
            <br><br>
            <label for="products_per_order">Products per Order:</label>
            <input type="number" name="products_per_order" id="products_per_order" value="1" min="1" required />
            <br><br>
            <label for="include_canada">Include Canadian Addresses:</label>
            <input type="checkbox" name="include_canada" id="include_canada" checked />
            <br><br>
            <label for="include_usa">Include US Addresses:</label>
            <input type="checkbox" name="include_usa" id="include_usa" checked />
            <br><br>
            <button type="submit" name="generate_test_orders" class="button button-primary">Generate Orders</button>
        </form>

        <form method="post" style="margin-top: 20px;">
            <h2>Generate Test Products</h2>
            <label for="product_count">Number of Products:</label>
            <input type="number" name="product_count" id="product_count" value="1" min="1" required />
            <br><br>
            <button type="submit" name="generate_test_products" class="button button-primary">Generate Products</button>
        </form>
    </div>';
}

function wc_generate_test_orders( $order_count, $products_per_order, $include_canada, $include_usa ) {
    $addresses = wc_get_sample_addresses( $include_canada, $include_usa );

    for ( $i = 0; $i < $order_count; $i++ ) {
        $order = wc_create_order();

        // Generate random customer data
        $random_address = $addresses[ array_rand( $addresses ) ];

        $order->set_address( $random_address, 'billing' );
        $order->set_address( $random_address, 'shipping' );

        // Add random products to the order
        $products = wc_get_products( [ 'limit' => $products_per_order, 'orderby' => 'rand' ] );

        foreach ( $products as $product ) {
            $quantity = rand( 1, 3 );
            $order->add_product( $product, $quantity );
        }

        // Set the payment method and mark the order as completed
        $order->set_payment_method( 'bacs' ); // Bank transfer as an example payment method
        $order->calculate_totals();
        $order->update_status( 'processing' );
        $order->payment_complete();
    }
}

function wc_generate_test_products( $product_count ) {
    // Generate a random hex color for the background
    $randomColor = sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255));
    
    // Use the random color in the placeholder URL with a fixed white text color
    $image_url = "https://placehold.co/400x400/{$randomColor}/FFF.png";

    for ( $i = 0; $i < $product_count; $i++ ) {
        $product = new WC_Product_Simple();

        $product_name = 'Test Product ' . wp_generate_password( 6, false );
        $price = rand( 10, 100 );

        $product->set_name( $product_name );
        $product->set_regular_price( $price );
        $product->set_sku( 'TEST-' . wp_generate_password( 8, false ) );
        $product->set_stock_quantity( rand( 1, 50 ) );
        $product->set_manage_stock( true );
        $product->set_status( 'publish' );

        // Set dimensions and weight
        $product->set_length( '5' ); // Length in inches or cm (WooCommerce settings determine the unit)
        $product->set_width( '5' );  // Width
        $product->set_height( '5' ); // Height
        $product->set_weight( '5' ); // Weight in lbs or kg (WooCommerce settings determine the unit)

        // Download and attach image to product
        $image_id = wc_generate_image_attachment( $image_url );
        if ( $image_id ) {
            $product->set_image_id( $image_id );
        }

        $product->save();
    }
}

function wc_generate_image_attachment( $image_url ) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents( $image_url );
    $filename = basename( $image_url );

    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents( $file, $image_data );

    $wp_filetype = wp_check_filetype( $filename, null );
    $attachment = [
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment( $attachment, $file );
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return $attach_id;
}

function wc_get_sample_addresses( $include_canada, $include_usa ) {
    $canada_addresses = include __DIR__ . '/addresses-canada.php';
    $usa_addresses = include __DIR__ . '/addresses-usa.php';

    $addresses = [];

    if ( $include_canada ) {
        $addresses = array_merge( $addresses, $canada_addresses );
    }

    if ( $include_usa ) {
        $addresses = array_merge( $addresses, $usa_addresses );
    }

    // Shuffle the addresses for random order
    shuffle( $addresses );

    return $addresses;
}

?>
