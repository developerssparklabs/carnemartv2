<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-app-settings-menu">
    <ol class="eib2bpro_Sortable eib2bpro-app-settings-sortable">
        <?php
        foreach ($widgets as $my_widget_id => $my_widget) {
            if ($my_widget['active'] === 0) {
                continue;
            }
            $widget = $allWidgets[$my_widget_id]; ?>
            <li class="table-item w-100">
                <div class="p-30 form-check">
                    <h6>
                        <i class="eib2bpro-os-move eib2bpro-icon-move pl-2 pr-2"></i>
                        <?php eib2bpro_ui('onoff_ajax', 'top-widget-items[' . $widget['id'] . ']', (isset($widgets[$widget['id']]['active'])) ? $widgets[$widget['id']]['active'] : 0, ['app' => 'settings', 'do' => 'top-widget-onoff', 'id' => $widget['id'], 'class' => 'mr-2 switch-sm',]) ?>
                        <?php echo esc_html($widget['title']) ?>
                    </h6>
                </div>
            </li>
        <?php unset($allWidgets[$widget['id']]);
        } ?>

        <?php
        foreach ($allWidgets as $widget) { ?>
            <li class="table-item w-100">
                <div class="p-30 form-check">
                    <h6>
                        <i class="eib2bpro-os-move eib2bpro-icon-move pl-2 pr-2"></i>
                        <?php eib2bpro_ui('onoff_ajax', 'top-widget-items[' . $widget['id'] . ']', (isset($widgets[$widget['id']]['active'])) ? $widgets[$widget['id']]['active'] : 0, ['app' => 'settings', 'do' => 'top-widget-onoff', 'id' => $widget['id'], 'class' => 'mr-2 switch-sm',]) ?>
                        <?php echo esc_html($widget['title']) ?>
                    </h6>
                </div>
            </li>
        <?php } ?>
    </ol>
</div>