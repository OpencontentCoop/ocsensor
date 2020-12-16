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
  'turf.min.js',
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
  <div class="col-md-3">
    <ul class="nav nav-pills nav-stacked">
      {foreach $menu as $suffix => $item}
        <li role="presentation" {if $current_part|eq($suffix)}class="active"{/if}><a href="{$item.uri|ezurl(no)}">{$item.label|wash()}</a></li>
      {/foreach}
    </ul>
  </div>
  <div class="col-md-9">
    {if $current_part|begins_with('data-')}
      {include uri=concat('design:sensor_api_gui/config/data.tpl') parent_node=$menu[$current_part].node}
    {else}
      {include uri=concat('design:sensor_api_gui/config/', $current_part, '.tpl')}
    {/if}
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
          if(response.result !== 'success'){
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