{literal}
<script type="text/javascript">
    $(document).ready(function (){
        $('#chart').sensorChart({
            enableDailyInterval: true,
            enableRangeFilter: ['daily','weekly'],
            load: function (chart, params){
                var spinner = $('#spinner').html();
                var pieChart = $('#pie-chart');
                chart.html(spinner);
                pieChart.html('');
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                    var series = [];
                    var pieData = [];
                    $.each(response.series, function () {
                        var seriesItem = this;
                        var item = {
                            name: seriesItem.name,
                            data: []
                        };
                        $.each(seriesItem.data, function () {
                            if (this.interval !== 'all') {
                                item.data.push([this.interval * 1000, this.count]);
                            }else{
                                pieData.push({name: seriesItem.name, y: this.count})
                            }
                        });
                        series.push(item);
                        if (response.series.length === 1) {
                            $.each(seriesItem.series, function () {
                                var seriesChild = this;
                                var itemChild = {
                                    name: seriesChild.name,
                                    data: []
                                };
                                $.each(seriesChild.data, function () {
                                    if (this.interval !== 'all') {
                                        itemChild.data.push([this.interval * 1000, this.count]);
                                    }
                                });
                                series.push(itemChild);
                            });
                        }
                    });
                    pieChart.highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            type: 'pie'
                        },
                        title: {
                            text: chart.data('description')
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        accessibility: {
                            point: {
                                valueSuffix: '%'
                            }
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                }
                            }
                        },
                        series: [{
                            name: chart.data('name'),
                            colorByPoint: true,
                            data: pieData
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
                                    }]
                                }
                            }
                        }
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
                            },
                            stackLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                                }
                            }
                        },
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
                            }
                        },
                        title: {
                            text: ''
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

<div id="pie-chart" style="min-width: 310px; max-height: 800px; margin: 0 auto"></div>
<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>