{ezscript_require( array( 'ezjsc::jquery', 'highcharts/charts/highcharts.js', 'highcharts/charts/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'timesAvg'
            }
        };
        var getChart = function () {
            getVars.parameters.filters = $('#chart-filters').serializeArray();
            $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
                $('#timesAvg').highcharts({
                    chart: {
                        type: 'area'
                    },
                    xAxis: {
                        categories: response.categories,
                        tickmarkPlacement: 'on',
                        title: {
                            enabled: false
                        }
                    },
                    tooltip: {
                        shared: true
                    },
                    plotOptions: {
                        area: {
                            stacking: 'normal',
                            lineColor: '#666666',
                            lineWidth: 1,
                            marker: {
                                lineWidth: 1,
                                lineColor: '#666666'
                            }
                        }
                    },
                    title: {
                        text: response.title
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
        <div id="timesAvg" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    </div>
    <div class="col-md-2">
        {include uri='design:sensor/charts/_filters.tpl'}
    </div>
</div>