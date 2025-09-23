<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>

<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Widgets', 'eib2bpro') ?></h5>
        </div>
    </div>
</div>

<div class="eib2bpro-app-new-item-content pl-4 pr-4">
    <div class="container-fluid">
        <div id="eib2bpro-widgets--list">
            <div class="eib2bpro-GP">
                <h6><?php esc_html_e('Currently added widgets', 'eib2bpro'); ?></h6>
                <div class="row">
                    <?php foreach ($installed as $widget) { ?>
                        <div class="col-md-4 col-sm-12 py-2">
                            <div class="card h-100 text-white  bg-primary">
                                <div class="card-body">
                                    <span class="dashicons dashicons-share-alt"></span>
                                    <br>
                                    <h3 class="text-white mt-4"><?php echo esc_html($widget['title']); ?></h3>
                                </div>
                                <div class="card-body pt-0">
                                    <p class="card-text"><?php echo esc_attr($widget['description']) ?></p>
                                </div>
                                <ul class="list-group list-group-flush border-0 pb-3">
                                    <li class="list-group-item bg-transparent">
                                        <a href="javascript:;" class="eib2bpro-Widget_Delete btn btn-sm btn-outline-light" data-id="<?php echo esc_attr($widget['id']) ?>"><?php esc_html_e('Remove', 'eib2bpro'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <br />
                <br />
                <br />
                <br />
                <h6><?php esc_html_e('Available widgets', 'eib2bpro'); ?></h6>
                <div class="row">
                    <?php foreach ($all as $widget) { ?>
                        <?php if ((true === $widget['multiple']) or ($widget['multiple'] === false && array_search($widget['id'], array_column($installed, 'type')) === false)) { ?>
                            <div class="col-md-4 col-sm-12 py-2 mb-4">
                                <div class="card h-100 text-white bg-dark eib2bpro-shadow border-0">
                                    <div class="card-body">
                                        <span class="dashicons dashicons-share-alt"></span>
                                        <br>
                                        <h3 class="text-white mt-4"><?php echo esc_html($widget['title']); ?></h3>
                                    </div>
                                    <div class="card-body pt-0">
                                        <p class="card-text"><?php echo esc_attr($widget['description']) ?></p>
                                    </div>
                                    <ul class="list-group list-group-flush border-0 pb-3">
                                        <li class="list-group-item bg-transparent">
                                            <a href="javascript:;" class="eib2bpro-Widget_Add_Now btn btn-sm btn-outline-light" data-id="<?php echo esc_attr($widget['id']) ?>"><?php esc_html_e('Add to dashboard', 'eib2bpro'); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <p>&nbsp;</p>
            </div>
        </div>
    </div>
</div>