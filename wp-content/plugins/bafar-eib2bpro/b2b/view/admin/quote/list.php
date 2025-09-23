<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-container-fluid">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2 eib2bpro-b2b-nav-container eib2bpro-menu-2-right-border">
                <?php echo eib2bpro_view('b2b', 'admin', 'nav') ?>
            </div>
            <div class="col-12 col-lg-10 pt-4 mt-2 pl-5 s-0 ">
                <div class="eib2bpro-b2b-main-container">
                    <div class="eib2bpro-app-settings-sub-title">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-lg-8">
                                    <h3 class="mt-2 font-weight-bold"><?php esc_html_e('Quote Requests', 'eib2bpro') ?></h3>
                                </div>
                                <div class="col-12 col-lg-4 mt-4 mt-lg-1 text-right">
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($list->posts)) { ?>
                        <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center bg-white eib2bpro-shadow mt-3">
                            <div>
                                <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="eib2bpro-List_M1 eib2bpro-Container mt-2 pt-2">
                        <div class="eib2bpro-Orders_Container eib2bpro-b2b-quote-list">
                            <ol class="eib2bpro-sortable eib2bpro-sortable-registration-regtypes">
                                <?php
                                foreach ($list->posts as $item) {
                                    $offered = get_post_meta($item->ID, 'eib2bpro_offered', true);
                                    $offer_id = get_post_meta($item->ID, 'eib2bpro_offer_id', true); ?>
                                    <li>
                                        <div class="btnA eib2bpro-Item collapsed" id="item_<?php echo esc_attr($item->ID) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($item->ID) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($item->ID) ?>">
                                            <div class="container-fluid">
                                                <div class="liste row d-flex align-items-center">

                                                    <div data-col="approved" class="col-3 col-lg-1 text-left pl-2">
                                                        <i class="eib2bpro-b2b-quote-icon ri-checkbox-circle-fill <?php eib2bpro_a((0 < intval(get_post_meta($item->ID, 'eib2bpro_offered', true))) ? ' text-success' : '') ?>"></i>
                                                    </div>

                                                    <div data-col="name" class="col-9 col-lg-3 col-id-name pl-2 ">
                                                        <h6 class="eix-quick" <?php if ($offered && $offer_id && get_post($offer_id)) { ?> data-href="<?php echo eib2bpro_admin('b2b', ['quote_id' => $item->ID, 'id' => $offer_id, 'section' => 'offers', 'action' => 'edit']) ?>" data-width="700px" <?php } else { ?> data-href="<?php echo eib2bpro_admin('b2b', ['quote' => $item->ID, 'id' => 0, 'section' => 'offers', 'action' => 'edit']) ?>" data-width="700px" <?php } ?>>
                                                            <?php
                                                            $user_mail = '';
                                                            if (0 < ($customer_id = get_post_meta($item->ID, 'eib2bpro_customer_id', true))) {
                                                                $customer = get_userdata($customer_id);
                                                                if ($customer) {
                                                                    eib2bpro_e(sprintf('%s %s', $customer->first_name, $customer->last_name));
                                                                    $user_mail = $customer->user_email;
                                                                }
                                                            } else {
                                                                eib2bpro_e(get_post_meta($item->ID, 'eib2bpro_customer_email', true) ?: esc_html_e('Visitor', 'eib2bpro'));
                                                                $user_mail = get_post_meta($item->ID, 'eib2bpro_customer_email', true) ?: '';
                                                            }
                                                            ?>
                                                        </h6>
                                                        <span class="text-muted text-uppercase eix-quick" <?php if ($offered && $offer_id && get_post($offer_id)) { ?> data-href="<?php echo eib2bpro_admin('b2b', ['quote_id' => $item->ID, 'id' => $offer_id, 'section' => 'offers', 'action' => 'edit']) ?>" data-width="700px" <?php } else { ?> data-href="<?php echo eib2bpro_admin('b2b', ['quote' => $item->ID, 'id' => 0, 'section' => 'offers', 'action' => 'edit']) ?>" data-width="700px" <?php } ?>><?php eib2bpro_e(\EIB2BPRO\B2b\Site\Main::user('group_name', false, $customer_id)) ?></span>
                                                    </div>

                                                    <div data-col="status" class="col col-5 col-id-status d-none d-md-block d-lg-block text-right">
                                                        <?php
                                                        $products = get_post_meta($item->ID, 'eib2bpro_products', true);
                                                        foreach ($products as $product_id => $product_details) {
                                                            $product = wc_get_product($product_id);
                                                            if ($product) {
                                                                echo eib2bpro_product_image($product_id, $product_details['qty'], 'width: 50px;margin:0px');
                                                            }
                                                        } ?>
                                                    </div>

                                                    <div data-col="status" class="col col-3 col-id-status text-right eib2bpro-Col_3" data-colname="<?php esc_html_e('Date', 'eib2bpro'); ?>">
                                                        <?php if ($offered && $offer_id && get_post($offer_id)) { ?>
                                                            <div class="badge badge-pill badge-success mb-2 pl-2 pr-2">
                                                                <h6 class="p-0 m-0"><?php echo wc_price(get_post_meta($offer_id, 'eib2bpro_total', true)) ?></h6>
                                                            </div>
                                                            <br>
                                                        <?php } else { ?>
                                                            <h6><?php printf(esc_html__('%s ago', 'eib2bpro'), human_time_diff(strtotime($item->post_date), current_time('timestamp'))); ?></h6>
                                                        <?php } ?>
                                                        <span class="text-muted"><?php eib2bpro_e(date_i18n(get_option('date_format') . ' H:i', strtotime($item->post_date))) ?></span>
                                                    </div>

                                                </div>
                                            </div>


                                            <div class="collapse col-xs-12 col-sm-12 col-md-12" id="item_d_<?php echo esc_attr($item->ID) ?>">
                                                <div class="eib2bpro-Item_Details">
                                                    <div class="row">
                                                        <div class="col-12 col-lg-4 text-left">

                                                            <?php
                                                            $field_ids = wp_parse_id_list(get_post_meta($item->ID, 'eib2bpro_field_ids', true));
                                                            foreach ($field_ids as $field_id) {
                                                            ?>
                                                                <div class="eib2bpro-Order_Item border-0 pl-0 pb-0">
                                                                    <div class="row">

                                                                        <div class="col-12 col-sm-9 col-md-10">
                                                                            <h4><?php eib2bpro_e(get_post_meta($item->ID, 'eib2bpro_field_' . $field_id . '_title', true)); ?></h4>
                                                                            <?php $values = get_post_meta($item->ID, 'eib2bpro_field_' . $field_id, true);
                                                                            if (is_array($values)) {
                                                                                eib2bpro_e(implode(', ', $values));
                                                                            } elseif (stripos($values, '://') !== false) {
                                                                                echo '<a href="' . esc_url($values) . '" class="pl-0" target="_blank">' . esc_html__('View file', 'eib2bpro') . '</a>';
                                                                            } else {
                                                                                eib2bpro_e(get_post_meta($item->ID, 'eib2bpro_field_' . $field_id, true) ?: '-');
                                                                            } ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            <?php } ?>

                                                        </div>

                                                        <div class="col-12 col-lg-6  text-left mt-4 mt-lg-0">
                                                            <?php
                                                            $products = get_post_meta($item->ID, 'eib2bpro_products', true);
                                                            foreach ($products as $product_id => $product_details) {
                                                                $product = wc_get_product($product_id);
                                                                if ($product) {
                                                            ?>

                                                                    <div class=" eib2bpro-Order_Item border-0 pl-0">
                                                                        <div class="row">
                                                                            <div class="col-3 col-sm-3 col-md-2"><img src="<?php echo get_the_post_thumbnail_url($product_id); ?>" class="eib2bpro-Product_Image"></div>
                                                                            <div class="col-9 col-sm-9 col-md-10  text-left">
                                                                                <h4><?php eib2bpro_e($product->get_name()) ?></h4>
                                                                                <div class="fiyat  text-left">
                                                                                    <?php if (isset($product_details['variation']) || !empty($product_details['variation'])) {
                                                                                        foreach ($product_details['variation'] as $taxonomy => $taxonomy_value) {
                                                                                            $taxonomy = str_replace('attribute_', '', $taxonomy);
                                                                                            echo '<span class="text-muted font-12">' . esc_html(wc_attribute_label($taxonomy, $product)) . ': ' . esc_html($taxonomy_value) . "</span><br>";
                                                                                        }
                                                                                    } ?>

                                                                                    <span class="text-muted font-12"><?php esc_html_e('Qty: ', 'eib2bpro'); ?></span>
                                                                                    <span class="badge badge-pill badge-danger"><?php eib2bpro_e($product_details['qty']);; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                            <?php
                                                                }
                                                            } ?>
                                                        </div>

                                                        <div class="col-12 col-lg-2 mt-4 mt-lg-0">
                                                            <?php
                                                            if ($offered && $offer_id && get_post($offer_id)) { ?>
                                                                <a href="<?php echo eib2bpro_admin('b2b', ['quote_id' => $item->ID, 'id' => $offer_id, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="700px"><?php esc_html_e('Edit the offer', 'eib2bpro') ?></a>
                                                            <?php } else { ?>
                                                                <a href="<?php echo eib2bpro_admin('b2b', ['quote' => $item->ID, 'id' => 0, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="700px"><?php esc_html_e('Make an offer', 'eib2bpro') ?></a>
                                                            <?php } ?>
                                                            <br><br>
                                                            <?php if (!empty($user_mail)) { ?>
                                                                <a href="mailto:<?php eib2bpro_a($user_mail) ?>"><?php esc_html_e('Send email to customer', 'eib2bpro') ?></a>
                                                                <br><br>
                                                            <?php } ?>

                                                            <a href="<?php echo get_delete_post_link($item->ID, false, true); ?>" class="eib2bpro-confirm text-danger" data-confirm="<?php esc_attr_e('Are you sure?', 'eib2bpro'); ?>" data-width="500px"><?php esc_html_e('Delete', 'eib2bpro') ?></a>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php
                                } ?>
                            </ol>
                        </div>
                    </div>
                    <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $list->found_posts, 'page' => intval(eib2bpro_get('pg', 0)))); ?>

                </div>
            </div>
        </div>
    </div>
</div>