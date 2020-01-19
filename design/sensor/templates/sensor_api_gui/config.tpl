{def $current_language = ezini('RegionalSettings', 'Locale')}
{def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
{def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}
{ezscript_require(array(
  'ezjsc::jqueryUI',
  'ezjsc::jqueryio',
  'jquery.opendataTools.js',
  'jsrender.js',
  'handlebars.min.js',
  'moment-with-locales.min.js',
  'jquery.fileupload.js',
  'jquery.fileupload-process.js',
  'jquery.fileupload-ui.js',
  'alpaca.js',
  'leaflet.0.7.2.js',
  'Control.Loading.js',
  'Control.Geocoder.js',
  'leaflet.draw.js',
  'leaflet-osm-data.js',
  'Leaflet.MakiMarkers.js',
  'leaflet.activearea.js',
  'leaflet.markercluster.js',
  'jquery.opendatabrowse.js',
  'fields/OpenStreetMap.js',
  'fields/DrawMap.js',
  'fields/RelationBrowse.js',
  concat('https://www.google.com/recaptcha/api.js?hl=', fetch( 'content', 'locale' ).country_code|downcase),
  'fields/Recaptcha.js',
  'jquery.opendataform.js')
)}
{ezcss_load(array(
  'alpaca.min.css',
  'leaflet.0.7.2.css',
  'Control.Loading.css',
  'MarkerCluster.css',
  'MarkerCluster.Default.css',
  'jquery.fileupload.css',
  'leaflet.draw.css',
  'alpaca-custom.css'
))}
<script>
  $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
  $.opendataTools.settings('language', "{$current_language}");
  $.opendataTools.settings('languages', ['{ezini('RegionalSettings','SiteLanguageList')|implode("','")}']);
  $.opendataTools.settings('locale', "{$moment_language}");
  $.opendataTools.settings('endpoint',{ldelim}'search': '{'/opendata/api/useraware/search/'|ezurl(no,full)}/'{rdelim});
</script>


<section class="hgroup">
  <h1>{'Settings'|i18n('sensor/menu')}</h1>
</section>

{if $moderation_is_enabled}
  <div class="alert alert-warning">
    {'Moderazione attivata'|i18n('sensor/config')}
  </div>
{/if}

<div class="row">
  <div class="col-md-12">
  <ul class="list-unstyled">
    {if $root.can_edit}
    <li>{'Modifica impostazioni generali'|i18n('sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$root redirect_if_discarded='/sensor/config' redirect_after_publish='/sensor/config'}</li>
    {/if}
    {if $post_container_node.can_edit}
    <li>{'Modifica informazioni Sensor'|i18n('sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$post_container_node redirect_if_discarded='/sensor/config' redirect_after_publish='/sensor/config'}</li>
    {/if}
    {def $default_approvers = sensor_default_approvers()}
    <li>
      {'Riferimento per il cittadino'|i18n('sensor/config')}:
      {if count($default_approvers)|gt(0)}
        {foreach $default_approvers as $approver}<strong>{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}</strong>{/foreach}
      {/if}
      <form class="form-inline" style="display: inline" action="{'sensor/config/operators'|ezurl(no)}" method="post">
        <button class="btn btn-default btn-xs" name="SelectDefaultApprover" type="submit">Modifica</button>
      </form>
    </li>
    {undef $default_approvers}
  </ul>
  <hr />
		
  <div class="row">
  
    <div class="col-md-3">    
      <ul class="nav nav-pills nav-stacked">
        <li role="presentation" {if $current_part|eq('users')}class="active"{/if}><a href="{'sensor/config/users'|ezurl(no)}">{'Utenti'|i18n('sensor/config')}</a></li>
          <li role="presentation" {if $current_part|eq('operators')}class="active"{/if}><a href="{'sensor/config/operators'|ezurl(no)}">{'Operatori'|i18n('sensor/config')}</a></li>
          <li role="presentation" {if $current_part|eq('categories')}class="active"{/if}><a href="{'sensor/config/categories'|ezurl(no)}">{'Categorie'|i18n('sensor/config')}</a></li>
          <li role="presentation" {if $current_part|eq('areas')}class="active"{/if}><a href="{'sensor/config/areas'|ezurl(no)}">{'Zone'|i18n('sensor/config')}</a></li>
          <li role="presentation" {if $current_part|eq('groups')}class="active"{/if}><a href="{'sensor/config/groups'|ezurl(no)}">{'Gruppi'|i18n('sensor/config')}</a></li>
          {if $data|count()|gt(0)}
            {foreach $data as $item}
              <li role="presentation" {if $current_part|eq(concat('data-',$item.contentobject_id))}class="active"{/if}><a href="{concat('sensor/config/data-',$item.contentobject_id)|ezurl(no)}">{$item.name|wash()}</a></li>
            {/foreach}
          {/if}
          <li role="presentation" {if $current_part|eq('notifications')}class="active"{/if}><a href="{'sensor/config/notifications'|ezurl(no)}">{'Testi notifiche'|i18n('sensor/config')}</a></li>
      </ul>
    </div>
      
    <div class="col-md-9">    

      {if $current_part|eq('notifications')}
        {include uri='design:sensor_api_gui/config/notifications.tpl'}
      {elseif and($current_part|begins_with('data-'), $data|count()|gt(0))}
        {foreach $data as $item}
          {if $current_part|eq(concat('data-',$item.contentobject_id))}
            {include uri=concat('design:sensor_api_gui/config/data.tpl') parent_node=$item}
          {/if}
        {/foreach}
      {else}
        {include uri=concat('design:sensor_api_gui/config/', $current_part, '.tpl')}
      {/if}
    </div>
  
  </div>
  
  </div>
</div>

<div id="modal" class="modal fade">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <div class="clearfix">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div id="item" class="clearfix"></div>
      </div>
    </div>
  </div>
</div>