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
                        type: 'column'
                    },
                    xAxis: {
                        categories: response.categories,
                        tickmarkPlacement: 'on',
                        title: {
                            enabled: false
                        }
                    },
					yAxis: {
						min: 0,
						title: {
							text: 'Giorni'
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
                        valueSuffix: ' giorni',
                        valueDecimals: 1
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