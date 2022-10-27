<?php // Opening PHP tag - nothing should be before this, not even whitespace

/**********************************************************************

 **********************************************************************/

if (!class_exists('Link_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FastStock_Management_Table extends WP_List_Table
{

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'ptitle' => 'Title',
            'mrp' => 'MRP',
            'price' => 'Sale Price',
            'cost' => 'Cost Price',
            'quantity' => 'Quantity',
            'weight' => 'Weight (kg)',
        );
        return $columns;
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="product_ids[]" value="%s" />', $item['ID']
        );
    }

    public function column_ptitle($item)
    {
        global $post;

        $edit_link = get_edit_post_link($item['ID']);
        $view_link = get_post_permalink($item['ID']);
        $ptitle = '<a class="row-title" href="' . esc_url($edit_link) . '">' . esc_html($item['ptitle']) . '</a>';

        $actions = array(
            'id' => sprintf(__('ID: %d', 'faststock'), $item['ID']),
            'edit' => '<a href="' . esc_url($edit_link) . '">Edit</a>',
            'view' => '<a target="_blank" href="' . esc_url($view_link) . '">View</a>',
        );

        $row_actions = $this->row_actions($actions);

        return sprintf('%1$s %2$s', $ptitle, $row_actions);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'mrp':
            case 'price':
            case 'cost':
            case 'quantity':
                ?>
                    <input type="number" class="input-text"  style="width: 80px;" name="product_data[<?php echo $item['ID'] ?>][<?php echo $column_name ?>]" placeholder="<?php echo $item[$column_name] ?>"/>
                <?php
break;
            case 'weight':
                ?>
                    <input type="number" class="input-text"  style="width: 80px;" name="product_data[<?php echo $item['ID'] ?>][<?php echo $column_name ?>]" placeholder="<?php echo $item[$column_name] ?>" step=".001"/>
                <?php
break;

            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'quantity' => array('_stock', false),
        );
        return $sortable_columns;
    }

    public function faststock_query_to_product($query)
    {
        $rows = array();
        foreach ($query->posts as $post) {

            $product = wc_get_product($post->ID);

            if ($product) {
                $rows[] = array(
                    'ID' => $product->get_id(),
                    'ptitle' => $product->get_name(),
                    'mrp' => $product->get_regular_price(),
                    'price' => $product->get_price(),
                    'cost' => get_post_meta($post->ID, 'cost_price', true),
                    'quantity' => $product->get_stock_quantity(),
                    'weight' => $product->get_weight(),
                );
            }
        }

        return $rows;
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $search = !empty($_REQUEST['s']) ? trim(wp_unslash($_REQUEST['s'])) : '';

        $this->process_bulk_action();

        // Sorting
        $orderby = !empty($_REQUEST['orderby']) ? wc_clean($_REQUEST['orderby']) : '_stock';
        $order = !empty($_REQUEST['order']) ? strtoupper(wc_clean($_REQUEST['order'])) : 'ASC';

        //pagination settings
        $perPage = 20;
        $currentPage = $this->get_pagenum();

        //Get data
        $query = new WP_Query(array(
            'post_type' => array('product', 'product_variation'),
            'posts_per_page' => $perPage,
            'offset' => ($currentPage - 1) * $perPage,
            's' => $search,
            'orderby' => 'meta_value_num',
            'meta_key' => $orderby,
            'order' => $order,
        ));

        //maps the query to products
        $data = $this->faststock_query_to_product($query);

        //pagination
        $this->set_pagination_args(array(
            'total_items' => $query->found_posts,
            'per_page' => $perPage,
            'total_pages' => ceil($query->found_posts / $perPage),
        ));

        //show rows
        $this->_column_headers = array($columns, $hidden, $sortable); // needed as per wpdocs
        $this->items = $data;

    }

    public function get_bulk_actions()
    {
        return array(
            'save' => __('Save', 'faststock'),
        );
    }

    public function process_bulk_action()
    {
        //Called when a bulk action is being triggered...

        if ('save' === $this->current_action()) {

            $product_ids = $_POST['product_ids'];
            $product_data = $_POST['product_data'];

            if (!empty($product_ids) && !empty($product_data)) {

                $html = '';

                foreach ($product_ids as $id) {

                    $product = wc_get_product($id);
                    $updated = false;

                    if ($product_data[$id]['mrp']) {
                        $product->set_regular_price($product_data[$id]['mrp']);
                        $updated = true;
                    }

                    if ($product_data[$id]['price']) {
                        $product->set_price($product_data[$id]['price']);
                        $product->set_sale_price($product_data[$id]['price']);
                        $updated = true;
                    }

                    if ($product_data[$id]['quantity']) {
                        $product->set_stock_quantity($product_data[$id]['quantity']);
                        $updated = true;
                    }

                    if ($product_data[$id]['weight']) {
                        $product->set_weight($product_data[$id]['weight']);
                        $updated = true;
                    }

                    if ($product_data[$id]['cost']) {
                        update_post_meta($id, 'cost_price', $product_data[$id]['cost']);
                        $updated = true;
                    }

                    if ($updated) {
                        $product->save();
                        $html .= '<p>[' . $id . '] ' . $product->get_name() . '</p>';   
                    }

                } // for

                echo    '<div class="notice notice-success is-dismissible">
                        <p><strong>Updated Products</strong></p>
                        ' . $html . '
                        </div>';
            }
        } //if (save)
    } //function

} //class
