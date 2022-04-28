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

<section class="hgroup">
    <button class="navbar-toggle" data-target="#stat-menu" data-toggle="collapse" type="button" style="padding: 0;">
        <span class="fa fa-caret-down fa-2x"></span>
    </button>
    <h1>{sensor_translate('Statistics')}</h1>
</section>

<div class="row">
    <div class="col-md-3">
        <div class="navbar-collapse collapse" id="stat-menu">
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
                    <a id="download-csv" href="{if fetch('user', 'has_access_to', hash('module','sensor','function','manage'))}{'/sensor/dashboard/(export)'|ezurl(no)}{else}{'/sensor/export'|ezurl(no)}{/if}">
                        <i class="fa fa-download"></i> {sensor_translate('Export in CSV')}
                    </a>
                </li>
                <li>
                    <a href="{'sensor/openapi/'|ezurl(no)}">
                        <i class="fa fa-external-link-square"></i> {sensor_translate('Json API')}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-9">
        {if $current}
            <div id="chart-filters">
                <div class="row" id="posts-search">
                    <div class="col-md-3 form-group hide" id="type-filter">
                        <label>{sensor_translate('Filter by type')}</label>
                        <select class="select form-control" name="type" multiple>
                            {foreach sensor_types() as $type}
                                <option value="{$type.identifier}">
                                    {$type.name|wash()}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-3 form-group hide" id="area-filter">
                        <label>{sensor_translate('Filter by area')}</label>
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
                        <label>{sensor_translate('Filter by category')}</label>
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
                        <label>{sensor_translate('Filter by macro category')}</label>
                        <select class="select form-control" name="">
                            <option></option>
                            {foreach $categories.children as $item}
                                <option value="{$item.id}">{$item.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-3 form-group hide" id="maincategories-filter">
                        <label>{sensor_translate('Filter by macro category')}</label>
                        <select class="select form-control" name="" multiple>
                            {foreach $categories.children as $item}
                                <option value="{$item.id}">{$item.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-3 form-group hide" id="interval-filter">
                        <label>{sensor_translate('Filter by time interval')}</label>
                        <select class="select form-control" name="interval">
                            <option value="daily" class="daily_interval" disabled>{sensor_translate('Daily')}</option>
                            <option value="weekly">{sensor_translate('Weekly')}</option>
                            <option value="monthly">{sensor_translate('Monthly')}</option>
                            <option value="quarterly">{sensor_translate('Quarterly')}</option>
                            <option value="half-yearly">{sensor_translate('Half-yearly')}</option>
                            <option value="yearly" selected="selected">{sensor_translate('Yearly')}</option>
                        </select>
                    </div>
                    {def $usergroups = fetch(content, list, hash( parent_node_id, ezini("UserSettings", "DefaultUserPlacement"),
                                                                  attribute_filter, array(array('contentobject_id', '!=', sensor_operators_root_node().contentobject_id)),
                                                                  limitation, array(),
                                                                  class_filter_type, 'include',
                                                                  class_filter_array, array('user_group'),
                                                                  order_by, array('name', true()) ) )}
                    {if count($usergroups)|gt(0)}
                    <div class="col-md-3 form-group hide" id="usergroup-filter">
                        <label>{sensor_translate('Filter by author group')}</label>
                        <select class="select form-control" name="user_group" multiple>
                            <option value="0">{sensor_translate('No group')}</option>
                            {foreach $usergroups as $group}
                                <option value="{$group.contentobject_id}">{$group.name|wash()}</option>
                           {/foreach}
                        </select>
                    </div>
                    {/if}
                    {undef $usergroups}
                    <div class="col-md-3">
                        {if $has_group_tag}
                        <div style="display: flex">
                            <div>
                        {/if}
                        <div class="form-group hide" id="group-filter">
                            <label>{sensor_translate('Filter by group in charge')}</label>
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
                                {if $has_group_reference}
                                    <optgroup label="{sensor_translate('Reference')}">
                                    {foreach $references as $identifier => $reference}
                                        <option value="{$identifier}" style="font-style: italic">{$reference|wash()}</option>
                                    {/foreach}
                                    </optgroup>
                                {/if}
                            </select>
                        </div>
                        {if $has_group_tag}
                            </div>
                            <div>
                                <div class="form-group hide" id="taggroup-filter" style="margin-left: 10px">
                                    <label>{sensor_translate('Group data')}</label>
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
                            <label>{sensor_translate('Select event')}</label>
                            <select class="form-control" name="event">
                                <option value="open">{sensor_translate('Creating')}</option>
                                <option value="fix">{sensor_translate('Fixing')}</option>
                                <option value="close">{sensor_translate('Closing')}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div id="range-slider"></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane active" id="panel-{$current.identifier}">
                {include uri=concat('design:sensor_api_gui/charts/',$engine, '.tpl') stat=$current}
                {if fetch('user', 'has_access_to', hash('module','*','function','*'))}
                    <div id="editor-helper">
                        <div class="input-group">
                            <input class="form-control form-control-sm" type="text" id="link" />
                            <a id="linkButton" href="#" class="input-group-addon btn-info">{sensor_translate('Copy link')}</a>
                        </div>
                    </div>
                {/if}
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
                filters: ['type', 'area', 'category', 'interval', 'usergroup'],
                enableDailyInterval: false,
                enableEventFilter: false,
                enableRangeFilter: false,
                load: function (){},
                months: $.sensorTranslate.translate('Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec').split('_'),
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
                    var filterContainer = self.settings.filtersContainer.find('#'+this+'-filter');
                    if (filterContainer.find('select').length > 0){
                        params[this] = filterContainer.find('select').val()
                    }else if (filterContainer.find('input').is(':checked')){
                        params[this] = 1;
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
