{ezscript_require( array( 'ezjsc::jquery', 'highcharts/stock/highstock.js', 'highcharts/stock/highcharts-more.js', 'highcharts/stock/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'performance'
            }
        };
        var getChart = function(){
            $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
                $('#performance').highcharts('StockChart', {
                    chart: {
                        type: 'arearange'
                    },
                    rangeSelector: {
                        selected: 1
                    },
                    tooltip: {
                        valueSuffix: ' ore',
                        valueDecimals: 2
                    },
                    title: {
                        text: response.title
                    },
                    series: [{
                        name: response.seriesName,
                        data: response.data
                    }],
                    yAxis:{
                        min: 0
                    }
                });
            });
        };
        getChart();
    });
</script>
{/literal}

<div id="performance" style="min-width: 310px; height: 400px; margin: 0 auto"></div>