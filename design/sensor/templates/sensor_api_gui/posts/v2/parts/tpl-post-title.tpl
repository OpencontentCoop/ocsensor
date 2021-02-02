{literal}
<script id="tpl-post-title" type="text/x-jsrender">
<section class="hgroup">
  <div class="pull-right">
    {{if capabilities.can_edit}}
      <a class="btn btn-default btn-lg button-icon"
         title="{/literal}{'Modifica'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
         href="{{:accessPath}}/sensor/edit/{{:id}}" data-post="{{:id}}"><i class="fa fa-pencil"></i></a>
    {{/if}}
    {{if capabilities.can_behalf_of}}
        <a class="btn btn-default btn-lg button-icon"
           title="{/literal}{'Crea una copia'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
           href="{{:accessPath}}/sensor/copy/{{:id}}" data-post="{{:id}}"><i class="fa fa-copy"></i></a>
    {{/if}}
    {{if capabilities.can_remove}}
      <a class="btn btn-default btn-lg button-icon" href="#"
         title="{/literal}{'Elimina'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
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
  <p class="lead"><strong>{{:type.name}}</strong> di {{if canReadUsers}}<a href="/sensor/user/{{:author.id}}">{{:author.name}}</a>{{else}}{{:author.name}}{{/if}}</p>
</section>
</script>
{/literal}