{literal}
<script type="text/javascript">
    $(function () {
        var chart = $('#chart');
        var intervalFilter = $('#interval-filter').removeClass('hide');

        $(".select").select2({
            templateResult: function (item) {
                var style = item.element ? $(item.element).attr('style') : '';
                return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
            }
        });

        $.each([intervalFilter], function () {
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
                'interval': intervalFilter.find('select').val(),
            };
            $.getJSON('/api/sensor_gui/stat/' + chart.data('identifier'), params, function (response) {
                var data = [];
                $.each(response.series, function () {
                    $.each(this.data, function () {
                        data.push([this.interval*1000, this.count]);
                    });
                });
                chart.highcharts({
                    chart: {
                        type: 'column'
                    },
                    xAxis: {
                        type: 'datetime',
                        ordinal: false
                    },
                    yAxis: {
                        allowDecimals: false,
                        min: 0,
                        title: {
                            text: '{/literal}{'Numero'|i18n('sensor/chart')}{literal}'
                        }
                    },
                    plotOptions: {
                        series: {
                            marker: {
                                enabled: true
                            }
                        }
                    },
                    title: {
                        text: chart.data('description')
                    },
                    series: [{
                        name: chart.data('description'),
                        data: data
                    }],
                    legend: {
                        enabled: false
                    },
                    exporting: {
                        buttons: {
                            contextButton: {
                                menuItems: [{
                                    textKey: 'downloadPNG',
                                    onclick: function () {
                                        this.exportChart();
                                    }
                                }, {
                                    textKey: 'downloadJPEG',
                                    onclick: function () {
                                        this.exportChart({
                                            type: 'image/jpeg'
                                        });
                                    }
                                }, {
                                    textKey: 'downloadPDF',
                                    onclick: function () {
                                        this.exportChart({
                                            type: 'application/pdf'
                                        });
                                    }
                                }, {
                                    textKey: 'downloadSVG',
                                    onclick: function () {
                                        this.exportChart({
                                            type: 'image/svg+xml'
                                        });
                                    }
                                }]
                            }
                        }
                    }
                });
            });
        };
        loadChart();
    });
</script>
{/literal}
<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}" data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>