<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2 eib2bpro-Scroll<?php if (eib2bpro_get('orderby')) {
                                                                            echo " mb-0";
                                                                        } ?>">
    <div class="row eib2bpro-gp eib2bpro-GP">
        <ul>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('action') ?>" href="<?php echo eib2bpro_admin('products', array()); ?>"><?php esc_html_e('Products', 'eib2bpro'); ?></a></li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('action', 'categories') ?>" href="<?php echo eib2bpro_admin('products', array('action' => 'categories')); ?>"><?php esc_html_e('Categories', 'eib2bpro'); ?></a>
            </li>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('action', 'attributes') ?>" href="<?php echo eib2bpro_admin('products', array('action' => 'attributes')); ?>"><?php esc_html_e('Attributes', 'eib2bpro'); ?></a>
            </li>

            <?php do_action('eib2bpro_submenu', 'products'); ?>

            <?php if ('' === eib2bpro_get('action')) : ?>
                <li class="eib2bpro-Li_Search">
                    <?php if ('-1' !== eib2bpro_get('category') && '-2' !== eib2bpro_get('category')) { ?>
                        <div class="btn-group">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="dashicons dashicons-sort"></span></a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?php echo eib2bpro_thead_sort('title') ?>"><?php esc_html_e('Name', 'eib2bpro'); ?></a>
                                <a class="dropdown-item" href="<?php echo eib2bpro_thead_sort('meta__price') ?>"><?php esc_html_e('Price', 'eib2bpro'); ?></a>
                                <a class="dropdown-item" href="<?php echo eib2bpro_thead_sort('meta__sku') ?>"><?php esc_html_e('SKU', 'eib2bpro'); ?></a>
                                <a class="dropdown-item" href="<?php echo eib2bpro_thead_sort('date') ?>"><?php esc_html_e('Date', 'eib2bpro'); ?></a>
                                <a class="dropdown-item" href="<?php echo eib2bpro_thead_sort('menu_order') ?>"><?php esc_html_e('Position', 'eib2bpro'); ?></a>
                            </div>
                        </div>
                    <?php } ?>
                    <a href="javascript:;" class="eib2bpro-Button1 eib2bpro-Search_Button"><?php esc_html_e('Search', 'eib2bpro'); ?></a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

</div>
<?php if (eib2bpro_get('orderby')) { ?>
    <div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2 eib2bpro-Scroll eib2bpro-OrderBy">
        <div class="row eib2bpro-gp eib2bpro-GP">
            <ul>
                <li><?php esc_html_e('ORDER BY', 'eib2bpro'); ?></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp; &mdash;</li>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('orderby', 'title') ?>" href="<?php echo eib2bpro_thead_sort('title') ?>"><?php esc_html_e('Name', 'eib2bpro'); ?></a></li>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('orderby', 'meta__price') ?>" href="<?php echo eib2bpro_thead_sort('meta__price') ?>"><?php esc_html_e('Price', 'eib2bpro'); ?></a></li>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('orderby', 'meta__sku') ?>" href="<?php echo eib2bpro_thead_sort('meta__sku') ?>"><?php esc_html_e('SKU', 'eib2bpro'); ?></a></li>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('orderby', 'date') ?>" href="<?php echo eib2bpro_thead_sort('date') ?>"><?php esc_html_e('Date', 'eib2bpro'); ?></a></li>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('orderby', 'menu_order') ?>" href="<?php echo eib2bpro_thead_sort('menu_order') ?>"><?php esc_html_e('Position', 'eib2bpro'); ?></a></li>
            </ul>
        </div>
    </div>
<?php } ?>