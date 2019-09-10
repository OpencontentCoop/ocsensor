{ezscript_require( array( 'ezjsc::jquery', 'highcharts/charts/highcharts.js', 'highcharts/charts/modules/exporting.js' ) )}
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
        </ul>
    </div>

    <div class="col-md-9">
        {if $current}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group hide" id="area-filter">
                        <label>{'Filtra per Area/Quartiere/Zona'|i18n('sensor/post')}</label>
                        <select class="select form-control" name="area">
                            <option></option>
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
                        <select class="select form-control" name="">
                            <option></option>
                            {foreach $categories.children as $item}
                                <option value="{$item.id}"
                                        style="padding-left:{$item.level|mul(10)}px;{*if $item.level|eq(0)}font-weight: bold;{/if*}">{$item.name|wash()}</option>
                                {*foreach $item.children as $child}
                                    <option value="{$child.id}" style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                                {/foreach*}
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group hide" id="interval-filter">
                        <label>{'Filtra per Intervallo di tempo'|i18n('sensor/post')}</label>
                        <select class="select form-control" name="interval">
                            <option value="monthly">{'Mensile'|i18n('sensor/chart')}</option>
                            <option value="quarterly">{'Trimestrale'|i18n('sensor/chart')}</option>
                            <option value="half-yearly">{'Semestrale'|i18n('sensor/chart')}</option>
                            <option value="yearly" selected="selected">{'Annuale'|i18n('sensor/chart')}</option>
                        </select>
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
