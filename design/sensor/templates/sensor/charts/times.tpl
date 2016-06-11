{ezscript_require( array( 'ezjsc::jquery', 'highcharts/stock/highstock.src.js', 'highcharts/stock/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'times'
            }
        };
        var getChart = function () {
            getVars.parameters.filters = $('#chart-filters').serializeArray();
            $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
                $('#times').highcharts('StockChart', {
                    rangeSelector: {
                        selected: 1
                    },
                    yAxis: {
                        min: 0
                    },
                    tooltip: {
                        valueSuffix: {/literal}{' giorni'|i18n('sensor/chart')}{literal},
                        valueDecimals: 1
                    },
                    title: {
                        text: response.title
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: false,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: '0 0 3px black, 0 0 3px black'
                                }
                            },
                            dataGrouping: {
                                enabled: true
                            }
                        }
                    },
                    series: response.series
                });
            });
        };
        getChart();
        $('#chart-filters input').bind('change', function () {
            getChart();
        })
    });
</script>
{/literal}

<div class="row">
    <div class="col-md-10">
        <div id="times" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    </div>
    <div class="col-md-2">
        {include uri='design:sensor/charts/_filters.tpl'}
    </div>
</div>