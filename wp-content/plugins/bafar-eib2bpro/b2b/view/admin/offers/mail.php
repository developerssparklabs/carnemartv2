<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Offers', 'eib2bpro') ?> <?php eib2bpro_ui('wpml_selector'); ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(['do' => 'mail-offer', 'id' => eib2bpro_get('id', 0)]); ?>

<input name="id" value="<?php echo eib2bpro_clean($id, '0') ?>" type="hidden">

<div class="eib2bpro-app-new-item-content">
    <div class="container-fluid">

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12 text-center font-16">
                <?php
                $targered = 0;
                $groups = wp_parse_id_list(get_post_meta($id, 'eib2bpro_groups', true));
                foreach ($groups as $group) {
                    if (0 < intval($group)) {
                        $targered += \EIB2BPRO\B2b\Admin\Groups::count_users(intval($group));
                    }
                }
                $targered += count(wp_parse_list(get_post_meta($id, 'eib2bpro_users', true)));
                ?>

                <?php eib2bpro_e(sprintf(esc_html__('This mail will be sent to %d customers, are you sure?', 'eib2bpro'), $targered)); ?>
            </div>
        </div>
    </div>

</div>

<div class="row text-right pt-4">
    <div class="col-12 text-center pr-5">
        <?php eib2bpro_save(esc_html__('Send now', 'eib2bpro')) ?>
    </div>
</div>
</div>
</div>

</form>