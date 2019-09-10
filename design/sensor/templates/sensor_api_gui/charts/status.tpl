{literal}
<script type="text/javascript">
    $(function () {
        var chart = $('#chart');
        var areaFilter = $('#area-filter').removeClass('hide');
        var categoryFilter = $('#category-filter').removeClass('hide');
        var intervalFilter = $('#interval-filter');

        $.each([areaFilter, categoryFilter, intervalFilter], function () {
            this.find('select').on('change', function () {
                chart.trigger('sensor:chart:filterchange');
            });
        });

        chart.on('sensor:chart:filterchange', function () {
            loadChart();
        });

        var loadChart = function () {
            chart.html($('#spinner').html());
            var params = {
                'category': categoryFilter.find('select').val(),
                'area': areaFilter.find('select').val(),
                'interval': intervalFilter.find('select').val(),
            };
            $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                var series = [];
                $.each(response.series, function () {
                    series.push({
                        name: this.status+ ' ' + this.count,
                        y: this.percentage
                    });
                });
                chart.highcharts({
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: chart.data('name')
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                format: '{point.name}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
                    },
                    series: [{
                        name: chart.data('name'),
                        colorByPoint: true,
                        data: series
                    }]
                });
            });
        };
        loadChart();
    });
</script>
{/literal}

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}" style="min-width: 310px; height: 400px; margin: 0 auto"></div>