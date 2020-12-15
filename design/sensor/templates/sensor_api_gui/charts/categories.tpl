{literal}
<script type="text/javascript">
    $(function () {
        var chart = $('#chart');
        var areaFilter = $('#area-filter').removeClass('hide');
        var categoryFilter = $('#category-filter').removeClass('hide');
        var intervalFilter = $('#interval-filter').removeClass('hide');

        $(".select").select2({
            templateResult: function (item) {
                var style = item.element ? $(item.element).attr('style') : '';
                return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
            }
        });

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
                var categories = [];
                var series = [];
                $.each(response.intervals, function (index, value) {
                    if (value !== 'all') {
                        categories.push(value);
                    }
                });
                $.each(response.series, function () {
                    var seriesItem = this;
                    var item = {
                        name: seriesItem.name,
                        data: []
                    };
                    $.each(seriesItem.data, function () {
                        if (this.interval !== 'all') {
                            item.data.push(this.count);
                        }
                    });
                    series.push(item);
                    if (response.series.length === 1) {
                        $.each(seriesItem.series, function () {
                            var seriesChild = this;
                            var itemChild = {
                                name: seriesChild.name,
                                data: []
                            };
                            $.each(seriesChild.data, function () {
                                if (this.interval !== 'all') {
                                    itemChild.data.push(this.count);
                                }
                            });
                            series.push(itemChild);
                        });
                    }
                });
                chart.highcharts({
                    chart: {
                        type: 'column'
                    },
                    xAxis: {
                        categories: categories,
                        tickmarkPlacement: 'on',
                        title: {
                            enabled: false
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '{/literal}{'Numero'|i18n('sensor/chart')}{literal}'
                        },
                        stackLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                            }
                        }
                    },
                    tooltip: {
                        shared: true
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                                style: {
                                    textShadow: '0 0 3px black'
                                }
                            }
                        }
                    },
                    title: {
                        text: chart.data('description')
                    },
                    series: series,
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
                                }, {
                                    separator: true
                                }, {
                                    text: 'Vedi segnalazioni',
                                    onclick: function () {
                                        var queryData = [];
                                        $.each(params, function (key, value) {
                                            queryData.push({name: key, value: value});
                                        })
                                        window.location = '/sensor/posts#'+$.param(queryData);
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

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 800px; margin: 0 auto"></div>