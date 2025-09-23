<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
<?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Categories', 'eib2bpro'), 'description' => '', 'buttons' => '<a href="' . admin_url('edit-tags.php?taxonomy=product_cat&post_type=product') . '" class="btn btn-sm btn-danger eib2bpro-panel"> + &nbsp; ' . esc_html__('New category', 'eib2bpro') . ' &nbsp;</a>')); ?>
<?php echo eib2bpro_view('products', \EIB2BPRO\Products::app('mode'), 'nav') ?>


<div id="eib2bpro-categories" class="eib2bpro-GP">

    <?php if (0 === count($categories)) { ?>
        <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
            <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
            </div>
        </div>
    <?php } else { ?>
        <?php
        function eib2bpro_categories($categories, $all, $d = 0)
        {
            if (!function_exists('icl_object_id')) {
                $current_lang = 'en';
            } else {
                global $sitepress;
                $current_lang = $sitepress->get_current_language();
                $current_lang =  $current_lang ? $current_lang : $sitepress->get_default_language();
            }
        ?>
            <ol class="<?php echo "eib2bpro-Depth_" . esc_attr($d);
                        if (0 === $d) echo 'eib2bpro-Sortables'; ?>">
                <?php
                foreach ($categories as $category) { ?>
                    <li id='eib2bpro-Category_<?php echo intval($category['id']) ?>'>
                        <div class="eib2bpro-Item2">
                            <div class="row">
                                <div class="col-8 col-lg-11"><?php echo esc_html($category['name']) ?></div>
                                <div class="col-4 col-lg-1 text-right eib2bpro-RightMe">
                                    <div class="eib2bpro-Actions text-right float-right">
                                        <a href="<?php echo esc_url(admin_url('term.php?taxonomy=product_cat&post_type=product&tag_ID=' . intval($category['id']) . '&action=edit&eib2bpro_hide&lang=' . $current_lang)); ?>" class="eib2bpro-Button1 eib2bpro-MainButton eib2bpro-panel"><?php esc_html_e('View', 'eib2bpro'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        if (isset($all[$category['id']])) {
                            eib2bpro_categories($all[$category['id']], $all, ++$d);
                            --$d;
                        } ?>
                    </li>
                <?php } ?>
            </ol>
        <?php }

        eib2bpro_categories($categories[0], $categories);
        ?>
</div>
<?php } ?>