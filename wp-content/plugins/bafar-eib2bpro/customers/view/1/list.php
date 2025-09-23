<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (!$ajax) { ?>

    <?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
    <?php
    $buttons = '';
    if (!eib2bpro_get('mini')) {
        $buttons = '<a href="' . admin_url('user-new.php?eib2bpro_hide') . '" class="btn btn-sm btn-danger eib2bpro-panel">  &nbsp;+ &nbsp; ' . esc_html__('New customer', 'eib2bpro') . ' &nbsp;</a>';
    }
    ?>
    <?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Customers', 'eib2bpro'), 'description' => '', 'buttons' => $buttons)); ?>
    <?php echo eib2bpro_view('customers', 1, 'nav-' . eib2bpro_option('customers_nav', 'groups'), array('counts' => $counts, 'roles' => $roles)) ?>

    <div id="eib2bpro-customers-1" class="">

        <div class="eib2bpro-Searching<?php if ('' === eib2bpro_get('s', '') && !eib2bpro_get('mini')) echo " closed"; ?>">
            <div class="eib2bpro-Searching_In">
                <input type="text" class="form-control eib2bpro-Search_Input" placeholder="<?php esc_html_e('Search in customers...', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>"></span>
            </div>
        </div>

        <div class="eib2bpro-List_M1 eib2bpro-Container eib2bpro-GP">
        <?php }
    if (isset($filter['mode']) && 98 === intval($filter['mode'])) {
        echo '<h2 class="badge badge-black badge-pill eib2bpro-Badge_Big_Title">' . esc_html__('Customers', 'eib2bpro') . '</h2>
    <div id="eib2bpro-customers-1"><div class="eib2bpro-GP eib2bpro-List_M1 eib2bpro-Container">';
    } ?>
        <?php if (0 === count($customers)) { ?>
            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                <div>
                    <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="eib2bpro-Customers_Container">
                <?php foreach ($customers as $customer) { ?>
                    <div class="btnA eib2bpro-Item eib2bpro-Item-Ajax collapsed" id="item_<?php echo esc_attr($customer['id']) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($customer['id']) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($customer['id']) ?>" data-app="customers" data-do="details" data-id="<?php echo esc_attr($customer['id']) ?>">
                        <div class="container-fluid">
                            <div class="liste row d-flex align-items-center">

                                <div class="col-7 col-sm-3 eib2bpro-Col_Name">
                                    <p class="eib2bpro-orders--name eix-quick" data-href="<?php echo esc_url(admin_url("user-edit.php?user_id=" . esc_attr($customer['id']))); ?>" data-width="850px">
                                        <?php echo esc_html(sprintf('%s %s', $customer['first_name'], $customer['last_name'])) ?>
                                    </p>
                                    <p class="eib2bpro-orders--address eix-quick" data-href="<?php echo esc_url(admin_url("user-edit.php?user_id=" . esc_attr($customer['id']))); ?>" data-width="850px">
                                        <?php echo esc_html(isset(WC()->countries->states[$customer['billing_address']['country']][$customer['billing_address']['state']]) ? WC()->countries->states[$customer['billing_address']['country']][$customer['billing_address']['state']] : $customer['billing_address']['state']) ?>
                                    </p>
                                </div>
                                <div class="col col-2 eib2bpro-Col_Phone eib2bpro-Col_3 eib2bpro-StopPropagation  align-middle" data-colname="<?php esc_attr_e('Phone', 'eib2bpro'); ?>">
                                    <?php eib2bpro_e(\EIB2BPRO\B2b\Site\Main::user('group_name', '', $customer['id'])); ?>
                                </div>
                                <div class="col col-3 eib2bpro-Col_Email eib2bpro-Col_3 eib2bpro-StopPropagation align-middle" data-colname="<?php esc_attr_e('E-mail', 'eib2bpro'); ?>">
                                    <a href="mailto:<?php echo esc_attr($customer['email']) ?>"><?php echo esc_html($customer['email']) ?></a>
                                    <br>
                                    <a href="tel:<?php echo esc_attr($customer['billing_address']['phone']) ?>"><?php echo esc_html($customer['billing_address']['phone']) ?></a>
                                </div>

                                <div class="col col-2 eib2bpro-Col_OrderCount eib2bpro-Col_3 align-middle text-right" data-colname="<?php esc_attr_e('Orders', 'eib2bpro'); ?>" data-order-count="<?php echo esc_attr($customer['orders_count']) ?>"><?php echo esc_html($customer['orders_count']) ?> <?php esc_html_e('ORDERS', 'eib2bpro'); ?></div>
                                <div class="col col-sm-2 eib2bpro-Col_TotalSpent eib2bpro-Col_3X text-right" data-colname="<?php esc_attr_e('Spent', 'eib2bpro'); ?>">
                                    <span class="eib2bpro-orders--item-price"><?php echo wc_price($customer['total_spent']); ?></span>
                                    <button class="eib2bpro-Mobile_Actions eib2bpro-M1-A"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_<?php echo esc_attr($customer['id']) ?>">
                            <div class="eib2bpro-Item_Details">
                                <div class="row">
                                    <div class="col-sm-9  eib2bpro-Customer_Details eib2bpro-Item-Ajax-Container text-left">
                                        <div class="lds-ellipsis lds-ellipsis-black">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-2 eib2bpro-Customer_Details_Actions">
                                        <a href="<?php echo esc_url(admin_url("user-edit.php?user_id=" . esc_attr($customer['id']))); ?>" class="eib2bpro-HideMe eib2bpro-StopPropagation eib2bpro-panel" data-width="850px"><?php esc_html_e('Edit customer', 'eib2bpro'); ?></a>
                                        <a href="mailto:<?php echo sanitize_email($customer['email']) ?>" class="eib2bpro-StopPropagation eib2bpro-HideMe eib2bpro-panel"><?php esc_html_e('Send e-mail', 'eib2bpro'); ?></a>
                                        <?php if (current_user_can('delete_users')) { ?><a href="<?php echo wp_nonce_url("users.php?action=delete&user=" . esc_attr($customer['id']), 'bulk-users'); ?>" class="eib2bpro-HideMe eib2bpro-StopPropagation text-danger eib2bpro-panel"><?php esc_html_e('Delete', 'eib2bpro'); ?></a><?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!$ajax) { ?>
            <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $count, 'per_page' => absint(eib2bpro_option('reactors-tweaks-pg-customers', 10)), 'page' => intval(eib2bpro_get('pg', 0)))); ?>
        </div>
    </div>
<?php }
        if (isset($filter['mode']) && 98 === intval($filter['mode'])) {
            echo '</div></div>';
        } ?>