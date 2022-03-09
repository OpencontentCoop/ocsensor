{literal}
<script type="text/javascript">
    $(document).ready(function (){
        $('#chart').sensorChart({
            filters: ['maincategory', 'usergroup'],
            enableRangeFilter: true,
            rangeMax: false,
            load: function (chart, params){
                chart.html($('#spinner').html());
                params.format = 'highcharts';
                $.getJSON('/api/sensor/stats/' + chart.data('identifier'), params, function (response) {
                    $.each(response, function () {
                        this.config.exporting = getExportingConfig(1500, 1000);
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
