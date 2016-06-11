{ezscript_require( array( 'ezjsc::jquery', 'highcharts/charts/highcharts.js', 'highcharts/charts/modules/data.js', 'highcharts/charts/modules/drilldown.js', 'highcharts/charts/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'categoriesDrilldown'
            }
        };
        var getChart = function () {
            getVars.parameters.filters = $('#chart-filters').serializeArray();
            $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (responseArray) {
                $('#categories').empty();
                $.each(responseArray, function(){
                    var response = this;
                    var chartContainer = $('<div style="min-width: 310px; height: 400px; margin: 0 auto"></div>')
                    $('#categories').append(chartContainer);
                    chartContainer.highcharts({
                        chart: {
                            type: 'pie'
                        },
                        lang:{
                            drillUpText: 'Vedi tutte'
                        },
                        title: {
                            text: response.title
                        },
                        subtitle: {
                            text: 'Clicca sulle aree per il dettaglio dei descrittori.'
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
                            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.count}</b> segnalazioni<br/>'
                        },
                        series: [{
                            name: 'Aree',
                            colorByPoint: true,
                            data: response.series
                        }],
                        drilldown: {
                            series: response.drilldown
                        },
                        exporting:{
                          sourceWidth: 1000,
                          sourceHeight: 500,
                        }
                    });
                });
            });
        };
        $(document).on('sensor:charts:filterchange', '#chart-filters', function () {
            getChart();
        });
        $('#chart-filters').trigger('sensor:charts:filterchange');
    });
</script>
{/literal}


<form id="chart-filters">
  {include uri='design:sensor/charts/filters/interval.tpl' prechecked="half-yearly"}
</form>

<div id="categories"></div>