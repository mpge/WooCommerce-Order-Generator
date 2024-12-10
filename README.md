# WooCommerce Test Product Generator

A simple plugin to generate test products in WooCommerce with predefined attributes such as dimensions, weight, price, and stock quantity. This plugin is designed for developers and testers to quickly populate a WooCommerce store with sample data.

## Features

- Generates test products with random names, SKUs, prices, and stock quantities.
- Sets default product dimensions to 5x5x5 (Length x Width x Height).
- Sets the product weight to 5 lbs.
- Assigns a default image to all generated products.
- Supports product generation via both the WordPress Admin and direct function calls.
- Publishes generated products automatically to the WooCommerce store.

## Installation

1. Download the plugin files and upload them to the `wp-content/plugins/` directory of your WordPress installation.
2. Go to the **Plugins** page in your WordPress Admin.
3. Activate the "WooCommerce Test Product Generator" plugin.

## Usage

### From the WordPress Admin Area

1. After activating the plugin, go to **WooCommerce > Tools > Test Product Generator**.
2. Specify the number of products you want to generate in the provided form.
3. Click **Generate Products** to create the products.
4. A success message will confirm the creation of the test products, which will now appear in your WooCommerce product list.

### From Code

You can also generate products programmatically by calling the `wc_generate_test_products` function in your custom scripts or theme's `functions.php` file:

```php
wc_generate_test_products(10); // Generates 10 test products.
