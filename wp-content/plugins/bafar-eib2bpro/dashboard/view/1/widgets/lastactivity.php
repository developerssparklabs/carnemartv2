<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (!isset($ajax)) { ?>
    <div class="eib2bpro-Widget_Options">
        <h1 class="float-left">
            <?php esc_html_e('Last Activities', 'eib2bpro'); ?>
        </h1>
        <div class="eib2bpro-Widget_Options_AutoHide float-left">
            <ul>
                <li><a class="eib2bpro-Widget_Settings_Range <?php if ('online' === $args['range']) {
                                                            echo ' eib2bpro-Selected';
                                                        } ?>" data-range='online' data-widgettype="Lastactivity" data-id="<?php echo esc_attr($args['id']) ?>" href="javascript:;"><?php esc_html_e('Online', 'eib2bpro'); ?></a></li>
                <li><a class="eib2bpro-Widget_Settings_Range <?php if ('all' === $args['range']) {
                                                            echo ' eib2bpro-Selected';
                                                        } ?>" data-id="<?php echo esc_attr($args['id']) ?>" data-widgettype="Lastactivity" data-range='all' href="javascript:;"><?php esc_html_e('All', 'eib2bpro'); ?></a></li>
            </ul>
        </div>
        <div class="eib2bpro-Clear_Both"></div>
    </div>
    <div class="eib2bpro-Widget_Lastactivity_container eib2bpro-Range_<?php echo esc_attr($args['range']) ?>">
        <div class="eib2bpro-EmptyTable <?php if (0 < count($result)) {
                                        echo ' d-none';
                                    } else {
                                        echo 'd-flex';
                                    } ?> align-items-center justify-content-center text-center">
            <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No visitors online', 'eib2bpro'); ?></div>
        </div>
    <?php } ?>
    <?php foreach ($result as $session) { ?>
        <div class="eib2bpro-Widget_Lastactivity_row animated fadeInUp d-flex align-items-center eib2bpro-Time_<?php echo date("Hi", strtotime($session['date'])) ?> eib2bpro-Widget_Lastactivity_Sess_<?php echo esc_attr($session['id']) ?>" id="eib2bpro-Widget_Lastactivity_Sess_<?php echo esc_attr($session['id']) ?>">
            <div class="eib2bpro-I1">
                <?php if (300 > (eib2bpro_strtotime('now', 'U') - eib2bpro_strtotime($session['date'], 'U'))) { ?>
                    <div class="eib2bpro-online" data-time="<?php echo date("H:i", strtotime($session['date'])) ?>">
                        <div class="eib2bpro-circle"></div>
                    </div>
                <?php } else { ?>
                    <span class=""><?php echo date("H:i", strtotime($session['date'])) ?></span>
                <?php } ?>
            </div>
            <div class="eib2bpro-I1 eib2bpro-I2">
                <?php echo wp_kses_post($session['visitor']) ?>
            </div>

            <div class="eib2bpro-I">
                <div class="eib2bpro-I_Overflow">
                    <?php foreach ($session['views'] as $views_key => $r) { ?>
                        <div class="eib2bpro-I_O_Container">
                            <?php if (1 === $r['type']) { ?>
                                <img src="<?php echo get_the_post_thumbnail_url($r['details']['id']); ?>" class="eib2bpro-Product_Image" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html($r['details']['name']) ?> (<?php echo esc_html(strip_tags(wc_price($r['details']['price']))) ?>)">
                            <?php } elseif (2 === $r['type']) {  ?>
                                <div class="eib2bpro-Widget_Lastactivity_T2" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html($r['details']['name']) ?>"><?php echo esc_html($r['details']['name']) ?></div>
                            <?php } elseif (4 === $r['type']) {  ?>
                                <img src="<?php echo get_the_post_thumbnail_url($r['details']['id']); ?>" class="eib2bpro-Product_Image">
                                <div class="eib2bpro-Widget_Lastactivity_T4" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html(sprintf(esc_html__('%s (%s) has been added to cart', 'eib2bpro'), $r['details']['name'], strip_tags(wc_price($r['details']['price'])))) ?>">+</div>
                            <?php } elseif (5 === $r['type']) {  ?>
                                <img src="<?php echo get_the_post_thumbnail_url($r['details']['id']); ?>" class="eib2bpro-Product_Image">
                                <div class="eib2bpro-Widget_Lastactivity_T5" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html(sprintf(esc_html__('%s (%s) has been removed from cart', 'eib2bpro'), $r['details']['name'], strip_tags(wc_price($r['details']['price'])))) ?>">-</div>
                            <?php } elseif (6 === $r['type']) {  ?>
                                <div class="eib2bpro-Widget_Lastactivity_T6" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e('Checkout', 'eib2bpro'); ?>"><?php echo esc_html(get_woocommerce_currency_symbol()) ?></div>
                            <?php } elseif (7 === $r['type']) {  ?>
                                <div class="eib2bpro-Widget_Lastactivity_T7" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e('Homepage', 'eib2bpro'); ?>"><span class="dashicons dashicons-admin-site"></span></div>
                            <?php } elseif (10 === $r['type']) {  ?>
                                <div class="eib2bpro-Widget_Lastactivity_T10" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e('Search:', 'eib2bpro'); ?> <?php echo esc_html(implode(", ", $r['details']['term'])) ?>"><span class="dashicons dashicons-search"></span></div>
                            <?php } elseif (17 === $r['type']) {  ?>
                                <div class="eib2bpro-Widget_Lastactivity_T2" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html($r['details']['term']) ?>"><?php echo esc_html($r['details']['term']) ?></div>
                            <?php } ?>
                            <?php if (1 < $r['details']['cnt']) { ?>
                                <span class="eib2bpro-Widget_Lastactivity_T0 badge badge-warning"><?php echo esc_html($r['details']['cnt']) ?></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="eib2bpro-Clear_Both">
                    </div>
                </div>
            </div>
            <div class="eib2bpro-Clear_Both"></div>
        </div>
    <?php } ?>
    <?php if (!isset($ajax)) { ?>
    </div>
<?php } ?>