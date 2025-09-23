<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-container-fluid eib2bpro-app-settings-<?php echo eib2bpro_get('section', 'default') ?>">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('Settings', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2 pt-4 mt-4 s-pt-0 eib2bpro-menu-2-right-border ">
                <?php echo eib2bpro_view('settings', 0, 'menu'); ?>
            </div>
            <div class="col-12 col-lg-10 pl-5 pt-4 mt-3 s-0">
                <?php echo eib2bpro_view('settings', 0, 'head', array(
                    'icon' => $settings['title']['icon'],
                    'title' => $settings['title']['title'],
                    'description' => $settings['title']['description'],
                    'buttons' => $settings['title']['buttons']
                )); ?>

                <div class="app-data-container w-100 mt-4 mb-5">
                    <div class="table-container eib2bpro-shadow">
                        <?php eib2bpro_form(array('do' => 'menu')); ?>
                        <div class="rowx">
                            <div id="carouselControls" class="carousel slide w-100">
                                <div class="eib2bpro-Scroll2">
                                    <ul class="carousel-indicators carousel-groups">
                                        <?php $index = -1;
                                        foreach ($menu as $group => $group_name) {
                                            ++$index; ?>
                                            <li data-target="#carouselControls" data-slide-to="<?php eib2bpro_a($index) ?>" class=" <?php if (0 === $index) {
                                                                                                                                        echo 'active';
                                                                                                                                    } ?>" data-save="0">
                                                <?php eib2bpro_e($group_name['role']) ?>
                                            </li>
                                        <?php
                                        } ?>

                                    </ul>
                                </div>
                                <div class="carousel-inner eib2bpro-app-settings-menu">

                                    <?php $index = 0;
                                    foreach ($menu

                                        as $id => $items) {
                                        ++$index; ?>

                                        <div class="carousel-item <?php if (1 === $index) {
                                                                        echo ' active';
                                                                    } ?>" data-do="menu">
                                            <ol class="row eib2bpro-app-settings-sortable <?php if (1 < $index) {
                                                                                                echo 'eib2bpro-os-hidden ';
                                                                                            } ?>eib2bpro-app-settings-sortable-<?php eib2bpro_a($id) ?>">

                                                <?php foreach ($items['menu'] as $app_id => $item) {
                                                    if (!isset($item[2]) && !isset($item['eix'])) {
                                                        continue;
                                                    }
                                                    if (!isset($item['hide']) || (isset($item['hide']) && 0 === $item['hide'])) {
                                                ?>
                                                        <li id="app_id_<?php eib2bpro_a($app_id) ?>" class="hasItems table-item col-12">
                                                            <div class="p-30 row">
                                                                <div class="col-12 col-lg-5 text-left">
                                                                    <div class="float-left pt-2">
                                                                        <i class="eib2bpro-os-move eib2bpro-icon-move pl-2"></i>
                                                                    </div>
                                                                    <div class="float-left pl-3 pt-1">
                                                                        <input type="checkbox" name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][active]" value="1" class="switch_1 app-swtich-onoff switch-sm" <?php eib2bpro_checked(intval(isset($item['active']) ? $item['active'] : 0)) ?>>
                                                                    </div>
                                                                    <div class="float-left pl-3">
                                                                        <h5>
                                                                            <i class="eib2bpro-app-icon app_id_<?php eib2bpro_a($app_id) ?> <?php if (stripos($item[6], 'dashicons') !== false) {
                                                                                                                                                echo 'dashicons-before';
                                                                                                                                            } ?> <?php eib2bpro_a($item[6]) ?>"></i>
                                                                            <span class="pl-3">
                                                                                <?php eib2bpro_e(wp_strip_all_tags(preg_replace('~<span(.*?)</span>~Usi', '', $item[0]))) ?>
                                                                                <?php if ('x' === substr($app_id, 0, 1) && isset($item['parent']) && '0' !== $item['parent']) { ?>
                                                                                    &nbsp; <span class="badge badge-pill badge-secondary"><?php echo esc_html(strtoupper($item['parent'])) ?></span>
                                                                                <?php } ?>
                                                                                <?php if (isset($item['target']) && 2 === intval($item['target'])) { ?>
                                                                                    &nbsp; <span class="badge badge-pill badge-secondary"><?php esc_html_e('DIVIDER', 'eib2bpro'); ?></span>
                                                                                <?php } ?>
                                                                            </span>
                                                                            <input name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][6]" class="icon_<?php eib2bpro_a($app_id) ?>" value="<?php eib2bpro_a($item[6]) ?>" type="hidden">
                                                                            <input name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][0]" value="<?php echo esc_attr($item[0]) ?>" type="hidden">
                                                                        </h5>
                                                                    </div>
                                                                </div>


                                                                <div class="col-7 d-none d-lg-block pr-4 text-right">
                                                                    <button class="eib2bpro-os-change-icon" data-id="<?php eib2bpro_a($app_id) ?>" data-icon="<?php eib2bpro_a($item[6]) ?>" data-iconset="remix"><?php esc_html_e('Change icon', 'eib2bpro'); ?></button>
                                                                    <?php if ('x' === substr($app_id, 0, 1)) { ?>
                                                                        <input name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][0]" value="<?php eib2bpro_a($item[0]) ?>" type="hidden">
                                                                        <input name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][2]" value="<?php eib2bpro_a($item[2]) ?>" type="hidden">
                                                                        <input name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][parent]" value="<?php eib2bpro_a(isset($item['parent']) ? $item['parent'] : 0) ?>" type="hidden">
                                                                        <input name="menu[<?php eib2bpro_a($id) ?>][<?php eib2bpro_a($app_id) ?>][target]" value="<?php eib2bpro_a(isset($item['target']) ? $item['target'] : 0) ?>" type="hidden">
                                                                        <button class="bg-white text-danger eib2bpro-rounded eib2bpro-app-ajax" data-id="<?php eib2bpro_a($app_id) ?>" data-app="settings" data-action="eib2bpro" data-do="delete-menu" data-confirm="<?php esc_attr_e('Are you sure?', 'eib2bpro') ?>" data-asnonce="<?php echo wp_create_nonce('eib2bpro-security') ?>"><?php esc_html_e('Delete', 'eib2bpro') ?></button>
                                                                    <?php } ?>
                                                                </div>


                                                            </div>

                                                        </li>

                                                <?php
                                                    }
                                                } ?>
                                            </ol>
                                        </div>
                                    <?php
                                    } ?>

                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-save eib2bpro-app-save-button-hidden eib2bpro-os-stop-propagation"><?php esc_html_e('Save', 'eib2bpro') ?></button>
                        </form>
                    </div>
                    <br>
                    <a href="javascript:;" class="mt-5 ml-4 eib2bpro-app-ajax" data-id="<?php eib2bpro_a($app_id) ?>" data-app="settings" data-action="eib2bpro" data-do="reset-menu" data-asnonce="<?php echo wp_create_nonce('eib2bpro-security') ?>" data-confirm="<?php esc_attr_e('This will reset all the menu settings, are you sure?', 'eib2bpro') ?>"><?php esc_html_e('Reset menu', 'eib2bpro') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>