<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>

<div id='eib2bpro-panel' data-rel="1" class="d-none">
    <div id="inbrowser--loading" class="inbrowser--loading h100 d-flex align-items-center align-middle">
        <div class="lds-ellipsis lds-ellipsis-black">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <div class="eib2bpro-Trig_Close">
        <a href="javascript:;" class="eib2bpro-Trig_CloseButton"><span class="dashicons dashicons-no-alt"></span></a>
    </div>
    <div class="eib2bpro-Trig_Framer">
        <iframe frameborder=0 class="eib2bpro-Trig_Framer_In" src="about:blank" id="inbrowser"></iframe>
    </div>
    <div class="eib2bpro-Trig_Content">
    </div>
</div>

<div id='eib2bpro-panel2' data-rel="2" class="d-none">
    <div id="inbrowser--loading2" class="inbrowser--loading h100 d-flex align-items-center align-middle">
        <div class="lds-ellipsis lds-ellipsis-black">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <div class="eib2bpro-Trig_Close">
        <a href="javascript:;" class="eib2bpro-Trig_CloseButton" data-id="2"><span class="dashicons dashicons-no-alt"></span></a>
    </div>
    <div class="eib2bpro-Trig_Framer">

        <iframe frameborder=0 class="eib2bpro-Trig_Framer_In" src="about:blank" id="inbrowser2"></iframe>

    </div>
</div>

<div id='eib2bpro-panel3' data-rel="3" class="d-none">

    <div class="eib2bpro-LeftMenu eib2bpro-Channels">
        <div class="text-center">
            <?php
            $eib2bpro_img_src = wp_get_attachment_image_src(eib2bpro_option('logo'), 'full');
            if (!is_array($eib2bpro_img_src)) {
                $eib2bpro_img_src = array('');
            }
            ?>
            <img src="<?php echo esc_url_raw($eib2bpro_img_src[0]) ?>" class="eib2bpro-LeftMenu_Logo">
        </div>
        <nav class="eib2bpro-MainMenu vertical">
            <ul>
                <li><a href="<?php eib2bpro_e(eib2bpro_admin('core', ['section' => 'me'])) ?>" class="eib2bpro-panel"><?php esc_html_e('Me', 'eib2bpro'); ?></a></li>
                <li><a href="javascript:;" class="eib2bpro-Left_Search"><?php esc_html_e('Search', 'eib2bpro'); ?></a></li>
                <?php echo \EIB2BPRO\Admin::generateMenu(array('settings' => true)); ?>
                <li>
                    <a href="<?php echo wp_logout_url(); ?>"><?php esc_html_e('Logout', 'eib2bpro'); ?></a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<div id="notifications" class="d-none">
    <div id="heading"><?php esc_html_e('Notifications', 'eib2bpro'); ?>
        <span class="float-right">
            <a href="javascript:;" class="eib2bpro-X badge badge-black">x</a>
        </span>
    </div>
    <div class="eib2bpro-Notifications_Content"></div>
</div>

<div id="eib2bpro-Ajax_Notification" class="d-none">
    <div class="badge badge-pill badge-warning eib2bpro-Ajax_Notification_Container">
        <div class="d-flex align-items-center align-middle">

            <div class="row">
                <div class="eib2bpro-Ajax_Notification_Top">
                    <div class="eib2bpro-Loading">
                        <div class="lds-ellipsis">
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="eib2bpro-OK d-none">
                        <span class="dashicons dashicons-yes"></span>
                    </div>

                    <div class="eib2bpro-Error te d-none">
                        <span class="dashicons dashicons-no"></span>
                    </div>
                </div>

                <div class="col align-middle d-flex align-items-center">
                    <span class="eib2bpro-Text"> Please wait...</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="eib2bpro-search-1--overlay" id="eib2bpro-search-1--overlay">
    <div id="eib2bpro-search-1--wrapper">
        <form method="get" id="eib2bpro-search-1-form" action="" onsubmit="return false;">
            <a href="#" class="eib2bpro-search-1--close" id="eib2bpro-search-1--close-button"><span class="dashicons dashicons-no"></span></a>
            <input type="text" value="" name="ss1" placeholder="Search..." class="eib2bpro-search-input" autocomplete="off">
        </form>

        <div class="eib2bpro-Search_Container_Searching hidden"><?php esc_html_e('Searching...', 'eib2bpro'); ?></div>

        <div class="eib2bpro-Search_Products eib2bpro-Search_Start"></div>
        <div class="eib2bpro-Search_Orders eib2bpro-Search_Start"></div>
        <div class="eib2bpro-Search_Customers eib2bpro-Search_Start"></div>
        <div class="eib2bpro-Search_Container">
        </div>
        <div class="eib2bpro-Search_Container_No">
            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('Nothing found', 'eib2bpro'); ?></div>
            </div>
        </div>
    </div>
</div>
<?php if (\EIB2BPRO\Admin::is_ei()) {
    wp_nonce_field('eib2bpro-general');
} ?>
</div>