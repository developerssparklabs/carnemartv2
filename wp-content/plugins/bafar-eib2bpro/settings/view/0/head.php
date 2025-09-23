<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-app-settings-sub-title">
    <div class="float-left w-50">
        <h4>
            <i class="<?php eib2bpro_a($icon); ?>"></i>
            <?php eib2bpro_e($title); ?>
            <br>
            <div class="text-muted"><?php eib2bpro_e($description); ?></div>
        </h4>
    </div>
    <div class="float-right text-right w-50">
        <?php foreach ($buttons as $button) { ?>
            <a class="<?php eib2bpro_a($button['class']); ?> btn btn-sm btn-danger eib2bpro-rounded" href="<?php echo esc_url($button['href']); ?>" data-width="<?php eib2bpro_a($button['width']); ?>">
                + <?php eib2bpro_e($button['text']); ?>
            </a>
        <?php } ?>
    </div>
</div>
<div class="clear-both"></div>