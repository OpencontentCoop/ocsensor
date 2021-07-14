{literal}
<script type="text/javascript">
    $(document).ready(function (){
        var isPieLoaded = false;
        $('#chart').sensorChart({
            filters: ['type', 'area', 'category', 'interval', 'group'],
            enableDailyInterval: true,
            enableRangeFilter: ['daily','weekly'],
            rangeMax: false,
            load: function (chart, params){
                var spinner = $('#spinner').html();
                var pieChart = $('#pie-chart');
                chart.html(spinner);
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                    var series = [];
                    var pieData = [];
                    $.each(response.series, function () {
                        var seriesItem = this;
                        var item = {
                            name: seriesItem.name,
                            color: seriesItem.color,
                            data: []
                        };
                        $.each(seriesItem.data, function () {
                            if (this.interval !== 'all') {
                                item.data.push([this.interval * 1000, this.count]);
                            }else{
                                pieData.push({
                                    name: seriesItem.name,
                                    color: seriesItem.color,
                                    y: this.count
                                })
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
                    if (!isPieLoaded) {
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
                            accessibility: {
                                point: {
                                    valueSuffix: '%'
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y} - {point.percentage:.1f}%</b><br/>'
                            },
                            plotOptions: {
                                pie: {
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        format: '{point.y} - {point.percentage:.1f}%'
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
                        isPieLoaded = true;
                    }
                    chart.highcharts({
                        chart: {
                            type: 'column'
                            // type: 'column',
                            // zoomType: 'x',
                            // resetZoomButton: {
                            //     position: {
                            //         align: 'left'
                            //     }
                            // }
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
                            shared: true,
                            dateTimeLabelFormats: {
                                day: '%b %e, %Y',
                                hour: '%b %e %Y',
                                millisecond: '%b %e %Y',
                                minute: '%b %e %Y',
                                month: '%B %Y',
                                second: '%b %e %Y',
                                week: '%b %e, %Y',
                                year: '%Y'
                            },
                            pointFormat: '<span style="color:{point.color}">{series.name}</span>: <b>{point.y} - {point.percentage:.1f}%</b><br/>'
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