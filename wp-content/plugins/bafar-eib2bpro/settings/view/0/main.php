<?php defined('ABSPATH') || exit; ?>
<?php if (\EIB2BPRO\Settings\Main::need()) { ?>
    <div class="eib2bpro-container-fluid eib2bpro-app-settings-<?php echo eib2bpro_get('section', 'default') ?>">
        <div class="eib2bpro-title">
            <h3><?php esc_html_e('Settings', 'eib2bpro'); ?></h3>
        </div>
        <div class="eib2bpro-gp">
            <div class="row">
                <div class="col-12 col-lg-2 pt-5 s-pt-0 eib2bpro-menu-2-right-border ">
                    <?php echo eib2bpro_view('settings', 0, 'menu'); ?>
                </div>
                <div class="col-12 col-lg-10 pl-5 pt-4 mt-3 s-0">
                    <?php echo eib2bpro_view('settings', 0, 'head', array(
                        'icon' => $settings['title']['icon'],
                        'title' => $settings['title']['title'],
                        'description' => $settings['title']['description'],
                        'buttons' => $settings['title']['buttons']
                    )); ?>
                    <div class="clear-both"></div>
                    <div class="eib2bpro-app-data-container w-100 mt-4">
                        <div class="table-container eib2bpro-shadow mb-5">
                            <div class="rowx">
                                <?php eib2bpro_form(); ?>
                                <input type="hidden" name="do" value="edit">
                                <input type="hidden" name="section" value="<?php echo esc_attr(eib2bpro_get('section')) ?>">
                                <div id="carouselControls" class="carousel slide w-100">
                                    <div class="eib2bpro-Scroll2">
                                        <ul class="carousel-indicators carousel-groups<?php eib2bpro_a(1 === count($settings['pages']) ? ' eib2bpro-hidden' : '') ?>">
                                            <?php $index = -1;
                                            foreach ($settings['pages'] as $page_key => $page) {
                                                ++$index; ?>
                                                <li data-save="<?php eib2bpro_a(isset($page['save']) ? $page['save'] : 1); ?>" data-location="<?php eib2bpro_a($page_key) ?>" data-target="#carouselControls" data-slide-to="<?php eib2bpro_a($index) ?>" class=" <?php if ($page_key === eib2bpro_get('tab') || (!eib2bpro_get('tab') && 0 === $index)) {
                                                                                                                                                                                                                                                                        echo 'active';
                                                                                                                                                                                                                                                                    } ?>">
                                                    <?php eib2bpro_e($page['title']) ?>
                                                </li>
                                            <?php
                                            } ?>

                                        </ul>
                                    </div>
                                    <div class="carousel-inner">
                                        <?php $index = 0;
                                        foreach ($settings['pages'] as $page_key => $page) {
                                            ++$index; ?>
                                            <div class="carousel-item <?php if ($page_key === eib2bpro_get('tab') || (!eib2bpro_get('tab') && 1 === $index)) {
                                                                            echo ' active';
                                                                        } ?>" data-id="<?php eib2bpro_a($index); ?>" data-do="<?php (isset($page['do'])) ? eib2bpro_a($page['do']) : eib2bpro_a('edit'); ?>">
                                                <div class="row">
                                                    <div class="container-fluid">
                                                        <?php echo eib2bpro_r($page['function']($page_key)) ?>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php
                                        } ?>
                                    </div>
                                </div>
                                <?php eib2bpro_save('', 'hidden eib2bpro-btn-fixed'); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>