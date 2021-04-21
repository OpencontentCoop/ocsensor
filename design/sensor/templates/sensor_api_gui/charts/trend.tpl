{literal}
<script type="text/javascript">
    $(document).ready(function () {
        $('#chart').sensorChart({
            filters: ['area', 'maincategory', 'group', 'interval'],
            enableDailyInterval: true,
            enableRangeFilter: ['daily','weekly','monthly'],
            rangeMax: false,
            load: function (chart, params) {
                chart.html($('#spinner').html());
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                    var series = [];
                    $.each(response.series, function () {
                        var seriesItem = this;
                        var item = {
                            name: seriesItem.name,
                            data: []
                        };
                        $.each(seriesItem.data, function () {
                            if (this.interval !== 'all') {
                                item.data.push([this.interval * 1000, this.count]);
                            }
                        });
                        series.push(item);
                    });
                    chart.highcharts({
                        chart: {
                            type: 'column'
                        },
                        xAxis: {
                            type: 'datetime',
                            ordinal: false,
                            tickmarkPlacement: 'on'
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
                                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'black'
                                }
                            }
                        },
                        title: {
                            text: chart.data('description')
                        },
                        series: series,
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
                });
            }
        });
    })
</script>
{/literal}

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>