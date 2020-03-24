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
  'jquery.opendataform.js',
  'bootstrap-toggle.min.js'
))}
{ezcss_load(array(
  'alpaca.min.css',
  'leaflet.0.7.2.css',
  'Control.Loading.css',
  'MarkerCluster.css',
  'MarkerCluster.Default.css',
  'jquery.fileupload.css',
  'leaflet.draw.css',
  'alpaca-custom.css',
  'bootstrap-toggle.min.css'
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


<div class="row">
  <div class="col-md-12">
  
  <div class="row">
  
    <div class="col-md-3">    
      <ul class="nav nav-pills nav-stacked">
          <li role="presentation" {if $current_part|eq('default')}class="active"{/if}><a href="{'sensor/config'|ezurl(no)}">{'Settings'|i18n('sensor/config')}</a></li>
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

      {if $current_part|eq('default')}
          
          <table class="table table-striped">
            {if $root.can_edit}
            <tr>
              <th>Impostazioni generali</th>
              <td class="text-center">
                <a class="btn btn-default" href="{concat('/content/edit/', $root.contentobject_id, '/f')|ezurl(no)}">Modifica</a>
              </td>
            </tr>
            {/if}
            {if $post_container_node.can_edit}
            <tr>
              <th>Informazioni Sensor</th>
              <td class="text-center">                
                <a class="btn btn-default" href="{concat('/content/edit/', $post_container_node.contentobject_id, '/f')|ezurl(no)}">Modifica</a>
              </td>
            </tr>
            {/if}
            <tr>
              <th>
                {'Riferimento per il cittadino'|i18n('sensor/config')}:
                {def $default_approvers = sensor_default_approvers()}
                {if count($default_approvers)|gt(0)}
                  {foreach $default_approvers as $approver}{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}{/foreach}
                {/if}                                
                <br /><small>Con questa opzione si individua l'operatore che prende in carico in prima battuta le segnalazioni</small>
              </th>
              <td class="text-center">
                <form class="form-inline" style="display: inline" action="{'sensor/config/operators'|ezurl(no)}" method="post">
                  <button class="btn btn-default" name="SelectDefaultApprover" type="submit">Cambia</button>
                </form>
              </td>              
            </tr>
            <tr{if $moderation_is_enabled} class="warning"{/if}>
              <th>
              	Imposta come privata ogni nuova segnalazione inserita
              	<br /><small>Se l'opzione è attivata le nuove segnalazioni non sono pubblicamente visibili</small>
              </th>
              <td class="text-center">
                <input type="checkbox" {if $moderation_is_enabled}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="Moderation"{else}disabled{/if}>
              </td>
            </tr>
            <tr>
              <th>
                Nascondi al segnalatore il consenso di pubblicazione
              	<br /><small>Se l'opzione è attivata non viene richiesto al segnalatore il consenso di rendere pubblica la segnalazione: gli operatori non potranno in alcun modo renderla pubblica</small>
              </th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.HidePrivacyChoice}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HidePrivacyChoice"{else}disabled{/if}>
              </td>
            </tr>
            <tr>
              <th>
              	Nascondi al pubblico la timeline dettagliata
              	<br /><small>Se l'opzione è attivata verranno mostrati nella cronologia soltanto gli eventi di presa in carico e chiusura</small>
              </th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.HideTimelineDetails}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideTimelineDetails"{else}disabled{/if}>
              </td>
            </tr>
            <tr>
              <th>
              	Assegnazione automatica in base alla categoria
              	<br /><small>Se l'opzione è attivata, quando viene associata una categoria, la segnalazione verrà assegnata al gruppo e agli operatori configurati nella categoria</small>
              </th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.CategoryAutomaticAssign}checked{/if} disabled data-toggleconfig>
              </td>
            </tr>
            {if $sensor_settings.CategoryAutomaticAssign}
            <tr>
              <th>
              	Assegnazione casuale all'operatore in base alla categoria
              	<br /><small>Se l'opzione è attivata, in assenza di un'indicazione esplicita di operatore di categoria, ne viene scelto uno casualmente dal gruppo di riferimento</small>
              </th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.CategoryAutomaticAssignToRandomOperator}checked{/if} disabled data-toggleconfig>
              </td>
            </tr>
            {/if}
            <tr>
              <th>Il segnalatore può riaprire una segnalazione chiusa</th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.AuthorCanReopen}checked{/if} disabled data-toggleconfig>
              </td>              
            </tr>  
            <tr>
              <th>Il riferimento può riaprire una segnalazione chiusa</th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.ApproverCanReopen}checked{/if} disabled data-toggleconfig>
              </td>                
            </tr>    
            {*
            <tr>
              <th>Impedisci al segnalatore di selezionare la zona</th>
              <td class="text-center">
                <input type="checkbox" {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|eq('enabled')}checked{/if} disabled data-toggleconfig>
              </td>
            </tr>
            *}
            <tr>
              <th>Quando viene terminato un intervento, reimposta sempre come riferimento {foreach $default_approvers as $approver}{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}{/foreach}</th>
              <td class="text-center">
                <input type="checkbox" {if $sensor_settings.ForceUrpApproverOnFix}checked{/if} disabled data-toggleconfig>
              </td>
            </tr>
          </table>
      {elseif $current_part|eq('notifications')}
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

{literal}
<script>
  $(document).ready(function() {
    $('[data-toggleconfig]').bootstrapToggle({
      on: 'SI',
      off: 'NO',
      onstyle: 'success'
    }).change(function(e) {
      var self = $(this);
      if (self.data('attribute')){
        $.getJSON('{/literal}{'sensor/config/_set'|ezurl(no)}{literal}/?key='+self.data('attribute')+'&value='+self.prop('checked'), function(response){
          if(response.result != 'success'){
            self.data('attribute', false);            
            var revertValue = self.prop('checked') ? 'off' : 'on';            
            self.bootstrapToggle(revertValue);            
            self.prop('disabled', 'disabled');
          }
        });
      }
    });
  })
</script>
{/literal}