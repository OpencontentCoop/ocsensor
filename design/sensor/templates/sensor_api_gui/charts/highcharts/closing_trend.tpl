{literal}
<script type="text/javascript">

    $(document).ready(function () {
        $('#chart').sensorChart({
            filters: [],
            load: function (chart, params) {
                chart.html($('#spinner').html());
                params.format = 'highcharts';
                $.getJSON('/api/sensor/stats/' + chart.data('identifier'), params, function (response) {
                    $.each(response, function () {
                        Highcharts.stockChart('chart', this.config);
                        if (this.hasOwnProperty('filterLegend') && this.filterLegend.hasOwnProperty('categories')){
                            var editorHelper = $('#editor-helper');
                            var helpList = $('<ul class="list-group" style="margin-top: 20px"></ul>').appendTo(editorHelper);
                            helpList.append('<li class="list-group-item" style="font-weight: bold">Lista degli id delle categorie per la preselezione <code>&category[]=...&category[]=...<id></code></li>');
                            $.each(this.filterLegend.categories, function (){
                                helpList.append('<li class="list-group-item"><span class="pull-right" style="font-weight: bold">'+ this.id+ '</span> ' + this.name+'</li>');
                            })
                        }
                    })
                });
            }
        });
    })
</script>
{/literal}

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 1000px; margin: 0 auto"></div>
