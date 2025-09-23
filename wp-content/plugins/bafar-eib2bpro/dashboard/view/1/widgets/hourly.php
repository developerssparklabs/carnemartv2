<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-Widget_Options">
    <h1 class="float-left">
        <?php esc_html_e('Visitors', 'eib2bpro'); ?>
    </h1>
    <div class="eib2bpro-Widget_Options_AutoHide float-left">
        <ul>
            <li>
                <a class="eib2bpro-Widget_Settings_Range <?php if ('hourly' === $args['range']) {
                                                        echo ' eib2bpro-Selected';
                                                    } ?>" data-range='hourly' data-widgettype="hourly" data-id="<?php echo esc_attr($args['id']) ?>" href="javascript:;"><?php esc_html_e('Hourly', 'eib2bpro'); ?></a>
            </li>
            <li>
                <a class="eib2bpro-Widget_Settings_Range <?php if ('daily' === $args['range']) {
                                                        echo ' eib2bpro-Selected';
                                                    } ?>" data-range='daily' data-widgettype="hourly" data-id="<?php echo esc_attr($args['id']) ?>" href="javascript:;"><?php esc_html_e('Daily', 'eib2bpro'); ?></a>
            </li>
            <li>
                <a class="eib2bpro-Widget_Settings_Range <?php if ('monthly' === $args['range']) {
                                                        echo ' eib2bpro-Selected';
                                                    } ?>" data-range='monthly' data-widgettype="hourly" data-id="<?php echo esc_attr($args['id']) ?>" href="javascript:;"><?php esc_html_e('Monthly', 'eib2bpro'); ?></a>
            </li>
        </ul>
    </div>
</div>

<div class="chart-container eib2bpro-Widget_Hourly_Chart eib2bpro-Widget_hourly_container">
    <canvas id="eib2bpro-Chart_<?php echo esc_attr($args['id']) ?>" width="120"></canvas>
</div>
<script>
    jQuery(document).ready(function() {
        "use strict";

        var ctx = document.getElementById("eib2bpro-Chart_<?php echo esc_attr($args['id']) ?>").getContext('2d');
        ctx.height = 105;
        var myChart = new Chart(ctx, {
            type: 'bar',
            maintainAspectRatio: false,

            options: {
                <?php if (isset($args['ajax'])) { ?>
                    animation: false,
                <?php } ?>
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,

                        }
                    }],
                    yAxes: [{
                        display: false,
                        ticks: {
                            min: 0,
                            max: <?php echo intval($max + (ceil($max * 0.3))) ?>,
                            stepSize: 1
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                }
            },
            data: {
                labels: [<?php echo implode(",", $labels); ?>],
                datasets: [{
                    <?php if ('one' === \EIB2BPRO\Admin::$theme) { ?>
                        backgroundColor: '#8bbed9',
                    <?php } ?>
                    label: '',
                    data: [<?php echo implode(",", $results); ?>],
                    borderWidth: 1
                }]
            },

        });
        <?php if (!isset($args['ajax'])) { ?>
            Chart.plugins.register({
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;

                    chart.data.datasets.forEach(function(dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        if (!meta.hidden) {
                            meta.data.forEach(function(element, index) {
                                // Draw the text in black, with the specified font
                                <?php if ('one' === \EIB2BPRO\Admin::$theme) { ?>
                                    ctx.fillStyle = '#666';
                                <?php } else { ?>
                                    ctx.fillStyle = '#ccc';
                                <?php } ?>

                                var fontSize = 13;
                                var fontStyle = 'normal';
                                var fontFamily = 'Arial';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

                                // Just naively convert to string for now
                                var dataString = dataset.data[index].toString();

                                // Make sure alignment settings are correct
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';

                                var padding = 5;
                                var position = element.tooltipPosition();
                                if (dataString > 0) {
                                    ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                                }
                            });
                        }
                    });
                }
            });
        <?php } ?>
    });
</script>