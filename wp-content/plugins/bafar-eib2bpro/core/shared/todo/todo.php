<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<li id="eib2bpro-me-todo-<?php eib2bpro_a($item['id']) ?>" data-id="<?php eib2bpro_a($item['id']) ?>" data-status="<?php eib2bpro_a($item['status']) ?>" class="d-flex clearfix">
    <div class="eib2bpro-todo-checked float-left pl-4 pr-2">
        <input class="eib2bpro-todo-input-check" type="checkbox" value="<?php eib2bpro_a($item['id']) ?>" <?php checked(1, $item['checked']) ?>>
    </div>
    <div class="eib2bpro-todo-content flex-fill float-left pr-4">
        <div type="text" class="eib2bpro-todo-input" contenteditable="<?php eib2bpro_a(1 === intval($item['checked']) ? 'false' : 'true') ?>">
            <?php eib2bpro_e(wp_kses_post(stripslashes_deep($item['content']))); ?>
        </div>
    </div>
    <div class="eib2bpro-todo-actions float-right pr-2">
        <i class="eib2bpro-os-delete eib2bpro-todo-delete ri-delete-bin-line"></i>
        <i class="eib2bpro-os-move eib2bpro-icon-move"></i>
    </div>
</li>