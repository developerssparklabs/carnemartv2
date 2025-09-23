<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (!$ajax) { ?>

    <?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
    <?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Products', 'eib2bpro'), 'description' => '', 'buttons' => '<a href="' . admin_url('post-new.php?post_type=product') . '" class="btn btn-sm btn-danger eib2bpro-panel">  &nbsp;+ &nbsp; ' . esc_html__('New product', 'eib2bpro') . ' &nbsp;</a>')); ?>
    <?php echo eib2bpro_view('products', 1, 'nav') ?>


    <div id="eib2bpro-products-1" class="eib2bpro-products ">
        <div class="eib2bpro-Searching<?php if ('' === eib2bpro_get('s', '')) echo " closed"; ?>">
            <div class="eib2bpro-Searching_In">
                <div class="input-group">
                    <input type="text" class="form-control eib2bpro-Search_Input" aria-label="<?php esc_html_e('Search in products...', 'eib2bpro'); ?>" placeholder="<?php esc_html_e('Search in products...', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>">
                    <div class="input-group-append">
                        <input type="hidden" name='eib2bpro-Input_Status' class='eib2bpro-Input_Status' value='<?php echo esc_attr(eib2bpro_get('category')); ?>' />
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php if ('' !== eib2bpro_get('category', '')) {
                                                                                                                                                                        echo esc_html(eib2bpro_get('category', ''));
                                                                                                                                                                    } else {
                                                                                                                                                                        echo esc_html__('All Categories', 'eib2bpro');
                                                                                                                                                                    } ?></button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item eib2bpro-Products_Cat_Dropdown" href="javascript:;" data-slug=''><?php esc_html_e('All Categories', 'eib2bpro'); ?></a>

                            <?php foreach ($categories[0] as $category) { ?>
                                <a class="dropdown-item eib2bpro-Products_Cat_Dropdown" href="javascript:;" data-slug='<?php echo esc_attr($category['slug']) ?>'><?php echo esc_html($category['name']) ?></a>
                            <?php } ?>

                        </div>
                    </div>
                </div>

                <input type="text" class="form-control eib2bpro-Search_Input eib2bpro-Display_None" placeholder="<?php esc_html_e('Search in products...', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>" autofocus></span>
            </div>
        </div>
        <div class="eib2bpro-Container eib2bpro-GP">
        <?php } ?>
        <div class="row">

            <div class="col-12 col-lg-<?php if (!$ajax && '' === eib2bpro_get('s', '')) {
                                            echo '9';
                                        } else {
                                            echo '12';
                                        } ?> eib2bpro-Right">
                <div class="eib2bpro-GP eib2bpro-List_M1 eib2bpro-Container">

                    <div class="eib2bpro-List_M1_Bulk eib2bpro-Bulk eib2bpro-Display_None">
                        <?php if ('trash' === eib2bpro_get('status')) { ?>
                            <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Restore" data-do="restore" data-status='restore' href="javascript:;"><?php esc_html_e('Restore products', 'eib2bpro'); ?></a>
                            <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Restore" data-do="deleteforever" data-status='deleteforever' href="javascript:;"><?php esc_html_e('Delete forever', 'eib2bpro'); ?></a>
                        <?php } else { ?>
                            <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="outofstock" href="javascript:;"><?php esc_html_e('Set to &mdash; Out of stock', 'eib2bpro'); ?></a>
                            <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="instock" href="javascript:;"><?php esc_html_e('Set to &mdash; In stock', 'eib2bpro'); ?></a>
                            <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="trash" data-status='trash' href="javascript:;"><?php esc_html_e('Delete', 'eib2bpro'); ?></a>

                        <?php } ?>
                        <a class="eib2bpro-Select_All float-right" data-state='select' href="javascript:;"><?php esc_html_e('Select All', 'eib2bpro'); ?></a>
                    </div>

                    <?php if (0 === count($products)) { ?>
                        <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                            <div>
                                <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                            </div>
                        </div>
                </div>
            <?php } else { ?>

                <?php if ($slug = sanitize_text_field(eib2bpro_get('category'))) { ?>
                    <?php $category = get_term_by('slug', $slug, 'product_cat');
                            if ($category) {
                                echo "<h4 class='eib2bpro-Cat_Title'>" . esc_html($category->name) . "</h4>";
                            } elseif ('-1' === $slug) {
                                echo "<h4 class='eib2bpro-Cat_Title'>" . esc_html__('Critical Stock', 'eib2bpro') . "</h4>";
                            } elseif ('-2' === $slug) {
                                echo "<h4 class='eib2bpro-Cat_Title'>" . esc_html__('Out of stock', 'eib2bpro') . "</h4>";
                            } elseif ('-3' === $slug) {
                                echo "<h4 class='eib2bpro-Cat_Title'>" . esc_html__('Trash', 'eib2bpro') . "</h4>";
                            } elseif ('-4' === $slug) {
                                echo "<h4 class='eib2bpro-Cat_Title'>" . esc_html__('On sale', 'eib2bpro') . "</h4>";
                            } ?>
                <?php } elseif ('trash' === eib2bpro_get('status')) { ?>
                    <h4 class='eib2bpro-Cat_Title'><?php esc_html_e('Trashed Products', 'eib2bpro'); ?></h4>
                <?php } elseif ('private' === eib2bpro_get('status')) { ?>
                    <h4 class='eib2bpro-Cat_Title'><?php esc_html_e('Private Products', 'eib2bpro'); ?></h4>
                <?php } elseif ('draft' === eib2bpro_get('status')) { ?>
                    <h4 class='eib2bpro-Cat_Title'><?php esc_html_e('Draft Products', 'eib2bpro'); ?></h4>
                <?php } elseif ('pending' === eib2bpro_get('status')) { ?>
                    <h4 class='eib2bpro-Cat_Title'><?php esc_html_e('Pending Review', 'eib2bpro'); ?></h4>
                <?php } else { ?>
                    <h4 class='eib2bpro-Cat_Title'><?php esc_html_e('All Products', 'eib2bpro'); ?></h4>
                <?php } ?>
                <hr />

                <div class="eib2bpro-Products_Sortable">
                    <?php if ($products) {
                            foreach ($products

                                as $product) { ?>
                            <?php if ('variant' !== $product['type']) { ?>
                                <div class="btnA eib2bpro-Item collapsed" data-id="<?php echo esc_attr($product['id']) ?>" id="item_<?php echo esc_attr($product['id']) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($product['id']) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($product['id']) ?>">
                                    <div class="container-fluid">
                                        <div class="liste row d-flex align-items-center">

                                            <?php if ('variant' !== $product['type']) { ?>

                                                <div class="eib2bpro-Checkbox_Hidden">
                                                    <input type="checkbox" class="eib2bpro-Checkbox eib2bpro-StopPropagation" data-id='<?php echo esc_attr($product['id']) ?>'>
                                                </div>

                                                <div class="eib2bpro-Col_Image col-3 col-sm align-middle">
                                                    <img src="<?php echo get_the_post_thumbnail_url($product['id'], array(150, 150)); ?>" class="eib2bpro-Product_Image">

                                                </div>
                                                <div class="eib2bpro-Col_Title col-6 col-sm-3 align-middle">

                                                    <span class="eix-quick" data-href="<?php echo esc_url(admin_url('post.php?post=' . $product['id'] . '&action=edit&eib2bpro_hide')); ?>"><?php echo esc_html($product['title']) ?></span> <?php if ('publish' !== $product['status'] && !eib2bpro_get('status')) { ?> &nbsp;
                                                        <span class="badge badge-secondary text-uppercase"><?php echo esc_html($product['status']) ?></span><?php } ?>
                                                    <button class="eib2bpro-Mobile_Actions eib2bpro-M21 eib2bpro-Display_None"><span class="dashicons dashicons-arrow-down-alt2"></span></button>

                                                </div>
                                                <div class="align-middle col-2">
                                                    <div class="eib2bpro-Price1" id="eib2bpro-Price_<?php echo esc_attr($product['id']) ?>">
                                                        <?php echo str_replace("&ndash;", "", $product['price_html']); ?>
                                                    </div>

                                                </div>
                                                <div class="eib2bpro-Col_3 col-2 align-middle text-center" data-colname="Stock">
                                                    <div class="eib2bpro-Stocks1" id="eib2bpro-Stock_<?php echo esc_attr($product['id']) ?>">
                                                        <?php if (true === $product['managing_stock']) { ?>
                                                            <?php if (0 < intval($product['stock_quantity'])) {
                                                                echo esc_html(intval($product['stock_quantity']));
                                                            } else {
                                                                echo '<span class="badge badge-danger">' . esc_html__('Out of stock', 'eib2bpro') . '</span>';
                                                            } ?>
                                                        <?php } else { ?>
                                                            <?php if (true === $product['in_stock']) {
                                                                echo '<span class="text-mute">∞</span>';
                                                            } else {
                                                                echo '<span class="badge badge-danger">' . esc_html__('Out of stock', 'eib2bpro') . '</span>';
                                                            } ?>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="eib2bpro-Col_3 col-1 align-middle" data-colname="Visible">
                                                    <?php if ('trash' !== $product['status']) { ?>
                                                        <label class="switch eib2bpro-StopPropagation">
                                                            <input type="checkbox" value="1" data-id="<?php echo esc_attr($product['id']) ?>" class="success eib2bpro-OnOff" <?php if ('visible' === $product['catalog_visibility']) echo ' checked'; ?> />
                                                            <span class="eib2bpro-slider round"></span>
                                                        </label>
                                                    <?php } ?>
                                                </div>
                                                <div class="eib2bpro-Col_Categories eib2bpro-Col_3 col-2 align-middle" data-colname="Categories">
                                                    <?php
                                                    foreach ($product['categories'] as $category) { ?>
                                                        <a href="<?php echo eib2bpro_admin('products', array('category' => $category->slug)); ?>"><?php echo esc_html($category->name) ?></a>
                                                        <br />
                                                    <?php }
                                                    ?>
                                                    <?php if ('menu_order' === eib2bpro_get('orderby')) { ?>
                                                        <a class="eib2bpro-Products_Hand" data-id="<?php echo esc_attr($product['id']) ?>" href="javascript:;"><span>≡</span></a>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_<?php echo esc_attr($product['id']) ?>">
                                        <div class="eib2bpro-Item_Details ">
                                            <div class="containerx">
                                                <div class="row">
                                                    <?php if ('trash' !== $product['status']) { ?>
                                                        <?php if ('variable' === $product['type']) { ?>
                                                            <div class="col col-sm-9 d-none d-sm-inline eib2bpro-StopPropagation">
                                                                <p>&nbsp;</p>
                                                                <span class="dashicons dashicons-info"></span>
                                                                &nbsp; <?php esc_html_e('This is a variable product, so you can not edit it directly', 'eib2bpro'); ?>
                                                            </div>
                                                        <?php } else { ?>
                                                            <div class="col col-sm-3 d-none d-md-inline eib2bpro-StopPropagation">
                                                                <h4><?php esc_html_e('Price', 'eib2bpro'); ?></h4>
                                                                <span class="input-price-container"><?php echo esc_html(get_woocommerce_currency_symbol()) ?></span>
                                                                <span class="input-price-container eib2bpro-Products_Input_Price"><?php echo esc_html(get_woocommerce_currency_symbol()) ?></span>
                                                                <input type="text" name="regular_price" class="eib2bpro-PriceAjax eib2bpro-PriceAjax_Regular form-control" data-id="<?php echo esc_attr($product['id']) ?>" placeholder="<?php esc_html_e('Regular Price', 'eib2bpro'); ?>" value="<?php echo esc_attr($product['regular_price']) ?>" />
                                                                <input type="text" name="sale_price" class="eib2bpro-PriceAjax eib2bpro-PriceAjax_Sale form-control" data-id="<?php echo esc_attr($product['id']); ?>" placeholder="<?php esc_html_e('Sale Price', 'eib2bpro'); ?>" value="<?php echo esc_attr($product['sale_price']) ?>" />
                                                                <button data-id="<?php echo esc_attr($product['id']) ?>" class="eib2bpro-PriceAjax1 button button-sm btn-black"><?php esc_html_e('Save', 'eib2bpro'); ?></button>
                                                            </div>
                                                            <div class="col-1">
                                                            </div>
                                                            <div class="col col-sm-3  d-none d-md-inline  eib2bpro-StopPropagation">
                                                                <h4><?php esc_html_e('Stock', 'eib2bpro'); ?></h4>
                                                                <input type="text" name="qnty" data-id="<?php echo esc_attr($product['id']) ?>" value="<?php echo esc_attr($product['stock_quantity']) ?>" class="eib2bpro-StockAjax2 form-control">
                                                                <button data-id="<?php echo esc_attr($product['id']); ?>" class="eib2bpro-StockAjax1 button button-sm btn-black"><?php esc_html_e('Save', 'eib2bpro'); ?></button>
                                                                <br />
                                                                <input type="checkbox" name="unlimited" data-id="<?php echo esc_attr($product['id']) ?>" class="eib2bpro-StockAjax" <?php if ((true !== $product['managing_stock'] && true === $product['in_stock']) or (true === $product['managing_stock'] && 9999 === $product['stock_quantity'])) echo ' checked'; ?>> <?php esc_html_e('Unlimited', 'eib2bpro'); ?>
                                                                <br>
                                                                <input type="checkbox" name="outofstock" data-id="<?php echo esc_attr($product['id']) ?>" class="eib2bpro-StockAjax" <?php if (true !== $product['in_stock']) echo ' checked'; ?>> <?php esc_html_e('Out of stock', 'eib2bpro'); ?>
                                                                <br>
                                                            </div>
                                                            <div class="col-2">
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if ('trash' === $product['status']) { ?>
                                                        <div class="col-12 col-sm-12 eib2bpro-Product_Actions text-right">
                                                            <a href="<?php echo wp_nonce_url("post.php?action=untrash&amp;post=" . $product['id'], "untrash-post_" . $product['id']); ?>" class="eib2bpro-StopPropagation"><?php esc_html_e('Restore product', 'eib2bpro'); ?></a>
                                                            &nbsp; &nbsp;
                                                            <a href="<?php echo get_delete_post_link($product['id'], false, true); ?>" class="eib2bpro-StopPropagation eib2bpro-CommentStatusButton_Red"><?php esc_html_e('Delete forever', 'eib2bpro'); ?></a>
                                                            &nbsp; &nbsp;
                                                        <?php } else { ?>
                                                            <div class="col-12 col-sm-3 eib2bpro-Product_Actions">
                                                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $product['id'] . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-StopPropagation eib2bpro-panel" data-hash="<?php echo esc_attr($product['id']) ?>"><?php esc_html_e('Edit product', 'eib2bpro'); ?></a>
                                                                <br /><br />
                                                                <a href="<?php echo esc_url(get_post_permalink($product['id'])) ?>" class="eib2bpro-StopPropagation" target="_new"><?php esc_html_e('View product page', 'eib2bpro'); ?></a>
                                                                <br /><br />
                                                                <a href="<?php echo wp_nonce_url(admin_url('edit.php?post_type=product&action=duplicate_product&amp;post=' . $product['id']), 'woocommerce-duplicate-product_' . $product['id']) ?>" class="eib2bpro-StopPropagation eib2bpro-panel"><?php esc_html_e('Duplicate', 'woocommerce') ?></a>
                                                                <br /><br />
                                                                <?php echo apply_filters('b2bpro_product_actions', '', $product['id']) ?>
                                                                <a href="<?php echo admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fproducts&filter=single_product&products=' . esc_attr($product['id']) . '&period=year&compare=previous_year&interval=month'); ?>" class="eib2bpro-StopPropagation eib2bpro-panel" data-width="1281px"><?php esc_html_e('Reports', 'eib2bpro'); ?></a>
                                                                <br /><br />
                                                                <a href="<?php echo get_delete_post_link($product['id'], false, false); ?>" class="eib2bpro-StopPropagation eib2bpro-CommentStatusButton_Red"><?php esc_html_e('Delete', 'eib2bpro'); ?></a>
                                                            <?php } ?>
                                                            </div>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                        <?php }
                        } ?>
                                </div>
                </div>
            <?php } ?>

            <?php if (isset($pagination->found_posts)) { ?>
                <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $pagination->found_posts, 'per_page' => absint(eib2bpro_option('products-per-page', 10)), 'page' => intval(eib2bpro_get('pg', 0)), 'url' => remove_query_arg('pg', eib2bpro_admin('products', array('status' => eib2bpro_get('status'), 'category' => isset($filter['category']) ? $filter['category'] : '', 'parents' => eib2bpro_get('parents'), 's' => eib2bpro_get('s', ''), 'orderby' => eib2bpro_get('orderby', ''), 'order' => eib2bpro_get('order', '')))))); ?>
            <?php } ?>

            </div>

            <div class="col-lg-3 eib2bpro-Channels <?php if ('' !== eib2bpro_get('s', '')) echo " hidden"; ?>">
                <ul class="eib2bpro-General">
                    <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('category' => '0')); ?>"><?php esc_html_e('All', 'eib2bpro'); ?></a></span>
                    </li>
                    <?php if (0 < $critical_stock) { ?>
                        <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('category' => '-1')); ?>"><?php esc_html_e('Critical Stocks', 'eib2bpro'); ?> <span class="badge badge-pill badge-danger"><?php echo esc_html($critical_stock) ?></span></a></span>
                        </li><?php } ?>
                    <?php if (0 < $outof_stock) { ?>
                        <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('category' => '-2')); ?>"><?php esc_html_e('Out of stock', 'eib2bpro'); ?> <span class="badge badge-pill badge-danger"><?php echo esc_html($outof_stock) ?></span></a></span>
                        </li><?php } ?>
                    <?php if (0 < $on_sales) { ?>
                        <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('category' => '-4')); ?>"><?php esc_html_e('On Sale', 'eib2bpro'); ?> <span class="badge badge-pill badge-danger"><?php echo esc_html($on_sales) ?></span></a></span>
                        </li><?php } ?>
                    <?php if (0 < wp_count_posts('product')->private) { ?>
                        <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('status' => 'private')); ?>"><?php esc_html_e('Private', 'eib2bpro'); ?> <span class="badge badge-pill badge-danger"><?php echo esc_html(wp_count_posts('product')->private) ?></a></span>
                        </li></span><?php } ?>
                    <?php if (0 < wp_count_posts('product')->draft) { ?>
                        <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('status' => 'draft')); ?>"><?php esc_html_e('Draft', 'eib2bpro'); ?> <span class="badge badge-pill badge-danger"><?php echo esc_html(wp_count_posts('product')->draft) ?></span></a></span>
                        </li><?php } ?>
                    <?php if (0 < wp_count_posts('product')->pending) { ?>
                        <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('status' => 'pending')); ?>"><?php esc_html_e('Pending', 'eib2bpro'); ?> <span class="badge badge-pill badge-danger"><?php echo esc_html(wp_count_posts('product')->pending) ?></span></a></span>
                        </li><?php } ?>
                    <li><span class="eib2bpro-Info"><a href="<?php echo eib2bpro_admin('products', array('status' => 'trash')); ?>"><?php esc_html_e('Trash', 'eib2bpro'); ?></a></span>
                    </li>
                </ul>

                <?php
                function categories($categories, $all, $d = 0, $parent = 0, $parents = array(), $show_me = false)
                {
                ?>
                    <ul class="eib2bpro-Depth_<?php echo esc_attr($d); ?>">
                        <?php
                        $in_parents = explode("-", eib2bpro_get('parents', "0-0"));
                        if ($categories) {
                            foreach ($categories as $category) {  ?>
                                <li class="eib2bpro-Parent_<?php echo esc_attr($parent) ?>  eib2bpro-Category_<?php echo esc_attr($category['id']) ?> <?php if (0 < $d) { ?>  collapse <?php if ($show_me or in_array($category['id'], $in_parents) or $category['slug'] === eib2bpro_get('category')) {
                                                                                                                                                                                            $show_me = TRUE; ?> show <?php } ?>" aria-labelledby="heading2" data-parent=".eib2bpro-Category_<?php echo esc_attr($parent) ?>" <?php } else { ?>"<?php } ?>>
                                    <span class="eib2bpro-Info">
                                        <span class="badge badge-pill badge-secondary"><?php echo esc_html($category['count']) ?></span> &nbsp;
                                        <a href="<?php echo eib2bpro_admin('products', array('category' => $category['slug'], 'parents' => implode('-', $parents))); ?>"><?php echo esc_html($category['name']) ?></a>
                                        <?php
                                        array_push($parents, $category['id']);
                                        if (isset($all[$category['id']])) {
                                            echo ' <button class="btn btn-link" type="button" data-toggle="collapse"
                    data-target=".eib2bpro-Parent_' . $category['id'] . '" aria-expanded="true" aria-controls="collapse">
                    +
                    </button>    </span>';
                                            categories($all[$category['id']], $all, ++$d, $category['id'], $parents, $show_me);
                                            --$d;
                                            $show_me = FALSE;
                                        } else {
                                            echo "</span>";
                                        } ?>
                                </li>
                        <?php
                            }
                        }
                        ?>
                    </ul>
                <?php } ?>
                <?php categories($categories[0], $categories); ?>
            </div>
        </div>
        </div>
    </div>
    <?php if (!$ajax) { ?>
        </div>
    <?php } ?>