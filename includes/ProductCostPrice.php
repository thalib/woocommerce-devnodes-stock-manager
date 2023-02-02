<?php // Opening PHP tag - nothing should be before this, not even whitespace

/**********************************************************************
 * Add to the Column
 * Product Cost aka (Buy Price) and Markup % support added
 **********************************************************************/
add_filter('manage_edit-product_columns', 'devnodes_product_column_new');
function devnodes_product_column_new($columns)
{

    /* NOTE: Below code is sufficient only to add custom column
    $columns['cost_price'] = 'Buy Price';
    return $columns;
     */

    // below code reorders the colum + add custom column
    return array(
        'cb' => '<input type="checkbox" />', // checkbox for bulk actions
        'name' => 'Name',
        'sku' => 'SKU',
        'product_cat' => 'Categories',
        'is_in_stock' => 'Stock',
        'price' => 'Price',
        'cost_price' => 'Cost', // Added
        'markup_percent' => 'Markup %', // Added
        'hide_product' => 'Hide', // Added
    );
}

add_action('manage_product_posts_custom_column', 'devnodes_product_column_data');
function devnodes_product_column_data($column)
{
    global $post;

    if ('cost_price' === $column) {

        $cost_price = get_post_meta($post->ID, 'cost_price', true);
        if ($cost_price) {
            echo esc_attr('₹' . $cost_price);
        }

    }

    if ('markup_percent' === $column) {

        $cost_price = get_post_meta($post->ID, 'cost_price', true);
        $product = wc_get_product($post);
        $sale_price = $product->get_price();
        if ($cost_price and $sale_price) {
            $percent = round((($sale_price - $cost_price) / $cost_price) * 100, 0);

            $html = '<span class="devnodes-green">';
            if ($percent < 30) {
                $html = '<span class="devnodes-red">';
            } else if ($percent < 75) {
                $html = '<span class="devnodes-blue">';
            }
            $html .= $percent . ' %</span>';
            echo $html;
        }

    }

    if ('hide_product' === $column) {
        $skip_product = get_post_meta($post->ID, 'skip_product', true);
        if ($skip_product) {
            echo esc_attr('Yes');
        }
    }
}

// make a column sortable
add_filter('manage_edit-product_sortable_columns', 'devnodes_product_column_sortable');
function devnodes_product_column_sortable($columns)
{
    $columns['hide_product'] = 'hide_product';
    return $columns;
}

/*****************************************************************************
 * Get the cost front user using product custom fields
 *****************************************************************************/
add_action('woocommerce_product_options_general_product_data', 'devnodes_product_custom_field');
function devnodes_product_custom_field()
{
    global $woocommerce, $post;
    // Textarea
    $cost_price = get_post_meta($post->ID, 'cost_price', true);
    woocommerce_wp_text_input(
        array(
            'id' => 'devnodes_cost_price',
            'label' => __('Cost Price (₹)', 'devnodes'),
            'value' => $cost_price,
            'placeholder' => '',
            'description' => __('(by Devnodes)', 'devnodes'),
        )
    );

    // Checkbox
    $skip_product = get_post_meta($post->ID, 'skip_product', true) ? 1 : 0;
    woocommerce_wp_checkbox(
        array(
            'id' => 'devnodes_skip_product',
            'wrapper_class' => 'skip_product',
            'label' => __('Hide Product', 'devnodes'),
            'value' => $skip_product,
            'cbvalue' => 1,
            'description' => __('(by Devnodes)', 'devnodes'),
        )
    );

}

add_action('woocommerce_process_product_meta', 'devnodes_product_custom_field_save');
function devnodes_product_custom_field_save($post_id)
{
    if (!empty($_POST)) {
        if (isset($_POST['devnodes_cost_price'])) {
            update_post_meta($post_id, 'cost_price', wc_clean($_POST['devnodes_cost_price']));
        }

        // Checkbox - either case update the value
        if (isset($_POST['devnodes_skip_product'])) {
            update_post_meta($post_id, 'skip_product', 1);
        } else {
            update_post_meta($post_id, 'skip_product', 0);
        }
    }
}

// We need some CSS to position the paragraph.
add_action('admin_head', 'devnodes_css');
function devnodes_css()
{
    echo "
	<style type='text/css'>
    .devnodes-red,
    .devnodes-green,
    .devnodes-blue {
        color: #ffffff;
        padding: 3px 8px 3px 8px;
        border-radius: 5px;
        font-weight: bold;
    }

    .devnodes-red {
        background-color: #ff5757;
    }

    .devnodes-green {
        background-color: #20bf6b;
    }

    .devnodes-blue {
        background-color: #5f27cd;
    }

	</style>
	";
}
