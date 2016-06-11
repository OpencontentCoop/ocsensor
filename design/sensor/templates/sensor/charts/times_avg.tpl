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
							text: '{/literal}{'Giorni'|i18n('sensor/chart')}{literal}'
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
                        valueSuffix: '{/literal}{' giorni'|i18n('sensor/chart')}{literal}',
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
        $(document).on('sensor:charts:filterchange', '#chart-filters', function () {
            getChart();
        });
        $('#chart-filters').trigger('sensor:charts:filterchange');
    });
</script>
{/literal}

<div id="timesAvg" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<form id="chart-filters">
    <div class="row">
        <div class="col-md-6">
            {include uri='design:sensor/charts/filters/category.tpl'}
        </div>
        <div class="col-md-6">
            {include uri='design:sensor/charts/filters/interval.tpl'}
        </div>
    </div>
</form>
