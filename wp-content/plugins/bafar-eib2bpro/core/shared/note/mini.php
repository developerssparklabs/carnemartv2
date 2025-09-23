<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<div class="eib2bpro-notes eib2bpro-notes-mini">
    <div class="eib2bpro-List_M1 eib2bpro-Container">
        <div class="eib2bpro-offers-container eib2bpro-list-container">
            <ol class="eib2bpro-notes-sortable">
                <?php foreach ($all as $item) {
                    $content = $item['content'];
                    $title = strip_tags(explode("<div>", $content)[0]); ?>
                    <li id="eib2bpro_note_<?php eib2bpro_a($item['id']) ?>" data-id="<?php eib2bpro_a($item['id']) ?>" class="mb-0">
                        <div class="btnA eib2bpro-note eib2bpro-no-shadow eib2bpro-note-<?php eib2bpro_a(isset($item['color']) ? $item['color'] : '#ccc') ?> <?php eib2bpro_a(0 === intval($item['collapsed']) ? ' collapsed' : '') ?> pb-1 p-0 " data-type="<?php echo esc_attr($item['resource_type']) ?>" data-id="<?php echo esc_attr($item['id']) ?>" id="item_<?php echo esc_attr($item['id']) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($item['id']) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($item['id']) ?>">
                            <div class="liste d-flex align-items-center overflow-hidden">
                                <div class="col-12">
                                    <div class="eib2bpro-note-title<?php eib2bpro_a(1 === intval($item['collapsed']) ? ' hidden' : '') ?>">
                                        <?php eib2bpro_e(stripslashes_deep($title)) ?>
                                        <div class="float-right">
                                            <i class="eib2bpro-os-move eib2bpro-icon-move"></i>
                                        </div>
                                    </div>
                                    <div class="eib2bpro-note-buttons <?php eib2bpro_a(0 === intval($item['collapsed']) ? ' hidden' : '') ?>">
                                        <div class="eib2bpro-note-button float-left">
                                            <button class="eib2bpro-note-b">B</button>
                                            <button class="eib2bpro-note-i">I</button>
                                        </div>
                                        <div class="float-right">
                                            <?php eib2bpro_ui('ajax_button', 'note-delete', '', ['id' => $item['id'], 'do' => 'delete-note', 'title' => '', 'html' => '<i class="eib2bpro-note-delete ri-delete-bin-line"></i>', 'confirm' => esc_html__('Are you sure to delete?', 'eib2bpro'), 'class' => 'eib2bpro-StopPropagation']); ?>
                                        </div>
                                        <div class="eib2bpro-note-colors float-right hidden pr-2">
                                            <?php
                                            $colors = ['000000', 'F5CAD9'];
                                            foreach ($colors as $color) { ?>
                                                <div class="eib2bpro-note-color <?php eib2bpro_a($color === $item['color'] ? 'eib2bpro-selected' : '')  ?> eib2bpro-note-<?php eib2bpro_a($color) ?>" data-color="<?php eib2bpro_a($color) ?>"></div>
                                            <?php } ?>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="collapse <?php eib2bpro_a(1 === intval($item['collapsed']) ? ' show' : '') ?> eib2bpro-note-text eib2bpro-StopPropagation" id="item_d_<?php echo esc_attr($item['id']) ?>">
                                <div class="eib2bpro-note-textarea eib2bpro-StopPropagation eib2bpro-note-empty-<?php eib2bpro_a(intval(empty(trim($item['content'])))) ?>" data-type=" <?php echo esc_attr($item['resource_type']) ?>" data-id="<?php echo esc_attr($item['id']) ?>" <?php echo eib2bpro_r(($item['created_by'] === get_current_user_id() || \EIB2BPRO\Admin::is_admin()) ? " contenteditable='true'" : ''); ?>><?php if (empty(trim($item['content']))) {
                                                                                                                                                                                                                                                                                                                                                                                                    esc_html_e('Add a new note...', 'eib2bpro');
                                                                                                                                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                                                                                                                                    echo wp_kses_post(stripslashes($item['content']));
                                                                                                                                                                                                                                                                                                                                                                                                } ?></div>
                            </div>

                        </div>
                    </li>
                <?php } ?>
            </ol>
        </div>
    </div>
</div>