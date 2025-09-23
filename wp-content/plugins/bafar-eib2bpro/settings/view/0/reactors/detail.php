<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-Reactors_Details">

    <div class="eib2bpro-Reactors_Details_Intro text-center">
        <div class="w-75">
            <h2><?php echo esc_html($reactor['title']) ?></h2>
            <p><?php echo wp_kses_post($reactor['details']); ?></p>
            <br><br><br>
        </div>
    </div>

    <div class="text-center">
        <a href="<?php echo eib2bpro_secure_url('reactors', esc_attr($reactor['id']), array('action' => 'activate', 'id' => $reactor['id']))  ?>" class="btn btn-sm btn-danger"><?php esc_html_e('Activate', 'eib2bpro'); ?></a>
    </div>

</div>