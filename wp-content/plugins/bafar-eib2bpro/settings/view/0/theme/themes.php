<?php defined('ABSPATH') || exit; ?>
<div class="m-0">
    <div class="row align-items-center">
        <input id="selected_theme" name="theme" value="<?php eib2bpro_a(eib2bpro_option('theme', 'one')) ?>" type="hidden">
        <?php foreach ($options as $theme_id => $theme) { ?>
            <div class="theme-item col-md-6 p-0 h-md-100 <?php if ($theme_id === $selected) {
                                                                echo 'selected';
                                                            } ?>">
                <div class="pt-5 text-center">
                    <a href="javascript:;" class="theme-select-a" data-id="<?php eib2bpro_a($theme_id); ?>">

                        <h1 class="pb-4">
                            <?php if ($theme_id === $selected) { ?>
                                <span class="mr-2 theme-selected ri-checkbox-circle-fill"></span>
                                <?php } ?><?php eib2bpro_e($theme['title']) ?>
                        </h1>
                        <div class="h1-line mb-4"></div>
                        <img class="theme-image pl-1" src="<?php eib2bpro_a(EIB2BPRO_PUBLIC . 'core/public/img/theme-' . $theme_id); ?>.jpg">
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        "use strict";
        $(document).on('click', '.theme-select-a', function() {
            $('#selected_theme').val($(this).data('id'));
            $('.app-save-button').trigger('click');
        })
    });
</script>