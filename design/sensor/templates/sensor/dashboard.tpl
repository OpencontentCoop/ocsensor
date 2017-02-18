{if $current_user_has_notifications|not()}
  <div class="alert alert-danger">
      <i class="fa fa-exclamation-triangle"></i>
      {"Attenzione: non hai nessuna notifica attiva e quindi non riceverai alcuna mail. Puoi attivare le notifiche nella pagina dedicata alle <a href='%notification_url'>impostazioni delle notifiche</a>."|i18n('sensor/dashboard', '', hash( '%notification_url', 'notification/settings'|ezurl(no) ))}
  </div>
{/if}
<section class="hgroup">
  <div class="row">
    <div class="col-md-7">
      <h1>
        {"Le mie segnalazioni"|i18n('sensor/dashboard')}
        {if $simplified_dashboard|not()}<br /><small>{"Segnalazioni da leggere, in corso e chiuse"|i18n('sensor/dashboard')}</small>{/if}
      </h1>
    </div>
    <div class="col-md-5">
        <strong>{"Legenda:"|i18n('sensor/dashboard')}</strong><br />
        {if $simplified_dashboard|not()}
          <i class="fa fa-comments-o"></i> {"indica la presenza di pareri"|i18n('sensor/dashboard')} <br />
        {/if}
        <i class="fa fa-comments"></i> {"indica la presenza di messaggi privati"|i18n('sensor/dashboard')} <br />
        <i class="fa fa-exclamation-triangle"></i> {"indica la presenza di variazioni in cronologia non lette"|i18n('sensor/dashboard')}
    </div>
  </div>
</section>

{if $simplified_dashboard}

  {if $all_items|count()|gt(0)}
    {include uri="design:sensor/post/table_items.tpl" item_list=$all_items name=all_items}
    {include name=navigator
            uri='design:navigator/google.tpl'
            page_uri='sensor/dashboard'
            item_count=$all_items_count
            view_parameters=$view_parameters
            item_limit=$limit}
  {/if}

{else}

  <div class="row">
    <div class="col-md-9">

      <ul class="nav nav-pills" style="margin-bottom: 10px">
        {foreach $list_types as $type}
          <li role="presentation" class="{if $current_list.identifier|eq($type.identifier)}active{/if} {if $type.count|eq(0)}disabled{/if}">
            <a href="{if $type.count|gt(0)}{concat('sensor/dashboard/(list)/',$type.identifier,$filters_query)|ezurl(no)}{else}#{/if}">
              {$type.name|wash()}
              <span class="badge">{$type.count}</span>
            </a>
          </li>
        {/foreach}
        <li role="presentation" class="pull-right">
            <a href="{concat('sensor/dashboard/post/default/csv/',$filters_query)|ezurl(no)}">
              <small><i class="fa fa-download"></i>
              {if $filters_query|eq('')}
                {"Esporta CSV"|i18n('sensor/dashboard')}
              {else}
                {"Esporta risultati CSV"|i18n('sensor/dashboard')}
              {/if}
              </small>
            </a>
          </li>
      </ul>
      <div class="tab-pane active">
        {include name=navigator
                uri='design:navigator/google.tpl'
                page_uri=concat('sensor/dashboard/(list)/',$current_list.identifier)
                page_uri_suffix=$filters_query
                item_count=$current_list.count
                view_parameters=$view_parameters
                item_limit=$limit}
        {include uri="design:sensor/post/table_items.tpl" item_list=$items name=items}
        {include name=navigator
                uri='design:navigator/google.tpl'
                page_uri=concat('sensor/dashboard/(list)/',$current_list.identifier)
                page_uri_suffix=$filters_query
                item_count=$current_list.count
                view_parameters=$view_parameters
                item_limit=$limit}
      </div>
    </div>
    
    <div class="col-md-3" id="sidebar">

      {if $expiring_items|count()}
        <aside class="widget" {if $expiring_items|count()|gt(3)}style="height: 300px;overflow-y: auto"{/if}>
          <h4 class="section_header">In scadenza</h4>
          <ul class="media-list">
              {foreach $expiring_items as $item}
                <li class="media">
                  <a class="media-date" href="{concat('sensor/posts/',$item.id)|ezurl(no)}" style="opacity: 1">
                      {$item.expiring_date.timestamp|datetime('custom', '%j')}<span>{$item.expiring_date.timestamp|datetime('custom', '%M')}</span>
                  </a>
                  <h5>
                    <a href={concat('sensor/posts/',$item.id)|ezurl()}>
                      <strong>{$item.id}</strong> {$item.object.name|wash()}
                    </a>
                  </h5>
                  <small>{$item.expiring_date.text|wash()}</small>
                </li>
              {/foreach}
          </ul>
        </aside>
      {/if}

      {ezscript_require(array('ezjsc::jquery','ezjsc::jqueryUI'))}
      <script type="text/javascript">
      {literal}$(function() {
        $( ".from_picker" ).datepicker({defaultDate: "+1w",changeMonth: true,changeYear: true,dateFormat: "dd-mm-yy",numberOfMonths: 1});
        $( ".to_picker" ).datepicker({defaultDate: "+1w",changeMonth: true,changeYear: true,dateFormat: "dd-mm-yy",numberOfMonths: 1});    
      });{/literal}
      </script>
      
      <div class="well dashboard-search">
        <form method="get" action="{'sensor/dashboard/post'|ezurl(no)}" class="form">
          <div class="form-group">
            <label class="" for="searchId">Cerca per Numero</label>
            <input type="text" value="{if is_set($filters.id)}{$filters.id|wash()}{/if}" placeholder="Cerca per Numero" name="filters[id]" id="searchId" class="form-control">
          </div>
          <div class="form-group">
            <label class="" for="searchCreatorId">Cerca per autore</label>
            <input type="text" value="{if is_set($filters.creator_id)}{$filters.creator_id|wash()}{/if}" placeholder="Cerca per autore" name="filters[creator_id]" id="searchCreatorId" class="form-control">
          </div>
          <div class="form-group">
            <label class="" for="searchSubject">Cerca per oggetto</label>
            <input type="text" value="{if is_set($filters.subject)}{$filters.subject|wash()}{/if}" placeholder="Cerca per oggetto" name="filters[subject]" id="searchSubject" class="form-control">
          </div>
          {def $fake_relation_list = array()}
          {if is_set( $filters.category )}
            {foreach $filters.category as $category}
              {set $fake_relation_list = $fake_relation_list|append( hash( 'contentobject_id', $category ) )}
            {/foreach}
          {/if}
          {def $fake_attribute = hash( 'content', hash( 'relation_list', $fake_relation_list ) )}      
          <div class="form-group">
            <label class="" for="searchCategory">Cerca per area tematica</label>
            <select data-placeholder="{'Cerca per area tematica'|i18n('sensor/post')}" name="filters[category][]" class="select form-control" id='searchCategory'>
              <option value="">{'Cerca per area tematica'|i18n('sensor/post')}</option>
              {foreach sensor_root_handler().categories.tree as $category}
                {include name=cattree uri='design:tools/walk_item_option.tpl' item=$category recursion=0 attribute=$fake_attribute}
              {/foreach}
            </select>
          </div>          
          <div class="form-group">
            <label class="" for="searchowner">Cerca per assegnatario</label>
            <select data-placeholder="{'Cerca per assegnatario'|i18n('sensor/post')}" name="filters[owner]" class="remote-select form-control" id='searchowner' data-value="operators">
              {if is_set( $filters.owner )}
               <option value="{$filters.owner}" selected="selected">{include uri='design:content/view/sensor_person.tpl' sensor_person=fetch( content, object, hash(object_id, $filters.owner) )}</option>
              {else}
                <option value="">{'Cerca per assegnatario'|i18n('sensor/post')}</option>
              {/if}
            </select>
          </div>
          <div class="form-group">
            <label for="from" class="">Data creazione (inizio)</label>
            <input type="text" class="form-control from_picker" name="filters[creation_range][from]" placeholder="Data creazione (inizio)" value="{if is_set($filters.creation_range.from)}{$filters.creation_range.from|wash()}{/if}" />
          </div>
          <div class="form-group">
            <label for="to" class="">Data creazione (fine)</label>
            <input class="form-control to_picker" type="text" name="filters[creation_range][to]" placeholder="Data creazione (fine)" value="{if is_set($filters.creation_range.to)}{$filters.creation_range.to|wash()}{/if}" />
          </div>
          <button class="btn btn-info" type="submit"><span class="fa fa-search"></span> Cerca</button>
          <a class="btn btn-danger pull-right" title="Reset" href="{'sensor/dashboard/post'|ezurl(no)}"><span class="fa fa-close"></span> Annulla</a>      
        </form>
      </div>
    </div>    
  </div>

  {def $locale = fetch( 'content', 'locale' ).country_code|downcase}
  {ezscript_require( array('ezjsc::jquery', 'select2.full.min.js', concat('select2-i18n/', $locale, '.js') ))}
  {ezcss_require(array('select2.min.css'))}
  <script type="application/javascript">
    var RemoteSelectUrl = {'sensor/data?contentType=operators'|ezurl()};
    var Locale = '{$locale}';
    {literal}
    $(document).ready(function(){
      $(".select").select2({
        language: Locale,
        templateResult: function (item) {
          var style = item.element ? $(item.element).attr('style') : '';
          return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
        }
      });
      $(".remote-select").each(function(){
        var that = $(this);
        that.select2({
          language: Locale,
          ajax: {
            url: RemoteSelectUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
              return {
                q: params.term, // search term
                page: params.page,
                value: that.data( 'value' )
              };
            },
            processResults: function (data, params) {
              params.page = params.page || 1;
              return {
                results: data.items,
                pagination: {
                  more: (params.page * 30) < data.total_count
                }
              };
            },
            cache: true
          },
          minimumInputLength: 1
        });
      });
    });
    {/literal}</script>

    <style>
      {literal}
          .label {
            font-size: 90%;
          }
      {/literal}
    </style>

{/if}
