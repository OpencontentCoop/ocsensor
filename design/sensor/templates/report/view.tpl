{def $social_pagedata = social_pagedata()}
{def $css = array('reveal.css', 'reveal-white.css')}
{if sensor_settings('UseStatCalculatedColor')|not()}
    {set $css = $css|append('highcharts.css')}
{/if}
{ezcss_require($css)}
{ezscript_require(array('highstock/highstock.js', 'highcharts/pareto.js', 'reveal.js'))}
<div class="reveal">
    <div class="slides">
        <section>
            <img src="{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90" width="90">
            <h3>{attribute_view_gui attribute=$report|attribute('title')}</h3>
            {attribute_view_gui attribute=$report|attribute('intro')}
        </section>
        {foreach $items as $index => $item}
            <section data-background-image="{$social_pagedata.logo_path|ezroot(no)}" data-background-size="100px" data-background-position="top left">
                <h4>{attribute_view_gui attribute=$item|attribute('title')}</h4>
                <div class="slide-content row">
                    {def $has_text = cond($item|has_attribute('text'), true(), false())}
                    {def $has_link = cond($item|has_attribute('link'), true(), false())}
                    {if $has_text}
                        {def $text_length = $item|attribute('text').content.output.output_text|strip_tags()|count_chars()}
                        <div class="slide-content-item slide-text col-md-{if $has_link}6{else}12 text-center{/if} {if $text_length|gt(80)} r-fit-text{/if}">
                            {attribute_view_gui attribute=$item|attribute('text')}
                        </div>
                        {undef $text_length}
                    {/if}
                    {if $has_link}
                        <div class="slide-content-item slide-chart col-md-{if $has_text}6{else}12{/if}">
                            <div class="chart row" data-slide="{$item.contentobject_id}"></div>
                        </div>
                    {/if}
                    {undef $has_text $has_link}
                </div>
            </section>
        {/foreach}
    </div>
</div>
{literal}
<script>
    const timezone = new Date().getTimezoneOffset()
    Highcharts.setOptions({
        global: {
            timezoneOffset: timezone
        },
        lang: {
            loading: 'Sto caricando...',
            months: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
            weekdays: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Venerdì', 'Sabato', 'Domenica'],
            shortMonths: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lugl', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
            exportButtonTitle: "Esporta",
            printButtonTitle: "Importa",
            rangeSelectorFrom: "Da",
            rangeSelectorTo: "A",
            rangeSelectorZoom: "Periodo",
            downloadPNG: 'Download immagine PNG',
            downloadJPEG: 'Download immagine JPEG',
            downloadPDF: 'Download documento PDF',
            downloadSVG: 'Download immagine SVG',
            printChart: 'Stampa grafico',
            thousandsSep: ".",
            decimalPoint: ','
        }
    });
    $(document).ready(function (){
        $('.chart').each(function (){
            var container = $(this);
            var slide = container.data('slide');
            $.getJSON('{/literal}{concat('/sensor/report/', $report.remote_id)|ezurl(no)}{literal}/s/'+slide, function (response) {
                var chartLength = response.length;
                if (chartLength > 1){
                    container.parents('.slide-content').find('.slide-content-item').removeClass('col-md-6').removeClass('r-fit-text').removeAttr('style').addClass('col-md-12');
                }
                var chartClass = 12/chartLength;
                $.each(response, function (){
                    var chart = $('<div id="s-'+slide+'" class="col-md-'+chartClass+'" style="min-width: 310px; height: 500px; margin: 0 auto"></div>').appendTo(container);
                    this.config.title.text = '';
                    if (this.type === 'stockChart'){
                        Highcharts.stockChart('s-'+slide, this.config);
                    }else {
                        chart.highcharts(this.config);
                    }
                })
            });
        });
        Reveal.initialize({
            center: true,
            history: false
        });
    });
</script>
{/literal}