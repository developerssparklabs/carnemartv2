<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (!$ajax) { ?>

    <?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
    <?php $buttons = '<a href="' . admin_url('post-new.php?post_type=shop_order&eib2bpro_hide') . '" class="btn btn-sm btn-danger eib2bpro-panel"> + &nbsp; ' . esc_attr__('New order', 'eib2bpro') . ' &nbsp;</a>'; ?>
    <?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Orders', 'eib2bpro'), 'description' => '', 'buttons' => $buttons)); ?>
    <?php echo eib2bpro_view('orders', 1, 'nav', array('list' => $list)) ?>

    <div id="eib2bpro-orders-1">

        <div class="eib2bpro-Searching<?php if ('' === eib2bpro_get('s', '')) echo " closed"; ?>">
            <div class="eib2bpro-Searching_In">
                <input type="text" class="form-control eib2bpro-Search_Input" placeholder="<?php esc_html_e('Search in orders..', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>"></span>
            </div>
        </div>

        <?php do_action('eib2bpro_need'); ?>

        <div class="eib2bpro-GP eib2bpro-List_M1 eib2bpro-Container">
        <?php }
    if (isset($filter['mode']) && 98 === intval($filter['mode'])) {
        echo '<h2 class="badge badge-black badge-pill eib2bpro-Badge_Big_Title">' . esc_html__('Orders', 'eib2bpro') . '</h2>
        <div id="eib2bpro-orders-1"><div class="eib2bpro-GP eib2bpro-List_M1 eib2bpro-Container">';
    } ?>

        <?php if (0 === count($orders)) { ?>
            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                <div>
                    <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                </div>
            </div>
        <?php } else { ?>

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="eib2bpro-List_M1_Bulk eib2bpro-Bulk eib2bpro-Display_None">
                    <?php if ('trash' === eib2bpro_get('status')) { ?>
                        <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Restore" data-do="changestatus" data-status='restore' href="javascript:;"><?php esc_html_e('Restore orders', 'eib2bpro'); ?></a>
                        <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Restore" data-do="changestatus" data-status='deleteforever' href="javascript:;"><?php esc_html_e('Delete forever', 'eib2bpro'); ?></a>
                        <?php } else {
                        foreach (wc_get_order_statuses() as $st_key => $st_val) { ?>
                            <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="changestatus" data-status='<?php echo str_replace('wc-', '', $st_key) ?>' href="javascript:;"><?php echo esc_html($st_val); ?></a>
                        <?php } ?>
                        <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="changestatus" data-status='trash' href="javascript:;"><?php esc_html_e('Move to trash', 'eib2bpro'); ?></a>
                    <?php } ?>
                    <a class="eib2bpro-Select_All float-right" data-state='select' href="javascript:;"><?php esc_html_e('Select All', 'eib2bpro'); ?></a>

                </div>
                <?php foreach ($orders as $order_group) { ?>
                    <h6><?php echo esc_html($order_group['title']) ?></h6>
                    <div class="eib2bpro-Orders_Container">
                        <?php foreach ($order_group['orders'] as $order) { ?>
                            <div class="btnA eib2bpro-Item collapsed" data-toggle="collapse" data-target="#order_<?php echo esc_attr($order['id']) ?>" aria-expanded="false" aria-controls="order_<?php echo esc_attr($order['id']) ?>" id="item_<?php echo esc_attr($order['id']) ?>">
                                <div class="container-fluid">
                                    <div class="liste row d-flex align-items-center">
                                        <div class="eib2bpro-Checkbox_Hidden">
                                            <input type="checkbox" class="eib2bpro-Checkbox eib2bpro-StopPropagation" data-id='<?php echo esc_attr($order['id']) ?>' data-state='<?php echo esc_attr($order['status']) ?>'>
                                        </div>
                                        <div class="text-center d-none d-sm-inline eib2bpro-Order_No" data-colname="<?php esc_html_e('Order No: ', 'eib2bpro'); ?>"><span class="eib2bpro-Order_No eib2bpro-Strong"><?php echo esc_attr($order['std']->get_order_number()) ?></span>
                                        </div>
                                        <div class="col col-sm-2 col-md-1 eib2bpro-orders--item-badge text-center eib2bpro-Col_3">

                                            <span class="siparisdurumu text-<?php echo esc_attr($order['status']); ?>"><span class="bg-custom bg-<?php echo esc_attr($order['status']); ?>" aria-hidden="true"></span><br><?php echo wc_get_order_status_name($order['status']); ?></span>

                                            <span class="badge badge-pill eib2bpro-Display_None"><?php echo esc_html(wc_get_order_status_name($order['status'])); ?></span>
                                        </div>
                                        <div class="eib2bpro-Col_Name col-7 col-sm-2">
                                            <p class="eib2bpro-orders--name eix-quick" data-href="<?php echo esc_url(admin_url('post.php?post=' . esc_attr($order['id']) . '&action=edit&eib2bpro_hide')); ?>">
                                                <?php echo eib2bpro_clean($order['shipping']['first_name'], $order['billing']['first_name']) . " " . eib2bpro_clean($order['shipping']['last_name'], $order['billing']['last_name']); ?>
                                            </p>
                                            <p class="eib2bpro-orders--address eix-quick" data-href="<?php echo esc_url(admin_url('post.php?post=' . esc_attr($order['id']) . '&action=edit&eib2bpro_hide')); ?>">
                                                <?php if (isset($order['shipping']['country'])) {
                                                    if (isset(\WC()->countries->states[eib2bpro_clean($order['shipping']['country'], $order['billing']['country'])][eib2bpro_clean($order['shipping']['state'], $order['billing']['state'])])) {
                                                        echo \WC()->countries->states[eib2bpro_clean($order['shipping']['country'], $order['billing']['country'])][eib2bpro_clean($order['shipping']['state'], $order['billing']['state'])];
                                                    } else {
                                                        echo eib2bpro_clean($order['shipping']['state'], $order['billing']['state']);
                                                    }
                                                    if ('' !== eib2bpro_clean($order['shipping']['state'], $order['billing']['state'])) {
                                                        echo ', ';
                                                    }
                                                    echo esc_html(eib2bpro_clean($order['shipping']['city'], $order['billing']['city']));
                                                } ?>
                                            </p>
                                        </div>
                                        <div class="col col-sm-2 eib2bpro-Col_3" data-colname='<?php esc_html_e('Details', 'eib2bpro'); ?>'>
                                            <span class="eib2bpro-Order_No  d-inline d-lg-none"><?php esc_html_e('Order No', 'eib2bpro'); ?>: <?php echo esc_attr($order['std']->get_order_number()) ?><br /><br /></span>
                                            <span><?php echo wc_format_datetime($order['date_created'], 'd M,');; ?></span>
                                            <span><?php echo wc_format_datetime($order['date_created'], 'H:i'); ?><br /></span>
                                            <div class="mt-2 pb-1"><?php echo esc_html(preg_replace('~<span(.*?)</span>~Usi', '', $order['payment_method_title'])) ?></div>
                                        </div>
                                        <div class="col col-sm-4 d-md-none d-lg-block eib2bpro-Order_Products eib2bpro-Col_3" data-colname='<?php esc_html_e('Products', 'eib2bpro'); ?>'>
                                            <?php do_action('eib2bpro_order_details_closed', intval($order['id'])) ?>

                                            <?php foreach ($order['line_items'] as $item) { ?>
                                                <?php echo eib2bpro_product_image($item['product_id'], $item['quantity'], 'width: 55px;'); ?>
                                            <?php } ?>
                                            <?php if ($order['customer_note'] && esc_attr($order['status']) !== "completed") { ?>
                                                <div class="eib2bpro-Clear_Both"></div>
                                                <div class="eib2bpro-Order_Customer_Notice bg-warning"><?php printf(esc_html__('Note: %s', 'eib2bpro'), esc_html($order['customer_note'])) ?></div>
                                            <?php } ?>
                                        </div>
                                        <div class="col eib2bpro-Col_Price eib2bpro-Col_3X text-right" data-colname='Price'>
                                            <span class="eib2bpro-orders--item-price"><?php echo wc_price($order['total'], array('currency' => $order['currency'], 'price_format' => get_woocommerce_price_format())); ?></span>
                                            <br>
                                            <span class="badge badge-pill badge-<?php echo esc_attr($order['status']) ?> d-inline-block d-sm-none eib2bpro-Order_Status_R"><?php echo esc_html(wc_get_order_status_name($order['status'])); ?></span>

                                        </div>


                                        <div class="col col-sm-1  eib2bpro-Actions text-center d-none">
                                            <span class="dashicons dashicons-arrow-down-alt2 bthidden1" aria-hidden="true"></span>
                                            <span class="dashicons dashicons-no-alt bthidden" aria-hidden="true"></span>

                                        </div>

                                    </div>
                                </div>
                                <div class="collapse col-xs-12 col-sm-12 col-md-12 eib2bpro-Order_Details" id="order_<?php echo esc_attr($order['id']) ?>">
                                    <div class="row m-0">


                                        <div class="row eib2bpro-Order_Items">
                                            <div class="col-md-4 col-sm-6">

                                                <?php foreach ($order['line_items'] as $item) { ?>
                                                    <div class="row eib2bpro-Order_Item">
                                                        <div class="col-3 col-sm-3 col-md-2"><img src="<?php echo get_the_post_thumbnail_url($item['product_id']); ?>" class="eib2bpro-Product_Image"></div>
                                                        <div class="col-9 col-sm-9 col-md-10">
                                                            <h4><?php echo esc_html($item['name']) ?></h4>
                                                            <?php
                                                            $formatted_meta_data = $item->get_formatted_meta_data();
                                                            if ($formatted_meta_data) { ?>
                                                                <div class="eib2bpro-Order_Details_Variation">
                                                                    <?php
                                                                    foreach ($formatted_meta_data as $meta) {
                                                                        echo '<strong>' . esc_html($meta->display_key) . '</strong>: <span class="badgex badge-pillx badge-blackx"> ' . wp_kses_post($meta->display_value) . '</span> &nbsp; &nbsp;<br> ';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            <?php } ?>
                                                            <div class="fiyat">
                                                                <?php echo wc_price(($item['subtotal'] / $item['qty']), array('currency' => $order['currency'])); ?>
                                                                x
                                                                <span class="badge badge-pill badge-danger"><?php echo esc_html($item['qty']); ?></span>
                                                                = <?php echo wc_price($item['subtotal'], array('currency' => $order['currency'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php foreach ($order['coupon_lines'] as $item) { ?>
                                                    <div class="row eib2bpro-Order_Item">
                                                        <div class="col-3 col-sm-3 col-md-2">
                                                            <div class="eib2bpro-Order_Item_Group"><span class="ri-coupon-3-fill"></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-9 col-sm-9 col-md-10">
                                                            <h4 class="text-uppercase"><?php echo esc_html($item->get_code()) ?></h4>

                                                            <div class="fiyat">
                                                                - <?php echo wc_price($item->get_discount(), array('currency' => $order['currency'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php foreach ($order['shipping_lines'] as $item) { ?>
                                                    <div class="row eib2bpro-Order_Item">
                                                        <div class="col-3 col-sm-3 col-md-2">
                                                            <div class="eib2bpro-Order_Item_Group"><i class="ri-truck-fill"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-9 col-sm-9 col-md-10">
                                                            <h4><?php echo esc_html($item['name']) ?></h4>

                                                            <div class="fiyat">
                                                                <?php echo wc_price($item['total'], array('currency' => $order['currency'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php foreach ($order['fee_lines'] as $item) { ?>

                                                    <div class="row eib2bpro-Order_Item">
                                                        <div class="col-3 col-sm-3 col-md-2">
                                                            <div class="eib2bpro-Order_Item_Group">%</div>
                                                        </div>
                                                        <div class="col-9 col-sm-9 col-md-10">
                                                            <h4><?php echo esc_html($item['name']) ?></h4>

                                                            <div class="fiyat">
                                                                <?php echo wc_price($item['total'], array('currency' => $order['currency'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>


                                                <?php foreach ($order['tax_lines'] as $item) { ?>

                                                    <div class="row eib2bpro-Order_Item">
                                                        <div class="col-3 col-sm-3 col-md-2">
                                                            <div class="eib2bpro-Order_Item_Group">%</div>
                                                        </div>
                                                        <div class="col-9 col-sm-9 col-md-10">
                                                            <h4><?php echo esc_html($item['label']) ?></h4>

                                                            <div class="fiyat">
                                                                <?php echo wc_price($item['tax_total'] + $item['shipping_tax_total'] + $item['discount_tax'], array('currency' => $order['currency'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php do_action('eib2bpro_order_details', intval($order['id'])) ?>

                                            </div>
                                            <div class="col-sm-1"></div>
                                            <div class="col-md-4 col-sm-5 eib2bpro-StopPropagation eib2bpro-Order_Address">

                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <h4><?php esc_html_e('Billing Address', 'eib2bpro'); ?></h4>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-12"><?php echo wp_kses_post($order['billing_formatted']) ?></div>
                                                    <div class="col-xs-12 col-sm-12 col-md-12 p20">
                                                        <strong><?php esc_html_e('E-Mail', 'eib2bpro'); ?></strong>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-12"><a href="mailto:<?php echo sanitize_email($order['billing']['email']) ?>"><?php echo sanitize_email($order['billing']['email']) ?></a>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-12 p20">
                                                        <strong><?php esc_html_e('Telephone', 'eib2bpro'); ?></strong>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-12"><a href="tel:<?php echo esc_attr($order['billing']['phone']) ?>"><?php echo esc_html($order['billing']['phone']) ?></a>
                                                    </div>
                                                </div>

                                                <div class="row">&nbsp;</div>

                                                <?php if ('' !== trim($order['shipping']['address_1']) or '' !== trim($order['shipping']['address_2'])) { ?>
                                                    <div class="row eib2bpro-StopPropagation">
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <h4><?php esc_html_e('Shipping Address', 'eib2bpro'); ?></h4>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-12"><?php echo wp_kses_post($order['shipping_formatted']) ?></div>
                                                        <?php if (isset($order['shipping']['email'])) { ?>
                                                            <div class="col-xs-12 col-sm-12 col-md-12 p20">
                                                                <strong><?php esc_html_e('E-Mail', 'eib2bpro'); ?></strong>
                                                            </div>
                                                            <div class="col-xs-12 col-sm-12 col-md-12"><a href="mailto:<?php echo sanitize_email($order['shipping']['email']) ?>"><?php echo sanitize_email($order['shipping']['email']) ?></a>
                                                            </div>
                                                        <?php } ?>
                                                        <?php if (isset($order['shipping']['phone']) && !empty($order['shipping']['phone'])) { ?>
                                                            <div class="col-xs-12 col-sm-12 col-md-12 p20">
                                                                <strong><?php esc_html_e('Telephone', 'eib2bpro'); ?></strong>
                                                            </div>
                                                            <div class="col-xs-12 col-sm-12 col-md-12"><a href="tel:<?php echo esc_attr($order['shipping']['phone']) ?>"><?php echo esc_html($order['shipping']['phone']) ?></a>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>

                                            </div>
                                            <div class="col-sm-1"></div>
                                            <div class="col-md-2 col-sm-12 eib2bpro-Order_Address eib2bpro-Order_Actions">
                                                <?php if (!empty($order['next_statuses']) && 'trash' !== $order['status']) { ?>
                                                    <div class="row">
                                                        <h4><?php esc_html_e('Change to...', 'eib2bpro'); ?></h4>
                                                        <?php $order_statuses = wc_get_order_statuses();
                                                        $order_statuses['trash'] = esc_html__('Delete', 'eib2bpro'); ?>
                                                        <?php foreach ($order['next_statuses'] as $next_status) { ?>
                                                            <a href="javascript:;" data-status="<?php echo esc_attr($next_status) ?>" data-do='changestatus' data-id='<?php echo esc_attr($order['id']) ?>' data-text="<?php echo esc_html($order_statuses[$next_status]) ?>" class="eib2bpro-Ajax_Button eib2bpro-StopPropagation eib2bpro-Order_Change_Statuses"><span class="text-<?php echo str_replace('wc-', '', esc_attr($next_status)) ?>">⬤</span><?php echo esc_html($order_statuses[$next_status]) ?>
                                                            </a>
                                                        <?php } ?>
                                                        <br />
                                                    </div>
                                                <?php } ?>
                                                <div class="row">
                                                    <?php if ('trash' === $order['status']) { ?>
                                                        <a href="javascript:;" data-status="restore" data-do='changestatus' data-id='<?php echo esc_attr($order['id']) ?>' class="eib2bpro-Ajax_Button eib2bpro-StopPropagation"><?php esc_html_e('Restore order', 'eib2bpro'); ?></a>
                                                        <a href="javascript:;" data-status="deleteforever" data-do='changestatus' data-id='<?php echo esc_attr($order['id']) ?>' class="eib2bpro-Ajax_Button eib2bpro-StopPropagation"><?php esc_html_e('Delete forever', 'eib2bpro'); ?></a>
                                                    <?php } else { ?>
                                                        &nbsp;
                                                        <br />
                                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . esc_attr($order['id']) . '&action=edit&eib2bpro_hide')); ?>" class=" eib2bpro-Ajax_Btn_SP eib2bpro-panel" data-hash="<?php echo esc_attr($order['id']) ?>"><?php esc_html_e('View order details', 'eib2bpro'); ?></a>
                                                        <br />
                                                        <br />
                                                        <a href="mailto:<?php echo sanitize_email($order['billing']['email']) ?>"><?php esc_html_e('E-mail to customer', 'eib2bpro'); ?></a>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
            </div>
            <?php if (!$ajax) { ?>
                <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $list['statuses_count'][eib2bpro_get('status', 'count')], 'per_page' => absint(eib2bpro_option('orders-per-page', 10)), 'page' => intval(eib2bpro_get('pg', 0)))); ?>
        </div>
    </div>
<?php }

            if (isset($filter['mode']) && 98 === intval($filter['mode'])) {
                echo '</div></div>';
            }
?>