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

    <div id="eib2bpro-orders-2" class="eib2bpro-GP eib2bpro-GP_Top eib2bpro-Filter_Closed">

        <div class="eib2bpro-Searching<?php if ('' === eib2bpro_get('s', '')) echo " closed"; ?>">
            <div class="eib2bpro-Searching_In">
                <input type="text" class="form-control eib2bpro-Search_Input" placeholder="<?php esc_html_e('Search in orders...', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>" data-status='<?php echo esc_attr(eib2bpro_get('status')) ?>' autofocus></span>
            </div>
        </div>

    <?php } ?>


    <div class="eib2bpro-list-m2 eib2bpro-List_M2-1 eib2bpro-Panel_Orders eib2bpro-Container">

        <?php if (0 === count($orders['all']['orders'])) { ?>
            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                </div>
            </div>
        <?php } else { ?>


            <table class="table table-hover">
                <thead>
                    <tr class="eib2bpro-Standart">
                        <th scope="col" class="eib2bpro-Table_Col2"><a href="<?php echo eib2bpro_thead_sort('id') ?>"><?php esc_html_e('No', 'eib2bpro'); ?></a></th>
                        <th scope="col" class="eib2bpro-Table_Col3"><?php esc_html_e('Status', 'eib2bpro') ?></th>
                        <th scope="col" class="eib2bpro-Table_Col4 eib2bpro-Col_Customer"><?php esc_html_e('Customer', 'eib2bpro') ?></th>
                        <th scope="col" class="eib2bpro-Table_Col5"><a href="<?php echo eib2bpro_thead_sort('post_date') ?>"><?php esc_html_e('Details', 'eib2bpro') ?></a>
                        </th>
                        <th scope="col" class="eib2bpro-Table_Col6 eib2bpro-Col_Products"><?php esc_html_e('Products', 'eib2bpro') ?></th>
                        <th scope="col" class="eib2bpro-Table_Col7 text-right"><a href="<?php echo eib2bpro_thead_sort('meta__order_total') ?>"><?php esc_html_e('Total', 'eib2bpro') ?></a>
                        </th>
                        <?php if ('trash' === eib2bpro_get('status')) { ?>
                            <th scope="col" class="text-right eib2bpro-Table_Col8"></th>
                        <?php } else { ?>
                            <th scope="col" class="text-right eib2bpro-Table_Col9"></th>
                        <?php } ?>

                    </tr>

                </thead>

                <tbody>

                    <tr class="eib2bpro-List_M2_Bulk eib2bpro-Bulk eib2bpro-Display_None">
                        <td scope="col" colspan="9">
                            <?php if ('trash' === eib2bpro_get('status')) { ?>
                                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Restore" data-do="changestatus" data-status='restore' href="javascript:;"><?php esc_html_e('Restore orders', 'eib2bpro'); ?></a>
                                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Restore" data-do="changestatus" data-status='deleteforever' href="javascript:;"><?php esc_html_e('Delete forever', 'eib2bpro'); ?></a>
                            <?php } else { ?>
                                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="changestatus" data-status='processing' href="javascript:;"><?php esc_html_e('Change status to &mdash; Processing', 'eib2bpro'); ?></a>
                                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="changestatus" data-status='on-hold' href="javascript:;"><?php esc_html_e('Change status to &mdash; On-Hold', 'eib2bpro'); ?></a>
                                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="changestatus" data-status='completed' href="javascript:;"><?php esc_html_e('Change status to &mdash; Completed', 'eib2bpro'); ?></a>
                                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="changestatus" data-status='trash' href="javascript:;"><?php esc_html_e('Move to trash', 'eib2bpro'); ?></a>
                            <?php } ?>

                        </td>
                    </tr>

                    <?php foreach ($orders as $order_group) { ?>

                        <?php foreach ($order_group['orders'] as $order) { ?>
                            <tr class="align-middle eib2bpro-Status<?php echo esc_attr($order['status']) ?>" id='item_<?php echo absint($order['id']); ?>'>
                                <td class="eib2bpro-Col_No align-middle"><?php echo esc_attr($order['id']) ?></td>
                                <td class="d-none d-sm-table-cell align-middle eib2bpro-orders--item-badge" data-colname="Status">
                                    <span class="badge badge-pill badge-<?php echo esc_attr($order['status']) ?>"><?php echo wc_get_order_status_name($order['status']); ?></span>
                                </td>
                                <td class="eib2bpro-Col_Name d-none d-sm-table-cell align-middle">
                                    <p class="eib2bpro-orders--name">
                                        <?php echo eib2bpro_clean($order['shipping']['first_name'], $order['billing']['first_name']) . " " . eib2bpro_clean($order['shipping']['last_name'], $order['billing']['last_name']); ?>
                                    </p>
                                    <p class="eib2bpro-orders--address">
                                        <?php echo eib2bpro_clean($order['shipping']['city'], $order['billing']['city']) . ', ' . \WC()->countries->states[eib2bpro_clean($order['shipping']['country'], $order['billing']['country'])][eib2bpro_clean($order['shipping']['state'], $order['billing']['state'])]; ?>
                                    </p>

                                    <span class="badge badge-pill badge-<?php echo esc_attr($order['status']) ?> d-inline-block d-sm-none eib2bpro-Order_Status_R"><?php echo esc_attr($order['status']); ?></span>

                                </td>
                                <td class="eib2bpro-Col_3 eib2bpro-Col_Details align-middle" data-colname="Details">
                                    <span><strong><?php echo eib2bpro_clean($order['shipping']['first_name'], $order['billing']['first_name']) . " " . eib2bpro_clean($order['shipping']['last_name'], $order['billing']['last_name']); ?></strong> &nbsp; &nbsp; </span>
                                    <span><?php echo date("d F", strtotime($order['date_created'])); ?>
                                        <br />
                                        <?php echo esc_html($order['payment_method_title']) ?>
                                    </span>
                                </td>
                                <td class="eib2bpro-Col_3 align-middle eib2bpro-Col_Products" data-colname="Products">
                                    <?php foreach ($order['line_items'] as $item) {
                                        echo esc_url(eib2bpro_product_image($item['product_id'], $item['quantity'], 'height:60px;'));
                                    } ?>
                                    <div class="eib2bpro-Clear_Both">

                                        <?php if ('trash' === $order['status']) { ?>
                                            <div class="d-none d-sm-block">
                                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $order['id'] . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-Button1 eib2bpro-Ajax_Button eib2bpro-MainButton" data-do='restore' data-id="<?php echo esc_attr($order['id']) ?>"><?php esc_html_e('Restore order', 'eib2bpro') ?></a>
                                                &nbsp;
                                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $order['id'] . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-Button1 eib2bpro-Ajax_Button" data-do='deleteforever' data-id="<?php echo esc_attr($order['id']) ?>"><?php esc_html_e('Delete order forever', 'eib2bpro') ?></a>
                                            </div>
                                        <?php } ?>

                                    </div>
                                    <?php if ($order['customer_note'] && $order['status'] !== "completed") { ?>
                                        <div class="eib2bpro-Clear_Both"></div>
                                        <div class="eib2bpro-Order_Customer_Notice bg-warning"><?php printf(esc_html__('Note: %s', 'eib2bpro'), esc_html($order['customer_note'])) ?></div>
                                    <?php } ?>
                                </td>
                                <td class="eib2bpro-Col_Price align-middle text-right" data-colname='Price'>
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . intval($order['id']) . '&action=edit&eib2bpro_hide')); ?>"><span class="eib2bpro-orders--item-price"><?php echo wc_price($order['total'], array()); ?></span></a>
                                    <button class="eib2bpro-Mobile_Actions eib2bpro-M21"><span class="dashicons dashicons-arrow-down-alt2"></span></button>

                                </td>


                                <td class="eib2bpro-Col_3 eib2bpro-Col_Actions  align-middle text-right">
                                    <ul>
                                        <?php if ('trash' === $order['status']) { ?>
                                            <li class="d-inline-block d-sm-none">
                                                <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($order['id']) . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-Button1 eib2bpro-Ajax_Button eib2bpro-MainButton" data-do='restore' data-id="<?php echo absint($order['id']) ?>"><?php esc_html_e('Restore order', 'eib2bpro') ?></a>
                                            </li>

                                            <li class="d-inline-block d-sm-none">
                                                <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($order['id']) . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-Button1 eib2bpro-Ajax_Button" data-do='deleteforever' data-id="<?php echo absint($order['id']) ?>"><?php esc_html_e('Delete order forever', 'eib2bpro') ?></a>
                                            </li>

                                        <?php } else { ?>
                                            <li>
                                                <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($order['id']) . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-Button1 eib2bpro-MainButton eib2bpro-panel"><?php esc_html_e('View', 'eib2bpro') ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>

                </tbody>
            </table>

        <?php } ?>

        <?php if (!$ajax) { ?>
            <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $list['statuses_count'][eib2bpro_get('status', 'count')], 'per_page' => eib2bpro_option('per_page', 10), 'page' => intval(eib2bpro_get('pg', 0)))); ?>
        <?php } ?>
    </div>
    </div>