jQuery(document).ready(function ($) {
    'use strict';

    if ($('.eib2bpro-charts').length > 0) {
        $.each($('.eib2bpro-charts'), function (k, item) {
            var eiOptions = {
                series: $(item).data('series'),
                chart: {
                    height: $(item).data('height'),
                    type: $(item).data('type'),
                    animations: {
                        enabled: true
                    },
                    sparkline: {
                        enabled: $(item).data('sparkline'),
                    },
                    events: {
                        dataPointMouseEnter: function (event, chartContext, config) {
                            jQuery('.carousel-item.active', document).removeClass('active');
                            jQuery('.carousel-item[data-id=' + config.dataPointIndex + ' ]', document).addClass('active');

                        }
                    }
                },
                plotOptions: {
                    bar: {
                        vertical: false,
                        horizontal: false,
                        columnWidth: '70%',
                        endingShape: 'rounded'
                    },
                },
                markers: {
                    size: 0
                },
                grid: {
                    show: false
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 0,
                    curve: 'smooth',
                    colors: ['#008ffb']
                },
                colors: ["#008ffb", "#50c79b", "#70cac2"],
                xaxis: {
                    categories: $(item).data('labels'),
                    labels: {
                        offsetY: -20,
                        offsetX: 17
                    }
                },
                legend: {
                    show: false
                },
                yaxis: {
                    show: false,
                    decimalsInFloat: 2
                },
                fill: {
                    opacity: 0.8,
                    type: 'solid'
                }
            };
            var eiChart = new ApexCharts(item, eiOptions);
            eiChart.render();
        });
    }
});