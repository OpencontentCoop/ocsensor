{ezscript_require( array('highcharts/pareto.js' ))}
{literal}
<script type="text/javascript">
    $(document).ready(function (){
        $('#chart').sensorChart({
            filters: ['area', 'category', 'group', 'taggroup'],
            enableRangeFilter: true,
            rangeMax: false,
            load: function (chart, params){
                chart.html($('#spinner').html());
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                    var series = [{
                        type: 'pareto',
                        name: 'Pareto',
                        yAxis: 1,
                        zIndex: 10,
                        baseSeries: 3,
                        tooltip: {
                            valueDecimals: 2,
                            valueSuffix: '%'
                        }
                    }];
                    $.each(response.series, function () {
                        var seriesItem = this;
                        var item = {
                            name: seriesItem.name,
                            type: 'column',
                            yAxis: 0,
                            zIndex: 2,
                            visible: seriesItem.name !== 'Totale',
                            showInLegend: seriesItem.name !== 'Totale',
                            data: []
                        };
                        $.each(seriesItem.data, function () {
                            if (this.interval !== 'all') {
                                item.data.push([this.interval, this.count]);
                            }
                        });
                        series.push(item);
                    });
                    var categories = response.intervals;

                    chart.highcharts({
                        chart: {
                            type: 'column'
                        },
                        xAxis: {
                            categories: categories,
                            tickmarkPlacement: 'on',
                            title: {
                                enabled: false
                            }
                        },
                        yAxis: [{
                            min: 0,
                            title: {
                                text: '{/literal}{'Numero'|i18n('sensor/chart')}{literal}'
                            },
                            alignTicks: false,
                            gridLineWidth: 0,
                            stackLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                                }
                            }
                        },{
                            title: {
                                text: ''
                            },
                            minPadding: 0,
                            maxPadding: 0,
                            max: 100,
                            min: 0,
                            opposite: true,
                            alignTicks: false,
                            labels: {
                                format: "{value}%"
                            }
                        }],
                        tooltip: {
                            shared: true
                        },
                        plotOptions: {
                            column: {
                                stacking: 'normal',
                                dataLabels: {
                                    enabled: true,
                                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                    style: {
                                        textShadow: '0 0 3px black'
                                    }
                                }
                            },
                            pareto: {
                                dataLabels: {
                                    enabled: true,
                                    format: "{point.y:.1f}"
                                }
                            }
                        },
                        title: {
                            text: chart.data('description')
                        },
                        series: series,
                        exporting: {
                            sourceWidth: 1500,
                            sourceHeight: 1000,
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
                                    }/*, {
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
                                    }*/]
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