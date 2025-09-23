<?php defined('ABSPATH') || exit; ?>
<input name="tab" type="hidden" value="<?php echo eib2bpro_get('tab', 'default') ?>">
<div class="m-0 eib2bpro-app-settings-form">
    <div class="row align-items-center">
        <?php foreach ($options as $item) { ?>
            <div class="table-item <?php
                                    if (isset($item['if'])) {
                                        if (!in_array(eib2bpro_option($item['if'][0]), $item['if'][1])) {
                                            echo ' hidden ';
                                        }
                                    } ?> col-<?php eib2bpro_a($item['col']) ?> table_row_<?php eib2bpro_a($item['id']) ?> <?php eib2bpro_a($item['class']) ?>">
                <div class="p-30<?php if (empty($item['title'])) { ?> pt-2<?php } ?>">
                    <?php if (!empty($item['title'])) { ?>
                        <h6 class=" pb-2"><?php eib2bpro_e($item['title']) ?></h6>
                    <?php } ?>
                    <?php
                    $value = eib2bpro_option($item['id'], $item['default']);
                    ?>
                    <?php if ('input' === $item['type']) { ?>
                        <input name="<?php eib2bpro_a($item['id']) ?>" value="<?php eib2bpro_a($value) ?>" class="form-control <?php eib2bpro_a($item['class']) ?>">
                    <?php } ?>

                    <?php if ('onoff' === $item['type']) { ?>
                        <div class="d-flex align-items-end">
                            <input type="hidden" name="' . esc_attr($name) . '" value="0">
                            <input type="checkbox" name="<?php eib2bpro_a($item['id']) ?>[]" class="btn pr-5 switch_1 switch-<?php eib2bpro_a($item['size']) ?> mt-1 eib2bpro-StopPropagation <?php if (in_array((string)$opt_key, $value)) {
                                                                                                                                                                                echo ' checked';
                                                                                                                                                                            } ?> eib2bpro-app-button-opt-click" value="<?php eib2bpro_a($value) ?>" data-item="<?php eib2bpro_a($item['id']) ?>" data-rel="<?php eib2bpro_a($opt_key) ?>" data-type="<?php eib2bpro_a($item['type']) ?>" <?php if (1 === intval($value)) {
                                                                                                                                                                                                                                                                                                                                                                        echo ' checked';
                                                                                                                                                                                                                                                                                                                                                                    } ?>>
                            <span class="eib2bpro-checkbox-label pl-2"><?php eib2bpro_e($item['label']) ?></span>
                        </div>
                    <?php } ?>

                    <?php if ('onoff_single' === $item['type']) { ?>
                        <div class="d-flex align-items-center">
                            <input type="hidden" name="<?php eib2bpro_a($item['id']) ?>" value="0">
                            <input type="checkbox" name="<?php eib2bpro_a($item['id']) ?>" class="btn pr-5 switch_1 switch-<?php eib2bpro_a($item['size']) ?> eib2bpro-StopPropagation eib2bpro-app-button-opt-click" value="1" data-item="<?php eib2bpro_a($item['id']) ?>" data-rel="<?php eib2bpro_a($item['id']) ?>" data-type="<?php eib2bpro_a($item['type']) ?>" <?php if (1 === intval($value)) {
                                                                                                                                                                                                                                                                                                                                    echo ' checked';
                                                                                                                                                                                                                                                                                                                                } ?>>
                            <span class="eib2bpro-checkbox-label pl-2"><?php eib2bpro_e($item['label']) ?></span>
                        </div>
                    <?php } ?>


                    <?php if ('range' === $item['type']) { ?>
                        <input name="<?php eib2bpro_a($item['id']) ?>" value="<?php eib2bpro_a($value) ?>" type="range" min="<?php eib2bpro_a($item['opt']['min']) ?>" max="<?php eib2bpro_a($item['opt']['max']) ?>" step="<?php eib2bpro_a($item['opt']['step']) ?>" class="form-control w-50 <?php eib2bpro_a($item['class']) ?>">
                    <?php } ?>

                    <?php if ('select' === $item['type']) { ?>
                        <select name="<?php eib2bpro_a($item['id']) ?>" class="form-control w-25 <?php eib2bpro_a($item['class']) ?>">
                            <?php foreach ($item['opt'] as $opt_key => $opt_value) { ?>
                                <option value="<?php eib2bpro_a($opt_key) ?>" <?php if ($value === $opt_key) {
                                                                            echo " selected";
                                                                        } ?>><?php eib2bpro_e($opt_value) ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>

                    <?php if ('select2_optgroup' === $item['type']) { ?>
                        <select name="<?php eib2bpro_a($item['id']) ?>" class="form-control select2c w-25 <?php eib2bpro_a($item['class']) ?>">
                            <?php foreach ($item['opt'] as $group_key => $group) { ?>
                                <optgroup label="<?php eib2bpro_a($group_key) ?>">
                                    <?php foreach ($group as $opt_key => $opt_value) { ?>
                                        <option value="<?php eib2bpro_a($opt_value) ?>" <?php if ($value === $opt_value) {
                                                                                        echo " selected";
                                                                                    } ?>><?php eib2bpro_e($opt_value) ?></option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                        </select>
                    <?php } ?>


                    <?php if ('select_group' === $item['type']) { ?>
                        <input type="hidden" name="<?php eib2bpro_a($item['id']) ?>" class="opt-<?php eib2bpro_a($item['id']) ?>" value="<?php eib2bpro_a($value) ?>">
                        <div class="btn-group" role="group">
                            <?php foreach ($item['opt'] as $opt_key => $opt_value) { ?>
                                <a type="button" class="btn <?php if ((string)$value === (string)$opt_key) {
                                                                echo ' btn-danger';
                                                            } else {
                                                                echo 'btn-group-light';
                                                            } ?> eib2bpro-app-button-opt-click" data-item="<?php eib2bpro_a($item['id']) ?>" data-rel="<?php eib2bpro_a($opt_key) ?>" data-type="<?php eib2bpro_a($item['type']) ?>" data-conditions="<?php if (isset($opt_value['conditions'])) {
                                                                                                                                                                                                                                    echo esc_attr(json_encode($opt_value['conditions']));
                                                                                                                                                                                                                                } ?>">
                                    <?php eib2bpro_a($opt_value) ?>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if ('onoff_multiple' === $item['type']) {
                    ?>
                        <div class="onoff_multiple_div">
                            <input type="checkbox" name="<?php eib2bpro_a($item['id']) ?>[]" value="pseudo" class="d-none" checked>
                            <?php foreach ($item['opt'] as $opt_key => $opt_value) { ?>
                                <input type="checkbox" name="<?php eib2bpro_a($item['id']) ?>[]" class="btn pr-5 switch_1 switch-<?php eib2bpro_a($item['size']) ?> mt-1 eib2bpro-StopPropagation <?php if (in_array((string)$opt_key, (array)$value)) {
                                                                                                                                                                                    echo ' checked';
                                                                                                                                                                                } ?> eib2bpro-app-button-opt-click" value="<?php eib2bpro_a($opt_key) ?>" data-item="<?php eib2bpro_a($item['id']) ?>" data-rel="<?php eib2bpro_a($opt_key) ?>" data-type="<?php eib2bpro_a($item['type']) ?>" data-conditions="<?php if (isset($opt_value['conditions'])) {
                                                                                                                                                                                                                                                                                                                                                                                                echo esc_attr(json_encode($opt_value['conditions']));
                                                                                                                                                                                                                                                                                                                                                                                            } ?>" <?php if (in_array((string)$opt_key, (array)$value)) {
                                                                                                                                                                                                                                                                                                                                                                                                                                echo ' checked';
                                                                                                                                                                                                                                                                                                                                                                                                                            } ?>>
                                <span class="eib2bpro-checkbox-label pl-2"><?php eib2bpro_a($opt_value) ?></span>
                                <br>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if ('onoff_group' === $item['type']) { ?>
                        <div class="onoff_group_div mt-3">
                            <?php foreach ($item['opt'] as $opt_key => $opt_value) { ?>
                                <div class="mb-2 d-flex align-items-center">
                                    <input type="checkbox" class="d-none" name="<?php eib2bpro_a($opt_key) ?>" value="0" checked>
                                    <input type="checkbox" name="<?php eib2bpro_a($opt_key) ?>" class="btn pr-5 mr-1 switch_1 switch-<?php eib2bpro_a($item['size']) ?> eib2bpro-StopPropagation <?php if (1 === intval(eib2bpro_option($opt_key, $opt_value['default']))) {
                                                                                                                                                                                    echo ' checked';
                                                                                                                                                                                } ?> eib2bpro-app-button-opt-click" value="1" data-item="<?php eib2bpro_a($item['id']) ?>" data-rel="<?php eib2bpro_a($opt_key) ?>" data-type="<?php eib2bpro_a($item['type']) ?>" data-conditions="<?php if (isset($opt_value['conditions'])) {
                                                                                                                                                                                                                                                                                                                                                                        echo esc_attr(json_encode($opt_value['conditions']));
                                                                                                                                                                                                                                                                                                                                                                    } ?>" <?php if (1 === intval(eib2bpro_option($opt_key, $opt_value['default']))) {
                                                                                                                                                                                                                                                                                                                                                                                                    echo ' checked';
                                                                                                                                                                                                                                                                                                                                                                                                } ?>>
                                    &nbsp; <?php eib2bpro_a($opt_value['label']) ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if ('big_select' === $item['type']) { ?>
                        <div class="row">
                            <input type="hidden" name="<?php eib2bpro_a($item['id']) ?>" class="opt-<?php eib2bpro_a($item['id']) ?>" value="<?php eib2bpro_a($value) ?>">
                            <?php foreach ($item['opt'] as $opt_key => $opt_value) { ?>
                                <div class="col">
                                    <div class="card p-4 mt-1 changable <?php if ((string)$opt_key === (string)eib2bpro_option($item['id'], $item['default'])) {
                                                                            echo 'selected';
                                                                        } ?>">
                                        <a href="javascript:;" class="eib2bpro-app-button-opt-click" data-item="<?php eib2bpro_a($item['id']) ?>" data-rel="<?php eib2bpro_a($opt_key) ?>" data-type="<?php eib2bpro_a($item['type']) ?>" data-conditions="<?php if (isset($opt_value['conditions'])) {
                                                                                                                                                                                                                                        echo esc_attr(json_encode($opt_value['conditions']));
                                                                                                                                                                                                                                    } ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php eib2bpro_a($opt_value['title']) ?></h5>
                                                <p class="card-text"><?php eib2bpro_a($opt_value['description']) ?></p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if ('color' === $item['type']) { ?>
                        <div class="container-fluid pt-2">
                            <div class="row">
                                <?php foreach ($item['opt'] as $opt_key => $opt_value) { ?>
                                    <div class="col">
                                        <?php eib2bpro_option_color(
                                            array(
                                                'name' => $opt_key,
                                                'label' => $opt_value['label'],
                                                'css' => '',
                                                'value' => eib2bpro_clean2(eib2bpro_option($opt_key), $opt_value['default'])
                                            )
                                        );
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                    <?php } ?>

                    <?php if ('group' === $item['type']) { ?>
                    <?php } ?>

                    <?php if ('html' === $item['type']) {
                        echo eib2bpro_r($item['html']);
                    } ?>

                    <?php if ('func' === $item['type']) {
                        $func = $item['func'];
                        $func();
                    } ?>

                    <?php if (isset($item['description']) && !empty($item['description'])) { ?>
                        <div class="eib2bpro-app-settings-option-description text-muted mt-4"><?php echo eib2bpro_r(wp_kses_data($item['description'])) ?></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>