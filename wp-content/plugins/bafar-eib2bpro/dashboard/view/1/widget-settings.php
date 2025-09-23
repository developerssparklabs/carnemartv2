<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Settings', 'eib2bpro') ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(array('do' => 'save-widget-settings')); ?>

<input name="widget_id" value="<?php eib2bpro_a($id); ?>" type="hidden">

<div class="eib2bpro-app-new-item-content">
    <div class="container-fluid">
        <div class="row">
            <?php if ('options' === $settings['type']) { ?>
                <div class="eib2bpro-app-new-item-row col-12">
                    <label class="pb-3"><?php eib2bpro_e($settings['info']['title']); ?></label>
                    <?php if ('checkbox' === $settings['info']['type']) { ?>
                        <?php foreach ($settings['info']['values'] as $opt) { ?>
                            <div class="w-100 pb-3 d-flex align-items-center">
                                <?php eib2bpro_ui('onoff', $opt['id'], $opt['selected'], 'switch-sm mr-2'); ?><?php eib2bpro_e($opt['title']) ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="row text-right pt-4 w-100">
                <div class="col-12 text-right pr-5">
                    <?php eib2bpro_save() ?>
                </div>
            </div>
        </div>
    </div>
    </form>