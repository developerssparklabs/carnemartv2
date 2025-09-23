<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
<?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Attributes', 'eib2bpro'), 'description' => '', 'buttons' => '<a href="' . admin_url('edit.php?post_type=product&page=product_attributes') . '" class="btn btn-sm btn-danger eib2bpro-panel eib2bpro-panel-refresh"> + &nbsp; ' . esc_html__('New attribute', 'eib2bpro') . ' &nbsp;</a>')); ?>
<?php echo eib2bpro_view('products', \EIB2BPRO\Products::app('mode'), 'nav') ?>

<div id="eib2bpro-attribute" class="eib2bpro-GP">
    <?php if (0 === count($attributes['product_attributes'])) { ?>
        <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
            <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
            </div>
        </div>
    <?php } else { ?>
        <div class="eib2bpro-List_M2 eib2bpro-Container eib2bpro-list-m2 ">

            <?php foreach ($attributes['product_attributes'] as $attribute) { ?>
                <div class="row align-middle align-items-center eib2bpro-Item bg-white mb-2" id='item_<?php echo esc_attr($attribute['id']) ?>'>
                    <div class="col-4 col-sm-2 eib2bpro-Col_2 align-middle text-uppercase">
                        <?php echo esc_attr($attribute['name']) ?>
                    </div>
                    <div class="col-8 col-sm-6 eib2bpro-Col_Terms align-middle"><?php $terms = get_terms(wc_attribute_taxonomy_name_by_id($attribute['id']), array('hide_empty' => false, 'orderby' => 'id', 'order' => 'ASC'));
                                                                            if (0 < count($terms)) {
                                                                                foreach ($terms as $term) { ?>
                                <span class="badge badge-pill badge-black text-uppercase"><?php echo esc_attr($term->name) ?></span>
                        <?php }
                                                                            } ?>
                    </div>
                    <div class="col eib2bpro-Col_Actions eib2bpro-Col_5x  eib2bpro-Col_3 align-middle eib2bpro-Actions2">
                        <ul class="float-right">
                            <li>
                                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . $attribute['slug'] . '&post_type=product')); ?>" class="eib2bpro-Button1 eib2bpro-MainButton eib2bpro-panel"><?php esc_html_e('Configure', 'eib2bpro'); ?></a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=product&page=product_attributes&edit=' . $attribute['id'])); ?>" class="eib2bpro-Button1  eib2bpro-panel"><?php esc_html_e('Edit', 'eib2bpro'); ?></a>
                            </li>
                            <li>
                                <a href="<?php echo eib2bpro_secure_url('products', $attribute['id'], array('action' => 'view', 'id' => $attribute['id'])); ?>" data-do='delete-attribute' data-nonce='<?php echo wp_create_nonce('eib2bpro-products--attr-delete-' . $attribute['id']) ?>' data-id='<?php echo esc_attr($attribute['id']) ?>' class="eib2bpro-Button1  eib2bpro-AjaxButton" data-confirm="<?php esc_attr_e("Are you sure to delete?", "eib2bpro") ?>"><?php esc_html_e('Delete', 'eib2bpro'); ?></a>
                            </li>
                        </ul>
                    </div>
                    <button class="eib2bpro-Mobile_Actions"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>