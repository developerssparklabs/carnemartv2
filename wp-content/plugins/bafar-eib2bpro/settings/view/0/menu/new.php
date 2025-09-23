<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Menu', 'eib2bpro') ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(array('do' => 'new-menu')); ?>

<div class="eib2bpro-app-new-item-content eib2bpro-app-settings-menu">
    <div class="container-fluid">
        <div class="row">

            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php echo esc_html__('Open', 'eib2bpro'); ?></label>
                <?php eib2bpro_select('type', '', array(
                    '0' => esc_html__('In the admin panel', 'eib2bpro'),
                    '1' => esc_html__('In the new browser tab', 'eib2bpro'),
                    '2' => esc_html__('As a divider', 'eib2bpro')
                ), [], false, 'autofocus placeholder=""') ?>
            </div>


            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php echo esc_html__('Role', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('select', 'role', '', ['options' => $roles]) ?>
            </div>

            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php echo esc_html__('Title', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'title', '', ['attr' => 'autofocus placeholder=""']) ?>
            </div>

            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php echo esc_html__('Icon', 'eib2bpro'); ?></label>
                <button name="icon" class="eib2bpro-change-icon no-save" data-icon="fas fa-circle" data-iconset="fontawesome5"><?php esc_html_e('Change icon', 'eib2bpro'); ?></button>
            </div>



            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php echo esc_html__('URL', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'url', '', []) ?>
            </div>


        </div>

        <div class="row text-right pt-4">
            <div class="col-12 text-right pr-5">
                <?php eib2bpro_save() ?>
            </div>
        </div>

    </div>
</div>
</form>