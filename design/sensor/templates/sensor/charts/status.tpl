{ezscript_require( array( 'ezjsc::jquery', 'highcharts/charts/highcharts.js', 'highcharts/charts/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'status'
            }
        };
        var getChart = function () {
            getVars.parameters.filters = $('#chart-filters').serializeArray();
            $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
                $('#status').highcharts({
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: response.title
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                format: '{point.name}: {point.y:.1f}%'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
                    },
                    series: [{
                        name: response.title,
                        colorByPoint: true,
                        data: response.series
                    }]
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
        <div id="status" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    </div>
    <div class="col-md-2">
        {include uri='design:sensor/charts/_filters.tpl'}
    </div>
</div>
