<?php
/**
 * Plugin Name:       Fast Stock Manager
 * Plugin URI:        https://github.com/thalib/woocommerce-faststock-manager
 * Description:       Open Source Stock Manager for Woocommerce
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mohamed Thalib H
 * Author URI:        https://github.com/thalib
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/thalib/woocommerce-faststock-manager
 * Text Domain:       faststock
 * Domain Path:       /languages
 */


##########  Add a page for Product > Stock Manager

add_action('admin_menu', 'faststock_manager_admin_menu');
function faststock_manager_admin_menu()
{
    add_submenu_page(
        'edit.php?post_type=product',
        __('Fast Stock Manager', 'faststock'),
        __('Fast Stock Manager', 'faststock'),
        'manage_options',
        'faststock', //slug
        'faststock_manager_admin_page' //callback
    );

	//Quick link import/export of orders
    add_submenu_page(
        'edit.php?post_type=product',
        __('Import Products', 'fastdep'),
        __('Import Products', 'fastdep'),
        'read',
        'edit.php?post_type=product&page=product_importer', //slug
        '', //hook
    );

    add_submenu_page(
        'edit.php?post_type=product',
        __('Export Products', 'fastdep'),
        __('Export Products', 'fastdep'),
        'read',
        'edit.php?post_type=product&page=product_exporter', //slug
        '', //hook
    );
}

function faststock_manager_admin_page()
{
	require_once(plugin_dir_path(__FILE__) . 'lib/StockManager.php');

    $StockListTable = new FastStock_Management_Table();

    ?>
    <div class="wrap">
    <h1>Fast Stock Manager</h1>

    <form method="post">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <?php

    $StockListTable->prepare_items();
    $StockListTable->search_box('Search', 'search_id');
    $StockListTable->display();

    echo '</form></div>';
}

