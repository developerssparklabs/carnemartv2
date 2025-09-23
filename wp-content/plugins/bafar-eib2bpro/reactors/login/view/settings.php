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
                <div class="col-12 alert alert-success" role="alert">
                    <span class="dashicons dashicons-smiley"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e('Saved', 'eib2bpro'); ?>
                </div>
            </div>
        <?php } ?>
        <form action="" method="POST">

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Position', 'eib2bpro'); ?></label>

                    <div class="form-check form-check-inline">
                        <input type="radio" value="left" name="position" id="rb1" class=" eib2bpro-radio-2" <?php eib2bpro_a((eib2bpro_clean($settings['position'], 'center') === 'left') ? ' checked' : '') ?> />
                        <label for="rb1" class="eib2bpro-radio-2"><?php esc_html_e('Left', 'eib2bpro'); ?></label>

                        <input type="radio" value="left2" name="position" id="rb2" class=" eib2bpro-radio-2" <?php eib2bpro_a((eib2bpro_clean($settings['position'], 'center') === 'left2') ? ' checked' : '') ?> />
                        <label for="rb2" class="eib2bpro-radio-2"><?php esc_html_e('Left 2', 'eib2bpro'); ?></label>

                        <input type="radio" value="center" name="position" id="rb3" class=" eib2bpro-radio-2" <?php eib2bpro_a((eib2bpro_clean($settings['position'], 'center') === 'center') ? ' checked' : '') ?> />
                        <label for="rb3" class="eib2bpro-radio-2"><?php esc_html_e('Center', 'eib2bpro'); ?></label>

                        <input type="radio" value="right" name="position" id="rb4" class=" eib2bpro-radio-2" <?php eib2bpro_a((eib2bpro_clean($settings['position'], 'center') === 'right') ? ' checked' : '') ?> />
                        <label for="rb4" class="eib2bpro-radio-2"><?php esc_html_e('Right', 'eib2bpro'); ?></label>

                        <input type="radio" value="right2" name="position" id="rb5" class=" eib2bpro-radio-2" <?php eib2bpro_a((eib2bpro_clean($settings['position'], 'center') === 'right2') ? ' checked' : '') ?> />
                        <label for="rb5" class="eib2bpro-radio-2"><?php esc_html_e('Right 2', 'eib2bpro'); ?></label>


                    </div>

                </div>
            </div>

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Logo', 'eib2bpro'); ?></label>
                    <div class="custom-img-container">
                        <?php

                        $upload_link = esc_url(get_upload_iframe_src('image', eib2bpro_clean($settings['logo'], '0')));
                        $eib2bpro_img_src = wp_get_attachment_image_src(eib2bpro_clean($settings['logo'], '0'), 'full');
                        $valid_img = is_array($eib2bpro_img_src);
                        ?>

                        <?php if ($valid_img) { ?>
                            <div class="eib2bpro-Settings_Logo eib2bpro-Settings_Logo_logo p-2">
                                <a href="javascript:;" data-pr="logo" class="upload-custom-img"><img src="<?php echo esc_url($eib2bpro_img_src[0]) ?>" <?php eib2bpro_style("max-height:120px") ?> /></a>
                            </div>
                            <a href="javascript:;" data-pr="logo" class="remove-image upload-custom-img-text"><?php esc_html_e('Remove image', 'eib2bpro'); ?></a>
                        <?php } else { ?>
                            <div class="eib2bpro-Settings_Logo text-center p-3" id="x">
                                <a href="javascript:;" data-pr="logo" class="upload-custom-img upload-custom-img-text"><?php esc_html_e('Select image', 'eib2bpro'); ?></a>
                            </div>
                        <?php } ?>
                        <input class="custom-img-logo" name="logo" type="hidden" value="<?php echo esc_attr(eib2bpro_clean($settings['logo'], '0')); ?>" />
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Background image', 'eib2bpro'); ?></label>
                    <div class="custom-img-container" data-i18n="<?php esc_html_e('Select image', 'eib2bpro'); ?>">
                        <?php

                        $upload_link = esc_url(get_upload_iframe_src('image', eib2bpro_clean($settings['background'], '0')));
                        $eib2bpro_img_src = wp_get_attachment_image_src(eib2bpro_clean($settings['background'], '0'), 'full');
                        $valid_img = is_array($eib2bpro_img_src);
                        ?>

                        <?php if ($valid_img) { ?>
                            <div class="eib2bpro-Settings_Logo eib2bpro-Settings_Logo_background  p-2">
                                <a href="javascript:;" data-pr="background" class="upload-custom-img"><img src="<?php echo esc_url($eib2bpro_img_src[0]) ?>" <?php eib2bpro_style("max-height:120px") ?> /></a>
                            </div>
                            <a href="javascript:;" data-pr="background" class="remove-image upload-custom-img-text"><?php esc_html_e('Remove image', 'eib2bpro'); ?></a>
                        <?php } else { ?>
                            <div class="eib2bpro-Settings_Logo text-center  p-3" id="y">
                                <a href="javascript:;" data-pr="background" class="upload-custom-img upload-custom-img-text"><?php esc_html_e('Select image', 'eib2bpro'); ?></a>
                            </div>
                        <?php } ?>
                        <input class="custom-img-background" name="background" type="hidden" value="<?php echo esc_attr(eib2bpro_clean($settings['background'], '0')); ?>" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Colors', 'eib2bpro'); ?></label>
                    <div class="d-block ml-3">
                        <?php eib2bpro_option_color(
                            array(
                                'name' => 'box',
                                'label' => esc_html__('Box background', 'eib2bpro'),
                                'css' => '',
                                'value' => eib2bpro_clean($settings['box'], '#fff')
                            )
                        );
                        ?>

                        <?php eib2bpro_option_color(
                            array(
                                'name' => 'text',
                                'label' => esc_html__('Text color', 'eib2bpro'),
                                'css' => '',
                                'value' => eib2bpro_clean($settings['text'], '#555555')
                            )
                        );
                        ?>


                        <?php eib2bpro_option_color(
                            array(
                                'name' => 'button',
                                'label' => esc_html__('Button background', 'eib2bpro'),
                                'css' => '',
                                'value' => eib2bpro_clean($settings['button'], '#555555')
                            )
                        );
                        ?>

                        <?php eib2bpro_option_color(
                            array(
                                'name' => 'buttontext',
                                'label' => esc_html__('Button text color', 'eib2bpro'),
                                'css' => '',
                                'value' => eib2bpro_clean($settings['buttontext'], '#fff')
                            )
                        );
                        ?>
                    </div>
                </div>
            </div>


            <div class="mt-4 text-center">
                <?php wp_nonce_field('eib2bpro_reactors'); ?>
                <button name="submit" class="btn btn-sm eib2bpro-Button1" type="submit"><?php esc_html_e('Save', 'eib2bpro'); ?></button>
            </div>
        </form>
    </div>