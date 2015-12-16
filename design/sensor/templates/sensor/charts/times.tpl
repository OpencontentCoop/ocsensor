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
        var getChart = function(){
            $.getJSON('{/literal}{'sensor/data'|ezurl(no)}{literal}', getVars, function (response) {
                $('#times').highcharts('StockChart', {
				  rangeSelector: {
					  selected: 1
				  },
				  yAxis: {
					  min: 0
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
							  enabled: true,
							  forced: true							  
						  }
					  }
				  },
				  series: response.series
			  });
            });
        };
        getChart();
    });
</script>
{/literal}

<div id="times" style="min-width: 310px; height: 800px; margin: 0 auto"></div>