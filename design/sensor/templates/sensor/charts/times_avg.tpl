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
        var getChart = function(){
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
    });
</script>
{/literal}

<div id="timesAvg" style="min-width: 310px; height: 400px; margin: 0 auto"></div>