{literal}
<script type="text/javascript">

    $(document).ready(function () {
        $('#chart').sensorChart({
            filters: [],
            load: function (chart, params) {
                chart.html($('#spinner').html());
                $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {

                    Highcharts.stockChart('chart', {
                        tooltip: {
                            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}%</b><br/>',
                            valueDecimals: 0
                        },
                        rangeSelector: {
                            selected: 5,
                            buttons: [{
                                type: 'week',
                                count: 1,
                                text: '1w',
                                title: '1 settimana'
                            },{
                                type: 'month',
                                count: 1,
                                text: '1m',
                                title: '1 mese'
                            }, {
                                type: 'month',
                                count: 3,
                                text: '3m',
                                title: '3 mesi'
                            }, {
                                type: 'month',
                                count: 6,
                                text: '6m',
                                title: '6 mesi'
                            }, {
                                type: 'year',
                                count: 1,
                                text: '1a',
                                title: '1 anno'
                            }, {
                                type: 'all',
                                text: 'Tutto',
                                title: 'Tutto'
                            }]
                        },
                        plotOptions: {
                            line: {
                                dataLabels: {
                                    enabled: true,
                                    color: 'black'
                                }
                            },
                            series: {
                                showInNavigator: true
                            }
                        },
                        legend: {
                            enabled:true,
                            alignColumns:false
                        },
                        title: {
                            text: chart.data('description')
                        },
                        yAxis: {
                            max: 100,
                            min: 0,
                            labels: {
                                formatter: function () {
                                    return this.value + '%';
                                }
                            },
                            plotLines: [{
                                value: 0,
                                width: 2,
                                color: 'silver'
                            }]
                        },
                        scrollbar: {
                            enabled: false
                        },
                        series: response.series
                    });
                });
            }
        });
    })
</script>
{/literal}

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 1000px; margin: 0 auto"></div>