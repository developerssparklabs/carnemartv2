<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (!$ajax) { ?>

    <?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
    <?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Coupons', 'eib2bpro'), 'description' => '', 'buttons' => '<a href="' . admin_url('post-new.php?post_type=shop_coupon&eib2bpro_hide') . '" class="btn btn-sm btn-danger eib2bpro-panel">  &nbsp;+ &nbsp; ' . esc_html__('New coupon', 'eib2bpro') . ' &nbsp;</a>')); ?>
    <?php echo eib2bpro_view('coupons', 1, 'nav', array('counts' => $counts)) ?>

    <div id="eib2bpro-coupons-1" class="eib2bpro-GP">
        <div class="eib2bpro-Searching<?php if ('' === eib2bpro_get('s', '')) echo " closed"; ?>">
            <div class="eib2bpro-Searching_In">
                <input type="text" class="form-control eib2bpro-Search_Input" placeholder="<?php esc_html_e('Search in coupons...', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>"></span>
            </div>
        </div>
        <div class="eib2bpro-List_M1 eib2bpro-Container">
        <?php } ?>
        <?php if (0 === count($coupons)) { ?>
            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="eib2bpro-Coupons_Container">
                <?php foreach ($coupons as $coupon) { ?>
                    <div class="btnA eib2bpro-Item collapsed" id="item_<?php echo esc_attr($coupon['id']) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($coupon['id']) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($coupon['id']) ?>">
                        <div class="container-fluid">
                            <div class="liste row d-flex align-items-center">
                                <div class="col-4 col-sm-2 d-flex align-items-center text-left eib2bpro-Coupon_Code">
                                    <span class="eib2bpro-Code badge badge-pill badge-black eix-quick" data-href="<?php echo esc_url(admin_url('post.php?post=' . $coupon['id'] . '&action=edit&eib2bpro_hide')) ?>"><?php echo esc_attr($coupon['code']) ?></span>
                                    <button class="eib2bpro-Mobile_Actions eib2bpro-M1-A"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                                </div>
                                <div class="eib2bpro-Details2R"></div>
                                <div class="eib2bpro-Coupon_Info col-8 col-sm-10 col-md-3">
                                    <h6>
                                        <?php switch ($coupon['type']) {
                                            case "fixed_cart": ?>
                                                <?php echo sprintf(esc_html__('%s %s', 'eib2bpro'), get_woocommerce_currency_symbol(), esc_html($coupon['amount'])); ?>
                                                <?php break; ?>

                                            <?php
                                            case "percent": ?>
                                                <?php echo sprintf('%s', esc_html($coupon['amount']) . '%'); ?>
                                                <?php break; ?>

                                            <?php
                                            default: ?>
                                                <?php echo sprintf('%s %s', get_woocommerce_currency_symbol(), esc_html($coupon['amount'])); ?>
                                                <?php break; ?>
                                        <?php } ?>
                                    </h6>
                                    <?php if (!in_array($coupon['status'], ['publish', 'private', 'trash'])) { ?>
                                        <span class="badge badge-pill badge-danger text-uppercase"><?php echo esc_attr($coupon['status']) ?></span>
                                    <?php } ?>
                                    <?php
                                    if ($coupon['expiry_date']) {
                                        echo esc_html(date_i18n('d M', strtotime($coupon['post_date'])) . ' - ' . date_i18n('d M', $coupon['expiry_date']));
                                    } else {
                                        echo "";
                                    } ?>
                                </div>
                                <div class="eib2bpro-Col_Coupon_Stats col-7 eib2bpro-Col_3" data-colname='' id="eib2bpro-coupons-stats">
                                    <?php if (0 < $coupon['stats']['usage_count']) { ?>
                                        <div class="container">
                                            <div class="row d-flex align-items-center">
                                                <div class="col">
                                                    <div class="eib2bpro-Big"><?php echo esc_attr($coupon['stats']['usage_count']) ?></div> <?php esc_html_e('TIMES USED', 'eib2bpro'); ?>
                                                </div>
                                                <div class="col">
                                                    <div class="eib2bpro-Big"><?php echo wc_price($coupon['stats']['total_discount']) ?></div> <?php esc_html_e('TOTAL DISCOUNT', 'eib2bpro'); ?>
                                                </div>
                                                <div class="col">
                                                    <div class="eib2bpro-Big"><?php echo wc_price($coupon['stats']['total_sales']) ?></div> <?php esc_html_e('TOTAL SPENT', 'eib2bpro'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if ($coupon['expiry_date'] && $coupon['expiry_date'] < time()) { ?>
                                        <div class="eib2bpro-Coupon_Expired">
                                            <span class="dashicons dashicons-info"></span>
                                            &nbsp; <?php esc_html_e('This coupon has expired', 'eib2bpro'); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_<?php echo esc_attr($coupon['id']) ?>">
                            <div class="eib2bpro-Item_Details">
                                <?php if ('publish' === $coupon['status'] or 'private' === $coupon['status'] or 'future' === $coupon['status'] or 'draft' === $coupon['status']) { ?>
                                    <span class="eib2bpro-StopPropagation">
                                        <label class="switch">
                                            <input type="checkbox" value="1" data-nonce="<?php echo wp_create_nonce('eib2bpro-coupons--onoff-' . $coupon['code']) ?>" data-id="<?php echo esc_attr($coupon['code']) ?>" class="success eib2bpro-ActivePassive eib2bpro-StopPropagation" <?php if ('publish' === $coupon['status']) echo ' checked'; ?> />
                                            <span class="eib2bpro-slider round"></span>
                                        </label></span>
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $coupon['id'] . '&action=edit&eib2bpro_hide')) ?>" class="eib2bpro-StopPropagation eib2bpro-HideMe eib2bpro-panel" data-hash="<?php echo esc_attr($coupon['id']) ?>"><?php esc_html_e('Edit', 'eib2bpro'); ?></a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wc-admin&filter=single_coupon&coupons=' . $coupon['id'] . '&path=/analytics/coupons&period=year&compare=previous_year')) ?>" class="eib2bpro-StopPropagation eib2bpro-panel" data-width="1100px"><?php esc_html_e('Stats', 'eib2bpro'); ?></a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wc-admin&filter=advanced&path=/analytics/orders&period=year&compare=previous_year&coupon_includes=' . $coupon['id'])) ?>" class="eib2bpro-StopPropagation eib2bpro-panel" data-width="1100px"><?php esc_html_e('Orders', 'eib2bpro'); ?></a>
                                    <a href="<?php echo eib2bpro_secure_url('coupons', $coupon['id'], array('action' => 'delete', 'id' => $coupon['id'])); ?>" class="eib2bpro-HideMe eib2bpro-StopPropagation text-danger"><?php esc_html_e('Delete', 'eib2bpro'); ?></a>
                                <?php } else { ?>
                                    <a href="<?php echo eib2bpro_secure_url('coupons', $coupon['id'], array('action' => 'delete', 'untrash' => 'true', 'id' => $coupon['id'])); ?>" class="eib2bpro-HideMe  eib2bpro-StopPropagation"><?php esc_html_e('Restore Coupon', 'eib2bpro'); ?></a>
                                    <a href="<?php echo eib2bpro_secure_url('coupons', $coupon['id'], array('action' => 'delete', 'forever' => 'true', 'id' => $coupon['id'])); ?>" class="eib2bpro-HideMe eib2bpro-StopPropagation"><?php esc_html_e('Delete Forever', 'eib2bpro'); ?></a>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
            </div>
            <?php if (!$ajax) { ?>
                <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $count, 'page' => intval(eib2bpro_get('pg', 0)))); ?>
        </div>

    <?php } ?>
    </div>
    </div>