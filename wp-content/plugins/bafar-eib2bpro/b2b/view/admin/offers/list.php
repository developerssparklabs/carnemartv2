<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-container-fluid">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>
    <?php eib2bpro_form(['do' => 'offers-positions']) ?>
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
                                    <h3 class="mt-2 font-weight-bold"><?php esc_html_e('Offers', 'eib2bpro') ?></h3>
                                </div>
                                <div class="col-12 col-lg-4 mt-4 mt-lg-1 text-right">
                                    <a class="eib2bpro-panel btn btn-sm btn-danger eib2bpro-rounded" href="<?php echo eib2bpro_admin('b2b', ['section' => 'offers', 'action' => 'edit', 'id' => 0]) ?>" data-width="700px">
                                        + <?php esc_html_e('New offer', 'eib2bpro'); ?>
                                    </a>
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
                        <div class="eib2bpro-offers-container eib2bpro-list-container eib2bpro-shadow">
                            <ol class="eib2bpro-sortable eib2bpro-sortable-registration-regtypes">
                                <?php foreach ($list->posts as $item) { ?>
                                    <li>
                                        <div class="btnA eib2bpro-Item collapsed pb-1 p-0 " id="item_<?php echo esc_attr($item->ID) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($item->ID) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($item->ID) ?>">
                                            <div class="liste d-flex align-items-center overflow-hidden">
                                                <div data-col="name" class="col-4 col-sm-2 col-lg-1 col-id-movep-30">
                                                    <h6 class="m-0 p-0 pl-3">
                                                        <input type="hidden" name="position[<?php eib2bpro_a($item->ID) ?>]" value="1">
                                                        <?php eib2bpro_ui('onoff_ajax', 'status', 'publish' === $item->post_status ? 1 : 0, ['app' => 'b2b', 'do' => 'change-offer-status', 'id' => $item->ID]) ?>
                                                    </h6>
                                                </div>
                                                <div data-col="name" class="col-8 col-sm-10 col-lg-3 col-id-name p-30">
                                                    <h6 class="eix-quick" data-href="<?php echo eib2bpro_admin('b2b', ['id' => $item->ID, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="700px">
                                                        <?php eib2bpro_e(get_the_title($item->ID)) ?>
                                                    </h6>
                                                    <div class="text-muted eix-quick" data-href="<?php echo eib2bpro_admin('b2b', ['id' => $item->ID, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="700px"><?php echo eib2bpro_r(sprintf(esc_html__(' %s  • %d products', 'eib2bpro'), wc_price(get_post_meta($item->ID, 'eib2bpro_total', true)), count((array)get_post_meta($item->ID, 'eib2bpro_products', true)))) ?></div>

                                                </div>
                                                <div data-col="slug" class="col-12 col-md-8 d-none d-sm-none d-md-none d-lg-block col-id-slug pl-0 pr-0 text-center">
                                                    <div class="row-x">
                                                        <i class="eib2bpro-os-move eib2bpro-icon-move pr-1 pt-2"></i>

                                                        <?php
                                                        $targered = 0;
                                                        $groups = wp_parse_id_list(get_post_meta($item->ID, 'eib2bpro_groups', true));
                                                        foreach ($groups as $group) {
                                                            if (0 < intval($group)) {
                                                                $targered += \EIB2BPRO\B2b\Admin\Groups::count_users(intval($group));
                                                            }
                                                        }
                                                        $targered += count(wp_parse_list(get_post_meta($item->ID, 'eib2bpro_users', true)));
                                                        ?>

                                                        <?php if ('bundle' ===  get_post_meta($item->ID, 'eib2bpro_offer_type', true)) { ?>
                                                            <div class="eib2bpro-offer-stats-box p-30">
                                                                <div class="eib2bpro-offer-stats-number">
                                                                    <?php
                                                                    $atc = intval(get_post_meta($item->ID, '_eib2bpro_offer_stats_buy_count_all', true));
                                                                    if (0 < $atc && 0 < $targered) {
                                                                        eib2bpro_e(min(100, round($atc * 100 / $targered)));
                                                                    } else {
                                                                        eib2bpro_e('0');
                                                                    }
                                                                    ?>%</div>
                                                                <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('Buy', 'eib2bpro'); ?></div>
                                                            </div>
                                                        <?php } ?>

                                                        <?php if ('bundle' ===  get_post_meta($item->ID, 'eib2bpro_offer_type', true)) { ?>
                                                            <div class="eib2bpro-offer-stats-box p-30">
                                                                <div class="eib2bpro-offer-stats-number">
                                                                    <?php
                                                                    $atc = intval(get_post_meta($item->ID, '_eib2bpro_offer_stats_atc_count_all', true));
                                                                    if (0 < $atc && 0 < $targered) {
                                                                        eib2bpro_e(min(100, round($atc * 100 / $targered)));
                                                                    } else {
                                                                        eib2bpro_e('0');
                                                                    }
                                                                    ?>%
                                                                </div>
                                                                <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('Add to cart', 'eib2bpro'); ?></div>
                                                            </div>
                                                        <?php } ?>

                                                        <div class="eib2bpro-offer-stats-box p-30">
                                                            <div class="eib2bpro-offer-stats-number">
                                                                <?php
                                                                $seen = intval(get_post_meta($item->ID, '_eib2bpro_offer_stats_seen_count_all', true));
                                                                if (0 < $seen && 0 < $targered) {
                                                                    eib2bpro_e(min(100, round($seen * 100 / $targered)));
                                                                } else {
                                                                    eib2bpro_e('0');
                                                                }
                                                                ?>%
                                                            </div>
                                                            <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('Seen', 'eib2bpro'); ?></div>
                                                        </div>

                                                        <div class="eib2bpro-offer-stats-box p-30">
                                                            <div class="eib2bpro-offer-stats-number">
                                                                <?php
                                                                eib2bpro_e($targered);
                                                                ?>
                                                            </div>
                                                            <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('Targered', 'eib2bpro'); ?></div>
                                                        </div>

                                                        <div data-col="slug" class="eib2bpro-b2b-chart-div d-none">
                                                            <?php
                                                            for ($i = 15; $i > 0; --$i) {
                                                                $date = date('Ym', strtotime("now - $i months"));
                                                                $date_labels[$date] = date('d M, Y', strtotime("now - $i days"));
                                                            }

                                                            $stats = [];
                                                            $stats_meta = get_post_meta($item->ID, '_eib2bpro_offer_stats_buy_days', true);
                                                            for ($i = 15; $i >= 0; --$i) {
                                                                $date = eib2bpro_strtotime("now - $i day", 'Ymd');
                                                                if (isset($stats_meta[$date])) {
                                                                    $stats[$date] = intval($stats_meta[$date]);
                                                                } else {
                                                                    $stats[$date] = 0;
                                                                }
                                                            }

                                                            ?>
                                                            <div class="eib2bpro-charts" data-type="bar" data-height="90" data-sparkline="true" id="eib2bpro-chart-<?php eib2bpro_a($item->ID) ?>" class="pr-3" data-labels='<?php echo eib2bpro_r(json_encode(array_values($date_labels))) ?>' data-series='<?php echo eib2bpro_r(json_encode([['name' => 'Orders', 'data' => array_values($stats)]])) ?>' class="pr-3"></div>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>

                                            <div class="collapse col-xs-12 col-sm-12 col-md-12" id="item_d_<?php echo esc_attr($item->ID) ?>">
                                                <div class="eib2bpro-Item_Details pl-0 pr-0">
                                                    <div class="container-fluid">
                                                        <div class="row text-left">
                                                            <div class="col-12 col-md-9">

                                                                <?php
                                                                if ('announcement' ===  get_post_meta($item->ID, 'eib2bpro_offer_type', true)) {
                                                                    eib2bpro_e(strip_tags(get_post_meta($item->ID, 'eib2bpro_promo_text', true)));
                                                                } else {
                                                                    $products = get_post_meta($item->ID, 'eib2bpro_products', true);
                                                                    foreach ($products as $_product) {
                                                                        $product = wc_get_product($_product['id']);
                                                                        if ($product) { ?>
                                                                            <div class="eib2bpro-Order_Item border-0 w-50 float-left eib2bpro-offer-style">
                                                                                <div class="row">
                                                                                    <div class="col-3 col-sm-3 col-md-2 text-center"><img src="<?php echo get_the_post_thumbnail_url($_product['id']); ?>" class="eib2bpro-Product_Image"></div>
                                                                                    <div class="col-9 col-sm-9 col-md-10 text-left">
                                                                                        <h4><?php eib2bpro_e($product->get_name()) ?></h4>
                                                                                        <?php if ('bundle' ===  get_post_meta($item->ID, 'eib2bpro_offer_type', true)) { ?>
                                                                                            <div class="fiyat">
                                                                                                <?php echo wc_price(wc_format_decimal($_product['price'])); ?>
                                                                                                x
                                                                                                <span class="badge badge-pill badge-danger"><?php echo esc_html($_product['unit']); ?></span>
                                                                                                = <?php echo wc_price(wc_format_decimal($_product['price']) * $_product['unit']);
                                                                                                    ?>
                                                                                            </div>
                                                                                        <?php } ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                <?php }
                                                                    }
                                                                } ?>

                                                            </div>
                                                            <div class="col-12 col-sm-3 eib2bpro-offer-users-column">
                                                                <?php
                                                                $groups = wp_parse_id_list(get_post_meta($item->ID, 'eib2bpro_groups', true));
                                                                if (isset($groups[0]) && 0 === $groups[0]) {
                                                                    unset($groups[0]);
                                                                }

                                                                if (!empty($groups)) {
                                                                    echo '<h6 class="text-uppercase text-muted">' . esc_html__('Groups', 'eib2bpro') . '</h6>';
                                                                }
                                                                foreach ($groups as $group) {
                                                                    if (0 < intval($group)) {
                                                                        $title = trim(get_the_title($group));
                                                                        if ('' !== $title) {
                                                                            eib2bpro_e(get_the_title($group));
                                                                            echo '<br>';
                                                                        }
                                                                    }
                                                                } ?>
                                                                <?php $users = wp_parse_list(get_post_meta($item->ID, 'eib2bpro_users', true));
                                                                if (!empty($users)) {
                                                                    echo '<h6 class="pt-4 text-uppercase text-muted">' . esc_html__('Users', 'eib2bpro') . '</h6>';
                                                                }
                                                                foreach ($users as $user) {
                                                                    if (!is_numeric($user)) {
                                                                        eib2bpro_e($user);
                                                                    } elseif (get_userdata($user)) {
                                                                        eib2bpro_e(sprintf('%s %s (%s)', get_user_meta($user, 'first_name', true), get_user_meta($user, 'last_name', true), get_userdata($user)->user_login));
                                                                    }
                                                                    echo '<br>';
                                                                } ?>

                                                                <h6 class="pt-4 text-uppercase text-muted"><?php esc_html_e('Created at', 'eib2bpro'); ?></h6>
                                                                <?php eib2bpro_e(date_i18n(get_option('date_format') . ' H:i', strtotime($item->post_date))) ?>

                                                                <br>
                                                                <br>

                                                                <?php if ('bundle' === get_post_meta($item->ID, 'eib2bpro_offer_type', true) && 1 === 2) { ?>

                                                                    <h6 class="pt-4 text-uppercase text-muted"><?php esc_html_e('Share', 'eib2bpro'); ?></h6>
                                                                    <a href="<?php echo eib2bpro_admin('b2b', ['id' => $item->ID, 'section' => 'offers', 'action' => 'mail', 'id' => $item->ID]) ?>" class="eib2bpro-panel pl-0 ml-0" data-width="600px"><?php esc_html_e('Send email', 'eib2bpro') ?></a>
                                                                    <?php if (intval(get_post_meta($item->ID, 'eib2bpro_mail_sent', true)) > 0) { ?>
                                                                        <br>
                                                                        <span class="text-danger">
                                                                            <?php eib2bpro_e(sprintf(esc_html__('Sent: %s', 'eib2bpro'), date_i18n(get_option('date_format'), strtotime(get_post_meta($item->ID, 'eib2bpro_mail_sent', true))))); ?>
                                                                        </span>
                                                                    <?php } ?>
                                                                    <br>
                                                                    <br>

                                                                <?php } ?>

                                                            </div>
                                                        </div>
                                                        <div class="row eib2bpro-Item_Details m-0">
                                                            <div class="col-12 text-right">
                                                                <a href="<?php echo eib2bpro_admin('b2b', ['id' => $item->ID, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="700px"><?php esc_html_e('Edit', 'eib2bpro') ?></a>
                                                                <a href="<?php echo get_delete_post_link($item->ID, false, true); ?>" class="eib2bpro-confirm text-danger" data-confirm="<?php esc_attr_e('Are you sure?', 'eib2bpro'); ?>" data-width="500px"><?php esc_html_e('Delete', 'eib2bpro') ?></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </li>
                                <?php } ?>
                            </ol>
                        </div>
                    </div>
                    <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $list->found_posts, 'per_page' => absint(eib2bpro_option('orders-per-page', 10)), 'page' => intval(eib2bpro_get('pg', 0)))); ?>
                </div>
            </div>
        </div>
    </div>
    <button class="eib2bpro-app-save-button-hidden"></button>
    </form>
</div>