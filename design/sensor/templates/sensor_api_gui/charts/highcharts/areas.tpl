{literal}
<script type="text/javascript">
    $(document).ready(function () {
        $('#chart').sensorChart({
            enableDailyInterval: true,
            enableRangeFilter: ['daily','weekly','monthly'],
            load: function (chart, params) {
                var pie = $('#pie-chart');
                var spinner = $('#spinner').html();
                chart.html(spinner);
                pie.html('');
                params.format = 'highcharts';
                $.getJSON('/api/sensor/stats/' + chart.data('identifier'), params, function (response) {
                    $.each(response, function () {
                        this.config.exporting = getExportingConfig();
                        if (this.config.chart.type === 'pie') {
                            pie.highcharts(this.config);
                        } else {
                            chart.highcharts(this.config);
                        }
                    })
                });
            }
        });
    })
</script>
{/literal}

<div id="pie-chart" style="min-width: 310px; max-height: 800px; margin: 0 auto"></div>
<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>