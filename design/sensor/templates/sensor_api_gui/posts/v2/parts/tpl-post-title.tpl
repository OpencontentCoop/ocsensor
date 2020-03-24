{literal}
<script id="tpl-post-title" type="text/x-jsrender">
<section class="hgroup">
  <div class="pull-right">
    {{if capabilities.can_edit}}
      <a class="btn btn-default btn-lg button-icon" href="{{:accessPath}}/sensor/edit/{{:id}}" data-post="{{:id}}"><i class="fa fa-pencil"></i></a>
    {{/if}}
    {{if capabilities.can_remove}}
      <a class="btn btn-default btn-lg button-icon" href="#"
         data-remove
         data-confirmation="{/literal}{'Sei sicuro di voler rimuovere questo contenuto?'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
         data-post="{{:id}}"><i class="fa fa-trash"></i></a>
    {{/if}}
  </div>
  <h1>
    <span class="label label-{{:statusCss}}">{{:status.name}}</span>
    <span class="label label-primary" id="current-post-id">{{:id}}</span>
    {{:subject}}
  </h1>
  <p class="lead"><strong>{{:type.name}}</strong> di {{:author.name}}</p>
</section>
</script>
{/literal}