{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#chart').sensorChart({
                filters: ['area', 'category'],
                load: function (chart, params) {
                    chart.html($('#spinner').html());
                    $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                        var series = [];
                        $.each(response.series, function () {
                            series.push({
                                name: this.status + ' ' + this.count,
                                y: this.percentage
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
                                        format: '{point.name}'
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
                            },
                            series: [{
                                name: chart.data('name'),
                                colorByPoint: true,
                                data: series
                            }],
                            exporting: {
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
                    });
                }
            });
        })
    </script>
{/literal}
<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     style="min-width: 310px; height: 400px; margin: 0 auto"></div>

