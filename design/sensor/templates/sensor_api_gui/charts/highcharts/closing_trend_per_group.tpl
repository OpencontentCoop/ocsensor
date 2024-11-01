{literal}
<script type="text/javascript">

    $(document).ready(function () {
        $('#chart').sensorChart({
            filters: ['group'],
            load: function (chart, params) {
                chart.html($('#spinner').html());
                params.format = 'highcharts';
                $.getJSON('/api/sensor/stats/' + chart.data('identifier'), params, function (response) {
                    $.each(response, function () {
                        Highcharts.stockChart('chart', this.config);
                        // if (this.hasOwnProperty('filterLegend') && this.filterLegend.hasOwnProperty('groups')){
                        //     var editorHelper = $('#editor-helper');
                        //     var helpList = $('<ul class="list-group" style="margin-top: 20px"></ul>').appendTo(editorHelper);
                        //     helpList.append('<li class="list-group-item" style="font-weight: bold">'+$.sensorTranslate.translate('List of category ids for api selection')+' <code>&group[]=...&group[]=...<id></code></li>');
                        //     $.each(this.filterLegend.groups, function (){
                        //         helpList.append('<li class="list-group-item"><span class="pull-right" style="font-weight: bold">'+ this.id+ '</span> ' + this.name+'</li>');
                        //     })
                        // }
                    })
                });
            }
        });
    })
</script>
{/literal}

<div id="chart" data-identifier="{$current.identifier}" data-name="{$current.name|wash()}"
     data-description="{$current.description|wash()}" style="min-width: 310px; height: 1000px; margin: 0 auto"></div>
