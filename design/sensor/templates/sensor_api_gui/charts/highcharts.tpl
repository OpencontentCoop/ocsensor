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
        months: $.sensorTranslate.translate('January_February_March_April_May_June_July_August_September_October_November_December').split('_'),
        weekdays: $.sensorTranslate.translate('Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday').split('_'),
        shortMonths: $.sensorTranslate.translate('Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec').split('_'),
        exportButtonTitle: $.sensorTranslate.translate('Export'),
        printButtonTitle: $.sensorTranslate.translate('Print'),
        rangeSelectorFrom: $.sensorTranslate.translate('From'),
        rangeSelectorTo: $.sensorTranslate.translate('To'),
        rangeSelectorZoom: $.sensorTranslate.translate('Time range'),
        downloadPNG: $.sensorTranslate.translate('Download PNG image'),
        downloadJPEG: $.sensorTranslate.translate('Download JPG image'),
        downloadPDF: $.sensorTranslate.translate('Download PDF document'),
        downloadSVG: $.sensorTranslate.translate('Download SVG'),
        printChart: $.sensorTranslate.translate('Print chart'),
        viewFullscreen: 'Visualizza a schermo intero',
        thousandsSep: ".",
        decimalPoint: ','
    }
})
var getPointDataLink = function (category, serie, identifier, params){
    console.log(category, serie, identifier, params)
    params._c = category;
    params._s = serie;
    var href = '/sensor/console/stats/' + identifier + '?' + $.param(params);
    let a = document.createElement('a');
    a.target= '_blank';
    a.href= href;
    a.click();
};
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
