<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
<?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_attr__('Dashboard', 'eib2bpro'), 'description' => '', 'buttons' => '<a href="' . eib2bpro_admin('dashboard', array('action' => 'default')) . '" class="eib2bpro-Dashboard_Buttons eib2bpro-Selected">' . esc_html__('Overview', 'eib2bpro') . '</a> <a href="' . eib2bpro_admin('dashboard', array('action' => 'wc-admin')) . '" class="eib2bpro-Dashboard_Buttons">' . esc_html__('Charts', 'eib2bpro') . '</a>')); ?>

<?php do_action('eib2bpro_need'); ?>

<meta http-equiv="refresh" content="1800" />

<div class="gridster eib2bpro-GP">
    <ul>
        <?php
        $rows = 99;
        $availableWidgets = \EIB2BPRO\Dashboard\Main::availableWidgets();
        foreach ($map as $widget_id => $widget) {
            ++$rows;
            $widget['row'] = (isset($widget['row'])) ? $widget['row'] : ++$rows; ?>
            <?php $widgetclass = '\EIB2BPRO\Dashboard\Widgets\\' . sanitize_key($widget['type']); ?>

            <li class="eib2bpro-Widget  eib2bpro-Widget_<?php echo esc_attr($widget['type']) ?>" data-sizex="<?php echo esc_attr($widget['w']) ?>" data-sizey="<?php echo esc_attr($widget['h']) ?>" data-col="<?php echo esc_attr(eib2bpro_clean($widget['col'], 1)) ?>" data-row="<?php echo esc_attr($widget['row']) ?>" data-min-sizex="<?php echo esc_attr(eib2bpro_clean($availableWidgets[$widget['type']]['minw'], 1)) ?>" data-min-sizey="<?php echo esc_attr(eib2bpro_clean($availableWidgets[$widget['type']]['minh'], 1)) ?>" data-max-sizex="<?php echo esc_attr(eib2bpro_clean($availableWidgets[$widget['type']]['maxw'], 10)) ?>" data-max-sizey="<?php echo esc_attr(eib2bpro_clean($availableWidgets[$widget['type']]['maxh'], 10)) ?>" data-id="<?php echo esc_attr($widget['id']) ?>" data-type="<?php echo esc_attr($widget['type']) ?>" id="eib2bpro-Widget_<?php echo esc_attr($widget['id']) ?>">
                <div class="item-content">
                    <div class="eib2bpro-ControlButton">
                        <?php if (true === $availableWidgets[$widget['type']]['settings']) { ?>
                            <a href="<?php echo eib2bpro_admin('dashboard', array('action' => 'widget-settings', 'id' => esc_attr($widget['id']))); ?>" class="eib2bpro-Widget_Settings_Button eib2bpro-panel" data-width="450px">
                                <span class="dashicons dashicons-admin-tools"></span>
                            </a>
                        <?php } ?>
                        <a href="javascript:;" class="XX">
                            <span class="dashicons dashicons-move"></span>
                        </a>
                    </div>

                    <?php
                    if (isset($settings[$widget['id']])) {
                        $__settings = $_settings = $settings[$widget['id']];
                    } else {
                        $__settings = $_settings = array();
                    }
                    $_settings = $widgetclass::settings($_settings); ?>

                    <div class="eib2bpro-Widget_Content">
                        <?php $widgetclass::run($widget, $__settings); ?>
                    </div>
                </div>
            </li>
        <?php
        } ?>
    </ul>
</div>

<?php if (\EIB2BPRO\Admin::is_admin()) { ?>
    <div class="eib2bpro-Widget_Add eib2bpro-GP text-right"><a href="<?php echo eib2bpro_admin('dashboard', array('action' => 'widget_list')) ?>" data-width="1090px" class="eib2bpro-panel"><?php esc_html_e('Add or remove widgets', 'eib2bpro'); ?></a></div>

    <div id="eib2bpro-wp-notices" class="eib2bpro-GP">
        <?php apply_filters('admin_notices', array()); ?>
    </div>
<?php } ?>

<p>&nbsp;</p>