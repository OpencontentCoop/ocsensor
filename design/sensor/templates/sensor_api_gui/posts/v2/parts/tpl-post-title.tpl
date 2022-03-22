{literal}
<script id="tpl-post-title" type="text/x-jsrender">
<section class="hgroup">
  <div class="pull-right">
    <a class="btn btn-default btn-lg button-icon"
         title="{{:~sensorTranslate('Save as pdf')}}"
         href="{{:~accessPath("/sensor/posts/")}}{{:id}}/pdf"><i class="fa fa-file-pdf-o"></i></a>
    {{if capabilities.can_edit}}
      <a class="btn btn-default btn-lg button-icon"
         title="{{:~sensorTranslate('Edit')}}"
         href="{{:~accessPath("/sensor/edit/")}}{{:id}}" data-post="{{:id}}"><i class="fa fa-pencil"></i></a>
    {{/if}}
    {{if capabilities.can_behalf_of}}
        <a class="btn btn-default btn-lg button-icon"
           title="{{:~sensorTranslate('Duplicate')}}"
           href="{{:~accessPath("/sensor/copy/")}}{{:id}}"{/literal}{if ezini('SensorConfig', 'SmartDuplicationGui', 'ocsensor.ini')|eq('enabled')}{literal} data-duplicate="{{:id}}"{/literal}{/if}{literal}><i class="fa fa-copy"></i></a>
    {{/if}}
    {{if capabilities.can_remove}}
      <a class="btn btn-default btn-lg button-icon" href="#"
         title="{{:~sensorTranslate('Delete')}}"
         data-remove
         data-confirmation="{{:~sensorTranslate('Are you sure you remove this content?')}}"
         data-post="{{:id}}"><i class="fa fa-trash"></i></a>
    {{/if}}
  </div>
  <h1>
    <span class="label label-{{:statusCss}}">{{:~sensorTranslate(status.identifier, 'status')}}</span>
    <span class="label label-primary" id="current-post-id">{{:id}}</span>
    {{:subject}}
  </h1>
  <p class="lead"><strong>{{:~sensorTranslate(type.identifier, 'type')}}</strong> &middot;
    {{if canReadUsers}}<a href="/sensor/user/{{:author.id}}">{{:author.name}}</a> {{if author.phone}}<a style="font-size:.6em" href="tel:{{:author.phone}}"><i class="fa fa-phone-square"></i> {{:author.phone}}</a>{{/if}}
    {{else}}{{:author.name}}{{/if}}
    {{for author.groups}}<span class="label label-default" data-usergroup="{{:#data}}">...</span>{{/for}}
  </p>
</section>
{/literal}{if ezini('SensorConfig', 'SmartDuplicationGui', 'ocsensor.ini')|eq('enabled')}{literal}
{{if capabilities.can_behalf_of}}
<div id="modal-duplicate" class="modal fade">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <div class="clearfix">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div id="form-duplicate" class="clearfix"></div>
      </div>
    </div>
  </div>
</div>
{{/if}}
{/literal}{/if}{literal}
</script>
{/literal}
