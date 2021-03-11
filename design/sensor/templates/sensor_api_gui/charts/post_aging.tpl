{ezscript_require( array('highcharts/pareto.js' ))}
{literal}
<script type="text/javascript">
    $(document).ready(function (){
        $('#chart').sensorChart({
            filters: ['area', 'category', 'group'],
            load: function (chart, params){
                chart.html($('#spinner').html());
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                    var series = [];
                    $.each(response.series, function () {
                        var seriesItem = this;
                        var item = {
                            name: seriesItem.name,
                            type: 'column',
                            data: []
                        };
                        $.each(seriesItem.data, function () {
                            if (this.interval !== 'all') {
                                var count = this.count == 0 ? null : this.count;
                                item.data.push([this.interval, count]);
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
                        yAxis: {
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
                            text: chart.data('description')
                        },
                        legend: {
                            alignColumns:false
                        },
                        series: series,
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