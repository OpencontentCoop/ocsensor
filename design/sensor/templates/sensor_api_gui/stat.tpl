{ezcss_require(array(
    'select2.min.css',
    'iThing.css'
))}
{ezscript_require( array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'jQAllRangeSliders-withRuler-min.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
    'highcharts/charts/highcharts.js',
    'highcharts/charts/modules/exporting.js' )
)}
<section class="hgroup">
    <h1>{'Statistiche'|i18n('sensor/chart')}</h1>
</section>

<div class="row">

    <div class="col-md-3">
        <ul class="nav nav-pills nav-stacked">
            {foreach $list as $chart}
                <li role="presentation"
                    {if and( $current, $current.identifier|eq($chart.identifier))}class="active"{/if}>
                    <a href="{concat('sensor/stat/',$chart.identifier)|ezurl(no)}">
                        {$chart.name|wash()}
                    </a>
                </li>
            {/foreach}
            <li class="divider" style="border-bottom: 1px solid #ccc"></li>
            <li>
                <a id="download-csv" href="{'sensor/export/'|ezurl(no)}">
                    <i class="fa fa-download"></i> {"Esporta in formato CSV"|i18n('sensor/dashboard')}
                </a>
            </li>
            <li>
                <a href="{'sensor/openapi/'|ezurl(no)}">
                    <i class="fa fa-external-link-square"></i> {"Consulta in formato JSON"|i18n('sensor/dashboard')}
                </a>
            </li>
        </ul>
    </div>

    <div class="col-md-9">
        {if $current}
            <div id="chart-filters">
                <div class="row" id="posts-search">
                    <div class="col-md-4">
                        <div class="form-group hide" id="area-filter">
                            <label>{'Filtra per zona'|i18n('sensor/post')}</label>
                            <select class="select form-control" name="area" multiple>
                                {foreach $areas.children as $item}
                                    {*<option value="{$item.id}" style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>*}
                                    {foreach $item.children as $child}
                                        <option value="{$child.id}"
                                                style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                                    {/foreach}
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group hide" id="category-filter">
                            <label>{'Filtra per categoria'|i18n('sensor/post')}</label>
                            <select class="select form-control" name="" multiple>
                                {foreach $categories.children as $item}
                                    <option value="{$item.id}"
                                            style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>
                                    {foreach $item.children as $child}
                                        <option value="{$child.id}" style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                                    {/foreach}
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group hide" id="interval-filter">
                            <label>{'Filtra per Intervallo di tempo'|i18n('sensor/post')}</label>
                            <select class="select form-control" name="interval">
                                <option value="daily" class="daily_interval" disabled>{'Giornaliero'|i18n('sensor/chart')}</option>
                                <option value="weekly">{'Settimanale'|i18n('sensor/chart')}</option>
                                <option value="monthly">{'Mensile'|i18n('sensor/chart')}</option>
                                <option value="quarterly">{'Trimestrale'|i18n('sensor/chart')}</option>
                                <option value="half-yearly">{'Semestrale'|i18n('sensor/chart')}</option>
                                <option value="yearly" selected="selected">{'Annuale'|i18n('sensor/chart')}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row hide" id="range-filter">
                    <div class="col-md-3">
                        <div class="form-group" id="event-filter" style="margin-top: 15px;">
                            <label>{'Seleziona evento'|i18n('sensor/post')}</label>
                            <select class="form-control" name="event">
                                <option value="open">{'Creazione'|i18n('sensor/chart')}</option>
                                <option value="fix">{'Fine lavorazione'|i18n('sensor/chart')}</option>
                                <option value="close">{'Chiusura'|i18n('sensor/chart')}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div id="range-slider"></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane active" id="panel-{$current.identifier}">
                {include uri=concat('design:sensor_api_gui/charts/',$current.identifier, '.tpl') stat=$current}
            </div>
        {/if}
    </div>

</div>

<div id="spinner" class="hide">
    <div class="spinner text-center" style="position:absolute;width:100%;top:45%">
        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
        <span class="sr-only">Loading...</span>
    </div>
</div>
{def $posts_date_range = sensor_posts_date_range()}
{literal}
<script>
    ;(function ($, window, document, undefined) {
        'use strict';
        var pluginName = 'sensorChart',
            defaults = {
                filtersContainer: $('#chart-filters'),
                filters: ['area', 'category', 'interval'],
                enableDailyInterval: false,
                enableEventFilter: false,
                enableRangeFilter: false,
                load: function (){},
                months: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
                rangeMin: {days: 2},
                rangeMax: {days: 60},
                rangeDateMin: new Date('{/literal}{$posts_date_range['first']}{literal}'),
                rangeDateMax: new Date('{/literal}{$posts_date_range['last']}{literal}')
            };

        function Plugin(element, options, postId) {
            this.settings = $.extend({}, defaults, options);
            this.chartContainer = $(element);
            this.intervalFilter = this.settings.filtersContainer.find('#interval-filter');
            this.rangeFilter = this.settings.filtersContainer.find('#range-filter');
            this.downloadButton = $('#download-csv');
            this.downloadBaseHref = this.downloadButton.attr('href');
            var self = this;

            var initRange = function(){
                var rangeParameters = {
                    // arrows: false,
                    bounds: {
                        min: self.settings.rangeDateMin,
                        max: self.settings.rangeDateMax
                    },
                    defaultValues: {
                        min: self.settings.rangeDateMin,
                        max: self.settings.rangeDateMax
                    }
                };
                if (self.settings.rangeMax) {
                    rangeParameters.defaultValues = {
                        min: self.settings.rangeDateMax,
                        max: (new Date()).setDate(self.settings.rangeDateMax.getDate() - self.settings.rangeMax.days)
                    };
                    rangeParameters.range = {
                        min: self.settings.rangeMin,
                        max: self.settings.rangeMax
                    };
                }

                if (!self.rangeFilter.data('is_init') === true) {
                    self.settings.filtersContainer.find("#range-slider").dateRangeSlider(
                        rangeParameters
                    ).bind("valuesChanged", function (e, data) {
                        self.chartContainer.trigger('sensor:chart:filterchange');
                    });
                    if (self.settings.enableEventFilter) {
                        self.settings.filtersContainer.find('#event-filter select').select2();
                    }
                    self.rangeFilter.data('is_init', true);
                }
            };

            if (!this.settings.enableEventFilter){
                this.settings.filtersContainer.find('#event-filter').parent().remove();
                this.settings.filtersContainer.find("#range-slider").parent().removeClass('col-md-9').addClass('col-md-12');
            }

            if (this.settings.enableDailyInterval){
                this.intervalFilter.find('.daily_interval').removeAttr('disabled');
            }else{
                if (this.intervalFilter.find("select").val() === 'daily'){
                    this.intervalFilter.find("select").val('yearly').trigger('change');
                }
            }

            if (typeof this.settings.enableRangeFilter === 'object') {
                this.intervalFilter.find("select").on('change', function () {
                    var interval = $(this).val();
                    if ($.inArray(interval, self.settings.enableRangeFilter) > -1) {
                        self.rangeFilter.removeClass('hide');
                        initRange();
                    } else {
                        self.rangeFilter.addClass('hide');
                    }
                });
                if ($.inArray(this.intervalFilter.find("select").val(), this.settings.enableRangeFilter) > -1){
                    this.rangeFilter.removeClass('hide');
                    initRange();
                }
            }else if(this.settings.enableRangeFilter){
                this.rangeFilter.removeClass('hide');
                initRange();
            }

            $.each(this.settings.filters, function (){
                self.settings.filtersContainer.find('#'+this+'-filter').removeClass('hide');
            });

            this.settings.filtersContainer.find(".select").select2({
                templateResult: function (item) {
                    var style = item.element ? $(item.element).attr('style') : '';
                    return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
                }
            });

            this.settings.filtersContainer.find("select").on('change', function () {
                self.chartContainer.trigger('sensor:chart:filterchange');
            });

            this.chartContainer.on('sensor:chart:filterchange', function () {
                var params = {};
                $.each(self.settings.filters, function (){
                    params[this] = self.settings.filtersContainer.find('#'+this+'-filter').find('select').val()
                });
                if (self.settings.enableRangeFilter === true || $.inArray(params.interval, self.settings.enableRangeFilter) > -1) {
                    var dateValues = self.settings.filtersContainer.find("#range-slider").dateRangeSlider("values");
                    params['start'] = dateValues.min.toISOString();
                    params['end'] = dateValues.max.toISOString();
                    if (self.settings.enableEventFilter) {
                        params['event'] = self.settings.filtersContainer.find('#event-filter').find('select').val();
                    }
                }
                self.settings.load(self.chartContainer, params);
                var downloadQuery = [];
                $.each(params, function (key,val){
                    if (val && val.length > 0) {
                        downloadQuery.push({name: key, value: val});
                    }
                });
                self.downloadButton.attr('href', self.downloadBaseHref + '?' + $.param(downloadQuery));
            });

            self.chartContainer.trigger('sensor:chart:filterchange');
        }

        $.extend(Plugin.prototype, {});

        $.fn[pluginName] = function (options, postId) {
            return this.each(function () {
                if (!$.data(this, 'plugin_' + pluginName)) {
                    $.data(this, 'plugin_' +
                        pluginName, new Plugin(this, options, postId));
                }
            });
        };

    })(jQuery, window, document);

</script>
{/literal}