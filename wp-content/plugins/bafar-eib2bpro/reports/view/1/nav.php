<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Reports_Mode_1">
    <div class="row eib2bpro-gp">
        <ul>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('action') ?>" href="<?php echo eib2bpro_admin('reports', array('report' => 'overview'));  ?>"><?php esc_html_e('Overview', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'revenue') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'revenue'));  ?>"><?php esc_html_e('Revenue', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'orders') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'orders'));  ?>"><?php esc_html_e('Orders', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'products') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'products'));  ?>"><?php esc_html_e('Products', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'categories') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'categories'));  ?>"><?php esc_html_e('Categories', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'coupons') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'coupons'));  ?>"><?php esc_html_e('Coupons', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'taxes') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'taxes'));  ?>"><?php esc_html_e('Taxes', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'downloads') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'downloads'));  ?>"><?php esc_html_e('Downloads', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'stock') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'stock'));  ?>"><?php esc_html_e('Stock', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('report', 'customers') ?>" href="<?php echo eib2bpro_admin('reports', array('action' => 'woocommerce', 'report' => 'customers'));  ?>"><?php esc_html_e('Customers', 'eib2bpro'); ?></a></li>
            <?php if (eib2bpro_get('action', '') !== 'woocommerce') { ?>
                <li class="eib2bpro-Li_Search">
                    <a href="<?php echo eib2bpro_admin('reports', array('graph' => 2));  ?>" class="eib2bpro-Button1 eib2bpro-Graph_Button eib2bpro-Search_Button<?php if ("2" === eib2bpro_option('reports-graph', "2")) {
                                                                                                                                                                        echo ' eib2bpro-Selected';
                                                                                                                                                                    } ?>"><span class="dashicons dashicons-chart-area"></span></span></a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>