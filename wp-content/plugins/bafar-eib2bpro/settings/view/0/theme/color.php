<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-Settings_Colors text-center mt-4 pt-3 pb-5">
    <?php foreach ($colors as $color_key => $color_value) {
        if ('custom' !== $color_key) { ?>
            <a href="javascript:;" class="eib2bpro-Settings_Color<?php if ($colors_selected['key'] === $color_key) {
                                                                        echo " eib2bpro-Settings_Color_Selected";
                                                                    } ?>" <?php eib2bpro_style("background:#" . esc_attr($color_key)) ?> data-colors='<?php echo esc_attr(json_encode($color_value)); ?>'></a>
    <?php }
    } ?>
    <a href="javascript:;" class="eib2bpro-Settings_Color eib2bpro-Settings_Color_Own d-flex align-content-center<?php if ($colors_selected['key'] === 'custom') {
                                                                                                                        echo " eib2bpro-Settings_Color_Selected";
                                                                                                                    } ?>" data-colors='<?php echo esc_attr(json_encode($colors['custom'])); ?>'><span class="fas fa-cog"></span>
    </a>
</div>

<div class="eib2bpro-Settings_Color_Own_Div <?php eib2bpro_e('custom' === $colors_selected['key'] ? '' : 'd-none') ?> align-content-center">

    <?php
    $color_labels = array(
        'header-background' => esc_html__('Menu', 'eib2bpro'),
        'header-icons' => esc_html__('Icons', 'eib2bpro'),
        'header-hover' => esc_html__('Menu Hover', 'eib2bpro'),
        'primary-buttons' => esc_html__('Buttons', 'eib2bpro'),
    );
    ?>
    <?php foreach ($colors['custom'] as $color_key => $color_value) {
        if (isset($color_labels[$color_key])) { ?>
            <div class="eib2bpro-Settings_Color_Own_Options">
                <?php echo esc_html($color_labels[$color_key]); ?>
                <br>
                <input type="text" value="<?php echo esc_attr($color_value) ?>" class="eib2bpro-Settings_Color_Own-<?php echo esc_attr($color_key) ?> energyplus-color-field" data-default-color="<?php echo esc_attr($color_value) ?>" />
            </div>
    <?php }
    } ?>
    <button class="eib2bpro-Settings_Color_Own_Save eib2bpro-Button1 badge-black"><?php esc_html_e('Save custom colors', 'eib2bpro'); ?></button>
</div>