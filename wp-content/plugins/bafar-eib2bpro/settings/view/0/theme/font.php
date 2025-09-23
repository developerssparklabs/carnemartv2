<?php defined('ABSPATH') || exit; ?>
<?php if (!eib2bpro_is_ajax()) { ?>
    <div class="text-center mt-5 mb-5 ">
        <input class="eib2bpro-app-font-search " type="text" placeholder="Search a font">
    </div>
    <input id="selected_font" name="font" value="<?php echo esc_attr(eib2bpro_option('font', 'Lato')) ?>" type="hidden">

    <div class="eib2bpro-app-settings-theme-font-container-disabled eib2bpro-app-settings-form">
    </div>
    <div class="eib2bpro-app-settings-theme-font-container eib2bpro-app-settings-form">
    <?php } ?>
    <div class="row m-3">
        <?php foreach ($fonts as $font) {
            $name = str_replace('+', ' ', strtok($font, ':'))
        ?>
            <!-- We dynamically preview fonts  -->
            <style>
                @import url("https://fonts.googleapis.com/css?family=<?php eib2bpro_a($font) ?>");
            </style>
            <div class="col-12 col-lg-4">
                <div class="card p-4 m-2 <?php if ($name === eib2bpro_option('font')) echo 'selected' ?>">
                    <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=eib2bpro&app=settings&do=font&page=' . ($page + 1))); ?>" class="eib2bpro-app-font-selection" data-id="<?php eib2bpro_a($name); ?>">
                        <div class="card-body" <?php eib2bpro_style('font-family: ' . esc_attr($name)) ?>">
                            <h5 class="card-title"><?php eib2bpro_e($name) ?></h5>
                        </div>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php if (!eib2bpro_is_ajax()) { ?>

    </div>
<?php } ?>