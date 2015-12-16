{ezscript_require( array( 'ezjsc::jquery', 'highcharts/charts/highcharts.js', 'highcharts/charts/modules/data.js', 'highcharts/charts/modules/drilldown.js', 'highcharts/charts/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'categories'
            }
        };
        var getChart = function(){
		  $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
			$('#categories').highcharts({
			  chart: {
				  type: 'pie'
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
				  pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> del totale<br/>'
			  },
			  series: [{
				  name: 'Aree',
				  colorByPoint: true,
				  data: response.series
			  }],
			  drilldown: {
				  series: response.drilldown
			  }
			});
		  });
        };
        getChart();
    });
</script>
{/literal}

<div id="categories" style="min-width: 310px; height: 400px; margin: 0 auto"></div>