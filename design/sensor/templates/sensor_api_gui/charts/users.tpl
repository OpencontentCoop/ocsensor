{literal}
<script type="text/javascript">
    $(document).ready(function () {
        $('#chart').sensorChart({
            enableDailyInterval: true,
            filters: ['interval'],
            enableRangeFilter: ['daily','weekly','monthly'],
            rangeMax: false,
            load: function (chart, params) {
                chart.html($('#spinner').html());
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                    var data = [];
                    $.each(response.series, function () {
                        $.each(this.data, function () {
                            data.push([this.interval * 1000, this.count]);
                        });
                    });
                    chart.highcharts({
                        chart: {
                            type: 'column'
                        },
                        xAxis: {
                            type: 'datetime',
                            ordinal: false
                        },
                        yAxis: {
                            allowDecimals: false,
                            min: 0,
                            title: {
                                text: '{/literal}{'Numero'|i18n('sensor/chart')}{literal}'
                            }
                        },
                        plotOptions: {
                            column: {
                                dataLabels: {
                                    enabled: true
                                }
                            },
                            series: {
                                marker: {
                                    enabled: true
                                }
                            }
                        },
                        title: {
                            text: chart.data('description')
                        },
                        series: [{
                            name: chart.data('description'),
                            data: data
                        }],
                        legend: {
                            enabled: false
                        },
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
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>