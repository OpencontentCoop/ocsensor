{literal}
<script id="tpl-post-popup" type="text/x-jsrender">
<div class="clearfix">
    <h4><a href="{{:accessPath}}/sensor/posts/{{:id}}">{{:subject}}</a></h4>
    <ul class="list-inline"><li>
      <span class="label label-{{:typeCss}}">{{:type.name}}</span>
      <span class="label label-{{:statusCss}}">{{:status.name}}</span>
    </li></ul>
    <ul class="list-unstyled">
        <li><small><i class="fa fa-clock-o"></i> {/literal}{'Pubblicata il'|i18n('sensor/post')}{literal} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
        {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
        <li><small><i class="fa fa-clock-o"></i> {/literal}{'Ultima modifica del'|i18n('sensor/post')}{literal} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>
        {{/if}}
        <li><small><i class="fa fa-comments"></i> {{:comment_count}} {/literal}{'commenti'|i18n('sensor/post')}{literal}</small></li>
        <li><small><i class="fa fa-comment"></i> {{:response_count}} {/literal}{'risposte ufficiali'|i18n('sensor/post')}{literal}</small></li>
    </ul>
    <p><a href="{{:accessPath}}/sensor/posts/{{:id}}" class="pull-right btn btn-info btn-sm" style="color:#fff">{/literal}{'Dettagli'|i18n('sensor/post')}{literal}</a></p>
</div>
</script>
{/literal}