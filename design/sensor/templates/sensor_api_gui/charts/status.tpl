{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#chart').sensorChart({
                filters: ['type', 'area', 'category', 'group'],
                load: function (chart, params) {
                    chart.html($('#spinner').html());
                    var barChart = $('#bar-chart');
                    barChart.html('');
                    $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                        var series = [];
                        var barSeries = [];
                        $.each(response.series, function () {
                            series.push({
                                name: this.status + ' ' + this.count,
                                y: this.percentage,
                                color: this.color
                            });
                            barSeries.push({
                                name: this.status,
                                data: [this.count],
                                color: this.color
                            });
                        });
                        chart.highcharts({
                            chart: {
                                type: 'pie'
                            },
                            title: {
                                text: chart.data('name')
                            },
                            plotOptions: {
                                series: {
                                    dataLabels: {
                                        enabled: true,
                                        format: '{point.name}: {point.y:.2f}%'
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b><br/>'
                            },
                            series: [{
                                name: chart.data('name'),
                                colorByPoint: true,
                                data: series
                            }],
                            exporting: {
                                sourceWidth: 1500,
                                sourceHeight: 800,
                                buttons: {
                                    contextButton: {
                                        menuItems: [{
                                            textKey: 'downloadPNG',
                                            onclick: function () {
                                                this.exportChart();
                                            }
                                        }, {
                                            textKey: 'downloadJPEG',
                                            onclick: function () {
                                                this.exportChart({
                                                    type: 'image/jpeg'
                                                });
                                            }
                                        }, {
                                            textKey: 'downloadPDF',
                                            onclick: function () {
                                                this.exportChart({
                                                    type: 'application/pdf'
                                                });
                                            }
                                        }, {
                                            textKey: 'downloadSVG',
                                            onclick: function () {
                                                this.exportChart({
                                                    type: 'image/svg+xml'
                                                });
                                            }
                                        }, {
                                            separator: true
                                        }, {
                                            text: 'Vedi segnalazioni',
                                            onclick: function () {
                                                var queryData = [];
                                                $.each(params, function (key, value) {
                                                    queryData.push({name: key, value: value});
                                                })
                                                window.location = '/sensor/posts#' + $.param(queryData);
                                            }
                                        }]
                                    }
                                }
                            }
                        });
                        barChart.highcharts({
                            chart: {
                                type: 'column'
                            },
                            xAxis: {
                                categories: [chart.data('name')],
                                title: {
                                    text: ''
                                }
                            },
                            yAxis: {
                                allowDecimals: false,
                                min: 0,
                                title: {
                                    text: '{/literal}{'Numero'|i18n('sensor/chart')}{literal}'
                                }
                            },
                            tooltip: {
                                shared: true
                            },
                            plotOptions: {
                                column: {
                                    dataLabels: {
                                        enabled: true,
                                    }
                                }
                            },
                            title: {
                                text: ''
                            },
                            series: barSeries
                        });
                    });
                }
            });
        })
    </script>
{/literal}
<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     style="min-width: 310px; height: 400px; margin: 0 auto"></div>
<div id="bar-chart" style="max-height: 800px; margin: 0 auto"></div>
