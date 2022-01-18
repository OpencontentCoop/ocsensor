{ezpagedata_set('has_container', true())}
{ezcss_require(array(
    'select2.min.css',
    'iThing.css',
    'bootstrap-toggle.min.css'
))}
{ezscript_require(array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'jQAllRangeSliders-withRuler-min.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
    'moment-with-locales.min.js',
    'bootstrap-toggle.min.js'
))}

{def $engine = 'highcharts'}
<div class="container">
<section class="hgroup">
    <h1>{'Statistiche'|i18n('sensor/chart')}</h1>

    <div class="dropdown pull-right">
        <button class="btn btn-default dropdown-toggle" type="button" id="chartDropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            {foreach $list as $chart}
                {if and( $current, $current.identifier|eq($chart.identifier))}
                    {$chart.name|wash()}
                {/if}
            {/foreach}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="chartDropdownMenu">
            {foreach $list as $chart}
                <li {if and( $current, $current.identifier|eq($chart.identifier))}class="active"{/if}>
                    <a href="{concat('sensor/stat/',$chart.identifier)|ezurl(no)}">
                        {$chart.name|wash()}
                    </a>
                </li>
            {/foreach}
            <li role="separator" class="divider"></li>
            <li>
                <a id="download-csv" href="{if fetch('user', 'has_access_to', hash('module','sensor','function','manage'))}{'/sensor/dashboard/(export)'|ezurl(no)}{else}{'/sensor/export'|ezurl(no)}{/if}">
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

    <div class="col-md-12" style="padding-top: 40px;">
        {if $current}
            <div id="chart-filters">
                <div class="row" id="posts-search">
                    <div class="col-md-3 form-group hide" id="type-filter">
                        <label>{'Filtra per tipo'|i18n('sensor/post')}</label>
                        <select class="select form-control" name="type" multiple>
                            {foreach sensor_types() as $type}
                                <option value="{$type.identifier}">
                                    {$type.name|wash()}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-3 form-group hide" id="area-filter">
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
                    <div class="col-md-3 form-group hide" id="category-filter">
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
                    <div class="col-md-3 form-group hide" id="maincategory-filter">
                        <label>{'Filtra per macro categoria'|i18n('sensor/post')}</label>
                        <select class="select form-control" name="">
                            <option></option>
                            {foreach $categories.children as $item}
                                <option value="{$item.id}">{$item.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-3 form-group hide" id="interval-filter">
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
                    <div class="col-md-3">

                        <div class="form-group hide" id="group-filter">
                            <label>{'Filtra per gruppo di incaricati'|i18n('sensor/post')}</label>
                            <select class="select form-control" name="group" multiple>
                                {foreach $groups as $group => $items}
                                    {foreach $items as $item}
                                        <option value="{$item.id}">{$item.name|wash()}</option>
                                    {/foreach}
                                {/foreach}
                            </select>
                        </div>

                        {if $has_group_tag}
                        <div style="display: flex">
                            <div>
                        {/if}
                                <div class="form-group hide" id="groupwithtag-filter" data-field="group">
                                    <label>{'Filtra per gruppo di incaricati'|i18n('sensor/post')}</label>
                                    <select class="select form-control" name="group" multiple>
                                        {def $tag_group_id = 0}
                                        {foreach $groups as $group => $items}
                                            {if count($items)|gt(1)}
                                                {set $tag_group_id = $tag_group_id|inc()}
                                                <option value="{$tag_group_id}" style="font-weight: bold">{$group|wash()}</option>
                                            {/if}
                                            {foreach $items as $item}
                                                <option value="{$item.id}"{if count($items)|gt(1)} style="margin-left: 20px"{/if}>{$item.name|wash()}</option>
                                            {/foreach}
                                        {/foreach}
                                    </select>
                                </div>
                        {if $has_group_tag}
                            </div>
                            <div>
                                <div class="form-group hide" id="taggroup-filter" style="margin-left: 10px">
                                    <label>{'Raggruppa'|i18n('sensor/post')}</label>
                                    <input type="checkbox" data-toggleconfig />
                                </div>
                            </div>
                        </div>
                        {/if}
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
        {/if}
    </div>

</section>
</div>

<div class="container-fluid" style="padding: 0 40px">
<div class="row">
    {if $current}
    <div class="tab-pane active" id="panel-{$current.identifier}">
        {include uri=concat('design:sensor_api_gui/charts/',$engine, '.tpl') stat=$current}
        {if fetch('user', 'has_access_to', hash('module','*','function','*'))}
            <div id="editor-helper">
                <div class="input-group">
                    <input class="form-control form-control-sm" type="text" id="link" />
                    <a id="linkButton" href="#" class="input-group-addon btn-info">Copia link</a>
                </div>
            </div>
        {/if}
    </div>
    {/if}
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
                filters: ['type', 'area', 'category', 'interval'],
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
            this.settings.filtersContainer.find('[data-toggleconfig]').bootstrapToggle({
              on: 'SI',
              off: 'NO',
              onstyle: 'success'
            }).change(function(e) {
                self.chartContainer.trigger('sensor:chart:filterchange');
            });
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
                        min: (new Date()).setDate(self.settings.rangeDateMax.getDate() - self.settings.rangeMax.days),
                        max: self.settings.rangeDateMax
                    };
                    rangeParameters.range = {
                        min: self.settings.rangeMin,
                        max: self.settings.rangeMax
                    };
                }
                if (!self.rangeFilter.data('is_init') === true) {
                    self.settings.filtersContainer.find("#range-slider").dateRangeSlider(
                        rangeParameters
                    ).bind("userValuesChanged", function (e, data) {
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
                    var filterField = this;
                    var filterContainer = self.settings.filtersContainer.find('#'+this+'-filter');
                    if (filterContainer.data('field')){
                        filterField = filterContainer.data('field')
                    }
                    if (filterContainer.find('select').length > 0){
                        params[filterField] = filterContainer.find('select').val()
                    }else if (filterContainer.find('input').is(':checked')){
                        params[filterField] = 1;
                    }
                });
                if (self.settings.enableRangeFilter === true || $.inArray(params.interval, self.settings.enableRangeFilter) > -1) {
                    var dateValues = self.settings.filtersContainer.find("#range-slider").dateRangeSlider("values");
                    params['start'] = moment(dateValues.min).hours(0).minutes(0).format('YYYY-MM-DD HH:mm');
                    params['end'] = moment(dateValues.max).hours(23).minutes(59).format('YYYY-MM-DD HH:mm');
                    if (self.settings.enableEventFilter) {
                        params['event'] = self.settings.filtersContainer.find('#event-filter').find('select').val();
                    }
                }
                self.settings.load(self.chartContainer, params);
                $('#link').val('{/literal}{'/api/sensor/stats'|ezurl(no,full)}{literal}/' + self.chartContainer.data('identifier') + '?' + $.param(params));
                var downloadQuery = [];
                $.each(params, function (key,val){
                    if (val && val.length > 0) {
                        downloadQuery.push({name: key, value: val});
                    }
                });
                self.downloadButton.attr('href', self.downloadBaseHref + '?' + $.param(downloadQuery));
            });
            self.chartContainer.trigger('sensor:chart:filterchange');

            $("#linkButton").on("click", function (e){
                var copyText = document.querySelector("#link");
                copyText.select();
                document.execCommand("copy");
                e.preventDefault();
            });
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
<style>
    #posts-search label{display: block;white-space: nowrap;}
    .toggle.btn{min-height: 32px;}
    #stat-menu{max-height:none}
    #stat-menu.collapsing ul.nav li{text-align: left}
    #stat-menu.in ul.nav li{text-align: left}
</style>
{/literal}
</div>
