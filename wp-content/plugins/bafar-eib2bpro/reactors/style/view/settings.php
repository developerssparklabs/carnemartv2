<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php eib2bpro_e($reactor['title']) ?></h5>
            <a href="<?php echo eib2bpro_secure_url('reactors', esc_attr($reactor['id']), array('action' => 'activate', 'do' => 'deactivate', 'id' => $reactor['id']))  ?>" class="text-danger font-weight-normal mt-2"><?php esc_html_e('Deactivate now', 'eib2bpro'); ?></a>
        </div>
    </div>
</div>

<div class="eib2bpro-app-new-item-content">
    <div class="container-fluid">
        <?php if (1 === $saved) { ?>
            <div class="row">
                <div class="col-12 alert alert-success text-center" role="alert">
                    <span class="dashicons dashicons-smiley"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e('Saved', 'eib2bpro'); ?>
                </div>
            </div>
        <?php } ?>
        <form action="" method="POST">

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Screens', 'eib2bpro'); ?></label>
                    <div class="m-0">
                        <?php

                        $enabled = eib2bpro_option('reactors-style-screens', array_keys(\EIB2BPRO\Reactors\style\style::all_screens()));

                        foreach ($screens as $key => $value) { ?>
                            <div class="form-check w-100 pb-1 ml-0 pl-0">
                                <input type="checkbox" name="reactors-style-screens[]" class="switch_1 switch-sm form-control" value='<?php echo esc_attr($key) ?>' <?php if (in_array($key, $enabled)) {
                                                                                                                                                                        echo 'checked';
                                                                                                                                                                    } ?> /> &nbsp; <?php echo esc_html($value) ?> <br>
                            </div>
                        <?php } ?>
                    </div>
                    <br>
                    <div class="text-muted"><?php esc_html_e('If you have compatibility problems, please turn off style for that page', 'eib2bpro'); ?></div>
                </div>
            </div>

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Options', 'eib2bpro'); ?></label>
                    <div class="p-0">
                        <div>
                            <input type="checkbox" value="1" class="switch_1 switch-sm" name="reactors-style-shadow" <?php if (1 === eib2bpro_option('reactors-style-shadow', 1)) {
                                                                                                                            echo " checked";
                                                                                                                        } ?>>
                            &nbsp; <?php esc_html_e('Add shadows to tables', 'eib2bpro'); ?>
                        </div>

                        <div class="pt-1">
                            <input type="checkbox" value="1" class="switch_1 switch-sm" name="reactors-style-bg" <?php if (1 === eib2bpro_option('reactors-style-bg', 1)) {
                                                                                                                        echo " checked";
                                                                                                                    } ?>>
                            &nbsp; <?php esc_html_e('Remove table row background colors', 'eib2bpro'); ?>
                        </div>

                        <div class="pt-1">
                            <input type="checkbox" value="1" class="switch_1 switch-sm" name="reactors-style-click" <?php if (1 === eib2bpro_option('reactors-style-click', 0)) {
                                                                                                                        echo " checked";
                                                                                                                    } ?>>
                            &nbsp; <?php esc_html_e('Show item actions when click like E+ (EXPERIMENTAL)', 'eib2bpro'); ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <?php wp_nonce_field('eib2bpro_reactors'); ?>
                <button name="submit" class="btn btn-sm eib2bpro-Button1" type="submit"><?php esc_html_e('Save', 'eib2bpro'); ?></button>
            </div>
        </form>
    </div>
</div>