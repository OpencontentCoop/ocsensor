{literal}
<script type="text/javascript">
    $(document).ready(function (){
        $('#chart').sensorChart({
            filters: ['group', 'interval', 'taggroup', 'usergroup'],
            enableDailyInterval: true,
            enableRangeFilter: ['daily','weekly'],
            rangeMax: {days: 180},
            load: function (chart, params){
                var spinner = $('#spinner').html();
                chart.html(spinner);
                params.format = 'highcharts';
                $.getJSON('/api/sensor/stats/' + chart.data('identifier'), params, function (response) {
                    $.each(response, function () {
                        this.config.exporting = getExportingConfig();
                        this.config.plotOptions.column.dataLabels.formatter = function(){
                            return (this.y !== 0) ? this.y : '';
                        }
                        chart.highcharts(this.config);
                    })
                });
            }
        });
    })
</script>
{/literal}

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>
