<section class="hgroup">
    <h1>{'Statistiche'|i18n('sensor/menu')}</h1>
</section>

<div class="row">

    <div class="col-md-3">
        <ul class="nav nav-pills nav-stacked">
            {foreach sensor_chart_list() as $chart}
            <li role="presentation" {if and( $current, $current.identifier|eq($chart.identifier))}class="active"{/if}>
                <a href="{concat('sensor/stat/',$chart.identifier)|ezurl(no)}">
                    {$chart.name|wash()}
                </a>
            </li>
            {/foreach}
        </ul>
    </div>

    <div class="col-md-9">
        {if $current}
        <div class="tab-pane active" id="{$current.identifier}">
            {include uri=$current.template_uri}
        </div>
        {/if}
    </div>

</div>
