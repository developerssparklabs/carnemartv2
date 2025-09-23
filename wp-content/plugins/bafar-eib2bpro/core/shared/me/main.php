<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<div>
    <div class="p-0 position-relative eib2bpro-me-avatar">
        <?php if (get_user_meta(get_current_user_id(), 'eib2bpro_avatar', true)) {
            echo eib2bpro_ui('avatar', get_current_user_id(), ['class' => 'eib2bpro-my-avatar', 'w' => 70, 'h' => 70, 'toggle' => 'none']);
        } else {  ?>
            <div class="eib2bpro-settings-about"></div>
        <?php } ?>
        <div class="eib2bpro-me-settings">
            <a href="<?php eib2bpro_e(eib2bpro_admin("core", ["action" => "me", "go" => "settings"])) ?>"><?php esc_html_e('Settings', 'eib2bpro'); ?></a>
        </div>
    </div>
    <div id="carouselControls" class="eib2bpro-me-tabs carousel slide w-100" data-keyboard="false">
        <ul class="carousel-indicators carousel-groups">
            <li data-target="#carouselControls" data-slide-to="0" class="active"><?php esc_html_e('To-dos', 'eib2bpro'); ?></li>
            <li data-target="#carouselControls" data-slide-to="1" class=""><?php esc_html_e('Notes', 'eib2bpro'); ?></li>
        </ul>
        <div class="carousel-inner">
            <div class="carousel-item active" data-id="0">
                <div class="eib2bpro-me-todo-container" data-limit="5">
                    <?php \EIB2BPRO\Core\Todo::render(); ?>
                </div>
            </div>
            <div class="carousel-item" data-id="1">
                <?php \EIB2BPRO\Core\Note::render('private'); ?>
            </div>
        </div>
    </div>
</div>