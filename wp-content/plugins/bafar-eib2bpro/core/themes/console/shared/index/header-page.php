<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (1 === $type) { ?>
    <div class="eib2bpro-title">
        <div class="eib2bpro-GP">
            <h3><?php echo esc_html($title) ?></h3>
            <div class="eib2bpro-title--description"><?php echo esc_html($description) ?> </div>
            <?php if (!isset($no_button)) { ?>
                <div class="eib2bpro-title--buttons" class="float-sm-right"><?php echo wp_kses_post($buttons) ?></div>
            <?php } ?>
            <div class="eib2bpro-Clear_Both"></div>
        </div>
    </div>
<?php } ?>