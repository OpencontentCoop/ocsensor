{literal}
<script id="tpl-post-title" type="text/x-jsrender">
<section class="hgroup">
  <h1>
    <span class="label label-primary" id="current-post-id">{{:id}}</span> {{:subject}} <small>{{:author.name}}</small>
    {{if capabilities.can_edit}}
      <a class="btn btn-warning btn-sm" href="{{:accessPath}}/sensor/edit/{{:id}}" data-post="{{:id}}"><i class="fa fa-edit"></i></a>
    {{/if}}
    {{if capabilities.can_remove}}
      <a class="btn btn-danger btn-sm" href="#"
         data-remove
         data-confirmation="{/literal}{'Sei sicuro di voler rimuovere questo contenuto?'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
         data-post="{{:id}}"><i class="fa fa-trash"></i></a>
    {{/if}}
  </h1>
    <ul class="breadcrumb pull-right" id="current-post-breadcrumb">
      <li>
        <span class="label label-{{:typeCss}}">{{:type.name}}</span>
        <span class="label label-{{:statusCss}}">{{:status.name}}</span>
        {{if privacy.identifier == 'private'}}
          <span class="label label-default">{{:privacy.name}}</span>
        {{/if}}
        {{if moderation.identifier == 'waiting'}}
          <span class="label label-danger">{{:moderation.name}}</span>
        {{/if}}
      </li>
    </ul>
</section>
</script>
{/literal}