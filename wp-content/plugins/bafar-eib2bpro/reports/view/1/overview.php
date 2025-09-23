<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
<?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Reports', 'eib2bpro'), 'description' => '', 'buttons' => '')); ?>
<?php echo eib2bpro_view('reports', 1, 'nav') ?>

<div id="eib2bpro-reports" class="eib2bpro-GP">

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-md-offset-2 text-center">
                <div class="eib2bpro-Reports_Range btn-group" role="group" aria-label="Button group with nested dropdown">
                    <a href="<?php echo eib2bpro_change_url('range', 'daily', 'btn btn-secondary', 'btn-dark', true) ?>"><?php esc_html_e('Daily', 'eib2bpro'); ?></a>
                    <a href="<?php echo eib2bpro_change_url('range', 'weekly', 'btn btn-secondary', 'btn-dark') ?>"><?php esc_html_e('Weekly', 'eib2bpro'); ?></a>
                    <a href="<?php echo eib2bpro_change_url('range', 'monthly', 'btn btn-secondary', 'btn-dark') ?>"><?php esc_html_e('Monthly', 'eib2bpro'); ?></a>
                </div>
            </div>
        </div>

        <div class="row">
            <?php $max_width = 261 / 30 * count($data['results']);
            if (100 > $max_width || ("1" === eib2bpro_option('reports-graph', "2"))) {
                $max_width = 100;
            } ?>

            <div id="eib2bpro-eeez" data-max="<?php echo esc_attr($max_width / 100) ?>" class="eib2bpro-eeez_Graph<?php echo esc_attr(eib2bpro_option('reports-graph', "2")) ?>">
                <div id="eib2bpro-eee" <?php eib2bpro_style('width:' . esc_attr($max_width) . '%') ?>>
                    <canvas id="eib2bpro-Chart_1" width="100%" class="eib2bpro-Chart_1_<?php echo esc_attr(eib2bpro_option('reports-graph', "2")) ?>"></canvas>
                </div>
            </div>
            <div class="eib2bpro-Reports_Top_Cards row w-100">
                <?php if (0 === (int)eib2bpro_get("month")) { ?>

                    <?php foreach ($data['quick'] as $quick) {   ?>
                        <div class="col-lg-<?php echo floor(12 / count($data['quick'])); ?> col-sm-4">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo esc_html($quick['title']) ?></h5>
                                <p class="card-text text-center"><?php echo wp_kses_data($quick['text']) ?></p>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

            <br />&nbsp;
            <br />&nbsp;
            <br />&nbsp;
            <br />

        </div>
    </div>
</div>
<div class="eib2bpro-Reports_Div">
    <div class="eib2bpro-Reports_Div_Inner table-responsive ">
        <?php $visitor_total = array_sum(array_column($data['results'], 'visitors'));
        $data['results_r'] = array_reverse($data['results']) ?>
        <table class="eib2bpro-Reports_Table table table-hover text-center">
            <thead>
                <th class="text-left"><?php esc_html_e('Date', 'eib2bpro'); ?></th>
                <?php if (0 < $visitor_total) { ?>
                    <th><?php esc_html_e('Visitors', 'eib2bpro'); ?></th>
                <?php } ?>
                <th><?php esc_html_e('Orders', 'eib2bpro'); ?></th>
                <th class="text-right"><?php esc_html_e('Sales', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <th class="text-right"><?php esc_html_e('Net Sal.', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <th class="text-right"><?php esc_html_e('Shipping', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <th class="text-right"><?php esc_html_e('Taxes', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <th class="text-right"><?php esc_html_e('Refunds', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <th class="text-right"><?php esc_html_e('Coupons', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <?php foreach (apply_filters('eib2bpro_extends_reports_columns', array()) as $k => $v) { ?>
                    <th class="text-right"><?php echo esc_html($v) ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
                <?php } ?>
                <th class="text-right"><?php esc_html_e('Goals', 'eib2bpro'); ?> &nbsp;(<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</th>
            </thead>
            <tbody>
                <?php
                foreach ($data['results_r'] as $date) {
                    if (!isset($date['label'])) {
                        continue;
                    }
                ?>
                    <tr>
                        <td class="text-left text-uppercase"><?php echo esc_html($date['label']) ?></td>
                        <?php if (0 < $visitor_total) { ?>
                            <td><?php echo (isset($date['visitors'])) ? intval($date['visitors']) : '0' ?></td>
                        <?php } ?>
                        <td><?php echo (0 < $date['orders_count']) ? intval($date['orders_count']) : '-' ?></td>
                        <td class="text-right"><?php echo floatval(0 !== intval($date['total_sales'])) ? wc_price($date['total_sales']) : '-' ?>

                            <?php if ($date['prev'] === 0) {
                                echo '&nbsp; <span class="eib2bpro-Opacity_0">▲</span>';
                            } elseif (0 !== intval($date['total_sales']) && $date['total_sales'] > $date['prev']) {
                                echo '&nbsp; <span class="text-success">▲</span>';
                            } elseif (0 !== intval($date['total_sales']) && $date['total_sales'] < $date['prev']) {
                                echo '&nbsp; <span class="text-danger">▼</span>';
                            } elseif (0 !== intval($date['total_sales'])) {
                                echo '&nbsp; <span class="text-warning">—</span>';
                            } ?>
                        </td>
                        <td class="text-right"><?php echo (0 !== intval($date['net_revenue'])) ? wc_price($date['net_revenue']) : '-' ?></td>
                        <td class="text-right"><?php echo (0 !== intval($date['shipping'])) ? wc_price($date['shipping']) : '-' ?></td>
                        <td class="text-right"><?php echo (0 !== intval($date['taxes'])) ? wc_price($date['taxes']) : '-' ?></td>
                        <td class="text-right"><?php echo (0 !== intval($date['refunds'])) ? wc_price($date['refunds']) : '-' ?></td>
                        <td class="text-right"><?php echo (0 !== intval($date['coupons'])) ? wc_price($date['coupons']) : '-' ?></td>
                        <?php foreach (apply_filters('eib2bpro_extends_reports_columns', array()) as $k => $v) {
                            if (isset($date[$k])) { ?>
                                <td class="text-right"><?php echo (0 !== $date[$k]) ? wc_price($date[$k]) : '-' ?></td>
                        <?php }
                        } ?>
                        <td class="text-right"><span class="eib2bpro-Goal_Bullet<?php if ($date['total_sales'] <= $date['goal']) {
                                                                                    echo ' text-danger';
                                                                                } else {
                                                                                    echo ' text-success';
                                                                                } ?>"><?php echo wc_price($date['total_sales'] - $date['goal']) ?> &nbsp; &#11044;</span></td>

                    </tr>
                <?php
                } ?>
            </tbody>
        </table>
    </div>
</div>


<?php
$conversion_range = ['daily' => 1, 'weekly' => 7, 'monthly' => 30];
if ($conversion_range[eib2bpro_get('range', 'daily')] <= eib2bpro_option('tracker-keep-data', 1) && 0 === (int)eib2bpro_get("month")) {
    $funnel_is_active = true; ?>
    <div class="eib2bpro-Reports_Div">
        <div class="eib2bpro-Reports_Div_Inner">
            <h6><?php esc_html_e("Conversions", 'eib2bpro'); ?></h6>
            <div id="eib2bpro-Chart_Conversation">
            </div>
        </div>
    </div>
<?php } ?>

<?php if ('' === eib2bpro_get("range")) { ?>
    <div class="eib2bpro-Reports_Div pb-4">
        <div class="eib2bpro-Reports_Div_Inner text-center">
            <a href="<?php echo eib2bpro_admin('reports', array('month' => (int)eib2bpro_get("month") + 1));  ?>" class="eib2bpro-Dashboard_Buttons"> ← <?php echo date_i18n('F', strtotime("-" . ((int)eib2bpro_get("month") + 1) . ' MONTH')) ?></a>

            &nbsp; &nbsp;

            <?php if (0 < (int)eib2bpro_get("month")) { ?>
                <?php echo date_i18n('F', strtotime("-" . ((int)eib2bpro_get("month")) . ' MONTH')) ?>
                &nbsp; &nbsp;
                <a href="<?php echo eib2bpro_admin('reports', array('month' => (int)eib2bpro_get("month") - 1));  ?>" class="eib2bpro-Dashboard_Buttons"><?php echo date_i18n('F', strtotime("-" . ((int)eib2bpro_get("month") - 1) . ' MONTH')) ?> →</a>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<div class="eib2bpro-Reports_Div">
</div>


<?php
if ("2" === eib2bpro_option('reports-graph', "2")) {   ?>
    <script>
        jQuery(document).ready(function() {
            "use strict";

            var dataset_01 = {
                label: "<?php esc_html_e('Visitors', 'eib2bpro') ?>",
                borderWidth: 1,
                pointRadius: 0,
                <?php if ('dark' === \EIB2BPRO\Admin::$theme) { ?>
                    backgroundColor: "#CBE86B",
                    backgroundColor: "rgba(0,173,160,0.6)",
                    backgroundColor: "rgba(242,201,10,0.5)",
                    borderColor: "rgba(255,255,255,0.1)",
                    pointBorderColor: "rgba(0,0,0,0)",
                <?php } else {   ?>
                    backgroundColor: "#CBE86B",
                    backgroundColor: "rgba(0,173,160,0.6)",
                    backgroundColor: "rgba(203,232,107,0.5)",
                    borderColor: "rgba(255,255,255,0.8)",
                    pointBorderColor: "rgba(0,0,0,0)",
                <?php } ?>
                data: [0, <?php echo implode(",", array_column($data['results'], 'visitors')) ?>, 0]
            };

            var dataset_02 = {
                label: "<?php esc_html_e('Sales', 'eib2bpro') ?>",
                borderWidth: 2,
                pointRadius: 0,

                <?php if ('dark' === \EIB2BPRO\Admin::$theme) { ?>
                    backgroundColor: "rgba(0,173,160,0.6)",
                    backgroundColor: "rgba(203,232,107,0.5)",
                    backgroundColor: "rgba(0,173,160,0.6)",
                    borderColor: "rgba(0,0,0,0)",
                    pointBorderColor: "rgba(0,0,0,0)",
                <?php } else {   ?>
                    backgroundColor: "rgba(0,173,160,0.6)",
                    backgroundColor: "rgba(203,232,107,0.5)",
                    backgroundColor: "rgba(0,173,160,0.6)",
                    borderColor: "rgba(0,0,0,0)",
                    pointBorderColor: "rgba(0,0,0,0)",
                    borderColor: "rgba(255,255,255,0.6)",

                <?php } ?>

                data: [0, <?php echo implode(",", array_column($data['results'], 'total_sales')); ?>, 0]

            };

            // Graph data
            var data = {
                labels: ['', '<?php echo implode("','", array_column($data['results'], 'label')); ?>', ''],
                datasets: [dataset_01]
            };

            // Graph options
            var options = {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: true
                },
                legend: {
                    display: false
                },
                tooltips: {
                    position: 'nearest',
                    mode: 'index',
                    intersect: false,
                    bodySpacing: 6,
                    fontSize: 13,
                    callbacks: {
                        label: function(tooltipItems, data) {
                            var label = data.datasets[tooltipItems.datasetIndex].label || '';

                            if (label) {
                                label += ': ';
                            }


                            if (tooltipItems.datasetIndex === 0)
                                label += tooltipItems.yLabel + " <?php echo eib2bpro_r(get_woocommerce_currency()) ?>";
                            else
                                label += tooltipItems.yLabel;
                            return label;
                        }
                    }
                },
                animation: {
                    duration: 1400,
                    easing: 'easeOutBack'
                },
                scales: {
                    xAxes: [{
                        minBarLength: 10,
                        display: true,
                        beginAtZero: true,
                        drawBorder: false,

                        ticks: {
                            fontColor: "#A7A7A2",
                            beginAtZero: true,
                            padding: -35,
                            mirror: true,
                            fontSize: 12,
                            stepSize: 1,
                            max: 81,
                            min: 0

                        },
                        gridLines: {
                            drawBorder: false,
                            display: false,
                            zeroLineWidth: 10

                        }
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                scaleBeginAtZero: true
            };

            // The container
            var ctx = document.getElementById("eib2bpro-Chart_1").getContext("2d");

            // Display the first chart
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: options
            });

            // Add second chart after a delay
            setTimeout(function() {
                myLineChart.chart.config.data.datasets.unshift(dataset_02);
                myLineChart.update();
            }, 100);



            <?php if ('yearly' !== eib2bpro_get("range") && isset($funnel_is_active)) { ?>

                /* CONVERSION GRAPH */

                var funnel_data = {
                    labels: ['<?php esc_html_e('Home Page', 'eib2bpro'); ?>', '<?php esc_html_e('Product Page', 'eib2bpro'); ?>', '<?php esc_html_e('Add to cart', 'eib2bpro'); ?>', '<?php esc_html_e('Checkout', 'eib2bpro'); ?>', '<?php esc_html_e('Buy', 'eib2bpro'); ?>'],
                    colors: ['#54c8a7', '#e4f2af'],
                    values: [<?php echo implode(',', $data['funnel']) ?>]
                }

                var graph = new FunnelGraph({
                    container: '#eib2bpro-Chart_Conversation',
                    gradientDirection: 'horizontal',
                    data: funnel_data,
                    displayPercent: true,
                    direction: 'horizontal'
                });

                graph.draw();
            <?php } ?>
        })
    </script>
<?php } else {   ?>
    <script>
        jQuery(document).ready(function() {
            "use strict";

            var dataset_01 = {
                label: "<?php esc_html_e('Sales', 'eib2bpro') ?>",
                <?php if ('dark' === \EIB2BPRO\Admin::$theme) { ?>
                    backgroundColor: "#CBE86B",
                    backgroundColor: "rgba(0,173,160,0.6)",
                    borderWidth: 0,
                    borderColor: "rgba(0,0,0,0.8)",
                    pointBorderColor: "rgba(0,0,0,0)",
                <?php } else {   ?>
                    backgroundColor: "#CBE86B",
                    backgroundColor: "rgba(204,204,204,0.8)",
                    borderWidth: 1,
                    borderColor: "rgba(255,255,255,0.8)",
                    pointBorderColor: "rgba(0,0,0,0)",
                <?php } ?>
                pointRadius: 0,
                data: [<?php echo implode(",", array_column($data['results'], 'total_sales')); ?>]

            };

            // Graph data
            var data = {
                labels: ['<?php echo implode("','", array_column($data['results'], 'label')); ?>'],
                datasets: [dataset_01]
            };

            // Graph options
            var options = {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: true
                },
                legend: {
                    display: false
                },
                tooltips: {
                    position: 'nearest',
                    mode: 'index',
                    intersect: false,
                    bodySpacing: 6,
                    fontSize: 13,
                    callbacks: {
                        label: function(tooltipItems, data) {
                            var label = data.datasets[tooltipItems.datasetIndex].label || '';

                            if (label) {
                                label += ': ';
                            }


                            if (tooltipItems.datasetIndex === 1)
                                label += tooltipItems.yLabel + " <?php echo eib2bpro_e(get_woocommerce_currency()) ?>";
                            else
                                label += tooltipItems.yLabel;
                            return label;
                        }
                    }
                },
                animation: {
                    duration: 1400,
                    easing: 'easeOutBack'
                },
                scales: {
                    xAxes: [{
                        minBarLength: 10,
                        display: true,
                        beginAtZero: true,
                        drawBorder: false,

                        ticks: {
                            fontColor: "#A7A7A2",
                            beginAtZero: true,
                            padding: -35,
                            mirror: true,
                            fontSize: 12,
                            stepSize: 1,
                            max: 81,
                            min: 0

                        },
                        gridLines: {
                            drawBorder: false,
                            display: false,
                            zeroLineWidth: 10

                        }
                    }],
                    yAxes: [{
                        display: true,
                        gridLines: {
                            drawBorder: false,
                            display: true,
                            zeroLineWidth: 0,
                            <?php if ('dark' === \EIB2BPRO\Admin::$theme) { ?>
                            <?php } else {  ?>
                                color: '#efefef'
                            <?php } ?>

                        }
                    }]
                },
                scaleBeginAtZero: true
            };

            // The container
            var ctx = document.getElementById("eib2bpro-Chart_1").getContext("2d");

            // Display the first chart
            var myLineChart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: options
            });


            <?php if ('yearly' !== eib2bpro_get("range")) { ?>

                /* CONVERSION GRAPH */

                var funnel_data = {
                    labels: ['<?php esc_html_e('Home Page', 'eib2bpro'); ?>', '<?php esc_html_e('Product Page', 'eib2bpro'); ?>', '<?php esc_html_e('Add to cart', 'eib2bpro'); ?>', '<?php esc_html_e('Checkout', 'eib2bpro'); ?>', '<?php esc_html_e('Buy', 'eib2bpro'); ?>'],
                    colors: ['#54c8a7', '#e4f2af'],
                    values: [<?php echo implode(',', $data['funnel']) ?>]
                }

                var graph = new FunnelGraph({
                    container: '#eib2bpro-Chart_Conversation',
                    gradientDirection: 'horizontal',
                    data: funnel_data,
                    displayPercent: true,
                    direction: 'horizontal'
                });

                graph.draw();

            <?php } ?>

        })
    </script>
<?php } ?>