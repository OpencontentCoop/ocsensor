{literal}
<script type="text/javascript">
    $(document).ready(function (){
        google.charts.setOnLoadCallback(function (){
        $('#chart').sensorChart({
            enableDailyInterval: true,
            enableRangeFilter: ['daily','weekly'],
            load: function (chart, params) {
                var pie = $('#pie-chart');
                var spinner = $('#spinner').html();
                chart.html(spinner);
                pie.html('');
                params.format = 'table';
                $.getJSON('/api/sensor/stats/' + chart.data('identifier'), params, function (response) {
                    $.each(response, function () {
                        $.each(response, function (i,v){
                            var data, options, gchart;
                            if (i === 0) {
                                data = google.visualization.arrayToDataTable(v, true);
                                options = {
                                    legend: { position: "right" }
                                };
                                gchart = new google.visualization.PieChart(document.getElementById('pie-chart'));
                            }else{
                                data = google.visualization.arrayToDataTable(v);
                                options = {
                                    legend: { position: "bottom"},
                                    bar: { groupWidth: '75%' },
                                    isStacked: true
                                };
                                gchart = new google.visualization.ColumnChart(document.getElementById('chart'));
                            }
                            gchart.draw(data, options);
                        })
                    })
                });
            }
        });
        });
    })
</script>
{/literal}

<h3 class="text-center">{$current.description|wash()}</h3>
<div id="pie-chart" style="width: 100%; height: 500px; margin: 0 auto"></div>
<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>