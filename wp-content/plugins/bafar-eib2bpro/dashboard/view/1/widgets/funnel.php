<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>

<div class="chart-container eib2bpro-Chart_Container">
    <div id="eib2bpro-Chart_Conversation" data-values='<?php echo eib2bpro_r(json_encode($data['funnel'])) ?>' data-labels='<?php echo eib2bpro_r(json_encode([esc_html__('Home Page', 'eib2bpro'), esc_html__('Product Page', 'eib2bpro'),  esc_html__('Add to cart', 'eib2bpro'), esc_html__('Checkout', 'eib2bpro'), esc_html__('Buy', 'eib2bpro')])) ?>'>
    </div>
</div>