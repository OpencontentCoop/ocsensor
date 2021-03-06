<section class="hgroup noborder">
    <div class="row">
        <div class="col-sm-12">
            {def $post_container = sensor_postcontainer()}
            {if $post_container|has_attribute( 'short_description' )}
            <div class="service_teaser vertical wow animated flipInX animated">
                {if $post_container|has_attribute( 'image' )}
                    <div class="service_photo">
                        <figure style="background-image:url({$post_container|attribute( 'image' ).content.original.full_path|ezroot(no)})"></figure>
                    </div>
                {/if}
                <div class="service_details">
                    <h2 class="section_header skincolored">
                        {$post_container.data_map.name.content|wash()}
                    </h2>
                    {attribute_view_gui attribute=$post_container.data_map.short_description}
                    {*<div id="sensorgraph" style="width: 100%; height: 500px; margin: 0 auto; padding: 10px;"></div>*}
                    {if $current_user.is_logged_in|not()}
                        <a href="#login"
                           class="btn btn-primary btn-lg btn-block">{'Accedi'|i18n('sensor/menu')}</a>
                    {else}
                        <a href="{'sensor/add'|ezurl(no)}"
                           class="btn btn-primary btn-lg btn-block">{'Segnala'|i18n('sensor/menu')}</a>
                    {/if}
                </div>
            </div>
            {/if}
        </div>
    </div>
</section>
{*
<script src="http://code.highcharts.com/highcharts.js"></script>
<script>
  {literal}
  $(function () {
    var colors = Highcharts.getOptions().colors,
        categories = [
          'Nessuna categoria',
          'Ambiente e territorio',
          'Commercio e negozi',
          'Cultura, turismo e tempo libero',
          'Lavori pubblici',
          'Lavoro',
          'Mobilità e trasporti',
          'Organizzazione e attività istituzionali',
          'Servizi al cittadino',
          'Sicurezza pubblica'
        ],
        data = [{
          y: 55.11,
          color: colors[0],
          drilldown: {
            categories: ['Aperti', 'Chiusi'],
            data: [10.10, 45.01],
            color: colors[0]
          }
        }, {
          y: 21.63,
          color: colors[1],
          drilldown: {
            categories: ['Aperti', 'Chiusi'],
            data: [3.20, 18.43],
            color: colors[1]
          }
        }, {
          y: 11.94,
          color: colors[2],
          drilldown: {
            categories: ['Aperti', 'Chiusi'],
            data: [3.12, 8.82],
            color: colors[2]
          }
        }, {
          y: 7.15,
          color: colors[3],
          drilldown: {
            categories: ['Aperti', 'Chiusi'],
            data: [4.55, 2.4],
            color: colors[3]
          }
        }, {
          y: 2.14,
          color: colors[4],
          drilldown: {
            categories: ['Aperti', 'Chiusi'],
            data: [ 0.12, 1.01],
            color: colors[4]
          }
        }],
        browserData = [],
        versionsData = [],
        i,
        j,
        dataLen = data.length,
        drillDataLen,
        brightness;


    // Build the data arrays
    for (i = 0; i < dataLen; i += 1) {

      // add browser data
      browserData.push({
        name: categories[i],
        y: data[i].y,
        color: data[i].color
      });

      // add version data
      drillDataLen = data[i].drilldown.data.length;
      for (j = 0; j < drillDataLen; j += 1) {
        brightness = 0.2 - (j / drillDataLen) / 5;
        versionsData.push({
          name: data[i].drilldown.categories[j],
          y: data[i].drilldown.data[j],
          color: Highcharts.Color(data[i].color).brighten(brightness).get()
        });
      }
    }

    // Create the chart
    $('#sensorgraph').highcharts({
      chart: {
        type: 'pie'
      },
      title: {
        text: ''
      },
      yAxis: {
        title: {
          text: 'Percentuale di risoluzione'
        }
      },
      plotOptions: {
        pie: {
          shadow: false,
          center: ['50%', '50%']
        }
      },
      tooltip: {
        valueSuffix: '%'
      },
      credits: {enabled: false},
      series: [{
        name: 'Categoria',
        data: browserData,
        size: '60%',
        dataLabels: {
          formatter: function () {
            return this.y > 5 ? this.point.name : null;
          },
          color: 'white',
          distance: -30
        }
      }, {
        name: 'Stato',
        data: versionsData,
        size: '80%',
        innerSize: '60%',
        dataLabels: {
          formatter: function () {
            // display only if larger than 1
            return this.y > 1 ? '<b>' + this.point.name + ':</b> ' + this.y + '%'  : null;
          }
        }
      }]
    });
  });
  {/literal}
</script>
*}
