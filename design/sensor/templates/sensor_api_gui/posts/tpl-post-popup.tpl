{literal}
<script id="tpl-post-popup" type="text/x-jsrender">
<div class="clearfix">
    <h4><a href="{{:~accessPath("/sensor/posts/")}}{{:id}}">{{:subject}}</a></h4>
    <ul class="list-inline"><li>
      <span class="label label-{{:typeCss}}">{{:type.name}}</span>
      <span class="label label-{{:statusCss}}">{{:status.name}}</span>
    </li></ul>
    <ul class="list-unstyled">
        <li><small><i class="fa fa-clock-o"></i> {{:~sensorTranslate('Created at')}} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
        {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
        <li><small><i class="fa fa-clock-o"></i> {{:~sensorTranslate('Last modified at')}} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>
        {{/if}}
        <li><small><i class="fa fa-comments"></i> {{:comment_count}} {{:~sensorTranslate('comments')}}</small></li>
        <li><small><i class="fa fa-comment"></i> {{:response_count}} {{:~sensorTranslate('official replies')}}</small></li>
    </ul>
    <p><a href="{{:~accessPath("/sensor/posts/")}}{{:id}}" class="btn btn-info btn-sm" style="color:#fff">{{:~sensorTranslate('Details')}}</a></p>
</div>
</script>
{/literal}
