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

{literal}<script>
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
var getExportingConfig = function (width, height){
    var sourceWidth = width || 1500;
    var sourceHeight = height || 800;
    return {
        sourceWidth: 1500,
        sourceHeight: 800,
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
};
</script>{/literal}

{include uri=concat('design:sensor_api_gui/charts/highcharts/',$current.identifier, '.tpl') stat=$current}