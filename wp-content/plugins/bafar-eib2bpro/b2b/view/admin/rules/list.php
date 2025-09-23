<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-container-fluid">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2  eib2bpro-b2b-nav-container eib2bpro-menu-2-right-border">
                <?php echo eib2bpro_view('b2b', 'admin', 'nav') ?>
            </div>
            <div class="col-12 col-lg-10 pt-4 mt-2 pl-5 s-0">
                <div class="eib2bpro-b2b-main-container">
                    <div class="eib2bpro-app-settings-sub-title">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-lg-8">
                                    <h3 class="mt-2 font-weight-bold"><?php esc_html_e('Rules', 'eib2bpro') ?></h3>
                                </div>
                                <div class="col-12 col-lg-4 mt-4 mt-lg-1 text-right">
                                    <a class="eib2bpro-new-rule-button btn btn-sm btn-danger eib2bpro-rounded" href="javascript:;">
                                        + <?php esc_html_e('New Rule', 'eib2bpro'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="eib2bpro-Orders_Container">
                        <div class="eib2bpro-List_M1 eib2bpro-Container  mt-3">
                            <?php \EIB2BPRO\Rules\Main::list() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>