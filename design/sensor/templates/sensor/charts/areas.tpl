{ezscript_require( array( 'ezjsc::jquery', 'highcharts/charts/highcharts.js', 'highcharts/charts/modules/exporting.js' ) )}
{literal}
<script type="text/javascript">
    $(function () {
        var getVars = {
            contentType: 'chart',
            parameters: {
                type: 'areas'
            }
        };
        var getChart = function(){
		  $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
			$('#areas').highcharts({
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
				  name: 'Punti sulla mappa',
				  colorByPoint: true,
				  data: response.series
			  }]
			});
		  });
        };
        getChart();
    });
</script>
{/literal}

<div id="areas" style="min-width: 310px; height: 400px; margin: 0 auto"></div>