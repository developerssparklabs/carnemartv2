<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-menu-2">
    <div class="eib2bpro-Scroll">
        <ul class="eib2bpro-b2b-lists eib2bpro-menu-2-right-border1">
            <li class="eib2bpro-menu-2-group mt-lg-4 pt-lg-4">
                <div class="text-uppercase">
                    <?php esc_html_e('B2B', 'eib2bpro'); ?>
                </div>
                <?php $restricted_menu = apply_filters('eib2bpro_disable_nav_items', []); ?>
                <ul>
                    <li class="eib2bpro-menu-2-item <?php eib2bpro_selected('section', false); ?>">
                        <a href="<?php echo eib2bpro_admin('b2b') ?>">
                            <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Overview', 'eib2bpro'); ?></span>
                        </a>
                    </li>
                    <?php if (!in_array('offers', $restricted_menu)) { ?>
                        <li class="eib2bpro-menu-2-item<?php eib2bpro_selected('section', 'offers'); ?>">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'offers']) ?>">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Offers', 'eib2bpro'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if (!in_array('quote', $restricted_menu)) { ?>
                        <li class="eib2bpro-menu-2-item<?php eib2bpro_selected('section', 'quote'); ?>">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'quote']) ?>">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Quotes', 'eib2bpro'); ?></span>
                                <?php if (0 < eib2bpro_option('badge-quote', 0)) { ?>
                                    <span class="badge text-danger mr-4"><?php eib2bpro_e(eib2bpro_option('badge-quote', '')) ?></span>
                                <?php } ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if (!in_array('groups', $restricted_menu)) { ?>
                        <li class="eib2bpro-menu-2-item<?php eib2bpro_selected('section', 'groups'); ?>">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'groups']) ?>">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Groups', 'eib2bpro') ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if (!in_array('rules', $restricted_menu)) { ?>
                        <li class="eib2bpro-menu-2-item<?php eib2bpro_selected('section', 'rules'); ?>">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'rules']) ?>">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Rules', 'eib2bpro'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if (!in_array('fields', $restricted_menu)) { ?>
                        <li class="eib2bpro-menu-2-item<?php eib2bpro_selected('section', 'fields'); ?>">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'fields']) ?>">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Extend', 'eib2bpro') ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if (!in_array('settings', $restricted_menu)) { ?>
                        <li class="eib2bpro-menu-2-item<?php eib2bpro_selected('section', 'settings'); ?>">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'settings']) ?>">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Settings', 'eib2bpro'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>

            <?php if (!in_array('quick', $restricted_menu)) { ?>
                <li class="eib2bpro-menu-2-group">
                    <div class="text-uppercase">
                        <?php esc_html_e('Quick', 'eib2bpro'); ?>
                    </div>
                    <ul>
                        <li class="eib2bpro-menu-2-item">
                            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'bulk', 'action' => 'category']) ?>" class="eib2bpro-panel" data-width="80%" data-hide-close="true">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Categories', 'eib2bpro'); ?></span>
                            </a>
                        </li>

                        <li class="eib2bpro-menu-2-item">
                            <a href="<?php echo eib2bpro_admin('products', []) ?>" class="eib2bpro-panel" data-width="80%" data-hide-close="true">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Products', 'eib2bpro'); ?></span>
                            </a>
                        </li>

                        <li class="eib2bpro-menu-2-item">
                            <a href="<?php echo eib2bpro_admin('customers', []) ?>" class="eib2bpro-panel" data-width="80%" data-hide-close="true">
                                <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Users', 'eib2bpro'); ?></span>
                            </a>
                        </li>

                    </ul>
                </li>
            <?php } ?>
            <?php if (!in_array('system', $restricted_menu)) { ?>
                <li class="eib2bpro-menu-2-group">
                    <div class="text-uppercase">
                        <?php esc_html_e('System', 'eib2bpro'); ?>
                    </div>
                    <ul>


                        <?php if (!in_array('toolbox', $restricted_menu)) { ?>
                            <li class="eib2bpro-menu-2-item">
                                <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'toolbox']) ?>">
                                    <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Toolbox', 'eib2bpro'); ?></span>
                                </a>
                            </li>
                        <?php } ?>

                        <?php if (!in_array('documentation', $restricted_menu)) { ?>
                            <li class="eib2bpro-menu-2-item">
                                <a href="https://en.er.gy/docs/b2b" class="eib2bpro-panel" data-width="80%">
                                    <span class="eib2bpro-menu-2-item-title"><?php esc_html_e('Documentation', 'eib2bpro'); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>