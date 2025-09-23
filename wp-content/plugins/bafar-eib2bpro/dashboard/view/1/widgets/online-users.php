<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<h1><?php esc_html_e('Online Visitors', 'eib2bpro'); ?></h1>
<div id='eib2bpro-Widget_<?php echo esc_attr($args['id']) ?>_Current' class="eib2bpro-Widget_onlineusers_Current" data-widgetid="<?php echo esc_attr($args['id']) ?>"><?php echo esc_html($result); ?></div>
<canvas id="eib2bpro-Widget_<?php echo esc_attr($args['id']) ?>_Canvas" class="eib2bpro-Widget_onlineusers_Canvas"></canvas>
<div id="bp-eib2bpro-wdg-ov--min"><?php echo esc_html($min); ?></div>
<div id="bp-eib2bpro-wdg-ov--max"><?php echo esc_html($max); ?></div>