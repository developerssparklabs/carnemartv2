<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-container-fluid eib2bpro-app-b2b-settings-registration">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2  eib2bpro-b2b-nav-container eib2bpro-menu-2-right-border">
                <?php echo eib2bpro_view('b2b', 'admin', 'nav') ?>
            </div>
            <div class="col-12 col-lg-10 pt-4 mt-2 pl-5 s-0 ">
                <div class="eib2bpro-b2b-main-container eib2bpro-app-settings-sub-title">
                    <h3 class="mt-2 pl-3 mb-4 pb-0 font-weight-bold"><?php esc_html_e('Settings', 'eib2bpro') ?></h3>
                    <?php eib2bpro_form(['do' => 'save-settings']) ?>
                    <div class="table-container eib2bpro-shadow mb-5 mt-3">
                        <div id="carouselControls" class="carousel slide w-100">
                            <div class="eib2bpro-Scroll2">

                                <ul class="carousel-indicators carousel-groups">
                                    <li class="<?php eib2bpro_a('general' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>" data-save="0" data-target="#carouselControls" data-slide-to="0">
                                        <?php esc_html_e('Features', 'eib2bpro') ?>
                                    </li>

                                    <li data-save="1" data-target="#carouselControls" data-slide-to="1" class="<?php eib2bpro_a('guests' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                        <?php esc_html_e('Guests', 'eib2bpro') ?>
                                    </li>

                                    <li data-save="1" data-target="#carouselControls" data-slide-to="2" class="<?php eib2bpro_a('myaccount' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                        <?php esc_html_e('My Account', 'eib2bpro') ?>
                                    </li>

                                    <li data-save="1" data-target="#carouselControls" data-slide-to="3" class="<?php eib2bpro_a('quote' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                        <?php esc_html_e('Quotes', 'eib2bpro') ?>
                                    </li>

                                    <li data-save="1" data-target="#carouselControls" data-slide-to="4" class="<?php eib2bpro_a('tiers' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                        <?php esc_html_e('Products', 'eib2bpro') ?>
                                    </li>

                                    <li data-save="1" data-target="#carouselControls" data-slide-to="5" class="<?php eib2bpro_a('advanced' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                        <?php esc_html_e('Advanced', 'eib2bpro') ?>
                                    </li>

                                    <li data-save="1" data-target="#carouselControls" data-slide-to="6" class="<?php eib2bpro_a('lang' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                        <?php esc_html_e('Colors', 'eib2bpro') ?>
                                    </li>

                                    <?php if (1 === eib2bpro_option('b2b_enable_admin_panel', 1) && !in_array('features_admin_panel', apply_filters('eib2bpro_disable_nav_items', []))) { ?>
                                        <li data-save="0" data-target="#carouselControls" data-slide-to="7" class="<?php eib2bpro_a('lang' === eib2bpro_get('tab', 'general') ? 'active' : ''); ?>">
                                            <?php esc_html_e('Panel', 'eib2bpro') ?>
                                        </li>
                                    <?php } ?>

                                </ul>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item <?php eib2bpro_a('general' === eib2bpro_get('tab', 'general') ? 'active' : '') ?>" data-id="0" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <div class="eib2bpro-b2b-settings-features d-flex align-content-stretch flex-wrap w-100">
                                                <div class="eib2bpro-b2b-settings-features-card w-50">
                                                    <?php eib2bpro_ui('onoff_ajax', 'enable_offers', eib2bpro_option('b2b_enable_offers', 1), ['do' => 'enable', 'id' => 'offers', 'app' => 'b2b']) ?>
                                                    <h4 class="pt-4"><?php esc_html_e('Offers', 'eib2bpro'); ?></h4>
                                                    <div class="pt-3 text-muted">
                                                        <?php esc_html_e('Allows you to create special offers to your customers', 'eib2bpro'); ?>
                                                    </div>
                                                </div>

                                                <div class="eib2bpro-b2b-settings-features-card w-50">
                                                    <?php eib2bpro_ui('onoff_ajax', 'enable_bulkorder', eib2bpro_option('b2b_enable_bulkorder', 0), ['do' => 'enable', 'id' => 'bulkorder', 'app' => 'b2b']) ?>
                                                    <h4 class="pt-4"><?php esc_html_e('Bulk Order', 'eib2bpro'); ?></h4>
                                                    <div class="pt-3 text-muted">
                                                        <?php esc_html_e('Adds wholesale bulk order form to My Account', 'eib2bpro'); ?>
                                                    </div>
                                                </div>

                                                <div class="eib2bpro-b2b-settings-features-card w-50">
                                                    <?php eib2bpro_ui('onoff_ajax', 'enable_quickorders', eib2bpro_option('b2b_enable_quickorders', 0), ['do' => 'enable', 'id' => 'quickorders', 'app' => 'b2b']) ?>
                                                    <h4 class="pt-4"><?php esc_html_e('Quick Orders', 'eib2bpro'); ?></h4>
                                                    <div class="pt-3 text-muted">
                                                        <?php esc_html_e('Allows customers to create quick order lists', 'eib2bpro'); ?>
                                                    </div>
                                                </div>

                                                <?php if (!in_array('features_admin_panel', apply_filters('eib2bpro_disable_nav_items', []))) { ?>
                                                    <div class="eib2bpro-b2b-settings-features-card w-50">
                                                        <?php eib2bpro_ui('onoff_ajax', 'admin_panel', eib2bpro_option('b2b_enable_admin_panel', 1), ['do' => 'enable', 'id' => 'admin_panel', 'app' => 'b2b']) ?>
                                                        <h4 class="pt-4"><?php esc_html_e('Admin Panel', 'eib2bpro'); ?></h4>
                                                        <div class="pt-3 text-muted">
                                                            <?php esc_html_e('A customized panel to enhance your store management experience', 'eib2bpro'); ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="ei-b2b-settings-features d-flex align-content-stretch flex-wrap w-100"></div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item <?php eib2bpro_a('guests' === eib2bpro_get('tab') ? 'active' : '') ?>" data-id="1" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <?php echo eib2bpro_view('settings', 0, 'options.form', array('options' => $settings['guests']['options'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item <?php eib2bpro_a('myaccount' === eib2bpro_get('tab') ? 'active' : '') ?>" data-id="2" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <?php echo eib2bpro_view('settings', 0, 'options.form', array('options' => $settings['myaccount']['options'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item <?php eib2bpro_a('quote' === eib2bpro_get('tab') ? 'active' : '') ?>" data-id="3" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <?php echo eib2bpro_view('settings', 0, 'options.form', array('options' => $settings['quote']['options'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item <?php if ('tiers' === eib2bpro_get('tab')) {
                                                                echo 'active';
                                                            } ?>" data-id="4" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <?php echo eib2bpro_view('settings', 0, 'options.form', array('options' => $settings['appearance']['options'])); ?>
                                        </div>
                                    </div>
                                </div>


                                <div class="carousel-item <?php if ('advanced' === eib2bpro_get('tab')) {
                                                                echo 'active';
                                                            } ?>" data-id="5" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <?php echo eib2bpro_view('settings', 0, 'options.form', array('options' => $settings['others']['options'])); ?>
                                        </div>
                                    </div>
                                </div>


                                <div class="carousel-item <?php if ('lang' === eib2bpro_get('tab')) {
                                                                echo 'active';
                                                            } ?>" data-id="6" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <?php echo eib2bpro_view('settings', 0, 'options.form', array('options' => $settings['lang']['options'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item <?php if ('panel' === eib2bpro_get('tab')) {
                                                                echo 'active';
                                                            } ?>" data-id="7" data-do="save-settings">
                                    <div class="row">
                                        <div class="container-fluid">

                                            <div class="eib2bpro-settings-about">
                                                <div class="eib2bpro-about-icon">
                                                    <i class="ri-external-link-fill text-dark"></i>
                                                </div>
                                                <br>
                                                <div class="text-dark eib2bpro-font-14 font-weight-bold"><?php esc_html_e('Please go to Settings page for the admin panel', 'eib2bpro'); ?></div>
                                                <br>
                                                <br>
                                                <a href="<?php echo eib2bpro_admin('settings', ['section' => 'general']) ?>" class="btn btn-dark font-weight-normal"><?php esc_html_e('Go to settings →', 'eib2bpro'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item " data-id="2" data-do="edit">
                                    <div class="row">
                                        <div class="container-fluid">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php eib2bpro_save('', 'eib2bpro-btn-fixed hidden'); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>