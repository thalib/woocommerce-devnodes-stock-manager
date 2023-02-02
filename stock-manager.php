<?php
/**
 * Plugin Name:       Devnodes Stock Manager for Woocommerce
 * Plugin URI:        https://github.com/thalib/woocommerce-devnodes-stock-manager
 * Description:       Open Source Stock Manager for Woocommerce
 * Version:           2.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mohamed Thalib H
 * Author URI:        https://devnodes.in
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/thalib/woocommerce-devnodes-stock-manager
 * Text Domain:       devnodes
 * Domain Path:       /languages
 */

require_once(plugin_dir_path(__FILE__) . 'includes/StockManager.php');
require_once(plugin_dir_path(__FILE__) . 'includes/ProductCostPrice.php');

##########  Add a page for Product > Stock Manager

add_action('admin_menu', 'devnodes_manager_admin_menu');
function devnodes_manager_admin_menu()
{
    add_submenu_page(
        'edit.php?post_type=product',
        __('Stock Manager', 'devnodes'),
        __('Stock Manager', 'devnodes'),
        'manage_options',
        'devnodes', //slug
        'devnodes_manager_admin_page' //callback
    );

	//Quick link import/export of orders
    add_submenu_page(
        'edit.php?post_type=product',
        __('Import Products', 'devnodes'),
        __('Import Products', 'devnodes'),
        'read',
        'edit.php?post_type=product&page=product_importer', //slug
        '', //hook
    );

    add_submenu_page(
        'edit.php?post_type=product',
        __('Export Products', 'devnodes'),
        __('Export Products', 'devnodes'),
        'read',
        'edit.php?post_type=product&page=product_exporter', //slug
        '', //hook
    );
}

function devnodes_manager_admin_page()
{
    $StockListTable = new Devnodes_Management_Table();

    ?>
    <div class="wrap">
    <h1>Stock Manager <small>by Devnodes.in</small></h1>

    <form method="post">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <?php

    $StockListTable->prepare_items();
    $StockListTable->search_box('Search', 'search_id');
    $StockListTable->display();

    echo '</form></div>';
}

