{if $current.render_settings.highcharts.use_highstock}
    {def $requires = array(
        'highstock/highstock.js'
    )}
{else}
    {def $requires = array(
        'highcharts/highcharts.js',
        'highcharts/exporting.js'
    )}
{/if}
{ezscript_require($requires)}

{def $exportServer = false()}
{if ezini('HighchartsExport', 'Server', 'ocsensor.ini')|eq('enabled')}
    {set $exportServer = ezini('HighchartsExport', 'Uri', 'ocsensor.ini')}
{/if}

{literal}<script>
const timezone = new Date().getTimezoneOffset()
Highcharts.setOptions({
    global: {
        timezoneOffset: timezone
    },
    lang: {
        loading: 'Sto caricando...',
        months: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
        weekdays: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'],
        shortMonths: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
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
        viewFullscreen: 'Visualizza a schermo intero',
        thousandsSep: ".",
        decimalPoint: ','
    }
});
var getExportingConfig = function (width, height){
    var sourceWidth = width || 1500;
    var sourceHeight = height || 800;
    return {
        sourceWidth: 1500,
        sourceHeight: 800,
        {/literal}{if $exportServer}url: "{$exportServer|wash()}",{/if}{literal}
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
                }, 'separator' , {
                    textKey: 'viewFullscreen',
                    onclick: function () {
                        this.fullscreen.toggle();
                    }
                }]
            }
        }
    }
};
</script>{/literal}

{include uri=concat('design:sensor_api_gui/charts/highcharts/',$current.identifier, '.tpl') stat=$current}
