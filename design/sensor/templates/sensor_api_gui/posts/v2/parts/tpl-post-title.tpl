{literal}
<script id="tpl-post-title" type="text/x-jsrender">
<section class="hgroup">
  <div class="pull-right">
    {{if capabilities.can_add_image}}
    <div data-action-wrapper style="display: inline-block">
        <form class="form-group" data-upload="add_image" style="display: inline-block;margin-right: 1px">
            <div class="upload-button-container">
                <span class="btn btn-default fileinput-button button-icon btn-lg" style="cursor:pointer">
                    <i style="font-size: 0.8em;z-index: 4;position: absolute;display: inline-block;left: 1px;top: 2px;" class="fa fa-plus-circle text-primary"></i><i class="fa fa-image"></i>
                    <input class="upload" name="files" type="file">
                </span>
            </div>
            <div class="upload-button-spinner btn btn-default button-icon btn-lg" style="display: none">
                <i class="fa fa-cog fa-spin"></i>
            </div>
        </form>
    </div>
    {{else capabilities.is_author}}
        <div class="btn btn-default fileinput-button button-icon btn-lg">
            <i style="font-size: 0.8em;z-index: 4;position: absolute;display: inline-block;left: 1px;top: 2px;" class="fa fa-times text-muted"></i><i class="fa fa-image text-muted"></i>
        </div>
    {{/if}}
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
  <p class="lead"><strong>{{:type.name}}</strong> di {{:author.name}}</p>
</section>
</script>
{/literal}