<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<div class="eib2bpro-todos eib2bpro-todos-mini">
    <?php $todos = \EIB2BPRO\Core\Todo::all(0, ['limit' => 99999]); ?>
    <ul class="eib2bpro-todo-not-completed eib2bpro-todo-sortable">
        <?php foreach ($todos as $todo) {
            \EIB2BPRO\Core\Todo::get($todo);
        }
        ?>
        <li data-id="0" class="d-flex pt-2">
            <div class="eib2bpro-todo-checked float-left pl-4 ml-1 pr-2">
                <input class="eib2bpro-todo-input-check-0 d-none" type="checkbox" value="0" disabled>
            </div>
            <div class="eib2bpro-todo-content flex-fill float-left pr-3">
                <button class="eib2bpro-todo-add"><?php esc_html_e('Add a todo', 'eib2bpro'); ?></button>
                <div class="eib2bpro-todo-input eib2bpro-todo-new eib2bpro-todo-editing eib2bpro-autofocus eib2bpro-hidden w-100 pl-3" contenteditable="true"></div>
            </div>
            <div class="clearfix"></div>
        </li>
    </ul>

    <?php $todos = \EIB2BPRO\Core\Todo::all(1, ['limit' => $limit]); ?>
    <ul class="eib2bpro-todo-completed">
        <?php foreach ($todos as $todo) {
            \EIB2BPRO\Core\Todo::get($todo);
        }
        ?>
    </ul>
    <ul class="mt-0">
        <?php if ($limit <= count($todos)) { ?>
            <li data-id=" -1" class="ml-4 mt-3">
                <a href="javascript:;" class="text-muted eib2bpro-todo-show-more"><?php esc_html_e('Show more', 'eib2bpro'); ?></a>
            </li>
        <?php } ?>
    </ul>
</div>