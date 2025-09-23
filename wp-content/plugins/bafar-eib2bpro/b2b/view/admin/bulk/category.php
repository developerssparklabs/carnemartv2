<?php defined('ABSPATH') || exit; ?>

<?php eib2bpro_form(['do' => 'edit-bulk-category']); ?>
<table class="table table-hover eib2bpro-b2b-bulk-editor">
    <thead>
        <tr>
            <th scope="col" rowspan="2" class="align-middle">
                <div class="pl-4"><?php esc_html_e('Category Visibility', 'eib2bpro'); ?></div>
            </th>
            <th scope="col" class="eib2bpro_group">
                <span>
                    <?php esc_html_e('Guests', 'eib2bpro'); ?>
                </span>
            </th>
            <th scope="col" class="eib2bpro_group">
                <span>
                    <?php esc_html_e('B2C', 'eib2bpro'); ?>
                </span>
            </th>
            <?php
            $groups = \EIB2BPRO\B2b\Admin\Groups::get();
            foreach ($groups as $group) { ?>
                <th scope="col" class="eib2bpro_group">
                    <span>
                        <?php eib2bpro_e(get_the_title($group->ID)) ?>
                    </span>
                </th>
            <?php } ?>

        </tr>
        <tr>
            <th scope="col" class="eib2bpro_group">
                <input type="checkbox" class="eib2bpro-b2b-bulk-category-selectall" data-group="b2c">
            </th>
            <th scope="col" class="eib2bpro_group">
                <input type="checkbox" class="eib2bpro-b2b-bulk-category-selectall" data-group="guests">
            </th>
            <?php
            $groups = \EIB2BPRO\B2b\Admin\Groups::get();
            foreach ($groups as $group) { ?>
                <th scope="col" class="eib2bpro_group">
                    <input type="checkbox" class="eib2bpro-b2b-bulk-category-selectall" data-group="<?php eib2bpro_a($group->ID) ?>">
                </th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        EIB2BPRO\Admin::wc_engine();
        $_categories = \WC()->api->WC_API_Products->get_product_categories();
        $categories = eib2bpro_group_by('parent', $_categories['product_categories']);

        if (!function_exists('eib2bpro_bulk_category_list')) {
            function eib2bpro_bulk_category_list($categories, $all, $d = 0)
            {
                foreach ($categories as $category) { ?>
                    <tr class="eib2bpro-parent-<?php eib2bpro_a($category['parent']) ?>">
                        <td class="pl-3">
                            <?php
                            echo str_repeat('&nbsp;&nbsp;', $d);
                            echo '<a href="javascript:;" class="eib2bpro-b2b-bulk-category-sub" data-toggle="true" data-sub="' . esc_attr($category['id']) . '">';
                            if (isset($all[$category['id']])) {
                                echo '+';
                            }
                            echo '</a>';
                            ?>
                            <a href="javascript:;" class="eib2bpro-b2b-bulk-category-sub w-75" data-sub="<?php eib2bpro_a($category['id']) ?>">
                                <?php eib2bpro_e($category['name']) ?>
                            </a>
                        </td>
                        <td class="eib2bpro_group_check  eib2bpro-group-b2c">
                            <input type="checkbox" name="new_<?php eib2bpro_a($category['id'] . '_guests') ?>" value="1" <?php eib2bpro_a("0" !== (string)get_term_meta($category['id'], 'eib2bpro_group_guests', true) ? 'checked' : '') ?>>
                        </td>
                        <td class="eib2bpro_group_check eib2bpro-group-guests">
                            <input type="checkbox" name="new_<?php eib2bpro_a($category['id'] . '_b2c') ?>" value="1" <?php eib2bpro_a("0" !== (string)get_term_meta($category['id'], 'eib2bpro_group_b2c', true) ? 'checked' : '') ?>>
                        </td>
                        <?php
                        $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                        foreach ($groups as $group) { ?>
                            <td class="eib2bpro_group_check  eib2bpro-group-<?php eib2bpro_a($group->ID) ?>">
                                <input type="checkbox" name="new_<?php eib2bpro_a($category['id'] . '_' . $group->ID) ?>" value="1" <?php eib2bpro_a("0" !== get_term_meta($category['id'], 'eib2bpro_group_' . $group->ID, true) ? 'checked' : '') ?>>
                            </td>
                        <?php } ?>
                    </tr>
        <?php

                    if (isset($all[$category['id']])) {
                        eib2bpro_bulk_category_list($all[$category['id']], $all, ++$d);
                        --$d;
                    }
                }
            }
        }
        eib2bpro_bulk_category_list($categories[0], $categories);
        ?>
    </tbody>
</table>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <a href="<?php eib2bpro_a(admin_url('edit-tags.php?taxonomy=product_cat&post_type=product')) ?>" class="ml-1 text-danger eib2bpro-panel2"> + &nbsp; <?php esc_html_e('New category', 'eib2bpro') ?> &nbsp;</a>
        </div>
        <div class="text-right pr-5 col-6">
            <?php eib2bpro_save('') ?>
        </div>
    </div>
</div>
</form>